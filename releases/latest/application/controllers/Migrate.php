<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller {

    public function index() {
        // Only allow admin or local access for security
        if (!is_admin() && !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost'])) {
            show_error('Access denied', 403);
            return;
        }

        $this->load->library('migration');

        if ($this->migration->latest() === FALSE) {
            echo 'Migration error: ' . $this->migration->error_string();
        } else {
            echo 'Migrations completed successfully. Latest version: ' . $this->migration->latest();
        }
    }
}
