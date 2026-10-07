<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Assessments — versioned clinical forms.
 *
 * form     : render the template; drafts editable by their assessor,
 *            finals render read-only with an amendment history panel.
 * save     : draft save (assessments_add).
 * finalize : required-field validation then lock (assessments_finalize).
 * amend    : attributed, reasoned changes to a final (encounters_amend).
 */
class Assessments extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !mp_feature_enabled('patient_registry')){
			$this->show_feature_not_activated('patient_registry'); return;
		}
		if(!physio_can('assessments_view')){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('assessments_model', 'assessments');
		$this->load->model('encounters_model', 'encounters');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	private function _loadAssessment($id){
		$a = $this->assessments->get((int)$id);
		if(!$a) return array(null, 'Assessment not found');
		$enc = $this->encounters->getEncounter((int)$a->encounter_id);
		if($enc && $enc->warehouse_id && !physio_can_branch($enc->warehouse_id)){
			return array(null, 'Access denied');
		}
		return array($a, null);
	}

	public function form($id = 0){
		$encId = (int)$this->input->get('encounter_id');
		if($id){
			list($a, $err) = $this->_loadAssessment($id);
			if($err){ $this->show_access_denied_page(); return; }
			$enc = $this->encounters->getEncounter((int)$a->encounter_id);
		} else {
			$enc = $this->encounters->getEncounter($encId);
			if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
				$this->show_access_denied_page(); return;
			}
			$a = null;
		}
		$template = $a ? $this->assessments->getTemplate((int)$a->template_id)
		             : $this->assessments->getTemplate((int)$this->input->get('template_id'));
		if(!$template){ $this->show_access_denied_page(); return; }
		$patient = $this->db->select('p.*, c.customer_name')
			->from('db_patients p')->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->where('p.id', (int)$enc->patient_id)->get()->row();
		$data = array_merge($this->data, array(
			'page_title' => $template->name,
			'enc'        => $enc,
			'patient'    => $patient,
			'template'   => $template,
			'sections'   => $this->assessments->templateSections($template),
			'assessment' => $a,
			'answers'    => $a ? (json_decode($a->answers_json, true) ?: array()) : array(),
			'amendments' => $a ? $this->assessments->amendments((int)$a->id) : array(),
			'can'        => array(
				'save'     => physio_can('assessments_add'),
				'finalize' => physio_can('assessments_finalize'),
				'amend'    => physio_can('encounters_amend'),
			),
		));
		$data['content'] = $this->load->view('assessments/form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save(){
		if(!physio_can('assessments_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$answers = $this->input->post('answers');
		if(!is_array($answers)){
			$answers = array();
			foreach((array)$this->input->post() as $k => $v){
				if(strpos($k, 'ans__') === 0) $answers[substr($k, 5)] = $v;
			}
		}
		$res = $this->assessments->saveDraft(array(
			'encounter_id' => (int)$this->input->post('encounter_id'),
			'template_id'  => (int)$this->input->post('template_id'),
			'answers'      => $answers,
		), (int)$this->input->post('assessment_id'));
		$this->_json(isset($res['error'])
			? array('status' => 'error', 'message' => $res['error'])
			: array('status' => 'success', 'message' => 'Draft saved', 'assessment_id' => $res['assessment_id']));
	}

	public function finalize($id = 0){
		if(!physio_can('assessments_finalize')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		list($a, $err) = $this->_loadAssessment($id);
		if($err){ $this->_json(array('status' => 'error', 'message' => $err)); return; }
		$res = $this->assessments->finalize((int)$id);
		$this->_json($res === true
			? array('status' => 'success', 'message' => 'Assessment finalised — locked')
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function amend($id = 0){
		if(!physio_can('encounters_amend')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		list($a, $err) = $this->_loadAssessment($id);
		if($err){ $this->_json(array('status' => 'error', 'message' => $err)); return; }
		$changes = array();
		foreach((array)$this->input->post('amend') as $k => $v){ $changes[$k] = $v; }
		$res = $this->assessments->amend((int)$id, $changes,
			trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res === true
			? array('status' => 'success', 'message' => 'Amendment recorded — original preserved')
			: array('status' => 'error', 'message' => $res['error']));
	}
}
