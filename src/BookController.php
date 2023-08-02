<?php

class BookController
{
    
    public function __construct(private BookGateway $gateway)
    {
        
    }

    private function processOptionsRequest(): void
{
   header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
header("Access-Control-Allow-Methods: DELETE, PUT, OPTIONS");
header("Content-type: application/json; charset=UTF-8");
    http_response_code(200);
}
    
    public function processRequest(string $method, ?string $id, ?string $imageToDelete = null): void
    {
        if ($method === "OPTIONS") {
            $this->processOptionsRequest();}
        elseif ($id) {
            
            $this->processResourceRequest($method, $id, $imageToDelete );
        } else {
            
            $this->processCollectionRequest($method);
            
        }
    }
    

    private function processResourceRequest(string $method, string $id, ?string $imageToDelete = null): void
    {
        $book = $this->gateway->get($id);
        
        if ( ! $book) {
            http_response_code(404);
            echo json_encode(["message" => "Book not found"]);
            return;
        }
        
        switch ($method) {
            case "GET":
                echo json_encode($book);
                break;
                
            case "POST":
                $name = $_POST['name'];
                $publishing = $_POST['publishing'];
                $siteNumber = $_POST['siteNumber'];
                $author = $_POST['author'];
                
    
                $data = array(
                    "id" => $id,
                    "name" => $name,
                    "publishing" => $publishing,
                    "siteNumber" => $siteNumber,
                    "author" => $author
                );

                if(isset($_FILES['image'])){
                    var_dump('tu jest sprawdza czy obrazek isnieje');
                    $currantName = $_POST['currantName'];
                    $file = $_FILES['image'];
                    $identifier = uniqid();
                    $originalFileName = $file['name'];
                    $newFileName = $identifier . '_' . $originalFileName;
                    $targetPath = 'images/' . $newFileName;
                    if (!is_dir('./images')) {
                        mkdir('./images', 0777, true);
                    }

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        echo "Plik został zapisany.";
                    } else {
                        http_response_code(500);
                        echo json_encode(["errors" => "Wystąpił błąd podczas zapisywania pliku."]);
                        break;
                    }
                
                    $destination = 'images';   
                    $imagePath = 'http://localhost:8000/' . $destination . '/' . $newFileName;

                    $imageData = array(
                    "id" => $id,
                    "file" => $file,
                    "imageName" => $originalFileName,
                    "newFileName" => $identifier,
                    "destination" => $destination,
                    "imagePath" => $imagePath
                    );
                    
                    $this->gateway->deleteImage($currantName);

                    $isUpdatedFile = $this->gateway->updateFile($imageData);
    
                    if ($isUpdatedFile) {
                        echo json_encode(["message" => "Book updated"]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["errors" => "Błąd podczas aktualizacji książki."]);
                    }
                
                };
    $isUpdated = $this->gateway->update($data);
    
                if ($isUpdated) {
                    echo json_encode(["message" => "Book updated"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["errors" => "Błąd podczas aktualizacji książki."]);
                }
              
            break;
            case "DELETE":
                $this->gateway->deleteImage($imageToDelete);
                $this->gateway->delete($id);
    
                echo json_encode([
                    "message" => "Book $id deleted",
                ]);
                break;
                
            default:
                http_response_code(405);
                header("Allow: GET, PUT, DELETE");
        }
    }
    
    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getAll());
                break;
                
            case "POST":
                $postData = file_get_contents("php://input");
                var_dump($postData);
                    $name = $_POST['name'];
                    $publishing = $_POST['publishing'];
                    $siteNumber = $_POST['siteNumber'];
                    $author = $_POST['author'];
                
                   
                    $file = $_FILES['image'];
                    $identifier = uniqid();
                    $originalFileName = $file['name'];
                    $newFileName = $identifier . '_' . $originalFileName;
                    $targetPath = 'images/' . $newFileName;
                    if (!is_dir('./images')) {
                        mkdir('./images', 0777, true);
                    }

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        echo "Plik został zapisany.";
                    } else {
                        http_response_code(500);
                        echo json_encode(["errors" => "Wystąpił błąd podczas zapisywania pliku."]);
                        break;
                    }
                
                   
                    $destination = 'images';   
                    $imagePath = 'http://localhost:8000/' . $destination . '/' . $newFileName;

                    // Tworzymy tablicę asocjacyjną z danymi
                    $data = array(
                        "name" => $name,
                        "publishing" => $publishing,
                        "siteNumber" => $siteNumber,
                        "author" => $author,
                        "file" => $file,
                        "imageName" => $originalFileName,
                        "newFileName" => $identifier,
                        "destination" => $destination,
                        "imagePath" => $imagePath
                    );
                
                    
                    $errors = $this->getValidationErrors($data);
                
                    if (!empty($errors)) {
                        http_response_code(422);
                        echo json_encode(["errors" => $errors]);
                        break;
                    }
                
                    // Przekazujemy dane do metody create() w klasie gateway, aby dodać nowy rekord do bazy danych
                    $id = $this->gateway->create($data);
                
                    http_response_code(201);
                    echo json_encode([
                        "message" => "Book created",
                        "id" => $id
                    ]);
                    break;
            
            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }
    
    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];
        
        if ($is_new && empty($data["name"])) {
            $errors[] = "name is required";
        }
        
        if (array_key_exists("size", $data)) {
            if (filter_var($data["size"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "size must be an integer";
            }
        }
        
        return $errors;
    }
}