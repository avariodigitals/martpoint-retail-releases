<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Courses extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load_global();
        if(!mp_feature_enabled('courses')){
            show_404();
            return;
        }
        $this->load->model('course_model');
    }

    public function index(){
        $storeId = get_current_store_id();
        $data = $this->data;
        $data['page_title'] = 'Courses';
        $courses = $this->course_model->getCourses($storeId, 100);
        foreach($courses as $c){
            $c->item = $this->db->where('id', $c->item_id)->get('db_items')->row();
            $c->lesson_count = $this->db->where('course_id', $c->id)->where('is_published', 1)->count_all_results('db_course_lessons');
            $c->module_count = $this->db->where('course_id', $c->id)->where('status', 1)->count_all_results('db_course_modules');
            $c->student_count = $this->db->table_exists('db_course_enrollments') ? $this->db->where('course_id', $c->id)->where('status', 'active')->count_all_results('db_course_enrollments') : 0;
        }
        $data['courses'] = $courses;
        $data['unlinked_items'] = $this->_unlinked_course_items($storeId);
        $data['content'] = $this->load->view('courses/index', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    private function _unlinked_course_items($storeId){
        $linked = array_map(function($c){ return $c->item_id; }, $this->db->select('item_id')->where('store_id', $storeId)->where('status', 1)->get('db_courses')->result());
        $this->db->where('store_id', $storeId)->where('product_type', 'course')->where('status', 1);
        if(!empty($linked)) $this->db->where_not_in('id', $linked);
        return $this->db->get('db_items')->result();
    }

    public function add(){
        $storeId = get_current_store_id();
        $data = $this->data;
        $data['page_title'] = 'Add Course';
        $data['course'] = null;
        $data['preselect_item'] = (int)$this->input->get('item_id');
        $data['items'] = $this->db->where('store_id', $storeId)->where('product_type', 'course')->where('status', 1)->get('db_items')->result();
        $data['modules'] = [];
        $data['lessons'] = [];
        $data['content'] = $this->load->view('courses/form', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function edit($id = ''){
        $storeId = get_current_store_id();
        $id = (int)$id;
        $course = $this->course_model->get($id, $storeId);
        if(!$course){
            $this->session->set_flashdata('error', 'Course not found.');
            redirect('courses');
            return;
        }

        $data = $this->data;
        $data['page_title'] = 'Edit Course';
        $data['course'] = $course;
        $data['preselect_item'] = 0;
        $data['items'] = $this->db->where('store_id', $storeId)->where('product_type', 'course')->where('status', 1)->get('db_items')->result();
        $data['modules'] = $this->course_model->getModules($course->id);
        $data['lessons'] = $this->course_model->getLessons($course->id);
        $data['content'] = $this->load->view('courses/form', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function save($id = ''){
        $storeId = get_current_store_id();
        $id = (int)$id;

        $itemId = (int)$this->input->post('item_id', TRUE);
        $title = trim($this->input->post('title', TRUE));
        $description = trim($this->input->post('description', TRUE));
        $status = $this->input->post('status') ? 1 : 0;

        if(empty($itemId) || empty($title)){
            $this->session->set_flashdata('error', 'Item and Title are required.');
            $redirect = $id ? 'courses/edit/'.$id : 'courses/add';
            redirect($redirect);
            return;
        }

        $courseData = [
            'store_id'    => $storeId,
            'item_id'     => $itemId,
            'title'       => $title,
            'description' => $description,
            'status'      => $status
        ];

        $courseId = $this->course_model->saveCourse($courseData, $id ?: null);

        // Save modules (keyed by form key so new lessons can reference new modules)
        $modules = $this->input->post('modules', TRUE) ?? [];
        $moduleMap = [];   // form key => real module id
        $keptModules = [];
        foreach($modules as $key => $m){
            $mid = (int)($m['id'] ?? 0);
            $mData = [
                'course_id'  => $courseId,
                'title'      => trim($m['title'] ?? ''),
                'sort_order' => (int)($m['sort_order'] ?? 0),
                'status'     => 1
            ];
            if(empty($mData['title'])) continue;
            $realId = $this->course_model->saveModule($mData, $mid ?: null);
            $moduleMap[$key] = $realId;
            $keptModules[] = $realId;
        }

        // Save lessons
        $lessons = $this->input->post('lessons', FALSE) ?? [];
        $keptLessons = [];
        foreach($lessons as $l){
            $lid = (int)($l['id'] ?? 0);
            $moduleKey = $l['module_key'] ?? ($l['module_id'] ?? '');
            $realModuleId = $moduleMap[$moduleKey] ?? (int)$moduleKey;
            $lData = [
                'course_id'    => $courseId,
                'module_id'    => $realModuleId ?: null,
                'title'        => trim(strip_tags($l['title'] ?? '')),
                'content'      => $l['content'] ?? '',
                'video_url'    => trim(strip_tags($l['video_url'] ?? '')),
                'video_file'   => '',
                'sort_order'   => (int)($l['sort_order'] ?? 0),
                'is_published' => 1
            ];
            if(empty($lData['title'])) continue;
            $keptLessons[] = $this->course_model->saveLesson($lData, $lid ?: null);
        }

        // Soft-remove modules/lessons that were deleted in the builder
        if($id){
            $this->db->where('course_id', $courseId);
            if(!empty($keptModules)) $this->db->where_not_in('id', $keptModules);
            $this->db->update('db_course_modules', ['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

            $this->db->where('course_id', $courseId);
            if(!empty($keptLessons)) $this->db->where_not_in('id', $keptLessons);
            $this->db->update('db_course_lessons', ['is_published' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        }

        $this->session->set_flashdata('success', 'Course saved successfully.');
        redirect('courses/edit/'.$courseId);
    }

    public function delete($id = ''){
        $storeId = get_current_store_id();
        $id = (int)$id;
        $this->course_model->saveCourse(['status' => 0], $id);
        $this->session->set_flashdata('success', 'Course deleted.');
        redirect('courses');
    }
}
