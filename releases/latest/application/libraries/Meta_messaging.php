<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Meta Messaging library
 *
 * Sends outbound messages and receives webhook events across the three Meta
 * messaging channels MartPoint supports:
 *
 *   • WhatsApp Business API (WhatsApp Platform) — cloud API
 *   • Instagram Direct (Instagram Graph API)
 *   • Facebook Messenger (Messenger Platform)
 *
 * Configuration lives in db_storefront_settings per store:
 *   whatsapp_api_token, whatsapp_phone_number_id,
 *   whatsapp_webhook_verify_token, whatsapp_api_enabled,
 *   facebook_page_access_token, facebook_page_id,
 *   instagram_business_account_id, instagram_access_token
 *
 * This library never writes to the DB itself — controllers/models do that.
 * It only calls Meta's Graph API and returns the raw decoded responses so the
 * caller can log conversations, handle errors and render receipts.
 */
class Meta_messaging {

    protected $CI;
    protected $storeId;
    protected $settings = [];
    protected $graphVersion = 'v20.0';

    public function __construct($storeId = null){
        $this->CI =& get_instance();
        $this->CI->load->model('storefront_model');
        $this->storeId = $storeId ?: get_current_store_id();
        $this->settings = $this->CI->storefront_model->getSettings($this->storeId);
    }

    /* ------------------------------------------------------------------ */
    /* Capability checks                                                  */
    /* ------------------------------------------------------------------ */

    public function isWhatsappEnabled(){
        return !empty($this->settings->whatsapp_api_enabled)
            && !empty($this->settings->whatsapp_api_token)
            && !empty($this->settings->whatsapp_phone_number_id);
    }

    public function isMessengerEnabled(){
        return !empty($this->settings->facebook_page_access_token)
            && !empty($this->settings->facebook_page_id);
    }

    public function isInstagramEnabled(){
        return !empty($this->settings->instagram_access_token)
            && !empty($this->settings->instagram_business_account_id);
    }

    public function channels(){
        $out = [];
        if($this->isWhatsappEnabled()) $out[] = 'whatsapp';
        if($this->isInstagramEnabled()) $out[] = 'instagram';
        if($this->isMessengerEnabled()) $out[] = 'messenger';
        return $out;
    }

    /* ------------------------------------------------------------------ */
    /* Low-level Graph API caller                                         */
    /* ------------------------------------------------------------------ */

    /**
     * POST JSON to a Meta Graph API endpoint.
     * Returns ['ok'=>bool, 'data'=>array|string, 'error'=>string|null, 'http_code'=>int]
     */
    private function _post($path, array $body, $accessToken = null){
        $token = $accessToken ?: $this->_token();
        $url = 'https://graph.facebook.com/' . $this->graphVersion . '/' . ltrim($path, '/');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if($err || !$response){
            return ['ok'=>false, 'error'=>$err ?: 'Empty response', 'http_code'=>$httpCode];
        }
        if($httpCode >= 400 || !empty($decoded['error'])){
            return ['ok'=>false, 'error'=>($decoded['error']['message'] ?? $err ?: 'API error'), 'data'=>$decoded, 'http_code'=>$httpCode];
        }
        return ['ok'=>true, 'data'=>$decoded, 'http_code'=>$httpCode];
    }

    /**
     * GET from a Meta Graph API endpoint. Same return shape as _post().
     */
    private function _get($path, array $query = [], $accessToken = null){
        $token = $accessToken ?: $this->_token();
        $url = 'https://graph.facebook.com/' . $this->graphVersion . '/' . ltrim($path, '/')
            . '?' . http_build_query(array_merge(['access_token'=>$token], $query));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if($err || !$response){
            return ['ok'=>false, 'error'=>$err ?: 'Empty response', 'http_code'=>$httpCode];
        }
        if($httpCode >= 400 || !empty($decoded['error'])){
            return ['ok'=>false, 'error'=>($decoded['error']['message'] ?? $err ?: 'API error'), 'data'=>$decoded, 'http_code'=>$httpCode];
        }
        return ['ok'=>true, 'data'=>$decoded, 'http_code'=>$httpCode];
    }

