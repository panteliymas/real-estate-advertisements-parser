<?php

define("COLOR_BLACK", 1);
define("COLOR_WHITE", 0);

class PhpOcr
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

		$result = '';

		$column = 0;
		$columns = array();
		for ($x = 0; $x < $this->width; $x++) {
			$img_column = array();
			$is_empty = true;
			for ($y = 0; $y < $this->height; $y++) {
				$img_column[$y] = $this->getBWcolor($this->im, $x, $y);
				if ($img_column[$y] == COLOR_BLACK) {
					$is_empty = false;
				}
			}

			if ($is_empty) {
				$columns = array();
				$column = 0;
				continue;
			}

			foreach ($this->letters as $letter => $data) {

				$is_valid = true;

				if (empty($data[$column])) continue;
				foreach ($data[$column] as $index => $value) {
					// $index  - 0 - 15 номер строки картинки
					// $columns_tpl - массив коллонок (колонка => 0 или 1)
					if ($value != $img_column[$index]) {
						$is_valid = false;
					}
				}


				if ($is_valid) {
					if (!isset($columns[$letter])) $columns[$letter] = 0;
					$columns[$letter]++;
				}
			}

			$column++;

			foreach ($columns as $found_letter => $found_count) {
				//var_dump(($this->letters[$found_letter][0]));
				//var_dump($found_count);
				if (count($this->letters[$found_letter]) == $found_count) {
					$result .= $found_letter;
					$columns = array();
					$column = 0;
					break;
				}
			}
		}
		return $result;
	}


	public function setDatabaseDir($dir)
	{
		$this->dir = $dir;
	}

	public function prepare()
	{
		if ($handle = opendir($this->dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file == '.' || $file == '..')
					continue;

				$pathinfo = pathinfo($file);
				$pathinfo['filename'];

				$lines = file($this->dir . '/' . $pathinfo['basename']);

				foreach ($lines as $line_num => $line) {
					//$line = preg_replace('/^[ 8]/', '', $line);
					$lines[$line_num] = $line;
					if ($line == '') unset($lines[$line_num]);
				}
				$line_count = count($lines);
				$length = strlen($lines[0]);

				$rows = array();
				for ($y = 0; $y < $length; $y++) {
					$row = array();
					for ($x = 0; $x < $line_count; $x++) {
						if ($lines[$x]{$y} == ' ') {
							$row[] = COLOR_WHITE;
						} elseif ($lines[$x]{$y} == '8') {
							$row[] = COLOR_BLACK;
						}
					}
					if (count($row)) {
						$rows[] = $row;
					}
				}
				$letters[$pathinfo['filename']] = $rows;
			}
		}
		$this->letters = $letters;
	}

	public function createParts($img_file)
	{
		$this->im = $this->createImageFromFile($img_file);

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
					file_put_contents('parts/' . $x . '.txt', $result);
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
		if (($colorInfo['red'] > 200 || $colorInfo['green'] > 200 || $colorInfo['blue'] > 200) && $colorInfo['alpha'] > 10) {
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
