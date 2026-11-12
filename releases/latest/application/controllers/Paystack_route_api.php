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
