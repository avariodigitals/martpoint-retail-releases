<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Physiotherapy & Rehabilitation — clinical access and scope helpers.
 *
 * IMPORTANT (spec §5 / review item 3): clinical permission checks here do NOT
 * honour the inv_userid 1/2 bypass or is_admin()/is_store_admin() shortcuts in
 * MY_Controller::permissions(). Clinical access requires an explicit
 * db_permissions grant on the user's role — including for install super-admins.
 * These helpers must only be used on clinical code paths; retail modules keep
 * using $this->permissions() unchanged.
 */

if (!function_exists('physio_can')) {
    /**
     * Explicit-grant permission check for clinical data/actions.
     * No super-admin bypass: the user's role must hold the permission key.
     */
    function physio_can($perm) {
        $CI =& get_instance();
        $role_id = (int)$CI->session->userdata('role_id');
        if (!$role_id || empty($perm)) {
            return false;
        }
        // Deliberately a raw, bound query rather than
        // $CI->db->where(...)->count_all_results(...).
        //
        // CodeIgniter 3 keeps ONE shared Query Builder object per connection.
        // Composing a check through it while a caller is midway through
        // building a query merges the two: the caller's FROM/JOIN survive and
        // db_permissions is appended, which both corrupts the caller's SQL and
        // makes a qualified `role_id` ambiguous. A raw query leaves the builder
        // completely untouched, so this helper is safe to call from anywhere —
        // including from inside report and dashboard query composition.
        $sql = 'SELECT 1 FROM db_permissions WHERE permissions = ? AND role_id = ? LIMIT 1';
        $row = $CI->db->query($sql, array($perm, $role_id))->row();
        return !empty($row);
    }
}

if (!function_exists('physio_can_any')) {
    function physio_can_any(array $perms) {
        foreach ($perms as $p) {
            if (physio_can($p)) return true;
        }
        return false;
    }
}

if (!function_exists('physio_visit_locked')) {
    /**
     * A closed visit is view-only: nothing in its record may change without
     * the explicit `encounters_edit_closed` grant (admin-class roles hold it
     * by default; a Store Admin can grant it on the Roles screen).
     * Raw query, like physio_can — safe to call mid-query-builder.
     */
    function physio_visit_locked($encounterId) {
        if (!(int)$encounterId) return false;
        $CI =& get_instance();
        $row = $CI->db->query('SELECT queue_stage FROM db_encounters WHERE id = ?', array((int)$encounterId))->row();
        return $row && (string)$row->queue_stage === 'closed' && !physio_can('encounters_edit_closed');
    }
}

if (!function_exists('physio_visit_locked_error')) {
    /** The one wording every closed-visit refusal speaks. */
    function physio_visit_locked_error() {
        return 'This visit is closed — view only. Editing a closed visit needs the Edit Closed Visits right.';
    }
}

if (!function_exists('physio_require')) {
    /**
     * Deny (JSON-aware, via show_access_denied_page) unless the explicit
     * clinical permission is held.
     */
    function physio_require($perm) {
        if (!physio_can($perm)) {
            $CI =& get_instance();
            log_message('error', 'physio permission denied: ' . $perm
                . ' for user ' . $CI->session->userdata('inv_userid')
                . ' role ' . $CI->session->userdata('role_id')
                . ' on ' . $CI->uri->uri_string());
            $CI->show_access_denied_page();
        }
        return true;
    }
}

if (!function_exists('physio_enabled')) {
    /** Is the physiotherapy_rehabilitation business type active for this store? */
    function physio_enabled($store_id = null) {
        $CI =& get_instance();
        $store_id = $store_id ?: get_current_store_id();
        // Canonical resolver: db_store_industry_settings → db_store_business_profile
        // → db_store columns → default profile.
        if (function_exists('mp_get_store_profile')) {
            $profile = mp_get_store_profile($store_id);
            return ($profile['industry_type'] ?? '') === 'physiotherapy_rehabilitation';
        }
        if (!$CI->db->table_exists('db_store_business_profile')) return false;
        $row = $CI->db->select('industry_type')->where('store_id', $store_id)
            ->get('db_store_business_profile')->row();
        return $row && $row->industry_type === 'physiotherapy_rehabilitation';
    }
}

