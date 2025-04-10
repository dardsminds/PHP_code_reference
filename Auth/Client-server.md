# Complete OAuth 2.0 Client and Server Example in PHP

Here's a full example demonstrating both an OAuth 2.0 server and client implementation using the `league/oauth2-server` and `league/oauth2-client` packages.

## System Architecture

```
Client App (http://client.app) 
  │
  ├─ Requests authorization
  │
  └─ OAuth Server (http://auth.server)
       ├─ /authorize
       ├─ /token
       └─ /resource
```

## Part 1: OAuth 2.0 Server Implementation

### 1. Install Dependencies

```bash
composer require league/oauth2-server laminas/laminas-diactoros
```

### 2. Server Code (`server.php`)

```php
<?php
require 'vendor/autoload.php';

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

// Setup repositories (simplified implementations)
$clientRepository = new class implements ClientRepositoryInterface {
    public function getClientEntity($clientIdentifier) {
        $client = new class extends \League\OAuth2\Server\Entities\ClientEntityInterface {
            public function getIdentifier() { return 'testclient'; }
            public function getName() { return 'Test Client'; }
            public function getRedirectUri() { return 'http://client.app/callback'; }
            public function isConfidential() { return true; }
        };
        return $client->getIdentifier() === $clientIdentifier ? $client : null;
    }
    
    public function validateClient($clientIdentifier, $clientSecret, $grantType) {
        return $clientIdentifier === 'testclient' && $clientSecret === 'testsecret';
    }
};

// Create authorization server
$server = new AuthorizationServer(
    $clientRepository,
    new AccessTokenRepository(),
    new ScopeRepository(),
    'file://' . __DIR__ . '/private.key',
    'file://' . __DIR__ . '/public.key'
);

// Enable auth code grant
$server->enableGrantType(
    new AuthCodeGrant(
        new AuthCodeRepository(),
        new RefreshTokenRepository(),
        new \DateInterval('PT10M')
    ),
    new \DateInterval('PT1H')
);

// Create Slim app
$app = AppFactory::create();

// Authorization endpoint
$app->get('/authorize', function (ServerRequestInterface $request, ResponseInterface $response) use ($server) {
    try {
        $authRequest = $server->validateAuthorizationRequest($request);
        
        // Normally you'd authenticate the user here
        $authRequest->setUser(new UserEntity(1));
        $authRequest->setAuthorizationApproved(true);
        
        return $server->completeAuthorizationRequest($authRequest, $response);
    } catch (\Exception $e) {
        $response->getBody()->write($e->getMessage());
        return $response->withStatus(500);
    }
});

// Token endpoint
$app->post('/token', function (ServerRequestInterface $request, ResponseInterface $response) use ($server) {
    try {
        return $server->respondToAccessTokenRequest($request, $response);
    } catch (\Exception $e) {
        $response->getBody()->write($e->getMessage());
        return $response->withStatus(500);
    }
});

// Protected resource
$app->get('/resource', function (ServerRequestInterface $request, ResponseInterface $response) {
    // Normally you'd validate the token here
    $response->getBody()->write(json_encode([
        'user_id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
```

## Part 2: OAuth 2.0 Client Implementation

### 1. Install Dependencies

```bash
composer require league/oauth2-client
```

### 2. Client Code (`client.php`)

```php
<?php
require 'vendor/autoload.php';

use League\OAuth2\Client\Provider\GenericProvider;

session_start();

$provider = new GenericProvider([
    'clientId'                => 'testclient',
    'clientSecret'            => 'testsecret',
    'redirectUri'             => 'http://client.app/callback',
    'urlAuthorize'            => 'http://auth.server/authorize',
    'urlAccessToken'          => 'http://auth.server/token',
    'urlResourceOwnerDetails' => 'http://auth.server/resource'
]);

// Step 1: Authorization
if (!isset($_GET['code'])) {
    $authorizationUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authorizationUrl);
    exit;

// Step 2: Check state and get token
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
} else {
    try {
        // Get access token
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // Step 3: Use token to access protected resource
        $request = $provider->getAuthenticatedRequest(
            'GET',
            'http://auth.server/resource',
            $accessToken
        );

        $httpClient = new \GuzzleHttp\Client();
        $response = $httpClient->send($request);
        
        echo "Access Token: " . $accessToken->getToken() . "<br>";
        echo "Resource Response: " . $response->getBody();
        
    } catch (\Exception $e) {
        exit('Error: ' . $e->getMessage());
    }
}
```

## Part 3: Repository Implementations

Create these basic repository implementations for the server:

### `AccessTokenRepository.php`

```php
<?php
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function getNewToken($clientIdentifier, array $scopes, $userIdentifier = null)
    {
        $token = new AccessTokenEntity();
        $token->setClient($clientIdentifier);
        $token->setUserIdentifier($userIdentifier);
        foreach ($scopes as $scope) {
            $token->addScope($scope);
        }
        return $token;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        // Store token in database
    }

    public function revokeAccessToken($tokenId)
    {
        // Revoke token in database
    }

    public function isAccessTokenRevoked($tokenId)
    {
        return false; // Check if token is revoked
    }
}
```

## Part 4: Running the Example

1. Generate keys:
   ```bash
   openssl genrsa -out private.key 2048
   openssl rsa -in private.key -pubout -out public.key
   ```

2. Start the server (in one terminal):
   ```bash
   php -S localhost:8000 server.php
   ```

3. Start the client (in another terminal):
   ```bash
   php -S localhost:9000 client.php
   ```

4. Visit `http://localhost:9000/client.php` in your browser

## Flow Explanation

1. Client requests authorization from server
2. Server returns authorization code
3. Client exchanges code for access token
4. Client uses token to access protected resources

This example demonstrates the Authorization Code flow, which is the most secure OAuth 2.0 flow for web applications. The server issues access tokens after verifying the client's identity and the resource owner's credentials.
