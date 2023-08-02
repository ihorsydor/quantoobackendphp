<?php

class AuthorController
{
    public function __construct(private AuthorGateway $gateway)
    {
    }
    private function processOptionsRequest(): void
{
    header("Allow: GET, PUT, POST, PATCH, DELETE, OPTIONS");
    http_response_code(200);
}
    
    public function processRequest(string $method, ?string $id): void
    {
        if ($method === "OPTIONS") {
            $this->processOptionsRequest();}
        elseif ($id) {
            if (strpos($id, "search") === 0){
                $this->processSearchRequest($method, $id);
            }
            else{
               $this->processResourceRequest($method, $id); 
            }
            
            
        } else {
            
            $this->processCollectionRequest($method);
            
        }
    }
    
    private function processResourceRequest(string $method, string $id): void
    {
        $author = $this->gateway->get($id);
        
        if ( ! $author) {
            http_response_code(404);
            echo json_encode(["message" => "Author not found"]);
            return;
        }
        
        switch ($method) {
            case "GET":
                echo json_encode($author);
                break;
                
            case "PUT":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                
                $errors = $this->getValidationErrors($data, false);
                
                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }
                
                $rows = $this->gateway->update($author, $data);
                
                echo json_encode([
                    "message" => "Author $id updated",
                    "rows" => $rows
                ]);
                break;
                
            case "DELETE":
                $rows = $this->gateway->delete($id);
                
                echo json_encode([
                    "message" => "Author $id deleted",
                    "rows" => $rows
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
                $data = (array) json_decode(file_get_contents("php://input"), true);
                
                var_dump($data);

                $errors = $this->getValidationErrors($data);
                
                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }
                
                $id = $this->gateway->create($data);
                
                http_response_code(201);
                echo json_encode([
                    "message" => "Author created",
                    "id" => $id
                ]);
                break;
            
            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }
    
    private function processSearchRequest(string $method, string $id): void
    {
        
        $queryString = str_replace("search?query=", "", $id);
        
        $this->gateway->search($queryString);

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