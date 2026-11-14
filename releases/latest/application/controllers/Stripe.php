<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Legacy Stripe demo routes. This example accepted arbitrary amounts and card
 * tokens without an authenticated order, so it is intentionally disabled.
 */
class Stripe extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index()
    {
        show_404();
    }

    public function payment()
    {
        show_404();
    }
}
