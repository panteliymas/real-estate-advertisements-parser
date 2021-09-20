<?php

// Другие альтернативы OCR
// https://help.ubuntu.com/community/OCR

require_once 'tesseract_ocr.php';

class PhoneOcr
{
	private $char_table_from = array();
	private $char_table_to = array();
	private $gocr_path = '';
	public $http = null;
	private $userAgent;
	private $threshold = 0 ;

	public function __construct($is_http_new = false)
	{
		$replasement = array(
			'O' => '0', // агнл
			'О' => '0', // русс
			'S' => '5',
			'l' => '1', // англ L
			'Q' => '0', // англ L
			'D' => '0', // англ L
			'j' => '1', // англ
			'J' => '1', // англ
			'B' => '8', // англ
			'В' => '8', // русс
			'.' => ',',
		);

		$this->char_table_from = array_keys($replasement);
		$this->char_table_to = array_values($replasement);

		if ($is_http_new) {
			$this->http = new Http();
		}
	}

	public function fetchAndOcr($image_url, $delete_file = true)
	{
		if ($this->http == null) {
			$this->http = Router::getController()->http;
		}

		$result_temp = $this->http->result;

		$this->http->execute($image_url);
		if (!$this->http->error) {
			$path = ROOT_DIR . 'tmp/';

			$file_name = Helper::getFreeFileName($path, $image_url);

			$fp = fopen($path . $file_name, 'wb');
			if ($fp) {
				fwrite($fp, $this->http->result);
				fclose($fp);
				chmod($path . $file_name, 0755);
			}
			$phone = $this->phone($path . $file_name);
			if ($delete_file) {
				@unlink($path . $file_name);
			}
			$this->http->result = $result_temp;
			return $phone;
		}
		$this->http->result = $result_temp;
		return '';
	}

	public function imageOcr($image_content, $image_url)
	{
		//var_dump($image_content);
		$dir = ROOT_DIR . 'tmp/';
		$file_name = Helper::getFreeFileName($dir, $image_url);
		$phone = '';
		$resource = fopen($dir . $file_name, 'wb');
		if ($resource) {
			echo "\nGocr image of size: ".fwrite($resource, $image_content);
			//echo "\nmd5: ".md5($image_content)."\n\n";
			fclose($resource);
			chmod($dir . $file_name, 0755);
			echo "\nGocr image: ".$dir . $file_name."\n";
			$phone = $this->phone($dir . $file_name);
			//@unlink($dir . $file_name);
		}else{
			echo "\nGocr failed!\n";
		}

		return $phone;
	}

	public function setTthreshold($threshold) {
		$this->threshold = $threshold;
	}

	public function phone($image_file)
	{
		$filename_pnm = $image_file . '.pnm';

		$image = new Imagick($image_file);
		$image->setImageFormat('pnm');
		$this->threshold = 0;//0.5;
		if ($this->threshold != 0) {
			$max = $image->getQuantumRange();
			$max = $max["quantumRangeLong"];
			$image->thresholdImage($this->threshold * $max);
		}
		$image->trimImage(0);
		$image->writeImage($filename_pnm);

		$command = '/usr/local/bin/gocr' . ' -i ' . escapeshellarg($filename_pnm) . ' -a 30 -C ' . escapeshellarg('1234567890();,.+--');
		$detected = trim(shell_exec($command));
		
		@unlink($filename_pnm);
		
		return $detected;

/*
		$detected = TesseractOCR::recognize($image_file, range(0,9), '();,.+--');

		$detected = str_replace($this->char_table_from, $this->char_table_to, $detected);
		return $detected;*/

	}

	public function setGocrPath($gocr_path)
	{
		$this->gocr_path = $gocr_path;
	}
}