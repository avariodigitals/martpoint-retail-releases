<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Imports — Stage 7 legacy-migration console (physio business type).
 *
 * SOURCE STATUS: the Smart Hospital 4.0 SQL dump is not yet available.
 * The framework (batches, dedupe, resume, rollback, reconciliation) is
 * complete and tested; the SH4 table extractors are PENDING the dump.
 * The interim intake accepts a normalized JSON extract so a converted
 * export can be imported today without pretending a source mapping.
 */
class Imports extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !physio_can('imports_view')){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('imports_model', 'imports');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	public function index(){
		$data = array_merge($this->data, array(
			'page_title'     => 'Legacy Import — Migration Console',
			'batches'        => $this->imports->batches(),
			'reconciliation' => $this->imports->storeReconciliation(),
			'can' => array(
				'run'      => physio_can('imports_run'),
				'rollback' => physio_can('imports_rollback'),
			),
			'source_ready'   => false, // SH4 SQL not yet supplied — mapping pending
		));
		$data['content'] = $this->load->view('imports/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function batch($id = 0){
		$batch = $this->imports->batch((int)$id);
		if(!$batch){ $this->show_access_denied_page(); return; }
		$data = array_merge($this->data, array(
			'page_title'     => 'Import Batch #' . $batch->id,
			'batch'          => $batch,
			'rows'           => $this->imports->batchRows($batch->id),
			'reconciliation' => $this->imports->reconciliation($batch->id),
			'can' => array(
				'run'      => physio_can('imports_run'),
				'rollback' => physio_can('imports_rollback'),
			),
		));
		$data['content'] = $this->load->view('imports/batch', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/**
	 * Start a batch from a NORMALIZED JSON extract (interim format —
	 * the SH4 extractor produces this same contract once mapped):
	 *   { "patients": [ {legacy_id,name,phone,email,gender,dob,address,
	 *                    deceased,deceased_date,deceased_notes,inactive} ] }
	 * mode=dry_run validates and records nothing.
	 */
	public function run(){
		if(!physio_can('imports_run')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$mode  = $this->input->post('mode') === 'dry_run' ? 'dry_run' : 'import';
		$label = trim($this->input->post('label', TRUE) ?: '');
		$json  = (string)$this->input->post('extract_json');
		$src   = trim($this->input->post('source_system', TRUE) ?: 'smarthospital4');
		$rows  = json_decode($json, true);
		if(!is_array($rows) || empty($rows['patients']) || !is_array($rows['patients'])){
			$this->_json(array('status' => 'error', 'message' => 'Invalid extract — expected {"patients":[…]}')); return;
		}
		$batchId = $this->imports->startBatch($src, $label, $mode);
		$done = 0; $checkpoint = array('patients_cursor' => 0);
		if($mode === 'dry_run'){
			foreach($rows['patients'] as $i => $r){
				$bad = empty($r['legacy_id']) || empty($r['name']);
				$this->imports->record($batchId, 'patients', (string)($r['legacy_id'] ?? '?'),
					$bad ? 'failed' : 'skipped',
					array(), $bad ? 'missing required field' : 'dry run — would import');
				$checkpoint['patients_cursor'] = $i + 1;
			}
		} else {
			foreach($rows['patients'] as $i => $r){
				$res = $this->imports->importPatient($batchId, $r);
				if(empty($res['ok']) && empty($res['skipped'])){
					$this->imports->record($batchId, 'patients', (string)($r['legacy_id'] ?? '?'), 'failed', array(), null, $res['error'] ?? 'import failed');
				}
				$checkpoint['patients_cursor'] = $i + 1;
				$done++;
			}
		}
		$this->imports->checkpoint($batchId, $checkpoint);
		$this->imports->finishBatch($batchId);
		$this->_json(array('status' => 'success', 'message' => ($mode === 'dry_run' ? 'Dry run complete' : 'Import complete'),
			'batch_id' => $batchId));
	}

	public function resume($batchId = 0){
		if(!physio_can('imports_run')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$cursor = $this->imports->resumeBatch((int)$batchId);
		$this->_json(array('status' => 'success', 'message' => 'Batch resumed', 'cursor' => $cursor));
	}

	public function pause($batchId = 0){
		if(!physio_can('imports_run')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$this->imports->pauseBatch((int)$batchId);
		$this->_json(array('status' => 'success', 'message' => 'Batch paused'));
	}

	public function rollback($batchId = 0){
		if(!physio_can('imports_rollback')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->imports->rollback((int)$batchId);
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => !empty($res['replayed']) ? 'Already rolled back' : 'Rolled back — ' . (int)$res['deleted'] . ' target rows removed')
			: array('status' => 'error', 'message' => $res['error']));
	}
}
