<?php
$tokenUrl = 'http://localhost:8000/oauth-server.php';

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

var_dump($response);
echo "test";