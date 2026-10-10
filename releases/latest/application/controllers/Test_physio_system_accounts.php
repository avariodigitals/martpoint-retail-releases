<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Test_physio_system_accounts extends CI_Controller {
 private function check($ok,$msg){if(!$ok)throw new RuntimeException($msg);echo 'PASS '.$msg."\n";}
 public function run(){
  if(!is_cli() || getenv('MP_DB')!=='mp_verify_physio_20261010'){show_404();return;}
  $this->session->set_userdata(array('store_id'=>3,'inv_userid'=>113,'inv_username'=>'system-account-test','role_id'=>1));
  foreach(array('inpatient_model','physio_accounts_model','patient_billing_model','imports_model','patient_funds_model','treatment_plans_model') as $m)$this->load->model($m);
  $this->load->helper(array('physio','accounts'));
  $this->db->trans_begin();
  try{
   $this->inpatient_model->setPolicy('bed_system_accounts_enabled','0');
   $this->check(!$this->physio_accounts_model->systemEnabled(),'system routing is optional and off');
   $this->check($this->physio_accounts_model->resolveSystemAccount(0,3,999999)===null,'off ignores account choices');
   $this->physio_accounts_model->provisionSystemAccounts(3);
   $this->inpatient_model->setPolicy('bed_system_accounts_enabled','1');
   $this->physio_accounts_model->provisionSystemAccounts(3);
   $n=$this->db->where('store_id',3)->like('account_code','PHYSIO-BED-','after')->count_all_results('ac_accounts');
   $this->physio_accounts_model->provisionSystemAccounts(3);
   $this->check($this->db->where('store_id',3)->like('account_code','PHYSIO-BED-','after')->count_all_results('ac_accounts')===$n,'repeat enable does not duplicate bed accounts');
   $tag='SYS'.uniqid();$batch=$this->imports_model->startBatch('fixture',$tag);
   $p=$this->imports_model->importPatient($batch,array('legacy_id'=>$tag,'name'=>'System account fixture'));
   $this->db->insert('ac_accounts',array('store_id'=>3,'count_id'=>get_count_id('ac_accounts',3),'account_code'=>$tag,'account_name'=>$tag.' outpatient','sort_code'=>'999','parent_id'=>0,'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>'test','status'=>1,'delete_bit'=>0));
   $ctx=$this->physio_accounts_model->systemContext($p['customer_id'],3);
   $this->check(!$ctx['bed'] && count($ctx['choices'])>0,'outpatient sees non-bed system accounts');
   $ordinary=$ctx['choices'][0]->id;
   $this->check($this->physio_accounts_model->resolveSystemAccount($p['customer_id'],3,$ordinary)===$ordinary*1,'outpatient can select an ordinary account');
   try{$this->physio_accounts_model->resolveSystemAccount($p['customer_id'],3,99999999);$bad=false;}catch(Exception $e){$bad=true;}
   $this->check($bad,'foreign or unknown account rejected');
   $plan=$this->treatment_plans_model->createPlan(array('patient_id'=>$p['patient_id'],'customer_id'=>$p['customer_id'],'clinician_id'=>113,'care_setting'=>'outpatient'),array(array('item_id'=>3316,'qty'=>2,'unit_price'=>100)));
   $bill=$this->patient_billing_model->createBillFromPlan($plan['plan_id'],$ordinary);
   $this->check(!empty($bill['ok']) && (int)$this->patient_billing_model->getBill($bill['sales_id'])->physio_system_account_id===(int)$ordinary,'outpatient bill snapshots selected non-bed account');
   $outpay=$this->patient_billing_model->addPayment($bill['sales_id'],50,'cash','Outpatient',$tag.'out');
   $this->check(!empty($outpay['ok']) && (int)$this->db->where('id',$outpay['payment_id'])->get('db_salespayments')->row('account_id')===(int)$ordinary,'outpatient payment defaults to selected bill account');
   $w=$this->inpatient_model->saveWard($tag);$b=$this->inpatient_model->saveBed($w['ward_id'],'A',500);
   $this->check(!empty($b['ok']),'new bed provisions a system account when on');
   $a=$this->db->where('store_id',3)->where('bed_id',$b['bed_id'])->get('db_physio_payment_accounts')->row();
   try{$this->physio_accounts_model->resolveSystemAccount($p['customer_id'],3,$a->system_account_id);$bad=false;}catch(Exception $e){$bad=true;}
   $this->check($bad,'outpatient cannot choose a bed account');
   $ad=$this->inpatient_model->admit($p['patient_id'],array('bed_id'=>$b['bed_id'],'outpatient_decision'=>'continue','reason'=>'Test'));
   $this->check(!empty($ad['ok']),'inpatient admitted');
   $this->check($this->physio_accounts_model->resolveSystemAccount($p['customer_id'],3)===(int)$a->system_account_id,'inpatient automatically resolves current bed account');
   try{$this->physio_accounts_model->resolveSystemAccount($p['customer_id'],3,$ordinary);$bad=false;}catch(Exception $e){$bad=true;}
   $this->check($bad,'inpatient cannot override with ordinary account');
   $this->inpatient_model->setPolicy('auto_settle_wallet','0');$this->inpatient_model->postDailyCharges(3,date('Y-m-d'));
   $inv=$this->inpatient_model->admissionAccount($ad['admission_id']);
   $pay=$this->patient_billing_model->addPayment($inv['invoice_id'],100,'cash','Test',$tag);
   $this->check(!empty($pay['ok']),'direct patient payment succeeds');
   $r=$this->db->where('id',$pay['payment_id'])->get('db_salespayments')->row();
   $this->check((int)$r->account_id===(int)$a->system_account_id,'receipt stores linked system account');
   $tx=$this->db->where('ref_salespayments_id',$pay['payment_id'])->get('ac_transactions')->result();
   $this->check(count($tx)===1 && (float)$tx[0]->credit_amt===100.0,'one collection credits account book');
   $again=$this->patient_billing_model->addPayment($inv['invoice_id'],100,'cash','Test',$tag);
   $this->check(!empty($again['replayed']) && $this->db->where('ref_salespayments_id',$pay['payment_id'])->count_all_results('ac_transactions')===1,'retry does not duplicate money');
   $b2=$this->inpatient_model->saveBed($w['ward_id'],'B',700);
   $tr=$this->inpatient_model->requestTransfer($ad['admission_id'],$b2['bed_id'],'Test transfer');
   $this->inpatient_model->claimTask($tr['porter_task_id']);$this->inpatient_model->completeTask($tr['porter_task_id']);
   $new=$this->physio_accounts_model->resolveSystemAccount($p['customer_id'],3);
   $after=$this->patient_billing_model->addPayment($inv['invoice_id'],50,'transfer','Transfer',$tag.'move');
   $this->check(!empty($after['ok']) && (int)$this->db->where('id',$after['payment_id'])->get('db_salespayments')->row('account_id')===$new && $new!==(int)$a->system_account_id,'transfer routes new collection to the new bed');
   $this->check((int)$this->db->where('id',$pay['payment_id'])->get('db_salespayments')->row('account_id')===(int)$a->system_account_id,'transfer preserves earlier account history');
   $fund=$this->patient_funds_model->fundCash($p['patient_id'],50,$tag.'fund',113);
   $this->check(!empty($fund['ok']),'wallet fixture funded');
   $wallet=$this->patient_billing_model->addPayment($inv['invoice_id'],50,'patient_wallet','Wallet',$tag.'wallet');
   $this->check(!empty($wallet['ok']) && $this->db->where('ref_salespayments_id',$wallet['payment_id'])->count_all_results('ac_transactions')===0,'wallet settlement creates no second collection');
   $this->inpatient_model->setPolicy('bed_system_accounts_enabled','0');
   $off=$this->patient_billing_model->addPayment($inv['invoice_id'],50,'cash','Off',$tag.'off');
   $this->check(!empty($off['ok']) && $this->db->where('id',$off['payment_id'])->get('db_salespayments')->row('account_id')===null,'turning off restores existing payment behaviour');
  }finally{$this->db->trans_rollback();}
  echo "System account tests completed; fixture changes rolled back.\n";
 }
}
