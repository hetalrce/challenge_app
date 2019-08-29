<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Api_model extends CI_Model {
    function add_file($file_data) {
        if ($this->db->insert('files', $file_data)) {
            return $this->db->insert_id();
        } else {
            return false;
        }
    }

    function add_queue($file_data) {
        if ($this->db->insert('queue', $file_data)) {
            return true;
        } else {
            return false;
        }
    }
}
?>
