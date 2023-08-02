<?php

class BookGateway
{
    private PDO $conn;
    
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    
    public function getAll(): array
    {
        $sql = "SELECT book.*, author.name AS author_name, author.country AS author_country
        FROM book
        JOIN author ON book.author = author.id";
                
        $stmt = $this->conn->query($sql);
        
        $data = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
          
            
            $data[] = $row;
        }
        
        return $data;
    }
    
    public function create(array $data): string
{
    
    $image = $data['file']['name']; 
    
    $sql = "INSERT INTO book (name, publishing, siteNumber, author, imageName, newFileName, destination, imagePath)
            VALUES (:name, :publishing, :siteNumber, :author, :imageName, :newFileName, :destination, :imagePath)";
            
    $stmt = $this->conn->prepare($sql);
    
    $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
    $stmt->bindValue(":publishing", $data["publishing"], PDO::PARAM_STR);
    $stmt->bindValue(":siteNumber", $data["siteNumber"], PDO::PARAM_INT);
    $stmt->bindValue(":author", $data["author"], PDO::PARAM_INT);
    $stmt->bindValue(":imageName", $data["imageName"], PDO::PARAM_STR);
    $stmt->bindValue(":newFileName", $data["newFileName"], PDO::PARAM_STR);
    $stmt->bindValue(":destination", $data["destination"], PDO::PARAM_STR);
    $stmt->bindValue(":imagePath", $data["imagePath"], PDO::PARAM_STR);

    if ($stmt->execute()) {
        
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
                FROM book
                WHERE id = :id";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data !== false) {
           
        }
        
        return $data;
    }
    
    public function update(array $data): bool
{
    $sql = "UPDATE book
            SET name = :name,
                publishing = :publishing,
                siteNumber = :siteNumber,
                author = :author
            WHERE id = :id";

    $stmt = $this->conn->prepare($sql);

    var_dump("Jestem tutaj bookgateway update: ", $data);

    $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
    $stmt->bindValue(":publishing", $data["publishing"], PDO::PARAM_STR);
    $stmt->bindValue(":siteNumber", $data["siteNumber"], PDO::PARAM_INT);
    $stmt->bindValue(":author", $data["author"], PDO::PARAM_INT);
    $stmt->bindValue(":id", $data["id"], PDO::PARAM_INT);

    return $stmt->execute();
}

   public function updateFile(array $imageData): bool
{
    var_dump('co przekazuje w imagedata', $imageData);
    $sql = "UPDATE book
    SET imageName = :imageName,
    newFileName = :newFileName,
    destination = :destination,
    imagePath = :imagePath
    WHERE id = :id";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindValue(":imageName", $imageData["imageName"], PDO::PARAM_STR);
    $stmt->bindValue(":newFileName", $imageData["newFileName"], PDO::PARAM_STR);
    $stmt->bindValue(":destination", $imageData["destination"], PDO::PARAM_STR);
    $stmt->bindValue(":imagePath", $imageData["imagePath"], PDO::PARAM_STR);
    $stmt->bindValue(":id", $imageData["id"], PDO::PARAM_INT);
    
    return $stmt->execute();

}
    
    public function delete(string $id): int
    {
        $sql = "DELETE FROM book
                WHERE id = :id";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }

    public function deleteImage(string $fileToDelete): bool
{
    $directory = 'images/';
    $files = scandir($directory);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $parts = explode('_', $file);
        $identifier = $parts[0];

        if ($identifier === $fileToDelete) {
            $imagePath = $directory . $file;
            if (file_exists($imagePath)) {
                unlink($imagePath);
                return true;
            }
        }
    }

    return false;
}
}