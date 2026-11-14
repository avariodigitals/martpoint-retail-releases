<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Update channel hardening
|--------------------------------------------------------------------------
|
| The manifest is fetched from the public update channel, so anyone can read
| it — but nobody must be able to CHANGE it. This public key is what stops
| that: the updater refuses any manifest that is not signed by the matching
| private key, which lives ONLY in config/config.php on the central install
| (that file is gitignored, never packaged, and never overwritten by updates).
|
| Generate a fresh keypair if this one is ever lost or exposed:
|
|   php -r '$kp = sodium_crypto_sign_keypair();
|     echo "public:  " . sodium_bin2hex(sodium_crypto_sign_publickey($kp)) . "\n";
|     echo "private: " . sodium_bin2hex(sodium_crypto_sign_secretkey($kp)) . "\n";'
|
| Paste the PUBLIC key below (it ships with every install). Put the PRIVATE
| key in config/config.php on the central install as:
|
|   $config['release_signing_key'] = '<private key hex>';
|
| Manifest generation REFUSES to run without it once this key is set, so an
| unsigned manifest can never reach clients.
|
| Leave empty to disable signature enforcement (backward compatible).
*/
$config['update_pubkey'] = '4820aeb2992498e91af43ef2b2de5aa3ba835071edabefec2b4cc2d8d2ab33b5';
