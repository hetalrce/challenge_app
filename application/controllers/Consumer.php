<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once  dirname(dirname(dirname(__FILE__))). '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

class Consumer extends CI_Controller {

	public function __construct() 
	{
		parent::__construct();
	}

	public function index()
	{
		$config = $this->config->item('rabbitmq');
		$connection = new AMQPStreamConnection($config['host'], $config['port'],$config['user'], $config['pass']);
		$channel = $connection->channel();
		$channel->queue_declare('sync.download.done', false, false, false, false);

		echo " [*] Waiting for messages. To exit press CTRL+C\n";

		$callback = function ($msg) {
			echo ' [x] Received ', $msg->body, "\n";
			$queue = json_decode($msg->body, true);
			if ($this->downloadIt($queue['file_id'], $queue['url']) == 200) {
				echo ' [x] Download it ', $msg->body, "\n";
			}
		};

		$channel->basic_consume('sync.download.done', '', false, true, false, false, $callback);

		while ($channel->is_consuming()) {
			$channel->wait();
		}

		$channel->close();
		$connection->close();
	} 

	private function downloadIt($file_id, $file_url) 
	{
		set_time_limit(0);
		//The path & filename to save to.
		$save_to = 'download/' . basename ($file_url); 

		//Open file handler.
		$fp = fopen($save_to, 'w+');

		//If $fp is FALSE, something went wrong.
		if($fp === false){
			throw new Exception('Could not open: ' . $save_to);
		}

		//Create a cURL handle.
		$ch = curl_init($file_url);

		//Pass our file handle to cURL.
		curl_setopt($ch, CURLOPT_FILE, $fp);

		//Timeout if the file doesn't download after 50 seconds.
		curl_setopt($ch, CURLOPT_TIMEOUT, 500);

		curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		curl_setopt($ch, CURLOPT_NOPROGRESS, false );
		curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $download_size, $downloaded, $upload_size, $uploaded) use ($file_id) {
			$this->progressCallback($resource, $download_size, $downloaded, $upload_size, $uploaded, $file_id);
		});

		//Execute the request.
		curl_exec($ch);
		
		//If there was an error, throw an Exception
		if(curl_errno($ch)){
			throw new Exception(curl_error($ch));
		}

		//Get the HTTP status code.
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
		//Close the cURL handler.
		curl_close($ch);

		fclose($fp);

		return $statusCode;
	}

	private function progressCallback($resource, $download_size, $downloaded_size, $upload_size, $uploaded_size, $file_id)
    {
		$previous_progress = 0;
		$progress = 0;

        if($download_size > 0) {
            $progress =  round($downloaded_size / $download_size  * 100, 2); 
			$status = (($download_size - $downloaded_size) == 0 ) ? 'Completed' : 'Downloading' ;
			
			$response = [
				'file_size' => $download_size,
				'downloaded_size' => $downloaded_size,
				'remaining_size' =>  ($download_size - $downloaded_size),
				'status' => $status,
				'progress' => $progress . '%'
			];
		}
		
		if ( $progress > $previous_progress)
		{
			$previous_progress = $progress;
			$file = $file_id . '.txt';
			file_put_contents('queue/' . $file, serialize($response));
		}
    }
}
