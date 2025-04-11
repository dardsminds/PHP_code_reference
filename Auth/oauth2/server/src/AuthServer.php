<?php 

require __DIR__ . '/../vendor/autoload.php';

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface as ClientEntityInterfaceAlias;

class InMemoryClientRepository implements ClientRepositoryInterface
{
    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface
    {
        // Dummy client entity just for example
        return new class implements ClientEntityInterface {
            public function getIdentifier()
            {
                return 'client123';
            }

            public function getName()
            {
                return 'Sample Client';
            }

            public function getRedirectUri()
            {
                return 'http://localhost/client/callback.php';
            }

            public function isConfidential()
            {
                return true;
            }
        };
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        return $clientIdentifier === 'client123' && $clientSecret === 'secret456';
    }
}
