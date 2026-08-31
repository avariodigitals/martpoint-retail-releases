<?php
/**
 * MartPoint Retail — Core Base Controller
 *
 * All application controllers extend this class.
 * Handles authentication, global data loading, database updates,
 * language switching, store context, and theme engine setup.
 *
 * @author      Rapheal Ogundiran (MartPoint Retail by Avario)
 * @copyright   Copyright (c) 2019 - 2026, Avario / MartPoint Retail
 * @license     Private — All rights reserved
 * @since       1.0.0
 */
class MY_Controller extends CI_Controller{
      public $source_version = "4.0.8";
      public function __construct()
      {
        parent::__construct();

        //$this->output->enable_profiler(TRUE);

        //Before Login Update check
        if($this->uri->segment(1) == 'login' || empty($this->uri->segment(1))){
          $this->update_db();
        }

        //Used after logout
        if(!empty($this->input->cookie("language"))){
         $this->session->set_userdata('language',$this->input->cookie("language"));
         
         $cookie = array(
            'name'   => 'language',
              'value'  => '',
              'expire' => '0',
              );
          $this->input->set_cookie($cookie);
        }
        //end
        
        $default_lang = ($this->session->has_userdata('language')) ? $this->session->userdata('language') : "English";

        $this->lang->load($default_lang, $default_lang);
      }
      public function load_info(){
        /*if(strtotime(date("d-m-Y")) >= strtotime(date("05-04-2019"))){
            echo "License Expired! Contact Admin";exit();
          }*/

            //CHECK LANGUAGE IN SESSION ELSE FROM DB
            if(!$this->session->has_userdata('language') && $this->session->has_userdata('logged_in') ){
              $this->load->model('language_model');
              $this->language_model->set(get_current_store_language());
            }
            if($this->session->has_userdata('logged_in')){
              $this->lang->load($this->session->userdata('language'), $this->session->userdata('language'));
            }
            //End

            //If currency not set retrieve from DB
            $this->load_currency_data();
            //end

            

            $query =$this->db->select('site_name,version')->where('id',1)->get('db_sitesettings');

            $this->db->select('store_name,timezone,time_format,date_format,decimals,qty_decimals');
            if($this->session->userdata('logged_in')){
              $this->db->where('id',get_current_store_id());
            }
            else{
              $this->db->where('id',1);
            }
            $this->db->from('db_store');
            $query1 = $this->db->get();

            date_default_timezone_set(trim($query1->row()->timezone));

            $time_format = ($query1->row()->time_format=='24') ? date("h:i:s") : date("h:i:s a");

            $date_view_format = trim($query1->row()->date_format);
            $this->session->set_userdata(array('view_date'  => $date_view_format));
            $this->session->set_userdata(array('view_time'  => $query1->row()->time_format));
            $this->session->set_userdata(array('decimals'  => $query1->row()->decimals));
            $this->session->set_userdata(array('qty_decimals'  => $query1->row()->qty_decimals));
            $this->session->set_userdata(array('store_name'  => $query1->row()->store_name));
            

            $this->data = array('theme_link'    => base_url().'theme/',
                                'base_url'      => base_url(),
                                'SITE_TITLE'    => $query->row()->site_name,
                                'VERSION'       => $query->row()->version,
                                'CURRENCY'      => $this->session->userdata('currency'),
                                'CURRENCY_PLACE'=> $this->session->userdata('currency_placement'),
                                'CURRENCY_CODE' => $this->session->userdata('currency_code'),
                                'CUR_DATE'      => date("Y-m-d"),
                                'VIEW_DATE'     => $date_view_format,
                                'CUR_TIME'      => $time_format,
                                'SYSTEM_IP'     => $_SERVER['REMOTE_ADDR'],
                                'SYSTEM_NAME'   => gethostbyaddr($_SERVER['REMOTE_ADDR']),
                                'CUR_USERNAME'  => $this->session->userdata('inv_username'),
                                'CUR_USERID'    => $this->session->userdata('inv_userid'),
                                'CUR_USERID'    => $this->session->userdata('inv_userid'),
                                    );

            // Load subscription license status
            if($this->session->userdata('logged_in') && $this->db->table_exists('db_subscription_license')){
              $this->load->model('subscription_license_model','sub_lic');
              $this->data['subscription_status'] = $this->sub_lic->get_status();
            }
      }
      public function load_currency_data(){
        if($this->session->userdata('logged_in')){
          $q1=$this->db->query("SELECT a.currency_name,a.currency,a.currency_code,a.symbol,b.currency_placement FROM db_currency a,db_store b WHERE a.id=b.currency_id AND b.id=".get_current_store_id());
              $currency = $q1->row()->currency;
              $currency_placement = $q1->row()->currency_placement;
              $currency_code = $q1->row()->currency_code;
              $this->session->set_userdata(array('currency'  => $currency,'currency_placement'  => $currency_placement,'currency_code'  => $currency_code));
        }
        else{
          $this->session->set_userdata(array('currency'  => '','currency_placement'  => '','currency_code'  => '')); 
        }
      }

