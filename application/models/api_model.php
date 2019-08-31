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
}
?>
