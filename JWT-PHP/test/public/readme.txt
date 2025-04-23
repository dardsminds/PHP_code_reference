
ref: https://github.com/firebase/php-jwt

openssl
Use the following command:

openssl genrsa -out ./private.key 4096

ssh-keygen
Use the following command:

ssh-keygen -t rsa -b 4096 -m PEM -f private.key


-----------------------------------------------------

Public Key Generation
Now, you have to create one public key from the already generated private key. Use the same tool used in the step before.

openssl
Use the following command:

openssl rsa -in private.key -pubout -outform PEM -out public.key

ssh-keygen
Use the following command:

ssh-keygen -f private.key -e -m PKCS8 > public.key

----------------------------------------------------------

Client Credential Key format
The Client Credential accepts the public key in a JWK format. You can you a library or an online tool to generate the needed JWK. We suggest the following online tool. Configure it like this:

Public Key Use: Signing
Algorithm: RS256
Key ID: The same you set in PRIVATE_RSA_KEY_ID environment variable
PEM encoded key: your public key, copy here the contents of the file public.key.
Now you have the JWK necessary to register your client using the private key JWT.



https://russelldavies.github.io/jwk-creator/

