<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Return Invoice</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .card-title { font-size: 15px; font-weight: 700; margin-bottom: 10px; }
    .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .invoice-header .code { font-size: 13px; color: var(--mp-muted); }
    .invoice-header .status { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .status-paid { background: #D1FAE5; color: #065F46; }
    .status-partial { background: #FFFBEB; color: #B45309; }
    .status-unpaid { background: #FEF2F2; color: #DC2626; }
    .info-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
    .info-row .label { color: var(--mp-muted); }
    .info-block { margin-bottom: 12px; }
    .info-block:last-child { margin-bottom: 0; }
    .info-block .name { font-weight: 700; font-size: 14px; margin-bottom: 4px; }
    .info-block .detail { font-size: 13px; color: var(--mp-ink); line-height: 1.4; }
    .item-card { padding: 14px 0; border-bottom: 1px solid var(--mp-border); }
    .item-card:last-child { border-bottom: none; }
    .item-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
    .item-name { font-weight: 600; font-size: 14px; flex: 1; padding-right: 8px; }
    .item-qty { font-size: 12px; color: var(--mp-muted); }
    .item-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px; }
    .item-row .label { color: var(--mp-muted); }
    .total-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--mp-border); }
    .total-row:last-child { border-bottom: none; }
    .total-row.grand { font-size: 18px; font-weight: 700; color: var(--mp-primary); }
    .total-row .label { color: var(--mp-muted); }
    .payment-card { padding: 12px 0; border-bottom: 1px solid var(--mp-border); }
    .payment-card:last-child { border-bottom: none; }
    .payment-head { display: flex; justify-content: space-between; font-weight: 600; }
    .payment-meta { font-size: 12px; color: var(--mp-muted); }
    .actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .actions a { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 12px; border-radius: 12px; font-size: 13px; font-weight: 600; text-decoration: none; color: #fff; }
    .btn-edit { background: #10B981; }
    .btn-pos { background: #3B82F6; }
    .btn-pdf { background: #0057FF; }
    .btn-print { background: #B45309; }
    .btn-sale { background: #7C3AED; }
    .empty { text-align: center; padding: 24px; color: var(--mp-muted); font-size: 13px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 140px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/sales_returns'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Return Invoice</h1>
        </div>
      </div>

      <?php
        $return_id = (int)$return_id;
        $store_id = get_current_store_id();
        $CI =& get_instance();
        $is_cashier = (strpos(strtolower($CI->session->userdata('role_name') ?: ''), 'cashier') !== false);

        $q3 = $this->db->query("SELECT b.coupon_id,b.coupon_amt,b.store_id,b.sales_id,b.customer_id,COALESCE(a.customer_name,'Walk-in Customer') as customer_name,a.mobile,a.phone,a.gstin,a.tax_number,a.email,a.opening_balance,a.country_id,a.state_id,a.city,a.postcode,COALESCE(a.address,'') as address,b.return_date,b.created_time,b.reference_no,b.return_code,b.return_status,b.return_note,coalesce(b.grand_total,0) as grand_total,coalesce(b.subtotal,0) as subtotal,coalesce(b.paid_amount,0) as paid_amount,coalesce(b.other_charges_input,0) as other_charges_input,b.other_charges_tax_id,coalesce(b.other_charges_amt,0) as other_charges_amt,b.discount_to_all_input,b.discount_to_all_type,coalesce(b.tot_discount_to_all_amt,0) as tot_discount_to_all_amt,coalesce(b.round_off,0) as round_off,b.payment_status,b.pos FROM db_salesreturn b LEFT JOIN db_customers a ON a.id=b.customer_id WHERE b.id=? AND b.store_id=?", [$return_id, $store_id]);
        $res3 = $q3->row();
        if(empty($res3) || $res3->store_id != $store_id){
          echo '<div class="card"><div class="empty">Return invoice not found.</div></div>';
        } else {
          $sales_id = $res3->sales_id;
          $customer_id = $res3->customer_id;
          $customer_name = $res3->customer_name;
          $customer_mobile = $res3->mobile ?? '';
          $customer_phone = $res3->phone ?? '';
          $customer_email = $res3->email ?? '';
          $customer_country = $res3->country_id ? get_country($res3->country_id) : '';
          $customer_state = $res3->state_id ? get_state($res3->state_id) : '';
          $customer_city = $res3->city ?? '';
          $customer_address = $res3->address ?? '';
          $customer_postcode = $res3->postcode ?? '';
          $return_date = $res3->return_date;
          $reference_no = $res3->reference_no;
          $return_code = $res3->return_code;
          $return_status = $res3->return_status;
          $return_note = $res3->return_note;
          $pos = $res3->pos;

          $coupon_id = $res3->coupon_id;
          $coupon_amt = $res3->coupon_amt;
          $coupon_code = '';
          if(!empty($coupon_id)){
            $coupon_details = get_customer_coupon_details($coupon_id);
            $coupon_code = $coupon_details->code;
          }

          $sales_code = '';
          if(!empty($sales_id)){
            $sale_row = $this->db->select('sales_code')->where('id', $sales_id)->get('db_sales')->row();
            $sales_code = $sale_row ? $sale_row->sales_code : '';
          }

          $subtotal = $res3->subtotal;
          $grand_total = $res3->grand_total;
          $other_charges_amt = $res3->other_charges_amt;
          $paid_amount = $res3->paid_amount;
          $tot_discount_to_all_amt = $res3->tot_discount_to_all_amt;
          $round_off = $res3->round_off;
          $payment_status = $res3->payment_status;
      ?>

      <div class="card">
        <div class="invoice-header">
          <div>
            <div class="code">#<?= htmlspecialchars($return_code); ?></div>
            <div class="info-row" style="margin-top:6px;"><span class="label">Date</span><span><?= show_date($return_date); ?></span></div>
            <?php if($return_status): ?><div class="info-row"><span class="label">Status</span><span><?= htmlspecialchars($return_status); ?></span></div><?php endif; ?>
            <?php if($reference_no): ?><div class="info-row"><span class="label">Ref</span><span><?= htmlspecialchars($reference_no); ?></span></div><?php endif; ?>
            <?php if($sales_code): ?><div class="info-row"><span class="label">Against</span><span>#<?= htmlspecialchars($sales_code); ?></span></div><?php endif; ?>
          </div>
          <span class="status status-<?= strtolower($payment_status); ?>"><?= $payment_status; ?></span>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Customer</div>
        <div class="info-block">
          <div class="name"><?= htmlspecialchars($customer_name); ?></div>
          <div class="detail">
            <?= htmlspecialchars($customer_address); ?>
            <?php
              $addr = [];
              if($customer_city) $addr[] = $customer_city;
              if($customer_state) $addr[] = $customer_state;
              if($customer_country) $addr[] = $customer_country;
              if($customer_postcode) $addr[] = $customer_postcode;
              if(!empty($addr)) echo '<br>' . htmlspecialchars(implode(', ', $addr));
            ?>
          </div>
          <?php if($customer_mobile): ?><div class="detail"><i class="fa fa-phone"></i> <?= htmlspecialchars($customer_mobile); ?></div><?php endif; ?>
          <?php if($customer_email): ?><div class="detail"><i class="fa fa-envelope"></i> <?= htmlspecialchars($customer_email); ?></div><?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Returned Items</div>
        <?php
          $this->db->select("a.description,COALESCE(c.item_name, a.description, 'Unknown Item') as item_name,a.return_qty,a.tax_type,a.price_per_unit,b.tax,b.tax_name,a.tax_amt,a.discount_input,a.discount_amt,a.unit_total_cost,a.total_cost,c.sku,c.hsn");
          $this->db->where("a.return_id", $return_id);
          $this->db->from("db_salesitemsreturn a");
          $this->db->join("db_tax b", "b.id=a.tax_id", "left");
          $this->db->join("db_items c", "c.id=a.item_id", "left");
          $q2 = $this->db->get();

          $tot_qty = $tot_tax_amt = $tot_discount_amt = $tot_total_cost = 0;
          foreach($q2->result() as $res2):
            $tax_str = ($res2->tax_type == 'Inclusive') ? 'Inc.' : 'Exc.';
            $price_per_unit = $res2->price_per_unit;
            if($res2->tax_type == 'Inclusive' && $res2->return_qty){
              $price_per_unit -= ($res2->tax_amt / $res2->return_qty);
            }
            $tot_price = $price_per_unit * $res2->return_qty;
            $tot_qty += $res2->return_qty;
            $tot_tax_amt += $res2->tax_amt;
            $tot_discount_amt += $res2->discount_amt;
            $tot_total_cost += $res2->total_cost;
        ?>
          <div class="item-card">
            <div class="item-top">
              <span class="item-name"><?= htmlspecialchars($res2->item_name); ?></span>
              <span class="item-qty"><?= format_qty($res2->return_qty); ?></span>
            </div>
            <?php if($res2->description): ?><div style="font-size:12px;color:var(--mp-muted);margin-bottom:6px;"><?= nl2br(htmlspecialchars($res2->description)); ?></div><?php endif; ?>
            <div class="item-row"><span class="label">Unit</span><span><?= store_number_format($price_per_unit); ?></span></div>
            <div class="item-row"><span class="label">Price</span><span><?= store_number_format($tot_price); ?></span></div>
            <div class="item-row"><span class="label">Tax</span><span><?= store_number_format($res2->tax); ?>% <?= htmlspecialchars($res2->tax_name); ?> [<?= $tax_str; ?>]</span></div>
            <div class="item-row"><span class="label">Tax Amt</span><span><?= store_number_format($res2->tax_amt); ?></span></div>
            <div class="item-row"><span class="label">Discount</span><span><?= (empty($res2->discount_input) || $res2->discount_input == 0) ? '0' : store_number_format($res2->discount_input) . '%'; ?></span></div>
            <div class="item-row"><span class="label">Total</span><span><?= store_number_format($res2->total_cost); ?></span></div>
          </div>
        <?php endforeach; ?>
        <?php if($q2->num_rows() == 0): ?>
          <div class="empty">No returned items.</div>
        <?php endif; ?>
      </div>

      <div class="card">
        <div class="card-title">Summary</div>
        <div class="total-row"><span class="label">Subtotal</span><span><?= store_number_format($subtotal); ?></span></div>
        <?php if($other_charges_amt): ?><div class="total-row"><span class="label">Other Charges</span><span><?= store_number_format($other_charges_amt); ?></span></div><?php endif; ?>
        <?php if($coupon_amt): ?><div class="total-row"><span class="label">Coupon <?= htmlspecialchars(getTruncatedCCNumber($coupon_code)); ?></span><span><?= store_number_format($coupon_amt); ?></span></div><?php endif; ?>
        <?php if($tot_discount_to_all_amt): ?><div class="total-row"><span class="label">Discount</span><span><?= store_number_format($tot_discount_to_all_amt); ?></span></div><?php endif; ?>
        <div class="total-row"><span class="label">Round Off</span><span><?= store_number_format($round_off); ?></span></div>
        <div class="total-row grand"><span>Refunded</span><span><?= store_number_format($paid_amount); ?></span></div>
        <div class="total-row grand" style="color:var(--mp-ink);"><span>Grand Total</span><span><?= store_number_format($grand_total); ?></span></div>
      </div>

      <div class="card">
        <div class="card-title">Refund Payments</div>
        <?php
          $q3p = $this->db->where('return_id', $return_id)->get('db_salespaymentsreturn');
          if($q3p->num_rows() > 0):
            $total_paid = 0;
            foreach($q3p->result() as $res3p):
              $total_paid += $res3p->payment;
        ?>
          <div class="payment-card">
            <div class="payment-head"><span><?= show_date($res3p->payment_date); ?></span><span><?= store_number_format($res3p->payment); ?></span></div>
            <div class="payment-meta"><?= htmlspecialchars(payment_mode_label($res3p->payment_type)); ?> &middot; <?= htmlspecialchars(get_account_name($res3p->account_id)); ?><?php if(!empty($res3p->payment_note)): ?> &middot; <?= htmlspecialchars($res3p->payment_note); ?><?php endif; ?></div>
          </div>
        <?php endforeach; ?>
          <div class="total-row grand" style="border-top:1px solid var(--mp-border); margin-top:8px; padding-top:12px;"><span>Total Refunded</span><span><?= store_number_format($total_paid); ?></span></div>
        <?php else: ?>
          <div class="empty">No refunds recorded.</div>
        <?php endif; ?>
      </div>

      <?php if($return_note): ?>
      <div class="card">
        <div class="info-block"><div class="name">Note</div><div class="detail"><?= nl2br(htmlspecialchars($return_note)); ?></div></div>
      </div>
      <?php endif; ?>

      <?php if(!$is_cashier): ?>
      <div class="actions">
        <?php if($CI->permissions('sales_return_edit')): ?>
          <a href="<?= base_url('mobile/sales_return_edit/' . $return_id); ?>" class="btn-edit"><i class="fa fa-edit"></i> Edit</a>
        <?php endif; ?>
        <a href="<?= base_url('sales_return/print_invoice_pos/' . $return_id); ?>" target="_blank" class="btn-pos"><i class="fa fa-file-text"></i> POS</a>
        <a href="<?= base_url('sales_return/pdf/' . $return_id); ?>" target="_blank" class="btn-pdf"><i class="fa fa-file-pdf-o"></i> PDF</a>
        <a href="<?= base_url('sales_return/print_invoice/' . $return_id); ?>" target="_blank" class="btn-print"><i class="fa fa-print"></i> Print</a>
        <?php if(!empty($sales_id)): ?>
          <a href="<?= base_url('mobile/sales_invoice/' . $sales_id); ?>" class="btn-sale" style="grid-column: span 2;"><i class="fa fa-file-text-o"></i> View Original Sale <?= $sales_code ? '#' . htmlspecialchars($sales_code) : ''; ?></a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php } ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