if (!function_exists('physio_branch_ids')) {
    /**
     * Branch (db_warehouse) ids the current user may see clinical records for.
     * Users with `clinical_cross_branch` see all active branches of the store;
     * everyone else is limited to their db_userswarehouses assignment.
     */
    function physio_branch_ids() {
        // Memoised per request: this is called during the composition of other
        // report queries, and it is also called more than once for a single
        // page (the rail, the report and the KPI cards each ask). Re-querying
        // is both wasteful and unsafe — see physio_can() for why a nested query
        // can corrupt a caller's half-built Query Builder chain.
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $CI =& get_instance();
        $store_id = (int) get_current_store_id();
        if (physio_can('clinical_cross_branch')) {
            // Raw query so the caller's Query Builder state is never touched.
            $rows = $CI->db->query(
                'SELECT id FROM db_warehouse WHERE store_id = ? AND status = 1',
                array($store_id)
            )->result_array();
            $cache = array_map('intval', array_column($rows, 'id'));
            return $cache;
        }
        $assigned = function_exists('getArrayOfBranchIds') ? getArrayOfBranchIds() : array();
        $cache = array_map('intval', (array) $assigned);
        return $cache;
    }
}

if (!function_exists('physio_can_branch')) {
    /** May the current user touch a clinical record in this branch? */
    function physio_can_branch($warehouse_id) {
        if (empty($warehouse_id)) return true; // unscoped record — permission key already checked
        return in_array((int)$warehouse_id, array_map('intval', physio_branch_ids()), true);
    }
}

if (!function_exists('physio_notify')) {
    /**
     * Enqueue a notification on the outbox (db_notification_queue). Idempotent
     * by (store_id, event_key): a duplicate event_key is a no-op, so retries of
     * the business action never re-send. The cron consumer (stage 2) delivers.
     *
     * @param string $event_key    e.g. "lead.ack.123" or "appt.confirmed.45"
     * @param array  $n            channel|template_key|recipient|payload|scheduled_at
     * @return bool true when queued (or already queued), false on failure
     */
    function physio_notify($event_key, array $n) {
        $CI =& get_instance();
        if (!$CI->db->table_exists('db_notification_queue') || empty($event_key)) {
            return false;
        }
        $store_id = $n['store_id'] ?? get_current_store_id();
        $data = [
            'store_id'     => $store_id,
            'event_key'    => $event_key,
            'channel'      => in_array(($n['channel'] ?? 'email'), ['email','sms'], true) ? $n['channel'] : 'email',
            'template_key' => $n['template_key'] ?? null,
            'recipient'    => $n['recipient'] ?? null,
            'payload_json' => isset($n['payload']) ? json_encode($n['payload']) : null,
            'status'       => 'queued',
            'scheduled_at' => $n['scheduled_at'] ?? date('Y-m-d H:i:s'),
            'created_date' => date('Y-m-d'),
            'created_time' => date('H:i:s'),
            'created_by'   => $CI->session->userdata('inv_username') ?: 'system',
        ];
        // INSERT ... ON DUPLICATE would hide real errors; check-then-insert with
        // the unique key is fine because a lost race still yields a queued row.
        $exists = $CI->db->where('store_id', $store_id)->where('event_key', $event_key)
            ->count_all_results('db_notification_queue');
        if ($exists) return true;
        return (bool)$CI->db->insert('db_notification_queue', $data);
    }
}

if (!function_exists('physio_notification_process')) {
    /**
     * Outbox consumer — call from cron: php index.php cli physio_notifications.
     * Per-event delivery is isolated: one failure marks only that row and never
     * touches business records. Deceased patients are suppressed before send.
     * Returns array('sent'=>, 'failed'=>, 'suppressed'=>).
     */
    function physio_notification_process($limit = 20) {
        $CI =& get_instance();
        if (!isset($CI->db)) $CI->load->database();
        if (!$CI->db->table_exists('db_notification_queue')) return ['sent' => 0, 'failed' => 0, 'suppressed' => 0];
        $rows = $CI->db->where('status', 'queued')
            ->where('scheduled_at <= NOW()', null, FALSE)
            ->order_by('id', 'asc')->limit((int)$limit)
            ->get('db_notification_queue')->result();
        $sent = 0; $failed = 0; $suppressed = 0;
        foreach ($rows as $row) {
            if (physio_notification_suppressed($row)) {
                $CI->db->where('id', $row->id)->update('db_notification_queue', [
                    'status' => 'suppressed', 'last_error' => 'suppressed before send']);
                $suppressed++;
                continue;
            }
            // Debt reminders re-check current debt/pause/config at delivery
            // time and refresh the amount on partial payment.
            if (($row->template_key ?? '') === 'debt_reminder'
                && $CI->db->table_exists('db_debt_reminder_config')) {
                $CI->load->model('debt_reminder_v2_model', 'drv2_delivery');
                $amount = 0;
                $chk = $CI->drv2_delivery->deliveryCheck($row, $amount);
                if ($chk['suppress']) {
                    $CI->db->where('id', $row->id)->update('db_notification_queue', [
                        'status' => 'suppressed', 'last_error' => 'debt reminder: ' . $chk['reason']]);
                    $CI->drv2_delivery->audit($row->store_id, 'suppressed', null, null, $chk['reason'], $row->event_key);
                    $suppressed++;
                    continue;
                }
                // Refresh the amount so the email reflects any partial payment.
                if ($amount > 0 && $amount != (float)($row->payload_json ? json_decode($row->payload_json, true)['amount_due'] ?? 0 : 0)) {
                    $payload = json_decode($row->payload_json, true) ?: array();
                    $payload['amount_due'] = $amount;
                    $CI->db->where('id', $row->id)->update('db_notification_queue', array('payload_json' => json_encode($payload)));
                }
                $CI->drv2_delivery->audit($row->store_id, 'sent', null, null, null, $row->event_key, $amount);
            }
            $result = physio_deliver($row);
            if ($result === true) {
                $CI->db->where('id', $row->id)->update('db_notification_queue', [
                    'status' => 'sent', 'sent_at' => date('Y-m-d H:i:s'),
                    'last_error' => null, 'attempts' => (int)$row->attempts + 1]);
                $sent++;
            } else {
                // Retry with backoff; give up after 5 attempts.
                $dead = ((int)$row->attempts + 1) >= 5;
                $CI->db->where('id', $row->id)->update('db_notification_queue', [
                    'status'       => $dead ? 'failed' : 'queued',
                    'attempts'     => (int)$row->attempts + 1,
                    'last_error'   => is_string($result) ? substr($result, 0, 490) : 'delivery failed',
                    'scheduled_at' => $dead ? $row->scheduled_at : date('Y-m-d H:i:s', strtotime('+' . min(60, 5 * ((int)$row->attempts + 1)) . ' minutes')),
                ]);
                $failed++;
            }
        }
        return ['sent' => $sent, 'failed' => $failed, 'suppressed' => $suppressed];
    }
}

