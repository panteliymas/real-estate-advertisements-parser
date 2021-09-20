<?php

include_once(ROOT_DIR . 'core/Image/Loader.php');
require ROOT_DIR . '/core/City_Selector.php';

class ria_ua extends Core
{
	public $website_url = 'https://dom.ria.com/';
	private $city_url = '';
	public $def_city = '';
	public $crop_bottom = 0;
	protected $page = 1;
	protected $last_date_add = '';
	protected $adv_short = array();
	protected $adv_processed_max = 500;
	protected $adv_processed = 0;
	protected $db_id_new;
	protected $id;
	protected $updated_at;
	protected $cookie_file;
	protected $request_headers;
	protected $consider_area_id = false;
	protected $use_proxy = true; // для тестирования можно выставить в false

	public function parse()
	{
		// php index.php --p=ria.ua
		$this->website_id = 11;
		echo "\nOk. Let's go!\n";
		$this->thread_id = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : 0; // thread_id соответствует индексу области
		$this->parser_before_run($this->thread_id);
		$this->http->setTimeout(65); // сайт жестко тормозит
		$this->http->is_proxy = $this->use_proxy;
		$this->http->is_secure = $this->use_proxy; //exper
		//$this->http->is_payProxy = true;
		$this->http->is_payProxy = rand(5, 15) > 10; 
		$this->http->use_index = 3;
		$this->http->useCurl(true);
		$this->http->useCookie(true);
		$this->request_headers = array(
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4,uk;q=0.2',
			'Accept-Encoding: gzip,deflate',
			'Connection' => 'keep-alive',
		);
		$this->http->setUseragent('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36');
		$this->loadImageParser();
		$this->http->cookiePath = ROOT_DIR . 'cookies/website-' . $this->website_id . '-' . $this->thread_id . '.txt';
		if( file_exists($this->http->cookiePath) ) unlink($this->http->cookiePath);


		// ==================================
		//$url = 'realty-perevireno-prodaja-kvartira-nikolaev-leski-generala-karpenko-18256108.html';
		//$proxy = $this->http->get_proxy_pay();
		//$phones = $this->getPhoneRia($proxy, 'https://dom.ria.com/ru/' . $url);
		//$this->http->clear_use_proxy_pay($proxy);
		//var_dump($phones);
		//exit;


		// Перечень областей
		// https://developers.ria.com/dom/states?api_key=VmpMIOQeVZnWKJW7f6V5DiorFL302Trm5hREZyKE
		// перечень городов
		// https://developers.ria.com/dom/cities/20?api_key=VmpMIOQeVZnWKJW7f6V5DiorFL302Trm5hREZyKE
		//										  |-------- номер области
		// Перечень районов
		// https://developers.ria.com/dom/cities_districts/1?api_key=VmpMIOQeVZnWKJW7f6V5DiorFL302Trm5hREZyKE
		//										  		   |-------- номер города
		// [10, 10, 12, 'Киев', 1],
		// 	|	 |	 |     |    |--- группа запуска
		//  |    |   |     |-------- город
		//  |    |   |-------------- район города - село в районе (например Каролино-Бугаз, Овидиопольский район, Одесская обл.) Если 0 - то не учитывается
		//  |    |------------------ код города
		//  |----------------------- код области
		$city_code=[
			[10, 10, 0, 'Киев', 1],
			//[10, 209, 0, 'Барышевка', 1],
			//[10, 210, 0, 'Белая Церковь', 1],
			//[10, 636, 0, 'Березань', 1],
			//[10, 211, 0, 'Богуслав', 1],
			[10, 212, 0, 'Борисполь', 1],
			//[10, 213, 0, 'Бородянка', 1],
			[10, 214, 0, 'Бровары', 1],
			[10, 612, 0, 'Буча', 1],
			[10, 215, 0, 'Васильков', 1],
			//[10, 217, 0, 'Володарка', 1],
			[10, 216, 0, 'Вышгород', 1],
			//[10, 218, 0, 'Згуровка', 1],
			//[10, 219, 0, 'Иванков', 1],
			[10, 220, 0, 'Ирпень', 1],
			//[10, 221, 0, 'Кагарлык',1],
			[10, 648, 0, 'Киево-Святошинский', 1],
			[10, 223, 0, 'Макаров', 1],
			//[10, 224, 0, 'Мироновка', 1],
			[10, 225, 0, 'Обухов', 1],
			[10, 226, 0, 'Переяслав-Хмельницкий', 1],
			//[10, 227, 0, 'Полесское', 1],
			//[10, 228, 0, 'Ракитное', 1],
			[10, 626, 0, 'Ржищев', 1],
			//[10, 229, 0, 'Сквирa', 1],
			[10, 230, 0, 'Славутич', 1],
			//[10, 231, 0, 'Ставище', 1],
			//[10, 232, 0, 'Тараща', 1],
			//[10, 233, 0, 'Тетиев', 1],
			//[10, 234, 0, 'Фастов', 1],
			//[10, 235, 0, 'Яготин', 1],

			[1, 1, 0, 'Винница', 16],

			[2, 2, 0, 'Житомир', 11],

			[3, 3, 0, 'Тернополь', 14],

			[4, 4, 0, 'Хмельницкий', 10],
			[4, 498, 0, 'Каменец-Подольский', 10],

			[5, 5, 0, 'Львов', 2],
			[5, 309, 0, 'Червоноград', 2],

			[6, 6, 0, 'Чернигов', 14],

			[7, 7, 0, 'Харьков', 3],
			[7, 456, 0, 'Изюм', 3], 			// for email

			[8, 8, 0, 'Сумы', 8],

			[9, 9, 0, 'Ровно', 2],

			[11, 11, 0, 'Днепр', 3],
			[11, 76, 0, 'Кривой Рог', 3],
			[11, 72, 0, 'Каменское', 3],

			[12, 12, 0, 'Одесса', 4],
			[12, 643, 0, 'Южный', 4],
			[12, 625, 0, 'Затока', 4],
			[12, 350, 8977, 'Каролино-Бугаз', 4],

			[13, 109, 0, 'Дружковка', 7],
			[13, 112, 0, 'Константиновка', 7],
			[13, 113, 0, 'Краматорск', 7],
			[13, 119, 0, 'Мариуполь', 7],
			[13, 123, 0, 'Славянск', 7],
			[13, 114, 0, 'Красноармейск', 7], 	// for email
			[13, 100, 0, 'Артемовск', 7],
			[13, 107, 0, 'Доброполье', 7], 		// for email

			[14, 14, 0, 'Запорожье', 5],

			[16, 16, 0, 'Кропивницкий', 6],

			[17, 281, 0, 'Северодонецк', 7],	// for email
			[17, 267, 0, 'Лисичанск', 7],		// for email

			[18, 18, 0, 'Луцк', 15],
			[19, 19, 0, 'Николаев', 15],

			[20, 20, 0, 'Полтава', 6],
			[20, 605, 0, 'Кременчуг', 6],

			[23,23, 0, 'Херсон', 9],

			//[21, 593, 0, 'Севастополь', 11],
			//[21, 594, 'Ялта', 12],

			[24, 24, 0, 'Черкассы', 13],

			[25, 25, 0, 'Черновцы', 12],

			// для тестирования
			//[1, 1, 0, 'Винница', 100],
			//[19, 19, 0, 'Николаев', 100],
			[10, 10, 0, 'Киев', 100],

		];

		$nc = count($city_code);
		for($j=0; $j<$nc; $j++){
			if( $this->thread_id>0) {
				if ($city_code[$j][4] == $this->thread_id) { // если соответствует группе, то выполняем
					$this->parse_REALTY_city($city_code[$j][0], $city_code[$j][1], $city_code[$j][2], $city_code[$j][3]);
				}
			} else {
				$this->parse_REALTY_city($city_code[$j][0], $city_code[$j][1], $city_code[$j][2], $city_code[$j][3]);
			}

		}
	}

