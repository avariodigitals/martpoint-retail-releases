<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Meta Messaging Webhooks
 *
 * Receives webhook callbacks from Meta for WhatsApp Business API, Instagram
 * Direct and Facebook Messenger:
 *
 *   • GET  — webhook verification (hub.challenge)
 *   • POST — inbound messages, statuses (sent/delivered/read), etc.
 *
 * The verify token must match whatsapp_webhook_verify_token stored in
 * db_storefront_settings. Each store shares one endpoint; the channel is
 * determined from the entry/object type in the payload.
 *
 * This controller intentionally bypasses MY_Controller auth so Meta's
 * servers can reach it. It validates the verify token on GET and verifies
 * the X-Hub-Signature on POST where a secret is configured.
 */
class Messaging_webhooks extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('storefront_model');
    }

    /**
     * Webhook endpoint: /messaging/webhook
     */
    public function index(){
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            return $this->_verify();
        }
        return $this->_incoming();
    }

    /* ------------------------------------------------------------------ */
    /* Verification                                                       */
    /* ------------------------------------------------------------------ */

    private function _verify(){
        $mode      = $this->input->get('hub_mode');
        $challenge = $this->input->get('hub_challenge');
        $token     = $this->input->get('hub_verify_token');

        $store = $this->_storeByVerifyToken($token);
        if(!$store || !$challenge){
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if($mode === 'subscribe' && $token === $store->whatsapp_webhook_verify_token){
            http_response_code(200);
            echo $challenge;
        } else {
            http_response_code(403);
            echo 'Forbidden';
        }
    }

    /* ------------------------------------------------------------------ */
    /* Inbound events                                                     */
    /* ------------------------------------------------------------------ */

    private function _incoming(){
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if(!is_array($payload)){
            http_response_code(400);
            echo 'Invalid JSON';
            return;
        }

        // Optional signature verification against the app secret if configured.
        $signature = $this->input->get_request_header('X-Hub-Signature-256');
        if($signature && !$this->_validateSignature($raw, $signature)){
            http_response_code(401);
            echo 'Invalid signature';
            return;
        }

        $store = $this->_findStore($payload);
        if(!$store){
            // Not for us — still acknowledge so Meta stops retrying.
            http_response_code(200);
            echo 'ignored';
            return;
        }

        $this->_handlePayload($store, $payload);

        http_response_code(200);
        echo 'ok';
    }

    /* ------------------------------------------------------------------ */
    /* Store resolution                                                   */
    /* ------------------------------------------------------------------ */

    /**
     * Resolve the store from a webhook payload. WhatsApp webhooks do not
     * include a store id, so we match on the phone number ID / recipient
     * against each store's stored whatsapp_phone_number_id. Instagram and
     * Messenger payloads include a page/id we can match on facebook_page_id.
     */
    private function _findStore(array $payload){
        $this->db->select('id, store_id, whatsapp_phone_number_id, facebook_page_id, whatsapp_webhook_verify_token');
        $this->db->where('whatsapp_api_enabled', 1);
        $rows = $this->db->get('db_storefront_settings')->result();

        $entries = $payload['entry'] ?? [];
        foreach($entries as $entry){
            $changes = $entry['changes'] ?? [];
            $messaging = $entry['messaging'] ?? [];
            $messages  = $entry['messages'] ?? [];

            $recipientId = null;
            if(!empty($messages[0])){
                $recipientId = $messages[0]['to'] ?? null;
            } elseif(!empty($messaging[0])){
                $recipientId = $messaging[0]['recipient']['id'] ?? null;
            } elseif(!empty($changes[0])){
                $recipientId = $changes[0]['value']['metadata']['phone_number_id'] ?? null;
            }

            foreach($rows as $row){
                if(!empty($recipientId)){
                    if($recipientId == ($row->whatsapp_phone_number_id ?? '')
                        || $recipientId == ($row->facebook_page_id ?? '')){
                        return $row;
                    }
                }
            }
        }
        return null;
    }

    private function _storeByVerifyToken($token){
        if(!$token) return null;
        $this->db->where('whatsapp_webhook_verify_token', $token);
        return $this->db->get('db_storefront_settings')->row();
    }

    /* ------------------------------------------------------------------ */
    /* Payload handling                                                   */
    /* ------------------------------------------------------------------ */

    private function _handlePayload($store, array $payload){
        $CI =& get_instance();
        $CI->load->model('storefront_model');

        $entries = $payload['entry'] ?? [];
        foreach($entries as $entry){
            // WhatsApp: entry.changes[].value.messages / statuses
            foreach($entry['changes'] ?? [] as $change){
                $value = $change['value'] ?? [];
                $channel = 'whatsapp';
                if(!empty($value['messages'])){
                    $this->_storeMessages($CI, $store, $channel, $value['messages'], $value);
                }
                if(!empty($value['statuses'])){
                    $this->_storeStatuses($CI, $store, $channel, $value['statuses']);
                }
            }
            // Instagram / Messenger: entry.messaging[]
            foreach($entry['messaging'] ?? [] as $event){
                $channel = isset($event['message']) || isset($event['postback'])
                    ? (isset($event['recipient']['id']) && is_numeric($event['recipient']['id']) ? 'messenger' : 'instagram')
                    : 'instagram';
                $this->_storeMessagingEvent($CI, $store, $channel, $event);
            }
        }
    }

    private function _storeMessages($CI, $store, $channel, array $messages, array $meta){
        foreach($messages as $m){
            $from = $m['from'] ?? null;
            $msgId = $m['id'] ?? null;
            $timestamp = !empty($m['timestamp']) ? date('Y-m-d H:i:s', $m['timestamp']) : date('Y-m-d H:i:s');
            $type = 'text';
            $body = $m['text']['body'] ?? null;
            if(!empty($m['image'])){ $type='image'; $body = $m['image']['caption'] ?? '(image)'; }
            if(!empty($m['document'])){ $type='document'; $body = $m['document']['filename'] ?? '(document)'; }

            $convId = $this->_ensureConversation($CI, $store->id, $channel, $from, $m['from'] ?? null);
            $CI->db->insert('db_messaging_messages', [
                'conversation_id' => $convId,
                'direction' => 'inbound',
                'message_type' => $type,
                'body' => $body,
                'meta_message_id' => $msgId,
                'status' => 'delivered',
                'created_at' => $timestamp,
            ]);
            $CI->db->where('id', $convId)->update('db_messaging_conversations', ['last_message_at' => $timestamp]);
        }
    }

    private function _storeMessagingEvent($CI, $store, $channel, array $event){
        $sender = $event['sender']['id'] ?? null;
        $recipient = $event['recipient']['id'] ?? null;
        $timestamp = !empty($event['timestamp']) ? date('Y-m-d H:i:s', $event['timestamp'] / 1000) : date('Y-m-d H:i:s');
        $type = 'text';
        $body = null;
        if(!empty($event['message'])){
            $body = $event['message']['text'] ?? null;
            if(!empty($event['message']['attachments'])){
                $attach = $event['message']['attachments'][0] ?? [];
                $type = $attach['type'] ?? 'file';
                $body = $attach['payload']['url'] ?? '(attachment)';
            }
        } elseif(!empty($event['postback'])){
            $body = $event['postback']['payload'] ?? '(postback)';
        }

        $convId = $this->_ensureConversation($CI, $store->id, $channel, $sender, $recipient);
        $CI->db->insert('db_messaging_messages', [
            'conversation_id' => $convId,
            'direction' => 'inbound',
            'message_type' => $type,
            'body' => $body,
            'meta_message_id' => null,
            'status' => 'delivered',
            'created_at' => $timestamp,
        ]);
        $CI->db->where('id', $convId)->update('db_messaging_conversations', ['last_message_at' => $timestamp]);
    }

    private function _storeStatuses($CI, $store, $channel, array $statuses){
        foreach($statuses as $s){
            $mid = $s['id'] ?? null;
            $status = $s['status'] ?? null; // sent, delivered, read, failed
            $timestamp = !empty($s['timestamp']) ? date('Y-m-d H:i:s', $s['timestamp']) : date('Y-m-d H:i:s');
            if($mid){
                $CI->db->where('meta_message_id', $mid)
                    ->update('db_messaging_messages', ['status' => $status]);
            }
        }
    }

    private function _ensureConversation($CI, $storeId, $channel, $externalId, $recipientId = null){
        $this->db->where('store_id', $storeId)
                 ->where('channel', $channel)
                 ->where('external_conversation_id', $externalId)
        ;
        $row = $this->db->get('db_messaging_conversations')->row();
        if($row) return (int)$row->id;

        $phone = null;
        $name = null;
        if($channel === 'whatsapp' && $externalId){
            $phone = $externalId;
        }

        $this->db->insert('db_messaging_conversations', [
            'store_id' => $storeId,
            'channel' => $channel,
            'external_conversation_id' => $externalId,
            'customer_phone' => $phone,
            'customer_name' => $name,
            'status' => 'open',
            'last_message_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insert_id();
    }

    /* ------------------------------------------------------------------ */
    /* Signature check                                                    */
    /* ------------------------------------------------------------------ */

    private function _validateSignature($raw, $signature){
        $appSecret = $this->config->item('meta_app_secret');
        if(!$appSecret) return true; // not configured — skip
        $algo = 'sha256';
        $expected = $algo . '=' . hash_hmac($algo, $raw, $appSecret);
        return hash_equals($expected, $signature);
    }

}
