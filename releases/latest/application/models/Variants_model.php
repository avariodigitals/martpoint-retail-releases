<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Variants_model extends CI_Model {

	var $table = 'db_variants';
	var $column_order = array(null, 'variant_code','variant_name','description','status'); //set column field database for datatable orderable
	var $column_search = array('variant_code','variant_name','description','status'); //set column field database for datatable searchable 
	var $order = array('id' => 'desc'); // default order 

	private function _get_datatables_query()
	{
		
		$this->db->from($this->table);
		//if not admin
		//if(!is_admin()){
			$this->db->where("store_id",get_current_store_id());
		//}
		
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if(isset($_POST['search']['value']) && !empty($_POST['search']['value'])) // if datatable send POST for search
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
		
		if(isset($_POST['order']) && isset($_POST['order']['0']['column']) && isset($_POST['order']['0']['dir'])) // here order processing
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
		if(isset($_POST['length']) && $_POST['length'] != -1)
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
		$this->db->where("store_id",get_current_store_id());
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}


	public function verify_and_save(){
		$variant = $this->input->post('variant', TRUE);
		$description = $this->input->post('description', TRUE);
		$attribute_type = $this->input->post('attribute_type', TRUE);
		$attribute_value = $this->input->post('attribute_value', TRUE);

		//Validate This variant already exist or not
		$store_id=(store_module() && is_admin()) ? $store_id : get_current_store_id();
		$this->db->where("upper(variant_name)", strtoupper($variant));
		$this->db->where('store_id', $store_id);
		$query = $this->db->get('db_variants');
		if($query->num_rows()>0){
			return "This Variant Name already Exist.";

		}
		else{
			$info = array(
		    				'variant_name' 				=> $variant,
		    				'attribute_type'			=> !empty($attribute_type) ? $attribute_type : null,
		    				'attribute_value'			=> !empty($attribute_value) ? $attribute_value : null,
		    				'description' 				=> $description,
		    				'status' 				=> 1,
		    			);

			$info['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();

			$q1 = $this->db->insert('db_variants', $info);
			if ($q1){
					$this->session->set_flashdata('success', 'Success!! New Variant Added Successfully!');
			        return "success";
			}
			else{
			        return "failed";
			}
		}
	}

	//Get variant_details
	public function get_details($id,$data){
		//Validate This variant already exist or not
		$query=$this->db->query("select * from db_variants where upper(id)=upper('$id')");
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
			$data['variant_name']=$query->variant_name;
			$data['attribute_type']=$query->attribute_type;
			$data['attribute_value']=$query->attribute_value;
			$data['description']=$query->description;
			$data['store_id']=$query->store_id;
			return $data;
		}
	}
	public function update_variant(){
		$q_id = $this->input->post('q_id', TRUE);
		$variant = $this->input->post('variant', TRUE);
		$description = $this->input->post('description', TRUE);
		$attribute_type = $this->input->post('attribute_type', TRUE);
		$attribute_value = $this->input->post('attribute_value', TRUE);

		//Validate This variant already exist or not
		$store_id=(store_module() && is_admin()) ? $store_id : get_current_store_id();
		$this->db->where("upper(variant_name)", strtoupper($variant));
		$this->db->where("id !=", $q_id);
		$this->db->where('store_id', $store_id);
		$query = $this->db->get('db_variants');
		if($query->num_rows()>0){
			return "This Variant Name already Exist.";

		}
		else{
			$info = array(
		    				'variant_name' 				=> $variant,
		    				'attribute_type'			=> !empty($attribute_type) ? $attribute_type : null,
		    				'attribute_value'			=> !empty($attribute_value) ? $attribute_value : null,
		    				'description' 				=> $description,
		    			);
			
			$info['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();

			$q1 = $this->db->where('id',$q_id)->update('db_variants', $info);
		
			if ($q1){
					$this->session->set_flashdata('success', 'Success!! Variant Updated Successfully!');
			        return "success";
			}
			else{
			        return "failed";
			}
		}
	}
	public function update_status($id,$status){
		if (set_status_of_table($id,$status,'db_variants')){
            echo "success";
        }
        else{
            echo "failed";
        }
	}
	public function delete_variants_from_table($ids){
			$this->db->trans_begin();

			//find the this BRAND has the items ?
			$items_rec = $this->db->select("*")->where("store_id",get_current_store_id())->where("variant_id in($ids)")->get("db_items");
			if($items_rec->num_rows()>0){
				echo "Can't Delete!<br>Variant Has the Items! You need to delete Items!";
				exit;
			}

			$this->db->where("id in ($ids)");
			//if not admin
			if(!is_admin()){
				$this->db->where("store_id",get_current_store_id());
			}

			$query1=$this->db->delete("db_variants");


	        if ($query1){
	        	$this->db->trans_commit();
	            echo "success";
	        }
	        else{
	            echo "failed";
	        }	
		
	}


	/**
	 * Generate all size x colour (x material) combinations as variants.
	 * Each combination becomes one db_variants row with attribute_type='size'
	 * and attribute_value='M', plus a db_variant_attributes row for each
	 * attribute dimension (size, colour, material).
	 */
	public function generate_matrix_variants($sizes, $colours, $materials=''){
		$store_id = get_current_store_id();
		$sizes_arr = array_filter(array_map('trim', preg_split('/[,;\n]/', $sizes)));
		$colours_arr = array_filter(array_map('trim', preg_split('/[,;\n]/', $colours)));
		$materials_arr = array_filter(array_map('trim', preg_split('/[,;\n]/', $materials)));

		if(empty($sizes_arr) && empty($colours_arr)){
			return "Please enter at least sizes or colours.";
		}
		if(empty($sizes_arr)){ $sizes_arr = array(''); }
		if(empty($colours_arr)){ $colours_arr = array(''); }
		if(empty($materials_arr)){ $materials_arr = array(''); }

		$created = 0;
		$skipped = 0;
		$this->db->trans_begin();

		foreach($sizes_arr as $size){
			foreach($colours_arr as $colour){
				foreach($materials_arr as $material){
					$parts = array();
					if($size !== '') $parts[] = $size;
					if($colour !== '') $parts[] = $colour;
					if($material !== '') $parts[] = $material;
					$variant_name = implode(' / ', $parts);
					if(empty($variant_name)){ continue; }

					$exists = $this->db->where('store_id', $store_id)
						->where('UPPER(variant_name)', strtoupper($variant_name))
						->count_all_results('db_variants');
					if($exists > 0){ $skipped++; continue; }

					$primary_type = '';
					$primary_value = '';
					if($size !== ''){ $primary_type='size'; $primary_value=$size; }
					elseif($colour !== ''){ $primary_type='colour'; $primary_value=$colour; }
					elseif($material !== ''){ $primary_type='material'; $primary_value=$material; }

					$this->db->insert('db_variants', array(
						'store_id' => $store_id,
						'variant_name' => $variant_name,
						'attribute_type' => $primary_type,
						'attribute_value' => $primary_value,
						'description' => 'Generated by matrix builder',
						'status' => 1,
					));
					$variant_id = $this->db->insert_id();

					if($this->db->table_exists('db_variant_attributes')){
						if($size !== ''){
							$this->db->insert('db_variant_attributes', array(
								'store_id'=>$store_id, 'variant_id'=>$variant_id,
								'attribute_type'=>'size', 'attribute_value'=>$size, 'sort_order'=>1,
								'created_date'=>date('Y-m-d'),
							));
						}
						if($colour !== ''){
							$this->db->insert('db_variant_attributes', array(
								'store_id'=>$store_id, 'variant_id'=>$variant_id,
								'attribute_type'=>'colour', 'attribute_value'=>$colour, 'sort_order'=>2,
								'created_date'=>date('Y-m-d'),
							));
						}
						if($material !== ''){
							$this->db->insert('db_variant_attributes', array(
								'store_id'=>$store_id, 'variant_id'=>$variant_id,
								'attribute_type'=>'material', 'attribute_value'=>$material, 'sort_order'=>3,
								'created_date'=>date('Y-m-d'),
							));
						}
					}
					$created++;
				}
			}
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('success', "Success! {$created} variants created, {$skipped} already existed.");
		return "success<<<###>>>{$created} created, {$skipped} skipped";
	}

}
