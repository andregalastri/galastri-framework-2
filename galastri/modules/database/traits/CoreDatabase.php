<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Trait-CoreDatabase
 */

namespace galastri\modules\database\traits;

use galastri\extensions\Exception;
use galastri\language\Message;
use galastri\modules\Tools;

trait CoreDatabase
{
    private string $configFile = PROJECT_DIR.'/app/config/database.php';

    private \PDO $pdo;
    private bool $connected = false;
    private array $bind = [];
    private array $namedResult = [];
    private array $lastResult = [];
    private string $host = '';
    private string $port = '';
    private string $database = '';
    private string $username = '';
    private string $password = '';
    private array $options = [];
    private string $location = '';
    private array $queryChain = [];

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function setPort(string $port): self
    {
        $this->port = $port;
        return $this;
    }

    public function setDatabase(string $database): self
    {
        $this->database = $database;
        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    abstract public function connect(): self;

    public function disconnect(): self
    {
        $this->pdo = null;
        $this->connected = false;

        return $this;
    }

    public function begin(): self
    {
        $this->isConnected();
        $this->pdo->beginTransaction();

        return $this;
    }

    public function cancel(): self
    {
        $this->isConnected();
        $this->pdo->rollBack();

        return $this;
    }

    public function commit(): self
    {
        $this->isConnected();
        $this->pdo->commit();

        return $this;
    }

    public function query(string $queryString, int|null|string $label = null): self
    {
        $this->isConnected();

        $this->queryChain[] = function () use ($queryString, $label) {

            $pdoTypes = [
                'string' => \PDO::PARAM_STR,
                'int' => \PDO::PARAM_INT,
                'float' => \PDO::PARAM_STR,
                'bool' => \PDO::PARAM_BOOL,
                'null' => \PDO::PARAM_NULL,
                'resource' => \PDO::PARAM_LOB,
            ];

            try {
                $pdo = $this->pdo->prepare(
                    preg_replace('/[\t\n]+/u', ' ', trim($queryString))
                );

                foreach ($this->bind as $key => &$value) {
                    $type = Tools::typeOf($value);

                    if (isset($pdoTypes[$type])) {
                        $pdo->bindParam($key, $value, $pdoTypes[$type]);

                    } elseif (empty($value) && !in_array($value, [0, 0.0, '0'], true)) {
                        $pdo->bindParam($key, $value, \PDO::PARAM_NULL);
                        $value = null;

                    } else {
                        throw new Exception(
                            Message::get('DATABASE_BIND_PARAMETER_TYPE'),
                            [
                                $type,
                                'string, int, float, bool, null e resource'
                            ]
                        );
                    }
                }
                unset($value);

                if ($pdo->execute($this->bind)) {
                    $result = $this->processResult($pdo);

                    $this->setResult($result, $label);
                }

                $this->bind = [];

            } catch (\PDOException $e) {
                throw new Exception(
                    $this->filterPdoExceptionMessage($e->getMessage()),
                    $e->getCode()
                );
            }
        };

        return $this;
    }

    public function bind(array|int|string $bind, float|int|null|string $value = null)
    {
        $this->isConnected();

        $this->queryChain[] = function () use ($bind, $value) {

            if (!is_array($bind)) {
                $bind = [$bind => $value];
            }

            foreach ($bind as $key => $value) {
                $this->bind[$key] = $value;
            }
        };

        return $this;
    }

    public function submit(): self
    {
        $this->isConnected();

        foreach (array_reverse($this->queryChain) as $key => $function) {
            $function();
            unset($this->queryChain[$key]);
        }

        return $this;
    }

    public function getResult(int|null|string $label = null): array
    {
        $this->isConnected();

        if ($label === null) {
            return $this->lastResult;
        }

        if (!isset($this->namedResult[$label])) {
            throw new Exception(
                Message::get('DATABASE_RESULT_DOESNT_EXIST'),
                [$label]
            );
        }

        return $this->namedResult[$label];
    }

    public function getLastId(int|null|string $label = null): int
    {
        $this->isConnected();

        if ($label === null) {
            return $this->lastResult['lastId'] ?? -1;
        }

        return $this->namedResult[$label]['lastId'] ?? -1;
    }

    public function hasResult(int|null|string $label = null): bool
    {
        $this->isConnected();

        if ($label === null) {
            return !empty($this->lastResult) && ($this->lastResult['found'] ?? false);
        }

        return isset($this->namedResult[$label]['found']) && $this->namedResult[$label]['found'];
    }

    private function establishConnection(\PDO $pdo): void
    {
        try {
            foreach ($this->requiredProperties as $property) {
                if ($this->$property === '') {
                    throw new Exception(
                        Message::get('DATABASE_MISSING_REQUIRED_PROPERTY'),
                        [$property]
                    );
                }
            }

            $this->pdo = $pdo;

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

            $this->connected = true;

        } catch (\PDOException $e) {
            throw new Exception(
                $this->filterPdoExceptionMessage($e->getMessage()),
                'PDO'.$e->getCode()
            );
        }
    }

    private function setResult(array $result, int|null|string $label): void
    {
        $result = [
            'label' => $label,
            'data' => $result['data'] ?? [],
            'found' => (bool)($result['found'] ?? null),
            'affectedRows' => (int)($result['affectedRows'] ?? -1),
            'lastId' => (int)$result['lastId'] ?? -1,
        ];

        if ($label === null) {
            $this->lastResult = $result;
        } else {
            $this->namedResult[$label] = $result;
        }
    }

    private function isConnected(): void
    {
        if (!$this->connected) {
            throw new Exception(
                Message::get('DATABASE_DISCONNECTED'),
            );
        }
    }

    private function getLastInsertId(): int
    {
        try {
            $lastId = $this->pdo->lastInsertId();
            return $lastId == 0 ? -1 : $lastId;
        } catch (\PDOException $e) {
            return -1;
        }
    }

    private function processResult(\PDOStatement $pdo): array
    {
        if ($pdo->rowCount() > 0) {
            while ($found = $pdo->fetch(\PDO::FETCH_ASSOC)) {
                $result['data'][] = $found;
            }

            $result['found'] = true;
        }

        $result['affectedRows'] = $pdo->rowCount();
        $result['lastId'] = $this->getLastInsertId();

        return $result;
    }

    private function resolveDatabaseConfig(?string $dbConfigName = null): array
    {
        if ($dbConfigName === null || !file_exists($this->configFile)) {
            return [];
        }

        $configData = require $this->configFile;

        if (!isset($configData[$dbConfigName])) {
            throw new Exception(
                Message::get('DATABASE_CONFIG_DOESNT_EXIST'),
                [$dbConfigName]
            );
        }

        $config = $configData[$dbConfigName];

        foreach ($this->requiredConfigKeys as $property) {
            if (!array_key_exists($property, $config)) {
                throw new Exception(
                    Message::get('DATABASE_CONFIG_MISSING_REQUIRED_PROPERTY'),
                    [$dbConfigName, $property]
                );
            }
        }

        return $config;
    }

    private function filterPdoExceptionMessage(string $message): string
    {
        $message = preg_replace('/[\t\n]+/u', ' ', trim($message));
        return str_replace(['%'], ['%%'], $message);
    }
}
