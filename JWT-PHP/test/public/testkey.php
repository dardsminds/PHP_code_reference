<?php 
define('ROOT_PATH', dirname(__DIR__));

// Load Composer's autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$privateKey = <<<EOD
-----BEGIN PRIVATE KEY-----
MIIJQwIBADANBgkqhkiG9w0BAQEFAASCCS0wggkpAgEAAoICAQCSePIUoYbnARLQ
3P6ZyN0oeDttIW4SgCm05p4N7KdwTUIsL99GQs9oz8tjMLuFO80lHt2TjmT5NKel
+bbnTpSST+eINNt9pbRK4U1ysZCgSWmQ7SjxQQvBpB8a+mdDqd5ZPdXPZkLmjVKQ
OqrElGnaeeGwbDGVnUig1hxIsMo3xmAWHYySe0Yvi2/8prJMwAtwCXHmwkTETl7J
MjMaBz1AcvDXYrJTOaJ+QC2jskNnB5bIxyB/dwhYKGy9DGuAKlSzNkMLtDiSMf7a
B23s7X0zcJl/ysSI4YWOdbdcGSoQ4YkTCqbTdCGQR4k7XtBko68AMSXdlKRS3BSM
XCl5a3begSiAdib/OoWTW//9mXI3rVfC+FVsVj/JCC6SewaQK+2SHCgWVs7TXvrJ
BcrzdvNQumnRzbU/0wzqK2CLVJUSUne8U/G6za8E1AkTEI0cLqyyH4FLcaOb4T+E
SrhmT+Vkv1TTIajcJGDmMr6JAprOJhSBiwgOLfM33G6YOXtC5ZDxQegp8UQgnCSW
yimQaiPvADvo2OTxAiFh85MjmJwoAZp/fe+MFWbtncGDz6LRtkKRSV1siOCVcjla
hZ4KHeIZdqjw1MQ9lTZEkD2tx9h4uke5Xhj8tazf1BTsSZhNotRja4/msL4KiYg/
rEkpMthbSDv9LhacaEVqQXEbbqKecQIDAQABAoICAASPbEFiWQv1T4O+CarkjdF+
KCLTl7TO6t2AEPDaiskB4sWeP5uVUMCmODfFneT98bsON7RUdTDWvUHQi+uuLuJ8
8eFep0HFcwmiHkeWnyvaX/AcQ3olaLYLQ9D9dacYkWdiYoyDQMx4ScHbTb3ykqFz
yigfFOPKrTxIBkKTcdb5imbN3eFL92lbXu3KYS08t8PHINXK0RodMtwdP77iSvt9
c0xQEpYUP9/3OWSKFDP6O20orp/r/GVwQW3DsU92Ie51dJzB7EFNpK4JmALNzxLX
ATRxGJwxYfeHkzafGEfvxFUvL6FQPDZHf+PfuxRoYzZZ5rWmydSZl7CEswwzIWor
za8uAQ/jXCr9Jr5O6ezr+VwN3I9WhjqEQV49TRhsYXIVF2aaUdqLv93KVZLz2GV5
zP8hkNjNGD4yqABQImbDwn5NZ5dwDhn4jbbZ91XDD1qgzxi/BE8Ys9kVT8sZhR/m
iJAWzylI0gyMHw7wdpNPs0/yOBcbSeMWj6aYVlCAPUvtNGy2nUFiK44fl/W7Qg58
IsWjUUmVFMZ3M9s77+VxvQKQgWXr76Ec5NR/Pq9mksLv1CDEl+UZYK2l+Xa1wD+i
lvgR2Mv+/XEL73Rs25TCDOyGj7udncLFTKkBP3lXqm1WTgeGanTFAN8zqc+6kAuH
OjFcvdmUH0DA8INPyAkxAoIBAQDFl2CgHXgDrgjeSPpq74s5rqH6DO3ZvSq0j6rl
+YvEfVQkIF0sDYkMWwUIFS9OiSXYaVEeu0aSs2sQG9g6Pn/0k4fYM1pQMZrlqNll
i7pBklj/OxOE6IHr5zr3GjJKkpO5OD/JmZthPRbEIzPQ5zxs2P9WItvx9vzkaE7L
abI/e82lEyopK+KEtLSEIAz+rdBvT5AZjNZ/fjWteDzW+ix2naR3QFd/yr0u05n9
sWqhMzYUNwGdK54JoWLpG56M4eHvPxKactJQfijCTs2zY2Vqic04mzH8C6zUpDYY
ICwXTVVYHtobG/W4ppYHqf/HyXj3WNcW5FxIdCe9Efbw/fLpAoIBAQC9xSxhOxGf
M1ZXZezxvEBLBEPQNoG3ix0RnAUDiFzOq59wkoaGXC7H4bk9GNuT3NqnpLvfwOoW
gxfXRd/dw5+ipR6yWt82QKImtQfDQmhDzig3XOkA9IIVAr8Yz73QlE77Hf4ZWSEg
ZuCRZkOdrsm9gO9TU4sUE/ANVYIxmHhgA93tZiFOoMTpnmpSPRSQvZKGBHQrzqNo
nGRZTXYKuxnQtb3tRaWF+RGTmoDpbyxPvyGA5tDf70MtBp+sQxmkeVX8H9WdYOxT
NSvZFA/KWUqanGdFVl+w/6LM0vGSBwC/+CPYqEOzoclFO21/0S70VZUW99bWKycm
X4gFbkgjWUpJAoIBAFz7N0UhmPBiXVn9DZp0zxd9zktU+jiUhBwj31AJdnQoZgf6
Et5AIFXoHx4GmhRjBaQpKztC9ZrjQ2Z5M+90qdH1+t8Ki11henrIUkUu0583txmk
OzM4FqtkTKMreK8O+uUWSy4bUrsXfDcgOan8prqyArYOAWKDz83MKAgg3Phy0fr0
YcquFBJO1wO18WeHc6Zt0mmzlNy6D5hqFHc1kubemB5l8Mb6KLx4ZuazLnJdHv20
RNYpSF1PzLPVg27YfPGQxLhZgA7Qz21gl/vqsjbIUgJpRcRN2i7Wd34y5Yyxn4+w
NQK8zYzvF3rTzMG/VWVQMSdcnvCZeHnIkmQlnMECggEBAI+py3U4UJjjNoQnp39B
8rJX4jaobP9Uk4cXRDxuaUQUbTm905W4B7pOSfvU67Y+xlGPxqMX2p98Uvon7dhn
Flz9AAYqAT2DJL6E4gGSLnjWg1+WONb+Q9RAJgdUjfBvtnMpO4pZDVkISQ4KCzo+
bn/GMmg0oN9sUJjnmQ6OzOJzSvlEDgcGcswhn3/uubjxqxFGIeRgJRk2/EkW+Twa
dOqqC8SqqyqHaiUCHIGcJkGhAm4hTxOYgJR/pTW1/p2jNdMPDpp8G6zKXg2SwHmB
q5bsvMmjIAJRJSBGuZbBMnIiGpEUoVxGKKb+3GCdLhzPBXVD3yJ3vMWyILlrybTp
DfECggEBALuhNckKk6oSjlvSakTf+OuLBZ/RYbsOBRIRK+NeQqKSklYWTlMIvlqL
o8WW/dbaEHBoAUkm/+wTvSDxUNlHYEbAlKgQx99Qza6cLR3pTyicgcd+mtuynKN7
oh9KxEhkV2VVzqNVrT18zHpd+dwMnFWZzs4TWZv8nA3giRjcPZPkkFe0mqcOKNCH
fDZcQwdDtkz0x1Jmwr8j8l463yhQTRjPH4y1DAPdwG9UJK5Lkr0IxJzlEVS3Fbiu
BdNxCU6lCetZOE7nloCn948fGv8otbCOBGBdD8zqLFmpcfUUhoD79wETSYxJN5PV
aj0NLCDSselewwT6ddZH3ipYvpaaaZI=
-----END PRIVATE KEY-----
EOD;

$publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAknjyFKGG5wES0Nz+mcjd
KHg7bSFuEoAptOaeDeyncE1CLC/fRkLPaM/LYzC7hTvNJR7dk45k+TSnpfm2506U
kk/niDTbfaW0SuFNcrGQoElpkO0o8UELwaQfGvpnQ6neWT3Vz2ZC5o1SkDqqxJRp
2nnhsGwxlZ1IoNYcSLDKN8ZgFh2MkntGL4tv/KayTMALcAlx5sJExE5eyTIzGgc9
QHLw12KyUzmifkAto7JDZweWyMcgf3cIWChsvQxrgCpUszZDC7Q4kjH+2gdt7O19
M3CZf8rEiOGFjnW3XBkqEOGJEwqm03QhkEeJO17QZKOvADEl3ZSkUtwUjFwpeWt2
3oEogHYm/zqFk1v//ZlyN61XwvhVbFY/yQguknsGkCvtkhwoFlbO0176yQXK83bz
ULpp0c21P9MM6itgi1SVElJ3vFPxus2vBNQJExCNHC6ssh+BS3Gjm+E/hEq4Zk/l
ZL9U0yGo3CRg5jK+iQKaziYUgYsIDi3zN9xumDl7QuWQ8UHoKfFEIJwklsopkGoj
7wA76Njk8QIhYfOTI5icKAGaf33vjBVm7Z3Bg8+i0bZCkUldbIjglXI5WoWeCh3i
GXao8NTEPZU2RJA9rcfYeLpHuV4Y/LWs39QU7EmYTaLUY2uP5rC+ComIP6xJKTLY
W0g7/S4WnGhFakFxG26innECAwEAAQ==
-----END PUBLIC KEY-----
EOD;



$payload = [
    'email' => 'dario@nflic.com',
    'website' => 'https://www.nflic.com',
    'data' => [
        'product' => 'apple',
    ],
    'date' => date("h:i:sa")
];

$jwt = JWT::encode($payload, $privateKey, 'RS256');
echo "Encode: <br/>" . print_r($jwt, true) . "\n";


try {
    $decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
} catch (InvalidArgumentException $e) {
    // provided key/key-array is empty or malformed.
} catch (DomainException $e) {
    // provided algorithm is unsupported OR
    // provided key is invalid OR
    // unknown error thrown in openSSL or libsodium OR
    // libsodium is required but not available.
} catch (SignatureInvalidException $e) {
    // provided JWT signature verification failed.
} catch (BeforeValidException $e) {
    // provided JWT is trying to be used before "nbf" claim OR
    // provided JWT is trying to be used before "iat" claim.
} catch (ExpiredException $e) {
    // provided JWT is trying to be used after "exp" claim.
} catch (UnexpectedValueException $e) {
    // provided JWT is malformed OR
    // provided JWT is missing an algorithm / using an unsupported algorithm OR
    // provided JWT algorithm does not match provided key OR
    // provided key ID in key/key-array is empty or invalid.
}


echo "<hr><br/><textarea cols=80 rows=15>";
print_r($decoded);
echo "</textarea>";

$decoded_array = (array) $decoded;
echo "<hr><br/>Decode:\n" . print_r($decoded_array, true) . "\n";