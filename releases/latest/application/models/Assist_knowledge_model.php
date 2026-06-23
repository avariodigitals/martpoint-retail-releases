<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MartPoint Assist Knowledge Base
 * Role-aware KB that filters answers based on user role level
 */
class Assist_knowledge_model extends CI_Model {

	// Role levels (hierarchical)
	const ROLE_ALL = 'all';
	const ROLE_CASHIER = 'cashier';
	const ROLE_BUSINESS_OWNER = 'business_owner';
	const ROLE_ADMIN = 'admin';

	public $roleHierarchy = [
		'all' => 0,
		'cashier' => 1,
		'business_owner' => 2,
		'admin' => 3,
	];

	/** @var array Knowledge base topics loaded from JSON */
	private $knowledgeBase = [];

	public function __construct(){
		parent::__construct();
		$this->load->database();
		$this->_loadKnowledgeBase();
	}

	/**
	 * Load knowledge base from JSON file
	 */
	private function _loadKnowledgeBase(){
		$kbFile = APPPATH . 'config/assist_kb.json';
		if(file_exists($kbFile)){
			$json = file_get_contents($kbFile);
			$this->knowledgeBase = json_decode($json, true) ?: [];
		}
	}

	/**
	 * Get current user's role level
	 * @return string One of ROLE_ALL, ROLE_CASHIER, ROLE_BUSINESS_OWNER, ROLE_ADMIN
	 */
	public function getUserRoleLevel(){
		$userId = $this->session->userdata('inv_userid');
		$roleName = strtolower(trim($this->session->userdata('role_name') ?: ''));

		if($userId == 1 || $roleName === 'admin'){
			return self::ROLE_ADMIN;
		}
		if(is_store_admin()){
			return self::ROLE_BUSINESS_OWNER;
		}
		if(stripos($roleName, 'cashier') !== false){
			return self::ROLE_CASHIER;
		}
		return self::ROLE_ALL;
	}

	/**
	 * Check if user meets minimum role requirement
	 * @param string $requiredRole
	 * @return bool
	 */
	public function hasAccess($requiredRole){
		$userLevel = $this->roleHierarchy[$this->getUserRoleLevel()] ?? 0;
		$requiredLevel = $this->roleHierarchy[$requiredRole] ?? 0;
		return $userLevel >= $requiredLevel;
	}

	/**
	 * Search knowledge base for matching topics
	 * @param string $message User query
	 * @return array|null Matching KB entry or null
	 */
	public function search($message){
		$message = strtolower(trim($message));
		$userRole = $this->getUserRoleLevel();
		$userLevel = $this->roleHierarchy[$userRole] ?? 0;

		$bestMatch = null;
		$bestScore = 0;

		foreach($this->knowledgeBase as $entry){
			$requiredLevel = $this->roleHierarchy[$entry['required_role']] ?? 0;
			if($userLevel < $requiredLevel) continue; // Skip restricted topics

			// Score against keywords
			$score = $this->_scoreMatch($message, $entry['keywords'] ?? []);

			// Also score against preview and answer text (lower weight)
			$haystack = strtolower(($entry['preview'] ?? '') . ' ' . strip_tags($entry['answer'] ?? '') . ' ' . ($entry['category'] ?? ''));
			if(strpos($haystack, $message) !== false){
				$score += strlen($message);
			}

			if($score > $bestScore){
				$bestScore = $score;
				$bestMatch = $entry;
			}
		}

		return ($bestScore > 0) ? $bestMatch : null;
	}

	/**
	 * Get a single topic by ID (with role check)
	 * @param string $id Topic ID
	 * @return array|null
	 */
	public function getTopicById($id){
		$userRole = $this->getUserRoleLevel();
		$userLevel = $this->roleHierarchy[$userRole] ?? 0;
		foreach($this->knowledgeBase as $entry){
			if($entry['id'] === $id){
				$requiredLevel = $this->roleHierarchy[$entry['required_role']] ?? 0;
				if($userLevel < $requiredLevel) return null;
				return $entry;
			}
		}
		return null;
	}