if (!function_exists('physio_notification_suppressed')) {
    /** Never send ordinary reminders/acks to deceased patients. */
    function physio_notification_suppressed($row) {
        $CI =& get_instance();
        if (empty($row->payload_json) || !$CI->db->table_exists('db_patients')) return false;
        $payload = json_decode($row->payload_json, true);
        $patientId = !empty($payload['patient_id']) ? (int)$payload['patient_id'] : 0;
        if (!$patientId) {
            // Lead-side payloads carry lead_id — check its linked patient if any.
            if (!empty($payload['lead_id']) && $CI->db->field_exists('patient_id', 'db_leads')) {
                $lead = $CI->db->select('patient_id')->where('id', (int)$payload['lead_id'])
                    ->get('db_leads')->row();
                $patientId = $lead ? (int)$lead->patient_id : 0;
            }
        }
        if (!$patientId && !empty($payload['appointment_id']) && $CI->db->table_exists('db_appointments')) {
            $appt = $CI->db->select('patient_id')->where('id', (int)$payload['appointment_id'])
                ->where('store_id', $row->store_id)->get('db_appointments')->row();
            $patientId = $appt ? (int)$appt->patient_id : 0;
        }
        // Portal events: revoked access / withdrawn release suppress at
        // delivery time — a revoked proxy never receives stale messages.
        if (!empty($payload['portal_user_id']) && $CI->db->table_exists('db_patient_portal_users')) {
            $u = $CI->db->select('status')->where('id', (int)$payload['portal_user_id'])
                ->get('db_patient_portal_users')->row();
            if ($u && $u->status === 'revoked') return true;
        }
        if (!empty($payload['proxy_id']) && $CI->db->table_exists('db_patient_portal_proxies')) {
            $x = $CI->db->select('status')->where('id', (int)$payload['proxy_id'])
                ->get('db_patient_portal_proxies')->row();
            if ($x && $x->status === 'revoked') return true;
        }
        if (!empty($payload['document_id']) && $CI->db->table_exists('db_patient_documents')) {
            $d = $CI->db->select('released_to_patient')->where('id', (int)$payload['document_id'])
                ->where('store_id', $row->store_id)->get('db_patient_documents')->row();
            if ($d && (int)$d->released_to_patient !== 1) return true;
        }
        // Appointment reminders: suppress if the booking moved after the row
        // was queued, or is no longer deliverable (cancelled/completed/etc).
        if (($row->template_key ?? '') === 'appt_reminder' && !empty($payload['appointment_id'])
            && $CI->db->table_exists('db_appointments')) {
            $a = $CI->db->select('status, scheduled_at')->where('id', (int)$payload['appointment_id'])
                ->where('store_id', $row->store_id)->get('db_appointments')->row();
            if (!$a || $a->status !== 'confirmed') return true;
            if (!empty($payload['scheduled_at']) && strtotime($a->scheduled_at) !== strtotime($payload['scheduled_at']))
                return true;
        }
        if (!$patientId) return false;
        $p = $CI->db->select('deceased')->where('id', $patientId)->where('store_id', $row->store_id)
            ->get('db_patients')->row();
        return $p && (int)$p->deceased === 1;
    }
}

