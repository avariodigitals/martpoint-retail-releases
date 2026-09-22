<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Assist_brain — optional AI understanding layer for MartPoint Assist.
 *
 * Talks to any OpenAI-compatible chat-completions endpoint. Returns a strict
 * decision array that Assist_model maps onto its existing (deterministic)
 * capabilities. The AI never touches the database; it only chooses WHICH
 * capability to run and extracts entities from the user's message.
 *
 * Decision contract (all fields strings except entities):
 *   {"action":"capability","name":"create_sale","entities":{"customer":"musa"}}
 *   {"action":"answer","text":"..."}     general question / help reply
 *   {"action":"clarify","text":"..."}    ask the user a question
 *   {"action":"cancel"}                  user wants to stop
 *   {"action":"bulk_rows","rows":[{...}]} extract rows for a bulk entry
 *
 * Any failure (disabled, no key, timeout, bad JSON) returns null so callers
 * fall back to the rule-based engine.
 */
class Assist_brain {

	private $CI;
	private $cfg = null;

	public function __construct(){
		$this->CI =& get_instance();
		$this->CI->config->load('assist_ai', true);
		$this->cfg = $this->CI->config->item('assist_ai', 'assist_ai') ?: [];

		// DB settings (Site Settings screen) take precedence over the config file.
		// Columns are added by the 4.0.9.33 migration — guard for older installs.
		try {
			if($this->CI->db->field_exists('assist_ai_enabled', 'db_sitesettings')){
				$row = $this->CI->db->select('assist_ai_enabled, assist_ai_provider, assist_ai_endpoint, assist_ai_model, assist_ai_key')
					->order_by('id', 'asc')->limit(1)->get('db_sitesettings')->row();
				if($row){
					$this->cfg['enabled']  = (int)$row->assist_ai_enabled === 1;
					$this->cfg['provider'] = $row->assist_ai_provider ?: 'groq';
					if(!empty($row->assist_ai_endpoint)) $this->cfg['endpoint'] = $row->assist_ai_endpoint;
					if(!empty($row->assist_ai_model))    $this->cfg['model']    = $row->assist_ai_model;
					if(!empty($row->assist_ai_key))      $this->cfg['api_key']  = $row->assist_ai_key;
				}
			}
		} catch(Throwable $e){
			// DB not ready / table missing — config file still applies
		}
	}

	public function enabled(){
		return !empty($this->cfg['enabled']) && !empty($this->cfg['api_key']);
	}

	/**
	 * Is a capability allowed by the enabled_capabilities whitelist?
	 * null whitelist = everything allowed.
	 */
	public function capAllowed($name){
		$allowed = $this->cfg['enabled_capabilities'] ?? null;
		if($allowed === null) return true;
		return in_array($name, (array)$allowed, true);
	}

	/**
	 * Decide what a user message means.
	 * @param string $message  raw user text
	 * @param array  $context  business context (labels, features, capabilities, history)
	 * @return array|null normalized decision, or null on failure
	 */
	public function decide($message, $context){
		if(!$this->enabled()) return null;

		$system = $this->_systemPrompt($context);
		$payload = [
			'model'    => $this->cfg['model'] ?? 'gpt-4o-mini',
			'messages' => [
				['role' => 'system', 'content' => $system],
			],
			'temperature' => 0.1,
			'max_tokens'  => (int)($this->cfg['max_tokens'] ?? 400),
		];
		// Short history for pronoun/context resolution ("sell him another one")
		foreach(($context['history'] ?? []) as $h){
			if(!empty($h['user']))  $payload['messages'][] = ['role' => 'user',      'content' => $h['user']];
			if(!empty($h['bot']))   $payload['messages'][] = ['role' => 'assistant', 'content' => $h['bot']];
		}
		$payload['messages'][] = ['role' => 'user', 'content' => $message];
		$payload['response_format'] = ['type' => 'json_object'];

		$raw = $this->_call($payload);
		if($raw === null) return null;
		return $this->_normalize($raw);
	}

	/**
	 * Parse a free-form bulk list into rows for a given column spec.
	 * Used to salvage messy pasted lists the regex parser could not read.
	 * @return array rows (each = assoc array keyed by column names) or []
	 */
	public function extractBulkRows($message, array $columns, $entityLabel){
		if(!$this->enabled()) return [];

		$system = 'You are a data-entry parser for a retail app. '
			. 'Extract a list of '.$entityLabel.' records from the user text. '
			. 'Respond ONLY with JSON like {"rows":[{...},{...}]}. '
			. 'Each row is an object with these keys: '.implode(', ', $columns).'. '
			. 'Numbers must be plain numbers. Missing values = empty string. '
			. 'If nothing parseable, respond {"rows":[]}. ';

		$payload = [
			'model'    => $this->cfg['model'] ?? 'gpt-4o-mini',
			'messages' => [
				['role' => 'system', 'content' => $system],
				['role' => 'user',   'content' => $message],
			],
			'temperature' => 0,
			'max_tokens'  => (int)($this->cfg['max_tokens'] ?? 400),
			'response_format' => ['type' => 'json_object'],
		];

		$raw = $this->_call($payload);
		if($raw === null) return [];
		$data = json_decode($raw, true);
		if(!is_array($data) || empty($data['rows']) || !is_array($data['rows'])) return [];

		$rows = [];
		foreach($data['rows'] as $r){
			if(!is_array($r)) continue;
			$row = [];
			foreach($columns as $c){ $row[$c] = isset($r[$c]) ? trim((string)$r[$c]) : ''; }
			if(($row[$columns[0]] ?? '') !== '') $rows[] = $row;
		}
		return $rows;
	}