	/**
	 * Search topics with fuzzy/partial matching for autocomplete
	 * @param string $query Partial user input
	 * @param int $limit Max results
	 * @return array Matching topics with score
	 */
	public function searchTopics($query, $limit = 6){
		$userRole = $this->getUserRoleLevel();
		$userLevel = $this->roleHierarchy[$userRole] ?? 0;
		$query = strtolower(trim($query));
		$results = [];

		foreach($this->knowledgeBase as $entry){
			$requiredLevel = $this->roleHierarchy[$entry['required_role']] ?? 0;
			if($userLevel < $requiredLevel) continue;

			$score = 0;
			$haystack = strtolower($entry['answer'] . ' ' . ($entry['preview'] ?? '') . ' ' . ($entry['category'] ?? ''));
			$keywords = array_map('strtolower', $entry['keywords'] ?? []);

			if($query === ''){
				$score = 1; // Return all accessible topics when empty
			} else {
				// Exact keyword match scores highest
				foreach($keywords as $kw){
					if(strpos($kw, $query) !== false) $score += 10;
					if($query === $kw) $score += 20;
				}
				// Partial match in preview/answer/category
				if(strpos($haystack, $query) !== false) $score += 5;
				// Word-boundary match in keywords
				foreach($keywords as $kw){
					$words = preg_split('/\s+/', $kw);
					foreach($words as $w){
						if(strpos($w, $query) === 0) $score += 3;
					}
				}
			}

			if($score > 0){
				$results[] = [
					'id' => $entry['id'],
					'preview' => $entry['preview'] ?? substr(strip_tags($entry['answer']), 0, 60) . '...',
					'category' => $entry['category'] ?? 'general',
					'keywords' => $entry['keywords'] ?? [],
					'score' => $score
				];
			}
		}

		usort($results, function($a, $b){ return $b['score'] <=> $a['score']; });
		return array_slice($results, 0, $limit);
	}

	/**
	 * Get a list of available topics for current user's role
	 * @return array List of topic IDs and titles
	 */
	public function getAvailableTopics(){
		$userRole = $this->getUserRoleLevel();
		$userLevel = $this->roleHierarchy[$userRole] ?? 0;
		$topics = [];

		foreach($this->knowledgeBase as $entry){
			$requiredLevel = $this->roleHierarchy[$entry['required_role']] ?? 0;
			if($userLevel >= $requiredLevel){
				$topics[] = [
					'id' => $entry['id'],
					'category' => $entry['category'] ?? 'general',
					'preview' => $entry['preview'] ?? substr($entry['answer'], 0, 60) . '...'
				];
			}
		}
		return $topics;
	}

	/**
	 * Score how well the message matches the keywords
	 * Fuzzy: checks full substring, word overlap, and individual word matches
	 */
	private function _scoreMatch($message, $keywords){
		$msgWords = array_filter(preg_split('/\s+/', $message));
		$score = 0;
		foreach($keywords as $kw){
			$kw = strtolower(trim($kw));
			if(empty($kw)) continue;

			// Full substring match (highest score)
			if(strpos($message, $kw) !== false){
				$score += strlen($kw) * 2;
				continue;
			}

			// Keyword contained in message
			if(strpos($kw, $message) !== false){
				$score += strlen($message);
				continue;
			}

			// Word overlap scoring
			$kwWords = array_filter(preg_split('/\s+/', $kw));
			$overlap = 0;
			foreach($kwWords as $w){
				if(strlen($w) < 3) continue; // Skip tiny words
				foreach($msgWords as $mw){
					if($w === $mw){
						$overlap += strlen($w);
					} elseif(strpos($mw, $w) === 0 || strpos($w, $mw) === 0){
						$overlap += strlen($mw);
					}
				}
			}
			if($overlap > 0){
				$score += $overlap;
			}
		}
		return $score;
	}

	/**
	 * Get the unauthorized message for a role
	 */
	public function getUnauthorizedMessage(){
		return "I'm not able to share that information based on your current access level. Please contact your business owner or administrator for assistance with this topic.";
	}
}
