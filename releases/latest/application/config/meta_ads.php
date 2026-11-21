<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Configure these as environment variables. Never commit Meta credentials.
$config['meta_ads_app_id'] = getenv('MP_META_APP_ID') ?: '1492393586161488';
$config['meta_ads_login_config_id'] = getenv('MP_META_LOGIN_CONFIG_ID') ?: '1411401517644638';
// Legacy fallback only. Central fetches the rotatable secret from Next Admin's
// encrypted Meta settings endpoint using the existing Central billing HMAC.
$config['meta_ads_app_secret'] = getenv('MP_META_APP_SECRET') ?: '';
$config['meta_ads_token_key'] = getenv('MP_AI_ADS_ENCRYPTION_KEY') ?: '';
$config['meta_ads_graph_version'] = getenv('MP_META_GRAPH_VERSION') ?: 'v27.0';
$defaultRedirect = 'https://central.martpoint.com.ng/marketing/ai_ads/broker_callback';
$config['meta_ads_redirect_uri'] = getenv('MP_META_REDIRECT_URI') ?: $defaultRedirect;
