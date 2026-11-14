<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Best-effort transactional SMS routed through the store's configured SMS path. */
class Transactional_sms {
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Send an operational customer notice. Enrolled fleets route through
     * Central's metered account; standalone stores use their selected provider.
     */
    public function send($storeId, $recipient, $message, $templateKey, $eventKey) {
        $storeId = (int)$storeId;
        $recipient = trim((string)$recipient);
        $message = trim((string)$message);
        if (!$storeId || $recipient === '' || $message === '') return false;

        try {
            $this->CI->load->library('messaging_client');
            $managed = $this->CI->messaging_client->managed();
            $smsProvider = (int)mp_get_store_notification_setting($storeId, 'sms_status', 0);
            // A local provider that the merchant has not enabled is an
            // intentional opt-out, not a delivery failure worth logging.
            if (!$managed && $smsProvider === 0) return false;

            $this->CI->load->model('sms_model');
            $result = $this->CI->sms_model->send_sms($recipient, $message, array(
                'store_id' => $storeId,
                'message_category' => 'transactional',
                'template_key' => (string)$templateKey,
                'messaging_key' => (string)$eventKey,
            ));
            $sent = is_string($result)
                ? (preg_match('/\b(success|sent successfully|queued successfully)\b/i', $result) === 1
                    && stripos($result, 'failed') === false
                    && stripos($result, 'not sent') === false)
                : (bool)$result;
            if (!$sent) {
                log_message('error', 'Transactional SMS was not accepted for store ' . $storeId . ' (' . $templateKey . ').');
            }
            return $sent;
        } catch (Throwable $e) {
            log_message('error', 'Transactional SMS failed for store ' . $storeId . ' (' . $templateKey . '): ' . $e->getMessage());
            return false;
        }
    }
}
