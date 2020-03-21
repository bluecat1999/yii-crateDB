<?php

namespace Crate\yii;
use yii\db\ArrayExpression;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;

class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var int the dimension of array. Defaults to 0, means this column is not an array.
     */
    public $dimension = 0;

    /**
     * @var bool whether the column schema should OMIT using JSON support feature.
     * Default to `false`, meaning JSON support is enabled.
     */
    public $disableJsonSupport = false;

    /**
     * @var bool whether the column schema should OMIT using Elastic Arrays support feature.
     * Default to `false`, meaning Arrays support is enabled.
     */
    public $disableArraySupport = false;

    /**
     * @var bool whether the column schema should OMIT using Elastic Object support feature.
     * Default to `false`, meaning Arrays support is enabled.
     */
    public $disableObjectSupport = false;

    /**
     * @var bool whether the Array column value should be unserialized to an [[ArrayExpression]] object.
     * Default to `true`, meaning arrays are unserialized to [[ArrayExpression]] objects.
     */
    public $deserializeArrayColumnToArrayExpression = true;

    /**
     * {@inheritdoc}
     */
    public function dbTypecast($value)
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof ExpressionInterface) {
            return $value;
        }

        if ($this->dimension > 0) {
            return $this->disableArraySupport
                ? (string) $value
                : new ArrayExpression($value, $this->dbType, $this->dimension);
        }
        if (!$this->disableJsonSupport && in_array($this->dbType, [Schema::TYPE_JSON,Schema::TYPE_ARRAY], true)) {
            return new JsonExpression($value, $this->dbType);
        }

        return $this->typecast($value);
    }

    /**
     * {@inheritdoc}
     */
    public function phpTypecast($value)
    {
        if ($this->dimension > 0) {
            if ($this->disableArraySupport) {
                return $value;
            }
            if (!is_array($value)) {
                $value = $this->getArrayParser()->parse($value);
            }
            if (is_array($value)) {
                array_walk_recursive($value, function (&$val, $key) {
                    $val = $this->phpTypecastValue($val);
                });
            } elseif ($value === null) {
                return null;
            }

            return $this->deserializeArrayColumnToArrayExpression
                ? new ArrayExpression($value, $this->dbType, $this->dimension)
                : $value;
        }

        return $this->phpTypecastValue($value);
    }

    /**
     * Casts $value after retrieving from the DBMS to PHP representation.
     *
     * @param string|null $value
     * @return bool|mixed|null
     */
    protected function phpTypecastValue($value)
    {
        if ($value === null) {
            return null;
        }

        switch ($this->type) {
            case Schema::TYPE_BOOLEAN:
                switch (strtolower($value)) {
                    case 'true':
                        return true;
                    case 'false':
                        return false;
                }
                return (bool) $value;
            case Schema::TYPE_JSON:
                return $this->disableJsonSupport ? $value : json_decode($value, true);
        }

        return parent::phpTypecast($value);
    }


}