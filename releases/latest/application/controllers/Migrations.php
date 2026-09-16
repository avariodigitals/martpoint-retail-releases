<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrations extends CI_Controller {

    public function index(){
        if(PHP_SAPI !== 'cli'){
            show_404();
            return;
        }

        $this->load->library('migration');

        if(!$this->migration->latest()){
            echo "Migration failed:\n" . $this->migration->error_string() . "\n";
            exit(1);
        }

        $version = $this->db->get('migrations')->row()->version ?? 'unknown';
        echo "Migrations complete. Current version: " . $version . "\n";
        exit(0);
    }
}
