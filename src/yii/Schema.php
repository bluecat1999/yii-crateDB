<?php
/*
 * PROJECT_NAME:JG-B2B
 * 
 * Copyright (C) 2010-2101 Jiangong SiYu Software  Limited.
 *
 */

namespace Crate\yii;

use yii\base\NotSupportedException;
use yii\db\ConstraintFinderInterface;
use yii\db\ConstraintFinderTrait;
use yii\db\TableSchema;
use yii\db\ViewFinderTrait;
use Crate\PDO\PDO;

class Schema extends \yii\db\Schema implements ConstraintFinderInterface
{

    use ViewFinderTrait;
    use ConstraintFinderTrait;

    const TYPE_ARRAY = 'array';

    /**
     * {@inheritdoc}
     */
    public $columnSchemaClass = 'Crate\yii\ColumnSchema';

    /**
     * @var array mapping from physical column types (keys) to abstract column types (values)
     */
    public $typeMap = [
//`boolean`__   `boolean`__
//`byte`__
//`short`__     `integer`__
//`integer`__   `integer`__
//`long`__      `integer`__
//`float`__     `float`__
//`double`__    `float`__
//`string`__    `string`__
//`ip`__        `string`__
//`timestamp`__ `integer`__
//`geo_point`__ `array`__
//`geo_shape`__ `object`__
//`object`__    `object`__
//`array`__     `array`__
        'boolean'  => self::TYPE_BOOLEAN,
        'smallint' => self::TYPE_SMALLINT,
        'int2'     => self::TYPE_SMALLINT,
        'int4'     => self::TYPE_INTEGER,
        'int'      => self::TYPE_INTEGER,
        'integer'  => self::TYPE_INTEGER,
        'bigint'   => self::TYPE_BIGINT,
        'int8'     => self::TYPE_BIGINT,
        'long'     => self::TYPE_BIGINT,

        'char'   => self::TYPE_CHAR,
        'byte'   => self::TYPE_CHAR,
        'text'   => self::TYPE_TEXT,
        'string' => self::TYPE_TEXT,
        'name'   => self::TYPE_TEXT,

        'real'   => self::TYPE_FLOAT,
        'float'  => self::TYPE_FLOAT,
        'double' => self::TYPE_DOUBLE,

        'timestamp' => self::TYPE_TIMESTAMP,
        'array'     => self::TYPE_TEXT,
        'object'    => self::TYPE_JSON,

    ];

    protected $tableQuoteCharacter = '"';

    protected $columnQuoteCharacter = '"';


    protected function getDefaultSchema()
    {
        $dsn = $this->db->dsn;
        if (!preg_match(PDO::DSN_REGEX, $dsn, $matches)) {
            throw new \Exception(sprintf('Invalid DSN %s', $dsn));
        }
        $match = array_slice($matches, 1);

        if(!empty($match[1])) return $match[1];
        if(!empty($this->db->attributes[PDO::CRATE_ATTR_DEFAULT_SCHEMA])){
            return $this->db->attributes[PDO::CRATE_ATTR_DEFAULT_SCHEMA];
        }
    }
    protected function resolveTableName($name)
    {
        $resolvedName = new TableSchema();
        $parts = explode('.', str_replace('"', '', $name));
        if (isset($parts[1])) {
            $resolvedName->schemaName = $parts[0];
            $resolvedName->name = $parts[1];
        } else {
            $resolvedName->schemaName = $this->defaultSchema?:$this->getDefaultSchema();
            $resolvedName->name = $name;
        }
        $resolvedName->fullName = ($resolvedName->schemaName !== $this->defaultSchema ? $resolvedName->schemaName . '.' : '') . $resolvedName->name;
        return $resolvedName;
    }



