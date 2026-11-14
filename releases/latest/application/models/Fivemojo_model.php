<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Fivemojo_model extends CI_Model {

    public function index($mobile,$message)
    {

        $data = ['phone' => $mobile, 'text' => $message];
        //echo "<pre>";
        //print_r($this->sendSMS($data));
        return $this->sendSMS($data);
    }

    protected function sendSMS($data) {
        $q1=$this->db->select("*")->where('store_id',get_current_store_id())->get("db_fivemojo");
        if($q1->num_rows()>0){
            $url = $q1->row()->url;
            $token = $q1->row()->token;
            $instance_id = $q1->row()->instance_id;

            if(empty($url) || empty($token) || empty($instance_id)){
                return "Invalid Fivemojo API Details!";
            }

            
            try{
                
                $api=array();
                
                $api = array_merge($api, ['type' => 'text']);
                $api = array_merge($api, ['message' => $data['text']]);
                $api = array_merge($api, ['number' => $data['phone']]);
                $api = array_merge($api, ['instance_id' => $instance_id]);
                $api = array_merge($api, ['access_token' => $token]);
                
                /*For Special characters need to set unicode Ex: Currency Symbols*/
                //$api = array_merge($api, ['unicode' => '1']);
                
                //print_r($api);exit();

                $ch = curl_init();
                $data = http_build_query($api);
                $getUrl = $url."?".$data;

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_URL, $getUrl);
                curl_setopt($ch, CURLOPT_TIMEOUT, 80);
                 
                $response = curl_exec($ch);
                 
                if(curl_error($ch)){
                    return 'failed';
                }
                else
                {
                    return 'success';
                }
                 
                curl_close($ch);
                //return $output;

            }
            catch(Exception $e){
               // print_r($e);
                return "failed";
            }


        }
          
    }

}

/* End of file twilio_sms.php */
/* Location: ./application/models/twilio_sms.php */