	protected function parse_REALTY_city($state_id, $city_id, $area_id, $city_name)
	{
		//category and type
		$ar_cat =[
			[1,0],
			[4,0],
			[10,0],
			[13,0],
			[24,0],
			[30,0]
		];

		$this->def_city = $city_name;
		$this->adv_processed = 0;
		$this->crop_bottom = 54;
		if(empty($this->row_type)) $this->row_type = self::ROW_TYPE_REALTY;
		$this->http->request_headers=$this->request_headers;
		$this->http->execute($this->website_url);
		echo $this->website_url . ' length: ' . strlen($this->http->result) . "\n";

		foreach ($ar_cat as $c1) {
			$this->city_url = 'https://dom.ria.com/node/searchEngine/?' .
				'state_id=' . $state_id .
				'&city_id%5B' . $city_id . '%5D=' . $city_id .
				'&limit=100&sort=0&period=0&category=' . $c1[0] .
				'&realty_type=' . $c1[1] .
				'&operation_type=0&realty_id_only=&with_phone=&date_from=&date_to=&email=&sortByLevels=1';
			if ($area_id > 0) {
				$this->consider_area_id = true;
				$this->city_url .= '&area_id=' . $area_id;
			}
			echo 'city_url: ' . $this->city_url . "\n";
			$this->parse_city();
		}
	}

