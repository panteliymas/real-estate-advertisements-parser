<?php

class Parser
{
	public function clearSpaces($text)
	{
		$text = str_replace(array("\n", "\r"), ' ', $text);
		$text = str_replace(chr(160), " ", $text); // non-breaking space character
		$text = preg_replace('/\s[\s]+/', ' ', $text); // Strip multiple spaces
		return $text;
	}

	public static function getRoomsCount($text)
	{
		$patterns = array(
			'#(\d{1,2})[-\sх]*комн#si',
			'#(\d{1,2})[-\sх]*к[\/\\\.](к|кв)[\/\\\.,;\s]#si',
			'#(\d{1,2})[-\sх]*кімнат#si',
		);
		$text = Helper::cleanAttributeText($text);

		$result_parsing = array();
		foreach ($patterns as $pattern) {
			preg_match_all($pattern, $text, $results);
			foreach ($results[0] as $result) {
				$result_parsing[] = $result;
			}
		}

		foreach ($result_parsing as $result) {
			$result = intval($result);
			if ($result > 0 && $result < 20) {
				return $result;
			}
		}

		$cats = array(
			'однокомнатн' => 1,
			'двухкомнатн' => 2,
			'трехкомнатн' => 3,
			'четырехкомн' => 4,
			'пятикомнатн' => 5,
			'шестикомнатн' => 6,
		);

		foreach ($cats as $cat_name => $rooms) {
			if (strpos($text, $cat_name) !== false) {
				return $rooms;
			}
		}

		return 0;
	}
}