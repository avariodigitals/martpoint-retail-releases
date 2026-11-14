<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course_model extends CI_Model {

    public function __construct(){
        parent::__construct();
    }

    // ============== COURSES ==============

    public function get($id, $storeId = null){
        $storeId = $storeId ?: get_current_store_id();
        return $this->db->where('id', $id)->where('store_id', $storeId)->where('status', 1)->get('db_courses')->row();
    }

    public function getByItemId($itemId, $storeId = null){
        $storeId = $storeId ?: get_current_store_id();
        return $this->db->where('item_id', $itemId)->where('store_id', $storeId)->where('status', 1)->get('db_courses')->row();
    }

    public function getCourses($storeId = null, $limit = 50, $offset = 0){
        $storeId = $storeId ?: get_current_store_id();
        return $this->db->where('store_id', $storeId)->where('status', 1)->order_by('id','desc')->limit($limit, $offset)->get('db_courses')->result();
    }

    public function saveCourse($data, $id = null){
        if($id){
            $this->db->where('id', $id)->update('db_courses', array_merge($data, ['updated_at' => date('Y-m-d H:i:s')]));
            return $id;
        }
        $this->db->insert('db_courses', array_merge($data, ['created_at' => date('Y-m-d H:i:s')]));
        return $this->db->insert_id();
    }

    // ============== MODULES ==============

    public function getModules($courseId){
        return $this->db->where('course_id', $courseId)->where('status', 1)->order_by('sort_order','asc')->order_by('id','asc')->get('db_course_modules')->result();
    }

    public function saveModule($data, $id = null){
        if($id){
            $this->db->where('id', $id)->update('db_course_modules', array_merge($data, ['updated_at' => date('Y-m-d H:i:s')]));
            return $id;
        }
        $this->db->insert('db_course_modules', array_merge($data, ['created_at' => date('Y-m-d H:i:s')]));
        return $this->db->insert_id();
    }

    // ============== LESSONS ==============

    public function getLessons($courseId, $moduleId = null){
        $this->db->where('course_id', $courseId);
        if($moduleId !== null){
            $this->db->where('module_id', $moduleId);
        }
        $this->db->where('is_published', 1)->order_by('sort_order','asc')->order_by('id','asc');
        return $this->db->get('db_course_lessons')->result();
    }

    public function getLesson($lessonId){
        return $this->db->where('id', $lessonId)->where('is_published', 1)->get('db_course_lessons')->row();
    }

    public function saveLesson($data, $id = null){
        if($id){
            $this->db->where('id', $id)->update('db_course_lessons', array_merge($data, ['updated_at' => date('Y-m-d H:i:s')]));
            return $id;
        }
        $this->db->insert('db_course_lessons', array_merge($data, ['created_at' => date('Y-m-d H:i:s')]));
        return $this->db->insert_id();
    }

    // ============== ENROLLMENTS ==============

    public function getEnrollment($customerId, $courseId, $storeId = null){
        $storeId = $storeId ?: get_current_store_id();
        return $this->db->where('customer_id', $customerId)->where('course_id', $courseId)->where('store_id', $storeId)->where('status','active')->get('db_course_enrollments')->row();
    }

    public function getEnrollmentsByCustomer($customerId, $storeId = null){
        $storeId = $storeId ?: get_current_store_id();
        return $this->db->where('customer_id', $customerId)->where('store_id', $storeId)->where('status !=','cancelled')->order_by('id','desc')->get('db_course_enrollments')->result();
    }

    public function enrollCustomer($customerId, $courseId, $itemId, $orderId, $storeId = null){
        $storeId = $storeId ?: get_current_store_id();
        $existing = $this->getEnrollment($customerId, $courseId, $storeId);
        if($existing) return $existing->id;
        $this->db->insert('db_course_enrollments', [
            'store_id'    => $storeId,
            'customer_id' => $customerId,
            'course_id'   => $courseId,
            'item_id'     => $itemId,
            'order_id'    => $orderId,
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s')
        ]);
        return $this->db->insert_id();
    }

    // ============== PROGRESS ==============

    public function getProgress($enrollmentId){
        return $this->db->where('enrollment_id', $enrollmentId)->where('is_completed', 1)->get('db_course_progress')->result();
    }

    public function getLessonProgress($enrollmentId, $lessonId){
        return $this->db->where('enrollment_id', $enrollmentId)->where('lesson_id', $lessonId)->get('db_course_progress')->row();
    }

    public function markLessonComplete($enrollmentId, $lessonId){
        $existing = $this->getLessonProgress($enrollmentId, $lessonId);
        if($existing) return $existing->id;
        $this->db->insert('db_course_progress', [
            'enrollment_id' => $enrollmentId,
            'lesson_id'     => $lessonId,
            'is_completed'  => 1,
            'completed_at'  => date('Y-m-d H:i:s'),
            'created_at'    => date('Y-m-d H:i:s')
        ]);
        return $this->db->insert_id();
    }

    public function getCourseProgressPercent($courseId, $enrollmentId){
        $total = $this->db->where('course_id', $courseId)->where('is_published', 1)->count_all_results('db_course_lessons');
        if($total <= 0) return 0;
        $completed = count($this->getProgress($enrollmentId));
        return round(($completed / $total) * 100);
    }
}
