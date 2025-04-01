# PHP JWT Examples

Here are complete code samples for working with JWTs in PHP, including token creation, verification, and usage in a web application.

## 1. Basic JWT Creation and Verification

First, install the Firebase JWT library via Composer:
```bash
composer require firebase/php-jwt
```

```php
<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class JwtHandler {
    protected $secret_key;
    protected $algorithm;
    
    public function __construct() {
        $this->secret_key = "your-secret-key"; // Store this securely in production
        $this->algorithm = 'HS256';
    }
    
    public function createToken($payload) {
        $issued_at = time();
        $expiration_time = $issued_at + 3600; // Valid for 1 hour
        
        $token_payload = [
            'iat' => $issued_at,         // Issued at
            'exp' => $expiration_time,   // Expiration time
            'data' => $payload           // Your data
        ];
        
        return JWT::encode($token_payload, $this->secret_key, $this->algorithm);
    }
    
    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, $this->algorithm));
            return $decoded->data;
        } catch (Exception $e) {
            return null;
        }
    }
}

// Usage example:
$jwt = new JwtHandler();

// Create a token
$payload = ['user_id' => 123, 'username' => 'john_doe'];
$token = $jwt->createToken($payload);
echo "Generated Token: $token\n";

// Validate a token
$decoded = $jwt->validateToken($token);
if ($decoded) {
    echo "Valid Token. Data: " . print_r($decoded, true);
} else {
    echo "Invalid Token";
}
?>
```

## 2. JWT Authentication in a PHP Web Application

```php
<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header("Content-Type: application/json");

$secret_key = "your-secret-key";
$algorithm = 'HS256';

// Mock database of users
$users = [
    'john' => password_hash('password123', PASSWORD_BCRYPT),
    'jane' => password_hash('abc123', PASSWORD_BCRYPT)
];

// Login endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/login') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password required']);
        exit;
    }
    
    $username = $data['username'];
    $password = $data['password'];
    
    if (!isset($users[$username]) || !password_verify($password, $users[$username])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }
    
    // Create token
    $issued_at = time();
    $expiration_time = $issued_at + 3600; // 1 hour
    
    $payload = [
        'iat' => $issued_at,
        'exp' => $expiration_time,
        'data' => [
            'username' => $username,
            'role' => 'user' // You would typically get this from your DB
        ]
    ];
    
    $token = JWT::encode($payload, $secret_key, $algorithm);
    
    echo json_encode([
        'success' => true,
        'token' => $token,
        'expires' => $expiration_time
    ]);
    exit;
}

// Protected endpoint
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/protected') {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization header missing']);
        exit;
    }
    
    $auth_header = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $auth_header);
    
    try {
        $decoded = JWT::decode($token, new Key($secret_key, $algorithm));
        echo json_encode([
            'success' => true,
            'message' => 'Welcome to protected route',
            'user' => $decoded->data
        ]);
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token', 'details' => $e->getMessage()]);
    }
    exit;
}

// Default response for undefined routes
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
?>
```

## 3. Middleware for JWT Authentication

```php
<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class JwtMiddleware {
    private $secret_key;
    private $algorithm;
    
    public function __construct($secret_key, $algorithm = 'HS256') {
        $this->secret_key = $secret_key;
        $this->algorithm = $algorithm;
    }
    
    public function __invoke($request, $handler) {
        // Skip middleware for login route
        if ($request->getUri()->getPath() === '/login') {
            return $handler->handle($request);
        }
        
        $auth_header = $request->getHeaderLine('Authorization');
        
        if (empty($auth_header)) {
            return new Response(401, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Authorization header missing']));
        }
        
        $token = str_replace('Bearer ', '', $auth_header);
        
        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, $this->algorithm));
            // Add decoded data to request attributes
            $request = $request->withAttribute('jwt_data', $decoded->data);
            return $handler->handle($request);
        } catch (Exception $e) {
            return new Response(401, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Invalid token', 'details' => $e->getMessage()]));
        }
    }
}

// Usage with Slim Framework example:
$app = new \Slim\App();

$jwtMiddleware = new JwtMiddleware('your-secret-key');

// Apply middleware to all routes except login
$app->add($jwtMiddleware);

// Login route
$app->post('/login', function ($request, $response) {
    $data = $request->getParsedBody();
    // Validate credentials and return token
});

// Protected route
$app->get('/protected', function ($request, $response) {
    $jwt_data = $request->getAttribute('jwt_data');
    return $response->withJson(['message' => 'Protected data', 'user' => $jwt_data]);
});

$app->run();
?>
```

## Security Best Practices for PHP JWT Implementation:

1. **Use strong secret keys**: Generate long, random strings (at least 32 characters)
2. **Store secrets securely**: Use environment variables or secure vaults
3. **Set reasonable expiration times**: 15 minutes to 1 hour for access tokens
4. **Use HTTPS**: Always to prevent token interception
5. **Implement token refresh**: For better user experience without compromising security
6. **Validate all token claims**: Especially `exp` (expiration) and `iss` (issuer) when relevant
7. **Consider using OpenID Connect**: For more complex authentication scenarios

For production applications, you might want to consider established PHP authentication libraries like:
- Laravel Passport (for Laravel applications)
- OAuth2 Server (for implementing OAuth2)
- Symfony Security Component
