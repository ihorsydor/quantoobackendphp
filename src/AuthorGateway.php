<?php

class AuthorGateway
{
    private PDO $conn;
    
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    
    public function getAll(): array
    {
        $sql = "SELECT *
                FROM author";
                
        $stmt = $this->conn->query($sql);
        
        $data = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
          
            
            $data[] = $row;
        }
        
        return $data;
    }
    
    public function create(array $data): string
    {   
        $sql = "INSERT INTO author (name, country)
                VALUES (:name, :country)";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
        $stmt->bindValue(":country", $data["country"], PDO::PARAM_STR);
        
       

        if ($stmt->execute()) {
            // Debug: Wyświetlenie ID ostatnio dodanego rekordu
            echo "Ostatni wstawiony ID: " . $this->conn->lastInsertId();
        } else {
            // Debug: Wyświetlenie błędów zapytania
            echo "Błąd zapytania: " . implode(", ", $stmt->errorInfo());
        }
        
        return $this->conn->lastInsertId();
    }
    
    public function get(string $id): array | false
    {
        $sql = "SELECT *
                FROM author
                WHERE id = :id";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data !== false) {
           
        }
        
        return $data;
    }
    
    public function update(array $current, array $new): int
    {
        $sql = "UPDATE author
                SET name = :name, country = :country
                WHERE id = :id";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":name", $new["name"] ?? $current["name"], PDO::PARAM_STR);
        $stmt->bindValue(":country", $new["country"] ?? $current["country"], PDO::PARAM_STR);

        
        $stmt->bindValue(":id", $current["id"], PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }
    
    public function delete(string $id): int
    {
        $sql = "DELETE FROM author
                WHERE id = :id";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }
 

    public function search(string $queryString): string
    {
        $sql = "SELECT author.name AS author_name, book.name AS book_name
                FROM author
                LEFT JOIN book ON author.id = book.author
                WHERE author.name LIKE :searchString OR book.name LIKE :searchStringForBooks";
    
        $stmt = $this->conn->prepare($sql);
    
        $searchString = '%' . $queryString . '%';
        $stmt->bindValue(":searchString", $searchString, PDO::PARAM_STR);
    
        $searchStringForBooks = '%' . $queryString . '%';
        $stmt->bindValue(":searchStringForBooks", $searchStringForBooks, PDO::PARAM_STR);
    
        $stmt->execute();
    
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $authors = [];
        foreach ($results as $row) {
            $authorName = $row['author_name'];
            $bookName = $row['book_name'];
    
            if (!isset($authors[$authorName])) {
                $authors[$authorName] = [
                    'name' => $authorName,
                    'books' => [],
                ];
            }
    
            if ($bookName !== null) {
                $authors[$authorName]['books'][] = ['name' => $bookName];
            }
        }
    
        $authorObjects = array_values($authors);
        
        $jsonData = json_encode($authorObjects);
    
        header('Content-Type: application/json; charset=UTF-8');
        echo $jsonData;
    
        return $jsonData;
    }
    
    
}