if (!function_exists('physio_deliver')) {
    /**
     * Deliver one queued event. Development test sink: define MP_NOTIFY_SINK
     * to a writable file path — payloads are appended and the event is marked
     * sent, letting the whole pipeline be verified without real email.
     * Real SMTP delivery stays disabled until the transport is verified.
     * @return true|string  true on send, error message otherwise.
     */
    function physio_deliver($row) {
        $sink = defined('MP_NOTIFY_SINK') ? MP_NOTIFY_SINK : getenv('MP_NOTIFY_SINK');
        if ($sink === false) $sink = null;
        if ($sink) {
            $line = '[' . date('c') . '] ' . $row->event_key
                . ' -> ' . ($row->recipient ?: '-') . ' ' . ($row->template_key ?: '')
                . ' ' . ($row->payload_json ?: '') . "\n";
            return @file_put_contents($sink, $line, FILE_APPEND) !== false
                ? true : 'cannot write notification sink';
        }
        return 'no delivery transport configured';
    }
}

if (!function_exists('physio_docs_dir')) {
    /**
     * Private clinical-document storage — OUTSIDE the publicly served
     * docroot ONLY. Files here are never reachable by URL; they stream only
     * through the permission-checked Patient_docs::download endpoint.
     *
     * There is deliberately NO in-docroot fallback: if
     * <parent of docroot>/mp_private/patient_docs cannot be created or is
     * not writable, null is returned and uploads fail visibly rather than
     * silently storing clinical files somewhere web-servable.
     */
    function physio_docs_dir() {
        $dir = rtrim(dirname(FCPATH), '/') . '/mp_private/patient_docs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        if (is_dir($dir) && is_writable($dir)) {
            // Defence-in-depth markers for servers that honour them.
            if (!file_exists($dir . '/.htaccess')) {
                @file_put_contents($dir . '/.htaccess', "Require all denied\nDeny from all\n");
            }
            if (!file_exists($dir . '/index.html')) {
                @file_put_contents($dir . '/index.html', '');
            }
            return $dir;
        }
        return null;
    }
}

if (!function_exists('physio_docs_validate_file')) {
    /**
     * Validate an uploaded clinical file. Returns array('ok'=>true,'ext'=>..)
     * or array('ok'=>false,'error'=>msg). Whitelist of extensions + matching
     * MIME sniff via finfo; 15 MB cap.
     */
    function physio_docs_validate_file($tmpPath, $origName) {
        $allowed = array(
            'pdf'  => array('application/pdf'),
            'jpg'  => array('image/jpeg'),
            'jpeg' => array('image/jpeg'),
            'png'  => array('image/png'),
            'webp' => array('image/webp'),
            'dcm'  => array('application/dicom', 'application/octet-stream'),
        );
        $ext = strtolower(pathinfo((string)$origName, PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) {
            return array('ok' => false, 'error' => 'File type not allowed (pdf, jpg, png, webp, dcm only)');
        }
        if (!is_file($tmpPath) || filesize($tmpPath) === 0) {
            return array('ok' => false, 'error' => 'Empty or missing file');
        }
        if (filesize($tmpPath) > 15 * 1024 * 1024) {
            return array('ok' => false, 'error' => 'File exceeds 15 MB limit');
        }
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = $finfo ? finfo_file($finfo, $tmpPath) : null;
            if ($finfo) finfo_close($finfo);
            if ($mime && !in_array($mime, $allowed[$ext]) && $mime !== 'application/octet-stream') {
                return array('ok' => false, 'error' => 'File content does not match its extension');
            }
        }
        return array('ok' => true, 'ext' => $ext);
    }
}

