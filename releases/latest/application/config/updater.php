<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Update channel hardening
|--------------------------------------------------------------------------
|
| update_pubkey — hex-encoded ed25519 PUBLIC key used to verify the
| release manifest signature before any update is applied. Generate the
| keypair once on the build machine:
|
|   php -r '$kp = sodium_crypto_sign_keypair();
|     echo "public:  " . sodium_bin2hex(sodium_crypto_sign_publickey($kp)) . "\n";
|     echo "private: " . sodium_bin2hex(sodium_crypto_sign_secretkey($kp)) . "\n";'
|
| Paste the PUBLIC key here (ships with every install). Keep the PRIVATE
| key only on the build server, in config/config.php as
| $config['release_signing_key'] — the manifest generator signs with it.
|
| Leave empty to disable signature enforcement (backward compatible).
*/
$config['update_pubkey'] = '';
