<?php

namespace galastri\modules\database;

use galastri\extensions\Exception;
use galastri\language\Message;

class SqliteDatabase
{
    use traits\CoreDatabase;

    const DRIVER = 'sqlite';
    
    private array $requiredConfigKeys = ['location'];
    private array $requiredProperties = ['location'];

    public function __construct(?string $dbConfigName = null)
    {
        $config = $this->resolveDatabaseConfig($dbConfigName);

        if (empty($config)) {
            return;
        }

        $this->setLocation($config['location']);
        $this->setOptions($config['options'] ?? []);
    }

    public function connect(): self
    {
        $this->establishConnection(
            new \PDO(
                self::DRIVER.':'.$this->location,
                null,
                null,
                $this->options
            )
        );

        return $this;
    }
}
