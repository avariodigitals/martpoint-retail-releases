<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Country_model extends CI_Model {

	var $table = 'db_country';
	var $column_order = array('country','status'); //set column field database for datatable orderable
	var $column_search = array('country','status'); //set column field database for datatable searchable 
	var $order = array('id' => 'desc'); // default order 

	private function _get_datatables_query()
	{
		
		$this->db->from($this->table);

		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}


	public function verify_and_save(){
		$country_name = $this->input->post('country_name', TRUE);

		//Validate This country already exist or not
		$this->db->where("upper(country)", strtoupper($country_name));
		$query = $this->db->get('db_country');
		if($query->num_rows()>0){
			return "Country Name already Exist.";
			
		}
		else{
			$info = array('country' => $country_name, 'status' => 1);
			if ($this->db->insert('db_country', $info)){
					$this->session->set_flashdata('success', 'Success!! New Country Name Added Successfully!');
			        return "success";
			}
			else{
			        return "failed";
			}
		}
	}

	//Get country_details
	public function get_details($id){
		$data=$this->data;

		//Validate This country already exist or not
		$query = $this->db->where('id', $id)->get('db_country');
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
			$data['country']=$query->country;
			
			return $data;
		}
	}
	public function update_country(){
		$q_id = $this->input->post('q_id', TRUE);
		$country_name = $this->input->post('country_name', TRUE);
		
		//Validate This country already exist or not
		$this->db->where("upper(country)", strtoupper($country_name));
		$this->db->where("id !=", $q_id);
		$query = $this->db->get('db_country');
		if($query->num_rows()>0){
			return "Country Name already Exist.";
			
		}
		else{
			
			if ($this->db->where('id', $q_id)->update('db_country', array('country' => $country_name))){
					$this->session->set_flashdata('success', 'Success!! Country Name Updated Successfully!');
			        return "success";
			}
			else{
			        return "failed";
			}
		}
	}

	public function update_status($id,$status){

		$this->db->where("id",$id);
	    $this->db->set("status",$status);
	    $query1=$this->db->update('db_country');
        if ($query1){
            return "success";
        }
        return "failed";
	}
	public function delete_country($id){
        $query1="delete from db_country where id=$id";
        if ($this->db->simple_query($query1)){
            echo "success";
        }
        else{
            echo "failed";
        }
	}


}
