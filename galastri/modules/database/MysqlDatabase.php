<?php

namespace galastri\modules\database;

use galastri\extensions\Exception;
use galastri\language\Message;

class MysqlDatabase
{
    use traits\CoreDatabase {
        traits\CoreDatabase::setPort as private parentSetPort;
    }

    const DRIVER = 'mysql';
    
    private array $requiredConfigKeys = ['host', 'port', 'username', 'password', 'database'];
    private array $requiredProperties = ['host', 'username', 'database'];

    public function __construct(?string $dbConfigName = null)
    {
        $config = $this->resolveDatabaseConfig($dbConfigName);

        if (empty($config)) {
            return;
        }

        $this->setHost($config['host']);
        $this->setPort($config['port']);
        $this->setDatabase($config['database']);
        $this->setUsername($config['username']);
        $this->setPassword($config['password']);
        $this->setOptions($config['options'] ?? []);
    }

    public function setPort(string $value): self
    {
        $this->port = empty($value) ? '' : 'port='.$value;
        return $this;
    }

    public function connect(): self
    {
        $this->establishConnection(
            new \PDO (
                self::DRIVER.':host='.$this->host.';'.$this->port.' dbname='.$this->database,
                $this->username,
                $this->password,
                $this->options
            )
        );

        return $this;
    }
}
