<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Verify_isolation — proves the physiotherapy shell is scoped to ONE business
 * type (CLI only).
 *
 *   php index.php verify_isolation run
 *
 * Every other business type must keep the standard MartPoint shell, dashboard
 * and mobile experience. This runs the same resolvers the app uses
 * (physio_enabled() -> mp_get_store_profile()) against every registered
 * business type on a throwaway store row, so it proves the real code path
 * rather than restating the condition.
 *
 * The scratch store is created and removed inside this run; no existing store
 * or profile is touched.
 */
class Verify_isolation extends CI_Controller {

	private $scratchId = 0;
	private $pass = 0;
	private $fail = 0;

	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
		if(!function_exists('physio_enabled')) $this->load->helper('physio');
		if(!function_exists('mp_get_business_types')) $this->load->helper('business_profile');
	}

	private function check($name, $cond, $detail = ''){
		$ok = (bool)$cond;
		$this->rows[] = array($name, $ok, $detail);
		if($ok) $this->pass++; else $this->fail++;
		printf("%s  %-58s %s\n", $ok ? 'PASS' : 'FAIL', $name, $detail);
	}

	private function makeScratchStore(){
		$this->db->insert('db_store', array(
			'store_code' => 'ISO_VERIFY',
			'store_name' => 'Isolation verification (temporary)',
			'status'     => 0,
			'cid'        => 1,
		));
		$this->scratchId = (int)$this->db->insert_id();
		return $this->scratchId;
	}

	private function setIndustry($industry){
		$table = 'db_store_industry_settings';
		$exists = $this->db->where('store_id', $this->scratchId)->count_all_results($table);
		if($exists){
			$this->db->where('store_id', $this->scratchId)->update($table, array('industry_type' => $industry));
		} else {
			$this->db->insert($table, array('store_id' => $this->scratchId, 'industry_type' => $industry));
		}
		// The older profile table takes precedence in some installs; keep it
		// consistent so the test reflects whichever resolver the install uses.
		if($this->db->table_exists('db_store_business_profile')){
			if($this->db->where('store_id', $this->scratchId)->count_all_results('db_store_business_profile')){
				$this->db->where('store_id', $this->scratchId)->update('db_store_business_profile', array('industry_type' => $industry));
			} else {
				$this->db->insert('db_store_business_profile', array('store_id' => $this->scratchId, 'industry_type' => $industry));
			}
		}
	}

	private function cleanup(){
		if(!$this->scratchId) return;
		foreach(array('db_store_industry_settings','db_store_business_profile') as $t){
			if($this->db->table_exists($t)) $this->db->where('store_id', $this->scratchId)->delete($t);
		}
		$this->db->where('id', $this->scratchId)->delete('db_store');
	}

	public function run(){
		echo "== Physiotherapy shell isolation ==\n\n";
		$types = mp_get_business_types();
		$clinical = 'physiotherapy_rehabilitation';
		echo 'business types registered: ' . count($types) . "\n";
		echo "expected clinical type:    {$clinical}\n\n";

		$this->makeScratchStore();
		if(!$this->scratchId){ fwrite(STDERR, "could not create scratch store\n"); exit(2); }

		$activatedFor = array();
		foreach($types as $key => $label){
			$this->setIndustry($key);
			// Fresh instance of the resolver — no memoisation between types.
			$on = physio_enabled($this->scratchId);
			if($on) $activatedFor[] = $key;
		}

		$this->check('shell activates for exactly one business type',
			count($activatedFor) === 1, 'activated: ' . (implode(', ', $activatedFor) ?: 'none'));
		$this->check('that type is physiotherapy_rehabilitation',
			$activatedFor === array($clinical), 'got: ' . (implode(', ', $activatedFor) ?: 'none'));

		// Spot-check a representative spread so a single bad preset cannot hide.
		$spot = array('clinic', 'hospital', 'general_retail', 'pharmacy', 'restaurant',
			'salon', 'nylon_polythene', 'creator', 'manufacturing', 'grocery_store');
		foreach($spot as $key){
			if(!isset($types[$key])) continue;
			$this->setIndustry($key);
			$this->check("standard shell kept for '{$key}'", physio_enabled($this->scratchId) === false,
				$types[$key]);
		}

		$this->setIndustry($clinical);
		$this->check("clinical shell active for '{$clinical}'", physio_enabled($this->scratchId) === true,
			$types[$clinical]);

		$this->cleanup();

		echo "\n== SUMMARY: {$this->pass} passed, {$this->fail} failed ==\n";
		exit($this->fail ? 1 : 0);
	}
}
