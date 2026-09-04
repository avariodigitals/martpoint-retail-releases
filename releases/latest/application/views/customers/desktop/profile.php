<?php $this->load->view('customers/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.id-card-fit {
  position: relative;
  width: 100%;
  max-width: 324px;
  margin: 12px auto;
  overflow: hidden;
}
.id-card-fit .id-card-preview {
  position: absolute;
  top: 0;
  left: 50%;
  margin: 0;
  transform-origin: top center;
}
.id-card-preview {
  width: 324px;
  height: 204px;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  margin: 20px auto;
  background: linear-gradient(135deg, #fdfbf7 0%, #f5f0e8 100%);
  border: 1px solid #e0d5c5;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}
.id-card-brand {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 42px;
  font-weight: 900;
  color: #8b7355;
  opacity: 0.06;
  letter-spacing: 2px;
  text-transform: uppercase;
  white-space: nowrap;
  pointer-events: none;
  user-select: none;
}
.id-card-barcode { text-align: center; margin-top: 8px; }
.id-card-barcode img { height: 42px; }
.id-card-body { padding: 20px 16px 16px 16px; text-align: center; }
.id-card-name { font-size: 18px; font-weight: 700; color: #3d3229; margin-bottom: 4px; }
.id-card-phone { font-size: 14px; color: #6b5b4f; margin-bottom: 8px; }
.id-card-id { font-size: 11px; color: #9e8e7e; letter-spacing: 2px; font-family: monospace; }
.id-card-footer {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 8px 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255,255,255,0.5);
  border-top: 1px solid rgba(0,0,0,0.05);
}
.id-card-signature { font-size: 10px; color: #8b7355; font-style: italic; }
.id-card-expiry { font-size: 10px; color: #3d3229; font-weight: 600; }
</style>

<div class="mp-page-head">
  <div>
    <h2><i class="fa fa-user-circle"></i> <?= htmlspecialchars($customer->customer_name ?? 'Customer Profile'); ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($customer->customer_code ?? ''); ?></div>
  </div>
</div>

<input type="hidden" id="base_url" value="<?= base_url(); ?>">

<div class="mp-quick-actions">
  <?php if ($CI->permissions('customers_edit')): ?>
    <a href="<?= base_url('customers/update/' . $customer->id); ?>" class="mp-qa-btn blue"><i class="fa fa-edit"></i> Edit Customer</a>
  <?php endif; ?>
  <a href="<?= base_url('customers/statement/' . $customer->id); ?>" class="mp-qa-btn green"><i class="fa fa-file-text-o"></i> View Statement</a>
  <?php if ($CI->permissions('sales_payment_add')): ?>
    <a href="javascript:void(0);" onclick="pay_now(<?= $customer->id; ?>)" class="mp-qa-btn orange"><i class="fa fa-money"></i> Receive Payment</a>
  <?php endif; ?>
  <a href="<?= base_url('customers'); ?>" class="mp-qa-btn teal"><i class="fa fa-arrow-left"></i> Back to Customers</a>
</div>

<div class="pay_now_modal"></div>

<div class="mp-row" style="grid-template-columns:320px 1fr!important;align-items:start;">
  <!-- Left Sidebar -->
  <div>
    <div class="mp-card">
      <div class="mp-card-body">
        <h3 style="margin:0 0 4px;font-size:18px;font-weight:700;"><?= htmlspecialchars($customer->customer_name ?? ''); ?></h3>
        <p style="margin:0 0 12px;color:var(--mp-muted);"><?= htmlspecialchars($customer->customer_code ?? ''); ?></p>
        <table class="mp-tbl" style="margin-bottom:12px;">
          <tbody>
            <tr><td style="padding-left:0;">Phone</td><td style="text-align:right;font-weight:600;"><?= htmlspecialchars($customer->mobile ?? ''); ?></td></tr>
            <tr><td style="padding-left:0;">Email</td><td style="text-align:right;font-weight:600;"><?= htmlspecialchars($customer->email ?: '-'); ?></td></tr>
            <tr><td style="padding-left:0;">Tier</td><td style="text-align:right;"><span class="mp-pill paid"><?= htmlspecialchars($customer->loyalty_tier ?: 'Bronze'); ?></span></td></tr>
            <tr><td style="padding-left:0;">Loyalty Points</td><td style="text-align:right;font-weight:600;"><?= number_format($customer->loyalty_points ?? 0, 0); ?></td></tr>
            <tr><td style="padding-left:0;">Store Credit</td><td style="text-align:right;font-weight:600;"><?= store_number_format($customer->store_credit_balance ?? 0); ?></td></tr>
            <tr><td style="padding-left:0;">Gift Card Bal</td><td style="text-align:right;font-weight:600;"><?= store_number_format($customer->gift_card_balance ?? 0); ?></td></tr>
            <tr>
              <td style="padding-left:0;">Membership</td>
              <td style="text-align:right;">
                <?php if ($active_membership): ?>
                  <span class="mp-pill paid"><?= htmlspecialchars($active_membership->plan_name); ?> (<?= $active_membership->discount_percent; ?>% OFF)</span>
                <?php else: ?>
                  <span class="mp-pill muted">None</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr><td style="padding-left:0;">Total Due</td><td style="text-align:right;font-weight:600;color:var(--mp-danger);"><?= store_number_format($total_due ?? 0); ?></td></tr>
          </tbody>
        </table>

        <div class="id-card-fit" style="margin:12px auto 16px;">
          <div class="id-card-preview" id="idCardPreview">
            <div class="id-card-body">
              <div class="id-card-name"><?= htmlspecialchars($customer->customer_name ?? ''); ?></div>
              <div class="id-card-phone"><?= htmlspecialchars($customer->mobile ?? ''); ?></div>
              <div class="id-card-id">ID: <?= str_pad($customer->id, 6, '0', STR_PAD_LEFT); ?></div>
              <div class="id-card-barcode">
                <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=C<?= $customer->id; ?>&scale=2&height=8" alt="barcode">
              </div>
            </div>
            <div class="id-card-brand"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
            <div class="id-card-footer">
              <div class="id-card-signature"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
              <div class="id-card-expiry"><?= date('M Y'); ?></div>
            </div>
          </div>
        </div>
        <div class="text-center no-print">
          <button class="mp-btn-secondary" style="padding:8px 14px;font-size:13px;" onclick="printCard('idCardPreview')"><i class="fa fa-print"></i> Print</button>
          <button class="mp-btn-secondary" style="padding:8px 14px;font-size:13px;" onclick="downloadCard('idCardPreview','id-card-<?= str_pad($customer->id,6,'0',STR_PAD_LEFT); ?>.png')"><i class="fa fa-download"></i> PNG</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Main -->
  <div>
    <div class="mp-card">
      <ul class="nav nav-tabs" role="tablist">
        <li class="active"><a href="#purchases" data-toggle="tab"><i class="fa fa-shopping-cart"></i> Purchase History</a></li>
        <li><a href="#payments" data-toggle="tab"><i class="fa fa-money"></i> Statements</a></li>
        <?php if (!empty($service_history)): ?>
        <li><a href="#services" data-toggle="tab"><i class="fa fa-inbox"></i> Service History</a></li>
        <?php endif; ?>
        <li><a href="#giftcards" data-toggle="tab"><i class="fa fa-ticket"></i> Gift Cards</a></li>
        <li><a href="#storecredit" data-toggle="tab"><i class="fa fa-credit-card"></i> Store Credit</a></li>
        <li><a href="#coupons" data-toggle="tab"><i class="fa fa-tags"></i> Coupons</a></li>
        <li><a href="#memberships" data-toggle="tab"><i class="fa fa-id-card"></i> Memberships</a></li>
        <li><a href="#notes" data-toggle="tab"><i class="fa fa-sticky-note"></i> Customer Notes</a></li>
        <li><a href="#treatment_notes" data-toggle="tab"><i class="fa fa-file-text-o"></i> Treatment Notes</a></li>
        <?php if (!empty($medical_notes)): ?>
        <li><a href="#medical_notes" data-toggle="tab"><i class="fa fa-file-medical-o"></i> Medical Notes</a></li>
        <?php endif; ?>
        <li><a href="#custom_orders" data-toggle="tab"><i class="fa fa-pencil-square-o"></i> Custom Orders</a></li>
        <li><a href="#idcard" data-toggle="tab"><i class="fa fa-id-card"></i> ID Card</a></li>
      </ul>

      <div class="tab-content">
        <!-- Purchase History -->
        <div class="tab-pane active" id="purchases" style="padding:20px;">
          <table class="mp-static-table" id="purchaseTable">
            <thead>
              <tr><th>#</th><th>Date</th><th>Invoice</th><th>Amount</th><th>Paid</th><th>Due</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php if (!empty($purchases)): $i = 1; foreach ($purchases as $s): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= show_date($s->sales_date); ?></td>
                <td><a href="<?= base_url('sales/invoice/' . $s->id); ?>" target="_blank"><?= $s->sales_code; ?></a></td>
                <td class="amt"><?= store_number_format($s->grand_total); ?></td>
                <td class="amt"><?= store_number_format($s->paid_amount); ?></td>
                <td class="amt"><?= store_number_format($s->grand_total - $s->paid_amount); ?></td>
                <td>
                  <?php if ($s->payment_status == 'Paid'): ?><span class="mp-pill paid">Paid</span>
                  <?php elseif ($s->payment_status == 'Partial'): ?><span class="mp-pill partial">Partial</span>
                  <?php else: ?><span class="mp-pill unpaid"><?= $s->payment_status; ?></span>
                  <?php endif; ?>
                </td>
                <td><a href="<?= base_url('sales/invoice/' . $s->id); ?>" class="mp-qa-btn teal" style="padding:6px 12px;" target="_blank"><i class="fa fa-eye"></i></a></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="8" class="text-center text-muted">No purchase records found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Statements -->
        <div class="tab-pane" id="payments" style="padding:20px;">
          <div class="mp-kpi-grid" style="grid-template-columns:repeat(4,1fr)!important;margin-bottom:20px;">
            <div class="mp-kpi-card summary">
              <div class="mp-kpi-icon"><i class="fa fa-file-text-o"></i></div>
              <div class="mp-kpi-label">Opening Balance</div>
              <div class="mp-kpi-value"><?= store_number_format($opening); ?></div>
            </div>
            <div class="mp-kpi-card debt">
              <div class="mp-kpi-icon"><i class="fa fa-shopping-cart"></i></div>
              <div class="mp-kpi-label">Total Sales</div>
              <div class="mp-kpi-value"><?= store_number_format($statement_summary['total_sales']); ?></div>
            </div>
            <div class="mp-kpi-card sales">
              <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
              <div class="mp-kpi-label">Total Payments</div>
              <div class="mp-kpi-value"><?= store_number_format($statement_summary['total_payments']); ?></div>
            </div>
            <div class="mp-kpi-card debt">
              <div class="mp-kpi-icon"><i class="fa fa-balance-scale"></i></div>
              <div class="mp-kpi-label">Balance Due</div>
              <div class="mp-kpi-value"><?= store_number_format($statement_summary['closing_balance']); ?></div>
            </div>
          </div>
          <table class="mp-static-table">
            <thead>
              <tr><th>Date</th><th>Description</th><th>Reference</th><th class="text-right">Debit</th><th class="text-right">Credit</th><th class="text-right">Balance</th></tr>
            </thead>
            <tbody>
              <?php if (!empty($statement)): $i = 1; foreach ($statement as $row): ?>
              <tr>
                <td><?= show_date($row['date']); ?></td>
                <td><?= htmlspecialchars($row['description']); ?></td>
                <td><?= htmlspecialchars($row['reference']); ?></td>
                <td class="text-right" style="color:var(--mp-danger);"><?= $row['debit'] > 0 ? store_number_format($row['debit']) : '-'; ?></td>
                <td class="text-right" style="color:var(--mp-success);"><?= $row['credit'] > 0 ? store_number_format($row['credit']) : '-'; ?></td>
                <td class="text-right amt"><?= store_number_format($row['balance']); ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="6" class="text-center text-muted">No statement records found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
          <div class="text-center no-print" style="margin-top:10px;">
            <a href="<?= base_url('customers/statement/' . $customer->id); ?>" class="mp-qa-btn blue"><i class="fa fa-file-text-o"></i> View Full Statement / Print</a>
          </div>
        </div>

        <!-- Service History -->
        <?php if (!empty($service_history)): ?>
        <div class="tab-pane" id="services" style="padding:20px;">
          <table class="mp-static-table">
            <thead>
              <tr><th>#</th><th>Invoice</th><th>Items</th><th>Status</th><th>Drop-off</th><th>Last Update</th><th>Amount</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php $i = 1; foreach ($service_history as $sh): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><a href="<?= base_url('sales/invoice/' . $sh->sales_id); ?>" target="_blank"><?= $sh->sales_code; ?></a></td>
                <td><?= htmlspecialchars($sh->items_list ?: '-'); ?></td>
                <td>
                  <?php if ($sh->status == 'ready'): ?><span class="mp-pill paid">Ready for Pickup</span>
                  <?php elseif ($sh->status == 'collected'): ?><span class="mp-pill ok">Picked Up</span>
                  <?php elseif (in_array($sh->status, ['dropped_off','washing','ironing'])): ?><span class="mp-pill partial"><?= ucfirst(str_replace('_',' ',$sh->status)); ?></span>
                  <?php else: ?><span class="mp-pill muted"><?= ucfirst(str_replace('_',' ',$sh->status)); ?></span>
                  <?php endif; ?>
                  <?php if ($sh->status == 'ready'): ?><span class="mp-pill unpaid" style="margin-left:4px;"><i class="fa fa-exclamation-circle"></i> Not Picked</span><?php endif; ?>
                </td>
                <td><?= show_date($sh->created_at); ?></td>
                <td><?= show_date($sh->updated_at); ?></td>
                <td class="amt"><?= store_number_format($sh->grand_total); ?></td>
                <td><a href="<?= base_url('sales/invoice/' . $sh->sales_id); ?>" class="mp-qa-btn teal" style="padding:6px 12px;" target="_blank"><i class="fa fa-eye"></i></a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>

        <!-- Gift Cards -->
        <div class="tab-pane" id="giftcards" style="padding:20px;">
          <table class="mp-static-table">
            <thead>
              <tr><th>#</th><th>Card Number</th><th>Initial Value</th><th>Balance</th><th>Issue Date</th><th>Expiry</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (!empty($gift_cards)): $i = 1; foreach ($gift_cards as $g): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= $g->card_number; ?></td>
                <td class="amt"><?= store_number_format($g->initial_value); ?></td>
                <td class="amt"><?= store_number_format($g->current_balance); ?></td>
                <td><?= show_date($g->issue_date); ?></td>
                <td><?= show_date($g->expiry_date) ?: 'Never'; ?></td>
                <td>
                  <?php if ($g->status == 'active'): ?><span class="mp-pill paid">Active</span>
                  <?php elseif ($g->status == 'redeemed'): ?><span class="mp-pill ok">Redeemed</span>
                  <?php elseif ($g->status == 'expired'): ?><span class="mp-pill low">Expired</span>
                  <?php else: ?><span class="mp-pill out"><?= ucfirst($g->status); ?></span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="7" class="text-center text-muted">No gift cards found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Store Credit -->
        <div class="tab-pane" id="storecredit" style="padding:20px;">
          <table class="mp-static-table">
            <thead>
              <tr><th>#</th><th>Code</th><th>Amount</th><th>Balance</th><th>Source</th><th>Expiry</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (!empty($store_credits)): $i = 1; foreach ($store_credits as $sc): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= $sc->credit_code; ?></td>
                <td class="amt"><?= store_number_format($sc->amount); ?></td>
                <td class="amt"><?= store_number_format($sc->balance); ?></td>
                <td><?= ucfirst(str_replace('_',' ',$sc->source)); ?></td>
                <td><?= show_date($sc->expiry_date) ?: 'Never'; ?></td>
                <td>
                  <?php if ($sc->status == 'active'): ?><span class="mp-pill paid">Active</span>
                  <?php elseif ($sc->status == 'used'): ?><span class="mp-pill ok">Used</span>
                  <?php elseif ($sc->status == 'expired'): ?><span class="mp-pill low">Expired</span>
                  <?php else: ?><span class="mp-pill out"><?= ucfirst($sc->status); ?></span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="7" class="text-center text-muted">No store credit found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Coupons -->
        <div class="tab-pane" id="coupons" style="padding:20px;">
          <table class="mp-static-table">
            <thead>
              <tr><th>#</th><th>Code</th><th>Type</th><th>Value</th><th>Expiry</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (!empty($coupons)): $i = 1; foreach ($coupons as $c): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= $c->code; ?></td>
                <td><?= ucfirst($c->type); ?></td>
                <td class="amt"><?= $c->type == 'percentage' ? $c->value.'%' : store_number_format($c->value); ?></td>
                <td><?= show_date($c->expire_date) ?: 'Never'; ?></td>
                <td>
                  <?php if ($c->status == 1): ?><span class="mp-pill paid">Active</span>
                  <?php else: ?><span class="mp-pill out">Expired</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="6" class="text-center text-muted">No coupons found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Memberships -->
        <div class="tab-pane" id="memberships" style="padding:20px;">
          <table class="mp-static-table">
            <thead>
              <tr><th>#</th><th>Plan</th><th>Period</th><th>Status</th><th>Auto-Renew</th><th>Payment</th></tr>
            </thead>
            <tbody>
              <?php if (!empty($memberships)): $i = 1; foreach ($memberships as $m): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><b><?= htmlspecialchars($m->plan_name); ?></b><br><small style="color:var(--mp-muted);"><?= htmlspecialchars($m->plan_code); ?></small></td>
                <td><?= show_date($m->start_date); ?> <i class="fa fa-arrow-right text-muted"></i> <?= show_date($m->end_date); ?></td>
                <td>
                  <?php if ($m->status == 'active'): ?><span class="mp-pill paid">Active</span>
                  <?php elseif ($m->status == 'expired'): ?><span class="mp-pill out">Expired</span>
                  <?php else: ?><span class="mp-pill partial"><?= ucfirst($m->status); ?></span>
                  <?php endif; ?>
                </td>
                <td><?= $m->auto_renew ? '<span class="mp-pill ok"><i class="fa fa-refresh"></i> Auto</span>' : '<span class="mp-pill muted">-</span>'; ?></td>
                <td>
                  <?php if ($m->payment_status == 'paid'): ?><span class="mp-pill paid">Paid</span>
                  <?php elseif ($m->payment_status == 'overdue'): ?><span class="mp-pill out">Overdue</span>
                  <?php else: ?><span class="mp-pill partial"><?= ucfirst($m->payment_status); ?></span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="6" class="text-center text-muted">No membership records found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Notes -->
        <div class="tab-pane" id="notes" style="padding:20px;">
          <div class="mp-form-group full" style="margin-bottom:16px;">
            <label>Customer Notes</label>
            <textarea class="form-control mp-form-control" rows="10" readonly><?= nl2br(htmlspecialchars($customer->notes ?? '')); ?></textarea>
          </div>
          <?php if (empty($customer->notes)): ?><p class="text-muted">No notes recorded for this customer.</p><?php endif; ?>
          <?php if ($CI->permissions('customers_edit')): ?>
            <a href="<?= base_url('customers/update/' . $customer->id); ?>" class="mp-qa-btn blue" style="padding:6px 12px;"><i class="fa fa-edit"></i> Edit Notes</a>
          <?php endif; ?>
        </div>

        <!-- Treatment Notes -->
        <div class="tab-pane" id="treatment_notes" style="padding:20px;">
          <div class="clearfix" style="margin-bottom:10px;">
            <a href="<?= base_url('operations/treatment_note?customer_id=' . $customer->id); ?>" class="mp-qa-btn green pull-right no-print" style="padding:6px 12px;"><i class="fa fa-plus"></i> Add Note</a>
          </div>
          <table class="mp-static-table">
            <thead>
              <tr><th>#</th><th>Date</th><th>Service</th><th>Notes</th><th>Consumables</th><th>Staff</th></tr>
            </thead>
            <tbody>
              <?php if (!empty($treatment_notes)): $i = 1; foreach ($treatment_notes as $tn): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= show_date($tn->treatment_date); ?></td>
                <td><b><?= htmlspecialchars($tn->service_type); ?></b></td>
                <td><span style="color:var(--mp-muted);font-size:12px;"><?= nl2br(htmlspecialchars($tn->notes)); ?></span></td>
                <td>
                  <?php if (!empty($tn->consumables)): ?>
                    <ul style="list-style:none;margin:0;padding:0;font-size:12px;">
                      <?php foreach ($tn->consumables as $cons): ?>
                        <li><span class="mp-pill ok"><?= htmlspecialchars($cons->qty . ' ' . ($cons->consumable_unit ?: 'units')); ?></span> <?= htmlspecialchars($cons->item_name); ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <span style="color:var(--mp-muted);font-size:12px;"><?= nl2br(htmlspecialchars($tn->products_used)); ?></span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($tn->staff_name ?: '-'); ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="6" class="text-center text-muted">No treatment notes found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Medical Notes -->
        <?php if (!empty($medical_notes)): ?>
        <div class="tab-pane" id="medical_notes" style="padding:20px;">
          <?php if (!empty($medical_allergies)): ?>
          <div class="mp-card" style="background:rgba(220,38,38,.06)!important;border-color:var(--mp-danger)!important;margin-bottom:16px;">
            <div class="mp-card-body">
              <h4 style="font-size:15px;margin:0;color:var(--mp-danger);"><i class="fa fa-exclamation-triangle"></i> Known Allergies</h4>
              <ul style="margin:8px 0 0;">
                <?php foreach ($medical_allergies as $al): ?>
                  <li><strong><?= htmlspecialchars($al->allergies_flagged); ?></strong> <small style="color:var(--mp-muted);">(flagged <?= show_date($al->note_date); ?>)</small></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
          <?php endif; ?>
          <div class="clearfix" style="margin-bottom:10px;">
            <a href="<?= base_url('operations/medical_note?customer_id=' . $customer->id); ?>" class="mp-qa-btn green pull-right no-print" style="padding:6px 12px;"><i class="fa fa-plus"></i> Add Medical Note</a>
          </div>
          <table class="mp-static-table">
            <thead>
              <tr><th>#</th><th>Date</th><th>Doctor</th><th>Diagnosis</th><th>Rx</th><th>Medicines</th><th>Refills</th><th>Next Refill</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php if (!empty($medical_notes)): $i = 1; foreach ($medical_notes as $mn): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= show_date($mn->note_date); ?></td>
                <td><?= htmlspecialchars($mn->prescribing_doctor ?: '-'); ?></td>
                <td><b><?= htmlspecialchars($mn->diagnosis ?: '-'); ?></b><?php if ($mn->allergies_flagged): ?> <span class="mp-pill out" title="<?= htmlspecialchars($mn->allergies_flagged); ?>"><i class="fa fa-warning"></i></span><?php endif; ?></td>
                <td><?= !empty($mn->prescription_file) ? '<a href="'.base_url($mn->prescription_file).'" target="_blank" title="View Prescription" class="mp-qa-btn blue" style="padding:4px 10px;"><i class="fa fa-file-image-o"></i></a>' : '<span class="text-muted">-</span>'; ?></td>
                <td>
                  <?php if (!empty($mn->items)): ?>
                    <ul style="list-style:none;margin:0;padding:0;font-size:12px;">
                      <?php foreach ($mn->items as $mi): ?>
                        <li><span class="mp-pill ok"><?= htmlspecialchars($mi->qty); ?></span> <?= htmlspecialchars($mi->item_name); ?> <?php if ($mi->dosage): ?><small style="color:var(--mp-muted);"><?= htmlspecialchars($mi->dosage); ?></small><?php endif; ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?><span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?= $mn->refills_remaining > 0 ? '<span class="mp-pill ok">'.$mn->refills_remaining.' left</span>' : '<span class="text-muted">-</span>'; ?></td>
                <td><?= $mn->next_refill_date ? show_date($mn->next_refill_date) : '<span class="text-muted">-</span>'; ?></td>
                <td><a href="<?= base_url('operations/medical_note/' . $mn->id); ?>" class="mp-qa-btn blue" style="padding:6px 12px;"><i class="fa fa-pencil"></i></a></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="9" class="text-center text-muted">No medical notes found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>

        <!-- Custom Orders -->
        <div class="tab-pane" id="custom_orders" style="padding:20px;">
          <div class="clearfix" style="margin-bottom:10px;">
            <a href="<?= base_url('operations/custom_order?customer_id=' . $customer->id); ?>" class="mp-qa-btn green pull-right no-print" style="padding:6px 12px;"><i class="fa fa-plus"></i> New Custom Order</a>
          </div>
          <table class="mp-static-table">
            <thead>
              <tr><th>#</th><th>Order #</th><th>Item</th><th>Status</th><th>Due Date</th><th>Total</th><th>Deposit</th></tr>
            </thead>
            <tbody>
              <?php if (!empty($custom_orders)): $i = 1; foreach ($custom_orders as $co): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><span class="mp-pill muted"><?= htmlspecialchars($co->order_code); ?></span></td>
                <td><?= htmlspecialchars($co->item_name ?: '-'); ?></td>
                <td><span class="mp-pill <?= Custom_orders_model::status_badge($co->status); ?>"><?= Custom_orders_model::status_label($co->status); ?></span></td>
                <td><?= show_date($co->due_date); ?></td>
                <td class="amt"><?= store_number_format($co->total_amount); ?></td>
                <td class="amt"><?= store_number_format($co->deposit_paid); ?> / <?= store_number_format($co->deposit_amount); ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="7" class="text-center text-muted">No custom orders found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- ID Card -->
        <div class="tab-pane" id="idcard" style="padding:20px;">
          <div class="id-card-fit" style="margin:0 auto 20px;max-width:360px;">
            <div class="id-card-preview" id="idCardLarge">
              <div class="id-card-body">
                <div class="id-card-name"><?= htmlspecialchars($customer->customer_name ?? ''); ?></div>
                <div class="id-card-phone"><?= htmlspecialchars($customer->mobile ?? ''); ?></div>
                <div class="id-card-id">ID: <?= str_pad($customer->id, 6, '0', STR_PAD_LEFT); ?></div>
                <div class="id-card-barcode">
                  <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=C<?= $customer->id; ?>&scale=3&height=10" alt="barcode">
                </div>
              </div>
              <div class="id-card-brand"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
              <div class="id-card-footer">
                <div class="id-card-signature"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
                <div class="id-card-expiry"><?= date('M Y'); ?></div>
              </div>
            </div>
          </div>
          <div class="text-center no-print">
            <button class="mp-btn-primary" onclick="printCard('idCardLarge')"><i class="fa fa-print"></i> Print ID Card</button>
            <button class="mp-btn-secondary" onclick="downloadCard('idCardLarge','id-card-<?= str_pad($customer->id,6,'0',STR_PAD_LEFT); ?>.png')"><i class="fa fa-download"></i> Download PNG</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= $theme_link; ?>js/customers.js"></script>
<script src="<?= $theme_link; ?>plugins/tableExporter/libs/html2canvas/html2canvas.min.js"></script>
<script>
function fitIdCards(){
  $('.id-card-fit').each(function(){
    var card = this.querySelector('.id-card-preview');
    if (!card) return;
    var scale = Math.min(1, this.clientWidth / 324);
    card.style.transform = 'translateX(-50%) scale(' + scale + ')';
    this.style.height = (204 * scale) + 'px';
  });
}
$(window).on('resize', fitIdCards);
$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', fitIdCards);
$(fitIdCards);

function printCard(elId){
  var el = document.getElementById(elId);
  if (!el) return;
  var clone = el.cloneNode(true);
  clone.removeAttribute('style');
  clone.removeAttribute('id');
  var win = window.open('', '_blank');
  win.document.write('<html><head><title>ID Card</title>');
  win.document.write('<style>');
  win.document.write('body{margin:0;padding:20px;background:#f5f5f5;text-align:center;}');
  win.document.write('.id-card-preview{width:324px;height:204px;border-radius:12px;position:relative;overflow:hidden;background:linear-gradient(135deg,#fdfbf7 0%,#f5f0e8 100%);border:1px solid #e0d5c5;margin:0 auto;-webkit-print-color-adjust:exact;print-color-adjust:exact;}');
  win.document.write('.id-card-brand{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:42px;font-weight:900;color:#8b7355;opacity:0.06;letter-spacing:2px;text-transform:uppercase;white-space:nowrap;pointer-events:none;user-select:none;}');
  win.document.write('.id-card-barcode{text-align:center;margin-top:8px;}');
  win.document.write('.id-card-barcode img{height:42px;}');
  win.document.write('.id-card-body{padding:20px 16px 16px 16px;text-align:center;}');
  win.document.write('.id-card-name{font-size:18px;font-weight:700;color:#3d3229;margin-bottom:4px;}');
  win.document.write('.id-card-phone{font-size:14px;color:#6b5b4f;margin-bottom:8px;}');
  win.document.write('.id-card-id{font-size:11px;color:#9e8e7e;letter-spacing:2px;font-family:monospace;}');
  win.document.write('.id-card-footer{position:absolute;bottom:0;left:0;right:0;padding:8px 12px;display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.5);border-top:1px solid rgba(0,0,0,0.05);}');
  win.document.write('.id-card-signature{font-size:10px;color:#8b7355;font-style:italic;}');
  win.document.write('.id-card-expiry{font-size:10px;color:#3d3229;font-weight:600;}');
  win.document.write('</style></head><body>');
  win.document.write(clone.outerHTML);
  win.document.write('</body></html>');
  win.document.close();
  setTimeout(function(){ win.print(); win.close(); }, 300);
}

function downloadCard(elementId, filename){
  var el = document.getElementById(elementId);
  if (!el) return;
  var prevTransform = el.style.transform;
  el.style.transform = 'none';
  html2canvas(el, {scale: 2, useCORS: true, backgroundColor: null}).then(function(canvas){
    el.style.transform = prevTransform;
    var link = document.createElement('a');
    link.download = filename;
    link.href = canvas.toDataURL('image/png');
    link.click();
  }).catch(function(){
    el.style.transform = prevTransform;
  });
}

$(function(){
  $('#purchaseTable').DataTable({
    "paging": true,
    "lengthChange": false,
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false
  });
});
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.customers_list-active-li').addClass('active');
  $('.customers_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
