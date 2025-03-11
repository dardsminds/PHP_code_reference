Implementing Two-Factor Authentication (2FA) using Time-Based One-Time Password (TOTP) in PHP involves generating a shared secret, creating a TOTP, and verifying it. Below is a step-by-step guide and example code to implement TOTP-based 2FA in PHP.

---

### Step 1: Install Required Library
To simplify TOTP implementation, use the `spomky-labs/otphp` library, which is a PHP library for generating and verifying TOTP codes.

Install the library using Composer:
```bash
composer require spomky-labs/otphp
```

---

### Step 2: Generate a Secret Key
The secret key is shared between the server and the user's authenticator app (e.g., Google Authenticator).

```php
<?php
require 'vendor/autoload.php';

use OTPHP\TOTP;

// Generate a new TOTP secret
$totp = TOTP::create();
$secret = $totp->getSecret();

echo "Secret Key: " . $secret . "\n";
echo "Provisioning URI: " . $totp->getProvisioningUri() . "\n";
```

- The `getProvisioningUri()` method generates a URI that can be used to add the secret to an authenticator app (e.g., Google Authenticator or Authy).

---

### Step 3: Verify the TOTP Code
When the user enters a TOTP code from their authenticator app, verify it against the secret.

```php
<?php
require 'vendor/autoload.php';

use OTPHP\TOTP;

// Secret key (retrieved from the database or session)
$secret = 'JBSWY3DPEHPK3PXP'; // Replace with the actual secret

// Create TOTP object with the secret
$totp = TOTP::create($secret);

// Simulate user input (replace with actual user input)
$userCode = '123456'; // Replace with the code entered by the user

// Verify the code
if ($totp->verify($userCode)) {
    echo "Code is valid!";
} else {
    echo "Invalid code.";
}
```

---

### Step 4: Store the Secret Key
Store the secret key securely in your database, associated with the user's account. For example:

```php
// Save the secret key to the database
$userId = 1; // Replace with the actual user ID
$secret = 'JBSWY3DPEHPK3PXP'; // Replace with the generated secret

// Save $secret to the database for the user with ID $userId
```

---

### Step 5: Display QR Code for Easy Setup
To make it easier for users to add the secret to their authenticator app, generate a QR code using the provisioning URI.

You can use a library like `endroid/qr-code` to generate QR codes.

Install the library:
```bash
composer require endroid/qr-code
```

Generate the QR code:
```php
<?php
require 'vendor/autoload.php';

use OTPHP\TOTP;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Generate TOTP and provisioning URI
$totp = TOTP::create('JBSWY3DPEHPK3PXP'); // Replace with the actual secret
$provisioningUri = $totp->getProvisioningUri();

// Generate QR code
$qrCode = new QrCode($provisioningUri);
$writer = new PngWriter();
$result = $writer->write($qrCode);

// Output the QR code image
header('Content-Type: ' . $result->getMimeType());
echo $result->getString();
```

---

### Full Example Workflow
1. Generate a secret key and provisioning URI.
2. Store the secret key in the database.
3. Display the QR code to the user for setup.
4. Verify the TOTP code entered by the user.

---

### Notes
- Ensure the server's time is synchronized with NTP to avoid TOTP verification issues.
- Use HTTPS to securely transmit the secret key and TOTP codes.
- Store the secret key securely in your database (e.g., encrypted).

This implementation provides a robust and secure way to add TOTP-based 2FA to your PHP application.
