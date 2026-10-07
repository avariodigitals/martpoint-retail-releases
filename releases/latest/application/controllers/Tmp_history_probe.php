<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** TEMPORARY verification probe — deleted after use. */
class Tmp_history_probe extends CI_Controller {
	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
		// Session first (load_global redirects when not logged in), then the
		// same bootstrap the real controllers do.
		$this->session->set_userdata(array('inv_userid' => 114, 'inv_username' => 'director_t',
			'role_id' => 62, 'store_id' => 3, 'logged_in' => 1, 'display_name' => 'Clinic Director'));
		$this->load_global();
	}

	public function run($patientId = 0){
		$this->load->model('patients_model', 'p');
		$patientId = (int)$patientId;
		$patient = $this->p->getPatient($patientId, 3);
		if(!$patient){ echo "patient {$patientId} not found\n"; return; }
		$html = $this->load->view('patients/profile', array(
			'page_title' => $patient->customer_name,
			'patient'    => $patient,
			'episodes'   => $this->p->getEpisodes($patientId, 3),
			'appointments' => $this->p->getAppointments($patientId, 3),
			'events'     => $this->p->getEvents($patientId, 3),
			'deceased_by' => null,
			'can_edit'   => false,
			'theme_link' => base_url().'theme/',
		), TRUE);

		$has = function($needle) use ($html){ return strpos($html, $needle) !== false; };
		$importedRows = substr_count($html, 'fa-stethoscope') + substr_count($html, 'fa-bed')
			+ substr_count($html, 'fa-th') + substr_count($html, 'fa-sign-out') + substr_count($html, 'fa-file-text-o');
		echo "patient {$patientId} ({$patient->customer_name})\n";
		echo "  view rendered:         " . (strlen($html) > 2000 ? 'yes (' . strlen($html) . " bytes)" : 'NO') . "\n";
		echo "  imported section:      " . ($has('Clinical history — imported from previous system') ? 'present' : 'MISSING') . "\n";
		echo "  states no money posted:" . ($has('no charges, invoices or balances were created from them') ? ' yes' : ' NO') . "\n";
		echo "  imported record rows:  {$importedRows}\n";
		echo "  audit log card:        " . ($has('<h4>Audit Log</h4>') ? 'present' : 'MISSING') . "\n";
		echo "  source ids shown:      " . (preg_match('/sh4_opd#\d+/', str_replace(array("\n"," "), '', $html)) ? 'yes' : 'no') . "\n";
		echo "  div balance:           " . (substr_count($html, '<div') - substr_count($html, '</div>')) . " (0 = balanced)\n";
	}
}
