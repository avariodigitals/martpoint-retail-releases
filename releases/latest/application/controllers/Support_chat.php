<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Support Chat — MartPoint merchant ↔ Next.js support console bridge.
 *
 * Merchants (store owners) open this page from the PHP admin to chat with
 * the MartPoint support team. Messages are proxied server-to-server to the
 * Next.js Azera API (Supabase), where support staff see and reply from
 * /admin/(protected)/azera ("Azera Live Chat" console).
 *
 * Routes:
 *   support/chat                 — render the chat page
 *   support/chat/send            — proxy POST to /api/azera/chat
 *   support/chat/messages        — proxy GET from /api/azera/merchant/messages
 *
 * Config (config/support_chat.php or .env key SUPPORT_CHAT_NEXTJS_BASE):
 *   $config['support_chat_nextjs_base'] = 'https://martpoint.com.ng';
 */
class Support_chat extends MY_Controller {

    private $nextjsBase;

    public function __construct(){
        parent::__construct();
        $this->load_global();

        // Read Next.js base URL from config, fall back to production domain.
        $this->nextjsBase = rtrim(getenv('SUPPORT_CHAT_NEXTJS_BASE') ?: $this->config->item('support_chat_nextjs_base') ?: 'https://martpoint.com.ng', '/');
    }

    /**
     * Render the support chat page for the current merchant.
     */
    public function index(){
        list($storeId, $externalId) = $this->_chatIdentity();
        $store   = get_store_details($storeId);

        $data = array_merge($this->data, [
            'page_title'    => 'Support Chat',
            'storeId'       => $storeId,
            'storeName'     => htmlspecialchars($store->store_name ?? 'Store', ENT_QUOTES),
            'externalId'    => $externalId,
            'merchantName'  => htmlspecialchars($this->session->userdata('inv_username') ?: 'Merchant', ENT_QUOTES),
            'merchantEmail' => htmlspecialchars($this->session->userdata('email') ?: '', ENT_QUOTES),
            'nextjsBase'    => $this->nextjsBase,
        ]);
        $data['content'] = $this->load->view('support_chat', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    /**
     * Proxy: merchant sends a message → Next.js /api/azera/chat
     */
    public function send(){
        // The mobile/browser client sends JSON. Keep form POST support for
        // older clients, but decode JSON first when present.
        $payload = json_decode($this->input->raw_input_stream, TRUE);
        if(!is_array($payload)){
            $payload = $this->input->post(NULL, TRUE) ?: [];
        }

        list($storeId, $externalId) = $this->_chatIdentity();
        $store = $this->_chatStore();
        $name       = trim((string)($payload['customer_name'] ?? ''));
        $message    = trim((string)($payload['message'] ?? ''));

        if(!$storeId || !$externalId || !$message){
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'store_id, external_id and message are required']);
            return;
        }

        $response = $this->_proxyPost('/api/azera/chat', [
            'business_id'   => $storeId,
            'external_id'   => $externalId,
            'customer_name' => $name,
            'source'        => 'retail_app',
            'store_email'   => trim((string)(($store->email ?? '') ?: $this->session->userdata('email'))),
            'store_phone'   => trim((string)(($store->mobile ?? '') ?: ($store->phone ?? ''))),
            'store_name'    => trim((string)($store->store_name ?? '')),
            'conversation_token' => $this->_chatToken(),
            'message'       => $message,
        ]);
        $this->_rememberChatToken($response);
        echo $response;
    }

    /**
     * Proxy: merchant fetches their own messages → Next.js /api/azera/merchant/messages
     */
    public function messages(){
        list($storeId, $externalId) = $this->_chatIdentity();

        if(!$storeId || !$externalId){
            http_response_code(400);
            echo json_encode(['status'=>'error','message'=>'store_id and external_id are required']);
            return;
        }

        $url = $this->nextjsBase . '/api/azera/merchant/messages'
             . '?business_id=' . urlencode($storeId)
             . '&external_id=' . urlencode($externalId);

        $response = $this->_proxyGet($url);
        $this->_rememberChatToken($response);
        echo $response;
    }

    /**
     * Proxy: merchant ends their support conversation.
     */
    public function end(){
        $payload = json_decode($this->input->raw_input_stream, TRUE);
        if(!is_array($payload)){
            $payload = $this->input->post(NULL, TRUE) ?: [];
        }

        list($storeId, $externalId) = $this->_chatIdentity();
        if(!$storeId || !$externalId){
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['status'=>'error','message'=>'store_id and external_id are required']);
            return;
        }

