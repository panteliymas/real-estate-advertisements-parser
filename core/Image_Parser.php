<?php

class Image_Parser
{
	public $http;
	private $userAgent;
	private $url;

	public function __construct()
	{
		$this->http = new Http();
	}

	public function setUseragent($agent)
	{
		$this->userAgent = $agent;
		$this->http->setUseragent($agent);
	}

	/**
	 * @param $url - ссылка на загружаемый файл
	 * @param string $proxy_url - используется если для загрузки файла используется php скрипт fileGet.php
	 * @return bool|string
	 */
	public function fetchImage($url, $proxy_url='', $mark='')
	{
		$execute_url = ($proxy_url == '') ? $url : $proxy_url;
		$this->url = $url;
		$this->http->execute($execute_url);
		if (!$this->http->error) {
			$images_patch = ROOT_DIR . 'img/';
			//echo "\nFetch image http result:\n".$this->http->result."\n";
			$file_dir = Helper::randomString(5);
			$file_dir{1} = '/';
			$file_dir{3} = '/';
			$file_dir .= '/';
			if(!empty($mark)) $file_dir = $mark.'/'.$file_dir;

			$file_dir_full = $images_patch . $file_dir;
			$file_name = $this->file_force_contents($file_dir_full, $this->http->result);
			$file_path = $file_dir_full . $file_name;
			if (!$file_name) {
				echo "\nfetch image badfilename\n";
				Router::$controller->log_error(__LINE__, '', 'Cannot write image to file');
				return false;
			}
			//echo "fetch image $file_path\n";
			if (!$this->is_image_file($file_path)) {
				echo "\nfetch image bad file_path or not image\n";
				Router::$controller->log_error(__LINE__, '', 'Not image file '.$url);
				@unlink($file_path);
				return false;
			}
			return $file_dir . $file_name;
		} else {
			$comment = $this->http->error;
			Router::$controller->log_error(__LINE__, '', $comment);
		}
		return false;
	}

	private function is_image_file($file_path)
	{
		$is = @getimagesize($file_path);
		if (!$is) return false;
		elseif (!in_array($is[2], array(1, 2, 3))) return false; // 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(байтовый порядок intel), 8 = TIFF(байтовый порядок motorola), 9 = JPC, 10 = JP2, 11 = JPX.
		else return true;
	}

	private function file_force_contents($path, $contents)
	{
		$url_val = pathinfo(parse_url($this->url, PHP_URL_PATH));
		$filename = Helper::toTranslit($url_val['filename']);
		$extension = Helper::toTranslit($url_val['extension']);
		$basename = $filename . '.' . $extension;

		if (!is_dir($path) || !file_exists($path)) {
			$old = umask(0);
			@mkdir($path, 0777, true);
			umask($old);

			/*$dirs = explode('/' , $path);
			$count = count($dirs);
			$path = '';
			$mode = 0777;
			for ($i = 0; $i < $count; ++$i) {
				$path .= DIRECTORY_SEPARATOR . $dirs[$i];
				if (!is_dir($path))
				{
					mkdir($path, $mode);
				}
			}*/
		}

		if (!is_dir($path) || !file_exists($path)) {
			return false;
		}

		while (file_exists($path . $basename) == true) {
			$basename = $filename . '-' . Helper::randomString(mt_rand(1, 8)) . '.' . $extension;
		}

		$fp = fopen($path . $basename, 'wb');
		if ($fp) {
			$nb1=fwrite($fp, $contents);
			//echo "\n$nb1 byte written\n";
			fclose($fp);
			chmod($path . $basename, 0666);
			if(!empty($nb1)) {
				if(function_exists('imagecreatefromwebp')) {
					$path_info = pathinfo($basename);
					if ($path_info['extension'] == 'webp') {
						$im = imagecreatefromwebp($path . $basename);
						imagejpeg($im, $path . $basename . '.jpg', 100);
						imagedestroy($im);
						@unlink($path . $basename);
						$basename = $basename . '.jpg';
					}
				}
				return $basename;
			}else {
				Router::$controller->log_error(__LINE__, '', 'fwrite returned false');
				return false;
			}
		}
		return false;
	}

	/** $image - мя файла для обрезки
	 * $dh - количество пикселей (% от размера) для обрезки.
	 * $dh > 1 обрезка сверху в пикселях; $dh < 1 обрезка снизу в пикселях
	 * $dh < 1 AND $dh < 1 обрезка сверху в %; $dh < 0 AND dh > -1 обрезка снизу в %
	 * @return bool
	 **/
	public function crop($image, $dh)
	{
		list($w_i, $h_i, $type) = getimagesize($image); // Получаем размеры и тип изображения (число)
		$types = array("", "gif", "jpeg", "png"); // Массив с типами изображений
		$ext = $types[$type]; // Зная "числовой" тип изображения, узнаём название типа
		if ($ext) {
			$func = 'imagecreatefrom' . $ext; // Получаем название функции, соответствующую типу, для создания изображения
			$img_i = $func($image); // Создаём дескриптор для работы с исходным изображением
		} else {
			echo 'wrong image type'; // Выводим ошибку, если формат изображения недопустимый
			return false;
		}
		if (($dh > -1) AND ($dh < 1)) $dh = intval($h_i * $dh);
		$h_i = $h_i - abs($dh);

		$img_o = imagecreatetruecolor($w_i, $h_i); // Создаём дескриптор для выходного изображения
		if ($dh < 0) $dh = 0; // если обрезка снизу
		imagecopy($img_o, $img_i, 0, 0, 0, $dh, $w_i, $h_i); // Переносим часть изображения из исходного в выходное
		$func = 'image' . $ext; // Получаем функция для сохранения результата
		return $func($img_o, $image); // Сохраняем изображение в тот же файл, что и исходное, возвращая результат этой операции
	}
}