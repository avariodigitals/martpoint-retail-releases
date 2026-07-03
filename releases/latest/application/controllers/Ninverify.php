<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ninverify extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load_global();
    }

    /**
     * Verify NIN/BVN via configured API or mock/demo mode
     */
    public function verify(){
        $nin = trim($this->input->post('nin_bvn', TRUE));
        $store_id = get_current_store_id();
        $customer_id = $this->input->post('customer_id', TRUE);
        $user_id = $this->session->userdata('inv_userid');
        $user_name = $this->session->userdata('inv_username');

        if(empty($nin)){
            $result = ['status'=>'error','message'=>'Please enter NIN/BVN number.'];
            $this->_log_verification($store_id, $user_id, $user_name, $customer_id, $nin, 'none', $result, true, 0.00);
            echo json_encode($result);
            return;
        }

        // Detect NIN vs BVN
        $is_nin = preg_match('/^\d{11}$/', $nin);
        $is_bvn = preg_match('/^\d{10,11}$/', $nin);
        if(!$is_nin && !$is_bvn){
            $result = ['status'=>'error','message'=>'Invalid NIN/BVN format. NIN must be 11 digits, BVN 10-11 digits.'];
            $this->_log_verification($store_id, $user_id, $user_name, $customer_id, $nin, 'none', $result, true, 0.00);
            echo json_encode($result);
            return;
        }
        $id_type = $is_nin ? 'NIN' : 'BVN';

        // Get store API settings
        $store = $this->db->where('id', $store_id)->get('db_store')->row();
        $api_enabled = $store ? (isset($store->nin_api_enabled) ? $store->nin_api_enabled : 0) : 0;
        $api_key = $store ? (isset($store->nin_api_key) ? trim($store->nin_api_key) : '') : '';

        // Determine provider based on ID type
        if($is_nin){
            $api_provider = $store ? (isset($store->nin_provider) ? $store->nin_provider : (isset($store->nin_api_provider) ? $store->nin_api_provider : 'ninbvnportal')) : 'ninbvnportal';
        } else {
            $api_provider = $store ? (isset($store->bvn_provider) ? $store->bvn_provider : (isset($store->nin_api_provider) ? $store->nin_api_provider : 'ninbvnportal')) : 'ninbvnportal';
        }

        // Check required credentials based on provider
        $has_creds = false;
        if($api_provider === 'interswitch'){
            $client_id = $store ? (isset($store->interswitch_client_id) ? trim($store->interswitch_client_id) : '') : '';
            $client_secret = $store ? (isset($store->interswitch_client_secret) ? trim($store->interswitch_client_secret) : '') : '';
            $has_creds = !empty($client_id) && !empty($client_secret);
            // Also allow direct Bearer token in nin_api_key as fallback
            if(!$has_creds && !empty($api_key)){
                $has_creds = true;
            }
        } else {
            $has_creds = !empty($api_key);
        }

        // If API not enabled or missing credentials, use demo/mock verification
        if(empty($api_enabled) || !$has_creds){
            $result = $this->_mock_verify($nin);
            $this->_log_verification($store_id, $user_id, $user_name, $customer_id, $nin, 'demo', $result, true, 0.00);
            echo json_encode($result);
            return;
        }

        // Call real API
        $result = $this->_call_api($nin, $api_key, $api_provider, $id_type, $store);
        $cost_per_verify = isset($store->nin_api_cost) ? $store->nin_api_cost : 50.00;
        $cost = ($result['status'] === 'success') ? $cost_per_verify : 0.00;
        $this->_log_verification($store_id, $user_id, $user_name, $customer_id, $nin, $api_provider . ' (' . $id_type . ')', $result, false, $cost);
        echo json_encode($result);
    }

    private function _mock_verify($nin){
        // Mock verification for development/demo
        $is_nin = preg_match('/^\d{11}$/', $nin);
        $is_bvn = preg_match('/^\d{10,11}$/', $nin);

        if(!$is_nin && !$is_bvn){
            return [
                'status' => 'error',
                'message' => 'Invalid NIN/BVN format. NIN must be 11 digits, BVN 10-11 digits.'
            ];
        }

        $hash = crc32($nin);
        $first_names = ['Ade','Chioma','Oluwaseun','Fatima','Emmanuel','Ngozi','Ibrahim','Amina','Tunde','Halima'];
        $last_names = ['Adeyemi','Okafor','Balogun','Mohammed','Nwosu','Ibrahim','Ojo','Abdullahi','Eze','Yusuf'];
        $fn = $first_names[abs($hash) % count($first_names)];
        $ln = $last_names[abs($hash >> 4) % count($last_names)];

        return [
            'status' => 'success',
            'message' => 'Verification successful (Demo Mode). Please configure real API in Store Settings.',
            'data' => [
                'first_name' => $fn,
                'last_name' => $ln,
                'full_name' => $fn . ' ' . $ln,
                'phone' => '080' . str_pad(abs($hash % 100000000), 8, '0', STR_PAD_LEFT),
                'address' => 'No ' . (abs($hash % 100) + 1) . ', Demo Street, Lagos',
                'email' => strtolower($fn . '.' . $ln . '@example.com'),
                'nin' => $nin,
                'verified' => true
            ]
        ];
    }

    private function _call_api($nin, $api_key, $api_provider, $id_type, $store = null){
        if($api_provider === 'interswitch'){
            return $this->_call_interswitch_api($nin, $api_key, $id_type, $store);
        }

        // ninbvnportal or custom
        $store_id = get_current_store_id();
        if(!$store){
            $store = $this->db->where('id', $store_id)->get('db_store')->row();
        }
        $endpoint = ($store && !empty($store->nin_api_url)) ? rtrim($store->nin_api_url, '/') : '';
        if(empty($endpoint)){
            return ['status'=>'error','message'=>'Custom API URL is not configured in Store Settings.'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'nin' => $nin,
            'consent' => true
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if($error){
            return ['status'=>'error','message'=>'API connection error: ' . $error];
        }

        if($http_code != 200){
            $body_preview = substr($response, 0, 500);
            return ['status'=>'error','message'=>'API returned HTTP ' . $http_code . '. Response: ' . $body_preview];
        }

        $raw = json_decode($response, true);
        if(!$raw || !isset($raw['status'])){
            return ['status'=>'error','message'=>'Invalid API response from: ' . $endpoint . ' | Body: ' . substr($response, 0, 300)];
        }

        // Map provider response fields to our frontend format
        if($raw['status'] === 'success' && isset($raw['data'])){
            $d = $raw['data'];
            $fn = isset($d['firstname']) ? $d['firstname'] : '';
            $mn = isset($d['middlename']) ? $d['middlename'] : '';
            $sn = isset($d['surname']) ? $d['surname'] : '';
            $full = trim($fn . ' ' . $mn . ' ' . $sn);

            return [
                'status' => 'success',
                'message' => isset($raw['message']) ? $raw['message'] : 'Verification successful',
                'data' => [
                    'verified' => true,
                    'full_name' => $full,
                    'first_name' => $fn,
                    'last_name' => $sn,
                    'phone' => isset($d['telephoneno']) ? $d['telephoneno'] : '',
                    'address' => isset($d['residence_address']) ? $d['residence_address'] : '',
                    'email' => isset($d['email']) ? $d['email'] : '',
                    'nin' => isset($d['nin']) ? $d['nin'] : $nin,
                    'gender' => isset($d['gender']) ? $d['gender'] : '',
                    'birthdate' => isset($d['birthdate']) ? $d['birthdate'] : ''
                ]
            ];
        }

        // Return error as-is
        return $raw;
    }

    private function _call_interswitch_api($id, $api_key, $id_type, $store = null){
        // Determine base URL: allow override via nin_api_url store setting
        $base = 'https://api-marketplace-routing.k8.isw.la';
        if($store && !empty($store->nin_api_url)){
            $base = rtrim($store->nin_api_url, '/');
        }

        // The marketplace-routing prefix was the original working path (returned 401 = auth issue, not 404)
        $rel_paths = ($id_type === 'NIN') ? [
            '/marketplace-routing/api/v1/verify/identity/nin/verify',
            '/api/v1/verify/identity/nin/verify',
            '/verify/identity/nin/verify',
        ] : [
            '/marketplace-routing/api/v1/verify/identity/bvn/verify',
            '/api/v1/verify/identity/bvn/verify',
            '/verify/identity/bvn/verify',
        ];

        // Determine bearer token: try Client Credentials first, fallback to api_key as Bearer
        $bearer_token = '';
        $client_id = '';
        $token_error = '';
        if($store){
            $client_id = isset($store->interswitch_client_id) ? trim($store->interswitch_client_id) : '';
            $client_secret = isset($store->interswitch_client_secret) ? trim($store->interswitch_client_secret) : '';
            if(!empty($client_id) && !empty($client_secret)){
                $token_resp = $this->_get_interswitch_token($client_id, $client_secret);
                if($token_resp['status'] === 'success' && !empty($token_resp['token'])){
                    $bearer_token = $token_resp['token'];
                } else {
                    $token_error = $token_resp['message'];
                    // Do NOT return here — allow fallback to api_key (pre-generated Bearer token)
                }
            }
        }
        if(empty($bearer_token) && !empty($api_key)){
            $bearer_token = $api_key;
        }
        if(empty($bearer_token)){
            $msg = 'Interswitch credentials not configured or invalid. ';
            if(!empty($token_error)){
                $msg .= 'Token error: ' . $token_error . '. ';
            }
            $msg .= 'Please set a valid Client ID & Client Secret (use Sandbox credentials for testing), or paste a pre-generated Bearer token into the API Token / Key field in Store Settings. For sandbox, set Custom API URL to https://sandbox.interswitchng.com';
            return ['status'=>'error','message'=>$msg];
        }

        // Interswitch verification body uses generic "id" key
        $post_body = json_encode(['id' => $id]);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $bearer_token
        ];
        if(!empty($client_id)){
            $headers[] = 'client-id: ' . $client_id;
        }

        $last_error = '';
        foreach($rel_paths as $path){
            $endpoint = $base . $path;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if($error){
                $last_error = 'Connection error to ' . $endpoint . ': ' . $error;
                continue;
            }

            if($http_code == 404){
                $last_error = 'HTTP 404 from ' . $endpoint;
                continue; // Try next path variant
            }

            if($http_code != 200){
                $body_preview = substr($response, 0, 500);
                return ['status'=>'error','message'=>'Interswitch API returned HTTP ' . $http_code . ' from ' . $endpoint . '. Response: ' . $body_preview];
            }

            $raw = json_decode($response, true);
            if(!$raw){
                return ['status'=>'error','message'=>'Invalid JSON from Interswitch at ' . $endpoint . '. Body: ' . substr($response, 0, 300)];
            }

            // Success — process response
            return $this->_parse_interswitch_response($raw, $id_type, $id);
        }

        return ['status'=>'error','message'=>'Interswitch API endpoint not found. ' . $last_error];
    }

    private function _parse_interswitch_response($raw, $id_type, $id){
        // Interswitch may wrap under 'data' or return identity fields directly.
        $d = isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw;

        // Determine success: look for known identity fields or a success flag
        $has_identity = isset($d['firstName']) || isset($d['first_name']) || isset($d['firstname'])
                     || isset($d['surname']) || isset($d['lastName']) || isset($d['last_name'])
                     || isset($d['name']);

        if(!$has_identity && isset($raw['status']) && strtolower($raw['status']) !== 'success'){
            $msg = isset($raw['message']) ? $raw['message'] : (isset($raw['error']) ? $raw['error'] : 'Verification failed.');
            return ['status'=>'error','message'=>$msg];
        }

        // Map fields flexibly
        $fn = isset($d['firstName']) ? $d['firstName'] : (isset($d['first_name']) ? $d['first_name'] : (isset($d['firstname']) ? $d['firstname'] : ''));
        $mn = isset($d['middleName']) ? $d['middleName'] : (isset($d['middle_name']) ? $d['middle_name'] : (isset($d['middlename']) ? $d['middlename'] : ''));
        $ln = isset($d['surname']) ? $d['surname'] : (isset($d['lastName']) ? $d['lastName'] : (isset($d['last_name']) ? $d['last_name'] : (isset($d['name']) ? $d['name'] : '')));
        $full = trim($fn . ' ' . $mn . ' ' . $ln);
        if(empty($full) && !empty($ln)) $full = $ln;

        $phone = isset($d['phoneNumber']) ? $d['phoneNumber'] : (isset($d['phone']) ? $d['phone'] : (isset($d['telephone']) ? $d['telephone'] : (isset($d['telephoneno']) ? $d['telephoneno'] : '')));
        $address = isset($d['residentialAddress']) ? $d['residentialAddress'] : (isset($d['address']) ? $d['address'] : (isset($d['residence_address']) ? $d['residence_address'] : ''));
        $email = isset($d['email']) ? $d['email'] : '';
        $gender = isset($d['gender']) ? $d['gender'] : '';
        $birthdate = isset($d['dateOfBirth']) ? $d['dateOfBirth'] : (isset($d['birthdate']) ? $d['birthdate'] : (isset($d['dob']) ? $d['dob'] : ''));
        $returned_id = isset($d['nin']) ? $d['nin'] : (isset($d['bvn']) ? $d['bvn'] : (isset($d['id']) ? $d['id'] : $id));

        return [
            'status' => 'success',
            'message' => isset($raw['message']) ? $raw['message'] : $id_type . ' verified via Interswitch',
            'data' => [
                'verified' => true,
                'full_name' => $full,
                'first_name' => $fn,
                'last_name' => $ln,
                'phone' => $phone,
                'address' => $address,
                'email' => $email,
                'nin' => $returned_id,
                'gender' => $gender,
                'birthdate' => $birthdate,
                '_raw_response' => $raw
            ]
        ];
    }

    private function _get_interswitch_token($client_id, $client_secret){
        // Interswitch OAuth token endpoints to try
        // Sandbox: https://sandbox.interswitchng.com/passport/oauth/token
        // Production: https://passport.interswitchng.com/passport/oauth/token
        $token_urls = [
            'https://passport.interswitchng.com/passport/oauth/token',
            'https://sandbox.interswitchng.com/passport/oauth/token',
            'https://satpass.isw.com/passport/oauth/token',
            'https://api-marketplace-routing.k8.isw.la/passport/oauth/token',
        ];

        // Interswitch Passport accepts both Basic Auth header AND client_id/client_secret in body
        $post_fields = http_build_query([
            'grant_type'    => 'client_credentials',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
        ]);

        $auth_header = 'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret);
        $errors = [];

        foreach($token_urls as $token_url){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $token_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                $auth_header
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if($error){
                $errors[] = $token_url . ' => Connection error: ' . $error;
                continue;
            }

            if($http_code != 200){
                $errors[] = $token_url . ' => HTTP ' . $http_code . ': ' . substr($response, 0, 200);
                continue;
            }

            $raw = json_decode($response, true);
            if($raw && isset($raw['access_token'])){
                return ['status'=>'success','token'=>$raw['access_token']];
            }
            $errors[] = $token_url . ' => No access_token in response';
        }

        return ['status'=>'error','token'=>'','message'=>'Unable to obtain Interswitch access token. ' . implode(' | ', $errors)];
    }

    /**
     * Log every verification attempt for billing transparency
     */
    private function _log_verification($store_id, $user_id, $user_name, $customer_id, $nin, $provider, $result, $is_mock, $cost){
        $this->db->insert('db_nin_verification_logs', [
            'store_id' => $store_id,
            'user_id' => $user_id,
            'user_name' => $user_name,
            'customer_id' => $customer_id,
            'nin_bvn' => $nin,
            'provider' => $provider,
            'status' => isset($result['status']) ? $result['status'] : 'unknown',
            'response_message' => isset($result['message']) ? $result['message'] : '',
            'cost' => $cost,
            'is_mock' => $is_mock ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Diagnostic endpoint: test Interswitch connectivity and log everything
     * Call via browser: /ninverify/test?id=12345678901&type=NIN
     */
    public function test(){
        if(!is_admin() && !special_access()){
            echo json_encode(['status'=>'error','message'=>'Admin only.']);
            return;
        }

        $test_id = $this->input->get('id', TRUE) ?: '12345678901';
        $test_type = $this->input->get('type', TRUE) ?: 'NIN';
        $store_id = get_current_store_id();
        $store = $this->db->where('id', $store_id)->get('db_store')->row();

        $log = [];
        $log[] = '=== Interswitch Diagnostic Test ===';
        $log[] = 'Store ID: ' . $store_id;
        $log[] = 'Test ID: ' . $test_id;
        $log[] = 'Test Type: ' . $test_type;
        $log[] = '';

        $client_id = $store ? (isset($store->interswitch_client_id) ? trim($store->interswitch_client_id) : '') : '';
        $client_secret = $store ? (isset($store->interswitch_client_secret) ? trim($store->interswitch_client_secret) : '') : '';
        $api_key = $store ? (isset($store->nin_api_key) ? trim($store->nin_api_key) : '') : '';
        $nin_api_url = $store ? (isset($store->nin_api_url) ? trim($store->nin_api_url) : '') : '';

        $log[] = 'Client ID set: ' . (!empty($client_id) ? 'Yes (len=' . strlen($client_id) . ')' : 'No');
        $log[] = 'Client Secret set: ' . (!empty($client_secret) ? 'Yes (len=' . strlen($client_secret) . ')' : 'No');
        $log[] = 'API Key / Token set: ' . (!empty($api_key) ? 'Yes (len=' . strlen($api_key) . ')' : 'No');
        $log[] = 'Custom API URL: ' . (!empty($nin_api_url) ? $nin_api_url : 'Not set');
        $log[] = '';

        // Test token endpoints
        $token_urls = [
            'https://passport.interswitchng.com/passport/oauth/token',
            'https://sandbox.interswitchng.com/passport/oauth/token',
            'https://satpass.isw.com/passport/oauth/token',
            'https://api-marketplace-routing.k8.isw.la/passport/oauth/token',
            'https://api-marketplace-routing.k8.isw.la/oauth/token',
        ];

        $log[] = '--- Token Endpoint Tests ---';
        $bearer_token = '';
        foreach($token_urls as $token_url){
            $post_fields = http_build_query([
                'grant_type'    => 'client_credentials',
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
            ]);
            $auth_header = 'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $token_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                $auth_header
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            $log[] = 'URL: ' . $token_url;
            $log[] = 'HTTP Code: ' . $http_code;
            $log[] = 'Curl Error: ' . ($error ?: 'None');
            $log[] = 'Response: ' . substr($response, 0, 300);
            $log[] = '';

            if($http_code == 200){
                $raw = json_decode($response, true);
                if($raw && isset($raw['access_token'])){
                    $bearer_token = $raw['access_token'];
                    $log[] = 'SUCCESS: Token obtained from ' . $token_url;
                    $log[] = 'Token prefix: ' . substr($bearer_token, 0, 20) . '...';
                    $log[] = '';
                    break;
                }
            }
        }

        if(empty($bearer_token) && !empty($api_key)){
            $bearer_token = $api_key;
            $log[] = 'FALLBACK: Using api_key as Bearer token';
            $log[] = 'Token prefix: ' . substr($bearer_token, 0, 20) . '...';
            $log[] = '';
        }

        if(empty($bearer_token)){
            $log[] = 'ERROR: No valid token available.';
            $this->_write_debug_log($log);
            echo json_encode(['status'=>'error','message'=>'No valid token. Check logs.','log'=>implode("\n", $log)]);
            return;
        }

        // Test verification endpoints
        $base = 'https://api-marketplace-routing.k8.isw.la';
        if(!empty($nin_api_url)){
            $base = rtrim($nin_api_url, '/');
        }

        $paths = ($test_type === 'NIN') ? [
            '/marketplace-routing/api/v1/verify/identity/nin/verify',
            '/api/v1/verify/identity/nin/verify',
            '/verify/identity/nin/verify',
        ] : [
            '/marketplace-routing/api/v1/verify/identity/bvn/verify',
            '/api/v1/verify/identity/bvn/verify',
            '/verify/identity/bvn/verify',
        ];

        $log[] = '--- Verification Endpoint Tests ---';
        $log[] = 'Base URL used: ' . $base;
        $log[] = '';
        foreach($paths as $path){
            $endpoint = $base . $path;
            $post_body = json_encode(['id' => $test_id]);

            $headers = [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $bearer_token
            ];
            if(!empty($client_id)){
                $headers[] = 'client-id: ' . $client_id;
            }

            $log[] = 'Endpoint: ' . $endpoint;
            $log[] = 'Headers: ' . json_encode($headers);
            $log[] = 'Body: ' . $post_body;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            $log[] = 'HTTP Code: ' . $http_code;
            $log[] = 'Curl Error: ' . ($error ?: 'None');
            $log[] = 'Response: ' . ($response ?: '(empty)');
            $log[] = '';

            if($http_code == 200){
                $log[] = 'SUCCESS: Verification endpoint found at ' . $endpoint;
                break;
            }
        }

        $this->_write_debug_log($log);
        echo json_encode(['status'=>'done','message'=>'Diagnostic complete. Check ninverify_debug.log or see log below.','log'=>implode("\n", $log)]);
    }

    private function _write_debug_log($lines){
        $log_file = APPPATH . 'logs/ninverify_debug.log';
        $content = "[" . date('Y-m-d H:i:s') . "]\n" . implode("\n", $lines) . "\n\n";
        @file_put_contents($log_file, $content, FILE_APPEND | LOCK_EX);
    }

    /**
     * Waiver endpoint: allow admin to waive NIN requirement for a customer
     */
    public function waive(){
        if(!is_admin() && !special_access()){
            echo json_encode(['status'=>'error','message'=>'Permission denied.']);
            return;
        }
        $customer_id = $this->input->post('customer_id', TRUE);
        if(empty($customer_id)){
            echo json_encode(['status'=>'error','message'=>'Customer ID required.']);
            return;
        }
        $this->db->where('id', $customer_id)->update('db_customers', [
            'nin_waived' => 1,
            'nin_waived_by' => $this->session->userdata('inv_username'),
            'nin_waived_at' => date('Y-m-d H:i:s')
        ]);
        echo json_encode(['status'=>'success','message'=>'NIN requirement waived for this customer.']);
    }

    /**
     * AJAX endpoint: check if a customer is PayPlan-eligible
     */
    public function check_bnpl_eligible(){
        $customer_id = $this->input->post('customer_id', TRUE);
        if(empty($customer_id)){
            echo json_encode(['eligible'=>false,'reason'=>'No customer selected.']);
            return;
        }

        // Walk-in customer check
        if(is_walk_in_customer($customer_id)){
            echo json_encode(['eligible'=>false,'reason'=>'Walk-in customers are not eligible for PayPlan. Please create a registered customer.']);
            return;
        }

        $customer = get_customer_details($customer_id);
        if(!$customer){
            echo json_encode(['eligible'=>false,'reason'=>'Customer not found.']);
            return;
        }

        // Check if waived
        if(!empty($customer->nin_waived)){
            echo json_encode(['eligible'=>true,'reason'=>'NIN requirement waived.']);
            return;
        }

        // Check required fields
        if(empty($customer->customer_name)){
            echo json_encode(['eligible'=>false,'reason'=>'Customer name is required for PayPlan.']);
            return;
        }
        if(empty($customer->email)){
            echo json_encode(['eligible'=>false,'reason'=>'Customer email is required for PayPlan.']);
            return;
        }
        if(empty($customer->mobile)){
            echo json_encode(['eligible'=>false,'reason'=>'Customer phone number is required for PayPlan.']);
            return;
        }
        if(empty($customer->nin_bvn)){
            echo json_encode(['eligible'=>false,'reason'=>'Customer NIN/BVN is required for PayPlan.']);
            return;
        }
        if(empty($customer->nin_verified)){
            echo json_encode(['eligible'=>false,'reason'=>'Customer NIN/BVN is not verified.']);
            return;
        }

        echo json_encode(['eligible'=>true,'reason'=>'Customer is verified and eligible for PayPlan.']);
    }

    /**
     * Client Manager / Business Owner view: NIN usage summary
     * Simplified view without sensitive NIN numbers
     */
    public function usage(){
        $this->permission_check('nin_usage');
        $store_id = get_current_store_id();
        
        $data = $this->data;
        
        // Summary stats
        $data['total_count'] = $this->db->where('store_id', $store_id)->count_all_results('db_nin_verification_logs');
        $data['success_count'] = $this->db->where('store_id', $store_id)->where('status', 'success')->count_all_results('db_nin_verification_logs');
        $data['failed_count'] = $this->db->where('store_id', $store_id)->where('status !=', 'success')->count_all_results('db_nin_verification_logs');
        $cost_row = $this->db->select_sum('cost')->where('store_id', $store_id)->get('db_nin_verification_logs')->row();
        $data['total_cost'] = isset($cost_row->cost) ? $cost_row->cost : 0;
        $data['mock_count'] = $this->db->where('store_id', $store_id)->where('is_mock', 1)->count_all_results('db_nin_verification_logs');
        $data['live_count'] = $this->db->where('store_id', $store_id)->where('is_mock', 0)->count_all_results('db_nin_verification_logs');
        
        // Daily breakdown for current month
        $data['daily_breakdown'] = $this->db->query("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) as successful,
                SUM(cost) as daily_cost
            FROM db_nin_verification_logs
            WHERE store_id = ?
              AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ", [$store_id])->result();
        
        $data['page_title'] = 'NIN/BVN Usage Summary';
        $this->load->view('ninverify_usage', $data);
    }

    /**
     * Admin view: NIN verification usage log for billing transparency
     */
    public function log(){
        $this->permission_check('nin_logs');
        $store_id = get_current_store_id();
        
        $data = $this->data;
        
        $this->db->where('store_id', $store_id);
        $this->db->order_by('created_at', 'DESC');
        $data['logs'] = $this->db->get('db_nin_verification_logs')->result();
        
        // Summary stats
        $data['total_count'] = $this->db->where('store_id', $store_id)->count_all_results('db_nin_verification_logs');
        $data['success_count'] = $this->db->where('store_id', $store_id)->where('status', 'success')->count_all_results('db_nin_verification_logs');
        $cost_row = $this->db->select_sum('cost')->where('store_id', $store_id)->get('db_nin_verification_logs')->row();
        $data['total_cost'] = isset($cost_row->cost) ? $cost_row->cost : 0;
        $data['mock_count'] = $this->db->where('store_id', $store_id)->where('is_mock', 1)->count_all_results('db_nin_verification_logs');
        
        $data['page_title'] = 'NIN/BVN Verification Log';
        $this->load->view('ninverify_log', $data);
    }
}
