<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MartPoint Retail — Marketing landing controller (desktop).
 *
 * Renders the desktop Marketing landing page inside the AdminLTE shell.
 * The item list, ordering and permission gating come from the shared
 * marketing_menu_items() helper (application/helpers/marketing_helper.php),
 * which is also used by Mobile::marketing() — so mobile and desktop share
 * one source of truth for the menu logic. Each item carries url_desktop
 * so clicks stay inside desktop controllers.
 */
class Marketing extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	public function index()
	{
		// Show the landing page if the user holds any marketing permission.
		if(!(
			$this->permissions('discountCouponView') ||
			$this->permissions('customerCouponView') ||
			$this->permissions('discountCouponAdd') ||
			$this->permissions('customerCouponAdd') ||
			$this->permissions('loyalty_view') ||
			$this->permissions('gift_cards_view') ||
			$this->permissions('store_credit_view')
		)){
			$this->show_access_denied_page();
		}

		$data = $this->data;
		$data['page_title'] = 'Marketing';
		$data['marketing_items'] = function_exists('marketing_menu_items') ? marketing_menu_items() : [];
		$data['content'] = $this->load->view('marketing/desktop/overview', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
}
