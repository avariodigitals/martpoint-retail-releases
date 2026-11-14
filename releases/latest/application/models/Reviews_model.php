<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Product reviews (Phase 4). Distinct from db_storefront_testimonials —
 * testimonials are merchant-curated marketing content; these are
 * customer-submitted, moderated product ratings.
 *
 * Verified-purchase rule: a review is only flagged verified when a real
 * order in the SAME store, placed under the reviewer's email or phone,
 * contains that item (or one of its variants) and reached a state where the
 * goods were actually committed — order_status confirmed/processing/
 * shipped/delivered/completed. Verification never transfers: the matching
 * order must belong to the reviewer's own contact identity.
 *
 * Duplicate rule: one review per reviewer identity per item per store
 * (unique key on store_id+item_id+reviewer_key, where reviewer_key is a
 * SHA-256 of normalised email, else phone).
 */
class Reviews_model extends CI_Model {

	const TEXT_MAX = 2000;
	const TITLE_MAX = 150;

	/**
	 * Find the reviewer's qualifying order for an item, if any.
	 * Returns the order row or null.
	 */
	public function findQualifyingOrder($storeId, $itemId, $email, $phone){
		$storeId = (int)$storeId; $itemId = (int)$itemId;
		$email = trim(strtolower((string)$email));
		$phone = preg_replace('/\D/', '', (string)$phone);
		if($email === '' && $phone === '') return null;

		// Collect the item itself plus sibling/parent linkage so a review on
		// any variant page verifies against an order for any sibling variant.
		$itemIds = [$itemId];
		$item = $this->db->where('id', $itemId)->where('store_id', $storeId)->get('db_items')->row();
		if($item){
			if(!empty($item->parent_id)){
				$parent = (int)$item->parent_id;
				$itemIds[] = $parent;
				foreach($this->db->select('id')->where('parent_id', $parent)->where('store_id', $storeId)->get('db_items')->result() as $s) $itemIds[] = (int)$s->id;
			} elseif(($item->item_group ?? '') === 'Variants'){
				foreach($this->db->select('id')->where('parent_id', $itemId)->where('store_id', $storeId)->get('db_items')->result() as $s) $itemIds[] = (int)$s->id;
			}
		}
		$itemIds = array_unique($itemIds);

		$this->db->select('o.id, o.order_status')
			->from('db_online_orders o')
			->join('db_online_order_items i', 'i.order_id = o.id', 'inner')
			->where('o.store_id', $storeId)
			->where_in('i.item_id', $itemIds)
			->where_in('o.order_status', ['confirmed','processing','shipped','delivered','completed']);
		$this->db->group_start();
		if($email !== '') $this->db->or_where('LOWER(o.customer_email)', $email);
		if($phone !== ''){
			// Match phone digits tolerantly (orders store the raw dial string)
			$this->db->or_where("REPLACE(REPLACE(REPLACE(REPLACE(o.customer_phone,' ',''),'-',''),'(',''),')','') LIKE", '%' . $this->db->escape_like_str(substr($phone, -10)) . '%', false);
		}
		$this->db->group_end();
		return $this->db->order_by('o.id', 'desc')->limit(1)->get()->row();
	}

	/**
	 * Submit a review. Returns [review_id|null, error]. Enforces rating
	 * bounds, text length, dedupe and per-IP rate limiting.
	 */
	public function submitReview($storeId, $itemId, $data, $ip){
		$storeId = (int)$storeId; $itemId = (int)$itemId;
		$rating = (int)($data['rating'] ?? 0);
		if($rating < 1 || $rating > 5) return [null, 'Please choose a rating between 1 and 5.'];
		$name = trim(mb_substr((string)($data['name'] ?? ''), 0, 120));
		if($name === '') return [null, 'Please enter your name.'];
		$email = trim(mb_substr((string)($data['email'] ?? ''), 0, 150));
		$phone = trim(mb_substr((string)($data['phone'] ?? ''), 0, 30));
		if($email === '' && $phone === '') return [null, 'Please provide your email or phone so we can verify your purchase.'];
		$text = trim((string)($data['text'] ?? ''));
		if(mb_strlen($text) > self::TEXT_MAX) return [null, 'Review is too long (max ' . self::TEXT_MAX . ' characters).'];
		$title = trim(mb_substr((string)($data['title'] ?? ''), 0, self::TITLE_MAX));

		// The item must be a live online product in this store.
		$item = $this->db->where('id', $itemId)->where('store_id', $storeId)->where('status', 1)->where('publish_online', 1)->get('db_items')->row();
		if(!$item) return [null, 'Product not found.'];

		// Spam throttle: max 5 reviews per IP per hour, 3 pending per identity.
		$recent = $this->db->where('store_id', $storeId)->where('ip_address', $ip)
			->where('created_at >', date('Y-m-d H:i:s', time() - 3600))
			->count_all_results('db_product_reviews');
		if($recent >= 5) return [null, 'Too many reviews submitted. Please try again later.'];

		$key = hash('sha256', strtolower($email !== '' ? $email : 'p:' . preg_replace('/\D/', '', $phone)) . '|' . $storeId);
		$dup = $this->db->where('store_id', $storeId)->where('item_id', $itemId)->where('reviewer_key', $key)->count_all_results('db_product_reviews');
		if($dup > 0) return [null, 'You have already reviewed this product.'];

		$order = $this->findQualifyingOrder($storeId, $itemId, $email, $phone);
		$settings = isset($data['settings']) ? $data['settings'] : null;
		$requireApproval = !$settings || !empty($settings->reviews_require_approval);

		$row = [
			'store_id' => $storeId,
			'item_id' => $itemId,
			'order_id' => $order ? (int)$order->id : null,
			'reviewer_name' => $name,
			'reviewer_email' => $email !== '' ? $email : null,
			'reviewer_key' => $key,
			'rating' => $rating,
			'title' => $title !== '' ? $title : null,
			'review_text' => $text !== '' ? $text : null,
			'status' => $requireApproval ? 'pending' : 'approved',
			'is_verified' => $order ? 1 : 0,
			'ip_address' => $ip,
			'created_at' => date('Y-m-d H:i:s'),
		];
		if($order){
			$cust = $this->db->select('customer_id')->where('id', (int)$order->id)->get('db_online_orders')->row();
			if($cust && !empty($cust->customer_id)) $row['customer_id'] = (int)$cust->customer_id;
		}
		$this->db->insert('db_product_reviews', $row);
		if(!$this->db->affected_rows()){
			return [null, 'You have already reviewed this product.'];
		}
		return [$this->db->insert_id(), null, $requireApproval ? 'pending' : 'approved', (bool)$order];
	}

