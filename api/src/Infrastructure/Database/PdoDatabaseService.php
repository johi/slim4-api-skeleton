<?php

namespace App\Infrastructure\Database;

use App\Application\Configuration\AppConfiguration;
use PDO;
use PDOException;
use phpDocumentor\Reflection\Types\Null_;

class PdoDatabaseService implements DatabaseService
{
    private $driver;
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;

    private $connection;

    public function __construct($environment = 'development')
    {
        $databaseSettings = AppConfiguration::getKey('database');
        if (!isset($databaseSettings[$environment]['driver'])) {
            throw new DatabaseConnectionException('Missing driver');
        }
        $this->driver = $databaseSettings[$environment]['driver'];
        if (!isset($databaseSettings[$environment]['host'])) {
            throw new DatabaseConnectionException('Missing host');
        }
        $this->host = $databaseSettings[$environment]['host'];
        if (!isset($databaseSettings[$environment]['port'])) {
            throw new DatabaseConnectionException('Missing port');
        }
        $this->port = $databaseSettings[$environment]['port'];
        if (!isset($databaseSettings[$environment]['database'])) {
            throw new DatabaseConnectionException('Missing database');
        }
        $this->database = $databaseSettings[$environment]['database'];
        if (!isset($databaseSettings[$environment]['username'])) {
            throw new DatabaseConnectionException('Missing username');
        }
        $this->username = $databaseSettings[$environment]['username'];
        if (!isset($databaseSettings[$environment]['password'])) {
            throw new DatabaseConnectionException('Missing password');
        }
        $this->password = $databaseSettings[$environment]['password'];
    }

    public function getConnection()
    {
        $dsn = sprintf("%s:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $this->driver,
            $this->host,
            $this->port,
            $this->database,
            $this->username,
            $this->password);
        $this->connection = null;

        try {
            $this->connection = new PDO($dsn);
            $this->connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            //@todo we should use sql SET TIMEZONE = 'Europe/Copenhagen' for example from a user collected input,
            // it will last for the current connection session only, the way it works is that every user gets their
            // own connection per request, unless some sort of connection pooling is implemented, that reuses connections
            // that means that any date collected will have the same offset,
            // can only be an advantage considering cronjobs that act on date for example
        } catch (PDOException $exception) {
            throw new DatabaseConnectionException("Connection failed: " . $exception->getMessage());
        }

        return $this->connection;
    }

    public function fetchUuid(): string
    {
        $statement = $this->connection->query('SELECT uuid_generate_v4()');
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row['uuid_generate_v4'];
    }

    public function fetchTimestamp(int $addSeconds = null): string
    {
        $query = 'SELECT NOW() as timestamp';
        if (!is_null($addSeconds)) {
            $query = sprintf('SELECT (NOW() + interval \'%d\' second) as timestamp', $addSeconds);
        }
        $statement = $this->connection->query($query);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row['timestamp'];
    }

    public function startTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commitTransaction(): void
    {
        $this->connection->commit();
    }

    public function rollbackTransaction(): void
    {
        $this->connection->rollBack();
    }
}