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

        $connection = new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]);

      
        $this->checkAndCreateTables($connection);

        return $connection;
    }

    private function checkAndCreateTables(PDO $connection)
    {
        $this->createAuthorTableIfNotExists($connection);
        $this->createBookTableIfNotExists($connection);

        
        $this->checkAndCreateAuthorColumns($connection);
        $this->checkAndCreateBookColumns($connection);
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

    private function checkAndCreateAuthorColumns(PDO $connection)
    {
        
        $expectedColumns = [
            'id',
            'name',
            'country'
        ];

        $sql = "SHOW COLUMNS FROM author";
        $stmt = $connection->query($sql);
        $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);

      
        $missingColumns = array_diff($expectedColumns, $existingColumns);

        if (!empty($missingColumns)) {
            foreach ($missingColumns as $column) {
                $this->createAuthorColumn($connection, $column);
            }
        }
    }

    private function createAuthorColumn(PDO $connection, string $columnName)
    {
      
        $columnDefinition = match ($columnName) {
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(255) NOT NULL',
            'country' => 'VARCHAR(255) NOT NULL',
        };

        $sql = "ALTER TABLE author ADD $columnName $columnDefinition";
        $connection->exec($sql);
    }

    private function checkAndCreateBookColumns(PDO $connection)
    {
       
        $expectedColumns = [
            'id',
            'name',
            'publishing',
            'siteNumber',
            'author',
            'file',
            'imageName',
            'newFileName',
            'destination',
            'imagePath',
        ];

        $sql = "SHOW COLUMNS FROM book";
        $stmt = $connection->query($sql);
        $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        
        $missingColumns = array_diff($expectedColumns, $existingColumns);

        if (!empty($missingColumns)) {
            foreach ($missingColumns as $column) {
                $this->createBookColumn($connection, $column);
            }
        }
    }

    private function createBookColumn(PDO $connection, string $columnName)
    {
       
        $columnDefinition = match ($columnName) {
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(255) NOT NULL',
            'publishing' => 'VARCHAR(255) NOT NULL',
            'siteNumber' => 'INT NOT NULL',
            'author' => 'INT NOT NULL',
            'file' => 'VARCHAR(255) NOT NULL',
            'imageName' => 'VARCHAR(255) NOT NULL',
            'newFileName' => 'VARCHAR(255) NOT NULL',
            'destination' => 'VARCHAR(255) NOT NULL',
            'imagePath' => 'VARCHAR(255) NOT NULL',
        };

        $sql = "ALTER TABLE book ADD $columnName $columnDefinition";
        $connection->exec($sql);
    }
}
