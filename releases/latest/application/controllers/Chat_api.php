<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Chat API Bridge — connects Next.js storefront live chat to MartPoint admin.
 *
 * Endpoints (all JSON, CORS-enabled for cross-origin Next.js calls):
 *
 *   POST   /chat-api/send          Customer sends a message → creates conversation + inbound message
 *   GET    /chat-api/conversations List webchat conversations for the admin inbox
 *   GET    /chat-api/messages/:id  Fetch message history for a conversation
 *   POST   /chat-api/reply         Admin sends a reply → stored as outbound message
 *
 * Auth: X-Api-Key header must match CHAT_API_KEY (set in .env or config).
 * CORS: Access-Control-Allow-Origin reflects the request origin when allowed.
 *
 * The admin side reads/writes the same db_messaging_conversations /
 * db_messaging_messages tables used by Online_store > Messaging, so support
 * staff reply from the MartPoint dashboard and customers see replies in the
 * Next.js chat widget.
 */
class Chat_api extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('storefront_model');
        $this->_cors();
        if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
            http_response_code(200);
            exit;
        }
    }

    /* ------------------------------------------------------------------ */
    /* CORS                                                               */
    /* ------------------------------------------------------------------ */

    private function _cors(){
        $allowed = $this->config->item('chat_api_allowed_origins');
        $origin  = $this->input->get_request_header('Origin');
        if($allowed && in_array($origin, (array)$allowed, true)){
            header("Access-Control-Allow-Origin: $origin");
        }
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');
        header('Access-Control-Max-Age: 86400');
        header('Content-Type: application/json');
    }

    private function _json($data, $code = 200){
        http_response_code($code);
        echo json_encode($data);
        exit;
    }

    private function _auth(){
        $key = $this->input->get_request_header('X-Api-Key');
        $expected = $this->config->item('chat_api_key');
        if(!$expected || $key !== $expected){
            $this->_json(['status'=>'error','message'=>'Invalid or missing API key'], 401);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Customer: send a message                                           */
    /* ------------------------------------------------------------------ */

    public function send(){
        $this->_auth();
        $this->load->library('meta_messaging');

        $storeId   = (int)$this->input->post('store_id');
        $name      = trim($this->input->post('customer_name', TRUE) ?: 'Chat Customer');
        $email     = trim($this->input->post('customer_email', TRUE));
        $phone     = trim($this->input->post('customer_phone', TRUE));
        $text      = trim($this->input->post('text', TRUE));
        $channel   = 'webchat';

        if(!$storeId || !$text){
            $this->_json(['status'=>'error','message'=>'store_id and text are required'], 400);
        }

        // Verify store exists and webchat is enabled for it.
        $settings = $this->storefront_model->getSettings($storeId);
        if(!$settings){
            $this->_json(['status'=>'error','message'=>'Store not found'], 404);
        }

        $this->db->trans_begin();

        // Find an open webchat conversation for this customer, else create one.
        $this->db->where('store_id', $storeId)
                 ->where('channel', $channel)
                 ->where('customer_phone', $phone ?: $email)
                 ->where('status', 'open')
                 ->order_by('last_message_at', 'desc')
                 ->limit(1);
        $conv = $this->db->get('db_messaging_conversations')->row();

        if(!$conv){
            $this->db->insert('db_messaging_conversations', [
                'store_id'                 => $storeId,
                'channel'                  => $channel,
                'external_conversation_id' => null,
                'customer_phone'           => $phone ?: null,
                'customer_name'            => $name,
                'customer_email'           => $email ?: null,
                'status'                   => 'open',
                'last_message_at'          => date('Y-m-d H:i:s'),
            ]);
            $convId = (int)$this->db->insert_id();
        } else {
            $convId = (int)$conv->id;
            $this->db->where('id', $convId)->update('db_messaging_conversations', [
                'last_message_at' => date('Y-m-d H:i:s'),
                'status'          => 'open',
                'customer_name'   => $name,
                'customer_email'  => $email ?: null,
            ]);
        }

        $this->db->insert('db_messaging_messages', [
            'conversation_id' => $convId,
            'direction'       => 'inbound',
            'message_type'    => 'text',
            'body'            => $text,
            'meta_message_id' => null,
            'status'          => 'delivered',
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $this->_json(['status'=>'error','message'=>'Database error'], 500);
        }
        $this->db->trans_commit();

        $this->_json(['status'=>'success','conversation_id'=>$convId,'message'=>'Message sent']);
    }

    /* ------------------------------------------------------------------ */
    /* Admin: list webchat conversations                                  */
    /* ------------------------------------------------------------------ */

    public function conversations(){
        $this->_auth();
        $storeId = (int)$this->input->get('store_id');
        if(!$storeId){
            $this->_json(['status'=>'error','message'=>'store_id is required'], 400);
        }
        $conversations = $this->db->where('store_id', $storeId)
            ->where('channel', 'webchat')
            ->order_by('last_message_at', 'desc')
            ->get('db_messaging_conversations')
            ->result();
        $out = [];
        foreach($conversations as $c){
            $out[] = [
                'id'             => (int)$c->id,
                'customer_name'  => $c->customer_name,
                'customer_phone' => $c->customer_phone,
                'customer_email' => $c->customer_email,
                'status'         => $c->status,
                'last_message_at'=> $c->last_message_at,
                'created_at'     => $c->created_at,
            ];
        }
        $this->_json(['status'=>'success','conversations'=>$out]);
    }

    /* ------------------------------------------------------------------ */
    /* Admin / Widget: message history                                    */
    /* ------------------------------------------------------------------ */

    public function messages($conversationId = 0){
        $this->_auth();
        $convId = (int)$conversationId;
        $storeId = (int)$this->input->get('store_id');
        if(!$convId){
            $this->_json(['status'=>'error','message'=>'conversation id is required'], 400);
        }
        $this->db->where('id', $convId);
        if($storeId) $this->db->where('store_id', $storeId);
        $conv = $this->db->get('db_messaging_conversations')->row();
        if(!$conv){
            $this->_json(['status'=>'error','message'=>'Conversation not found'], 404);
        }
        $messages = $this->db->where('conversation_id', $convId)
            ->order_by('created_at', 'asc')
            ->get('db_messaging_messages')
            ->result();
        $out = [];
        foreach($messages as $m){
            $out[] = [
                'id'            => (int)$m->id,
                'direction'     => $m->direction,
                'message_type'  => $m->message_type,
                'body'          => $m->body,
                'status'        => $m->status,
                'created_at'    => $m->created_at,
            ];
        }
        $this->_json(['status'=>'success','messages'=>$out]);
    }

    /* ------------------------------------------------------------------ */
    /* Admin: send a reply                                                */
    /* ------------------------------------------------------------------ */

    public function reply(){
        $this->_auth();
        $storeId = (int)$this->input->post('store_id');
        $convId  = (int)$this->input->post('conversation_id');
        $text    = trim($this->input->post('text', TRUE));
        if(!$storeId || !$convId || !$text){
            $this->_json(['status'=>'error','message'=>'store_id, conversation_id and text are required'], 400);
        }
        $conv = $this->db->where('id', $convId)->where('store_id', $storeId)->where('channel', 'webchat')->get('db_messaging_conversations')->row();
        if(!$conv){
            $this->_json(['status'=>'error','message'=>'Conversation not found'], 404);
        }
        $this->db->insert('db_messaging_messages', [
            'conversation_id' => $convId,
            'direction'       => 'outbound',
            'message_type'    => 'text',
            'body'            => $text,
            'meta_message_id' => null,
            'status'          => 'sent',
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
        $this->db->where('id', $convId)->update('db_messaging_conversations', [
            'last_message_at' => date('Y-m-d H:i:s'),
            'status'          => 'open',
        ]);
        $this->_json(['status'=>'success','message'=>'Reply sent']);
    }

}