if (!function_exists('mp_event_label')) {
    /**
     * Plain-English label for a stored event / activity / stage code.
     *
     * The feeds used to print the raw column value, so staff saw things like
     * "intake_completed" and "waiting_physio → waiting_nurse". Nothing in the
     * UI should ever surface an internal code: this maps every code we write
     * to the words a receptionist, nurse or clinician would actually use.
     *
     * Unknown codes fall back to a title-cased, de-underscored version of
     * themselves and are never shown with their underscores intact.
     */
    function mp_event_label($code)
    {
        static $map = null;
        if ($map === null) {
            $map = array(
                // Visit / encounter lifecycle
                'checked_in'            => 'Patient checked in',
                'stage_change'          => 'Moved to a new stage',
                'reassigned'            => 'Sent to a therapist',
                'status'                => 'Status changed',
                'intake_completed'      => 'Nursing intake completed',
                'vitals'                => 'Vitals recorded',
                'nursing_intake'        => 'Nursing intake',
                'waiting_nurse'         => 'Waiting for nurse',
                'waiting_physio'        => 'Waiting for clinician',
                'with_physio'           => 'With clinician',
                'awaiting_finance'      => 'Awaiting payment',
                'closed'                => 'Visit closed',
                'visit_type'            => 'Visit type chosen',
                // Registration / identity
                'registered'            => 'Patient registered',
                'patient_code'          => 'Patient number assigned',
                'kept_patient_id'       => 'Existing patient number kept',
                'duplicate_flagged'     => 'Possible duplicate flagged',
                // Clinical
                'assessment_started'    => 'Assessment started',
                'assessment_saved'      => 'Assessment saved',
                'assessment_finalised'  => 'Assessment completed',
                'assessment_amended'    => 'Assessment amended',
                'assessment_remark'     => 'Assessment remark recorded',
                'treatment_charted'     => 'Treatment charted for the session',
                'investigation_reviewed'=> 'Investigation reviewed',
                'assessment_remark'    => 'Assessment remark recorded',
                'visit_type'           => 'Visit type chosen',
                'consent_recorded'      => 'Consent recorded',
                'document_uploaded'     => 'Document uploaded',
                // Money
                'assessment_fee_charged'=> 'Assessment fee charged',
                'assessment_fee_waived' => 'Assessment fee waived',
                'payment_received'      => 'Payment received',
                'invoice_created'       => 'Invoice created',
                'visit_bill_created'    => 'Visit bill created',
                // Notes / admin
                'note'                  => 'Note',
                'notes'                 => 'Notes',
                'deceased'              => 'Recorded as deceased',
                'deceased_date'         => 'Date of death recorded',
                'deceased_corrected'    => 'Deceased record corrected',
                // Appointments / leads
                'booked'                => 'Appointment booked',
                'rescheduled'           => 'Appointment rescheduled',
                'cancelled'             => 'Appointment cancelled',
                'no_show'               => 'Did not attend',
                'completed'             => 'Completed',
                'requested'             => 'Requested',
                'proposed'              => 'Proposed',
                'confirmed'             => 'Confirmed',
                'assigned'              => 'Assigned to a staff member',
                'converted'             => 'Converted',
                'arrived'               => 'Patient arrived',
                // Patient documents
                'view'                  => 'Viewed',
                'download'              => 'Downloaded',
                'upload'                => 'Uploaded',
                'release'               => 'Released to the patient portal',
                'unrelease'             => 'Removed from the patient portal',
                'verify'                => 'Signature verified',
                'denied'                => 'Access refused',
            );
        }

        $code = trim((string) $code);
        if ($code === '') {
            return '';
        }
        if (isset($map[$code])) {
            return $map[$code];
        }
        // Unknown: still never show underscores.
        return ucfirst(str_replace('_', ' ', $code));
    }
}

if (!function_exists('mp_first_name')) {
    /**
     * The person's name for "by ..." audit lines — never the login username.
     *
     * History rows used to show the login handle ("by storeadm"), which is
     * meaningless to clinic staff. We look the user up so rows written before
     * the change read correctly too; the name captured at the time is only a
     * fallback for users that have since been deleted.
     */
    function mp_first_name($userId, $fallback = '')
    {
        static $cache = array();
        $userId = (int) $userId;
        if ($userId > 0 && !array_key_exists($userId, $cache)) {
            $CI =& get_instance();
            $row = null;
            if ($CI->db->field_exists('first_name', 'db_users')) {
                $cols = 'first_name' . ($CI->db->field_exists('last_name', 'db_users') ? ', last_name' : '');
                $row = $CI->db->select($cols)->where('id', $userId)->get('db_users')->row();
            }
            $name = $row ? trim((string) $row->first_name) : '';
            if ($name === '' && $row && isset($row->last_name)) {
                $name = trim((string) $row->last_name);
            }
            $cache[$userId] = $name;
        }
        $name = $userId > 0 ? $cache[$userId] : '';
        return $name !== '' ? $name : (trim((string) $fallback) !== '' ? trim((string) $fallback) : 'System');
    }
}

if (!function_exists('mp_name_from_login')) {
    /**
     * As mp_first_name(), but for rows that only stored the login handle.
     *
     * Older tables (db_document_versions.uploaded_by) hold the username
     * rather than a user id, so we resolve the handle back to its user and
     * then use the same first-name rule. Unresolvable handles become "System"
     * — the login handle itself is never shown.
     */
    function mp_name_from_login($login, $fallback = '')
    {
        static $ids = array();
        $login = trim((string) $login);
        if ($login === '') {
            return mp_first_name(0, $fallback);
        }
        if (!array_key_exists($login, $ids)) {
            $CI =& get_instance();
            $row = $CI->db->select('id')->where('username', $login)->limit(1)->get('db_users')->row();
            $ids[$login] = $row ? (int) $row->id : 0;
        }
        return mp_first_name($ids[$login], $fallback);
    }
}