	private function get_pages_count()
	{
		$data = json_decode($this->http->result, true);
		return ceil($data['count'] / 100);
	}

	private function parse_city()
	{
		$this->base_url = $this->city_url . '&page=0';
		$this->http->request_headers=$this->request_headers;
		$this->http->execute($this->base_url);
		if (!$this->http->error) {
			$pages = $this->get_pages_count();
			//var_dump($pages);
			//die();
			$have_new = $this->parse_page();
			for ($i = 1; $i < $pages; $i++) {
				if ($this->adv_processed >= $this->adv_processed_max) return true;
				$this->page = $i;
				$this->base_url = $this->city_url . '&page=' . $i;
				echo "\nparse_city: " . $this->base_url . "\n\n";
				$this->http->request_headers=$this->request_headers;
				$this->http->execute($this->base_url);
				if (!$this->http->error) {
					$have_new = $this->parse_page();
					if (!$have_new && $i > 20) {
						break;
					}
				} 
			}
		} 
	}

	/**
	 * Парсит страницу кратких объявлений
	 */
	private function parse_page()
	{
		if ($this->adv_processed >= $this->adv_processed_max) return true;
		$result = false;
		$json = json_decode($this->http->result, true);

		if(!empty($json))
			foreach($json['items'] as $adv_short) {

				$this->updated_at = $adv_short['updated_at'];
				$this->id = intval($adv_short['realty_id']);

				if(!empty($this->updated_at) && !empty($this->id)){
					$this->db_id_new = 0;
					$db_id = $this->db->super_query("SELECT id FROM dle_siteparser
						WHERE
							website_id='{$this->website_id}' AND
							url_id='{$this->id}' AND
							row_type='0'
						");
					if (!empty($db_id['id']) && $db_id['id'] > 0) {
						$db_id = $db_id['id'];
						echo "check the need for update $db_id \n";
						// если объвление уже найдено в базе, то тогда определяем нужно ли обновлять
						$this->db_id_new = $this->advUpId($db_id, $this->updated_at);

						if ($this->db_id_new == 0) {
							continue;
						}
					}

					if ($this->consider_area_id && isset($adv_short['district_name']) && $this->utf_to_1251($adv_short['district_name']) != $this->def_city){
						echo "district is considered and it is not interesting\n";
						continue;
					}

					if ($this->parse_adv($adv_short['beautiful_url'])) {
						$result = true;
					}

					// для тестирования отдельных потоков
					if($this->thread_id == 100){
						exit;
					}
				}
			}
		return $result;
	}

	protected function parse_adv($url)
	{
		$sl=mt_rand(5,20);
		echo "\nSleeping:".$sl."\n";

		$city_name = $this->def_city;
		$id = $this->id;

		if($this->thread_id == 100){
			$url = 'realty-prodaja-kvartira-kiev-radujnyy-masiv-19851592.html';
			$id = 19851592;
			$sl=1;
		}

		sleep($sl);

		echo "\nparse_adv URL: $url\n";

		$this->http->request_headers=$this->request_headers;
		$this->http->execute('https://dom.ria.com/ru/'.$url);

		$flag = true;
		while ($flag){
			$flag = false;
			$pos1 = strpos($this->http->result, '<!--');
			$pos2 = strpos($this->http->result, '-->');
			if(( $pos1 !== false ) AND ($pos2 !== false)) {
				$str1 = substr($this->http->result, 0, $pos1);
				$str2 = substr($this->http->result, $pos2+3);
				$this->http->result = $str1.$str2;
				$flag = true;
			}
		}

		$adv_res = $this->http->result;

		if (preg_match('#window\.__INITIAL_STATE__=(.*?)};#is', $adv_res, $matches)) {
			$adv_json = trim($matches[1]);
			$adv_json .= '}';
			//$adv_json = rtrim($adv_json, ';');
		} else {
			$this->log_error(__LINE__);
            echo "\n$url\nNo json data. Error!\n\n";
			return false;
		}

		$attributes = array();

		$adv = json_decode($adv_json, true);

		$title = trim($this->utf_to_1251($adv['listing']['data']['tagH']));
		if(strlen($title)>0) $attributes['h1'] = $title;

		$adv= $adv['listing']['data']['realty'];
		if($this->db_id_new>0) $attributes['insert_id'] = $this->db_id_new;

		$adv_text = trim($this->utf_to_1251($adv['description']));

		$adv_city = trim($this->utf_to_1251($adv['city_name']));
		if($city_name != $adv_city){
			echo "The adv specifies set different city - do not save\n";
			return false;
		}
		
		if(strlen($adv_text) ==0 ) $adv_text = trim($this->utf_to_1251($adv['description_ru']));
		if(strlen($adv_text) ==0 ) $adv_text = trim($this->utf_to_1251($adv['description_uk']));
		if(preg_match('/\d\d\D\d\d\D\d\d\d\d\D\d\d\d\d\d/', $adv_text)){
			return false; //it's our own adv so skip it
		}

		$try = 0;
		do {
			echo "try get number phone. Try = $try\n";
			$proxy = $this->http->get_proxy_pay();
			$phones = $this->getPhoneRia($proxy, 'https://dom.ria.com/ru/' . $url);
			$alive = $phones === false ? 0 : 1;
    		$this->http->clear_use_proxy_pay($proxy, $alive);
			if($try>20) break;
		}while($phones === false);
		var_dump($phones);

		if(count($phones) == 0) {
			echo "This adv has not phone number\n";
			return false;
		}

		if(strpos($adv_res, $this->cp1251_to_utf('(без комиссионных)')) !== false) $attributes['commission'] = 0;

		if($adv['advert_type_id'] == 1) $type_operation = 'Продают';
		if($adv['advert_type_id'] == 3) $type_operation = 'Сдают';
		if($adv['advert_type_id'] == 4) $type_operation = 'Сдают';

		$name_type = '';
		if($adv['realty_type_id'] == 2)  $name_type = 'Квартира';
		if($adv['realty_type_id'] == 3)  $name_type = 'Комната';
		if($adv['realty_type_id'] == 5)  $name_type = 'Дом';
		if($adv['realty_type_id'] == 6)  $name_type = 'Часть дома';
		if($adv['realty_type_id'] == 7)  $name_type = 'Дача';
		if($adv['realty_type_id'] == 11) $name_type = 'Офис';
		if($adv['realty_type_id'] == 12) $name_type = 'Офис';
		if($adv['realty_type_id'] == 15) $name_type = 'Склад';
		if($adv['realty_type_id'] == 25) $name_type = 'Земельный участок';
		if($adv['realty_type_id'] == 21) $name_type = 'Коммерческая';
		if($adv['realty_type_id'] == 26) $name_type = 'Коммерческая';
		if($adv['realty_type_id'] == 34) $name_type = 'Гараж';

		if (strlen($name_type) == 0){
			echo "realty_type_id is not interesting realty type\n";
			$this->log_error(__LINE__, 'realty_type_id: '.$adv['realty_type_id'], 'not interesting realty type');
			return false;
		}

		// address
		if (isset($adv['street_name']) && isset($adv['street_id']) && intval($adv['street_id']) > 0) {
			$address = 'Украина, ' . $city_name . ', ' . $this->utf_to_1251($adv['street_name']);
			$pos = $this->geocoding($address, $attributes);
			echo " geocoding street_name: " . $pos . "\n";
		} else {
			$str = $this->utf_to_1251($adv_res);
			if (strpos($str, '"address":') !== false) {
				$str = substr($str, strpos($str, '"address":') + 12);
				$str = substr($str, 0, strpos($str, '"'));
				// https://realtors-partners.club/tasks/view/218760
				// Думаю правильнейне будет ориентироваться на район «Соляные»
				if ($city_name == 'Николаев' && strpos($str, 'Соляные') !== false) {
					$this->get_address_street('Соляные', $city_name, $attributes);
				} else {
					$address = 'Украина, ' . $str;
					$pos = $this->geocoding($address, $attributes);
					echo "geocoding from field 'address': " . $pos . "\n";
				}
			}
		}

		if(!is_numeric($attributes['latitude']) || !is_numeric($attributes['longitude'])) {
			$this->get_address_street($adv_text,$city_name, $attributes);
			echo "geocoding from adv_text'\n";
		}

		if(isset($adv['district_name'])) {
			if($this->utf_to_1251($adv['district_name']) != $city_name) {
				$rs = new City_Selector($city_name);
				$res = $rs->findDirect($this->utf_to_1251($adv['district_name']));
				if ($res) {
					$city_name = $res['city'];
					$attributes['latitude'] = $res['lat'];
					$attributes['longitude'] = $res['lon'];
					echo 'City_Selector: -> ' . $res['lat'] . ', ' . $res['lon'] . "\n";
				}
			}
		}

		// координаты берутся с сайта, если они есть
		// отключено, так как они, как правило, неправильные
/*
		if (isset($adv['latitude']) AND isset($adv['longitude'])) {
			$attributes['latitude'] = $adv['latitude'];
			$attributes['longitude'] = $adv['longitude'];
			echo 'coordinates set directly: -> ' . $attributes['latitude'] . ', ' . $attributes['longitude'] . "\n";
		}
*/
		$this->check_coordinate($city_name, $attributes);

		$price = $this->getNumbers($adv['price']);
		$currency = $this->find_currency($this->utf_to_1251($adv['currency_type']));
		if (!$price || !$currency) {
			$price = $adv['priceArr'][1];
			$price = $this->getNumbers($price);

			$currency = 0;
			if ($price) {
				$currency = 2;
			}
		}

		//$attributes['is_realtor'] = ($adv['is_commercial'] != 0);

		if ($adv['rooms_count']) {
			$attributes['rooms_count'] = $adv['rooms_count'];
		}
		if ($adv['floor']) {
			$attributes['floor'] = $adv['floor'];
		}
		if ($adv['floors_count']) {
			$attributes['floor_all'] = $adv['floors_count'];
		}
		if ($adv['total_square_meters']) {
			$attributes['area_all'] = $adv['total_square_meters'];
		}
		if ($adv['living_square_meters']) {
			$attributes['area_living'] = $adv['living_square_meters'];
		}
		if ($adv['kitchen_square_meters']) {
			$attributes['area_kitchen'] = $adv['kitchen_square_meters'];
		}
		if (isset($adv['characteristics_values']['219'])) {
			$attributes['ground'] = floatval($adv['characteristics_values']['219']) / 100;
		}
		// если дом, часть дома, дача, Земельный участок и не указана площадь участка - ищем в тексте
		if(in_array($adv['realty_type_id'], [5,6,7,25,]) && !isset($attributes['ground'])){
			if (preg_match('/(\d+) (соток|сотки|сотка)/', $adv_text, $matches)) {
				$attributes['ground'] = floatval($matches[1]) / 100;
			}
		}

		$adv_text_full = '';
		if (preg_match('#id="realtyDescriptionBlock">(.*?)<\/dl>#is', $adv_res, $matches)) {
			$adv_text_full = $this->strip_html($this->utf_to_1251($matches[1]));
		} elseif (preg_match('#class="additional-data" id="realtyDescriptionBlockBody">(.*?)<\/dl>#is', $adv_res, $matches)) {
			$adv_text_full = $this->strip_html($this->utf_to_1251($matches[1]));
		} else {
			$this->log_error(__LINE__);
		}


		/*if (strpos($adv_text, 'от посредника') !== false) {
			$attributes['is_realtor'] = 1;
		}

		if (strpos($adv_text_full, 'от посредника') !== false) {
			$attributes['is_realtor'] = 1;
		}*/

		if ($this->strpos_arr($adv_text, array('болгария', 'болгарии'))  !== false) {
			$attributes['disabled'] = 1;
		}

		//var_dump($title);

		if ($this->findPerDay($adv_text) || $this->findPerDay($adv_text_full) || $this->findPerDay($title)) {
			$attributes['disabled'] = 1;
		}

		/*if ($attributes['is_realtor']) {
			$attributes['disabled'] = 1;
		}

		if ($attributes['disabled'] || $attributes['is_realtor']) {
			$images_list = array();
		}*/


		$images = $this->getListPhoto($adv);

		$attributes['images'] = $images;
		if ($this->updated_at) {
			$attributes['last_date_add'] = $this->updated_at;
		}

		$adv_text_full = trim($adv_text_full);
		if ($adv_text_full) {
			$adv_text = $adv_text_full;
		}

		$adv_text = $title . ' ' . $adv_text;

		if(isset($this->row_type))
			$attributes['row_type']=$this->row_type;
		else
			$attributes['row_type']=0;

		$attributes['approved'] = 1;

		$insert_id=$this->save_post(
			'https://dom.ria.com/ru/'.$url,
			$id,
			$phones,
			$name_type,
			$type_operation,
			$adv_text,
			$city_name,
			0,
			$price,
			$currency,
			$attributes
		);

		if($insert_id){
			$this->save_statistics($id, count($images), $city_name, $type_operation, 'https://dom.ria.com/ru/'.$url);
			return true;
		}
		return false;
	}

	function getPhoneRia($proxy, $needed_page)
	{
		$headers = array(
			'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
			'content-type' => 'text/html; charset=utf-8',
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36'
		);


		$get_main_page = $this->postRia($proxy, $needed_page, array(
			'headers' => array(
				'accept: ' . $headers['accept'],
				'content-type: ' . $headers['content-type'],
				'user-agent: ' . $headers['user-agent']
			)
		));

		if($get_main_page['httpCode'] == 403){
			return false;
		}

		$main_page_html = htmlspecialchars($get_main_page['content']);

		$re_hash = '/hash&quot;:&quot;(.*?)&quot;,&quot;/';
		preg_match($re_hash, $main_page_html, $hash);
		$phones=[];
		if ($hash[1] != "") {
			$xhrPage = 'https://dom.ria.com/v1/api/realty/getOwnerAndAgencyData/' . $hash[1];
			$userDataJson = $this->imTooLazyToMakeItAllinOneFunction($proxy, $needed_page, $xhrPage, $get_main_page['cookies']);
			$userData = json_decode($userDataJson, true);
			//print_r($userData); //all info about user
			
			if(!isset($userData['owner']['phones'])) return array();

			foreach ($userData['owner']['phones'] as $key => $value) {
				$ph = $this->getNumbers($userData['owner']['phones'][$key]['phone_num']);         ///Телефоны на html страницах
				if(!empty($ph)) $phones[] = $ph;
			}
		} else {
			$re_phones = '/phones&quot;:&quot;(.*?)&quot;,&quot;/';
			preg_match($re_phones, $main_page_html, $phones);
			if(!empty($phones[1])) $phones[] = $phones[1];  ///Телефоны на страницах без html
		}

		return $phones;
	}

	function postRia($proxy, $url = null, $params = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if (isset($params['params'])) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params['params']);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->http->cookiePath);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->http->cookiePath);
		}
		if (isset($params['headers'])) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $params['headers']);
		}

		if (isset($params['cookies'])) {
			curl_setopt($ch, CURLOPT_COOKIE, $params['cookies']);
		}

		if ($this->use_proxy) {
			if($this->http->is_payProxy){
				echo 'RIA get_proxy_pay: '.$proxy['ip'].':'.$proxy['port']."\n";
				curl_setopt($ch, CURLOPT_PROXY, $proxy['ip'].':'.$proxy['port']);
				if (!empty($proxy['proxy_password'])) {
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['proxy_login'] . ':' . $proxy['proxy_password']);
				}
			}else{
				echo 'postRia proxy is: '.$this->http->proxy."\n";
				curl_setopt($ch, CURLOPT_PROXY, $this->http->proxy);
			}
		}

		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$result_explode = explode("\r\n\r\n", $result);

		$headers = ((isset($result_explode[0])) ? $result_explode[0] . "\r\n" : '') . '' . ((isset($result_explode[1])) ? $result_explode[1] : '');
		$content = $result_explode[count($result_explode) - 1];

		preg_match_all('|Set-Cookie: (.*);|U', $headers, $parse_cookies);

		$cookies = implode(';', $parse_cookies[1]);

		curl_close($ch);

		return array('headers' => $headers, 'cookies' => $cookies, 'content' => $content, 'httpCode'=>$httpCode);
	}


	function imTooLazyToMakeItAllinOneFunction($proxy, $referer = null, $xhrPage = null, $cookie = null)
	{

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $xhrPage);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if ($this->use_proxy) {
			if($this->http->is_payProxy){
				echo 'RIA get_proxy_pay: '.$proxy['ip'].':'.$proxy['port']."\n";
				curl_setopt($ch, CURLOPT_PROXY, $proxy['ip'].':'.$proxy['port']);
				if (!empty($proxy['proxy_password'])) {
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['proxy_login'] . ':' . $proxy['proxy_password']);
				}
			}else{
				echo 'postRia proxy is: '.$this->http->proxy."\n";
				curl_setopt($ch, CURLOPT_PROXY, $this->http->proxy);
			}
		}

		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		$headers = array();
		$headers[] = 'Cookie: ' . $cookie;
		$headers[] = 'Accept-Encoding: gzip, deflate, br';
		$headers[] = 'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7';
		$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36';
		$headers[] = 'Accept: application/json, text/plain, */*';
		$headers[] = 'Referer: ' . $referer;
		$headers[] = 'Authority: dom.ria.com';
		$headers[] = 'X-Requested-With: XMLHttpRequest';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);

		return $result;
	}

	protected function getListPhoto($adv)
	{
		$images = array();
		$images_list = array();

		// convert to big image
		// https://cdn.riastatic.com/photosnew/dom/photo/arenda-posutochnaya-kvartira-cherkassyi-tsentr-gogolya-ulitsa-290__40771852m.jpg - маленькое фото
		// https://cdn.riastatic.com/photosnew/dom/photo/arenda-posutochnaya-kvartira-cherkassyi-tsentr-gogolya-ulitsa-290__40771852f.jpg - большое фото
		foreach ( $adv['photos'] as $el) {
			$f = explode('.', $el['beautifulUrl']);
			$f[count($f) - 2] .= 'f';
			$images_list[] = 'https://cdn.riastatic.com/photosnew/'.implode('.', $f);
			//echo 'https://cdn.riastatic.com/photosnew/'.implode('.', $f)."\n";
		}

		foreach ($images_list as $image_url) {
			if (substr($image_url, -5) == 'm.jpg') {
				$image_url = substr($image_url, 0, -5) . 'fl.jpg';
			} elseif (substr($image_url, -5) == 'm.gif') {
				$image_url = substr($image_url, 0, -5) . 'fl.gif';
			} elseif (substr($image_url, -5) == 'm.png') {
				$image_url = substr($image_url, 0, -5) . 'fl.png';
			} elseif (substr($image_url, -5) == 'm.jpeg') {
				$image_url = substr($image_url, 0, -5) . 'fl.jpeg';
			}
			//var_dump($image_url);
			try {
				$this->image_parser->http->is_proxy = false;
				$image = $this->image_parser->fetchImage($image_url,'','s'.$this->website_id);

				$img = Image::factory(ROOT_DIR . 'img/' . $image);
				//$img->crop_background();
				$img->crop($img->width, $img->height - $this->crop_bottom, 0, 0)->save(ROOT_DIR . 'img/' . $image, 80);
				$img->save(ROOT_DIR . 'img/' . $image, 80);
				unset($img);

				$image = $this->validateImage($image, ROOT_DIR . 'img/');
				if ($image) {
					$this->image_parser->crop(ROOT_DIR . 'img/' . $image, 64);
					$images[] = $image;
					//echo 'image: ' . $image . "\n";
				}

				if (count($images) > 19) {
					break;
				}
			} catch (Exception $e) {
				//$this->log_error(__LINE__, $e->getMessage());
			}

			if (count($images) > 19) {
				break;
			}
		}
		return $images;
	}

	// функція оновлення фото
	//http://localhost/RieltSoftCRM/index.php?q=slando.ua/reload&url=https://www.olx.ua/obyavlenie/lux-apartamenty-v-zhk-slavutich-seven-zarechnyy-IDIxnOO.html
	public function reload()
	{
		//    echo $_SERVER['REMOTE_ADDR'];exit;
		$this->website_id = 11;
		$url = isset($_GET['url']) ? $_GET['url'] : '';
		if(empty($url)){
			echo '[]';
			exit;
		}
		ob_start();
		$this->http->setReferrer($this->website_url);
		$this->http->setUseragent('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36');

		$this->http->setTorUsing(false);
		$this->http->is_proxy = $this->use_proxy;
		$this->http->is_secure = $this->use_proxy; //exper
		$this->http->is_payProxy = true;
		$this->http->website_id = $this->website_id;
	    $this->http->thread_id = $this->thread_id;
	    $this->http->use_index = 3;

		$this->http->useCurl(true);

		$this->http->cookiePath = ROOT_DIR . 'cookies/website-' . $this->website_id . '-img.txt';
		@unlink($this->http->cookiePath);
		$this->http->useCookie(false);

		$this->loadImageParser();

		$this->http->execute($url);
		$flag = true;
		while ($flag){
			$flag = false;
			$pos1 = strpos($this->http->result, '<!--');
			$pos2 = strpos($this->http->result, '-->');
			if(( $pos1 !== false ) AND ($pos2 !== false)) {
				$str1 = substr($this->http->result, 0, $pos1);
				$str2 = substr($this->http->result, $pos2+3);
				$this->http->result = $str1.$str2;
				$flag = true;
			}
		}

		$adv_res = $this->http->result;

		if (preg_match('#window\.__INITIAL_STATE__=(.*?)};#is', $adv_res, $matches)) {
			$adv_json = trim($matches[1]);
			$adv_json .= '}';
			//$adv_json = rtrim($adv_json, ';');
		} else {
			echo '[]';
			exit;
		}

		$adv = json_decode($adv_json, true);
		$adv= $adv['listing']['data']['realty'];

		$images = $this->getListPhoto($adv);
		ob_end_clean();
		echo json_encode($images);
		exit;
	}
}