        echo $this->_proxyPost('/api/azera/merchant/end', [
            'business_id' => $storeId,
            'external_id' => $externalId,
            'conversation_token' => $this->_chatToken(),
        ]);
    }

    /** Proxy merchant voice/file uploads to Next.js without exposing Supabase. */
    public function upload(){
        list($storeId, $externalId) = $this->_chatIdentity();
        $messageType = trim((string)$this->input->post('message_type', TRUE) ?: 'file');
        $file = $_FILES['file'] ?? null;

        if(!$storeId || !$externalId || !$file || (int)$file['error'] !== UPLOAD_ERR_OK){
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['status'=>'error','message'=>'A valid file is required']);
            return;
        }

        $ch = curl_init($this->nextjsBase . '/api/azera/merchant/upload');
        $body = [
            'business_id' => (string)$storeId,
            'external_id' => $externalId,
            'conversation_token' => $this->_chatToken(),
            'message_type' => $messageType,
            'file' => new CURLFile($file['tmp_name'], $file['type'] ?: 'application/octet-stream', $file['name']),
        ];
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_bridgeHeaders());
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        http_response_code($httpCode ?: 200);
        header('Content-Type: application/json');
        echo $response ?: json_encode(['status'=>'error','message'=>'We could not upload that file right now. Please try again shortly.']);
    }

    /* ------------------------------------------------------------------ */
    /* Proxy helpers                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * Bind every bridge request to the authenticated merchant session.
     * Browser-supplied store_id/external_id values are deliberately ignored
     * so a merchant cannot use the proxy to query another store's thread.
     */
    private function _chatIdentity(){
        $storeId = (int)get_current_store_id();
        $externalId = trim((string)$this->session->userdata('support_chat_external_id'));
        if(!$externalId){
            $externalId = 'web_' . bin2hex(random_bytes(12));
            $this->session->set_userdata('support_chat_external_id', $externalId);
        }
        return [$storeId, $externalId];
    }

    private function _chatStore(){
        return get_store_details((int)get_current_store_id());
    }

    private function _chatToken(){
        return trim((string)$this->session->userdata('support_chat_token'));
    }

    private function _rememberChatToken($response){
        $decoded = json_decode((string)$response, TRUE);
        $token = $decoded['data']['conversation_token'] ?? '';
        if($token){
            $this->session->set_userdata('support_chat_token', $token);
        }
    }

    private function _bridgeHeaders($contentType = NULL){
        $secret = getenv('AZERA_PHP_BRIDGE_SECRET') ?: $this->config->item('support_chat_bridge_secret');
        $headers = [];
        if($contentType){
            $headers[] = 'Content-Type: '.$contentType;
        }
        if($secret){
            $headers[] = 'X-MartPoint-Chat-Bridge: '.$secret;
        }
        $storeId = (int)get_current_store_id();
        $store = $this->_chatStore();
        if($storeId > 0){
            $headers[] = 'X-MartPoint-Store-Id: '.$storeId;
            if($store){
                $headers[] = 'X-MartPoint-Store-Email: '.trim((string)($store->email ?? ''));
                $headers[] = 'X-MartPoint-Store-Phone: '.trim((string)(($store->mobile ?? '') ?: ($store->phone ?? '')));
                $headers[] = 'X-MartPoint-Store-Name: '.trim((string)($store->store_name ?? ''));
            }
        }
        if($this->_chatToken()){
            $headers[] = 'X-Azera-Chat-Token: '.$this->_chatToken();
        }
        return $headers;
    }

    private function _proxyPost(string $path, array $body){
        $ch = curl_init($this->nextjsBase . $path);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_bridgeHeaders('application/json'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        http_response_code($httpCode ?: 200);
        header('Content-Type: application/json');
        if($response === false){
            return json_encode(['status'=>'error','message'=>'Cannot reach Next.js chat API: '.$curlError]);
        }
        return $response ?: json_encode(['status'=>'error','message'=>'Empty response from Next.js chat API']);
    }

    private function _proxyGet(string $url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_bridgeHeaders());
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        http_response_code($httpCode ?: 200);
        header('Content-Type: application/json');
        if($response === false){
            return json_encode(['status'=>'error','message'=>'Cannot reach Next.js chat API: '.$curlError]);
        }
        return $response ?: json_encode(['status'=>'error','message'=>'Empty response from Next.js chat API']);
    }

}
