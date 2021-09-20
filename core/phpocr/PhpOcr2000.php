<?php

define("COLOR_BLACK", 1);
define("COLOR_WHITE", 0);

class PhpOcr2000
{
	private $dir = '';
	private $width = 0;
	private $height = 0;
	private $im = null;
	private $letters = array();

	public function recognition($img_file)
	{
		$this->prepare();

		$this->im = $this->createImageFromFile($img_file);

		if (!$this->im) {
			return false;
		}

		$this->removeBG();

		$letters_ocr = array();
		$rows = array();
		for ($x = 0; $x < $this->width; $x++) {
			$row = array();
			for ($y = 0; $y < $this->height; $y++) {
				$row[$y] = $this->getBWcolor($this->im, $x, $y);
			}
			$is_empty = true;
			foreach ($row as $index => $color) {
				if ($color) {
					$is_empty = false;
					break;
				}
			}

			if ($is_empty) {
				if (count($rows)) {
					$result = '';
					$items = count($rows[0]);
					$cols = count($rows);
					for ($xx = 0; $xx < $items; $xx++) {
						for ($yy = 0; $yy < $cols; $yy++) {
							$result .= ($rows[$yy][$xx]) ? COLOR_BLACK : COLOR_WHITE;
						}
						$result .= "\n";
					}

					$result_rows = explode("\n", $result);
					$result_array = array();
					foreach($result_rows as $result_row) {
						if (intval($result_row) == 0)  {
							continue;
						}
						$result_array[] .= $result_row;
					}
					$letters_ocr[] = $result_array;
				}
				$rows = array();
			} else {
				$rows[] = $row;
			}
		}

		//print_r($letters_ocr);

		$letters_result = array();
		foreach($letters_ocr as $letter_ocr) {
			$letter_result = array();
			if (strlen($letter_ocr[0]) < 3) continue;
			foreach($letter_ocr as $row_num => $letter_ocr_row) {
				foreach($this->letters as $file_name => $rows) {
					$index_count = strlen($rows[0]);
					if (empty($letter_result[$file_name])) $letter_result[$file_name] = array();
					$row = $rows[$row_num];
					for($i=0; $i<$index_count; $i++) {
						if ($letter_ocr_row{$i} == $row{$i} && $row{$i} == 1) {
							$letter_result[$file_name]['found'] ++;
						}
						if ($letter_ocr_row{$i} != $row{$i} && $row{$i} == 0) {
							$letter_result[$file_name]['found']--;
						}

						if ($row{$i} == 1) {
							$letter_result[$file_name]['positive'] ++;
						}
					}
				}
			}

			$letter_avg = array();
			foreach($letter_result as $letter => $data) {
				if ($data['found'] < 0) $data['found'] = 0;
				$letter_avg[$letter] = $data['positive'] - $data['found'];
			}
			asort($letter_avg, SORT_NUMERIC);

			$result = key($letter_avg);
			//print_r($letter_avg);

			$result_parts = explode('-', $result);
			$letters_result[] = $result_parts[0];
		}

		return implode('', $letters_result);
	}


	public function setDatabaseDir($dir)
	{
		$this->dir = $dir;
	}

	public function prepare()
	{
		if ($handle = opendir($this->dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file == '.' || $file == '..' || is_dir($this->dir . '/'. $file)) {
					continue;
				}

				$pathinfo = pathinfo($file);
				$pathinfo['filename'];

				$lines = file($this->dir . '/' . $pathinfo['basename']);

				$rows = array();
				foreach ($lines as $line_num => $line) {
					$lines[$line_num] = $line;
					if ($line == '') continue;
					$length = strlen($line);
					$row = '';
					for ($x = 0; $x < $length; $x++) {
						$row .= $line{$x} == ' ' ? (string)COLOR_WHITE : (string)COLOR_BLACK;
					}
					$rows[] = $row;
				}
				$letters[$pathinfo['filename']] = $rows;
			}
		}
		$this->letters = $letters;
	}

	public function removeBG()
	{
		$rgb = imagecolorat($this->im, 0, 0);
		$colorBgInfo = imagecolorsforindex($this->im, $rgb);

		$color_white = imagecolorallocate($this->im, 255, 255, 255);

		for ($x = 0; $x < $this->width; $x++) {
			for ($y = 0; $y < $this->height; $y++) {
				$rgb = imagecolorat($this->im, $x, $y);
				$colorInfo = imagecolorsforindex($this->im, $rgb);
				if (
					(abs($colorBgInfo['red'] - $colorInfo['red']) < 45) &&
					(abs($colorBgInfo['green'] - $colorInfo['green']) < 45) &&
					(abs($colorBgInfo['blue'] - $colorInfo['blue']) < 45)
				) {
					imagesetpixel($this->im, $x, $y, $color_white);
				}
			}
		}
	}

	public function createParts($img_file)
	{
		$this->im = $this->createImageFromFile($img_file);

		$this->removeBG();

		imagepng($this->im, $this->dir . '1111.png');

		if (!$this->im) {
			return false;
		}
		$rows = array();

		for ($x = 0; $x < $this->width; $x++) {
			$row = array();
			for ($y = 0; $y < $this->height; $y++) {
				$row[$y] = $this->getBWcolor($this->im, $x, $y);
			}
			$is_empty = true;
			foreach ($row as $index => $color) {
				if ($color) {
					$is_empty = false;
					break;
				}
			}

			if ($is_empty) {
				if (count($rows)) {
					$result = '';
					$items = count($rows[0]);
					$cols = count($rows);
					for ($xx = 0; $xx < $items; $xx++) {
						for ($yy = 0; $yy < $cols; $yy++) {
							$result .= ($rows[$yy][$xx]) ? '8' : ' ';
						}
						$result .= "\n";
					}

					$result_rows = explode("\n", $result);
					$result_array = array();
					foreach($result_rows as $result_row) {
						if (trim($result_row) == '')  {
							continue;
						}
						$result_array[] .= $result_row;
					}
					$result = implode("\n", $result_array);

					file_put_contents($this->dir . '/parts/' . $x . '.txt', $result);
				}
				$rows = array();
			} else {
				$rows[] = $row;
			}
		}
	}

	/**
	 * Get pixels color, understund only black or white pixels
	 *
	 * @param image $im
	 * @param int $x
	 * @param int $y
	 * @return int
	 */
	private function getBWcolor($im, $x, $y)
	{
		$rgb = imagecolorat($im, $x, $y);
		$colorInfo = imagecolorsforindex($im, $rgb);

		//print_r($colorInfo);

		if ($colorInfo['red'] > 240 || $colorInfo['green'] > 240 || $colorInfo['blue'] > 240) {
			return COLOR_WHITE;
		}
		return COLOR_BLACK;
	}

	private function createImageFromFile($img_file)
	{
		$img = 0;
		$img_sz = getimagesize($img_file);
		switch ($img_sz[2]) {
			case 1:
				$img = imagecreatefromgif($img_file);
				break;
			case 2:
				$img = imagecreatefromjpeg($img_file);
				break;
			case 3:
				$img = imagecreatefrompng($img_file);
				break;
		}
		$this->width = $img_sz[0];
		$this->height = $img_sz[1];
		return $img;
	}
}
