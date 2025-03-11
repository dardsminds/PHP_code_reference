
Below is an example of how to implement OAuth 2.0 using the **League OAuth2 Server** package in a plain PHP application.

---

### Step 1: Install the League OAuth2 Server
Install the package using Composer:
```bash
composer require league/oauth2-server
```

---

### Step 2: Set Up the OAuth 2.0 Server

#### 1. Create a Database for OAuth2
Create a database and tables for OAuth2. Here’s an example schema:

```sql
CREATE TABLE oauth_clients (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    secret VARCHAR(100) NOT NULL,
    redirect TEXT NOT NULL,
    personal_access_client BOOLEAN NOT NULL,
    password_client BOOLEAN NOT NULL,
    revoked BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE oauth_access_tokens (
    id SERIAL PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    client_id BIGINT UNSIGNED NOT NULL,
    scopes TEXT,
    revoked BOOLEAN NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE oauth_refresh_tokens (
    id SERIAL PRIMARY KEY,
    access_token_id BIGINT UNSIGNED NOT NULL,
    revoked BOOLEAN NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

#### 2. Implement the OAuth2 Server

Here’s an example of how to set up the OAuth2 server in plain PHP:

```php
<?php

require 'vendor/autoload.php';

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

// Create a Slim app
$app = AppFactory::create();

// Database connection (replace with your credentials)
$pdo = new PDO('mysql:host=localhost;dbname=oauth2', 'root', '');

// Repositories
$clientRepository = new ClientRepository($pdo);
$scopeRepository = new ScopeRepository($pdo);
$accessTokenRepository = new AccessTokenRepository($pdo);
$userRepository = new UserRepository($pdo);
$refreshTokenRepository = new RefreshTokenRepository($pdo);

// Setup the authorization server
$server = new AuthorizationServer(
    $clientRepository,
    $accessTokenRepository,
    $scopeRepository,
    new CryptKey('file://path/to/private.key', null, false), // Path to private key
    'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+tws=' // Encryption key
);

// Enable the password grant
$grant = new PasswordGrant($userRepository, $refreshTokenRepository);
$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // Refresh token expiry
$server->enableGrantType($grant, new \DateInterval('PT1H')); // Access token expiry

// OAuth2 token endpoint
$app->post('/oauth/token', function (ServerRequestInterface $request, ResponseInterface $response) use ($server) {
    try {
        return $server->respondToAccessTokenRequest($request, $response);
    } catch (OAuthServerException $exception) {
        return $exception->generateHttpResponse($response);
    } catch (\Exception $exception) {
        return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
            ->generateHttpResponse($response);
    }
});

// Protected resource endpoint
$app->get('/api/user', function (ServerRequestInterface $request, ResponseInterface $response) use ($pdo) {
    $resourceServer = new ResourceServer(
        $accessTokenRepository,
        new CryptKey('file://path/to/public.key', null, false) // Path to public key
    );

    try {
        $request = $resourceServer->validateAuthenticatedRequest($request);

        // Fetch user details from the database
        $userId = $request->getAttribute('oauth_user_id');
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (OAuthServerException $exception) {
        return $exception->generateHttpResponse($response);
    }
});

// Run the app
$app->run();
```

---

### Step 3: Implement Repositories

You need to implement the following repository interfaces:

1. **ClientRepository**:
   ```php
   use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
   use League\OAuth2\Server\Entities\ClientEntityInterface;

   class ClientRepository implements ClientRepositoryInterface
   {
       private $pdo;

       public function __construct(PDO $pdo)
       {
           $this->pdo = $pdo;
       }

       public function getClientEntity($clientIdentifier): ClientEntityInterface
       {
           $stmt = $this->pdo->prepare('SELECT * FROM oauth_clients WHERE id = ?');
           $stmt->execute([$clientIdentifier]);
           $client = $stmt->fetch(PDO::FETCH_ASSOC);

           if ($client) {
               return new ClientEntity($client);
           }

           return null;
       }

       public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
       {
           $client = $this->getClientEntity($clientIdentifier);
           return $client && $client->getSecret() === $clientSecret;
       }
   }
   ```

2. **UserRepository**:
   ```php
   use League\OAuth2\Server\Repositories\UserRepositoryInterface;
   use League\OAuth2\Server\Entities\UserEntityInterface;

   class UserRepository implements UserRepositoryInterface
   {
       private $pdo;

       public function __construct(PDO $pdo)
       {
           $this->pdo = $pdo;
       }

       public function getUserEntityByUserCredentials($username, $password, $grantType): ?UserEntityInterface
       {
           $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
           $stmt->execute([$username]);
           $user = $stmt->fetch(PDO::FETCH_ASSOC);

           if ($user && password_verify($password, $user['password'])) {
               return new UserEntity($user['id']);
           }

           return null;
       }
   }
   ```

---

### Step 4: Generate Encryption Keys

Generate a private and public key pair for token signing:
```bash
openssl genrsa -out private.key 2048
openssl rsa -in private.key -pubout -out public.key
```

---

### Step 5: Test the OAuth2 Server

1. **Request an Access Token**:
   ```bash
   curl -X POST http://localhost:8080/oauth/token \
        -d "grant_type=password" \
        -d "client_id=1" \
        -d "client_secret=your_client_secret" \
        -d "username=user@example.com" \
        -d "password=password"
   ```

2. **Access a Protected Resource**:
   ```bash
   curl -X GET http://localhost:8080/api/user \
        -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
   ```

---

### Notes:
- Replace placeholders (e.g., database credentials, key paths) with your actual values.
- Use HTTPS in production to secure communication.
- The **League OAuth2 Server** package is highly customizable and supports all OAuth2 grant types (e.g., authorization code, client credentials, refresh token).
