<?php

class TesseractOCR
{
	static function recognize($originalImage)
	{
		$rand = uniqid();
		$tifImage = TesseractOCR::convertImageToTif($rand, $originalImage);
		$configFile = TesseractOCR::generateConfigFile($rand, func_get_args());
		$outputFile = TesseractOCR::executeTesseract($rand, $tifImage, $configFile);
		$recognizedText = TesseractOCR::readOutputFile($outputFile);
		TesseractOCR::removeTempFiles($tifImage, $outputFile, $configFile);
		return $recognizedText;
	}

	static function convertImageToTif($rand, $originalImage)
	{
		$tifImage = sys_get_temp_dir() . '/tesseract-ocr-tif-' . $rand . '.tif';
		exec("convert -colorspace gray +matte $originalImage $tifImage");
		return $tifImage;
	}

	static function generateConfigFile($rand, $arguments)
	{
		$configFile = sys_get_temp_dir() . '/tesseract-ocr-config-' . $rand . '.conf';
		exec("touch $configFile");
		$whitelist = TesseractOCR::generateWhitelist($arguments);
		if (!empty($whitelist)) {
			$fp = fopen($configFile, 'w');
			fwrite($fp, "tessedit_char_whitelist $whitelist");
			fclose($fp);
		}
		return $configFile;
	}

	static function generateWhitelist($arguments)
	{
		array_shift($arguments); //first element is the image path
		$whitelist = '';
		foreach ($arguments as $chars) $whitelist .= join('', (array)$chars);
		return $whitelist;
	}

	static function executeTesseract($rand, $tifImage, $configFile)
	{
		$outputFile = sys_get_temp_dir() . '/tesseract-ocr-output-' . $rand;

		//echo "tesseract $tifImage $outputFile nobatch $configFile 2> /dev/null";

		exec("tesseract $tifImage $outputFile nobatch $configFile 2> /dev/null");
		return $outputFile . '.txt'; //tesseract appends txt extension to output file
	}

	static function readOutputFile($outputFile)
	{
		return trim(file_get_contents($outputFile));
	}

	static function removeTempFiles()
	{
		//array_map("unlink", func_get_args());
	}
}