      public function verify_store_and_user_status(){
            $store_rec = get_store_details();
            //STORE ACTIVE OR NOT
            if(!$store_rec->status){
              $this->session->set_flashdata('failed', 'Your Store Temporarily Inactive!');
              redirect('logout');exit;
            }
            //USER ACTIVE OR NOT
            if(!get_user_details()->status){
              $this->session->set_flashdata('failed', 'Your account is temporarily inactive!');
              redirect('logout');exit;
            }
      }
      public function load_global($validate_subs='VALIDATE'){
            //Check login or redirect to logout
            if($this->session->userdata('logged_in')!=1){ redirect(base_url().'logout','refresh');    }

            $this->verify_store_and_user_status();

            // Subscription enforcement
            $this->enforce_subscription();

            // DB update warning: handled in code_flashdata via session flag (not flashdata)
            // to prevent it from reappearing on every page load

            $this->load_info();
      }

      public function enforce_subscription(){
            $ctrl = strtolower($this->router->fetch_class());
            $method = strtolower($this->router->fetch_method());
            // Allow access to subscription management and login/logout pages
            if($ctrl === 'subscription_license' || $ctrl === 'login' || $ctrl === 'logout' || $ctrl === 'updates' || $ctrl === 'dashboard'){
              return;
            }
            if(!special_access() && $this->db->table_exists('db_subscription_license')){
              $this->load->model('subscription_license_model','sub_lic');
              $sub = $this->sub_lic->get_status();
              if($sub['status'] === 'SUSPENDED'){
                $this->session->set_flashdata('failed', 'Subscription is SUSPENDED. Contact admin for support.');
                redirect('dashboard','refresh');
                exit;
              }
              if($sub['status'] === 'EXPIRED'){
                $this->session->set_flashdata('failed', 'Subscription has EXPIRED. Please renew to continue.');
                redirect('dashboard','refresh');
                exit;
              }
            }
      }

      public function currency($value='',$with_comma=true){
        $value = trim($value);

        if($value !== '' && is_numeric($value)){
          $value= ($with_comma) ? store_number_format($value) : store_number_format($value,false);
        }

        if($this->session->userdata('currency_placement')=='Left'){
          if(!empty($value)){
            return $this->session->userdata('currency')." ".$value;
          }
          return $this->session->userdata('currency')."".$value;
          
        }
        else{
          if(!empty($value)){
            return $value." ".$this->session->userdata('currency');    
          }
         return $value."".$this->session->userdata('currency'); 
        }
      }

      

      public function store_wise_currency($store_id,$value=''){

        $q1=$this->db->query("SELECT a.currency_name,a.currency,a.currency_code,a.symbol,b.currency_placement FROM db_currency a,db_store b WHERE a.id=b.currency_id AND b.id=".$store_id);
              $currency = $q1->row()->currency;
              $currency_placement = $q1->row()->currency_placement;
              $currency_code = $q1->row()->currency_code;

        $value = trim($value);
        if(!empty($value) && is_numeric($value)){
          $value=number_format($value,2,'.','');
        }
        if($currency_placement=='Left'){
          if(!empty($value)){
            return $currency." ".$value;
          }
          return $currency."".$value;
          
        }
        else{
          if(!empty($value)){
            return $value." ".$currency;    
          }
         return $value."".$currency; 
        }
      }
      
      public function currency_code($value=''){
        if(!empty($this->session->userdata('currency_code'))){
          if($this->session->userdata('currency_placement')=='Left'){
            return $this->session->userdata('currency_code')." ".$value;
          }
          else{
           return $value." ".$this->session->userdata('currency'); 
          }
        }
        else{
          return $value;
        }
      }
      public function permissions($permissions=''){
          //If he the Admin (user_id 1 or 2 get full access)
          if($this->session->userdata('inv_userid')==1 || $this->session->userdata('inv_userid')==2){
            return true;
          }

          $role_id = $this->session->userdata('role_id');
          if(empty($role_id)){
            return false;
          }

          $tot=$this->db->query('SELECT count(*) as tot FROM db_permissions where permissions="'.$this->db->escape_str($permissions).'" and role_id='.(int)$role_id)->row()->tot;
          if($tot==1){
            return true;
          }

          // Fallback for standard roles whose permissions have not been seeded
          // Cache the default permissions per role to avoid loading the model on every call
          static $cached_role_perms = null;
          if($cached_role_perms === null){
            $this->load->model('default_data_model','default_data');
            $cached_role_perms = $this->default_data->get_role_default_permissions($this->session->userdata('role_name'));
          }
          if(!empty($cached_role_perms) && in_array($permissions, $cached_role_perms, true)){
            return true;
          }

           return false;
        }
        
