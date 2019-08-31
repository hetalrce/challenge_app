<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require_once  dirname(dirname(dirname(__FILE__))). '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Api extends REST_Controller {

	public function __construct() 
	{
		parent::__construct();
		$this->load->model('api_model');
	}

	public function download_post()
	{
		$input = $this->input->post();

		if ($insert_id = $this->api_model->add_file($input)) {
			$input['file_id'] = $insert_id;
				$this->send(json_encode($input));
				$response = [
					'id' => $insert_id,
					'message' => 'URL added successfully',
				];	
				$this->response($response, REST_Controller::HTTP_OK, TRUE);
		} else {
			$this->response(['message' => 'Something went wrong.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR, FALSE);
		}
	} 
	
	public function status_get()
	{
		$id = $this->input->get('id');
		
		$file = 'queue/' . $id . '.txt';
		$data = [];
	    if (!empty($id) && file_exists($file)) { 
			$data = unserialize(file_get_contents($file));
		}

		if (empty($data)) {
			$data['message'] = 'No data found';
		} 

		$this->response($data, REST_Controller::HTTP_OK);
	}

	/**
	 * @param string $message
     */
	private function send(string $message) 
	{
		$config = $this->config->item('rabbitmq');
		$connection = new AMQPStreamConnection($config['host'], $config['port'],$config['user'], $config['pass']);
	
		$channel = $connection->channel();
		$channel->queue_declare('sync.download.done', false, false, false, false);

		$msg = new AMQPMessage($message);
		$channel->basic_publish($msg, '', 'sync.download.done');
		
		$channel->close();
		$connection->close();
	}
}
