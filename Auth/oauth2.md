# PHP OAuth 2.0 Example

Here's a complete example of implementing OAuth 2.0 in PHP using the [league/oauth2-client](https://github.com/thephpleague/oauth2-client) package, which is a popular OAuth 2.0 client library for PHP.

## Installation

First, install the required package via Composer:

```bash
composer require league/oauth2-client
```

## Example: OAuth 2.0 Client Implementation

```php
<?php
require 'vendor/autoload.php';

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

// Configuration for your OAuth 2.0 provider
$provider = new GenericProvider([
    'clientId'                => 'your-client-id',    // The client ID assigned to you by the provider
    'clientSecret'            => 'your-client-secret',// The client password assigned to you by the provider
    'redirectUri'             => 'https://your-app.com/callback',
    'urlAuthorize'            => 'https://provider.com/oauth2/authorize',
    'urlAccessToken'          => 'https://provider.com/oauth2/token',
    'urlResourceOwnerDetails' => 'https://provider.com/oauth2/resource'
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {
    
    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    $authorizationUrl = $provider->getAuthorizationUrl();
    
    // Get the state generated for you and store it to the session
    $_SESSION['oauth2state'] = $provider->getState();
    
    // Redirect the user to the authorization URL
    header('Location: ' . $authorizationUrl);
    exit;
    
// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
    
} else {
    
    try {
        // Try to get an access token using the authorization code grant
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
        
        // We have an access token, which we may use in authenticated
        // requests against the service provider's API
        echo 'Access Token: ' . $accessToken->getToken() . "<br>";
        echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
        echo 'Expires: ' . $accessToken->getExpires() . "<br>";
        
        // Using the access token, we may look up details about the resource owner
        $resourceOwner = $provider->getResourceOwner($accessToken);
        
        // Return the owner's details
        var_export($resourceOwner->toArray());
        
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        
        // Failed to get the access token or user details
        exit($e->getMessage());
    }
}
```

## Example with a Specific Provider (Google)

For a more concrete example, here's how to use OAuth 2.0 with Google:

```php
<?php
require 'vendor/autoload.php';

use League\OAuth2\Client\Provider\Google;

// Replace these with your token from Google
$clientId = 'your-google-client-id';
$clientSecret = 'your-google-client-secret';

$provider = new Google([
    'clientId'     => $clientId,
    'clientSecret' => $clientSecret,
    'redirectUri'  => 'https://your-app.com/callback',
]);

if (!isset($_GET['code'])) {
    
    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;

} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
    
} else {
    
    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);
    
    // Optional: Now you have a token you can look up a users profile data
    try {
        // We got an access token, let's now get the owner details
        $ownerDetails = $provider->getResourceOwner($token);
        
        // Use these details to create a new profile or login
        printf('Hello %s!', $ownerDetails->getFirstName());
        
    } catch (Exception $e) {
        exit('Something went wrong: ' . $e->getMessage());
    }
    
    // Use this to interact with an API on the users behalf
    echo 'Token: ' . $token->getToken();
}
```

## Notes

1. You'll need to register your application with the OAuth provider (Google, Facebook, etc.) to get client credentials.
2. The redirect URI must match exactly what you register with the provider.
3. Always validate the state parameter to prevent CSRF attacks.
4. Store tokens securely - never expose them to clients or commit them to version control.
5. For production use, consider using more specific provider packages like:
   - `league/oauth2-google` for Google
   - `league/oauth2-facebook` for Facebook
   - `league/oauth2-github` for  on any specific part of OAuth 2.0 implementation in PHP?