if (!function_exists('mp_file_type_label')) {
    /**
     * Friendly name for a stored MIME type.
     *
     * The document history printed raw MIME strings, so staff saw
     * "application/octet-stream". Anything unrecognised still reads as a
     * plain word rather than a slash-separated token.
     */
    function mp_file_type_label($mime)
    {
        static $map = array(
            'application/pdf'       => 'PDF',
            'image/jpeg'            => 'JPEG image',
            'image/png'             => 'PNG image',
            'image/webp'            => 'WebP image',
            'image/gif'             => 'GIF image',
            'application/dicom'     => 'DICOM scan',
            'application/msword'    => 'Word document',
            'application/zip'       => 'ZIP archive',
        );
        $mime = strtolower(trim((string) $mime));
        if ($mime === '') {
            return 'File';
        }
        if (isset($map[$mime])) {
            return $map[$mime];
        }
        if ($mime === 'application/octet-stream') {
            return 'File';
        }
        // Unknown type: use the part after the slash, never the whole token.
        $short = substr($mime, strrpos($mime, '/') + 1);
        $short = str_replace(array('x-', 'vnd.', '+xml'), '', $short);
        return $short !== '' ? ucfirst(str_replace(array('_', '-'), ' ', $short)) : 'File';
    }
}

if (!function_exists('mp_stage_label')) {
    /** Plain-English label for a care-queue stage code. */
    function mp_stage_label($stage)
    {
        static $map = array(
            'waiting_nurse'    => 'Waiting for nurse',
            'nursing_intake'   => 'Nursing intake',
            'waiting_physio'   => 'Waiting for clinician',
            'with_physio'      => 'With clinician',
            'awaiting_finance' => 'Awaiting payment',
            'closed'           => 'Visit closed',
        );
        $stage = trim((string) $stage);
        if ($stage === '') {
            return '';
        }
        if (isset($map[$stage])) {
            return $map[$stage];
        }
        return ucfirst(str_replace('_', ' ', $stage));
    }
}

if (!function_exists('mp_code_label')) {
    /**
     * Last-resort humaniser for any status / type / mode code.
     *
     * Views were printing raw column values, so staff saw "in_progress",
     * "PENDING_REVIEW", "payment_status" style tokens. This is the catch-all
     * used wherever a code has no richer, context-specific wording — it never
     * returns underscores or SHOUTING CASE.
     *
     * Codes whose wording matters clinically (care-queue stages, event feed
     * types) have their own maps above; prefer those.
     */
    function mp_code_label($code, $fallback = '—')
    {
        static $map = null;
        if ($map === null) {
            $map = array(
                // Generic lifecycle
                'active' => 'Active', 'inactive' => 'Inactive',
                'enabled' => 'Enabled', 'disabled' => 'Disabled',
                'open' => 'Open', 'closed' => 'Closed', 'archived' => 'Archived',
                'draft' => 'Draft', 'final' => 'Completed', 'finalised' => 'Completed',
                'finalized' => 'Completed', 'pending' => 'Pending',
                'pending_review' => 'Awaiting review', 'approved' => 'Approved',
                'rejected' => 'Rejected', 'completed' => 'Completed',
                'cancelled' => 'Cancelled', 'canceled' => 'Cancelled',
                'scheduled' => 'Scheduled', 'confirmed' => 'Confirmed',
                'requested' => 'Requested', 'proposed' => 'Proposed',
                'rescheduled' => 'Rescheduled', 'checked_in' => 'Checked in',
                'in_progress' => 'In progress', 'interrupted' => 'Interrupted',
                'no_show' => 'Did not attend', 'expired' => 'Expired',
                'revoked' => 'Revoked', 'redeemed' => 'Redeemed',
                'used' => 'Used', 'posted' => 'Posted', 'void' => 'Voided',
                // Admission / bed
                'admitted' => 'Admitted', 'discharged' => 'Discharged',
                'available' => 'Available', 'occupied' => 'Occupied',
                'reserved' => 'Reserved', 'cleaning' => 'Being cleaned',
                'maintenance' => 'Under maintenance',
                'deceased' => 'Deceased',
                // Money
                'paid' => 'Paid', 'unpaid' => 'Unpaid', 'partial' => 'Partly paid',
                'overdue' => 'Overdue', 'refunded' => 'Refunded',
                'waived' => 'Waived', 'settled' => 'Settled',
                'cash' => 'Cash', 'card' => 'Card', 'transfer' => 'Bank transfer',
                'paystack' => 'Paystack', 'monnify' => 'Monnify',
                'wallet' => 'Patient funds', 'insurance' => 'Insurance',
                'credit' => 'On credit', 'cheque' => 'Cheque',
                // Clinical
                'initial' => 'Initial', 'follow_up' => 'Follow-up',
                'review' => 'Review', 'discharge' => 'Discharge',
                'inpatient' => 'Inpatient', 'outpatient' => 'Outpatient',
                'day_case' => 'Day case', 'emergency' => 'Emergency',
                'elective' => 'Elective',
            );
        }

        $raw = trim((string) $code);
        if ($raw === '') {
            return $fallback;
        }
        $key = strtolower($raw);
        if (isset($map[$key])) {
            return $map[$key];
        }
        // Unknown: sentence-case, spaces, no underscores — never show the code.
        return ucfirst(strtolower(str_replace('_', ' ', $raw)));
    }
}

