<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Brevo_model extends CI_Model {

    private $apiBase = 'https://api.brevo.com/v3/transactionalSMS/sms';

    public function index($mobile, $message)
    {
        return $this->sendSMS($mobile, $message);
    }

    protected function sendSMS($mobile, $message) {
        $store_id = get_current_store_id();
        $q1 = $this->db->select("*")->where('store_id', $store_id)->get("db_brevo");
        if($q1->num_rows() == 0){
            return "Invalid Brevo API Details!";
        }

        $api_key     = $q1->row()->api_key;
        $sender_name = $q1->row()->sender_name;

        if(empty($api_key) || empty($sender_name)){
            return "Invalid Brevo API Details!";
        }

        // Clean mobile number
        $mobile = preg_replace('/[^0-9+]/', '', $mobile);
        if(substr($mobile, 0, 1) !== '+'){
            $mobile = '+' . $mobile;
        }

        $payload = json_encode([
            'sender'    => $sender_name,
            'recipient' => $mobile,
            'content'   => $message,
            'type'      => 'transactional'
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiBase);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $api_key,
            'content-type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if(curl_error($ch)){
            log_message('error', 'Brevo SMS cURL error: ' . curl_error($ch));
            curl_close($ch);
            return 'failed';
        }

        curl_close($ch);

        if($httpCode >= 200 && $httpCode < 300){
            return 'success';
        } else {
            log_message('error', 'Brevo SMS failed. HTTP ' . $httpCode . ' Response: ' . $response);
            return 'failed';
        }
    }

}
