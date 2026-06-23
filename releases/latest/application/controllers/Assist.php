<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assist extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('assist_model');
		$this->load->model('customers_model');
		$this->load->model('items_model');
		$this->load->model('sales_model');
		$this->load->model('warehouse_model');
	}

	public function index(){
		// Panel is rendered via view include, not standalone page
		show_404(); exit;
	}

	public function process(){
		$message = trim($this->input->post('message', true) ?? '');
		$sessionId = trim($this->input->post('session_id', true) ?? '');
		if(!$sessionId) $sessionId = session_id();

		if(empty($message)){
			$this->_json(['type' => 'error', 'text' => 'Please enter a message.']);
		}

		$response = $this->assist_model->processMessage($message, $sessionId);
		$this->_json($response);
	}

	public function resolve(){
		$intent = $this->input->post('intent', true);
		$choice = $this->input->post('choice', true);
		$sessionId = trim($this->input->post('session_id', true) ?? session_id());

		$response = $this->assist_model->resolveChoice($intent, $choice, $sessionId);
		$this->_json($response);
	}

	public function confirm(){
		$draftId = $this->input->post('draft_id', true);
		$sessionId = trim($this->input->post('session_id', true) ?? session_id());

		$response = $this->assist_model->executeDraft($draftId, $sessionId);
		$this->_json($response);
	}

	public function cancel(){
		$draftId = $this->input->post('draft_id', true);
		$sessionId = trim($this->input->post('session_id', true) ?? session_id());

		$response = $this->assist_model->cancelDraft($draftId, $sessionId);
		$this->_json($response);
	}

	public function quick_action(){
		$action = $this->input->post('action', true);
		$sessionId = trim($this->input->post('session_id', true) ?? session_id());

		$response = $this->assist_model->startQuickActionFlow($action, $sessionId);
		$this->_json($response);
	}

	public function support_request(){
		$name = trim($this->input->post('name', true) ?? '');
		$email = trim($this->input->post('email', true) ?? '');
		$subject = trim($this->input->post('subject', true) ?? '');
		$message = trim($this->input->post('message', true) ?? '');

		if(empty($name) || empty($email) || empty($subject) || empty($message)){
			$this->_json(['success' => false, 'error' => 'All fields are required.']);
			return;
		}
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$this->_json(['success' => false, 'error' => 'Invalid email address.']);
			return;
		}

		$this->load->library('email');
		$this->email->from($email, $name);
		$this->email->to('support@martpoint.com.ng');
		$this->email->subject('[MartPoint Support] ' . $subject);
		$this->email->message(
			"Support Request from MartPoint Assist\n\n" .
			"Name: {$name}\n" .
			"Email: {$email}\n" .
			"Subject: {$subject}\n\n" .
			"Message:\n{$message}\n\n" .
			"---\nSent via MartPoint Assist Support Form"
		);

		if($this->email->send()){
			$this->_json(['success' => true]);
		} else {
			$this->_json(['success' => false, 'error' => 'Email could not be sent. Please try again later.']);
		}
	}

	public function topics(){
		$q = strtolower(trim($this->input->get('q', true) ?? ''));
		$this->load->model('assist_knowledge_model');
		$topics = $this->assist_knowledge_model->searchTopics($q);
		$this->_json(['topics' => $topics]);
	}

	public function topic(){
		$id = $this->input->get('id', true);
		$this->load->model('assist_knowledge_model');
		$this->load->model('assist_model');
		$entry = $this->assist_knowledge_model->getTopicById($id);
		if($entry){
			$followUp = $this->assist_model->getKbFollowUp($entry['category'] ?? 'general');
			$this->_json([
				'id'          => $entry['id'],
				'answer'      => $entry['answer'],
				'follow_up'   => $followUp['text'],
				'quick_tasks' => $followUp['tasks'] ?? []
			]);
		} else {
			$this->_json(['error' => 'Topic not found']);
		}
	}

	public function guide(){
		$name = $this->input->get('name', true) ?? 'features';
		$safe = preg_replace('/[^a-z0-9_-]/i', '', $name);
		$file = FCPATH . 'guides/' . $safe . '_pdf.html';
		if(!file_exists($file)){
			$file = FCPATH . 'guides/features_pdf.html';
		}
		$html = file_get_contents($file);
		// Strip script tags for safety
		$html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $html);
		$this->_json(['html' => $html]);
	}

	/**
	 * Knowledge Base Guide — HTML page opened in new tab, role-based content
	 */
	public function kb_guide(){
		$this->load->model('assist_knowledge_model');
		$roleLevel = $this->assist_knowledge_model->getUserRoleLevel();
		$levelVal  = $this->assist_knowledge_model->roleHierarchy[$roleLevel] ?? 0;

		$data['role_label']    = ucwords(str_replace('_', ' ', $roleLevel));
		$data['show_cashier']  = ($levelVal >= 0); // everyone sees cashier stuff
		$data['show_manager']  = ($levelVal >= 2); // business_owner & admin

		$this->load->view('assist/kb_guide', $data);
	}

	private function _json($data){
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}
}