if (!function_exists('physio_staff_label')) {
    /**
     * Display label for a staff name already fetched by a join.
     *
     * Historical rows (appointments, queue, admissions) can carry a system
     * account as the recorded clinician. Physio stores must never surface
     * those accounts to other users, so the name is masked here — the row
     * itself stays intact. Non-physio stores and real staff pass through
     * unchanged. Pass the joined role_id when the query provides one; if it
     * is absent, only the known system usernames are masked.
     */
    function physio_staff_label($name, $role_id = null) {
        $name = trim((string) $name);
        if ($name === '') return $name;
        if (!function_exists('physio_enabled') || !physio_enabled()) return $name;
        if ($role_id !== null && (int) $role_id > 0) {
            if (in_array((int) $role_id, array(1, 2), true)) return '—';
            return $name;
        }
        if (in_array(strtolower($name), array('master', 'storeadm'), true)) return '—';
        return $name;
    }
}

if (!function_exists('physio_clinical_user')) {
    /**
     * Does this db_users row belong to a clinical role?
     *
     * Clinical = the role holds at least one hands-on care grant. The Admin
     * and Store Admin roles are seeded with every grant, so a naive
     * permission check would call storeadm a clinician — that is exactly how
     * the vendor admin account ended up listed as a "clinical doctor" in the
     * booking and plan pickers. Roles 1 (Admin) and 2 (Store Admin) are
     * therefore never clinical, whatever they hold. Memoised per request.
     */
    function physio_clinical_user($user) {
        static $roleClinical = array();
        if (!is_object($user) || !isset($user->role_id)) return false;
        $roleId = (int) $user->role_id;
        if ($roleId === 1 || $roleId === 2) return false;
        if (!array_key_exists($roleId, $roleClinical)) {
            $CI =& get_instance();
            $keys = "'encounters_add','assessments_add','vitals_add','plans_add',"
                  . "'sessions_complete','md_authority','investigations_review','nursing_tasks_view'";
            $row = $CI->db->query(
                "SELECT 1 FROM db_permissions WHERE role_id = ? AND permissions IN ($keys) LIMIT 1",
                array($roleId)
            )->row();
            $roleClinical[$roleId] = (bool) $row;
        }
        return $roleClinical[$roleId];
    }
}

if (!function_exists('physio_staff_select')) {
    /**
     * Staff rows for a physio picker or list.
     *
     * Physio industry rules (any other store type gets the plain unfiltered
     * list, so retail/other industries are untouched):
     *  - system/vendor accounts (role 1 Admin, role 2 Store Admin — master,
     *    storeadm) are never listed. They can still log in and work, but
     *    like a licence seat count they are excluded from what everyone
     *    else is shown.
     *  - $clinical_only lists only clinical roles, so "Clinician"-style
     *    fields show carers instead of every user in the store.
     *
     * Uses a raw bound query — the CI3 shared Query Builder must not be
     * touched from a helper that can run mid-composition (see physio_can).
     */
    function physio_staff_select($store_id = null, $clinical_only = true, $order_by = 'username') {
        $CI =& get_instance();
        if ($store_id === null) { $store_id = (int) get_current_store_id(); }
        $store_id = (int) $store_id;
        $order = ($order_by === 'first_name') ? 'u.first_name' : 'u.username';
        if (!function_exists('physio_enabled') || !physio_enabled($store_id)) {
            return $CI->db->query(
                "SELECT u.* FROM db_users u WHERE u.store_id = ? AND u.status = 1 ORDER BY $order",
                array($store_id)
            )->result();
        }
        $rows = $CI->db->query(
            "SELECT u.* FROM db_users u
             WHERE u.store_id = ? AND u.status = 1
               AND u.role_id NOT IN (1, 2)
               AND LOWER(u.username) NOT IN ('master', 'storeadm')
             ORDER BY $order",
            array($store_id)
        )->result();
        if (!$clinical_only) return $rows;
        $out = array();
        foreach ($rows as $r) {
            if (physio_clinical_user($r)) $out[] = $r;
        }
        return $out;
    }
}

