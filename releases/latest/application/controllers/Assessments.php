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

	private function _physioDocumentSecret(){
		if(!$this->db->field_exists('physio_document_secret', 'db_sitesettings')){
			$this->db->query("ALTER TABLE `db_sitesettings` ADD COLUMN `physio_document_secret` VARCHAR(120) DEFAULT NULL");
		}
		$row = $this->db->select('physio_document_secret')->order_by('id', 'asc')->limit(1)->get('db_sitesettings')->row();
		return $row ? trim((string)$row->physio_document_secret) : '';
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
		if(!$a && !$this->assessments->templateEnabled($template->template_key)){
			$this->show_access_denied_page(); return;
		}
		$patient = $this->db->select('p.*, c.customer_name')
			->from('db_patients p')->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->where('p.id', (int)$enc->patient_id)->get()->row();
		$userSelect = 'u.first_name,u.last_name,u.username,r.role_name';
		if($this->db->field_exists('signature', 'db_users')) $userSelect .= ',u.signature';
		$physioUser = $this->db->select($userSelect)
			->from('db_users u')->join('db_roles r','r.id=u.role_id','left')->where('u.id',(int)$this->session->userdata('inv_userid'))->get()->row();
		$physioName = $physioUser ? trim(($physioUser->first_name ?: '').' '.($physioUser->last_name ?: '')) : '';
		if($physioName === '') $physioName = $physioUser->username ?? ($this->session->userdata('inv_username') ?: '');
		$physioRole = trim($physioUser->role_name ?? '') ?: ($this->session->userdata('role_name') ?: 'Physiotherapist');
		$vitalsSets = $this->encounters->getVitals((int)$enc->id);
		$vitalsSummary = '';
		foreach(array_reverse($vitalsSets ?: array()) as $vitalSet){
			if(($vitalSet->status ?? '') !== 'final') continue;
			$lines = array();
			foreach(($vitalSet->entries ?? array()) as $entry){
				$value = !empty($entry->not_measured) ? 'Not measured' : trim(($entry->value_text ?? '').' '.($entry->unit ?? ''));
				$lines[] = trim(($entry->label ?: $entry->vital_key).' : '.$value);
			}
			$vitalsSummary = 'Nursing vitals set #'.(int)$vitalSet->id.' · finalized '.($vitalSet->finalized_at ?? $vitalSet->created_at ?? '')."\n".implode("\n", $lines);
			break;
		}
		$answers = $a ? (json_decode($a->answers_json, true) ?: array()) : array();
		// These identity fields come from the live queue and logged-in account;
		// therapists should not retype or accidentally change them.
		if(($template->template_key ?? '') === 'physio_initial'){
			if($vitalsSummary !== '' && (!$a || $a->status === 'draft' || empty($answers['medical_nursing_vitals']))) $answers['medical_nursing_vitals'] = $vitalsSummary;
			if(!$a || $a->status === 'draft') $answers['administration_patient_name'] = $patient->customer_name ?? '';
			elseif(empty($answers['administration_patient_name'])) $answers['administration_patient_name'] = $patient->customer_name ?? '';
			if(!$a || $a->status === 'draft') $answers['administration_physiotherapist'] = $physioName;
			elseif(empty($answers['administration_physiotherapist'])) $answers['administration_physiotherapist'] = $a->assessor_name ?? $physioName;
			if(!$a || $a->status === 'draft') $answers['administration_position'] = $physioRole;
			elseif(empty($answers['administration_position'])) $answers['administration_position'] = $physioRole;
			if(!$a || $a->status === 'draft') $answers['administration_signature'] = $physioUser->signature ?? '';
			if(!$a || $a->status === 'draft') $answers['administration_date_of_assessment'] = date('Y-m-d');
			elseif(empty($answers['administration_date_of_assessment'])) $answers['administration_date_of_assessment'] = !empty($a->created_at) ? date('Y-m-d', strtotime($a->created_at)) : '';
		}
		$data = array_merge($this->data, array(
			'page_title' => $template->name,
			'enc'        => $enc,
			'patient'    => $patient,
			'template'   => $template,
			'sections'   => $this->assessments->templateSections($template),
			'assessment' => $a,
			'answers'    => $answers,
			'physio_user'=> $physioUser,
			'amendments' => $a ? $this->assessments->amendments((int)$a->id) : array(),
			'can'        => array(
				'save'     => physio_can('assessments_add'),
				'finalize' => physio_can('assessments_finalize'),
				'amend'    => physio_can('encounters_amend'),
			),
		));
		// Referral partners — a referral raised here is tagged to this
		// assessment so the patient history shows where it came from.
		$data['referral_panel'] = '';
		if(physio_can_any(array('referrals_view','referrals_manage'))){
			$this->load->model('referrals_model', 'referrals');
			$data['referral_panel'] = $this->load->view('referrals/patient_panel', $this->referrals->panelData(
				(int)$enc->patient_id,
				array(
					'source'        => 'assessment',
					'source_id'     => (int)($a->id ?? 0),
					'encounter_id'  => (int)$enc->id,
					'assessment_id' => (int)($a->id ?? 0),
				)
			), TRUE);
		}
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
		if($res === true && (string)($a->template_key ?? '') === 'physio_initial'){
			$answers = json_decode($a->answers_json, true) ?: array();
			$planRemark = trim((string)($answers['interpretation_treatment_plan_reassessment'] ?? ''));
			if($planRemark !== ''){
				$enc = $this->encounters->getEncounter((int)$a->encounter_id);
				if($enc && trim((string)($enc->assessment_remark ?? '')) === ''){
					$this->db->where('id', (int)$enc->id)->where('store_id', get_current_store_id())->update('db_encounters', array(
						'assessment_remark' => $planRemark,
						'remark_by' => (int)$this->session->userdata('inv_userid') ?: null,
						'remark_at' => date('Y-m-d H:i:s'),
					));
					$this->encounters->logEvent((int)$enc->id, get_current_store_id(), 'assessment_remark', null, null, 'Pulled from finalised assessment treatment plan');
				}
			}
		}
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

	/** Download a saved assessment as a patient-specific PDF copy. */
	public function download($id = 0){
		list($a, $err) = $this->_loadAssessment($id);
		if($err){ $this->show_access_denied_page(); return; }
		$template = $this->assessments->getTemplate((int)$a->template_id);
		$enc = $this->encounters->getEncounter((int)$a->encounter_id);
		$patient = $this->db->select('p.*, c.customer_name, c.mobile, c.email')
			->from('db_patients p')->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->where('p.id', (int)$a->patient_id)->where('p.store_id', (int)$a->store_id)->get()->row();
		$secret = $this->_physioDocumentSecret();
		if($secret === ''){
			show_error('Physio document secret is not set. Ask the Clinical Director to set it under Physio Document Settings before downloading assessment PDFs.', 403);
			return;
		}
		$view = $this->load->view('assessments/print', array(
			'assessment' => $a,
			'template' => $template,
			'sections' => $this->assessments->templateSections($template),
			'answers' => json_decode($a->answers_json, true) ?: array(),
			'patient' => $patient,
			'enc' => $enc,
		), TRUE);
		$this->load->library('pdf');
		$this->dompdf->loadHtml($view);
		$this->dompdf->setPaper('A4', 'portrait');
		$this->dompdf->render();
		$canvas = $this->dompdf->getCanvas();
		if($canvas && method_exists($canvas, 'get_cpdf')){
			$canvas->get_cpdf()->setEncryption($secret, $secret, array('print'));
		}
		$this->dompdf->stream('physio-assessment-'.$a->id.'.pdf', array('Attachment' => 1));
	}
}
