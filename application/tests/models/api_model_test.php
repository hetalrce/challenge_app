<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Api_model_test extends TestCase 
{
    public function setUp()
	{
		$this->resetInstance();
		$this->CI->load->model('Api_model');
		$this->obj = $this->CI->Api_model;
    }
    
    public function test_add_file() {
        $file_data = [
            'url' => 'test.png'
        ];
        
        $output = $this->obj->add_file($file_data);

        $this->assertInternalType("int", $output);
    }

    function test_add_queue() {
        $file_data = [
            'url' => 'test.png',
            'file_id' => 1
        ];
        
        $output = $this->obj->add_queue($file_data);

        $this->assertTrue($output);
    }
}
?>
