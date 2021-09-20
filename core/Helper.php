<?php

final class Helper
{
	public static function randomString($length)
	{
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$chars_length = (strlen($chars) - 1);
		$string = '';
		for ($i = 0; $i < $length; $i++) {
			$pos = mt_rand(0, $chars_length);
			$string .= $chars{$pos};
		}
		return $string;
	}

	public static function toTranslit($string)
	{
		$string = mb_strtolower($string, mb_detect_encoding($string));
		$string = preg_replace("/-{2,}/", "-", $string);
		$keys = array('а', 'б', 'в', 'г', 'ґ', 'д', 'е', 'ё', 'з', 'и', 'і', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ы', 'э');
		$values = array('a', 'b', 'v', 'g', 'g', 'd', 'e', 'e', 'z', 'i', 'y', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'i', 'e');
		$string = str_replace($keys, $values, $string);
		$string = str_replace(' ', '-', $string);
		$string = strtr($string,
			array(
				"ж" => "zh", "ц" => "ts", "ч" => "ch", "ш" => "sh",
				"щ" => "shch", "ь" => "", "ю" => "yu", "я" => "ya",
				"Ж" => "ZH", "Ц" => "TS", "Ч" => "CH", "Ш" => "SH",
				"Щ" => "SHCH", "Ь" => "", "Ю" => "YU", "Я" => "YA",
				"ї" => "i", "Ї" => "Yi", "є" => "ie", "Є" => "Ye"
			)
		);
		$string = trim(preg_replace("/[^0-9a-z_\-.]+/", "", $string));
		return $string;
	}

	public static function cleanAttributeText($attribute)
	{
		$attribute = strip_tags(htmlspecialchars_decode($attribute));
		$attribute = str_replace(chr(160), " ", $attribute);
		$attribute = trim(preg_replace('/\s[\s]+/', ' ', $attribute));
		return $attribute;
	}

	public static function getFreeFileName($path_directory, $file_url)
	{
		$url_val = pathinfo(parse_url($file_url, PHP_URL_PATH));
		$filename = Helper::toTranslit($url_val['filename']);
		$extension = Helper::toTranslit($url_val['extension']);
		$basename = $filename . '.' . $extension;

		while (file_exists($path_directory . $basename) == true) {
			$basename = $filename . '-' . Helper::randomString(mt_rand(1, 8)) . '.' . $extension;
		}
		return $basename;
	}

	public static function parse_extra_fields($extra_fields)
	{
		$extra_fields_array = array();
        //-- Странная проблема - в некоторых случаях три подряд вертикальных черты
		$extra_fields = str_replace('|||', '||', $extra_fields);
		$rows = explode('||', $extra_fields);
		foreach ($rows as $row) {
			$parts = explode('|', $row);
			if (count($parts) == 2) {
				$key = str_replace("&#124;", "|", $parts[0]);
				$value = str_replace("&#124;", "|", $parts[1]);
				$extra_fields_array[$key] = $value;
			}
		}
		return $extra_fields_array;
	}

	public static function export_text($text)
	{
		$text = strip_tags($text);
		$order = array("\r\n", "\n", "\r");
		$replace = ' ';
		$text = trim(str_replace($order, $replace, $text));
		$text = htmlspecialchars_decode($text);
		return $text;
	}

	public static function findEmail($text)
	{
		// http://forums.phpfreaks.com/topic/142046-solved-extract-email-address-from-string/?p=743827
		$pattern = '/([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9])*)+(\.(([a-z0-9])([-a-z0-9_-])?([a-z0-9]){1,5}))/si';

		$emails = array();
		if (preg_match_all($pattern, $text, $matches)) {
			foreach ($matches[0] as $key => $value) {
				$emails[] = $value;
			}
		}
		return $emails;
	}

	public static function encodeExtraRow($key, $value)
	{
		$key = str_replace("|", "&#124;", Helper::cleanAttributeText($key));
		$value = str_replace("|", "&#124;", Helper::cleanAttributeText($value));
		return $key . '|' . $value;
	}

	public static function encodeExtraFields($extra_fields)
	{
		$result = array();
		foreach ($extra_fields as $extra_key => $extra_val) {
			$result[] = self::encodeExtraRow($extra_key, $extra_val);
		}
		return implode('||', $result);
	}

	public static function decodeExtraRow($data)
	{
		$data = explode('|', $data);

		$key = str_replace("&#124;", "|", $data[0]);
		$value = str_replace("&#124;", "|", $data[1]);
		if (empty($key)) {
			return array();
		}
		return array($key => $value);
	}

	public static function decodeExtraFields($extra_fields)
	{
		$extra_fields = str_replace('|||', '||', $extra_fields);
		$rows = explode('||', $extra_fields);
		$result = array();
		foreach ($rows as $row) {
			$result = array_merge($result, self::decodeExtraRow($row));
		}
		return $result;
	}

	// только текст и первые $count_word слов
	public static function onlyText($text, $count_word = 3)
	{
		$text = preg_replace("/[^\p{Cyrillic}\p{Latin} ]/i", "", $text);

		$text = strip_tags($text);
		$words = explode(' ', $text);
		$res = '';
		$count = 0;
		foreach ($words as $word) {
			$count++;
			if ($count > $count_word) break;
			$res .= ' ' . $word;
		}
		$res= preg_replace("/\s{2,}/"," ",$res);
		return trim($res);
	}
}