	// ---------------------------------------------------------------

	private function _systemPrompt($context){
		$biz   = $context['business'] ?? [];
		$caps  = $context['capabilities'] ?? [];
		$flow  = $context['active_flow'] ?? null;

		$p  = 'You are Azera, the assistant inside MartPoint, a retail/POS app. ';
		$p .= 'Business type: '.($biz['industry_label'] ?? 'retail').'. ';
		if(!empty($biz['labels'])){
			$p .= 'This business calls things: ';
			$l = [];
			foreach($biz['labels'] as $k => $v){ $l[] = "$k=\"$v\""; }
			$p .= implode(', ', $l).'. ';
		}
		if(!empty($biz['features'])){
			$p .= 'Enabled features: '.implode(', ', $biz['features']).'. ';
		}
		$p .= "User role: ".($context['role'] ?? 'staff').".\n\n";

		$p .= "Your job: decide what the user wants and reply with ONE JSON object.\n";
		$p .= "Allowed actions:\n";
		$p .= '- {"action":"capability","name":"<cap>","entities":{...}} — run a capability. Entities you can extract: customer, item, qty, name, amount, category, unit, price, date.'."\n";
		$p .= '- {"action":"answer","text":"..."} — answer a general/how-to question yourself, briefly.'."\n";
		$p .= '- {"action":"clarify","text":"..."} — the request is ambiguous; ask ONE short question.'."\n";
		$p .= '- {"action":"cancel"} — user wants to stop/abort.'."\n\n";

		$p .= "Available capabilities for THIS business and role:\n";
		foreach($caps as $c){
			$p .= '- '.$c['name'].': '.$c['desc']."\n";
		}
		if(empty($caps)) $p .= "- (none)\n";

		if($flow){
			$p .= "\nThe user is mid-way through the '{$flow['name']}' task. Current step expects: {$flow['expects']}. "
				. "If their message answers that step, return {\"action\":\"capability\",\"name\":\"__flow_reply\",\"entities\":{\"value\":\"<their answer>\"}}. "
				. "If they want out, return {\"action\":\"cancel\"}.\n";
		}

		// Role-filtered, feature-gated KB hits — the AI's ground truth for
		// "how do I..." questions so it answers as an interactive guide.
		$kb = $context['knowledge'] ?? [];
		if(!empty($kb)){
			$p .= "\nReference knowledge for this store (use it to answer how-to questions; paraphrase in this business's own terms, do not dump it verbatim):\n";
			foreach($kb as $k){
				$answer = mb_substr($k['answer'] ?? '', 0, 700);
				$p .= "### {$k['preview']}\n{$answer}\n";
			}
		}

		$role = $context['role'] ?? 'staff';
		$p .= "\nRules: reply with JSON only. Pick a capability ONLY if it is listed. "
			. "Never invent capabilities, prices, stock levels or customer data — if asked for data you cannot query, use the closest capability. "
			. "For how-to/usage questions, answer from the reference knowledge in friendly numbered steps, then offer ONE natural follow-up. "
			. "The user is a '{$role}': if their request needs owner/admin access they lack, say it's an admin task and suggest what they CAN do — never reveal admin details. "
			. "Keep 'answer' text under 120 words, plain text.";
		return $p;
	}

	private function _call($payload){
		$endpoint = $this->cfg['endpoint'] ?? '';
		$key      = $this->cfg['api_key'] ?? '';
		if($endpoint === '' || $key === '') return null;

		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer '.$key,
		];
		foreach(($this->cfg['headers'] ?? []) as $h => $v){
			$headers[] = $h.': '.$v;
		}

		$ch = curl_init($endpoint);
		curl_setopt_array($ch, [
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_POSTFIELDS     => json_encode($payload),
			CURLOPT_TIMEOUT        => (int)($this->cfg['timeout'] ?? 12),
			CURLOPT_CONNECTTIMEOUT => 5,
		]);
		$body = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err  = curl_error($ch);
		curl_close($ch);

		if($body === false || $code < 200 || $code >= 300){
			log_message('error', 'Assist_brain API call failed: HTTP '.$code.' '.$err);
			return null;
		}
		$res = json_decode($body, true);
		$content = $res['choices'][0]['message']['content'] ?? null;
		return is_string($content) ? trim($content) : null;
	}

	private function _normalize($raw){
		// Strip code fences if the model wraps its JSON
		$raw = preg_replace('/^```(?:json)?|```$/m', '', trim($raw));
		$data = json_decode($raw, true);
		if(!is_array($data) || empty($data['action'])) return null;

		switch($data['action']){
			case 'capability':
				$name = preg_replace('/[^a-z0-9_]/i', '', $data['name'] ?? '');
				if($name === '') return null;
				$entities = isset($data['entities']) && is_array($data['entities']) ? $data['entities'] : [];
				return ['action' => 'capability', 'name' => strtolower($name), 'entities' => $entities];

			case 'answer':
			case 'clarify':
				$text = trim((string)($data['text'] ?? ''));
				if($text === '') return null;
				return ['action' => $data['action'], 'text' => $text];

			case 'cancel':
				return ['action' => 'cancel'];
		}
		return null;
	}
}
