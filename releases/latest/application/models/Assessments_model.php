<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Clinical assessments — versioned templates, draft/final responses,
 * append-only attributed amendments.
 *
 * Drafts are editable by the assessor. Finalising validates every required
 * field of the template version used, then locks the answers. Corrections
 * to a final assessment are amendments: each records the ORIGINAL value,
 * the new value, the actor and a reason — the prior state is never erased.
 */
class Assessments_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	// ------------------------------------------------------------------
	// Templates
	// ------------------------------------------------------------------
	public function getTemplate($templateId){
		return $this->db->where('id', (int)$templateId)->get('db_assessment_templates')->row();
	}

	/** Active templates visible to this store — newest version per key, store override wins. */
	public function listTemplates($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$rows = $this->db->where('status', 'active')
			->group_start()->where('store_id', 0)->or_where('store_id', $storeId)->group_end()
			->order_by('template_key')->order_by('version', 'desc')->order_by('store_id', 'desc')
			->get('db_assessment_templates')->result();
		$seen = array(); $out = array();
		foreach($rows as $r){
			if(isset($seen[$r->template_key])) continue;
			$seen[$r->template_key] = true; $out[] = $r;
		}
		return $out;
	}

	// ============== TEMPLATE ADMINISTRATION ==============

	/** All versions (any status) for a template key within this store's scope. */
	public function templateVersions($templateKey, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('template_key', $templateKey)
			->group_start()->where('store_id', 0)->or_where('store_id', $storeId)->group_end()
			->order_by('version', 'desc')->get('db_assessment_templates')->result();
	}

	public function templateGroups($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$rows = $this->db
			->group_start()->where('store_id', 0)->or_where('store_id', $storeId)->group_end()
			->order_by('template_key')->order_by('version', 'desc')
			->get('db_assessment_templates')->result();
		$groups = array();
		foreach($rows as $r){ $groups[$r->template_key][] = $r; }
		return $groups;
	}

	/** Clone the latest version of a key into a new draft (version+1) for this store. */
	public function draftNewVersion($templateKey, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$latest = $this->db->where('template_key', $templateKey)
			->group_start()->where('store_id', 0)->or_where('store_id', $storeId)->group_end()
			->order_by('version', 'desc')->limit(1)->get('db_assessment_templates')->row();
		if(!$latest) return array('error' => 'Template not found');
		$this->db->insert('db_assessment_templates', array(
			'store_id'      => $storeId,
			'template_key'  => $latest->template_key,
			'name'          => $latest->name,
			'version'       => (int)$latest->version + 1,
			'status'        => 'draft',
			'provisional'   => (int)$latest->provisional,
			'sections_json' => $latest->sections_json,
			'created_by'    => $this->session->userdata('inv_username') ?: 'system',
		));
		return array('id' => (int)$this->db->insert_id(), 'version' => (int)$latest->version + 1);
	}

	public function createDraft($templateKey, $name, $sectionsJson, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		if($templateKey === '' || $name === '') return array('error' => 'Key and name are required');
		if(!preg_match('/^[a-z0-9_]+$/', $templateKey)) return array('error' => 'Key: lowercase letters, digits, underscores only');
		$dup = $this->db->where('template_key', $templateKey)->where('store_id', $storeId)
			->where('version', 1)->get('db_assessment_templates')->row();
		if($dup) return array('error' => 'A template with this key already exists — create a new version instead');
		json_decode($sectionsJson); if(json_last_error() !== JSON_ERROR_NONE) return array('error' => 'Sections JSON is invalid');
		$this->db->insert('db_assessment_templates', array(
			'store_id' => $storeId, 'template_key' => $templateKey, 'name' => $name,
			'version' => 1, 'status' => 'draft', 'provisional' => 0,
			'sections_json' => $sectionsJson, 'created_by' => $this->session->userdata('inv_username') ?: 'system',
		));
		return array('id' => (int)$this->db->insert_id());
	}

	public function updateDraft($id, $name, $sectionsJson, $provisional, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$t = $this->getTemplate((int)$id);
		if(!$t || (int)$t->store_id !== (int)$storeId) return array('error' => 'Template not found');
		if($t->status !== 'draft') return array('error' => 'Only drafts can be edited — create a new version to change a published template');
		json_decode($sectionsJson); if(json_last_error() !== JSON_ERROR_NONE) return array('error' => 'Sections JSON is invalid');
		$this->db->where('id', $t->id)->update('db_assessment_templates', array(
			'name' => $name !== '' ? $name : $t->name,
			'sections_json' => $sectionsJson,
			'provisional' => $provisional ? 1 : 0,
		));
		return true;
	}

	/** Publish a draft: previous active store version of the same key is archived. */
	public function publishTemplate($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$t = $this->getTemplate((int)$id);
		if(!$t || (int)$t->store_id !== (int)$storeId) return array('error' => 'Template not found');
		if($t->status !== 'draft') return array('error' => 'Only drafts can be published');
		$this->db->trans_start();
		$this->db->where('store_id', $storeId)->where('template_key', $t->template_key)
			->where('status', 'active')->where('id !=', $t->id)
			->update('db_assessment_templates', array('status' => 'archived'));
		$this->db->where('id', $t->id)->update('db_assessment_templates', array('status' => 'active'));
		$this->db->trans_complete();
		return true;
	}

	public function templateSections($template){
		$sections = json_decode($template->sections_json, true);
		return is_array($sections) ? $sections : array();
	}

	// ------------------------------------------------------------------
	// Assessments
	// ------------------------------------------------------------------
	public function get($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', (int)$id)->where('store_id', $storeId)
			->get('db_assessments')->row();
	}

	public function listForEncounter($encId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('encounter_id', (int)$encId)->where('store_id', $storeId)
			->order_by('id', 'desc')->get('db_assessments')->result();
	}

	public function listForPatient($patientId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('patient_id', (int)$patientId)->where('store_id', $storeId)
			->order_by('id', 'desc')->get('db_assessments')->result();
	}

	/**
	 * Save a draft assessment (create or update). $answers is a flat map of
	 * "section_key.field_key" => value. Only the creator may edit a draft.
	 */
	public function saveDraft(array $d, $assessmentId = 0){
		$storeId = get_current_store_id();
		$uid  = (int)$this->session->userdata('inv_userid') ?: null;
		$name = $this->session->userdata('inv_username') ?: 'system';

		$template = $this->getTemplate((int)($d['template_id'] ?? 0));
		if(!$template) return array('error' => 'Template not found');

		if($assessmentId){
			$a = $this->get($assessmentId, $storeId);
			if(!$a) return array('error' => 'Assessment not found');
			if($a->status === 'final') return array('error' => 'Final assessments are locked — record an amendment');
			if((int)$a->assessor_user_id !== $uid) return array('error' => 'Only the assessor can edit this draft');
			$this->db->where('id', $assessmentId)->where('store_id', $storeId)->update('db_assessments', array(
				'answers_json' => json_encode($d['answers'] ?? new stdClass),
				'updated_at'   => date('Y-m-d H:i:s'),
			));
			return array('assessment_id' => $assessmentId);
		}

		$enc = $this->db->where('id', (int)$d['encounter_id'])->where('store_id', $storeId)
			->get('db_encounters')->row();
		if(!$enc) return array('error' => 'Visit not found');
		$this->db->insert('db_assessments', array(
			'store_id'         => $storeId,
			'encounter_id'     => (int)$enc->id,
			'episode_id'       => $enc->episode_id ? (int)$enc->episode_id : null,
			'patient_id'       => (int)$enc->patient_id,
			'template_id'      => (int)$template->id,
			'template_key'     => $template->template_key,
			'template_version' => (int)$template->version,
			'status'           => 'draft',
			'answers_json'     => json_encode($d['answers'] ?? new stdClass),
			'assessor_user_id' => $uid,
			'assessor_name'    => $name,
			'created_by'       => $name,
			'created_at'       => date('Y-m-d H:i:s'),
		));
		return array('assessment_id' => (int)$this->db->insert_id());
	}

	/** Required-field check against the template version in use. */
	public function missingRequired($template, array $answers){
		$missing = array();
		foreach($this->templateSections($template) as $sec){
			foreach(($sec['fields'] ?? array()) as $f){
				if(!empty($f['required'])){
                                    // Underscore, not dot: PHP rewrites '.' to '_'
                                    // in incoming POST keys, so the answer keys the
                                    // form submits are always underscored. With a dot
                                    // here every required field looked empty and
                                    // finalise refused with "required fields empty".
                                    $k = $sec['key'] . '_' . $f['key'];
                                    $v = $answers[$k] ?? null;
                                    if($v === null || $v === '') $missing[] = ($f['label'] ?? $k);
				}
			}
		}
		return $missing;
	}

	/**
	 * Finalise: validate required fields, lock the record.
	 * Caller holds assessments_finalize. Returns true|array('error'=>).
	 */
	public function finalize($assessmentId){
		$storeId = get_current_store_id();
		$a = $this->get($assessmentId, $storeId);
		if(!$a) return array('error' => 'Assessment not found');
		if($a->status === 'final') return array('error' => 'Already finalised');
		$template = $this->getTemplate((int)$a->template_id);
		$answers  = json_decode($a->answers_json, true) ?: array();
		$missing  = $this->missingRequired($template, $answers);
		if($missing){
			return array('error' => 'Required fields missing: ' . implode(', ', $missing));
		}
		$this->db->where('id', $assessmentId)->where('store_id', $storeId)->update('db_assessments', array(
			'status'       => 'final',
			'finalized_at' => date('Y-m-d H:i:s'),
			'finalized_by' => (int)$this->session->userdata('inv_userid') ?: null,
			'updated_at'   => date('Y-m-d H:i:s'),
		));
		return true;
	}

	/**
	 * Amend a FINAL assessment — append-only, attributed, reasoned.
	 * $changes: flat map "section.field" => new value. For each changed key
	 * the ORIGINAL stored answer is recorded alongside the new one.
	 * Caller holds encounters_amend. Returns true|array('error'=>).
	 */
	public function amend($assessmentId, array $changes, $reason){
		$storeId = get_current_store_id();
		$a = $this->get($assessmentId, $storeId);
		if(!$a) return array('error' => 'Assessment not found');
		if($a->status !== 'final') return array('error' => 'Only final assessments are amended');
		$reason = trim((string)$reason);
		if($reason === '') return array('error' => 'An amendment reason is required');

		$template = $this->getTemplate((int)$a->template_id);
		$labels   = array();
		foreach($this->templateSections($template) as $sec){
			foreach(($sec['fields'] ?? array()) as $f){
				// Underscore to match the answer keys (see missingRequired).
				$labels[$sec['key'] . '_' . $f['key']] = $f['label'] ?? $f['key'];
			}
		}
		$answers  = json_decode($a->answers_json, true) ?: array();
		$diff = array();
		foreach($changes as $k => $newVal){
			$oldVal = $answers[$k] ?? null;
			if($newVal === $oldVal) continue;
			$diff[] = array('field' => $k, 'label' => $labels[$k] ?? $k, 'from' => $oldVal, 'to' => $newVal);
			$answers[$k] = $newVal;
		}
		if(empty($diff)) return array('error' => 'No changes supplied');

		// Re-validate required fields — an amendment cannot blank a required field.
		$missing = $this->missingRequired($template, $answers);
		if($missing) return array('error' => 'Amendment would leave required fields empty: ' . implode(', ', $missing));

		$uid  = (int)$this->session->userdata('inv_userid') ?: null;
		$name = $this->session->userdata('inv_username') ?: 'system';
		$this->db->trans_start();
		$this->db->insert('db_assessment_amendments', array(
			'store_id'        => $storeId,
			'assessment_id'   => $assessmentId,
			'reason'          => $reason,
			'changes_json'    => json_encode($diff),
			'amended_by'      => $uid,
			'amended_by_name' => $name,
			'created_at'      => date('Y-m-d H:i:s'),
		));
		$this->db->where('id', $assessmentId)->where('store_id', $storeId)->update('db_assessments', array(
			'answers_json' => json_encode($answers),
			'updated_at'   => date('Y-m-d H:i:s'),
		));
		$this->db->trans_complete();
		return $this->db->trans_status() === FALSE ? array('error' => 'Amendment failed') : true;
	}

	public function amendments($assessmentId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('assessment_id', (int)$assessmentId)->where('store_id', $storeId)
			->order_by('id', 'asc')->get('db_assessment_amendments')->result();
	}
}