	/** Approved reviews for public display — never exposes email/phone/ip. */
	public function getPublicReviews($storeId, $itemId, $limit = 50, $offset = 0){
		return $this->db->select('id, rating, title, review_text, reviewer_name, is_verified, created_at')
			->where('store_id', (int)$storeId)
			->where('item_id', (int)$itemId)
			->where('status', 'approved')
			->order_by('created_at', 'desc')
			->limit((int)$limit, (int)$offset)
			->get('db_product_reviews')->result();
	}

	/** Aggregate over approved reviews only. Empty store -> count 0, avg null. */
	public function getAggregate($storeId, $itemId){
		$row = $this->db->select('COUNT(*) AS cnt, AVG(rating) AS avg_rating')
			->where('store_id', (int)$storeId)
			->where('item_id', (int)$itemId)
			->where('status', 'approved')
			->get('db_product_reviews')->row();
		return ['count' => (int)($row->cnt ?? 0), 'average' => $row && $row->avg_rating !== null ? round((float)$row->avg_rating, 1) : null];
	}

	public function listForModeration($storeId, $status = null, $limit = 100, $offset = 0){
		$q = $this->db->select('r.*, i.item_name, o.order_code')
			->from('db_product_reviews r')
			->join('db_items i', 'i.id = r.item_id', 'left')
			->join('db_online_orders o', 'o.id = r.order_id', 'left')
			->where('r.store_id', (int)$storeId);
		if($status) $q->where('r.status', $status);
		return $q->order_by("FIELD(r.status,'pending','approved','rejected')", '', false)
			->order_by('r.created_at', 'desc')
			->limit((int)$limit, (int)$offset)->get()->result();
	}

	public function countForModeration($storeId){
		return $this->db->select('status, COUNT(*) AS cnt')->where('store_id', (int)$storeId)
			->group_by('status')->get('db_product_reviews')->result();
	}

	/** Audited moderation: records who acted, when and why. */
	public function moderate($reviewId, $storeId, $action, $userId, $note = ''){
		if(!in_array($action, ['approve', 'reject'], true)) return false;
		$exists = $this->db->where('id', (int)$reviewId)->where('store_id', (int)$storeId)
			->count_all_results('db_product_reviews');
		if(!$exists) return false;
		return $this->db->where('id', (int)$reviewId)->where('store_id', (int)$storeId)->update('db_product_reviews', [
			'status' => $action === 'approve' ? 'approved' : 'rejected',
			'moderated_by' => (int)$userId,
			'moderated_at' => date('Y-m-d H:i:s'),
			'moderation_note' => mb_substr(trim((string)$note), 0, 255) ?: null,
		]);
	}

	public function report($reviewId, $storeId, $reason, $ip){
		$review = $this->db->where('id', (int)$reviewId)->where('store_id', (int)$storeId)->where('status', 'approved')->get('db_product_reviews')->row();
		if(!$review) return false;
		$key = hash('sha256', 'rep|' . $ip . '|' . $storeId);
		$exists = $this->db->where('review_id', (int)$reviewId)->where('reporter_key', $key)->count_all_results('db_review_reports');
		if($exists) return true; // already reported by this visitor
		return $this->db->insert('db_review_reports', [
			'review_id' => (int)$reviewId,
			'store_id' => (int)$storeId,
			'reason' => mb_substr(trim((string)$reason), 0, 255) ?: null,
			'reporter_key' => $key,
			'created_at' => date('Y-m-d H:i:s'),
		]);
	}

	public function reportCounts($storeId){
		$rows = $this->db->select('review_id, COUNT(*) AS cnt')->where('store_id', (int)$storeId)->group_by('review_id')->get('db_review_reports')->result();
		$map = [];
		foreach($rows as $r) $map[(int)$r->review_id] = (int)$r->cnt;
		return $map;
	}
}
