<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| MartPoint AI Growth is deliberately fail-closed.
|
| The merchant database stores only tenant-scoped Growth records. Central
| entitlement/payment verification and provider credentials are separate
| concerns. Keep this master switch false until those controls are reviewed.
*/
$config['ai_growth'] = array(
	'enabled' => false,
	'wallet_operations_enabled' => false,
	'wallet_funding_enabled' => false,
	'reference_plan_code' => 'AI_GROWTH',
	'reference_monthly_minor' => 15000000,
	'reference_currency' => 'NGN',
	'ledger_currency' => 'NGN',
);

