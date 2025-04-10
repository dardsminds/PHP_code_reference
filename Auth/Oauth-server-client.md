Sure! Below is a basic PHP example of an OAuth2 server and client using the OAuth2 Server library by The PHP League. This example is simplified to help you understand the flow.

⸻

1. Requirements

Install via Composer (on both server and client):

composer require league/oauth2-server
composer require nyholm/psr7 # For PSR-7 HTTP Message support

You’ll also need laminas/laminas-diactoros or nyholm/psr7 for request/response objects.

⸻

Part 1: OAuth2 Server

File: oauth-server.php

<?php
require 'vendor/autoload.php';

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

// Dummy Repositories (implement these properly in production)
class ClientRepository implements ClientRepositoryInterface {
    public function getClientEntity($clientIdentifier) {
        return new \League\OAuth2\Server\Entities\ClientEntity();
    }
    public function validateClient($clientIdentifier, $clientSecret, $grantType) {
        return $clientIdentifier === 'client-id' && $clientSecret === 'client-secret';
    }
}
class ScopeRepository implements ScopeRepositoryInterface {
    public function getScopeEntityByIdentifier($identifier) {
        $scope = new \League\OAuth2\Server\Entities\ScopeEntity();
        $scope->setIdentifier($identifier);
        return $scope;
    }
    public function finalizeScopes(array $scopes, $grantType, \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity, $userIdentifier = null) {
        return $scopes;
    }
}
class AccessTokenRepository implements AccessTokenRepositoryInterface {
    public function persistNewAccessToken(\League\OAuth2\Server\Entities\AccessTokenEntityInterface $accessTokenEntity) {}
    public function revokeAccessToken($tokenId) {}
    public function isAccessTokenRevoked($tokenId) {
        return false;
    }
}

$privateKey = new CryptKey('file://private.key', null, false);
$encryptionKey = 'base64:YOUR_RANDOM_KEY=='; // You can generate this using base64_encode(random_bytes(32))

$server = new AuthorizationServer(
    new ClientRepository(),
    new AccessTokenRepository(),
    new ScopeRepository(),
    $privateKey,
    $encryptionKey
);

$server->enableGrantType(new ClientCredentialsGrant(), new \DateInterval('PT1H'));

$psr17Factory = new Psr17Factory();
$serverRequest = $psr17Factory->createServerRequest('POST', '/token')->withParsedBody($_POST);

try {
    $response = $server->respondToAccessTokenRequest($serverRequest, $psr17Factory->createResponse());
    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }
    echo $response->getBody();
} catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {
    http_response_code(400);
    echo json_encode(['error' => $exception->getMessage()]);
}



⸻

Part 2: OAuth2 Client

File: client.php

<?php
$tokenUrl = 'http://localhost/oauth-server.php';

$clientId = 'client-id';
$clientSecret = 'client-secret';

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
]));

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
print_r($data);



⸻

Keys Setup (for server)

Generate keys:

openssl genrsa -out private.key 2048
openssl rsa -in private.key -pubout -out public.key

Also, generate a base64 encryption key for the encryption key:

echo base64_encode(random_bytes(32));



⸻

This is a simplified version to get you started. In a real-world scenario, you should:
	•	Properly implement repositories using a database.
	•	Secure the key files.
	•	Handle refresh tokens, user auth, etc., depending on your needs.

Want a version with password grant or user login?
