<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {

	var $table = 'db_category';
	var $column_order = array('category_code','category_name','description','status','store_id'); //set column field database for datatable orderable
	var $column_search = array('category_code','category_name','description','status','store_id'); //set column field database for datatable searchable 
	var $order = array('id' => 'desc'); // default order 

    public function __construct(){parent::__construct();$this->load->model('category_tree_model');}
    private function hierarchy_write($method){
        $this->db->trans_begin();$this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[get_current_store_id()]);$this->category_tree_model->clear();
        try{$result=$this->$method();if($result==='success'&&$this->db->trans_status())$this->db->trans_commit();else $this->db->trans_rollback();return $result;}catch(Throwable $e){$this->db->trans_rollback();log_message('error','Category save: '.$e->getMessage());return 'Could not save this category.';}
    }
    public function verify_and_save(){return $this->hierarchy_write('_verify_and_save');}
    public function update_category(){return $this->hierarchy_write('_update_category');}
    private function parent_input($id=0){
        $sid=get_current_store_id();$parent=$this->input->post('parent_id');
        if($id){$row=$this->db->where('id',(int)$id)->where('store_id',$sid)->get('db_category')->row();if(!$row)throw new InvalidArgumentException('Category not found.');if($parent===null)$parent=$row->parent_id??0;}
        return $this->category_tree_model->validate_parent($sid,$id,$parent??0);
    }

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


	private function _verify_and_save(){
		$category = $this->input->post('category', TRUE);
		$description = $this->input->post('description', TRUE);
        try{$parent_id=$this->parent_input(isset($q_id)?(int)$q_id:0);}catch(InvalidArgumentException $e){return $e->getMessage();}
		
		//Validate This category already exist or not
		$store_id=get_current_store_id();
		$this->db->where("upper(category_name)", strtoupper($category));
		$this->db->where('store_id', $store_id);
		$query = $this->db->get('db_category');
		if($query->num_rows()>0){
			return "This Category Name already Exist.";
			
		}
		else{
			$category_image='';
			if(!empty($_FILES['category_image']['name'])){
				$uploadDir = FCPATH . 'uploads/categories/';
				if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
				$config['upload_path']          = $uploadDir;
				$config['allowed_types']        = 'gif|jpg|jpeg|png|webp';
				$config['max_size']             = 2048;
				$config['max_width']            = 2000;
				$config['max_height']           = 2000;
				$this->load->library('upload');
				$this->upload->initialize($config);
				if(!$this->upload->do_upload('category_image')){
					return $this->upload->display_errors();
				}
				else{
					$category_image='uploads/categories/'.$this->upload->data('file_name');
				}
			}

			$info = array(
							'count_id' 				=> get_count_id('db_category'), 
		    				'category_code' 		=> get_init_code('category'), 
		    				'category_name' 		=> $category,
		    				'description' 			=> $description,
                            'parent_id' => $parent_id,
		    				'category_image' 		=> $category_image,
			    				'status' 				=> 1,
		    			);
			
			$info['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();	

			$q1 = $this->db->insert('db_category', $info);
			$this->category_tree_model->clear();
			if ($q1){
					$this->session->set_flashdata('success', 'Success!! New Category Added Successfully!');
			        return "success";
			}
			else{
			        return "failed";
			}
		}
	}

	//Get category_details
	public function get_details($id,$data){
		//Validate This category already exist or not
		$query=$this->db->where('id',(int)$id)->where('store_id',get_current_store_id())->get('db_category');
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
			$data['category_name']=$query->category_name;
			$data['description']=$query->description;
			$data['category_image']=$query->category_image;
            $data['parent_id']=$query->parent_id??0;
			$data['store_id']=$query->store_id;
			return $data;
		}
	}
	private function _update_category(){
		$q_id = $this->input->post('q_id', TRUE);
        $existing=$this->db->where('id',(int)$q_id)->where('store_id',get_current_store_id())->get('db_category')->row();
		$category = $this->input->post('category', TRUE);
		$description = $this->input->post('description', TRUE);
        try{$parent_id=$this->parent_input(isset($q_id)?(int)$q_id:0);}catch(InvalidArgumentException $e){return $e->getMessage();}

		//Validate This category already exist or not
		$store_id=get_current_store_id();
		$this->db->where("upper(category_name)", strtoupper($category));
		$this->db->where("id !=", $q_id);
		$this->db->where('store_id', $store_id);
		$query = $this->db->get('db_category');
		if($query->num_rows()>0){
			return "This Category Name already Exist.";
			
		}
		else{
			$category_image=$existing->category_image??'';
			if(!empty($_FILES['category_image']['name'])){
				$uploadDir = FCPATH . 'uploads/categories/';
				if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
				$config['upload_path']          = $uploadDir;
				$config['allowed_types']        = 'gif|jpg|jpeg|png|webp';
				$config['max_size']             = 2048;
				$config['max_width']            = 2000;
				$config['max_height']           = 2000;
				$this->load->library('upload');
				$this->upload->initialize($config);
				if(!$this->upload->do_upload('category_image')){
					return $this->upload->display_errors();
				}
				else{
					$category_image='uploads/categories/'.$this->upload->data('file_name');
				}
			}

			$info = array(
		    				'category_name' 		=> $category,
		    				'description' 			=> $description,
                            'parent_id' => $parent_id,
		    				'category_image' 		=> $category_image,
		    			);
			$info['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();
			$q1 = $this->db->where('id',$q_id)->where('store_id',get_current_store_id())->update('db_category', $info);
			$this->category_tree_model->clear();
			if ($q1){
					$this->session->set_flashdata('success', 'Success!! Category Updated Successfully!');
			        return "success";
			}
			else{
			        return "failed";
			}
		}
	}
	public function update_status($id,$status){
        if (set_status_of_table($id,$status,'db_category')){
            echo "success";
        }
        else{
            echo "failed";
        }
	}
	public function delete_categories_from_table($ids){
        $ids=array_values(array_unique(array_filter(array_map('intval',explode(',',(string)$ids)))));if(!$ids){echo 'No categories selected';return;}$sid=get_current_store_id();
        if($this->db->where_in('id',$ids)->where('store_id',$sid)->count_all_results('db_category')!==count($ids)){echo 'Category not found';return;}
        if($this->db->where('store_id',$sid)->where_in('parent_id',$ids)->count_all_results('db_category')){echo 'Move or remove subcategories before deleting their parent.';return;}
        foreach(['db_items','db_services'] as $table)if($this->db->where('store_id',$sid)->where_in('category_id',$ids)->count_all_results($table)){echo 'This category is used by products or services.';return;}
        $ok=$this->db->where('store_id',$sid)->where_in('id',$ids)->delete('db_category');$this->category_tree_model->clear();echo $ok?'success':'failed';
    }
}