        public function permission_check($value=''){
          if(!$this->permissions($value)){
             $this->show_access_denied_page();
          }
          return true;
        }
        public function permission_check_with_msg($value=''){
          if(!$this->permissions($value)){
             echo "You don't have permission for this operation.";
            exit();
          }
          return true;
        }
        public function show_access_denied_page()
        {
          // AJAX requests get JSON, not a redirect
          if($this->input->is_ajax_request() || $this->input->post('is_ajax')){
            header('Content-Type: application/json');
            echo json_encode(array('status'=>'error','message'=>'You don\'t have permission to access this feature.'));
            exit;
          }
          // Normal page requests: redirect back with a toastr notification
          $this->session->set_flashdata('error', 'You don\'t have permission to access that feature.');
          $referrer = $this->input->server('HTTP_REFERER', TRUE);
          if(!empty($referrer)){
            redirect($referrer);
          } else {
            redirect(base_url('dashboard'));
          }
        }

        /**
         * Render the branded "Feature Not Activated" card.
         * Use this when a feature flag is off (not a permission issue).
         */
        public function show_feature_not_activated($flag, $description = '')
        {
          $profile = function_exists('mp_get_store_profile') ? mp_get_store_profile() : [];
          $industry_label = '';
          if (is_array($profile) && !empty($profile['industry_type'])) {
            $industry_label = ucwords(str_replace(['_','-'], ' ', $profile['industry_type']));
          }
          $icons = [
            'treatment_notes'      => 'fa-file-text-o',
            'medical_notes'        => 'fa-file-medical-o',
            'custom_orders'        => 'fa-pencil-square-o',
            'memberships'          => 'fa-id-card',
            'packages'             => 'fa-gift',
            'production_workflow'  => 'fa-industry',
            'recipe_tracking'      => 'fa-cutlery',
            'kitchen_workflow'     => 'fa-utensils',
            'laundry_workflow'     => 'fa-tint',
            'price_catalogue'      => 'fa-book',
            'public_catalogue'     => 'fa-globe',
            'staff_assignment'     => 'fa-user-md',
            'staff_commission'     => 'fa-percent',
            'table_management'     => 'fa-table',
            'delivery_scheduling'  => 'fa-truck',
            'expiry_tracking'      => 'fa-calendar-times-o',
            'batch_tracking'       => 'fa-layer-group',
            'serial_number_tracking'=> 'fa-barcode',
            'imei_tracking'        => 'fa-mobile',
            'warranty_tracking'    => 'fa-shield',
            'online_store'         => 'fa-globe',
            'qr_ordering'          => 'fa-qrcode',
            'loyalty'              => 'fa-heart',
            'gift_cards'           => 'fa-gift',
            'store_credit'         => 'fa-credit-card',
            'payplan'              => 'fa-money',
            'bundles'              => 'fa-cubes',
            'manager_approvals'    => 'fa-check-circle-o',
            'accounts'             => 'fa-calculator',
            'warehouse'            => 'fa-building',
            'multi_unit_inventory' => 'fa-cubes',
          ];
          $icon = isset($icons[$flag]) ? $icons[$flag] : 'fa-lock';

          set_status_header(403);
          $d = $this->data ?? [];
          $d['page_title']      = function_exists('mp_feature_label') ? mp_feature_label($flag) : ucwords(str_replace(['_','-'],' ',$flag));
          $d['feature_label']   = $d['page_title'];
          $d['feature_key']     = $flag;
          $d['industry_label']  = $industry_label;
          $d['icon']            = $icon;
          $d['description']     = $description;
          $d['enable_url']      = base_url('business_profile');
          $d['back_url']        = base_url('dashboard');
          $this->load->view('operations/feature_not_activated.php', $d);
          exit;
        }
            //end
        public function get_current_version_of_db(){
          return $this->db->select('version')->from('db_sitesettings')->get()->row()->version;
        }
        
        public function belong_to($table,$rec_id){
          if(!is_it_belong_to_store($table,$rec_id)){
            show_error("Data may not avaialable!!", 403, $heading = "Something Went Wrong!!");
          }
        }

       public function update_db()
        { 
          //Before Login purpose only
          $this->load->model('updates_model');
          $this->updates_model->index();
        }

}