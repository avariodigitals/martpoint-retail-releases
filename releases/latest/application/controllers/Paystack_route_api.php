<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Fleet-scoped authentication and Central relay for Paystack payout routes. */
class Paystack_route_api extends MY_Controller {
    public function __construct() {
        parent::__construct();
        if (!mp_is_central()) show_404();
        $this->load->library('messaging_pricing');
    }

    public function register() {
        try {
            if (strtoupper($this->input->method()) !== 'POST') throw new RuntimeException('POST is required.');
            $installUrl = (string)$this->input->post('install_url');
            $timestamp = (string)$this->input->post('timestamp');
            $rawPayload = (string)$this->input->post('payload');
            $signature = (string)$this->input->post('signature');
            if (!ctype_digit($timestamp) || abs(time() - (int)$timestamp) > 300 || strlen($rawPayload) > 4000
                || !preg_match('/^[a-f0-9]{64}$/D', $signature) || !$this->db->table_exists('db_fleet_installs')) {
                throw new RuntimeException('Fleet authentication failed.');
            }

            $fleet = $this->db->where('install_url', $installUrl)->get('db_fleet_installs')->row_array();
            if (!$fleet || strlen($fleet['install_key'] ?? '') < 32
                || !hash_equals(Messaging_pricing::signature('paystack_payout_route_register', $timestamp, $installUrl, $rawPayload, $fleet['install_key']), $signature)) {
                throw new RuntimeException('Fleet authentication failed.');
            }

            $payload = json_decode($rawPayload, true);
            if (!is_array($payload) || !is_int($payload['store_id'] ?? null) || $payload['store_id'] < 1
                || !is_string($payload['subaccount_code'] ?? null) || !preg_match('/^ACCT_[A-Za-z0-9]+$/D', $payload['subaccount_code'])
                || !is_bool($payload['enabled'] ?? null)) {
                throw new RuntimeException('Invalid payout route request.');
            }

            $this->load->library('central_billing_client');
            if (!$this->central_billing_client->ready()) throw new RuntimeException('Central-to-Next route relay is unavailable.');
            $this->central_billing_client->post('payout-subaccount', array(
                'fleet_install_id' => (int)$fleet['id'],
                'install_url' => rtrim((string)$fleet['install_url'], '/'),
                'store_id' => $payload['store_id'],
                'subaccount_code' => $payload['subaccount_code'],
                'enabled' => $payload['enabled'],
            ));

            return $this->respond(array('success' => true, 'message' => 'Payout route registered.'));
        } catch (Throwable $e) {
            log_message('error', 'Fleet Paystack payout route relay failed: ' . mb_substr(strip_tags($e->getMessage()), 0, 300));
            return $this->respond(array('success' => false, 'message' => 'Central could not confirm the payout route. Check the Central error log.'));
        }
    }

    /** Return fleet-safe Paystack settings; secret keys never leave Central. */
    public function settings() {
        try {
            $payload = $this->verifiedFleetRequest('settings', 2000);
            $storeId = (int)($payload['store_id'] ?? 0);
            if ($storeId < 1) throw new RuntimeException('Invalid store.');
            $this->load->model('paystack_model', 'paystack');
            $settings = $this->paystack->get_settings($storeId);
            if (!$settings) return $this->respond(array('success'=>true, 'settings'=>array('configured'=>false,'enabled'=>0,'test_mode'=>1)));
            $safe = array('configured'=>!empty($settings->secret_key) && !empty($settings->public_key));
            foreach (array('public_key','enabled','test_mode','platform_fee_type','platform_fee_value',
                'platform_fee_percent','platform_fee_fixed','fee_bearer','callback_url') as $key) {
                if (isset($settings->$key)) $safe[$key] = $settings->$key;
            }
            return $this->respond(array('success'=>true, 'settings'=>$safe));
        } catch (Throwable $e) {
            log_message('error', 'Paystack fleet settings relay failed: '.mb_substr(strip_tags($e->getMessage()), 0, 300));
            return $this->output->set_status_header(503)->set_content_type('application/json')
                ->set_header('Cache-Control', 'no-store')
                ->set_output(json_encode(array('success'=>false, 'message'=>'Central Paystack settings are unavailable.')));
        }
    }

    /** Use Central's Paystack account for an allowlisted provider operation. */
    public function request() {
        try {
            $payload = $this->verifiedFleetRequest('request', 20000);
            $storeId = (int)($payload['store_id'] ?? 0);
            $method = strtoupper((string)($payload['method'] ?? ''));
            $endpoint = (string)($payload['endpoint'] ?? '');
            if ($storeId < 1 || !$this->allowedProviderRequest($method, $endpoint)
                || (!is_null($payload['data'] ?? null) && !is_array($payload['data']))) {
                throw new RuntimeException('Invalid Paystack request.');
            }
            $this->load->model('paystack_model', 'paystack');
            $result = $this->paystack->central_api_request($method, $endpoint, $payload['data'] ?? null, $storeId);
            return $this->respond(array(
                'success'=>true,
                'status'=>!empty($result['status']),
                'data'=>$result['data'] ?? array(),
                'raw'=>$result['raw'] ?? array(),
                'message'=>$result['message'] ?? null,
            ));
        } catch (Throwable $e) {
            log_message('error', 'Paystack fleet provider relay failed: '.mb_substr(strip_tags($e->getMessage()), 0, 300));
            return $this->output->set_status_header(503)->set_content_type('application/json')
                ->set_header('Cache-Control', 'no-store')
                ->set_output(json_encode(array('success'=>false, 'message'=>'Central could not process this MartPoint Pay request.')));
        }
    }