    /**
     * Creates a query builder for the Elastic database.
     * @return QueryBuilder query builder instance
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }
    /**
     * {@inheritdoc}
     */
    protected function findTableNames($schema = '')
    {
        $sql = 'SHOW TABLES';
        if ($schema !== '') {
            $sql .= ' FROM ' . $this->quoteSimpleTableName($schema);
        }

        return $this->db->createCommand($sql)->queryColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function findSchemaNames()
    {
        $sql = 'SHOW SCHEMAS';
        return $this->db->createCommand($sql)->queryColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableSchema($name)
    {
        $table = new TableSchema();
        $this->resolveTableNames($table, $name);

        if ($this->findColumns($table)) {
            $this->findConstraints($table);
            return $table;
        }

        return null;
    }

    /**
     * Resolves the table name and schema name (if any).
     * @param TableSchema $table the table metadata object
     * @param string $name the table name
     */
    protected function resolveTableNames($table, $name)
    {
        $parts = explode('.', str_replace('`', '', $name));
        if (isset($parts[1])) {
            $table->schemaName = $parts[0];
            $table->name = $parts[1];
            $table->fullName = $table->schemaName . '.' . $table->name;
        } else {
            $table->fullName = $table->name = $parts[0];
            $table->schemaName = $this->defaultSchema?:$this->getDefaultSchema();
        }
    }

    /**
     * Collects the metadata of table columns.
     * @param TableSchema $table the table metadata
     * @return bool whether the table exists in the database
     * @throws \Exception if DB query fails
     */
    protected function findColumns($table)
    {
        $tableName = $this->quoteValue($table->name);
        $schemaName = $this->quoteValue($table->schemaName);
        $sql = <<<SQL
SELECT a.column_name AS column_name,
       data_type,
       is_nullable,
       b.column_name AS Key
       FROM information_schema.columns a 
       LEFT JOIN information_schema.key_column_usage b ON a.table_name=b.table_name AND a.column_name=b.column_name
       WHERE a.table_schema={$schemaName} AND a.table_name={$tableName}
       ORDER BY a.table_name ASC, a.column_name ASC;

SQL;
        $columns = $this->db->createCommand($sql)->queryAll();
        if (empty($columns)) {
            return false;
        }
        foreach ($columns as $info) {
            $column = $this->loadColumnSchema($info);
            $table->columns[$column->name] = $column;
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
                }
        }
        return true;
    }

    /**
     * {@inheritdoc]
     */
    protected function findViewNames($schema = '')
    {
        if ($schema === '') {
            $schema = $this->defaultSchema;
        }
        $sql = <<<'SQL'
SELECT table_name
FROM information_schema.views
WHERE table_schema = :schemaName 
ORDER BY table_schema ASC, table_name ASC
SQL;
        return $this->db->createCommand($sql, [':schemaName' => $schema])->queryColumn();
    }

    /**
     * Collects the foreign key column details for the given table.
     * @param TableSchema $table the table metadata
     */
    protected function findConstraints($table)
    {
        // does not support foreign key
    }

    
    protected function loadColumnSchema($info)
    {
        $column = $this->createColumnSchema();
        $column->allowNull = $info['is_nullable'];
        $column->dbType = $info['data_type'];
        if(strpos($info['data_type'],'array')!==false){
            $column->dbType = 'array';
//            $column->dimension = 1;
        }
        $column->isPrimaryKey = !empty($info['key'])?true:false;
        $column->name = $info['column_name'];

        if (isset($this->typeMap[$column->dbType])) {
            $column->type = $this->typeMap[$column->dbType];
        } else {
            $column->type = self::TYPE_STRING;
        }
        $column->phpType = $this->getColumnPhpType($column);

        return $column;

    }

    protected function loadTablePrimaryKey($tableName)
    {
        $sql=<<<SQL
SELECT column_name 
    FROM information_schema.key_column_usage
    WHERE table_schema=:schemaName AND table_name=:tableName
SQL;
        $resolvedName = $this->resolveTableName($tableName);
        return $this->db->createCommand($sql, [
            ':schemaName' => $resolvedName->schemaName,
            ':tableName' => $resolvedName->name,
        ])->queryAll();

    }

    protected function loadTableForeignKeys($tableName)
    {
        throw new NotSupportedException('Elastic DB does not support default value constraints.');
    }

    protected function loadTableChecks($tableName)
    {
        // TODO: Implement loadTableChecks() method.
    }

    protected function loadTableUniques($tableName)
    {
        throw new NotSupportedException('Elastic DB does not support default value constraints.');
    }

    protected function loadTableDefaultValues($tableName)
    {
        throw new NotSupportedException('Elastic DB does not support default value constraints.');
    }

    protected function loadTableIndexes($tableName)
    {
        throw new NotSupportedException('Elastic DB does not support default value constraints.');
    }


}