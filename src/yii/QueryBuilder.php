<?php

namespace Crate\yii;

use yii\base\InvalidArgumentException;
use yii\db\Constraint;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\db\Query;
use yii\db\PdoValue;
use yii\helpers\StringHelper;

/**
 * QueryBuilder is the query builder for Elastic databases.
 *
 */
class QueryBuilder extends \yii\db\QueryBuilder
{


    /**
     * @var array mapping from abstract column types (keys) to physical column types (values).
     */
    public $typeMap = [
        Schema::TYPE_PK => 'PRIMARY KEY',
        Schema::TYPE_CHAR => 'byte',
        Schema::TYPE_STRING => 'varchar(255)',
        Schema::TYPE_TEXT => 'string',
        Schema::TYPE_TINYINT => 'smallint',
        Schema::TYPE_SMALLINT => 'smallint',
        Schema::TYPE_INTEGER => 'integer',
        Schema::TYPE_BIGINT => 'bigint',
        Schema::TYPE_FLOAT => 'float',
        Schema::TYPE_DOUBLE => 'double',
        Schema::TYPE_DECIMAL => 'double',
        Schema::TYPE_DATETIME => 'timestamp',
        Schema::TYPE_TIMESTAMP => 'timestamp',
        Schema::TYPE_TIME => 'timestamp',
        Schema::TYPE_DATE => 'timestamp',
        Schema::TYPE_BINARY => 'bytea',
        Schema::TYPE_BOOLEAN => 'boolean',
        Schema::TYPE_MONEY => 'double',
//        Schema::TYPE_JSON => 'jsonb',
    ];


    /**
     * {@inheritdoc}
     */
    protected function defaultConditionClasses()
    {
        return array_merge(parent::defaultConditionClasses(), [
            'ILIKE' => 'yii\db\conditions\LikeCondition',
            'NOT ILIKE' => 'yii\db\conditions\LikeCondition',
            'OR ILIKE' => 'yii\db\conditions\LikeCondition',
            'OR NOT ILIKE' => 'yii\db\conditions\LikeCondition',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultExpressionBuilders()
    {
        return array_merge(parent::defaultExpressionBuilders(), [
            'yii\db\ArrayExpression' => 'Crate\yii\ArrayExpressionBuilder',
            'yii\db\JsonExpression' => 'Crate\yii\JsonExpressionBuilder',
        ]);
    }


    /**
     * {@inheritdoc}
     */
//    public function batchInsert($table, $columns, $rows, &$params = [])
//    {
//        if (empty($rows)) {
//            return '';
//        }
//
//        $schema = $this->db->getSchema();
//        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
//            $columnSchemas = $tableSchema->columns;
//        } else {
//            $columnSchemas = [];
//        }
//
//        $values = [];
//        foreach ($rows as $row) {
//            $vs = [];
//            foreach ($row as $i => $value) {
//                if (isset($columns[$i], $columnSchemas[$columns[$i]])) {
//                    $value = $columnSchemas[$columns[$i]]->dbTypecast($value);
//                }
//                if (is_string($value)) {
//                    $value = $schema->quoteValue($value);
//                } elseif (is_float($value)) {
//                    // ensure type cast always has . as decimal separator in all locales
//                    $value = StringHelper::floatToString($value);
//                } elseif ($value === true) {
//                    $value = 'TRUE';
//                } elseif ($value === false) {
//                    $value = 'FALSE';
//                } elseif ($value === null) {
//                    $value = 'NULL';
//                } elseif ($value instanceof ExpressionInterface) {
//                    $value = $this->buildExpression($value, $params);
//                }
//                $vs[] = $value;
//            }
//            $values[] = '(' . implode(', ', $vs) . ')';
//        }
//        if (empty($values)) {
//            return '';
//        }
//
//        foreach ($columns as $i => $name) {
//            $columns[$i] = $schema->quoteColumnName($name);
//        }
//
//        return 'INSERT INTO ' . $schema->quoteTableName($table)
//        . ' (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $values);
//    }
}
