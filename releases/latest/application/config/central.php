<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| CENTRAL INSTALL FLAG — vendor only
|--------------------------------------------------------------------------
|
| This file exists ONLY on the vendor's central install. It is excluded
| from the release manifest and the full deployment package, so it never
| reaches client installs — code shipped to clients checks mp_is_central()
| and stays inert without this file.
|
| is_central     — master switch for central tooling (Fleet, Manifest,
|                  Release, package serving).
| central_domain — optional extra lock: central features only activate
|                  when the request host matches this domain, so even a
|                  leaked copy of this file can't turn a client install
|                  into a central server. Leave '' to allow any host.
*/
$config['is_central'] = true;
$config['central_domain'] = 'central.martpoint.com.ng';
