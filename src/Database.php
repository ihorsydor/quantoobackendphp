<?php

class Database
{
    public function __construct(private string $host,
                                private string $name,
                                private string $user,
                                private string $password)
    {}
        
    public function getConnection(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";
        
        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]);

        $this->createAuthorTableIfNotExists($connection);
        $this->createBookTableIfNotExists($connection);

        return $connection;

    }

    private function createAuthorTableIfNotExists(PDO $connection)
    {
        $sql = "CREATE TABLE IF NOT EXISTS author (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    country VARCHAR(255) NOT NULL
                )";

        $connection->exec($sql);
    }

    private function createBookTableIfNotExists(PDO $connection)
    {
        $sql = "CREATE TABLE IF NOT EXISTS book (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    publishing VARCHAR(255) NOT NULL,
                    siteNumber INT NOT NULL,
                    author INT NOT NULL,
                    file VARCHAR(255) NOT NULL,
                    imageName VARCHAR(255) NOT NULL,
                    newFileName VARCHAR(255) NOT NULL,
                    destination VARCHAR(255) NOT NULL,
                    imagePath VARCHAR(255) NOT NULL,
                    FOREIGN KEY (author) REFERENCES author(id) ON DELETE CASCADE
                )";

        $connection->exec($sql);
    }

}