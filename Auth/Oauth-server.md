# PHP OAuth 2.0 Server Example

Here's a complete example of implementing an OAuth 2.0 server in PHP using the [league/oauth2-server](https://oauth2.thephpleague.com/) package, which is the most robust OAuth 2.0 server implementation for PHP.

## Installation

First, install the required packages via Composer:

```bash
composer require league/oauth2-server
```

## Basic OAuth 2.0 Server Implementation

### 1. Database Setup

First, create the necessary database tables:

```sql
CREATE TABLE oauth_clients (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    secret VARCHAR(255) NOT NULL,
    redirect_uri VARCHAR(255) NOT NULL,
    confidential BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE oauth_access_tokens (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    user_id INTEGER,
    client_id INTEGER NOT NULL,
    scopes TEXT,
    revoked BOOLEAN DEFAULT false,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES oauth_clients (id)
);

CREATE TABLE oauth_auth_codes (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    user_id INTEGER,
    client_id INTEGER NOT NULL,
    scopes TEXT,
    revoked BOOLEAN DEFAULT false,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES oauth_clients (id)
);

CREATE TABLE oauth_refresh_tokens (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    access_token_id INTEGER NOT NULL,
    revoked BOOLEAN DEFAULT false,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens (id)
);
```

### 2. Server Implementation

```php
<?php
require 'vendor/autoload.php';

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;

// Create new DI container
$container = new \League\Container\Container();

// Create a PSR-7 request and response
$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);
$response = new Laminas\Diactoros\Response();

// Setup the repositories
$clientRepository = new ClientRepository(); // Your implementation
$scopeRepository = new ScopeRepository();   // Your implementation
$authCodeRepository = new AuthCodeRepository(); // Your implementation
$accessTokenRepository = new AccessTokenRepository(); // Your implementation
$refreshTokenRepository = new RefreshTokenRepository(); // Your implementation
$userRepository = new UserRepository(); // Your implementation

// Path to public and private keys
$privateKey = 'file://path/to/private.key';
$publicKey = 'file://path/to/public.key';

// Setup the authorization server
$server = new AuthorizationServer(
    $clientRepository,
    $accessTokenRepository,
    $scopeRepository,
    $privateKey,
    'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'
);

// Enable the authentication code grant
$grant = new AuthCodeGrant(
    $authCodeRepository,
    $refreshTokenRepository,
    new \DateInterval('PT10M') // authorization codes will expire after 10 minutes
);

$server->enableGrantType(
    $grant,
    new \DateInterval('PT1H') // access tokens will expire after 1 hour
);

// Enable the refresh token grant
$refreshGrant = new RefreshTokenGrant($refreshTokenRepository);
$refreshGrant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

$server->enableGrantType(
    $refreshGrant,
    new \DateInterval('PT1H') // new access tokens will expire after 1 hour
);

// Create the resource server (for validating access tokens)
$resourceServer = new ResourceServer(
    $accessTokenRepository,
    $publicKey
);

// Create Slim app
$app = AppFactory::create();

// Authorization endpoint
$app->get('/authorize', function (ServerRequestInterface $request, ResponseInterface $response) use ($server) {
    try {
        // Validate the HTTP request and return an AuthorizationRequest object
        $authRequest = $server->validateAuthorizationRequest($request);
        
        // The auth request object can be serialized and saved into a user's session
        // You will probably want to redirect the user at this point to login
        
        // Once the user has logged in, set the user on the AuthorizationRequest
        $authRequest->setUser(new UserEntity()); // Your user entity
        
        // Once the user has approved or denied the client update the status
        // (true = approved, false = denied)
        $authRequest->setAuthorizationApproved(true);
        
        // Return the HTTP redirect response
        return $server->completeAuthorizationRequest($authRequest, $response);
    } catch (OAuthServerException $exception) {
        return $exception->generateHttpResponse($response);
    } catch (\Exception $exception) {
        $response->getBody()->write($exception->getMessage());
        return $response->withStatus(500);
    }
});

// Token endpoint
$app->post('/access_token', function (ServerRequestInterface $request, ResponseInterface $response) use ($server) {
    try {
        return $server->respondToAccessTokenRequest($request, $response);
    } catch (OAuthServerException $exception) {
        return $exception->generateHttpResponse($response);
    } catch (\Exception $exception) {
        $response->getBody()->write($exception->getMessage());
        return $response->withStatus(500);
    }
});

// Protected resource endpoint
$app->get('/api/user', function (ServerRequestInterface $request, ResponseInterface $response) use ($resourceServer) {
    try {
        // Validate the access token in the request
        $request = $resourceServer->validateAuthenticatedRequest($request);
        
        // You can now access the user ID and scopes
        $userId = $request->getAttribute('oauth_user_id');
        $scopes = $request->getAttribute('oauth_scopes');
        
        // Return the user data
        $userData = [
            'id' => $userId,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        
        $response->getBody()->write(json_encode($userData));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (OAuthServerException $exception) {
        return $exception->generateHttpResponse($response);
    } catch (\Exception $exception) {
        $response->getBody()->write($exception->getMessage());
        return $response->withStatus(500);
    }
});

$app->run();
```

## Repository Implementations

Here's an example of a basic client repository implementation:

```php
<?php
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class ClientRepository implements ClientRepositoryInterface
{
    public function getClientEntity($clientIdentifier)
    {
        // Query your database for client details
        $client = new ClientEntity();
        $client->setIdentifier($clientIdentifier);
        $client->setName('My App');
        $client->setRedirectUri('https://my-app.com/callback');
        $client->setConfidential();
        
        return $client;
    }
    
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        // Validate client credentials (if confidential client)
        $client = $this->getClientEntity($clientIdentifier);
        
        if ($client->isConfidential() && !hash_equals($client->getSecret(), $clientSecret)) {
            return false;
        }
        
        return true;
    }
}
```

## Key Generation

You'll need to generate public and private keys:

```bash
# Generate private key
openssl genrsa -out private.key 2048

# Generate public key
openssl rsa -in private.key -pubout -out public.key
```

## Notes

1. This is a basic implementation. In production, you should:
   - Use proper dependency injection
   - Implement proper database repositories
   - Add more security measures
   - Implement proper user authentication
   - Add logging and monitoring

2. The league/oauth2-server package supports all OAuth 2.0 grants:
   - Authorization Code
   - Implicit
   - Password
   - Client Credentials
   - Refresh Token

3. For a more complete solution, consider using Passport (for Laravel) or other framework-specific OAuth 2.0 server packages.

Would you like me to elaborate on any specific part of the OAuth 2.0 server implementation?
