# Comprehensive Guide to Generating and Using Public/Private Key Pairs in PHP

Let me expand on several aspects of key generation and usage in PHP:

## 1. Detailed RSA Key Generation with Options

```php
<?php
// Extended configuration with more options
$config = [
    "digest_alg" => "sha512",
    "private_key_bits" => 4096,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
    "config" => "openssl.cnf", // Optional config file path
    "encrypt_key" => true,     // Add password protection
    "encrypt_key_cipher" => OPENSSL_CIPHER_AES_256_CBC
];

// Generate with passphrase
$passphrase = "your-strong-password-here"; // In production, get this from secure storage
$res = openssl_pkey_new($config);

// Export private key with passphrase protection
openssl_pkey_export($res, $privateKey, $passphrase, $config);

// Get public key
$keyDetails = openssl_pkey_get_details($res);
$publicKey = $keyDetails['key'];

// Store keys securely
file_put_contents('private.pem', $privateKey);
chmod('private.pem', 0600); // Restrict file permissions
file_put_contents('public.pem', $publicKey);

echo "Generated 4096-bit RSA keys with AES-256 encrypted private key\n";
?>
```

## 2. Using the Keys for Encryption/Decryption

### Asymmetric Encryption:
```php
$data = "Sensitive information to encrypt";

// Encrypt with public key
openssl_public_encrypt($data, $encrypted, $publicKey);
$encryptedBase64 = base64_encode($encrypted);

// Decrypt with private key
openssl_private_decrypt(base64_decode($encryptedBase64), $decrypted, $privateKey, $passphrase);

echo "Original: $data\n";
echo "Decrypted: $decrypted\n";
```

### Digital Signatures:
```php
// Create signature
openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA512);

// Verify signature
$valid = openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA512);

echo $valid === 1 ? "Signature valid" : "Signature invalid";
```

## 3. Handling Key Formats

### Convert between formats:
```php
// Convert PEM to DER format
$publicKeyDer = openssl_pkey_get_public($publicKey);
openssl_pkey_export_to_file($publicKeyDer, 'public.der', true);

// Extract components (PHP 7.2+)
$components = openssl_pkey_get_details(openssl_pkey_get_private($privateKey, $passphrase));
print_r($components);
```

## 4. Best Practices

1. **Key Rotation**: Implement a key rotation policy (typically every 1-2 years for RSA 4096)

2. **Secure Storage**:
   ```php
   // Better than files - use environment variables or secret manager
   putenv("PRIVATE_KEY=".base64_encode($privateKey));
   putenv("PRIVATE_KEY_PASSPHRASE=$passphrase");
   ```

3. **Key Usage**:
   - Use separate key pairs for encryption vs signing
   - Consider using certificates for identity verification

4. **Algorithm Selection**:
   ```php
   // For ECC (PHP 7.1+ with OpenSSL 1.1.0+)
   $eccConfig = [
       "curve_name" => "secp384r1",
       "private_key_type" => OPENSSL_KEYTYPE_EC,
   ];
   ```

## 5. Troubleshooting Common Issues

- **"openssl_pkey_new() returns false"**:
  - Check OpenSSL extension is loaded (`php -m | grep openssl`)
  - Verify `/etc/ssl/openssl.cnf` exists

- **Password-protected key problems**:
  ```php
  // When loading protected key:
  $key = openssl_pkey_get_private(
      file_get_contents('private.pem'),
      $passphrase
  );
  if ($key === false) {
      echo "Error: ".openssl_error_string();
  }
  ```
