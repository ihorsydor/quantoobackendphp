<?php

declare(strict_types=1);

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
header("Access-Control-Allow-Methods: DELETE, PUT, OPTIONS");
header("Content-type: multipart/form-data; charset=UTF-8");

spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");



if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "DELETE") {
   
    echo "Obsługuje metode Delete"; 
}

$parts = explode("/", $_SERVER["REQUEST_URI"]);

if ($parts[1] === "author") {
    
    $id = $parts[2] ?? null;

    $database = new Database("localhost", "quantoo", "quantoo", "test1234");

    $authorGateway = new AuthorGateway($database);
    $authorController = new AuthorController($authorGateway);

    $authorController->processRequest($_SERVER["REQUEST_METHOD"], $id);
} elseif ($parts[1] === "book") {
  
    $id = $parts[2] ?? null;
     
    $imageToDelete = isset($parts[3]) ? $parts[3] : null;

    $database = new Database("localhost", "quantoo", "quantoo", "test1234");

    $bookGateway = new BookGateway($database);
    
    $bookController = new BookController($bookGateway);

   

    $bookController->processRequest($_SERVER["REQUEST_METHOD"], $id, $imageToDelete);
    // $bookController->processRequest($_SERVER["REQUEST_METHOD"], $id, $imageToDelete);

} else {
    
    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found"]);
}