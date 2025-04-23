<?php 
define('ROOT_PATH', dirname(__DIR__));

// Load Composer's autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;



$key = 'Th3Qu1ckBr0wnF0xJumP!0v3RTh3La2yD0gN3arTh3Bank0fTheRiver?';
$payload = [
    'email' => 'dario@nflic.com',
    'website' => 'https://www.nflic.com',
    'data' => [
        'product' => 'apple',
    ],
    'date' => date("h:i:sa")
];

/**
 * IMPORTANT:
 * You must specify supported algorithms for your application. See
 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
 * for a list of spec-compliant algorithms.
 */
$jwt = JWT::encode($payload, $key, 'HS256');
$decoded = JWT::decode($jwt, new Key($key, 'HS256'));

print_r($jwt);
echo "<hr>";

echo "<textarea cols=80 rows=15>";
print_r($decoded);
echo "</textarea>";