if (!function_exists('physio_nurse_select')) {
    /**
     * Nurse-profile staff — who may be tasked with a treatment patient.
     *
     * The clinic role set carries a 'Nurse' profile ("Vitals, observations,
     * assigned care tasks and nursing handover"), and that profile is what the
     * tasked-nurse picker must show. Holding vitals_add is NOT enough: the
     * owner and admin roles hold every grant, so a permission-based list puts
     * the store admin in a clinical dropdown. Match the role NAME instead
     * ('Nurse', 'Senior Nurse', 'Staff Nurse' all qualify).
     *
     * A store whose nurse role was renamed to something without "nurse" in it
     * would otherwise have an empty picker and a treatment queue that cannot
     * move, so that case falls back to hands-on care roles holding vitals_add —
     * still never the system or owner accounts.
     */
    function physio_nurse_select($store_id = null, $order_by = 'first_name') {
        $CI =& get_instance();
        if ($store_id === null) { $store_id = (int) get_current_store_id(); }
        $store_id = (int) $store_id;
        $order = ($order_by === 'username') ? 'u.username' : 'u.first_name';
        $exclude = array(1, 2);
        if (function_exists('store_owner_role_ids')) {
            $exclude = array_merge($exclude, store_owner_role_ids($store_id));
        }
        $exclude = array_unique(array_map('intval', $exclude));
        $notIn = implode(',', $exclude);
        $rows = $CI->db->query(
            "SELECT u.* FROM db_users u
             JOIN db_roles r ON r.id = u.role_id
             WHERE u.store_id = ? AND u.status = 1
               AND u.role_id NOT IN ($notIn)
               AND LOWER(u.username) NOT IN ('master', 'storeadm')
               AND LOWER(r.role_name) LIKE '%nurse%'
             ORDER BY $order",
            array($store_id)
        )->result();
        if (!empty($rows)) return $rows;
        return $CI->db->query(
            "SELECT DISTINCT u.* FROM db_users u
             JOIN db_permissions p ON p.role_id = u.role_id AND p.permissions = 'vitals_add'
             WHERE u.store_id = ? AND u.status = 1
               AND u.role_id NOT IN ($notIn)
               AND LOWER(u.username) NOT IN ('master', 'storeadm')
             ORDER BY $order",
            array($store_id)
        )->result();
    }
}

if (!function_exists('physio_therapist_select')) {
    /**
     * Therapist profiles — who a patient can be sent to on the Care Queue.
     *
     * The desk routes by JOB, not by permission: the Physiotherapist profile is
     * the role that takes a patient through treatment, so match the role NAME
     * ('Physiotherapist', 'Senior Physiotherapist', 'Therapist' all qualify)
     * and fall back to any clinical role that can actually run a visit
     * (encounters_add) — a clinic whose role was renamed must still be able to
     * move its queue. System/owner accounts are never listed, and neither are
     * nurses who cannot treat, so a patient is never "sent to" somebody who
     * cannot see them.
     */
    function physio_therapist_select($store_id = null, $order_by = 'first_name') {
        $CI =& get_instance();
        if ($store_id === null) { $store_id = (int) get_current_store_id(); }
        $store_id = (int) $store_id;
        $order = ($order_by === 'username') ? 'u.username' : 'u.first_name';
        $exclude = array(1, 2);
        if (function_exists('store_owner_role_ids')) {
            $exclude = array_merge($exclude, store_owner_role_ids($store_id));
        }
        $exclude = array_unique(array_map('intval', $exclude));
        $notIn = implode(',', $exclude);
        $rows = $CI->db->query(
            "SELECT u.* FROM db_users u
             JOIN db_roles r ON r.id = u.role_id
             WHERE u.store_id = ? AND u.status = 1
               AND u.role_id NOT IN ($notIn)
               AND LOWER(u.username) NOT IN ('master', 'storeadm')
               AND (LOWER(r.role_name) LIKE '%physio%' OR LOWER(r.role_name) LIKE '%therapist%')
             ORDER BY $order",
            array($store_id)
        )->result();
        if (!empty($rows)) return $rows;
        return $CI->db->query(
            "SELECT DISTINCT u.* FROM db_users u
             JOIN db_permissions p ON p.role_id = u.role_id AND p.permissions = 'encounters_add'
             WHERE u.store_id = ? AND u.status = 1
               AND u.role_id NOT IN ($notIn)
               AND LOWER(u.username) NOT IN ('master', 'storeadm')
             ORDER BY $order",
            array($store_id)
        )->result();
    }
}