    private function verifiedFleetRequest($action, $maxBytes) {
        if (strtoupper($this->input->method()) !== 'POST') throw new RuntimeException('POST is required.');
        $installUrl = (string)$this->input->post('install_url');
        $timestamp = (string)$this->input->post('timestamp');
        $rawPayload = (string)$this->input->post('payload');
        $signature = (string)$this->input->post('signature');
        if (!ctype_digit($timestamp) || abs(time() - (int)$timestamp) > 300 || strlen($rawPayload) > $maxBytes
            || !preg_match('/^[a-f0-9]{64}$/D', $signature) || !$this->db->table_exists('db_fleet_installs')) {
            throw new RuntimeException('Fleet authentication failed.');
        }
        $fleet = $this->db->where('install_url', $installUrl)->get('db_fleet_installs')->row_array();
        if (!$fleet || strlen($fleet['install_key'] ?? '') < 32
            || !hash_equals(Messaging_pricing::signature('paystack_'.$action, $timestamp, $installUrl, $rawPayload, $fleet['install_key']), $signature)) {
            throw new RuntimeException('Fleet authentication failed.');
        }
        $payload = json_decode($rawPayload, true);
        if (!is_array($payload)) throw new RuntimeException('Invalid fleet payload.');
        return $payload;
    }

    private function allowedProviderRequest($method, $endpoint) {
        if ($method === 'GET') {
            return $endpoint === '/bank?country=nigeria&perPage=100'
                || preg_match('#^/bank/resolve\?account_number=\d{10}&bank_code=[A-Za-z0-9_-]{1,30}$#D', $endpoint)
                || preg_match('#^/subaccount/ACCT_[A-Za-z0-9]+$#D', $endpoint)
                || preg_match('#^/transaction/verify/[A-Za-z0-9_-]{1,120}$#D', $endpoint)
                || preg_match('#^/refund\?reference=[A-Za-z0-9_-]{1,120}$#D', $endpoint);
        }
        if ($method === 'POST') return in_array($endpoint, array('/transaction/initialize','/subaccount','/refund'), true);
        if ($method === 'PUT') return preg_match('#^/subaccount/ACCT_[A-Za-z0-9]+$#D', $endpoint);
        return false;
    }

    /** Relay the Central-owned payout account visibility/edit policy to a fleet. */
    public function policy() {
        try {
            if (strtoupper($this->input->method()) !== 'POST') throw new RuntimeException('POST is required.');
            $installUrl = (string)$this->input->post('install_url');
            $timestamp = (string)$this->input->post('timestamp');
            $rawPayload = (string)$this->input->post('payload');
            $signature = (string)$this->input->post('signature');
            if (!ctype_digit($timestamp) || abs(time() - (int)$timestamp) > 300 || strlen($rawPayload) > 2000
                || !preg_match('/^[a-f0-9]{64}$/D', $signature) || !$this->db->table_exists('db_fleet_installs')) {
                throw new RuntimeException('Fleet authentication failed.');
            }
            $fleet = $this->db->where('install_url', $installUrl)->get('db_fleet_installs')->row_array();
            if (!$fleet || strlen($fleet['install_key'] ?? '') < 32
                || !hash_equals(Messaging_pricing::signature('paystack_payout_account_policy', $timestamp, $installUrl, $rawPayload, $fleet['install_key']), $signature)) {
                throw new RuntimeException('Fleet authentication failed.');
            }
            $payload = json_decode($rawPayload, true);
            if (!is_array($payload) || !is_int($payload['store_id'] ?? null) || $payload['store_id'] < 1) {
                throw new RuntimeException('Invalid payout policy request.');
            }
            $this->load->library('central_billing_client');
            $policy = $this->central_billing_client->post('payout-policy', array(
                'fleet_install_id'=>(int)$fleet['id'],
                'install_url'=>rtrim((string)$fleet['install_url'], '/'),
                'store_id'=>$payload['store_id'],
            ));
            return $this->respond(array('success'=>true, 'allow_payout_account_edit'=>!empty($policy['allow_payout_account_edit'])));
        } catch (Throwable $e) {
            log_message('error', 'Paystack payout edit policy lookup failed: '.mb_substr(strip_tags($e->getMessage()), 0, 300));
            return $this->output->set_status_header(503)->set_content_type('application/json')
                ->set_header('Cache-Control', 'no-store')
                ->set_output(json_encode(array('success'=>false, 'message'=>'Central payout account policy is unavailable.')));
        }
    }

    private function respond(array $payload) {
        return $this->output->set_content_type('application/json')
            ->set_header('Cache-Control: no-store')
            ->set_output(json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE));
    }
}
