<?php
//header('Access-Control-Allow-Origin: *');
//error_log("Error log: " . ini_get("error_log"));

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Tuupola\Middleware\CorsMiddleware;
require_once __DIR__ . '/../vendor/autoload.php';


$app = AppFactory::create();

// Add CORS middleware
$corsMiddleware = new CorsMiddleware([
    "origin" => ["*"],
    "methods" => ["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS"],
    "headers.allow" => ["Content-Type", "Authorization"],
    "headers.expose" => [],
    "credentials" => true,
    "cache" => 0,
]);

$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$app->add($corsMiddleware);


$app->post('/login', function (Request $request, Response $response) {
    // Get the request data
    $body = (string)$request->getBody();
    $data = json_decode($body, true);

// Validate the input
if (!isset($data['username']) || !isset($data['password'])) {
    $response->getBody()->write(json_encode(['error' => 'Invalid input']));
    return $response->withStatus(400);
}

// Authenticate the user
$database = new \Ogunerkutay\ChatBackend\Database();
$connection = $database->getConnection();

$query = 'SELECT * FROM users WHERE username = :username';
$stmt = $connection->prepare($query);
$stmt->bindParam(':username', $data['username']);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || !password_verify($data['password'], $user['password'])) {
    $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
    return $response->withStatus(401);
}

$response->getBody()->write(json_encode(['success' => 'User authenticated', 'user_id' => $user['id']]));
return $response->withStatus(200);

});





$app->post('/register', function (Request $request, Response $response) {
    // Get the request data
    $body = (string)$request->getBody();
    $data = json_decode($body, true);
    
//error_log("Request Headers: " . json_encode($request->getHeaders()));
//error_log("Request Body: " . (string)$request->getBody());
//error_log("Data: " . json_encode($data));

// Validate the input
if (!isset($data['username']) || !isset($data['password'])) {
    $response->getBody()->write(json_encode(['error' => 'Invalid input']));
    return $response->withStatus(400);
}

// Create a new user
$database = new \Ogunerkutay\ChatBackend\Database();
$connection = $database->getConnection();

// Check if the user already exists
$query = 'SELECT * FROM users WHERE username = :username';
$stmt = $connection->prepare($query);
$stmt->bindParam(':username', $data['username']);
$stmt->execute();

if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    $response->getBody()->write(json_encode(['error' => 'User already exists']));
    return $response->withStatus(409);
}

// Add the new user
$query = 'INSERT INTO users (username, password) VALUES (:username, :password)';
$stmt = $connection->prepare($query);
$stmt->bindParam(':username', $data['username']);
$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
$stmt->bindParam(':password', $hashedPassword);
$stmt->execute();

$response->getBody()->write(json_encode(['success' => 'User registered']));
return $response->withStatus(201);

});


$app->post('/messages', function (Request $request, Response $response) {
    // Get the request data
//$data = $request->getParsedBody();
$body = (string)$request->getBody();
$data = json_decode($body, true);


// Validate the input
if (!isset($data['user_id']) || !isset($data['content'])) {
    $response->getBody()->write(json_encode(['error' => 'Invalid input']));
    return $response->withStatus(400);
}

// Save the message
$database = new \Ogunerkutay\ChatBackend\Database();
$connection = $database->getConnection();

$query = 'INSERT INTO messages (user_id, content, timestamp) VALUES (:user_id, :content, :timestamp)';
$stmt = $connection->prepare($query);
$stmt->bindParam(':user_id', $data['user_id']);
$stmt->bindParam(':content', $data['content']);
$stmt->bindValue(':timestamp', time());
$stmt->execute();

$response->getBody()->write(json_encode(['success' => 'Message sent']));
return $response->withStatus(201);

});


$app->get('/messages', function (Request $request, Response $response) {
    // Fetch messages
$database = new \Ogunerkutay\ChatBackend\Database();
$connection = $database->getConnection();

$query = 'SELECT messages.id, messages.content, messages.timestamp, users.username as author FROM messages INNER JOIN users ON messages.user_id = users.id ORDER BY messages.timestamp ASC';
$stmt = $connection->prepare($query);
$stmt->execute();

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response->getBody()->write(json_encode(['messages' => $messages]));
return $response->withStatus(200);

});




$app->run();
