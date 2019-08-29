<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Consumer_model extends CI_Model {
    function get_queue_data() {
        if ($query = $this->db->get('queue')) {
            return $query->result();
        } else {
            return false;
        }
    }

    function delete_queue_data($id) {
        if ($this->db->delete('queue', array('file_id' => $id))) {
            return true;
        } else {
            return false;
        }
    }
}
?>
