<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assist_model extends CI_Model {

	private $intents = [
		'SEARCH_CUSTOMER'     => ['find','search','lookup','customer','who is'],
		'CREATE_CUSTOMER'     => ['create customer','add customer','new customer','register customer'],
		'CREATE_RAW_MATERIAL' => ['raw material','raw materials','ingredient','ingredients','consumable','consumables','add material','new material','add ingredient','new ingredient','create ingredient','create material'],
		'SEARCH_PRODUCT'      => ['find product','search product','product','item'],
		'CHECK_STOCK'         => ['stock','how many','quantity','left','available'],
		'LOW_STOCK'           => ['low stock','running low','reorder','out of stock'],
		'CREATE_SALE'         => ['sell','sale','create sale','make sale','checkout'],
		'CREATE_INVOICE'      => ['invoice','create invoice','bill'],
		'CREATE_EXPENSE'      => ['expense','record expense','spend'],
		'CREATE_PURCHASE'     => ['purchase','buy','order','purchase order'],
		'BUSINESS_SUMMARY'    => ['summary','today sales','how much','performance','report'],
		'PROFIT_REPORT'       => ['profit','profit report','how much profit','today profit','profit today','my profit'],
		'TOP_PRODUCTS'        => ['top products','best sellers','best selling'],
		'CUSTOMER_BALANCE'    => ['balance','owe','debt','outstanding'],
		'DEBT_SUMMARY'        => ['debts','who owes','credit customers'],
		'ONLINE_ORDER_SUMMARY'=> ['online orders','online sales','web orders'],
		'GENERATE_PAYMENT_LINK'=> ['payment link','pay link','generate link'],
		'CREATE_QUOTATION'    => ['quotation','quote','estimate'],
		'CREATE_SERVICE_ORDER'=> ['service','repair','booking'],
		'HELP'                => ['help','what can you do','commands','hello','hi','hey','good morning','good afternoon','good evening'],
	];

	private $quickTasks = [
		['label' => 'Create Sale', 'action' => 'create_sale', 'icon' => 'fa-shopping-cart'],
		['label' => 'Check Stock', 'action' => 'check_stock', 'icon' => 'fa-cubes'],
		['label' => 'Find Customer', 'action' => 'find_customer', 'icon' => 'fa-users'],
		['label' => "Today's Sales", 'action' => 'today_sales', 'icon' => 'fa-line-chart'],
		['label' => 'Record Expense', 'action' => 'record_expense', 'icon' => 'fa-money'],
		['label' => 'Low Stock', 'action' => 'low_stock', 'icon' => 'fa-exclamation-triangle'],
		['label' => 'View Debts', 'action' => 'view_debts', 'icon' => 'fa-handshake-o'],
		['label' => 'Online Orders', 'action' => 'online_orders', 'icon' => 'fa-globe'],
	];

	public function __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->model('assist_knowledge_model');
		$this->load->library('assist_brain');
	}

	/**
	 * Get filtered quick tasks based on user role level
	 */
	private function _getFilteredQuickTasks(){
		$roleLevel = $this->assist_knowledge_model->getUserRoleLevel();
		$levelVal = $this->assist_knowledge_model->roleHierarchy[$roleLevel] ?? 0;

		$itemLabel = $this->_term('item', 'Item');
		$allTasks = [
			['label' => 'Create Sale', 'action' => 'create_sale', 'icon' => 'fa-shopping-cart', 'min_role' => 'all'],
			['label' => 'Check Stock', 'action' => 'check_stock', 'icon' => 'fa-cubes', 'min_role' => 'all'],
			['label' => 'Find '.$this->_term('customer', 'Customer'), 'action' => 'find_customer', 'icon' => 'fa-users', 'min_role' => 'all'],
			['label' => "Today's Sales", 'action' => 'today_sales', 'icon' => 'fa-line-chart', 'min_role' => 'cashier'],
			['label' => 'Daily Summary', 'action' => 'daily_summary', 'icon' => 'fa-file-text-o', 'min_role' => 'cashier'],
			['label' => 'Record Expense', 'action' => 'record_expense', 'icon' => 'fa-money', 'min_role' => 'cashier'],
			['label' => 'Low Stock', 'action' => 'low_stock', 'icon' => 'fa-exclamation-triangle', 'min_role' => 'all'],
			['label' => 'View Debts', 'action' => 'view_debts', 'icon' => 'fa-handshake-o', 'min_role' => 'cashier'],
			['label' => 'Online Orders', 'action' => 'online_orders', 'icon' => 'fa-globe', 'min_role' => 'all'],
			['label' => 'New '.$itemLabel, 'action' => 'create_item', 'icon' => 'fa-plus-circle', 'min_role' => 'business_owner'],
		];

		// Business-type aware tasks: only show when the store's preset enables the feature
		if($this->_featAny(['recipe_tracking','production_workflow'])){
			$allTasks[] = ['label' => 'Add '.$this->_rawMaterialLabel(), 'action' => 'create_raw_material', 'icon' => 'fa-leaf', 'min_role' => 'business_owner'];
		}

		$filtered = [];
		foreach($allTasks as $task){
			$taskLevel = $this->assist_knowledge_model->roleHierarchy[$task['min_role']] ?? 0;
			if($levelVal >= $taskLevel){
				$filtered[] = ['label' => $task['label'], 'action' => $task['action'], 'icon' => $task['icon']];
			}
		}
		return $filtered;
	}

	public function processMessage($message, $sessionId){
		$message = strtolower(trim($message));
		if(empty($message)){
			return $this->_welcomeResponse();
		}

		// Greetings should always trigger the welcome response before any KB lookup
		$greetings = ['hi','hey','hello','good morning','good afternoon','good evening','greetings','morning','evening'];
		$msgWords = array_filter(preg_split('/\s+/', $message));
		if(in_array($message, $greetings) || in_array($msgWords[0] ?? '', ['hi','hey','hello','yo'])){
			return $this->_welcomeResponse();
		}

		// Clear screen command
		$clearCommands = ['clear','clear screen','cls','/clear','clear chat','clean screen','reset chat'];
		if(in_array($message, $clearCommands)){
			return $this->_response('clear', '');
		}

		// Retry / resume handling
		$exactRetry = ['try again','retry','can you try again','try again please','go back','continue','resume','where were we','pick up where we left off','start over','restart','do it again'];
		$isRetry = false;
		foreach($exactRetry as $phrase){
			if($message === $phrase || $message === str_replace(' ', '', $phrase)){
				$isRetry = true;
				break;
			}
		}
		// Also allow substring match for very specific retry phrases
		if(!$isRetry && (strpos($message, 'try again') !== false || strpos($message, 'retry') !== false)){
			$isRetry = true;
		}

		if($isRetry){
			// Active conversation exists — retry current step
			$conversation = $this->_getConversation($sessionId);
			if($conversation && !empty($conversation['flow'])){
				return $this->_processFlowStep('', $conversation, $sessionId);
			}
			// No active conversation — restore last flow and restart from init
			$lastFlow = $this->_getLastFlow($sessionId);
			if($lastFlow && !empty($lastFlow['flow'])){
				$lastFlow['step'] = 'init';
				$lastFlow['data'] = [];
				$this->_setConversation($lastFlow, $sessionId);
				return $this->_processFlowStep('', $lastFlow, $sessionId);
			}
			return $this->_response('text', 'I don\'t have a previous task to retry. What would you like to do?', ['quick_tasks' => $this->_getFilteredQuickTasks()]);
		}

		// Check if we're in a conversational flow
		$conversation = $this->_getConversation($sessionId);
		if($conversation && !empty($conversation['flow'])){
			return $this->_processFlowStep($message, $conversation, $sessionId);
		}

		// Cancel flow keywords
		if(in_array($message, ['cancel','stop','quit','abort','never mind'])){
			$this->_clearConversation($sessionId);
			return $this->_response('text', 'Cancelled.', ['delayed_followup' => 'Would you want to do any other thing? I am available.', 'quick_tasks' => $this->_getFilteredQuickTasks()]);
		}

		// Special case: profit-related queries should pull live data, not send guides
		if($this->_isProfitQuery($message)){
			$levelVal = $this->assist_knowledge_model->roleHierarchy[$this->assist_knowledge_model->getUserRoleLevel()] ?? 0;
			if($levelVal < 1) return $this->_roleDeniedResponse();
			return $this->_todayProfit();
		}

		// 1. Check Knowledge Base first for how-to / help questions
		$kbMatch = $this->assist_knowledge_model->search($message);
		if($kbMatch){
			$followUp = $this->_getKbFollowUp($kbMatch['category'] ?? 'general');
			return $this->_response('knowledge', $kbMatch['answer'], [
				'follow_up'   => $followUp['text'],
				'quick_tasks' => $followUp['tasks'] ?? []
			]);
		}

		// 2. Fall back to intent detection for data actions
		$roleLevel = $this->assist_knowledge_model->getUserRoleLevel();
		$levelVal = $this->assist_knowledge_model->roleHierarchy[$roleLevel] ?? 0;

		$detected = $this->_detectIntent($message);
		$intent = $detected['intent'] ?? 'UNKNOWN';
		$entities = $detected['entities'] ?? [];

		switch($intent){
			case 'SEARCH_CUSTOMER':
				return $this->_searchCustomer($message, $entities['customer'] ?? null);
			case 'CREATE_CUSTOMER':
				if(!$this->_hasPermission('customers_add')) return $this->_roleDeniedResponse();
				return $this->_startFlow('create_customer', $sessionId);
			case 'CREATE_RAW_MATERIAL':
				if(!$this->_featAny(['recipe_tracking','production_workflow'])) return $this->_rawMaterialUnavailable();
				if(!$this->_hasPermission('items_add')) return $this->_roleDeniedResponse();
				$conversation = [
					'flow'       => 'entity_raw_material',
					'step'       => 'init',
					'data'       => ['values' => []],
					'created_at' => time()
				];
				if(!empty($entities['name'])){
					$conversation['data']['values']['item_name'] = ucwords($entities['name']);
				}
				$this->_setConversation($conversation, $sessionId);
				return $this->_processFlowStep('', $conversation, $sessionId);
			case 'SEARCH_PRODUCT':
			case 'CHECK_STOCK':
				return $this->_checkStock($message, $entities['item'] ?? null);
			case 'LOW_STOCK': return $this->_lowStock();
			case 'CREATE_SALE':
				if(!$this->_hasPermission('sales_add')) return $this->_roleDeniedResponse();
				if(!empty($entities['customer'])){
					$conversation = [
						'flow'       => 'create_sale',
						'step'       => 'search_customer_result',
						'data'       => $entities,
						'created_at' => time()
					];
					$this->_setConversation($conversation, $sessionId);
					return $this->_processFlowStep($entities['customer'], $conversation, $sessionId);
				}
				return $this->_startFlow('create_sale', $sessionId);
			case 'CREATE_INVOICE': return $this->_response('text', 'Please use the Sales > Invoices page to create invoices. I can help search for customers first if needed.');
			case 'CREATE_EXPENSE':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_createExpenseDraft($message);
			case 'BUSINESS_SUMMARY':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_businessSummary();
			case 'PROFIT_REPORT':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_todayProfit();
			case 'TOP_PRODUCTS':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_topProducts();
			case 'CUSTOMER_BALANCE':
			case 'DEBT_SUMMARY':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_customerBalances($message);
			case 'ONLINE_ORDER_SUMMARY': return $this->_onlineOrders();
			case 'HELP': return $this->_welcomeResponse();
			default:
				// Rule-based engine didn't recognize it — let the AI try
				$ai = $this->_aiDecide($message, $sessionId);
				if($ai !== null) return $ai;
				return $this->_fallbackResponse();
		}
	}

	public function startQuickActionFlow($action, $sessionId){
		$roleLevel = $this->assist_knowledge_model->getUserRoleLevel();
		$levelVal = $this->assist_knowledge_model->roleHierarchy[$roleLevel] ?? 0;

		switch($action){
			case 'create_sale': return $this->_startFlow('create_sale', $sessionId);
			case 'check_stock': return $this->_checkStock('');
			case 'find_customer': return $this->_searchCustomer('');
			case 'today_sales':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_businessSummary();
			case 'today_profit':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_todayProfit();
			case 'daily_summary':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_businessSummary();
			case 'record_expense':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_createExpenseDraft('');
			case 'low_stock': return $this->_lowStock();
			case 'view_debts':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_customerBalances('');
			case 'online_orders': return $this->_onlineOrders();
			case 'create_account':
				if($levelVal < 2) return $this->_roleDeniedResponse();
				return $this->_startFlow('create_account', $sessionId);
			case 'edit_store':
				if($levelVal < 2) return $this->_roleDeniedResponse();
				return $this->_startFlow('edit_store', $sessionId);
			case 'create_purchase':
				if($levelVal < 2) return $this->_roleDeniedResponse();
				return $this->_startFlow('create_purchase', $sessionId);
			case 'create_item':
				if($levelVal < 2) return $this->_roleDeniedResponse();
				return $this->_startFlow('create_item', $sessionId);
			case 'create_raw_material':
				if($levelVal < 2 || !$this->_hasPermission('items_add')) return $this->_roleDeniedResponse();
				if(!$this->_featAny(['recipe_tracking','production_workflow'])) return $this->_rawMaterialUnavailable();
				return $this->_startFlow('entity_raw_material', $sessionId);
			default: return $this->_fallbackResponse();
		}
	}

	private function _roleDeniedResponse(){
		return $this->_response('text', $this->assist_knowledge_model->getUnauthorizedMessage(), ['quick_tasks' => $this->_getFilteredQuickTasks()]);
	}

	public function resolveChoice($intent, $choice, $sessionId){
		// Check if we're in a flow that expects a choice
		$conversation = $this->_getConversation($sessionId);
		if($conversation && !empty($conversation['flow'])){
			$message = $choice; // The choice value becomes the user's input
			return $this->_processFlowStep($message, $conversation, $sessionId);
		}

		if(strpos($intent, 'customer_') === 0){
			$customerId = str_replace('customer_', '', $intent);
			return $this->_customerDetail($customerId);
		}
		if(strpos($intent, 'product_') === 0){
			$productId = str_replace('product_', '', $intent);
			return $this->_productDetail($productId);
		}
		return $this->_response('text', 'Sorry, I could not process that selection.');
	}

	// =================== CONVERSATION STATE ===================

	private function _getConversation($sessionId){
		$conv = $this->session->userdata('assist_conversation') ?: [];
		return $conv;
	}

	private function _setConversation($data, $sessionId){
		$this->session->set_userdata('assist_conversation', $data);
		$this->_saveLastFlow($data, $sessionId);
	}

	private function _clearConversation($sessionId){
		$this->session->unset_userdata('assist_conversation');
	}

	private function _saveLastFlow($conversation, $sessionId){
		if(!empty($conversation['flow'])){
			$this->session->set_userdata('assist_last_flow', $conversation);
		}
	}

	private function _getLastFlow($sessionId){
		return $this->session->userdata('assist_last_flow') ?: null;
	}

	private function _clearLastFlow($sessionId){
		$this->session->unset_userdata('assist_last_flow');
	}

	private function _startFlow($flow, $sessionId){
		$conversation = [
			'flow' => $flow,
			'step' => 'init',
			'data' => [],
			'created_at' => time()
		];
		$this->_setConversation($conversation, $sessionId);
		$this->_saveLastFlow($conversation, $sessionId);
		return $this->_processFlowStep('', $conversation, $sessionId);
	}

	private function _advanceStep($step, $data, $sessionId){
		$conversation = $this->_getConversation($sessionId);
		$conversation['step'] = $step;
		$conversation['data'] = array_merge($conversation['data'] ?? [], $data);
		$this->_setConversation($conversation, $sessionId);
		return $conversation;
	}

	private function _processFlowStep($message, $conversation, $sessionId){
		$msg = strtolower(trim($message));
		if(in_array($msg, ['cancel','stop','quit','abort','never mind'])){
			$this->_clearConversation($sessionId);
			return $this->_response('text', 'Cancelled.', ['delayed_followup' => 'Would you want to do any other thing? I am available.', 'quick_tasks' => $this->_getFilteredQuickTasks()]);
		}

		$flow = $conversation['flow'];
		$step = $conversation['step'];

		if($flow === 'create_sale'){
			return $this->_flowCreateSale($step, $message, $conversation, $sessionId);
		}
		if($flow === 'create_customer'){
			return $this->_flowCreateCustomer($step, $message, $conversation, $sessionId);
		}
		if($flow === 'create_account'){
			return $this->_flowCreateAccount($step, $message, $conversation, $sessionId);
		}
		if($flow === 'edit_store'){
			return $this->_flowEditStore($step, $message, $conversation, $sessionId);
		}
		if($flow === 'create_purchase'){
			return $this->_flowCreatePurchase($step, $message, $conversation, $sessionId);
		}
		if($flow === 'create_item'){
			return $this->_flowCreateItem($step, $message, $conversation, $sessionId);
		}
		if(strpos($flow, 'entity_') === 0){
			return $this->_flowEntityCreate(substr($flow, 7), $step, $message, $conversation, $sessionId);
		}

		// Unknown flow - clear and fallback
		$this->_clearConversation($sessionId);
		return $this->_fallbackResponse();
	}

	// =================== CREATE SALE FLOW ===================

	private function _hasPermission($perm){
		$userId = $this->session->userdata('inv_userid');
		if($userId == 1) return true;
		$roleId = $this->session->userdata('role_id');
		if(empty($roleId)) return false;
		$tot = $this->db->query('SELECT count(*) as tot FROM db_permissions where permissions="'.$perm.'" and role_id='.$roleId)->row()->tot;
		return ($tot == 1);
	}

	private function _canBuyOnCredit($customerId){
		if(is_walk_in_customer($customerId)) return false;
		$customer = get_customer_details($customerId);
		if(!$customer) return false;
		$creditLimit = floatval($customer->credit_limit ?? -1);
		if($creditLimit == -1) return true;
		if($creditLimit == 0) return false;
		$salesDue = floatval($customer->sales_due ?? 0);
		return ($salesDue <= $creditLimit);
	}

	private function _isItemExpired($item){
		try {
			$this->load->model('expiry_settings_model');
			$expirySettings = $this->expiry_settings_model->get_settings();
			if(is_valid_date($item->expire_date) && $expirySettings->stop_selling_expired == 1){
				$today = date('Y-m-d');
				if($item->expire_date < $today){
					return true;
				}
			}
		} catch(Exception $e) {}
		return false;
	}

	private function _hasEnoughStock($itemId, $qtyNeeded){
		$item = get_item_details($itemId);
		if(!$item) return false;
		if($item->service_bit == 1) return true;
		$available = total_available_qty_items_of_warehouse('', get_current_store_id(), $itemId);
		return ($available >= $qtyNeeded);
	}

	private function _findNonExpiredAlternatives($searchTerm){
		$storeId = get_current_store_id();
		$this->db->like('item_name', $searchTerm, 'both');
		$this->db->or_like('item_code', $searchTerm, 'both');
		$this->db->where('store_id', $storeId);
		$this->db->where('status', 1);
		$items = $this->db->get('db_items')->result();
		$valid = [];
		foreach($items as $item){
			$item = $this->_getItemWithBarcode($item->id);
			if(!$item) continue;
			if(!$this->_isItemExpired($item)){
				$valid[] = $item;
			}
		}
		return $valid;
	}

	private function _buildItemOptions($items){
		$options = [];
		foreach($items as $item){
			$options[] = ['label' => ($item->item_name ?? 'Unknown').' @ '.number_format($item->sales_price ?? 0, 2).' ('.($item->item_code ?? 'N/A').')', 'value' => 'product_'.$item->id];
		}
		return $options;
	}

	private function _getItemWithBarcode($itemId){
		$item = $this->db->where('id', $itemId)->get('db_items')->row();
		if(!$item) return null;
		$barcode = $this->db->where('item_id', $itemId)->where('status', 1)->order_by('id', 'asc')->get('db_item_barcodes')->row();
		if($barcode){
			$item->barcode        = $barcode->barcode;
			$item->batch_lot      = $barcode->batch_lot;
			$item->purchase_price = $barcode->purchase_price;
			$item->sales_price    = $barcode->sales_price;
			$item->mrp            = $barcode->mrp;
			$item->expire_date    = $barcode->expire_date;
			$item->mfg_date       = $barcode->mfg_date;
			$item->barcode_qty    = $barcode->qty;
		}
		return $item;
	}

	private function _flowCreateSale($step, $message, $conversation, $sessionId){
		$msg = strtolower(trim($message));
		$data = $conversation['data'] ?? [];

		switch($step){

			case 'init':
			case 'ask_customer':
				$this->_advanceStep('search_customer_result', [], $sessionId);
				return $this->_conversational("Great! Let's create a sale. Who is this sale for? Please enter the ".$this->_term('customer','customer')." name or phone number.", ['step'=>'search_customer_result']);

			case 'search_customer_result':
				if(empty($msg)){
					return $this->_conversational('I need a customer name or phone to search. Please try again, or type "cancel" to stop.', ['step'=>'search_customer_result']);
				}
				$this->db->like('customer_name', $msg, 'both');
				$this->db->or_like('mobile', $msg, 'both');
				$customers = $this->db->get('db_customers')->result();

				if(empty($customers)){
					$this->_advanceStep('ask_create_customer', [], $sessionId);
					return $this->_conversational('I couldn\'t find any customer matching "'.ucwords($msg).'". Would you like me to create a new customer?', [
						'step' => 'ask_create_customer',
						'options' => [
							['label'=>'Yes, create customer', 'value'=>'yes'],
							['label'=>'No, cancel', 'value'=>'no']
						]
					]);
				}
				if(count($customers) === 1){
					$c = $customers[0];
					$this->_advanceStep('ask_item', ['customer_id'=>$c->id, 'customer_name'=>$c->customer_name], $sessionId);
					$conv = $this->_getConversation($sessionId);
					if(!empty($conv['data']['item'])){
						return $this->_processFlowStep($conv['data']['item'], $conv, $sessionId);
					}
					return $this->_conversational('Found '.$this->_term('customer','customer').': <strong>'.($c->customer_name ?? 'Unknown').'</strong> ('.($c->mobile ?? 'N/A').'). Now, what '.strtolower($this->_term('item','item')).' are you selling?', ['step'=>'ask_item']);
				}

				$options = [];
				foreach($customers as $c){
					$options[] = ['label' => ($c->customer_name ?? 'Unknown').' - '.($c->mobile ?? 'No phone'), 'value' => 'customer_'.$c->id];
				}
				$this->_advanceStep('pick_customer', ['search_name'=>$msg], $sessionId);
				return $this->_conversational('I found multiple customers. Please pick one:', ['step'=>'pick_customer', 'options'=>$options]);

			case 'pick_customer':
				if(strpos($msg, 'customer_') === 0){
					$customerId = str_replace('customer_', '', $msg);
					$c = $this->db->where('id', $customerId)->get('db_customers')->row();
					if($c){
						$this->_advanceStep('ask_item', ['customer_id'=>$c->id, 'customer_name'=>$c->customer_name], $sessionId);
						$conv = $this->_getConversation($sessionId);
						if(!empty($conv['data']['item'])){
							return $this->_processFlowStep($conv['data']['item'], $conv, $sessionId);
						}
						return $this->_conversational('Selected: <strong>'.($c->customer_name ?? 'Unknown').'</strong>. Now, what item are you selling?', ['step'=>'ask_item']);
					}
				}
				return $this->_conversational('Please select a customer from the list above, or type "cancel" to stop.', ['step'=>'pick_customer']);

			case 'ask_create_customer':
				if(in_array($msg, ['yes','y','yeah','sure','ok','okay'])){
					$this->_advanceStep('collect_name', [], $sessionId);
					return $this->_conversational("Perfect! What is the customer's full name?", ['step'=>'collect_name']);
				}
				if(in_array($msg, ['no','n','nope','nah'])){
					$this->_clearConversation($sessionId);
					return $this->_response('text', 'No problem. Sale cancelled.', ['delayed_followup'=>'Would you want to do any other thing? I am available.', 'quick_tasks'=>$this->_getFilteredQuickTasks()]);
				}
				return $this->_conversational('Please say yes or no, or type "cancel" to stop.', [
					'step'=>'ask_create_customer',
					'options'=>[
						['label'=>'Yes, create customer', 'value'=>'yes'],
						['label'=>'No, cancel', 'value'=>'no']
					]
				]);

			case 'collect_name':
				if(empty($msg) || strlen($msg) < 2){
					return $this->_conversational('I need a valid name. Please enter the customer\'s full name:', ['step'=>'collect_name']);
				}
				$this->_advanceStep('collect_email', ['new_customer_name'=>ucwords(trim($message))], $sessionId);
				return $this->_conversational('Got it. What is the email address? (Type "skip" if none)', ['step'=>'collect_email']);

			case 'collect_email':
				$email = (in_array($msg, ['skip','none','no','n/a'])) ? '' : trim($message);
				$this->_advanceStep('collect_phone', ['new_customer_email'=>$email], $sessionId);
				return $this->_conversational('What is the phone number? (Type "skip" if none)', ['step'=>'collect_phone']);

			case 'collect_phone':
				$phone = (in_array($msg, ['skip','none','no','n/a'])) ? '' : trim($message);
				$this->_advanceStep('collect_credit', ['new_customer_phone'=>$phone], $sessionId);
				return $this->_conversational('What is the credit limit? (Type "0" or "skip" for none)', ['step'=>'collect_credit']);

			case 'collect_credit':
				$credit = (in_array($msg, ['skip','none','no','n/a'])) ? 0 : floatval(str_replace([',',' '], '', $message));
				$conv = $this->_getConversation($sessionId);
				$d = $conv['data'];
				$store_id = get_current_store_id();
				$mobile = $d['new_customer_phone'] ?? '';

				// Check for duplicate mobile
				if(!empty($mobile)){
					$this->db->where('mobile', $mobile);
					$this->db->where('store_id', $store_id);
					$existing = $this->db->get('db_customers')->row();
					if($existing){
						$this->_advanceStep('ask_continue_sale', ['customer_id'=>$existing->id, 'customer_name'=>$existing->customer_name], $sessionId);
						return $this->_conversational('That customer already exists: <strong>'.htmlspecialchars($existing->customer_name).'</strong> (ID: '.$existing->id.'). Would you like to continue with the sale?', [
							'step'=>'ask_continue_sale',
							'options'=>[
								['label'=>'Yes, continue sale', 'value'=>'yes'],
								['label'=>'No, cancel', 'value'=>'no']
							]
						]);
					}
				}

				$customerData = [
					'store_id'       => $store_id,
					'count_id'       => get_count_id('db_customers'),
					'customer_code'  => get_init_code('customer'),
					'customer_name'  => $d['new_customer_name'] ?? 'Unknown',
					'email'          => $d['new_customer_email'] ?? '',
					'mobile'         => $mobile,
					'credit_limit'   => $credit,
					'created_date'   => date('Y-m-d'),
					'created_time'   => date('H:i:s'),
					'created_by'     => $this->session->userdata('inv_username') ?? 'system',
					'system_ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
					'system_name'    => gethostname() ?: '',
					'status'         => 1
				];
				$this->db->insert('db_customers', $customerData);
				$customerId = $this->db->insert_id();
				$this->_advanceStep('ask_continue_sale', ['customer_id'=>$customerId, 'customer_name'=>$d['new_customer_name']], $sessionId);
				return $this->_conversational('Customer "'.($d['new_customer_name'] ?? 'Unknown').'" created successfully! <strong>Customer ID: '.$customerId.'</strong>. Would you like to continue with the sale?', [
					'step'=>'ask_continue_sale',
					'options'=>[
						['label'=>'Yes, continue sale', 'value'=>'yes'],
						['label'=>'No, cancel', 'value'=>'no']
					]
				]);

			case 'ask_continue_sale':
				if(in_array($msg, ['yes','y','yeah','sure','ok','okay'])){
					$this->_advanceStep('ask_item', [], $sessionId);
					return $this->_conversational('Awesome! What item are you selling?', ['step'=>'ask_item']);
				}
				if(in_array($msg, ['no','n','nope','nah','cancel'])){
					$this->_clearConversation($sessionId);
					return $this->_response('text', 'Sale cancelled.', ['delayed_followup'=>'Would you want to do any other thing? I am available.', 'quick_tasks'=>$this->_getFilteredQuickTasks()]);
				}
				return $this->_conversational('Please say yes or no:', [
					'step'=>'ask_continue_sale',
					'options'=>[
						['label'=>'Yes, continue sale', 'value'=>'yes'],
						['label'=>'No, cancel', 'value'=>'no']
					]
				]);

			case 'ask_item':
				if(empty($msg) || strlen($msg) < 2){
					return $this->_conversational('Please tell me the '.strtolower($this->_term('item','item')).' name or code:', ['step'=>'ask_item']);
				}
				$this->db->like('item_name', $msg, 'both');
				$this->db->or_like('item_code', $msg, 'both');
				if($this->db->field_exists('not_for_sale', 'db_items')){
					$this->db->where("(not_for_sale IS NULL OR not_for_sale = 0)");
				}
				$items = $this->db->get('db_items')->result();

				if(empty($items)){
					return $this->_conversational('No '.strtolower($this->_term('item','item')).' found matching "'.ucwords($msg).'". Please try again, or type "cancel" to stop.', ['step'=>'ask_item']);
				}
				if(count($items) === 1){
					$item = $this->_getItemWithBarcode($items[0]->id);
					if(!$item) return $this->_conversational('Product not found. Please try again:', ['step'=>'ask_item']);
					if($this->_isItemExpired($item)){
						$alternatives = $this->_findNonExpiredAlternatives($msg);
						if(!empty($alternatives)){
							$options = $this->_buildItemOptions($alternatives);
							return $this->_conversational('<span style="color:#e74c3c;"><strong>This item has expired ('.$item->expire_date.'). Cannot sell expired items.</strong></span> Here are items you can sell:', ['step'=>'pick_item', 'options'=>$options]);
						}
						return $this->_conversational('<span style="color:#e74c3c;"><strong>This item has expired ('.$item->expire_date.'). Cannot sell expired items.</strong></span> No non-expired alternatives found. Please search for a different item:', ['step'=>'ask_item']);
					}
					$this->_advanceStep('ask_price_type', ['item_id'=>$item->id, 'item_name'=>$item->item_name, 'item_mrp'=>$item->mrp ?? 0, 'item_sales_price'=>$item->sales_price ?? 0], $sessionId);
					return $this->_conversational('Found: <strong>'.($item->item_name ?? 'Unknown').'</strong>. Which price would you like to use?', [
					'step'=>'ask_price_type',
					'options'=>[
						['label'=>'Retail (MRP) @ '.number_format($item->mrp ?? 0, 2), 'value'=>'retail'],
						['label'=>'Wholesale @ '.number_format($item->sales_price ?? 0, 2), 'value'=>'wholesale']
					]
				]);
				}

				$options = [];
				foreach($items as $item){
					$item = $this->_getItemWithBarcode($item->id);
					if(!$item) continue;
					if($this->_isItemExpired($item)) continue;
					$options[] = ['label' => ($item->item_name ?? 'Unknown').' @ '.number_format($item->sales_price ?? 0, 2).' ('.($item->item_code ?? 'N/A').')', 'value' => 'product_'.$item->id];
				}
				if(empty($options)){
					return $this->_conversational('<span style="color:#e74c3c;"><strong>All matching items are expired.</strong></span> No items available to sell. Please search for a different item:', ['step'=>'ask_item']);
				}
				$this->_advanceStep('pick_item', ['search_name'=>$msg], $sessionId);
				return $this->_conversational('Multiple '.$this->_pluralize(strtolower($this->_term('item','item'))).' found. Please pick one:', ['step'=>'pick_item', 'options'=>$options]);

			case 'pick_item':
				if(strpos($msg, 'product_') === 0){
					$itemId = str_replace('product_', '', $msg);
					$item = $this->_getItemWithBarcode($itemId);
					if($item){
						if($this->_isItemExpired($item)){
							$searchTerm = $data['search_name'] ?? $msg;
							$alternatives = $this->_findNonExpiredAlternatives($searchTerm);
							if(!empty($alternatives)){
								$options = $this->_buildItemOptions($alternatives);
								return $this->_conversational('<span style="color:#e74c3c;"><strong>This item has expired ('.$item->expire_date.'). Cannot sell expired items.</strong></span> Here are items you can sell:', ['step'=>'pick_item', 'options'=>$options]);
							}
							return $this->_conversational('<span style="color:#e74c3c;"><strong>This item has expired ('.$item->expire_date.'). Cannot sell expired items.</strong></span> No non-expired alternatives found. Please search for a different item:', ['step'=>'ask_item']);
						}
						$this->_advanceStep('ask_price_type', ['item_id'=>$item->id, 'item_name'=>$item->item_name, 'item_mrp'=>$item->mrp ?? 0, 'item_sales_price'=>$item->sales_price ?? 0], $sessionId);
						return $this->_conversational('Selected: <strong>'.($item->item_name ?? 'Unknown').'</strong>. Which price would you like to use?', [
					'step'=>'ask_price_type',
					'options'=>[
						['label'=>'Retail (MRP) @ '.number_format($item->mrp ?? 0, 2), 'value'=>'retail'],
						['label'=>'Wholesale @ '.number_format($item->sales_price ?? 0, 2), 'value'=>'wholesale']
					]
				]);
					}
				}
				return $this->_conversational('Please select a product from the list, or type "cancel" to stop.', ['step'=>'pick_item']);

			case 'ask_price_type':
				if(!in_array($msg, ['retail','wholesale'])){
					$conv = $this->_getConversation($sessionId);
					$d = $conv['data'];
					return $this->_conversational('Please select a price type:', [
						'step'=>'ask_price_type',
						'options'=>[
							['label'=>'Retail (MRP) @ '.number_format($d['item_mrp'] ?? 0, 2), 'value'=>'retail'],
							['label'=>'Wholesale @ '.number_format($d['item_sales_price'] ?? 0, 2), 'value'=>'wholesale']
						]
					]);
				}
				$conv = $this->_getConversation($sessionId);
				$d = $conv['data'];
				$priceType = ($msg === 'retail') ? 'retail' : 'wholesale';
				$defaultPrice = ($priceType === 'retail') ? ($d['item_mrp'] ?? 0) : ($d['item_sales_price'] ?? 0);
				$this->_advanceStep('ask_qty', ['price_type'=>$priceType, 'default_price'=>$defaultPrice], $sessionId);
				$conv = $this->_getConversation($sessionId);
				if(!empty($conv['data']['qty']) && $conv['data']['qty'] > 0){
					return $this->_processFlowStep((string)$conv['data']['qty'], $conv, $sessionId);
				}
				return $this->_conversational('Using <strong>'.ucfirst($priceType).'</strong> price @ '.number_format($defaultPrice, 2).'. How many are you selling?', ['step'=>'ask_qty']);

			case 'ask_qty':
				$qty = intval(str_replace([',',' '], '', $message));
				if($qty <= 0){
					return $this->_conversational('Please enter a valid quantity (1 or more):', ['step'=>'ask_qty']);
				}
				$conv = $this->_getConversation($sessionId);
				$itemId = $conv['data']['item_id'] ?? 0;
				if(!$this->_hasEnoughStock($itemId, $qty)){
					$available = total_available_qty_items_of_warehouse('', get_current_store_id(), $itemId);
					return $this->_conversational('<span style="color:#e74c3c;"><strong>Insufficient stock!</strong></span> Only '.number_format($available, 2).' available. Please enter a lower quantity:', ['step'=>'ask_qty']);
				}
				$this->_advanceStep('ask_discount', ['qty'=>$qty], $sessionId);
				return $this->_conversational('Any discount? Enter a percentage (e.g. 5 for 5%) or type "0" for none:', ['step'=>'ask_discount']);

			case 'ask_discount':
				$discount = floatval(str_replace([',','%',' '], '', $message));
				if($discount < 0) $discount = 0;
				$this->_advanceStep('ask_sale_price', ['discount'=>$discount], $sessionId);
				$conv = $this->_getConversation($sessionId);
				$defaultPrice = $conv['data']['default_price'] ?? 0;
				return $this->_conversational('What is the sale price per unit? (Type "default" to use '.number_format($defaultPrice, 2).'):', ['step'=>'ask_sale_price']);

			case 'ask_sale_price':
				$conv = $this->_getConversation($sessionId);
				$defaultPrice = $conv['data']['default_price'] ?? 0;
				if(in_array($msg, ['default','d','def'])){
					$price = $defaultPrice;
				} else {
					$price = floatval(str_replace([',',' '], '', $message));
				}
				if($price <= 0){
					return $this->_conversational('Please enter a valid price greater than 0, or type "default":', ['step'=>'ask_sale_price']);
				}
				if($price != $defaultPrice && !$this->_hasPermission('sales_edit')){
					return $this->_conversational('<span style="color:#e74c3c;"><strong>You do not have permission to change prices.</strong></span> Please enter the system price '.number_format($defaultPrice, 2).' or type "default":', ['step'=>'ask_sale_price']);
				}

				$d = $conv['data'];
				$qty = $d['qty'] ?? 1;
				$discount = $d['discount'] ?? 0;
				$subtotal = $qty * $price;
				$discountAmt = $subtotal * ($discount / 100);
				$grandTotal = $subtotal - $discountAmt;

				$html = '<div class="assist-card"><h4>Invoice Preview</h4>';
				$html .= '<p><strong>Customer:</strong> '.($d['customer_name'] ?? 'Walk-in').'</p>';
				$html .= '<div class="assist-table-wrap">';
				$html .= '<table class="assist-table">';
				$html .= '<tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>';
				$html .= '<tr><td>'.($d['item_name'] ?? 'Item').'</td><td>'.$qty.'</td><td>'.number_format($price, 2).'</td><td>'.number_format($subtotal, 2).'</td></tr>';
				if($discount > 0){
					$html .= '<tr><td colspan="3">Discount ('.$discount.'%)</td><td>-'.number_format($discountAmt, 2).'</td></tr>';
				}
				$html .= '<tr style="font-weight:bold"><td colspan="3">Grand Total</td><td>'.number_format($grandTotal, 2).'</td></tr>';
				$html .= '</table></div></div>';

				$this->_advanceStep('confirm_invoice', [
					'sale_price' => $price,
					'preview_subtotal' => $subtotal,
					'preview_discount' => $discountAmt,
					'preview_total' => $grandTotal
				], $sessionId);

				return $this->_conversational('Here is the invoice preview. Shall I create it?', [
					'step'=>'confirm_invoice',
					'html'=>$html,
					'options'=>[
						['label'=>'Yes, create invoice', 'value'=>'yes'],
						['label'=>'No, cancel', 'value'=>'no']
					]
				]);

			case 'confirm_invoice':
				if(in_array($msg, ['yes','y','yeah','sure','ok','okay'])){
					try {
						$salesId = $this->_createInvoiceFromFlow($sessionId);
					} catch(Throwable $e){
						return $this->_conversational('Error creating invoice: '.$e->getMessage(), ['step'=>'confirm_invoice']);
					}
					if(!$salesId){
						return $this->_conversational('Something went wrong creating the invoice. Please try again later.', ['step'=>'confirm_invoice']);
					}
					$sales = $this->db->where('id', $salesId)->get('db_sales')->row();
					$salesCode = $sales->sales_code ?? 'N/A';
					$grandTotal = $sales->grand_total ?? 0;

					$this->_advanceStep('ask_paid', ['sales_id'=>$salesId, 'sales_code'=>$salesCode, 'grand_total'=>$grandTotal], $sessionId);

					$invoiceLink = base_url('sales/print_invoice_pos/'.$salesId);
					$html = '<div class="assist-card"><h4>Invoice Created!</h4>';
					$html .= '<p><strong>Invoice #:</strong> '.$salesCode.'</p>';
					$html .= '<p><strong>Total:</strong> '.number_format($grandTotal, 2).'</p>';
					$html .= '<p><a href="'.$invoiceLink.'" target="_blank" style="color:#667eea;font-weight:600;"><i class="fa fa-file-text"></i> View Invoice</a></p></div>';

					return $this->_conversational('Invoice <strong>'.$salesCode.'</strong> created successfully! Has the client paid?', [
						'step'=>'ask_paid',
						'html'=>$html,
						'options'=>[
							['label'=>'Yes, record payment', 'value'=>'yes'],
							['label'=>'No, not yet', 'value'=>'no']
						]
					]);
				}
				if(in_array($msg, ['no','n','nope','nah','cancel'])){
					$this->_clearConversation($sessionId);
					return $this->_response('text', 'Invoice cancelled.', ['delayed_followup'=>'Would you want to do any other thing? I am available.', 'quick_tasks'=>$this->_getFilteredQuickTasks()]);
				}
				return $this->_conversational('Please say yes or no:', [
					'step'=>'confirm_invoice',
					'html'=>$html,
					'options'=>[
						['label'=>'Yes, create invoice', 'value'=>'yes'],
						['label'=>'No, cancel', 'value'=>'no']
					]
				]);

			case 'ask_paid':
				if(in_array($msg, ['yes','y','yeah','sure','ok','okay'])){
					$this->_advanceStep('ask_amount', [], $sessionId);
					$conv = $this->_getConversation($sessionId);
					$total = $conv['data']['grand_total'] ?? 0;
					return $this->_conversational('How much was paid? (Total is '.number_format($total, 2).'):', ['step'=>'ask_amount']);
				}
				if(in_array($msg, ['no','n','nope','nah','cancel'])){
					$conv = $this->_getConversation($sessionId);
					$customerId = $conv['data']['customer_id'] ?? 0;
					if(!$this->_canBuyOnCredit($customerId)){
						return $this->_conversational('<span style="color:#e74c3c;"><strong>This customer cannot buy on credit.</strong></span> Please record payment now.', [
							'step'=>'ask_paid',
							'options'=>[
								['label'=>'Yes, record payment', 'value'=>'yes']
							]
						]);
					}
					$salesId = $conv['data']['sales_id'] ?? 0;
					$pdfUrl = base_url('pdf/sales/'.$salesId);
					$html = '<div class="assist-card"><h4>Invoice Ready</h4><p><a href="'.$pdfUrl.'" target="_blank" style="color:#667eea;font-weight:600;"><i class="fa fa-file-pdf-o"></i> Download PDF Invoice</a></p></div>';
					$this->_clearConversation($sessionId);
					return $this->_response('html', 'No problem. The invoice is ready. You can collect payment later.', ['html'=>$html, 'quick_tasks'=>$this->_getFilteredQuickTasks()]);
				}
				return $this->_conversational('Please say yes or no:', [
					'step'=>'ask_paid',
					'options'=>[
						['label'=>'Yes, record payment', 'value'=>'yes'],
						['label'=>'No, not yet', 'value'=>'no']
					]
				]);

			case 'ask_amount':
				$amount = floatval(str_replace([',',' '], '', $message));
				if($amount <= 0){
					return $this->_conversational('Please enter a valid amount greater than 0:', ['step'=>'ask_amount']);
				}
				$this->_advanceStep('ask_payment_mode', ['amount_paid'=>$amount], $sessionId);
				$this->load->model('payment_modes_model');
				$modes = $this->payment_modes_model->get_enabled_modes(get_current_store_id());
				$options = [];
				foreach($modes as $mode){
					$options[] = ['label' => $mode->name, 'value' => $mode->code];
				}
				return $this->_conversational('Payment of '.number_format($amount, 2).' received. What payment mode was used?', ['step'=>'ask_payment_mode', 'options'=>$options]);

			case 'ask_payment_mode':
				if(empty($msg)){
					return $this->_conversational('Please select a payment mode:', ['step'=>'ask_payment_mode']);
				}
				$this->load->model('payment_modes_model');
				$mode = $this->payment_modes_model->get_mode_by_code($msg, get_current_store_id());
				if(!$mode){
					$modes = $this->payment_modes_model->get_enabled_modes(get_current_store_id());
					$options = [];
					foreach($modes as $m) $options[] = ['label' => $m->name, 'value' => $m->code];
					return $this->_conversational('Please select a valid payment mode from the list:', ['step'=>'ask_payment_mode', 'options'=>$options]);
				}
				$this->_advanceStep('ask_bank', ['payment_mode'=>$mode->code, 'payment_mode_name'=>$mode->name], $sessionId);
				$banks = $this->db->where('store_id', get_current_store_id())->where('status', 1)->get('db_bankdetails')->result();
				if(!empty($banks)){
					$options = [];
					foreach($banks as $b){
						$label = !empty($b->bank_name) ? $b->bank_name : ($b->holder_name ?? 'Bank #'.$b->id);
						$options[] = ['label' => $label, 'value' => 'bank_'.$b->id];
					}
					return $this->_conversational('Which bank was used for this payment?', ['step'=>'ask_bank', 'options'=>$options]);
				}
				$this->_advanceStep('apply_payment', ['bank_id'=>null], $sessionId);
				return $this->_processFlowStep('apply', $this->_getConversation($sessionId), $sessionId);

			case 'ask_bank':
				if(strpos($msg, 'bank_') === 0){
					$bankId = str_replace('bank_', '', $msg);
					$this->_advanceStep('apply_payment', ['bank_id'=>$bankId], $sessionId);
					return $this->_processFlowStep('apply', $this->_getConversation($sessionId), $sessionId);
				}
				return $this->_conversational('Please select a bank from the list, or type "cancel" to stop.', ['step'=>'ask_bank']);

			case 'apply_payment':
				$result = $this->_applyPaymentFromFlow($sessionId);
				if(!$result){
					return $this->_conversational('Payment recording failed. Please try again or contact support.', ['step'=>'apply_payment']);
				}
				$conv = $this->_getConversation($sessionId);
				$salesId = $conv['data']['sales_id'] ?? 0;
				$salesCode = $conv['data']['sales_code'] ?? 'N/A';
				$token = get_pdf_token('sales', $salesId, $salesCode);
				$pdfUrl = base_url('publicpdf/sales/'.$salesId.'?t='.$token.'&download=1');
				$viewUrl = base_url('sales/print_invoice_pos/'.$salesId);

				$html = '<div class="assist-card"><h4>Sale Complete!</h4>';
				$html .= '<p><strong>Invoice:</strong> '.$salesCode.'</p>';
				$html .= '<p><a href="'.$pdfUrl.'" target="_blank" style="color:#667eea;font-weight:600;"><i class="fa fa-file-pdf-o"></i> Download Shareable PDF</a></p>';
				$html .= '<p><a href="'.$viewUrl.'" target="_blank" style="color:#667eea;font-weight:600;"><i class="fa fa-file-text"></i> View Invoice</a></p></div>';

				$this->_clearConversation($sessionId);
				return $this->_response('html', 'All done! Payment recorded successfully.', ['html'=>$html, 'quick_tasks'=>$this->_getFilteredQuickTasks()]);

			default:
				$this->_clearConversation($sessionId);
				return $this->_fallbackResponse();
		}
	}

	// =================== CREATE CUSTOMER FLOW ===================

	private function _flowCreateCustomer($step, $message, $conversation, $sessionId){
		$msg = strtolower(trim($message));
		$data = $conversation['data'] ?? [];

		switch($step){
			case 'init':
				$this->_advanceStep('collect_name', [], $sessionId);
				return $this->_conversational("Let's create a new ".$this->_term('customer','customer').". What is the ".$this->_term('customer','customer')."'s full name?", ['step'=>'collect_name']);

			case 'collect_name':
				if(empty($msg) || strlen($msg) < 2){
					return $this->_conversational('I need a valid name. Please enter the '.$this->_term('customer','customer').'\'s full name:', ['step'=>'collect_name']);
				}
				$this->_advanceStep('collect_email', ['new_customer_name'=>ucwords(trim($message))], $sessionId);
				return $this->_conversational('Got it. What is the email address? (Type "skip" if none)', ['step'=>'collect_email']);

			case 'collect_email':
				$email = (in_array($msg, ['skip','none','no','n/a'])) ? '' : trim($message);
				$this->_advanceStep('collect_phone', ['new_customer_email'=>$email], $sessionId);
				return $this->_conversational('What is the phone number? (Type "skip" if none)', ['step'=>'collect_phone']);

			case 'collect_phone':
				$phone = (in_array($msg, ['skip','none','no','n/a'])) ? '' : trim($message);
				$this->_advanceStep('collect_credit', ['new_customer_phone'=>$phone], $sessionId);
				return $this->_conversational('What is the credit limit? (Type "0" or "skip" for none)', ['step'=>'collect_credit']);

			case 'collect_credit':
				$credit = (in_array($msg, ['skip','none','no','n/a'])) ? 0 : floatval(str_replace([',',' '], '', $message));
				$conv = $this->_getConversation($sessionId);
				$d = $conv['data'];
				$store_id = get_current_store_id();
				$mobile = $d['new_customer_phone'] ?? '';

				// Check for duplicate mobile
				if(!empty($mobile)){
					$this->db->where('mobile', $mobile);
					$this->db->where('store_id', $store_id);
					$existing = $this->db->get('db_customers')->row();
					if($existing){
						$this->_clearConversation($sessionId);
						return $this->_response('text', 'That customer already exists: <strong>'.htmlspecialchars($existing->customer_name).'</strong> (ID: '.$existing->id.').', ['quick_tasks'=>$this->_getFilteredQuickTasks()]);
					}
				}

				$customerData = [
					'store_id'       => $store_id,
					'count_id'       => get_count_id('db_customers'),
					'customer_code'  => get_init_code('customer'),
					'customer_name'  => $d['new_customer_name'] ?? 'Unknown',
					'email'          => $d['new_customer_email'] ?? '',
					'mobile'         => $mobile,
					'credit_limit'   => $credit,
					'created_date'   => date('Y-m-d'),
					'created_time'   => date('H:i:s'),
					'created_by'     => $this->session->userdata('inv_username') ?? 'system',
					'system_ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
					'system_name'    => gethostname() ?: '',
					'status'         => 1
				];
				$this->db->insert('db_customers', $customerData);
				$customerId = $this->db->insert_id();
				$this->_clearConversation($sessionId);
				return $this->_response('success', 'Customer "'.($d['new_customer_name'] ?? 'Unknown').'" created successfully! <strong>Customer ID: '.$customerId.'</strong>', [
					'quick_tasks' => $this->_getFilteredQuickTasks()
				]);

			default:
				$this->_clearConversation($sessionId);
				return $this->_fallbackResponse();
		}
	}

	// =================== INVOICE CREATION HELPERS ===================

	private function _createInvoiceFromFlow($sessionId){
		$conv = $this->_getConversation($sessionId);
		$d = $conv['data'];

		$storeId = get_current_store_id();
		$customerId = $d['customer_id'] ?? get_walk_in_customer_id();
		$itemId = $d['item_id'] ?? 0;
		$qty = $d['qty'] ?? 1;
		$price = $d['sale_price'] ?? 0;
		$discount = $d['discount'] ?? 0;

		$subtotal = $qty * $price;
		$discountAmt = $subtotal * ($discount / 100);
		$grandTotal = $subtotal - $discountAmt;
		$roundOff = number_format($grandTotal - round($grandTotal, 2), 2, '.', '');
		if($roundOff == 0) $roundOff = 0;

		$initCode = get_only_init_code('sales');
		$countId = get_count_id('db_sales');
		$salesCode = $initCode . $countId;

		$warehouseId = null;
		$whRow = $this->db->select('id')->where('store_id', $storeId)->where('warehouse_type','System')->get('db_warehouse')->row();
		if($whRow) $warehouseId = $whRow->id;

		$sysName = @gethostbyaddr($this->input->ip_address());
		if(!$sysName) $sysName = 'localhost';

		$salesData = [
			'store_id' => $storeId,
			'init_code' => $initCode,
			'count_id' => $countId,
			'sales_code' => $salesCode,
			'sales_date' => date('Y-m-d'),
			'sales_status' => 'Final',
			'payment_status' => 'Unpaid',
			'customer_id' => $customerId,
			'warehouse_id' => $warehouseId,
			'discount_to_all_input' => $discount,
			'discount_to_all_type' => 'in_percentage',
			'tot_discount_to_all_amt' => $discountAmt,
			'subtotal' => $subtotal,
			'round_off' => $roundOff,
			'grand_total' => $grandTotal,
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
			'created_by' => ($this->session->userdata('inv_username') ?: 'system'),
			'system_ip' => $this->input->ip_address(),
			'system_name' => $sysName,
			'pos' => 1,
			'status' => 1,
			'sales_note' => 'Created via MartPoint Assist'
		];

		$this->db->trans_begin();
		$this->db->insert('db_sales', $salesData);
		$salesId = $this->db->insert_id();
		if(!$salesId){
			$this->db->trans_rollback();
			return false;
		}

		$itemDetails = get_item_details($itemId);
		$purchasePrice = 0;
		if($itemDetails){
			$purchasePrice = (!empty($itemDetails->purchase_price) && $itemDetails->purchase_price > 0) ? $itemDetails->purchase_price : ($itemDetails->price ?? 0);
		}
		$serviceBit = $itemDetails ? ($itemDetails->service_bit ?? 0) : 0;

		$priceType = $d['price_type'] ?? 'wholesale';
		$this->db->insert('db_salesitems', [
			'store_id' => $storeId,
			'sales_id' => $salesId,
			'sales_status' => 'Final',
			'item_id' => $itemId,
			'description' => '',
			'sales_qty' => $qty,
			'price_per_unit' => $price,
			'tax_id' => null,
			'tax_amt' => null,
			'tax_type' => 'Inclusive',
			'discount_type' => 'Percentage',
			'discount_input' => 0,
			'discount_amt' => 0,
			'unit_total_cost' => $price,
			'total_cost' => $subtotal,
			'purchase_price' => $purchasePrice,
			'price_type' => $priceType,
			'status' => 1
		]);

		if($serviceBit == 0 && $warehouseId){
			$this->db->set('stock', 'stock - ' . $qty, false);
			$this->db->where('id', $itemId);
			$this->db->update('db_items');

			$this->db->set('available_qty', 'available_qty - ' . $qty, false);
			$this->db->where('item_id', $itemId);
			$this->db->where('warehouse_id', $warehouseId);
			$this->db->update('db_warehouseitems');
		}

		$this->db->trans_commit();
		return $salesId;
	}

	private function _applyPaymentFromFlow($sessionId){
		$conv = $this->_getConversation($sessionId);
		$d = $conv['data'];

		$storeId = get_current_store_id();
		$salesId = $d['sales_id'] ?? 0;
		$customerId = $d['customer_id'] ?? 0;
		$amount = $d['amount_paid'] ?? 0;
		$paymentMode = $d['payment_mode'] ?? '';
		$paymentModeName = $d['payment_mode_name'] ?? '';
		$total = $d['grand_total'] ?? 0;

		// Resolve payment mode to a real enabled store mode; fall back to default/cash if missing
		$paymentModeRow = $this->db->where('store_id', $storeId)
								  ->where('code', $paymentMode)
								  ->where('enabled', 1)
								  ->where('status', 1)
								  ->get('db_payment_modes')
								  ->row();
		if(!$paymentModeRow){
			$paymentModeRow = $this->db->where('store_id', $storeId)
									  ->where('is_default', 1)
									  ->where('enabled', 1)
									  ->where('status', 1)
									  ->get('db_payment_modes')
									  ->row();
		}
		if(!$paymentModeRow){
			$paymentModeRow = $this->db->where('store_id', $storeId)
									  ->where('enabled', 1)
									  ->where('status', 1)
									  ->order_by('sort_order', 'asc')
									  ->get('db_payment_modes')
									  ->row();
		}
		if($paymentModeRow){
			$paymentMode = $paymentModeRow->code;
			$paymentModeName = !empty($paymentModeName) ? $paymentModeName : $paymentModeRow->name;
			$paymentModeId = $paymentModeRow->id;
		} else {
			$paymentMode = 'cash';
			$paymentModeName = !empty($paymentModeName) ? $paymentModeName : 'Cash';
			$paymentModeId = null;
		}

		$paymentCode = get_init_code('sales_payment');
		$countId = get_count_id('db_salespayments');

		$sysName = @gethostbyaddr($this->input->ip_address());
		if(!$sysName) $sysName = 'localhost';

		$paymentData = [
			'payment_code' => $paymentCode,
			'count_id' => $countId,
			'store_id' => $storeId,
			'sales_id' => $salesId,
			'payment_date' => date('Y-m-d'),
			'payment_type' => $paymentMode,
			'payment_mode_id' => $paymentModeId,
			'payment' => $amount,
			'payment_note' => 'Paid By ' . $paymentModeName . ' via MartPoint Assist',
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
			'created_by' => ($this->session->userdata('inv_username') ?: 'system'),
			'system_ip' => $this->input->ip_address(),
			'system_name' => $sysName,
			'change_return' => 0,
			'status' => 1,
			'customer_id' => $customerId,
			'account_id' => null
		];

		$this->db->trans_begin();
		$this->db->insert('db_salespayments', $paymentData);
		$paymentId = $this->db->insert_id();

		// Update sales payment status
		$totPaidRow = $this->db->select_sum('payment')->where('sales_id', $salesId)->get('db_salespayments')->row();
		$totPaid = $totPaidRow ? ($totPaidRow->payment ?? 0) : 0;
		$paymentStatus = ($totPaid >= $total) ? 'Paid' : 'Partial';
		$this->db->where('id', $salesId)->update('db_sales', ['payment_status' => $paymentStatus]);

		$this->db->trans_commit();
		return true;
	}

	public function executeDraft($draftId, $sessionId){
		$draft = $this->_getDraft($draftId, $sessionId);
		if(!$draft) return $this->_response('text', 'Draft not found or expired.');
		switch($draft['type']){
			case 'sale': return $this->_response('text', 'Sale draft ready. Please go to POS to complete it.', ['action'=>'redirect','url'=>base_url('pos')]);
			case 'customer': return $this->_executeCustomerCreation($draft);
			case 'expense': return $this->_executeExpense($draft);
			default: return $this->_response('text', 'Unknown draft type.');
		}
	}

	public function cancelDraft($draftId, $sessionId){
		$this->_removeDraft($draftId, $sessionId);
		return $this->_response('text', 'Draft cancelled. What else can I help with?');
	}

	// =================== CREATE ACCOUNT FLOW ===================

	private function _flowCreateAccount($step, $message, $conversation, $sessionId){
		$msg = strtolower(trim($message));
		$data = $conversation['data'] ?? [];
		switch($step){
			case 'init':
				$this->_advanceStep('ask_name', [], $sessionId);
				return $this->_conversational("Let's create a new account. What is the account name? (e.g. Petty Cash, Rent Expense)", ['step'=>'ask_name']);
			case 'ask_name':
				if(empty($msg) || strlen($msg) < 2){
					return $this->_conversational('Please enter a valid account name:', ['step'=>'ask_name']);
				}
				$this->_advanceStep('ask_type', ['account_name'=>ucwords(trim($message))], $sessionId);
				return $this->_conversational('What type of account is this?', [
					'step' => 'ask_type',
					'options' => [
						['label'=>'Asset (Cash, Inventory, Equipment)', 'value'=>'asset'],
						['label'=>'Liability (Loans, Payables)', 'value'=>'liability'],
						['label'=>'Income (Sales, Services)', 'value'=>'income'],
						['label'=>'Expense (Rent, Utilities, Salaries)', 'value'=>'expense']
					]
				]);
			case 'ask_type':
				if(!in_array($msg, ['asset','liability','income','expense'])){
					return $this->_conversational('Please select a valid account type:', [
						'step' => 'ask_type',
						'options' => [
							['label'=>'Asset', 'value'=>'asset'],
							['label'=>'Liability', 'value'=>'liability'],
							['label'=>'Income', 'value'=>'income'],
							['label'=>'Expense', 'value'=>'expense']
						]
					]);
				}
				$this->_advanceStep('ask_balance', ['account_type'=>$msg], $sessionId);
				return $this->_conversational('What is the opening balance? (Enter 0 if none)', ['step'=>'ask_balance']);
			case 'ask_balance':
				$balance = floatval(str_replace([',',' '], '', $msg));
				$storeId = get_current_store_id();
				$userId = $this->session->userdata('inv_userid');
				$insert = [
					'count_id'     => get_count_id('ac_accounts'),
					'store_id'     => $storeId,
					'sort_code'    => get_count_id('ac_accounts'),
					'account_code' => '',
					'parent_id'    => 0,
					'account_name' => ($data['account_name'] ?? 'New Account'),
					'note'         => 'Created via Azera',
					'created_date' => date('Y-m-d'),
					'created_time' => date('H:i:s'),
					'created_by'   => $this->session->userdata('inv_username') ?: 'Azera',
					'system_ip'    => $this->input->ip_address(),
					'system_name'  => 'Azera',
					'status'       => 1
				];
				$this->db->insert('ac_accounts', $insert);
				$this->_clearConversation($sessionId);
				return $this->_response('success', '<strong>Account created!</strong><br>Name: '.htmlspecialchars($data['account_name']).'<br>Type: '.ucfirst($data['account_type'] ?? 'asset').'<br>Balance: '.number_format($balance, 2), [
					'quick_tasks' => $this->_getFilteredQuickTasks()
				]);
		}
		$this->_clearConversation($sessionId);
		return $this->_fallbackResponse();
	}

	// =================== EDIT STORE FLOW ===================

	private function _flowEditStore($step, $message, $conversation, $sessionId){
		$msg = strtolower(trim($message));
		$data = $conversation['data'] ?? [];
		$storeId = get_current_store_id();
		switch($step){
			case 'init':
				$store = $this->db->select('store_name,mobile,phone,email,address,city,state,country,postcode,store_website')->where('id',$storeId)->get('db_store')->row();
				$details = '<strong>Current Store Details:</strong><br>';
				$details .= 'Name: '.htmlspecialchars($store->store_name ?? 'N/A').'<br>';
				$details .= 'Mobile: '.htmlspecialchars($store->mobile ?? 'N/A').'<br>';
				$details .= 'Phone: '.htmlspecialchars($store->phone ?? 'N/A').'<br>';
				$details .= 'Email: '.htmlspecialchars($store->email ?? 'N/A').'<br>';
				$details .= 'Address: '.htmlspecialchars($store->address ?? 'N/A').'<br>';
				$details .= 'City: '.htmlspecialchars($store->city ?? 'N/A').'<br>';
				$details .= 'State: '.htmlspecialchars($store->state ?? 'N/A').'<br>';
				$details .= 'Country: '.htmlspecialchars($store->country ?? 'N/A').'<br>';
				$details .= 'Postcode: '.htmlspecialchars($store->postcode ?? 'N/A').'<br>';
				$details .= 'Website: '.htmlspecialchars($store->store_website ?? 'N/A').'<br>';
				$this->_advanceStep('ask_field', ['store'=>$store], $sessionId);
				return $this->_conversational($details.'<br>What would you like to change?', [
					'step' => 'ask_field',
					'options' => [
						['label'=>'Store Name', 'value'=>'store_name'],
						['label'=>'Mobile', 'value'=>'mobile'],
						['label'=>'Phone', 'value'=>'phone'],
						['label'=>'Email', 'value'=>'email'],
						['label'=>'Address', 'value'=>'address'],
						['label'=>'City', 'value'=>'city'],
						['label'=>'State', 'value'=>'state'],
						['label'=>'Country', 'value'=>'country'],
						['label'=>'Postcode', 'value'=>'postcode'],
						['label'=>'Website', 'value'=>'store_website']
					]
				]);
			case 'ask_field':
				$validFields = ['store_name','mobile','phone','email','address','city','state','country','postcode','store_website'];
				if(!in_array($msg, $validFields)){
					return $this->_conversational('Please select a field from the list above, or type "cancel" to stop.', ['step'=>'ask_field']);
				}
				$this->_advanceStep('ask_value', ['edit_field'=>$msg], $sessionId);
				$label = ucwords(str_replace('_', ' ', $msg));
				return $this->_conversational('What is the new '.$label.'?', ['step'=>'ask_value']);
			case 'ask_value':
				$field = $data['edit_field'] ?? '';
				$value = trim($message);
				if(empty($value)){
					return $this->_conversational('Please enter a valid value:', ['step'=>'ask_value']);
				}
				$this->db->where('id', $storeId)->update('db_store', [$field => $value]);
				$this->_clearConversation($sessionId);
				$label = ucwords(str_replace('_', ' ', $field));
				return $this->_response('success', '<strong>'.$label.' updated!</strong><br>New value: '.htmlspecialchars($value), [
					'quick_tasks' => $this->_getFilteredQuickTasks()
				]);
		}
		$this->_clearConversation($sessionId);
		return $this->_fallbackResponse();
	}

	// =================== CREATE PURCHASE FLOW ===================

	private function _flowCreatePurchase($step, $message, $conversation, $sessionId){
		$msg = strtolower(trim($message));
		$data = $conversation['data'] ?? [];
		$storeId = get_current_store_id();
		switch($step){
			case 'init':
				$this->_advanceStep('ask_supplier', [], $sessionId);
				return $this->_conversational("Let's create a purchase order. Who is the ".$this->_term('supplier','supplier')."? Enter ".$this->_term('supplier','supplier')." name or phone.", ['step'=>'ask_supplier']);
			case 'ask_supplier':
				if(empty($msg)){
					return $this->_conversational('Please enter a supplier name or phone:', ['step'=>'ask_supplier']);
				}
				$this->db->like('supplier_name', $msg, 'both');
				$this->db->or_like('mobile', $msg, 'both');
				$suppliers = $this->db->get('db_suppliers')->result();
				if(empty($suppliers)){
					return $this->_conversational('No supplier found matching "'.htmlspecialchars($msg).'". Please try again or type "cancel".', ['step'=>'ask_supplier']);
				}
				if(count($suppliers) === 1){
					$s = $suppliers[0];
					$this->_advanceStep('ask_warehouse', ['supplier_id'=>$s->id, 'supplier_name'=>$s->supplier_name], $sessionId);
					return $this->_conversational('Selected '.$this->_term('supplier','supplier').': <strong>'.htmlspecialchars($s->supplier_name).'</strong>. Which '.$this->_term('warehouse','warehouse').'?', ['step'=>'ask_warehouse']);
				}
				$options = [];
				foreach($suppliers as $s){
					$options[] = ['label'=>htmlspecialchars($s->supplier_name).' - '.htmlspecialchars($s->mobile ?? 'N/A'), 'value'=>'supplier_'.$s->id];
				}
				$this->_advanceStep('pick_supplier', [], $sessionId);
				return $this->_conversational('Multiple suppliers found. Please pick one:', ['step'=>'pick_supplier', 'options'=>$options]);
			case 'pick_supplier':
				if(strpos($msg, 'supplier_') === 0){
					$sid = str_replace('supplier_', '', $msg);
					$s = $this->db->where('id', $sid)->get('db_suppliers')->row();
					if($s){
						$this->_advanceStep('ask_warehouse', ['supplier_id'=>$s->id, 'supplier_name'=>$s->supplier_name], $sessionId);
						return $this->_conversational('Selected: <strong>'.htmlspecialchars($s->supplier_name).'</strong>. Which warehouse?', ['step'=>'ask_warehouse']);
					}
				}
				return $this->_conversational('Please select a supplier from the list.', ['step'=>'pick_supplier']);
			case 'ask_warehouse':
				if(empty($msg)){
					return $this->_conversational('Please enter a warehouse name:', ['step'=>'ask_warehouse']);
				}
				$this->db->like('warehouse_name', $msg, 'both');
				$this->db->where('store_id', $storeId);
				$wh = $this->db->get('db_warehouse')->result();
				if(empty($wh)){
					return $this->_conversational('No warehouse found. Please try again.', ['step'=>'ask_warehouse']);
				}
				if(count($wh) === 1){
					$w = $wh[0];
					$this->_advanceStep('ask_item', ['warehouse_id'=>$w->id, 'warehouse_name'=>$w->warehouse_name], $sessionId);
					return $this->_conversational('Branch: <strong>'.htmlspecialchars($w->warehouse_name).'</strong>. What item are you purchasing?', ['step'=>'ask_item']);
				}
				$options = [];
				foreach($wh as $w){
					$options[] = ['label'=>htmlspecialchars($w->warehouse_name), 'value'=>'warehouse_'.$w->id];
				}
				$this->_advanceStep('pick_warehouse', [], $sessionId);
				return $this->_conversational('Multiple warehouses found. Pick one:', ['step'=>'pick_warehouse', 'options'=>$options]);
			case 'pick_warehouse':
				if(strpos($msg, 'warehouse_') === 0){
					$wid = str_replace('warehouse_', '', $msg);
					$w = $this->db->where('id', $wid)->get('db_warehouse')->row();
					if($w){
						$this->_advanceStep('ask_item', ['warehouse_id'=>$w->id, 'warehouse_name'=>$w->warehouse_name], $sessionId);
						return $this->_conversational('Branch: <strong>'.htmlspecialchars($w->warehouse_name).'</strong>. What item are you purchasing?', ['step'=>'ask_item']);
					}
				}
				return $this->_conversational('Please select a warehouse.', ['step'=>'pick_warehouse']);
			case 'ask_item':
				if(empty($msg)){
					return $this->_conversational('Please enter an item name:', ['step'=>'ask_item']);
				}
				$this->db->like('item_name', $msg, 'both');
				$this->db->or_like('item_code', $msg, 'both');
				$this->db->where('store_id', $storeId);
				$items = $this->db->get('db_items')->result();
				if(empty($items)){
					return $this->_conversational('No item found. Please try again.', ['step'=>'ask_item']);
				}
				if(count($items) === 1){
					$item = $this->_getItemWithBarcode($items[0]->id);
					$this->_advanceStep('ask_qty', ['item_id'=>$item->id, 'item_name'=>$item->item_name], $sessionId);
					return $this->_conversational('Item: <strong>'.htmlspecialchars($item->item_name).'</strong>. How many are you buying?', ['step'=>'ask_qty']);
				}
				$options = [];
				foreach($items as $item){
					$item = $this->_getItemWithBarcode($item->id);
					if(!$item) continue;
					$options[] = ['label'=>htmlspecialchars($item->item_name).' @ '.number_format($item->purchase_price ?? 0, 2).' ('.htmlspecialchars($item->item_code ?? 'N/A').')', 'value'=>'item_'.$item->id];
				}
				$this->_advanceStep('pick_item', [], $sessionId);
				return $this->_conversational('Multiple items found. Pick one:', ['step'=>'pick_item', 'options'=>$options]);
			case 'pick_item':
				if(strpos($msg, 'item_') === 0){
					$iid = str_replace('item_', '', $msg);
					$item = $this->_getItemWithBarcode($iid);
					if($item){
						$this->_advanceStep('ask_qty', ['item_id'=>$item->id, 'item_name'=>$item->item_name], $sessionId);
						return $this->_conversational('Item: <strong>'.htmlspecialchars($item->item_name).'</strong>. How many are you buying?', ['step'=>'ask_qty']);
					}
				}
				return $this->_conversational('Please select an item.', ['step'=>'pick_item']);
			case 'ask_qty':
				$qty = intval(str_replace(',', '', trim($message)));
				if($qty <= 0){
					return $this->_conversational('Please enter a valid quantity:', ['step'=>'ask_qty']);
				}
				$this->_advanceStep('ask_price', ['qty'=>$qty], $sessionId);
				return $this->_conversational('Quantity: '.$qty.'. What is the purchase price per unit?', ['step'=>'ask_price']);
			case 'ask_price':
				$price = floatval(str_replace([',',' '], '', trim($message)));
				if($price < 0){
					return $this->_conversational('Please enter a valid price:', ['step'=>'ask_price']);
				}
				$total = ($data['qty'] ?? 0) * $price;
				$ref = 'PO-'.date('Ymd').'-'.rand(1000,9999);
				$purchase = [
					'purchase_code'      => get_init_code('purchase'),
					'count_id'           => get_count_id('db_purchase'),
					'purchase_date'      => date('Y-m-d'),
					'purchase_status'    => 'Received',
					'reference_no'       => '',
					'supplier_id'        => ($data['supplier_id'] ?? 0),
					'warehouse_id'       => ($data['warehouse_id'] ?? 0),
					'other_charges_input'=> null,
					'other_charges_tax_id'=> null,
					'other_charges_amt'  => null,
					'discount_to_all_input'=> null,
					'discount_to_all_type' => 'Percentage',
					'tot_discount_to_all_amt'=> null,
					'subtotal'           => $total,
					'round_off'          => null,
					'grand_total'        => $total,
					'purchase_note'      => 'Created via Azera',
					'created_date'       => date('Y-m-d'),
					'created_time'       => date('H:i:s'),
					'created_by'         => $this->session->userdata('inv_username') ?: 'Azera',
					'system_ip'          => $this->input->ip_address(),
					'system_name'        => 'Azera',
					'status'             => 1,
					'store_id'           => $storeId
				];
				$this->db->insert('db_purchase', $purchase);
				$purchaseId = $this->db->insert_id();
				$purchaseItem = [
					'purchase_id'        => $purchaseId,
					'purchase_status'    => 'Received',
					'item_id'            => ($data['item_id'] ?? 0),
					'purchase_qty'       => ($data['qty'] ?? 0),
					'price_per_unit'     => $price,
					'tax_id'             => 0,
					'tax_type'           => 'Inclusive',
					'tax_amt'            => 0,
					'discount_input'     => 0,
					'discount_amt'       => 0,
					'discount_type'      => 'Percentage',
					'unit_total_cost'    => $price,
					'total_cost'         => $total,
					'description'        => 'Created via Azera',
					'status'             => 1,
					'store_id'           => $storeId
				];
				$this->db->insert('db_purchaseitems', $purchaseItem);
				if(!empty($data['item_id'])){
					$this->db->set('stock', 'stock + '.intval($data['qty']), false);
					$this->db->where('id', $data['item_id']);
					$this->db->update('db_items');
				}
				$this->_clearConversation($sessionId);
				return $this->_response('success', '<strong>Purchase order created!</strong><br>Reference: '.$ref.'<br>Supplier: '.htmlspecialchars($data['supplier_name'] ?? '').'<br>Item: '.htmlspecialchars($data['item_name'] ?? '').'<br>Qty: '.($data['qty'] ?? 0).'<br>Total: '.number_format($total, 2), [
					'quick_tasks' => $this->_getFilteredQuickTasks()
				]);
		}
		$this->_clearConversation($sessionId);
		return $this->_fallbackResponse();
	}

	// =================== CREATE ITEM FLOW ===================

	private function _flowCreateItem($step, $message, $conversation, $sessionId){
		$msg = strtolower(trim($message));
		$data = $conversation['data'] ?? [];
		$storeId = get_current_store_id();
		switch($step){
			case 'init':
				$this->_advanceStep('ask_name', [], $sessionId);
				return $this->_conversational("Let's create a new ".strtolower($this->_term('item','item')).". What is the ".strtolower($this->_term('item','item'))." name?", ['step'=>'ask_name']);
			case 'ask_name':
				if(empty($msg) || strlen($msg) < 2){
					return $this->_conversational('Please enter a valid '.strtolower($this->_term('item','item')).' name:', ['step'=>'ask_name']);
				}
				$this->_advanceStep('ask_category', ['item_name'=>ucwords(trim($message))], $sessionId);
				return $this->_conversational('Got it. What category does this item belong to? (e.g. Electronics, Food, Clothing)', ['step'=>'ask_category']);
			case 'ask_category':
				if(empty($msg)){
					return $this->_conversational('Please enter a category:', ['step'=>'ask_category']);
				}
				$this->db->like('category_name', $msg, 'both');
				$this->db->where('store_id', $storeId);
				$cat = $this->db->get('db_category')->row();
				$categoryId = 0;
				if($cat){ $categoryId = $cat->id; }
				else {
					$this->db->insert('db_category', [
						'count_id'      => get_count_id('db_category'),
						'category_code' => get_init_code('category'),
						'category_name' => ucwords(trim($message)),
						'description'   => '',
						'category_image'=> '',
						'store_id'      => $storeId,
						'status'        => 1
					]);
					$categoryId = $this->db->insert_id();
				}
				$this->_advanceStep('ask_brand', ['category_id'=>$categoryId], $sessionId);
				return $this->_conversational('Category set. What brand? (Type "skip" if none)', ['step'=>'ask_brand']);
			case 'ask_brand':
				if(in_array($msg, ['skip','none','no','n/a'])){
					$this->_advanceStep('ask_purchase_price', ['brand_id'=>0, 'brand_skipped'=>true], $sessionId);
					return $this->_conversational('No brand selected. What is the purchase price (cost)?', ['step'=>'ask_purchase_price']);
				}
				// Search for matching brands
				$this->db->like('brand_name', $msg, 'both');
				$this->db->where('store_id', $storeId);
				$brands = $this->db->get('db_brands')->result();
				if(!empty($brands)){
					if(count($brands) === 1){
						$b = $brands[0];
						$this->_advanceStep('ask_purchase_price', ['brand_id'=>$b->id], $sessionId);
						return $this->_conversational('Brand: <strong>'.htmlspecialchars($b->brand_name).'</strong>. What is the purchase price (cost)?', ['step'=>'ask_purchase_price']);
					}
					// Multiple matches - let user pick
					$options = [];
					foreach($brands as $b){
						$options[] = ['label'=>htmlspecialchars($b->brand_name), 'value'=>'brand_'.$b->id];
					}
					$options[] = ['label'=>'None of these - create "'.ucwords(trim($message)).'"', 'value'=>'create_new'];
					$this->_advanceStep('pick_brand', ['proposed_brand'=>ucwords(trim($message))], $sessionId);
					return $this->_conversational('I found these matching brands. Please pick one:', ['step'=>'pick_brand', 'options'=>$options]);
				}
				// No match - ask to create
				$this->_advanceStep('confirm_brand', ['proposed_brand'=>ucwords(trim($message))], $sessionId);
				return $this->_conversational('Brand "<strong>'.htmlspecialchars(ucwords(trim($message))).'</strong>" was not found. Would you like to create it?', [
					'step' => 'confirm_brand',
					'options' => [
						['label'=>'Yes, create this brand', 'value'=>'yes'],
						['label'=>'No, try a different name', 'value'=>'no']
					]
				]);
			case 'pick_brand':
				if(strpos($msg, 'brand_') === 0){
					$bid = str_replace('brand_', '', $msg);
					$b = $this->db->where('id', $bid)->get('db_brands')->row();
					if($b){
						$this->_advanceStep('ask_purchase_price', ['brand_id'=>$b->id], $sessionId);
						return $this->_conversational('Brand: <strong>'.htmlspecialchars($b->brand_name).'</strong>. What is the purchase price (cost)?', ['step'=>'ask_purchase_price']);
					}
				}
				if(in_array($msg, ['create_new','yes','y'])){
					$proposed = $data['proposed_brand'] ?? 'New Brand';
					$this->db->insert('db_brands', [
						'brand_name'  => $proposed,
						'description' => '',
						'store_id'    => $storeId,
						'status'      => 1
					]);
					$brandId = $this->db->insert_id();
					$this->_advanceStep('ask_purchase_price', ['brand_id'=>$brandId], $sessionId);
					return $this->_conversational('Brand "<strong>'.htmlspecialchars($proposed).'</strong>" created. What is the purchase price (cost)?', ['step'=>'ask_purchase_price']);
				}
				return $this->_conversational('Please select an option from the list above.', ['step'=>'pick_brand']);
			case 'confirm_brand':
				if(in_array($msg, ['yes','y','yeah','sure','ok','okay'])){
					$proposed = $data['proposed_brand'] ?? 'New Brand';
					$this->db->insert('db_brands', [
						'brand_name'  => $proposed,
						'description' => '',
						'store_id'    => $storeId,
						'status'      => 1
					]);
					$brandId = $this->db->insert_id();
					$this->_advanceStep('ask_purchase_price', ['brand_id'=>$brandId], $sessionId);
					return $this->_conversational('Brand "<strong>'.htmlspecialchars($proposed).'</strong>" created. What is the purchase price (cost)?', ['step'=>'ask_purchase_price']);
				}
				if(in_array($msg, ['no','n','nope','nah'])){
					$this->_advanceStep('ask_brand', [], $sessionId);
					return $this->_conversational('No problem. Please enter the brand name you want to use, or type "skip":', ['step'=>'ask_brand']);
				}
				return $this->_conversational('Please say yes or no, or type "cancel" to stop.', [
					'step' => 'confirm_brand',
					'options' => [
						['label'=>'Yes, create this brand', 'value'=>'yes'],
						['label'=>'No, try a different name', 'value'=>'no']
					]
				]);
			case 'ask_purchase_price':
				$pprice = floatval(str_replace([',',' '], '', trim($message)));
				if($pprice < 0){
					return $this->_conversational('Please enter a valid purchase price:', ['step'=>'ask_purchase_price']);
				}
				$this->_advanceStep('ask_sales_price', ['purchase_price'=>$pprice], $sessionId);
				return $this->_conversational('Purchase price: '.number_format($pprice, 2).'. What is the sales price?', ['step'=>'ask_sales_price']);
			case 'ask_sales_price':
				$sprice = floatval(str_replace([',',' '], '', trim($message)));
				if($sprice < 0){
					return $this->_conversational('Please enter a valid sales price:', ['step'=>'ask_sales_price']);
				}
				$this->_advanceStep('ask_stock', ['sales_price'=>$sprice], $sessionId);
				return $this->_conversational('Sales price: '.number_format($sprice, 2).'. What is the opening stock quantity?', ['step'=>'ask_stock']);
			case 'ask_stock':
				$stock = intval(str_replace(',', '', trim($message)));
				if($stock < 0){
					return $this->_conversational('Please enter a valid stock quantity:', ['step'=>'ask_stock']);
				}
				$this->_advanceStep('ask_warehouse', ['stock'=>$stock], $sessionId);
				return $this->_conversational('Stock: '.$stock.'. Which '.$this->_term('warehouse','warehouse').'?', ['step'=>'ask_warehouse']);
			case 'ask_warehouse':
				if(empty($msg)){
					return $this->_conversational('Please enter a warehouse name:', ['step'=>'ask_warehouse']);
				}
				$this->db->like('warehouse_name', $msg, 'both');
				$this->db->where('store_id', $storeId);
				$wh = $this->db->get('db_warehouse')->result();
				if(empty($wh)){
					return $this->_conversational('No warehouse found. Please try again.', ['step'=>'ask_warehouse']);
				}
				if(count($wh) === 1){
					$w = $wh[0];
					$this->_advanceStep('ask_tax', ['warehouse_id'=>$w->id], $sessionId);
					return $this->_conversational('Branch: <strong>'.htmlspecialchars($w->warehouse_name).'</strong>. What tax rate? (Type "skip" if none)', ['step'=>'ask_tax']);
				}
				$options = [];
				foreach($wh as $w){
					$options[] = ['label'=>htmlspecialchars($w->warehouse_name), 'value'=>'warehouse_'.$w->id];
				}
				$this->_advanceStep('pick_warehouse', [], $sessionId);
				return $this->_conversational('Multiple warehouses found. Pick one:', ['step'=>'pick_warehouse', 'options'=>$options]);
			case 'pick_warehouse':
				if(strpos($msg, 'warehouse_') === 0){
					$wid = str_replace('warehouse_', '', $msg);
					$w = $this->db->where('id', $wid)->get('db_warehouse')->row();
					if($w){
						$this->_advanceStep('ask_tax', ['warehouse_id'=>$w->id], $sessionId);
						return $this->_conversational('Branch: <strong>'.htmlspecialchars($w->warehouse_name).'</strong>. What tax rate? (Type "skip" if none)', ['step'=>'ask_tax']);
					}
				}
				return $this->_conversational('Please select a warehouse.', ['step'=>'pick_warehouse']);
			case 'ask_tax':
				$taxId = 0;
				if(!in_array($msg, ['skip','none','no','n/a','0'])){
					$this->db->like('tax_name', $msg, 'both');
					$this->db->where('store_id', $storeId);
					$tax = $this->db->get('db_tax')->row();
					if($tax){ $taxId = $tax->id; }
				}
				$this->_advanceStep('ask_barcode', ['tax_id'=>$taxId], $sessionId);
				return $this->_conversational('Tax set. Enter barcode / SKU? (Type "skip" if none)', ['step'=>'ask_barcode']);
			case 'ask_barcode':
				$barcode = '';
				if(!in_array($msg, ['skip','none','no','n/a'])){
					$barcode = trim($message);
				}
				$this->_advanceStep('ask_expiry', ['barcode'=>$barcode], $sessionId);
				return $this->_conversational('Barcode set. Expiry date? (Type "none" if this item does not expire)', ['step'=>'ask_expiry']);
			case 'ask_expiry':
				$expireDate = '';
				if(!in_array($msg, ['none','no','n/a','skip'])){
					$expireDate = trim($message);
					if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expireDate)){
						return $this->_conversational('Please enter a valid date in YYYY-MM-DD format, or type "none":', ['step'=>'ask_expiry']);
					}
				}
				$itemCode = $data['barcode'] ?: 'ITEM-'.rand(10000,99999);
				$insert = [
					'item_code'      => $itemCode,
					'item_name'      => ($data['item_name'] ?? 'New Item'),
					'category_id'    => ($data['category_id'] ?? 0),
					'brand_id'       => ($data['brand_id'] ?? 0),
					'sku'            => $itemCode,
					'hsn'            => '',
					'unit_id'        => 1,
					'alert_qty'      => 0,
					'purchase_price' => ($data['purchase_price'] ?? 0),
					'sales_price'    => ($data['sales_price'] ?? 0),
					'stock'          => ($data['stock'] ?? 0),
					'tax_id'         => ($data['tax_id'] ?? 0),
					'tax_type'       => 'Inclusive',
					'profit_margin'  => null,
					'seller_points'  => null,
					'custom_barcode' => '',
					'description'    => 'Created via Azera',
					'item_group'     => 'Single',
					'discount_type'  => 'Percentage',
					'discount'       => 0,
					'mrp'            => ($data['sales_price'] ?? 0),
					'expire_date'    => $expireDate ?: null,
					'mfg_date'       => null,
					'status'         => 1,
					'store_id'       => $storeId,
					'created_date'   => date('Y-m-d'),
					'created_time'   => date('H:i:s'),
					'created_by'     => $this->session->userdata('inv_username') ?: 'Azera',
					'system_ip'      => $this->input->ip_address(),
					'system_name'    => 'Azera'
				];
				$this->db->insert('db_items', $insert);
				$itemId = $this->db->insert_id();
				$this->_clearConversation($sessionId);
				$msg = '<strong>Item created!</strong><br>Name: '.htmlspecialchars($data['item_name'] ?? '').'<br>Purchase: '.number_format($data['purchase_price'] ?? 0, 2).'<br>Sales: '.number_format($data['sales_price'] ?? 0, 2).'<br>Stock: '.($data['stock'] ?? 0);
				return $this->_response('success', $msg, [
					'quick_tasks' => $this->_getFilteredQuickTasks()
				]);
		}
		$this->_clearConversation($sessionId);
		return $this->_fallbackResponse();
	}

	// =================== BUSINESS CONTEXT ===================

	private $_bizProfile = null;

	private function _biz(){
		if($this->_bizProfile === null){
			$this->_bizProfile = function_exists('mp_get_store_profile') ? mp_get_store_profile(get_current_store_id()) : [];
		}
		return $this->_bizProfile;
	}

	private function _feat($flag){
		return function_exists('mp_feature_enabled') ? mp_feature_enabled($flag) : false;
	}

	private function _featAny($flags){
		foreach((array)$flags as $f){ if($this->_feat($f)) return true; }
		return false;
	}

	private function _term($key, $fallback = null){
		if(function_exists('mp_label')) return mp_label($key, $fallback);
		return $fallback ?: ucwords(str_replace('_', ' ', $key));
	}

	private function _rawMaterialLabel(){
		$biz = $this->_biz();
		$foodTypes = ['restaurant','fast_food','cafe','pizza_shop','shawarma','juice_bar','buka','canteen','butcher','bakery_cake_studio','frozen_foods_retailer'];
		return in_array($biz['industry_type'] ?? '', $foodTypes) ? 'Ingredient' : 'Raw Material';
	}

	private function _rawMaterialUnavailable(){
		return $this->_response('text',
			'Your current business setup does not use '.$this->_rawMaterialLabel().'s — this feature is for businesses with recipe tracking or production workflows. You can ask me to add a regular '.$this->_term('item','item').' instead.',
			['quick_tasks' => $this->_getFilteredQuickTasks()]);
	}

	// =================== ENTITY REGISTRY (declarative create flows) ===================
	//
	// Each entry describes one "thing" Assist can create conversationally.
	// The generic _flowEntityCreate() engine walks `fields` as slots: asks each
	// question, validates, allows "skip" on optional fields, then confirms and
	// inserts. `supports_bulk` adds a single-or-bulk choice up front.
	// To teach Assist a new entity, add a definition here plus a flow name
	// `entity_<key>`.

	private function _getEntityRegistry(){
		$rmLabel = $this->_rawMaterialLabel();
		return [
			'raw_material' => [
				'label'         => $rmLabel,
				'permission'    => 'items_add',
				'features'      => ['recipe_tracking','production_workflow'],
				'supports_bulk' => true,
				'auto_category' => $rmLabel.'s',
				'bulk_columns'  => ['item_name','unit_name','purchase_price','stock'],
				'bulk_hint'     => 'Send them like: <em>Flour, kg, 500, 20; Sugar, kg, 480, 10</em><br>That is <strong>Name, Unit, Cost per unit, Opening qty</strong> — separate each '.strtolower($rmLabel).' with a semicolon (;) or paste them line by line.',
				'fields'        => [
					['key' => 'item_name',       'type' => 'text',   'required' => true,  'prompt' => 'What is the name of this {label}?'],
					['key' => 'unit_id',         'type' => 'unit',   'required' => true,  'prompt' => 'Which unit is it measured in? (e.g. kg, litre, pcs)'],
					['key' => 'consumable_unit', 'type' => 'text',   'required' => false, 'prompt' => 'Unit label used in recipes/production? (e.g. ml, gram, sachet) — type "skip" to use the base unit'],
					['key' => 'purchase_price',  'type' => 'number', 'required' => false, 'prompt' => 'Cost price per unit? (type "skip" to set it later)'],
					['key' => 'stock',           'type' => 'number', 'required' => false, 'prompt' => 'Opening quantity in stock? (type "0" or "skip" if none)'],
					['key' => 'alert_qty',       'type' => 'number', 'required' => false, 'prompt' => 'Low-stock alert quantity? (type "skip" if none)'],
					['key' => 'expire_date',     'type' => 'date',   'required' => false, 'feature' => 'expiry_tracking', 'prompt' => 'Expiry date? (YYYY-MM-DD, or "none")'],
				],
			],
		];
	}

	private function _entityFields($def){
		$out = [];
		foreach($def['fields'] as $f){
			if(!empty($f['feature']) && !$this->_feat($f['feature'])) continue;
			$out[] = $f;
		}
		return array_values($out);
	}

	private function _flowEntityCreate($entityKey, $step, $message, $conversation, $sessionId){
		$registry = $this->_getEntityRegistry();
		$def = $registry[$entityKey] ?? null;
		if(!$def){
			$this->_clearConversation($sessionId);
			return $this->_fallbackResponse();
		}
		$msg = strtolower(trim($message));
		$data = $conversation['data'] ?? [];
		$storeId = get_current_store_id();
		$fields = $this->_entityFields($def);
		$label = $def['label'];

		switch($step){

			case 'init':
				if(!empty($def['supports_bulk'])){
					$this->_advanceStep('ask_mode', [], $sessionId);
					return $this->_conversational('Let\'s add '.$this->_pluralize($label).'. Do you want to add a <strong>single</strong> '.strtolower($label).' or <strong>several at once</strong>?', [
						'step' => 'ask_mode',
						'options' => [
							['label' => 'Single '.strtolower($label), 'value' => 'single'],
							['label' => 'Bulk (paste a list)', 'value' => 'bulk'],
						]
					]);
				}
				return $this->_entityAskNextField($entityKey, $def, $fields, $data, $sessionId, 0);

			case 'ask_mode':
				if(in_array($msg, ['bulk','many','several','list','multiple'])){
					$this->_advanceStep('bulk_paste', ['mode' => 'bulk'], $sessionId);
					return $this->_conversational($def['bulk_hint'], ['step' => 'bulk_paste']);
				}
				if(in_array($msg, ['single','one','1'])){
					return $this->_entityAskNextField($entityKey, $def, $fields, $data, $sessionId, 0);
				}
				return $this->_conversational('Please pick one:', [
					'step' => 'ask_mode',
					'options' => [
						['label' => 'Single '.strtolower($label), 'value' => 'single'],
						['label' => 'Bulk (paste a list)', 'value' => 'bulk'],
					]
				]);

			case 'field':
				$idx = $data['field_idx'] ?? 0;
				$f = $fields[$idx] ?? null;
				if(!$f){
					return $this->_entityAskNextField($entityKey, $def, $fields, $data, $sessionId, $idx);
				}
				$parsed = $this->_entityParseField($f, $message, $storeId);
				if(!empty($parsed['error'])){
					$opts = [];
					if($f['type'] === 'unit') $opts = $this->_unitOptions($storeId);
					return $this->_conversational($parsed['error'], ['step' => 'field', 'options' => $opts]);
				}
				$data['values'][$f['key']] = $parsed['value'];
				if(!empty($parsed['display'])) $data['display'][$f['key']] = $parsed['display'];
				if(!empty($parsed['note'])) $data['notes'][] = $parsed['note'];
				$this->_advanceStep('field', $data, $sessionId);
				return $this->_entityAskNextField($entityKey, $def, $fields, $this->_getConversation($sessionId)['data'], $sessionId, $idx + 1);

			case 'bulk_paste':
				$result = $this->_entityParseBulk($def, $message, $storeId);
				// If the strict parser struggled, let the AI read the list —
				// it handles messy text like "flour two bags at 500 each"
				if((empty($result['rows']) || !empty($result['errors'])) && $this->assist_brain->enabled()){
					$aiRows = $this->assist_brain->extractBulkRows($message, $def['bulk_columns'] ?? ['item_name'], $label);
					if(!empty($aiRows)){
						$aiResult = $this->_entityRowsFromRaw($aiRows, $def['bulk_columns'] ?? ['item_name'], $storeId);
						if(!empty($aiResult['rows'])) $result = $aiResult;
					}
				}
				if(!empty($result['errors'])){
					$errHtml = '<div class="assist-card"><h4>Some lines need fixing</h4><ul style="margin:6px 0 0 16px;">';
					foreach($result['errors'] as $e) $errHtml .= '<li>'.$e.'</li>';
					$errHtml .= '</ul></div>';
					return $this->_conversational('I could not read part of that list. Please resend the corrected lines:', ['step' => 'bulk_paste', 'html' => $errHtml]);
				}
				if(empty($result['rows'])){
					return $this->_conversational('I did not find any '.$this->_pluralize(strtolower($label)).' in that. '.$def['bulk_hint'], ['step' => 'bulk_paste']);
				}
				$this->_advanceStep('bulk_confirm', ['bulk_rows' => $result['rows'], 'created_units' => $result['created_units']], $sessionId);
				return $this->_conversational('Here is what I will create. Confirm?', [
					'step' => 'bulk_confirm',
					'html' => $this->_entityBulkPreviewHtml($def, $result['rows'], $result['created_units']),
					'options' => [
						['label' => 'Yes, create them', 'value' => 'yes'],
						['label' => 'No, cancel', 'value' => 'no'],
					]
				]);

			case 'bulk_confirm':
				if(in_array($msg, ['yes','y','yeah','sure','ok','okay'])){
					$rows = $data['bulk_rows'] ?? [];
					$created = 0;
					foreach($rows as $row){
						if($this->_entityInsert($entityKey, $def, $row, $storeId)) $created++;
					}
					$this->_clearConversation($sessionId);
					$extra = '';
					if(!empty($data['created_units'])){
						$extra = '<br>New units added: '.implode(', ', array_map('htmlspecialchars', $data['created_units']));
					}
					return $this->_response('success', '<strong>Done!</strong> '.$created.' '.$this->_pluralize(strtolower($label)).' created.'.$extra, [
						'quick_tasks' => $this->_getFilteredQuickTasks()
					]);
				}
				if(in_array($msg, ['no','n','nope','nah','cancel'])){
					$this->_clearConversation($sessionId);
					return $this->_response('text', 'Cancelled.', ['delayed_followup' => 'Would you want to do any other thing? I am available.', 'quick_tasks' => $this->_getFilteredQuickTasks()]);
				}
				return $this->_conversational('Please confirm:', [
					'step' => 'bulk_confirm',
					'options' => [
						['label' => 'Yes, create them', 'value' => 'yes'],
						['label' => 'No, cancel', 'value' => 'no'],
					]
				]);

			case 'confirm':
				if(in_array($msg, ['yes','y','yeah','sure','ok','okay'])){
					$itemId = $this->_entityInsert($entityKey, $def, $data['values'] ?? [], $storeId);
					if(!$itemId){
						$this->_clearConversation($sessionId);
						return $this->_response('error', 'Something went wrong creating the '.strtolower($label).'. Please try again.', ['quick_tasks' => $this->_getFilteredQuickTasks()]);
					}
					$this->_clearConversation($sessionId);
					$note = !empty($data['notes']) ? '<br>'.implode('<br>', array_map('htmlspecialchars', $data['notes'])) : '';
					return $this->_response('success', '<strong>'.$label.' created!</strong><br>Name: '.htmlspecialchars($data['values']['item_name'] ?? '').' (ID: '.$itemId.')'.$note, [
						'quick_tasks' => $this->_getFilteredQuickTasks()
					]);
				}
				if(in_array($msg, ['no','n','nope','nah','cancel'])){
					$this->_clearConversation($sessionId);
					return $this->_response('text', 'Cancelled.', ['delayed_followup' => 'Would you want to do any other thing? I am available.', 'quick_tasks' => $this->_getFilteredQuickTasks()]);
				}
				return $this->_conversational('Shall I create it?', [
					'step' => 'confirm',
					'options' => [
						['label' => 'Yes, create it', 'value' => 'yes'],
						['label' => 'No, cancel', 'value' => 'no'],
					]
				]);

			default:
				$this->_clearConversation($sessionId);
				return $this->_fallbackResponse();
		}
	}

	private function _entityAskNextField($entityKey, $def, $fields, $data, $sessionId, $startIdx){
		$values = $data['values'] ?? [];
		$idx = $startIdx;
		while(isset($fields[$idx]) && array_key_exists($fields[$idx]['key'], $values)){
			$idx++;
		}
		if(!isset($fields[$idx])){
			// All slots filled — show confirmation card
			$this->_advanceStep('confirm', ['field_idx' => $idx], $sessionId);
			return $this->_conversational('Here is the '.strtolower($def['label']).'. Shall I create it?', [
				'step' => 'confirm',
				'html' => $this->_entityPreviewHtml($def, $values, $data['display'] ?? []),
				'options' => [
					['label' => 'Yes, create it', 'value' => 'yes'],
					['label' => 'No, cancel', 'value' => 'no'],
				]
			]);
		}
		$f = $fields[$idx];
		$this->_advanceStep('field', ['field_idx' => $idx], $sessionId);
		$prompt = str_replace('{label}', strtolower($def['label']), $f['prompt']);
		$opts = [];
		if($f['type'] === 'unit') $opts = $this->_unitOptions(get_current_store_id());
		return $this->_conversational($prompt, ['step' => 'field', 'options' => $opts]);
	}

	private function _entityParseField($f, $message, $storeId){
		$val = trim($message);
		$msg = strtolower($val);
		$skips = ['skip','none','no','n/a','na','-'];
		$skipped = in_array($msg, $skips);

		if($skipped){
			if(!empty($f['required'])) return ['error' => 'This one is required — please give me an answer, or type "cancel" to stop.'];
			return ['value' => null];
		}

		switch($f['type'] ?? 'text'){
			case 'number':
				$num = floatval(str_replace([',',' '], '', $val));
				if($val === '' || !is_numeric(str_replace([',',' '], '', $val)) || $num < 0){
					return ['error' => 'Please enter a valid number (0 or more)'.(empty($f['required']) ? ', or "skip"' : '').':'];
				}
				return ['value' => $num];

			case 'date':
				if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)){
					return ['error' => 'Please enter the date as YYYY-MM-DD, or "none":'];
				}
				return ['value' => $val];

			case 'unit':
				// Option button sends unit_{id}
				if(strpos($msg, 'unit_') === 0){
					$uid = (int)str_replace('unit_', '', $msg);
					$u = $this->db->where('id', $uid)->where('store_id', $storeId)->get('db_units')->row();
					if($u) return ['value' => (int)$u->id, 'display' => $u->unit_name];
					return ['error' => 'That unit was not found. Please pick one or type a new unit name:'];
				}
				if($val === ''){
					return ['error' => 'Please type a unit (e.g. kg, pcs) or pick one below:'];
				}
				$found = $this->_findUnitByName($val, $storeId);
				if($found) return ['value' => (int)$found->id, 'display' => $found->unit_name];
				$newId = $this->_createUnit($val, $storeId);
				if(!$newId) return ['error' => 'I could not create that unit. Please try again:'];
				return ['value' => $newId, 'display' => ucwords($val), 'note' => 'New unit "'.ucwords($val).'" added'];

			case 'text':
			default:
				if($val === '' || strlen($val) < 2){
					return ['error' => 'Please enter a valid value:'];
				}
				return ['value' => ucwords($val)];
		}
	}

	private function _unitOptions($storeId){
		$units = $this->db->where('store_id', $storeId)->where('status', 1)->order_by('id', 'asc')->limit(8)->get('db_units')->result();
		$opts = [];
		foreach($units as $u){
			$opts[] = ['label' => $u->unit_name, 'value' => 'unit_'.$u->id];
		}
		return $opts;
	}

	private function _findUnitByName($name, $storeId){
		$name = trim($name);
		if($name === '') return null;
		return $this->db->where('store_id', $storeId)
			->where('status', 1)
			->group_start()
				->where('LOWER(unit_name)', strtolower($name))
				->or_where('LOWER(shortcode)', strtolower($name))
			->group_end()
			->get('db_units')->row();
	}

	private function _createUnit($name, $storeId){
		$name = ucwords(trim($name));
		if($name === '') return null;
		$insert = [
			'unit_name' => $name,
			'store_id'  => $storeId,
			'status'    => 1,
		];
		if($this->db->field_exists('shortcode', 'db_units')) $insert['shortcode'] = strtolower(substr($name, 0, 3));
		if($this->db->field_exists('description', 'db_units')) $insert['description'] = 'Created via MartPoint Assist';
		$this->db->insert('db_units', $insert);
		return $this->db->insert_id();
	}

	private function _entityParseBulk($def, $message, $storeId){
		$cols = $def['bulk_columns'] ?? ['item_name'];
		$lines = preg_split('/[\r\n;|]+/', $message);
		$rawRows = [];
		foreach($lines as $line){
			$line = trim($line);
			if($line === '') continue;
			$parts = array_map('trim', explode(',', $line));
			$raw = [];
			foreach($cols as $ci => $colKey){ $raw[$colKey] = $parts[$ci] ?? ''; }
			$rawRows[] = $raw;
		}
		return $this->_entityRowsFromRaw($rawRows, $cols, $storeId);
	}

	/**
	 * Normalize raw column-keyed rows (from regex parse OR AI extraction) into
	 * insertable rows: resolves/creates units, coerces numbers, validates names.
	 */
	private function _entityRowsFromRaw($rawRows, $cols, $storeId){
		$rows = [];
		$errors = [];
		$createdUnits = [];
		$unitCache = [];
		$rowNo = 0;

		foreach($rawRows as $rawRow){
			$rowNo++;
			$row = [];
			$display = [];

			foreach($cols as $colKey){
				$raw = trim((string)($rawRow[$colKey] ?? ''));
				switch($colKey){
					case 'item_name':
						if($raw === '' || strlen($raw) < 2){
							$errors[] = 'Entry '.$rowNo.': missing a name';
							continue 3;
						}
						$row['item_name'] = ucwords($raw);
						break;

					case 'unit_name':
						$unitName = ($raw !== '') ? $raw : 'Piece';
						$lk = strtolower($unitName);
						if(!isset($unitCache[$lk])){
							$u = $this->_findUnitByName($unitName, $storeId);
							if(!$u){
								$uid = $this->_createUnit($unitName, $storeId);
								$unitCache[$lk] = $uid ?: 0;
								if($uid) $createdUnits[] = ucwords($unitName);
							} else {
								$unitCache[$lk] = (int)$u->id;
							}
						}
						$row['unit_id'] = $unitCache[$lk] ?: null;
						$display['unit_id'] = ucwords($unitName);
						break;

					case 'purchase_price':
					case 'stock':
					case 'alert_qty':
						$num = str_replace([',',' '], '', $raw);
						$row[$colKey] = ($raw !== '' && is_numeric($num) && $num >= 0) ? (float)$num : 0;
						break;

					default:
						$row[$colKey] = $raw;
				}
			}
			$row['_display'] = $display;
			$rows[] = $row;
		}

		return ['rows' => $rows, 'errors' => $errors, 'created_units' => $createdUnits];
	}

	private function _entityPreviewHtml($def, $values, $display){
		$rows = '';
		foreach($this->_entityFields($def) as $f){
			$key = $f['key'];
			if(!array_key_exists($key, $values) || $values[$key] === null) continue;
			$val = $display[$key] ?? $values[$key];
			if(in_array($f['type'] ?? 'text', ['number'])) $val = number_format((float)$val, 2);
			$rows .= '<tr><td>'.ucwords(str_replace('_', ' ', $key)).'</td><td>'.htmlspecialchars((string)$val).'</td></tr>';
		}
		return '<div class="assist-card"><h4>'.htmlspecialchars($def['label']).' Preview</h4><table class="assist-table">'.$rows.'</table></div>';
	}

	private function _entityBulkPreviewHtml($def, $rows, $createdUnits){
		$html = '<div class="assist-card"><h4>'.count($rows).' '.$this->_pluralize($def['label']).'</h4>';
		$html .= '<div class="assist-table-wrap"><table class="assist-table">';
		$html .= '<tr><th>Name</th><th>Unit</th><th>Cost</th><th>Qty</th></tr>';
		foreach($rows as $r){
			$html .= '<tr><td>'.htmlspecialchars($r['item_name'] ?? '').'</td><td>'.htmlspecialchars($r['_display']['unit_id'] ?? '').'</td><td>'.number_format($r['purchase_price'] ?? 0, 2).'</td><td>'.number_format($r['stock'] ?? 0).'</td></tr>';
		}
		$html .= '</table></div>';
		if(!empty($createdUnits)){
			$html .= '<p style="margin-top:6px;font-size:12px;color:#666;">New units will be added: '.implode(', ', array_map('htmlspecialchars', $createdUnits)).'</p>';
		}
		$html .= '</div>';
		return $html;
	}

	private function _entityInsert($entityKey, $def, $values, $storeId){
		if($entityKey === 'raw_material'){
			return $this->_insertRawMaterial($def, $values, $storeId);
		}
		return null;
	}

	private function _insertRawMaterial($def, $values, $storeId){
		$name = trim($values['item_name'] ?? '');
		if($name === '') return null;

		$countId = get_count_id('db_items');
		$prefix = get_only_init_code('item');
		$itemCode = ($prefix ?: 'RM-') . $countId;
		$categoryId = $this->_ensureCategory($def['auto_category'] ?? 'Raw Materials', $storeId);

		$cost = (float)($values['purchase_price'] ?? 0);
		$insert = [
			'item_code'      => $itemCode,
			'count_id'       => $countId,
			'item_name'      => $name,
			'category_id'    => $categoryId,
			'brand_id'       => 0,
			'sku'            => $itemCode,
			'hsn'            => '',
			'unit_id'        => (int)($values['unit_id'] ?? 0),
			'alert_qty'      => (float)($values['alert_qty'] ?? 0),
			'purchase_price' => $cost,
			'sales_price'    => $cost,
			'stock'          => (float)($values['stock'] ?? 0),
			'tax_id'         => 0,
			'tax_type'       => 'Inclusive',
			'profit_margin'  => null,
			'seller_points'  => null,
			'custom_barcode' => '',
			'description'    => 'Created via MartPoint Assist',
			'item_group'     => 'Single',
			'discount_type'  => 'Percentage',
			'discount'       => 0,
			'mrp'            => $cost,
			'expire_date'    => !empty($values['expire_date']) ? $values['expire_date'] : null,
			'mfg_date'       => null,
			'service_bit'    => 0,
			'status'         => 1,
			'store_id'       => $storeId,
			'created_date'   => date('Y-m-d'),
			'created_time'   => date('H:i:s'),
			'created_by'     => $this->session->userdata('inv_username') ?: 'system',
			'system_ip'      => $this->input->ip_address(),
			'system_name'    => 'MartPoint Assist'
		];
		// These columns only exist on installs that ran the consumables migration
		if($this->db->field_exists('not_for_sale', 'db_items')) $insert['not_for_sale'] = 1;
		if($this->db->field_exists('consumable_unit', 'db_items') && !empty($values['consumable_unit'])){
			$insert['consumable_unit'] = $values['consumable_unit'];
		}

		$this->db->insert('db_items', $insert);
		return $this->db->insert_id();
	}

	private function _ensureCategory($name, $storeId){
		$cat = $this->db->like('category_name', $name, 'both')->where('store_id', $storeId)->get('db_category')->row();
		if($cat) return $cat->id;
		$this->db->insert('db_category', [
			'count_id'       => get_count_id('db_category'),
			'category_code'  => get_init_code('category'),
			'category_name'  => $name,
			'description'    => '',
			'category_image' => '',
			'store_id'       => $storeId,
			'status'         => 1
		]);
		return $this->db->insert_id();
	}

	private function _pluralize($word){
		if(substr(strtolower($word), -1) === 's') return $word;
		return $word.'s';
	}

	// =================== AI UNDERSTANDING (optional layer) ===================
	//
	// When config/assist_ai.php is enabled with an API key, messages the
	// keyword engine cannot classify are sent to an OpenAI-compatible LLM.
	// The AI only picks a capability + extracts entities — execution stays
	// deterministic inside this model. Returns null on any failure so the
	// caller falls back cleanly.

	private function _aiDecide($message, $sessionId){
		if(!$this->assist_brain->enabled()) return null;

		$decision = $this->assist_brain->decide($message, $this->_aiContext($message));
		if(!$decision) return null;

		switch($decision['action']){
			case 'capability':
				return $this->_executeCapability($decision['name'], $decision['entities'] ?? [], $sessionId, $message);
			case 'answer':
				return $this->_response('text', $decision['text'], ['quick_tasks' => $this->_getFilteredQuickTasks()]);
			case 'clarify':
				return $this->_response('text', $decision['text']);
			case 'cancel':
				return $this->_response('text', 'Okay — cancelled.', ['quick_tasks' => $this->_getFilteredQuickTasks()]);
		}
		return null;
	}

	private function _aiContext($message = ''){
		$biz = $this->_biz();
		$types = function_exists('mp_get_business_types') ? mp_get_business_types() : [];
		return [
			'business' => [
				'industry'       => $biz['industry_type'] ?? 'general_retail',
				'industry_label' => $types[$biz['industry_type'] ?? ''] ?? 'General Retail',
				'labels'         => $biz['labels'] ?? [],
				'features'       => $biz['features'] ?? [],
			],
			'role'         => $this->assist_knowledge_model->getUserRoleLevel(),
			'capabilities' => $this->_aiCapabilities(),
			'history'      => $this->session->userdata('assist_history') ?: [],
			'knowledge'    => $this->assist_knowledge_model->rank($message, 3),
		];
	}

	/**
	 * Capabilities the AI may trigger — filtered by role, permission,
	 * business-type feature flags and the config whitelist.
	 */
	private function _aiCapabilities(){
		$roleLevel = $this->assist_knowledge_model->getUserRoleLevel();
		$levelVal = $this->assist_knowledge_model->roleHierarchy[$roleLevel] ?? 0;
		$itemLabel = strtolower($this->_term('item', 'item'));
		$custLabel = strtolower($this->_term('customer', 'customer'));

		$caps = [
			['name' => 'create_sale',       'desc' => "create a sale/invoice for a {$custLabel} (entities: customer, item, qty)", 'min' => 0, 'perm' => 'sales_add'],
			['name' => 'create_customer',   'desc' => "add a new {$custLabel} (entities: name, phone)", 'min' => 0, 'perm' => 'customers_add'],
			['name' => 'check_stock',       'desc' => "check stock level or find a {$itemLabel} (entities: item)", 'min' => 0],
			['name' => 'low_stock',         'desc' => 'list items running low on stock', 'min' => 0],
			['name' => 'find_customer',     'desc' => "look up a {$custLabel} (entities: customer)", 'min' => 0],
			['name' => 'online_orders',     'desc' => 'online order status summary', 'min' => 0],
			['name' => 'today_sales',       'desc' => "today's sales totals", 'min' => 1],
			['name' => 'daily_summary',     'desc' => 'today sales vs expenses summary', 'min' => 1],
			['name' => 'today_profit',      'desc' => "today's profit figure", 'min' => 1],
			['name' => 'top_products',      'desc' => 'best selling items today', 'min' => 1],
			['name' => 'record_expense',    'desc' => 'record a business expense (entities: category, amount)', 'min' => 1],
			['name' => 'view_debts',        'desc' => "who owes money / outstanding balances (entities: customer)", 'min' => 1],
			['name' => 'create_item',       'desc' => "add a new {$itemLabel} to the catalog (entities: name)", 'min' => 2],
			['name' => 'create_purchase',   'desc' => 'record stock purchased from a supplier (entities: supplier, item, qty)', 'min' => 2],
			['name' => 'create_account',    'desc' => 'create an accounting ledger/account (entities: name, type)', 'min' => 2],
			['name' => 'edit_store',        'desc' => 'update store details like name, phone, address (entities: field, value)', 'min' => 2],
		];
		if($this->_featAny(['recipe_tracking','production_workflow'])){
			$caps[] = ['name' => 'create_raw_material', 'desc' => 'add a '.strtolower($this->_rawMaterialLabel()).' — a not-for-sale input used in recipes/production (entities: name, unit, price, qty). Can create several at once.', 'min' => 2, 'perm' => 'items_add'];
		}

		$out = [];
		foreach($caps as $c){
			if($levelVal < ($c['min'] ?? 0)) continue;
			if(!empty($c['perm']) && !$this->_hasPermission($c['perm'])) continue;
			if(!$this->assist_brain->capAllowed($c['name'])) continue;
			$out[] = ['name' => $c['name'], 'desc' => $c['desc']];
		}
		return $out;
	}

	private function _executeCapability($name, $entities, $sessionId, $message){
		$roleLevel = $this->assist_knowledge_model->getUserRoleLevel();
		$levelVal = $this->assist_knowledge_model->roleHierarchy[$roleLevel] ?? 0;
		$storeId = get_current_store_id();

		switch($name){
			case 'create_sale':
				if(!$this->_hasPermission('sales_add')) return $this->_roleDeniedResponse();
				if(!empty($entities['customer'])){
					$conversation = [
						'flow' => 'create_sale', 'step' => 'search_customer_result',
						'data' => $entities, 'created_at' => time()
					];
					$this->_setConversation($conversation, $sessionId);
					return $this->_processFlowStep((string)$entities['customer'], $conversation, $sessionId);
				}
				return $this->_startFlow('create_sale', $sessionId);

			case 'create_customer':
				if(!$this->_hasPermission('customers_add')) return $this->_roleDeniedResponse();
				return $this->_startFlow('create_customer', $sessionId);

			case 'create_item':
				if($levelVal < 2) return $this->_roleDeniedResponse();
				return $this->_startFlow('create_item', $sessionId);

			case 'create_raw_material':
				if($levelVal < 2 || !$this->_hasPermission('items_add')) return $this->_roleDeniedResponse();
				if(!$this->_featAny(['recipe_tracking','production_workflow'])) return $this->_rawMaterialUnavailable();
				$conversation = [
					'flow' => 'entity_raw_material', 'step' => 'init',
					'data' => ['values' => []], 'created_at' => time()
				];
				if(!empty($entities['name'])){
					$conversation['data']['values']['item_name'] = ucwords(trim((string)$entities['name']));
				}
				if(!empty($entities['unit'])){
					$u = $this->_findUnitByName((string)$entities['unit'], $storeId);
					if($u){
						$conversation['data']['values']['unit_id'] = (int)$u->id;
						$conversation['data']['display']['unit_id'] = $u->unit_name;
					}
				}
				if(isset($entities['price']) && is_numeric($entities['price'])){
					$conversation['data']['values']['purchase_price'] = (float)$entities['price'];
				}
				if(isset($entities['qty']) && is_numeric($entities['qty'])){
					$conversation['data']['values']['stock'] = (float)$entities['qty'];
				}
				$this->_setConversation($conversation, $sessionId);
				return $this->_processFlowStep('', $conversation, $sessionId);

			case 'create_purchase':
				if($levelVal < 2) return $this->_roleDeniedResponse();
				return $this->_startFlow('create_purchase', $sessionId);
			case 'create_account':
				if($levelVal < 2) return $this->_roleDeniedResponse();
				return $this->_startFlow('create_account', $sessionId);
			case 'edit_store':
				if($levelVal < 2) return $this->_roleDeniedResponse();
				return $this->_startFlow('edit_store', $sessionId);

			case 'check_stock':      return $this->_checkStock('', $entities['item'] ?? '');
			case 'low_stock':        return $this->_lowStock();
			case 'find_customer':    return $this->_searchCustomer('', $entities['customer'] ?? '');
			case 'online_orders':    return $this->_onlineOrders();
			case 'today_sales':
			case 'daily_summary':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_businessSummary();
			case 'today_profit':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_todayProfit();
			case 'top_products':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_topProducts();
			case 'record_expense':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_createExpenseDraft($message);
			case 'view_debts':
			case 'customer_balance':
				if($levelVal < 1) return $this->_roleDeniedResponse();
				return $this->_customerBalances($entities['customer'] ?? '');
		}
		return null; // unknown capability name — caller falls back
	}

	/**
	 * Store the last few exchanges so the AI can resolve pronouns/context.
	 * Called by the controller after every processed message.
	 */
	public function rememberExchange($message, $response){
		$history = $this->session->userdata('assist_history') ?: [];
		$botText = isset($response['text']) ? trim(strip_tags($response['text'])) : '';
		$history[] = ['user' => mb_substr($message, 0, 200), 'bot' => mb_substr($botText, 0, 200)];
		if(count($history) > 6) $history = array_slice($history, -6);
		$this->session->set_userdata('assist_history', $history);
	}

	// =================== INTENT DETECTION ===================

	private function _detectIntent($message){
		$msg = strtolower(trim($message));
		$entities = [];

		// Natural language patterns are checked first; they set entities so the
		// flow can skip redundant questions and respond fast.
		$saleCustomerPatterns = [
			'/(?:create|make|start|do|need)\s+(?:a\s+)?(?:sale|invoice|bill)(?:\s+(?:for|to))\s+(.+)/i',
			'/(?:sell|give)(?:\s+to\s+|\s+for\s+)(.+)/i',
			'/(?:sale|invoice)\s+(?:for\s+|to\s+)(.+)/i',
			'/(?:sell|sale)\s+(?:to\s+)(.+)/i',
		];
		foreach($saleCustomerPatterns as $pattern){
			if(preg_match($pattern, $msg, $m)){
				$entities['customer'] = $this->_cleanEntity($m[1]);
				return ['intent' => 'CREATE_SALE', 'entities' => $entities];
			}
		}

		// "sell 2 packs of milk to Muniru" -> qty/item/customer
		if(preg_match('/(?:sell|sale)\s+(\d+)\s*(?:pcs?|pieces?|packs?|cartons?|units?|qty|quantity)?\s*(?:of\s+)?(.+?)\s+(?:to|for)\s+(.+)/i', $msg, $m)){
			$entities['qty'] = (int)$m[1];
			$entities['item'] = $this->_cleanEntity($m[2]);
			$entities['customer'] = $this->_cleanEntity($m[3]);
			return ['intent' => 'CREATE_SALE', 'entities' => $entities];
		}

		// Customer search patterns: "find customer Muniru" / "who is Muniru" / "lookup Muniru"
		$customerPatterns = [
			'/(?:find|search|lookup)(?:\s+(?:customer|who\s+is))\s+(.+)/i',
			'/(?:who\s+is)\s+(.+)/i',
			'/(?:find|search|lookup)\s+(.+?)(?:\s+customer)?$/i',
		];
		foreach($customerPatterns as $pattern){
			if(preg_match($pattern, $msg, $m)){
				$entities['customer'] = $this->_cleanEntity($m[1]);
				return ['intent' => 'SEARCH_CUSTOMER', 'entities' => $entities];
			}
		}

		// Stock / product patterns
		$stockPatterns = [
			'/(?:stock|quantity|how many|how much)\s+(?:of\s+|left\s+(?:of\s+)?)?(.+)/i',
			'/(?:check|find|search)\s+(?:stock|product|item)(?:\s+(?:for|of))?\s+(.+)/i',
		];
		foreach($stockPatterns as $pattern){
			if(preg_match($pattern, $msg, $m)){
				$entities['item'] = $this->_cleanEntity($m[1]);
				return ['intent' => 'CHECK_STOCK', 'entities' => $entities];
			}
		}

		// Raw material / ingredient creation: "create raw material flour", "add ingredient sugar"
		if(preg_match('/(?:create|add|new|register|make)\s+(?:a\s+|an\s+|some\s+)?(?:raw\s+materials?|ingredients?|consumables?|materials?)\s+(?:called\s+|named\s+)?(.+)/i', $msg, $m)){
			$entities['name'] = $this->_cleanEntity($m[1]);
			return ['intent' => 'CREATE_RAW_MATERIAL', 'entities' => $entities];
		}
		if(preg_match('/(?:raw\s+materials?|ingredients?|consumables?)\s*$/i', $msg)){
			return ['intent' => 'CREATE_RAW_MATERIAL', 'entities' => $entities];
		}

		// Keyword fallback for fast matching. Word-boundary match so short
		// keywords ('hi','owe','bill') don't fire inside words like 'his',
		// 'towel', 'billion'. Trailing 's' allowed for plurals ('items').
		$scores = [];
		foreach($this->intents as $intent => $keywords){
			$score = 0;
			foreach($keywords as $kw){
				$plural = strlen($kw) >= 3 ? 's?' : '';
				if(preg_match('/\b'.preg_quote($kw, '/').$plural.'\b/i', $msg)) $score += strlen($kw);
			}
			if($score > 0) $scores[$intent] = $score;
		}
		if(!empty($scores)){
			arsort($scores);
			return ['intent' => array_key_first($scores), 'entities' => $entities];
		}
		return ['intent' => 'UNKNOWN', 'entities' => $entities];
	}

	private function _cleanEntity($value){
		$value = trim($value);
		$value = preg_replace('/\?|!|\.|,$/', '', $value);
		$value = preg_replace('/\s+/', ' ', $value);
		return $value;
	}

	// =================== CUSTOMER ===================

	private function _searchCustomer($message, $customerName = null){
		$name = !empty($customerName) ? $customerName : $this->_extractAfterKeywords($message, ['who is','find customer','search customer','lookup customer','customer','find','search','lookup']);
		if(empty($name)) return $this->_recentCustomers();

		$this->db->like('customer_name', $name, 'both');
		$this->db->or_like('mobile', $name, 'both');
		$customers = $this->db->get('db_customers')->result();

		if(empty($customers)){
			$html = '<div class="assist-card"><h4>No Match</h4><p>No customer found for "'.ucwords($name).'".</p><p><a href="'.base_url('customers/add').'" target="_blank" style="color:#667eea;font-weight:600;">+ Create New Customer</a></p></div>';
			return $this->_response('html', 'No customer found:', ['html' => $html, 'quick_tasks' => $this->_getFilteredQuickTasks()]);
		}
		if(count($customers) === 1) return $this->_customerDetail($customers[0]->id);

		$options = [];
		foreach($customers as $c){
			$options[] = ['label' => ($c->customer_name ?? 'Unknown').' - '.($c->mobile ?? 'No phone'), 'value' => 'customer_'.$c->id];
		}
		return $this->_response('choice', 'Multiple customers found:', ['options' => $options, 'context' => 'customer_search', 'quick_tasks' => $this->_getFilteredQuickTasks()]);
	}

	private function _recentCustomers(){
		$this->db->order_by('id', 'DESC');
		$this->db->limit(10);
		$customers = $this->db->get('db_customers')->result();

		if(empty($customers)){
			$html = '<div class="assist-card"><h4>Customers</h4><p>No customers yet.</p><p><a href="'.base_url('customers/add').'" target="_blank" style="color:#667eea;font-weight:600;">+ Create First Customer</a></p></div>';
			return $this->_response('html', 'Customers:', ['html' => $html, 'quick_tasks' => $this->_getFilteredQuickTasks()]);
		}

		$html = '<div class="assist-card"><h4>Recent Customers</h4><table class="assist-table">';
		$html .= '<tr><th>Name</th><th>Phone</th><th>Balance</th></tr>';
		foreach($customers as $c){
			$this->db->select_sum('debit'); $debit = $this->db->where('customer_id', $c->id)->get('db_customer_payments')->row()->debit ?? 0;
			$this->db->select_sum('credit'); $credit = $this->db->where('customer_id', $c->id)->get('db_customer_payments')->row()->credit ?? 0;
			$balance = $debit - $credit;
			$html .= '<tr><td>'.($c->customer_name ?? 'Unknown').'</td><td>'.($c->mobile ?? 'N/A').'</td><td>'.number_format($balance, 2).'</td></tr>';
		}
		$html .= '</table></div>';
		return $this->_response('html', 'Recent customers:', ['html' => $html, 'quick_tasks' => $this->_getFilteredQuickTasks()]);
	}

	private function _customerDetail($customerId){
		$customer = $this->db->where('id', $customerId)->get('db_customers')->row();
		if(!$customer) return $this->_response('text', 'Customer not found.');

		$this->db->select_sum('debit'); $debit = $this->db->where('customer_id', $customerId)->get('db_customer_payments')->row()->debit ?? 0;
		$this->db->select_sum('credit'); $credit = $this->db->where('customer_id', $customerId)->get('db_customer_payments')->row()->credit ?? 0;
		$balance = $debit - $credit;

		$lastSale = $this->db->where('customer_id', $customerId)->order_by('id', 'DESC')->limit(1)->get('db_sales')->row();

		$html = '<div class="assist-card"><h4>'.($customer->customer_name ?? 'Unknown').'</h4>';
		$html .= '<p><strong>Phone:</strong> '.($customer->mobile ?? 'N/A').'</p>';
		$html .= '<p><strong>Email:</strong> '.($customer->email ?? 'N/A').'</p>';
		$html .= '<p><strong>Outstanding:</strong> '.number_format($balance, 2).'</p>';
		if($lastSale) $html .= '<p><strong>Last Purchase:</strong> '.date('d M Y', strtotime($lastSale->sales_date)).'</p>';
		$html .= '</div>';
		return $this->_response('html', 'Customer found:', ['html' => $html, 'quick_tasks' => $this->_getFilteredQuickTasks()]);
	}

	private function _customerBalances($message){
		$name = $this->_extractAfterKeywords($message, ['balance','owe','debt','outstanding','how much does']);
		if(!empty($name) && strlen($name) > 2) return $this->_searchCustomer($message);

		$this->db->select('c.id, c.customer_name, c.mobile, SUM(cp.debit - cp.credit) as balance');
		$this->db->from('db_customers c');
		$this->db->join('db_customer_payments cp', 'cp.customer_id = c.id', 'left');
		$this->db->group_by('c.id');
		$this->db->having('balance >', 0);
		$this->db->order_by('balance', 'DESC');
		$this->db->limit(10);
		$debts = $this->db->get()->result();

		if(empty($debts)) return $this->_response('text', 'No customers with outstanding balance.', ['quick_tasks' => $this->_getFilteredQuickTasks()]);

		$html = '<div class="assist-card"><h4>Outstanding Debts</h4><table class="assist-table">';
		$html .= '<tr><th>Customer</th><th>Phone</th><th>Balance</th></tr>';
		foreach($debts as $d){
			$html .= '<tr><td>'.($d->customer_name ?? '').'</td><td>'.($d->mobile ?? 'N/A').'</td><td>'.number_format($d->balance, 2).'</td></tr>';
		}
		$html .= '</table></div>';
		return $this->_response('html', 'Customers with outstanding balances:', ['html' => $html, 'quick_tasks' => $this->_getFilteredQuickTasks()]);
	}

	private function _createCustomerDraft($message){
		preg_match('/(?:create|add|new|register)\s+customer\s+(.+?)(?:\s+(\d{10,11}))?$/i', $message, $m);
		$name = trim($m[1] ?? ''); $phone = trim($m[2] ?? '');
		if(empty($name)) return $this->_recentCustomers();

		$draft = ['id' => $this->_generateDraftId(), 'type' => 'customer', 'customer_name' => $name, 'mobile' => $phone];
		$this->_saveDraft($draft);

		$html = '<div class="assist-card"><h4>New Customer Preview</h4>';
		$html .= '<p><strong>Name:</strong> '.ucwords($name).'</p>';
		$html .= '<p><strong>Phone:</strong> '.($phone ?: 'Not provided').'</p></div>';
		return $this->_response('draft', 'Review customer details:', ['html' => $html, 'draft_id' => $draft['id'], 'draft_type' => 'customer']);
	}

	// =================== STOCK ===================

	private function _checkStock($message, $itemName = null){
		$name = !empty($itemName) ? $itemName : $this->_extractAfterKeywords($message, ['stock','how many','quantity','left','available','find product','search product','product','item']);
		if(empty($name)) return $this->_quickStockSummary();

		$this->db->like('item_name', $name, 'both');
		$this->db->or_like('item_code', $name, 'both');
		$items = $this->db->get('db_items')->result();

		if(empty($items)) return $this->_response('text', 'No '.strtolower($this->_term('item','item')).' found matching "'.ucwords($name).'".');
		if(count($items) === 1) return $this->_productStockDetail($this->_getItemWithBarcode($items[0]->id));

		$options = [];
		foreach($items as $item){
			$options[] = ['label' => ($item->item_name ?? 'Unknown').' ('.($item->item_code ?? 'N/A').')', 'value' => 'product_'.$item->id];
		}
		return $this->_response('choice', 'Multiple '.$this->_pluralize(strtolower($this->_term('item','item'))).' found:', ['options' => $options, 'context' => 'product_search']);
	}

	private function _quickStockSummary(){
		$this->db->select('item_name, item_code, stock, sales_price, alert_qty');
		$this->db->where('stock >', 0);
		$this->db->order_by('stock', 'DESC');
		$this->db->limit(10);
		$items = $this->db->get('db_items')->result();

		if(empty($items)) return $this->_response('text', 'No products in stock.');

		$html = '<div class="assist-card"><h4>Stock Summary</h4><table class="assist-table">';
		$html .= '<tr><th>Product</th><th>Code</th><th>Stock</th><th>Status</th></tr>';
		foreach($items as $item){
			$status = ($item->stock <= ($item->alert_qty ?? 0)) ? '<span style="color:orange">Low</span>' : '<span style="color:green">OK</span>';
			$html .= '<tr><td>'.($item->item_name ?? '').'</td><td>'.($item->item_code ?? '').'</td><td>'.($item->stock ?? 0).'</td><td>'.$status.'</td></tr>';
		}
		$html .= '</table></div>';
		return $this->_response('html', 'Top products in stock:', ['html' => $html]);
	}

	private function _productStockDetail($item){
		if(empty($item->barcode)){
			$item = $this->_getItemWithBarcode($item->id);
			if(!$item) return $this->_response('text', 'Product not found.');
		}
		$this->db->select('w.warehouse_name, wi.available_qty as stock');
		$this->db->from('db_warehouseitems wi');
		$this->db->join('db_warehouse w', 'w.id = wi.warehouse_id', 'left');
		$this->db->where('wi.item_id', $item->id);
		$stocks = $this->db->get()->result();

		$alertQty = $item->alert_qty ?? 0;
		$html = '<div class="assist-card"><h4>'.($item->item_name ?? 'Unknown').'</h4>';
		$html .= '<p><strong>Code:</strong> '.($item->item_code ?? 'N/A').'</p>';
		$html .= '<p><strong>Price:</strong> '.number_format($item->sales_price ?? 0, 2).'</p>';
		if(!empty($stocks)){
			$html .= '<table class="assist-table"><tr><th>Branch</th><th>Stock</th><th>Status</th></tr>';
			foreach($stocks as $s){
				$status = ($s->stock <= 0) ? '<span style="color:red">Out</span>' : (($s->stock <= $alertQty) ? '<span style="color:orange">Low</span>' : '<span style="color:green">OK</span>');
				$html .= '<tr><td>'.($s->warehouse_name ?? 'Main').'</td><td>'.($s->stock ?? 0).'</td><td>'.$status.'</td></tr>';
			}
			$html .= '</table>';
		} else {
			$html .= '<p>Stock: '.($item->stock ?? 0).'</p>';
		}
		$html .= '</div>';
		return $this->_response('html', 'Stock information:', ['html' => $html]);
	}

	private function _productDetail($productId){
		$item = $this->_getItemWithBarcode($productId);
		if(!$item) return $this->_response('text', 'Product not found.');
		return $this->_productStockDetail($item);
	}

	private function _lowStock(){
		$this->db->select('i.item_name, i.item_code, i.stock, i.alert_qty, w.warehouse_name, wi.available_qty as wh_stock');
		$this->db->from('db_items i');
		$this->db->join('db_warehouseitems wi', 'wi.item_id = i.id', 'left');
		$this->db->join('db_warehouse w', 'w.id = wi.warehouse_id', 'left');
		$this->db->where('i.stock <= i.alert_qty');
		$this->db->order_by('i.stock', 'ASC');
		$this->db->limit(20);
		$items = $this->db->get()->result();

		if(empty($items)) return $this->_response('text', 'No low stock items. All products are well stocked!');

		$html = '<div class="assist-card"><h4>Low Stock Alert</h4><table class="assist-table">';
		$html .= '<tr><th>Product</th><th>Code</th><th>Stock</th><th>Alert</th><th>Branch</th></tr>';
		foreach($items as $item){
			$html .= '<tr><td>'.($item->item_name ?? '').'</td><td>'.($item->item_code ?? '').'</td><td>'.($item->stock ?? 0).'</td><td>'.($item->alert_qty ?? 0).'</td><td>'.($item->warehouse_name ?? 'Main').'</td></tr>';
		}
		$html .= '</table></div>';
		return $this->_response('html', 'Items running low:', ['html' => $html]);
	}

	// =================== SALE ===================

	private function _createSaleDraft($message){
		$items = $this->_parseSaleItems($message);
		$customer = $this->_parseCustomerFromMessage($message);
		if(empty($items)) return $this->_quickSaleOptions();

		$draftItems = []; $total = 0;
		foreach($items as $iq){
			$found = $this->_findProduct($iq['name']);
			if(!$found) return $this->_response('text', 'Product not found: "'.ucwords($iq['name']).'". Check name and retry.');
			if(is_array($found)){
				$options = [];
				foreach($found as $f) $options[] = ['label' => $f->item_name.' @ '.number_format($f->sales_price, 2), 'value' => 'product_'.$f->id];
				return $this->_response('choice', 'Multiple products match "'.ucwords($iq['name']).'":', ['options' => $options, 'context' => 'sale_product_select']);
			}
			$qty = $iq['qty']; $price = $found->sales_price ?? 0; $sub = $qty * $price; $total += $sub;
			$draftItems[] = ['item_id' => $found->id, 'item_name' => $found->item_name, 'qty' => $qty, 'price' => $price, 'subtotal' => $sub];
		}

		$draft = ['id' => $this->_generateDraftId(), 'type' => 'sale', 'items' => $draftItems, 'total' => $total, 'customer' => $customer];
		$this->_saveDraft($draft);

		$html = '<div class="assist-card"><h4>Sale Draft</h4>';
		$html .= '<p><strong>Customer:</strong> '.($customer ? ucwords($customer) : 'Walk-in').'</p>';
		$html .= '<table class="assist-table"><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>';
		foreach($draftItems as $di) $html .= '<tr><td>'.$di['item_name'].'</td><td>'.$di['qty'].'</td><td>'.number_format($di['price'], 2).'</td><td>'.number_format($di['subtotal'], 2).'</td></tr>';
		$html .= '<tr style="font-weight:bold"><td colspan="3">Total</td><td>'.number_format($total, 2).'</td></tr></table></div>';
		return $this->_response('draft', 'Sale draft ready:', ['html' => $html, 'draft_id' => $draft['id'], 'draft_type' => 'sale']);
	}

	private function _quickSaleOptions(){
		$storeId = get_current_store_id();
		$today = date('Y-m-d');
		$this->db->where('sales_date', $today)->where('store_id', $storeId)->order_by('id', 'DESC')->limit(5);
		$sales = $this->db->get('db_sales')->result();

		$html = '<div class="assist-card"><h4>Quick Sale</h4>';
		$html .= '<p><a href="'.base_url('pos').'" target="_blank" style="color:#667eea;font-weight:600;"><i class="fa fa-shopping-cart"></i> Open POS Screen</a></p>';
		if(!empty($sales)){
			$html .= '<p style="margin-top:8px;font-size:12px;color:#666;"><strong>Today\'s Sales</strong></p>';
			$html .= '<table class="assist-table">';
			$html .= '<tr><th>Time</th><th>Amount</th></tr>';
			foreach($sales as $s){
				$html .= '<tr><td>'.($s->created_time ?? '--').'</td><td>'.number_format($s->grand_total ?? 0, 2).'</td></tr>';
			}
			$html .= '</table>';
		}
		$html .= '</div>';
		return $this->_response('html', 'Ready to sell:', ['html' => $html]);
	}

	// =================== EXPENSE ===================

	private function _createExpenseDraft($message){
		preg_match('/(?:record|create|add)?\s*(.+?)\s+(?:expense|cost)?\s*(?:n|N|#)?\s*([\d,.]+)/i', $message, $m);
		$category = trim($m[1] ?? ''); $amount = str_replace(',', '', trim($m[2] ?? ''));
		if(empty($category) || empty($amount) || !is_numeric($amount)) return $this->_todayExpenses();

		$draft = ['id' => $this->_generateDraftId(), 'type' => 'expense', 'category' => $category, 'amount' => (float)$amount, 'date' => date('Y-m-d')];
		$this->_saveDraft($draft);

		$html = '<div class="assist-card"><h4>Expense Preview</h4>';
		$html .= '<p><strong>Category:</strong> '.ucwords($category).'</p>';
		$html .= '<p><strong>Amount:</strong> '.number_format($amount, 2).'</p>';
		$html .= '<p><strong>Date:</strong> '.date('d M Y').'</p></div>';
		return $this->_response('draft', 'Review this expense:', ['html' => $html, 'draft_id' => $draft['id'], 'draft_type' => 'expense']);
	}

	private function _todayExpenses(){
		$storeId = get_current_store_id(); $today = date('Y-m-d');
		$this->db->where('expense_date', $today)->where('store_id', $storeId)->order_by('id', 'DESC');
		$expenses = $this->db->get('db_expense')->result();

		$html = '<div class="assist-card"><h4>Today\'s Expenses</h4>';
		if(empty($expenses)){
			$html .= '<p>No expenses recorded today.</p>';
		} else {
			$html .= '<table class="assist-table">';
			$html .= '<tr><th>Category</th><th>Amount</th></tr>';
			foreach($expenses as $e){
				$html .= '<tr><td>'.($e->expense_for ?? 'N/A').'</td><td>'.number_format($e->expense_amt ?? 0, 2).'</td></tr>';
			}
			$html .= '</table>';
		}
		$html .= '<p style="margin-top:10px;"><a href="'.base_url('expense/add').'" target="_blank" style="color:#667eea;font-weight:600;">+ Add Expense</a></p></div>';
		return $this->_response('html', 'Today\'s expenses:', ['html' => $html]);
	}

	// =================== REPORTS ===================

	private function _businessSummary(){
		$today = date('Y-m-d');
		$storeId = get_current_store_id();

		$this->db->select_sum('grand_total');
		$salesTotal = $this->db->where('sales_date', $today)->where('store_id', $storeId)->get('db_sales')->row()->grand_total ?? 0;
		$salesCount = $this->db->where('sales_date', $today)->where('store_id', $storeId)->count_all_results('db_sales');

		$this->db->select_sum('expense_amt');
		$expenses = $this->db->where('expense_date', $today)->where('store_id', $storeId)->get('db_expense')->row()->expense_amt ?? 0;

		$html = '<div class="assist-card"><h4>Today\'s Summary</h4>';
		$html .= '<p><strong>Sales:</strong> '.number_format($salesTotal, 2).' ('.$salesCount.' transactions)</p>';
		$html .= '<p><strong>Expenses:</strong> '.number_format($expenses, 2).'</p>';
		$html .= '<p><strong>Net:</strong> '.number_format($salesTotal - $expenses, 2).'</p></div>';
		return $this->_response('html', 'Today\'s business summary:', ['html' => $html, 'quick_tasks' => $this->_getFilteredQuickTasks()]);
	}

	private function _todayProfit(){
		$this->load->model('dashboard_model');
		$summary = $this->dashboard_model->get_daily_summary();
		$dateStr = date('F j, Y');
		$timeStr = date('g:i A');
		$salesCount = $summary['sales']['transactions'];
		$profit = $summary['profit']['gross_profit'];
		$currency = $this->session->userdata('currency_code') ?? '₦';

		if(!$summary['has_data']){
			return $this->_response('text', 'As of '.$dateStr.' and Time '.$timeStr.', no sales have been recorded today yet.', [
				'quick_tasks' => $this->_getFilteredQuickTasks()
			]);
		}

		$msg = 'As of Date <strong>'.$dateStr.'</strong> and Time <strong>'.$timeStr.'</strong>, profit recorded is <strong>'.$currency.' '.number_format($profit, 2).'</strong> from <strong>'.$salesCount.'</strong> sale'.($salesCount != 1 ? 's' : '').'.';
		return $this->_response('text', $msg, [
			'quick_tasks' => $this->_getFilteredQuickTasks()
		]);
	}

	private function _topProducts(){
		$today = date('Y-m-d'); $storeId = get_current_store_id();
		$this->db->select('i.item_name, SUM(si.sales_qty) as total_qty, SUM(si.total_cost) as revenue');
		$this->db->from('db_salesitems si');
		$this->db->join('db_items i', 'i.id = si.item_id', 'left');
		$this->db->join('db_sales s', 's.id = si.sales_id', 'left');
		$this->db->where('s.sales_date', $today);
		$this->db->where('s.store_id', $storeId);
		$this->db->group_by('si.item_id');
		$this->db->order_by('total_qty', 'DESC');
		$this->db->limit(5);
		$products = $this->db->get()->result();

		if(empty($products)) return $this->_response('text', 'No sales recorded today yet.');

		$html = '<div class="assist-card"><h4>Top Products Today</h4><table class="assist-table">';
		$html .= '<tr><th>Product</th><th>Qty</th><th>Revenue</th></tr>';
		foreach($products as $p) $html .= '<tr><td>'.($p->item_name ?? 'Unknown').'</td><td>'.($p->total_qty ?? 0).'</td><td>'.number_format($p->revenue ?? 0, 2).'</td></tr>';
		$html .= '</table></div>';
		return $this->_response('html', 'Top sellers today:', ['html' => $html]);
	}

	private function _onlineOrders(){
		$storeId = get_current_store_id(); $today = date('Y-m-d');
		$pending = 0; $completed = 0;
		if($this->db->table_exists('db_online_orders')){
			$pending = $this->db->where('order_status', 'pending')->where('store_id', $storeId)->count_all_results('db_online_orders');
			$completed = $this->db->where('order_status', 'completed')->where('DATE(created_at)', $today)->where('store_id', $storeId)->count_all_results('db_online_orders');
		}

		$html = '<div class="assist-card"><h4>Online Orders</h4>';
		$html .= '<p><strong>Pending:</strong> '.$pending.'</p>';
		$html .= '<p><strong>Completed Today:</strong> '.$completed.'</p></div>';
		return $this->_response('html', 'Online order status:', ['html' => $html]);
	}

	// =================== EXECUTION ===================

	private function _executeCustomerCreation($draft){
		$store_id = get_current_store_id();
		$mobile = $draft['mobile'] ?? '';

		// Check for duplicate mobile in same store
		if(!empty($mobile)){
			$this->db->where('mobile', $mobile);
			$this->db->where('store_id', $store_id);
			$existing = $this->db->get('db_customers')->row();
			if($existing){
				return $this->_response('text', 'A customer with mobile '.htmlspecialchars($mobile).' already exists: <strong>'.htmlspecialchars($existing->customer_name).'</strong> (ID: '.$existing->id.').');
			}
		}

		// Build proper customer record matching Customers_model::verify_and_save logic
		$data = array(
			'store_id'       => $store_id,
			'count_id'       => get_count_id('db_customers'),
			'customer_code'  => get_init_code('customer'),
			'customer_name'  => $draft['customer_name'],
			'mobile'         => $mobile,
			'created_date'   => date('Y-m-d'),
			'created_time'   => date('H:i:s'),
			'created_by'     => $this->session->userdata('inv_username') ?? 'system',
			'system_ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
			'system_name'    => gethostname() ?: '',
			'status'         => 1,
		);
		$this->db->insert('db_customers', $data);
		$customer_id = $this->db->insert_id();
		if($customer_id){
			return $this->_response('success', 'Customer "'.ucwords($draft['customer_name']).'" created successfully! <strong>Customer ID: '.$customer_id.'</strong>', [
				'customer_id'   => $customer_id,
				'customer_name'  => $draft['customer_name'],
				'quick_tasks'   => $this->_getFilteredQuickTasks()
			]);
		}
		return $this->_response('error', 'Failed to create customer.');
	}

	private function _executeExpense($draft){
		$data = ['expense_for' => $draft['category'], 'expense_amt' => $draft['amount'], 'expense_date' => $draft['date'], 'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'), 'status' => 1];
		$this->db->insert('db_expense', $data);
		if($this->db->affected_rows() > 0) return $this->_response('success', 'Expense of '.number_format($draft['amount'], 2).' recorded for '.ucwords($draft['category']).'!');
		return $this->_response('error', 'Failed to record expense.');
	}

	// =================== HELPERS ===================

	private function _parseSaleItems($message){
		$items = [];
		$skipWords = ['sell','sale','to','for','and','create','new','a','an','the','me','my','i','we','please','can','you','want','need','like','some','more','all','this','that','with','of','in','on','at','by','help','show','how','what','do','does','have','has','is','are','was','were','be','been','being','have','had','did','done','doing','will','would','could','should','may','might','must','shall','can','could','would','should','may','might','must','shall','will','would','could','should'];
		preg_match_all('/(?:\b(\d+)\s+)?([a-zA-Z][a-zA-Z\s]{2,}?)(?:\s+(?:and|&|,|\s+to\s+|\s+for\s+)|$)/i', $message, $matches, PREG_SET_ORDER);
		foreach($matches as $match){
			$qty = !empty($match[1]) ? (int)$match[1] : 1;
			$name = trim($match[2]);
			$lowerName = strtolower($name);
			if(in_array($lowerName, $skipWords)) continue;
			if(strlen($name) < 2) continue;
			// Skip if it's only common words
			$words = preg_split('/\s+/', $lowerName);
			$meaningful = array_filter($words, function($w) use($skipWords){ return !in_array($w, $skipWords); });
			if(count($meaningful) === 0) continue;
			$items[] = ['qty' => $qty, 'name' => $name];
		}
		return $items;
	}

	private function _parseCustomerFromMessage($message){
		preg_match('/\bto\s+([a-zA-Z\s]+?)(?:\s*$|\s+(?:for|and)\s+)/i', $message, $m);
		return !empty($m[1]) ? trim($m[1]) : null;
	}

	private function _findProduct($name){
		$this->db->like('item_name', $name, 'both');
		$this->db->or_like('item_code', $name, 'both');
		$q = $this->db->get('db_items');
		if($q->num_rows() === 0) return null;
		if($q->num_rows() === 1) return $q->row();
		return $q->result();
	}

	private function _extractAfterKeywords($message, $keywords){
		foreach($keywords as $kw){
			$pos = strpos($message, $kw);
			if($pos !== false){
				$after = trim(substr($message, $pos + strlen($kw)));
				if(!empty($after)) return $after;
			}
		}
		return '';
	}

	// =================== DRAFT STORAGE (Session-based) ===================

	private function _generateDraftId(){
		return 'draft_' . uniqid();
	}

	private function _saveDraft($draft){
		$drafts = $this->session->userdata('assist_drafts') ?: [];
		$drafts[$draft['id']] = $draft;
		$this->session->set_userdata('assist_drafts', $drafts);
	}

	private function _getDraft($draftId, $sessionId){
		$drafts = $this->session->userdata('assist_drafts') ?: [];
		return $drafts[$draftId] ?? null;
	}

	private function _removeDraft($draftId, $sessionId){
		$drafts = $this->session->userdata('assist_drafts') ?: [];
		unset($drafts[$draftId]);
		$this->session->set_userdata('assist_drafts', $drafts);
	}

	/**
	 * Check if the user is asking for a profit / daily summary report
	 */
	private function _isProfitQuery($message){
		$profitKeywords = ['profit','profit report','how much profit','today profit','profit today','my profit','daily summary','today summary'];
		foreach($profitKeywords as $kw){
			if(strpos($message, $kw) !== false) return true;
		}
		return false;
	}

	/**
	 * Generate contextual follow-up quick tasks for KB responses
	 */
	private function _getKbFollowUp($category){
		$roleLevel = $this->assist_knowledge_model->getUserRoleLevel();
		$levelVal = $this->assist_knowledge_model->roleHierarchy[$roleLevel] ?? 0;

		$map = [
			'sales'       => [
				'text'  => 'Would you like to create a sale now?',
				'tasks' => [
					['label' => 'Create Sale',    'action' => 'create_sale',    'icon' => 'fa-shopping-cart', 'min_role' => 'all']
				]
			],
			'inventory'   => [
				'text'  => 'Would you like to check stock levels or create a new '.$this->_term('item','item').'?',
				'tasks' => [
					['label' => 'Check Stock',    'action' => 'check_stock',    'icon' => 'fa-cubes',              'min_role' => 'all'],
					['label' => 'New '.$this->_term('item','Item'),    'action' => 'create_item',    'icon' => 'fa-plus-circle',        'min_role' => 'business_owner'],
					['label' => 'Add '.$this->_rawMaterialLabel(), 'action' => 'create_raw_material', 'icon' => 'fa-leaf', 'min_role' => 'business_owner', 'features' => ['recipe_tracking','production_workflow']]
				]
			],
			'customers'   => [
				'text'  => 'Would you like to find a customer?',
				'tasks' => [
					['label' => 'Find Customer',  'action' => 'find_customer',  'icon' => 'fa-users',              'min_role' => 'all']
				]
			],
			'expenses'    => [
				'text'  => 'Would you like to record an expense?',
				'tasks' => [
					['label' => 'Record Expense', 'action' => 'record_expense', 'icon' => 'fa-money',              'min_role' => 'cashier']
				]
			],
			'purchases'   => [
				'text'  => 'Would you like to check low stock or create a purchase order?',
				'tasks' => [
					['label' => 'Low Stock',      'action' => 'low_stock',      'icon' => 'fa-exclamation-triangle','min_role' => 'all'],
					['label' => 'New Purchase',   'action' => 'create_purchase','icon' => 'fa-shopping-basket',    'min_role' => 'business_owner']
				]
			],
			'reports'     => [
				'text'  => 'Would you like to see today\'s summary?',
				'tasks' => [
					['label' => "Today's Sales",  'action' => 'today_sales',    'icon' => 'fa-line-chart',         'min_role' => 'cashier']
				]
			],
			'settings'    => [
				'text'  => 'Would you like to make some changes to your store details?',
				'tasks' => [
					['label' => 'Edit Store',     'action' => 'edit_store',     'icon' => 'fa-cog',                'min_role' => 'business_owner']
				]
			],
			'accounts'    => [
				'text'  => 'Would you like to create a new account or ledger?',
				'tasks' => [
					['label' => 'Create Account', 'action' => 'create_account', 'icon' => 'fa-book',               'min_role' => 'business_owner']
				]
			],
		];

		$cat = strtolower($category ?? 'general');
		if(!isset($map[$cat])){
			return ['text' => 'Can I assist with anything else?', 'tasks' => []];
		}

		$filtered = [];
		foreach($map[$cat]['tasks'] as $task){
			$taskLevel = $this->assist_knowledge_model->roleHierarchy[$task['min_role']] ?? 0;
			if($levelVal < $taskLevel) continue;
			if(!empty($task['features']) && !$this->_featAny($task['features'])) continue;
			$filtered[] = ['label' => $task['label'], 'action' => $task['action'], 'icon' => $task['icon']];
		}

		return [
			'text'  => $map[$cat]['text'],
			'tasks' => $filtered
		];
	}

	/**
	 * Public wrapper for KB follow-up generation
	 */
	public function getKbFollowUp($category){
		return $this->_getKbFollowUp($category);
	}

	// =================== RESPONSE BUILDER ===================

	private function _response($type, $text, $data = []){
		return array_merge(['type' => $type, 'text' => $text], $data);
	}

	private function _conversational($text, $data = []){
		return array_merge(['type' => 'conversational', 'text' => $text], $data);
	}

	private function _welcomeResponse(){
		$name = ucfirst($this->session->userdata('display_name') ?: 'there');
		$greeting = '<div style="font-size:1.3em;margin-bottom:6px;"><span class="mp-wave-hand">👋</span> Hi <strong>'.htmlspecialchars($name).'</strong>!</div>';
		$greeting .= '<div style="margin-bottom:8px;">I am <strong>Azera</strong>, your MartPoint assistant.</div>';
		$greeting .= '<div style="margin-bottom:8px;font-size:0.95em;color:#555;">What can I help you with today?</div>';
		return $this->_response('welcome', $greeting, [
			'quick_tasks' => $this->_getFilteredQuickTasks()
		]);
	}

	private function _fallbackResponse(){
		$msg = 'Ooh 😮 I am so sorry I can\'t handle this at the moment.<br><br>';
		$msg .= '<strong>Try asking me a how-to question</strong> like:<br>';
		$msg .= '<em>"How do I create a sale?"</em> or <em>"How to check stock?"</em><br><br>';
		$msg .= 'Or would you like me to send you a guide, or you want to chat the support team?';
		return $this->_response('fallback', $msg, [
			'quick_tasks' => $this->_getFilteredQuickTasks()
		]);
	}
}