    private function _token(){
        // WhatsApp uses the WhatsApp API token; Messenger/IG use page/IG token.
        return $this->settings->whatsapp_api_token ?? '';
    }

    /* ------------------------------------------------------------------ */
    /* WhatsApp Business API                                              */
    /* ------------------------------------------------------------------ */

    /**
     * Send a free-form text message within the 24-hour customer-service
     * window (or to a number that has messaged the business recently).
     *
     * @param string $to      International format, e.g. "2348012345678" (no +)
     * @param string $text    Message body
     * @return array
     */
    public function sendWhatsappText($to, $text){
        if(!$this->isWhatsappEnabled()){
            return ['ok'=>false, 'error'=>'WhatsApp Business API not configured. Add token & phone number ID in Online Store > Settings.'];
        }
        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $this->_normalizePhone($to),
            'type' => 'text',
            'text' => ['body' => $text],
        ];
        return $this->_post('/' . $this->settings->whatsapp_phone_number_id . '/messages', $body);
    }

    /**
     * Send a WhatsApp template message (works outside the 24h window once the
     * template is approved in WhatsApp Manager).
     *
     * @param string $to            International format
     * @param string $template      Template name, e.g. "order_confirmation"
     * @param string $language      Language code, e.g. "en"
     * @param array  $components    Template components (header/body/button params)
     * @return array
     */
    public function sendWhatsappTemplate($to, $template, $language = 'en', array $components = []){
        if(!$this->isWhatsappEnabled()){
            return ['ok'=>false, 'error'=>'WhatsApp Business API not configured.'];
        }
        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $this->_normalizePhone($to),
            'type' => 'template',
            'template' => array_merge([
                'name' => $template,
                'language' => ['code' => $language],
            ], $components),
        ];
        return $this->_post('/' . $this->settings->whatsapp_phone_number_id . '/messages', $body);
    }

    /**
     * Mark a WhatsApp message as read (read receipt).
     */
    public function markWhatsappRead($messageId){
        if(!$this->isWhatsappEnabled()){
            return ['ok'=>false, 'error'=>'WhatsApp Business API not configured.'];
        }
        $body = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId,
        ];
        return $this->_post('/' . $this->settings->whatsapp_phone_number_id . '/messages', $body);
    }

    /* ------------------------------------------------------------------ */
    /* Facebook Messenger                                                 */
    /* ------------------------------------------------------------------ */

    /**
     * Send a text message via Facebook Messenger to a Page-scoped ID (PSID).
     */
    public function sendMessengerText($psid, $text){
        if(!$this->isMessengerEnabled()){
            return ['ok'=>false, 'error'=>'Facebook Messenger not configured. Add Page access token & Page ID in settings.'];
        }
        $body = [
            'recipient' => ['id' => $psid],
            'message' => ['text' => $text],
        ];
        return $this->_post('/me/messages', $body, $this->settings->facebook_page_access_token);
    }

    /* ------------------------------------------------------------------ */
    /* Instagram Direct                                                   */
    /* ------------------------------------------------------------------ */

    /**
     * Send an Instagram Direct message reply. Requires the Instagram
     * Business Account to be linked to a Facebook Page and the page token
     * with instagram_manage_messages permission.
     *
     * @param string $igUserId   Instagram user id (the sender's IG id)
     * @param string $text       Message body
     * @return array
     */
    public function sendInstagramText($igUserId, $text){
        if(!$this->isInstagramEnabled()){
            return ['ok'=>false, 'error'=>'Instagram Messaging not configured. Add access token & business account id in settings.'];
        }
        $body = [
            'recipient' => ['id' => $igUserId],
            'message' => ['text' => $text],
        ];
        return $this->_post('/me/messages', $body, $this->settings->instagram_access_token);
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                            */
    /* ------------------------------------------------------------------ */

    private function _normalizePhone($phone){
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if(strpos($phone, '00') === 0) $phone = substr($phone, 2);
        if(strpos($phone, '0') === 0 && strlen($phone) == 11){
            $phone = '234' . substr($phone, 1); // Nigeria default
        }
        return $phone;
    }

}
