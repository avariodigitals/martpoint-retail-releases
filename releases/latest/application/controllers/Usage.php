<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Separate browser request: never make a login or sale wait for telemetry. */
class Usage extends CI_Controller {
    public function ping() {
        $this->output->set_content_type('application/json')->set_header('Cache-Control: no-store');
        if ($this->input->method(true) !== 'POST') {
            $this->output->set_status_header(405)->set_output('{"ok":false}');
            return;
        }
        if (!$this->session->userdata('logged_in') || !$this->session->userdata('inv_userid')) {
            $this->output->set_status_header(401)->set_output('{"ok":false}');
            return;
        }
        // CI's database session lock must not block a simultaneous sale.
        session_write_close();
        try {
            $this->load->library('Usage_reporter');
            $this->usage_reporter->report(base_url());
        } catch (Throwable $e) {
            // Telemetry has no effect on POS availability.
        }
        $this->output->set_output('{"ok":true}');
    }
}
