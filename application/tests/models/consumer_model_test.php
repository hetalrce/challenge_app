<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Consumer_model_test extends TestCase 
{
    public function setUp()
	{
		$this->resetInstance();
		$this->CI->load->model('Consumer_model');
		$this->obj = $this->CI->Consumer_model;
    }
    
    public function test_get_queue_data() {
        $this->obj->get_queue_data();
        
        $this->assertResponseCode(200);
    }

    // function test_add_queue() {
    //     $file_data = [
    //         'url' => 'test.png',
    //         'file_id' => 1
    //     ];
        
    //     $output = $this->obj->add_queue($file_data);

    //     $this->assertTrue($output);
    // }
}
?>
