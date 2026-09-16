<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Omni Channel Controller
 * Handles social/messaging sales entry points without paid APIs.
 * Currently supports a zero-cost WhatsApp hand-off using wa.me links.
 */
class Omni extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('storefront_model');
	}

	/**
	 * Create a WhatsApp draft order for a single product and return a wa.me link.
	 *
	 * POST: store_id, product_id, qty
	 * JSON: { status, wa_url, order_id } | { status, message }
	 */
	public function wa_draft(){
		$storeId = (int)$this->input->post('store_id', TRUE);
		$productId = (int)$this->input->post('product_id', TRUE);
		$qty = max(1, (int)$this->input->post('qty', TRUE));

		if(!$storeId || !$productId){
			return $this->_json(['status' => false, 'message' => 'Invalid request.']);
		}

		$settings = $this->storefront_model->getSettings($storeId);
		$phone = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
		if(!$phone){
			return $this->_json(['status' => false, 'message' => 'WhatsApp number not configured.']);
		}
		if(empty($settings->allow_whatsapp)){
			return $this->_json(['status' => false, 'message' => 'WhatsApp orders are disabled in store settings.']);
		}

		$product = $this->storefront_model->getOnlineProduct($productId, $storeId);
		if(!$product){
			return $this->_json(['status' => false, 'message' => 'Product not found.']);
		}

		if((int)$product->stock < $qty && empty($settings->allow_backorder)){
			return $this->_json(['status' => false, 'message' => 'Not enough stock.']);
		}

		$price = (float)$this->storefront_model->getProductEffectivePrice($product);
		$subtotal = round($price * $qty, 2);
		$token = bin2hex(random_bytes(16));

		$orderData = [
			'store_id'            => $storeId,
			'customer_name'       => 'WhatsApp Customer',
			'customer_email'      => '',
			'customer_phone'      => '',
			'customer_address'    => '',
			'order_type'          => 'product',
			'payment_method'      => 'whatsapp',
			'shipping_method'     => null,
			'delivery_fee'        => 0,
			'subtotal'            => $subtotal,
			'grand_total'         => $subtotal,
			'order_status'        => 'pending',
			'payment_status'      => 'unpaid',
			'source_channel'      => 'whatsapp',
			'channel_user_id'     => '',
			'confirmation_token'  => $token,
			'token_expires_at'    => date('Y-m-d H:i:s', strtotime('+15 minutes')),
			'stock_adjusted'      => 0,
		];

		$orderId = $this->storefront_model->createOrder($orderData);
		if(!$orderId){
			return $this->_json(['status' => false, 'message' => 'Could not create order.']);
		}

		$this->storefront_model->addOrderItem([
			'order_id'     => $orderId,
			'item_type'    => 'product',
			'item_id'      => $productId,
			'item_name'    => $product->item_name,
			'item_image'   => $product->item_image,
			'qty'          => $qty,
			'unit_price'   => $price,
			'total_price'  => $subtotal,
			'service_note' => ''
		]);

		$store = get_store_details($storeId);
		$confirmUrl = base_url('omni/wa_confirm/' . $token);
		$msg  = "Hello " . ($store->store_name ?? 'Store') . ",\n\n";
		$msg .= "I want to order:\n";
		$msg .= (int)$qty . " x " . $product->item_name . " — " . store_number_format($subtotal) . "\n\n";
		$msg .= "Order code: " . $token . "\n";
		$msg .= "Confirm: " . $confirmUrl . "\n\n";
		$msg .= "Thank you.";

		$waUrl = "https://wa.me/" . $phone . "?text=" . urlencode($msg);

		return $this->_json([
			'status'    => true,
			'wa_url'    => $waUrl,
			'order_id'  => $orderId,
			'order_code'=> $this->storefront_model->getOrder($orderId)->order_code ?? ''
		]);
	}

	/**
	 * Public confirm link for the merchant.
	 * Validates the token and sends the merchant to the order detail page.
	 */
	public function wa_confirm($token = ''){
		$token = preg_replace('/[^a-f0-9]/', '', $token);
		if(!$token){
			show_404();
			return;
		}

		$order = $this->db
				->where('confirmation_token', $token)
				->where('token_expires_at >=', date('Y-m-d H:i:s'))
				->get('db_online_orders')
				->row();

		if(!$order){
			show_404();
			return;
		}

		redirect('online_store/order/' . (int)$order->id, 'location');
	}

	private function _json($data){
		header('Content-Type: application/json');
		echo json_encode($data);
	}
}
