<?php

namespace Crate\yii;
use yii\db\Connection;


class CrateConnection  extends Connection
{
    private $__driverName;

    public $schemaMap = [
        'crate' => 'Crate\yii\Schema', // Elastic
    ];

    public $commandMap = [
        'crate' => 'yii\db\Command'
    ];

    protected function createPdoInstance()
    {
        $pdoClass = $this->pdoClass;
        if ($pdoClass === null) {
            $pdoClass = 'PDO';
            $this->__driverName=$this->getDriverName();
            if ($this->__driverName !== null) {
                $driver = $this->__driverName;
            } elseif (($pos = strpos($this->dsn, ':')) !== false) {
                $driver = strtolower(substr($this->dsn, 0, $pos));
            }
            if (isset($driver)) {
                if ($driver === 'crate') {
                    $pdoClass = 'Crate\PDO\PDO';
                    $this->setDriverName($this->__driverName);
                }
            }
        }

        $dsn = $this->dsn;

        return new $pdoClass($dsn, $this->username, $this->password, $this->attributes);
    }
}