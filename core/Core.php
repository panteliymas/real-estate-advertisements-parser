<?php

define ('DATALIFEENGINE', true);

include_once(ROOT_DIR . 'core/Router.php');
include_once(ROOT_DIR . 'core/http/class.http.php');
include_once(ROOT_DIR . "core/mysql.php");
include_once(ROOT_DIR . 'core/dbconfig.php');
include_once(ROOT_DIR . 'core/Helper.php');
include_once(ROOT_DIR . 'core/Parser.php');
include_once(ROOT_DIR . 'core/Settings.php');
include_once(ROOT_DIR . 'core/Memcache.php');
include_once ROOT_DIR . 'core/GeocogingCache.php';
include_once ROOT_DIR . 'core/Address.php';

function my_fputcsv(&$handle, $fields, $delimiter = ';', $enclosure = '"')
{
	$string = '';
	foreach ($fields as $field) {
		$field = str_replace(array("\n", "\r", "\t", "\0", "\b", "\f"), " ", $field);
		$field = str_replace(';', ',', $field);
		$field = preg_replace('/\s[\s]+/', ' ', $field); // Strip multiple spaces
		$string .= $field . $delimiter;
	}
	fputs($handle, $string . "\r\n");
}

class Core
{
	/**
	 * @var tpl Fix_Templates
	 */

	/**
	 * put your comment there...
	 *
	 * @var Http
	 */
	public $http;
	/**
	 * @var Image_Parser
	 */
	protected $image_parser;
	/**
	 * @var db
	 */
	public $db;
	protected $new_items = 0;
	protected $website_id = 0;
	protected $thread_id = 0;
	protected $time_start = 0;
	protected $run_id = 0;
	protected $base_url = '';
	protected $cat_title = '';
	protected $cat_name = '';
	protected $row_type = 0;

	protected $debug_no_insert = false;
	protected $debug_no_insert_limit = 1;
	protected $debug_die_after = 0;
	protected $page = 1;
	protected $def_city = '';
	protected $interested_cities;

	const ROW_TYPE_AVTO = 1;
	const ROW_TYPE_REALTY = 0;
	const ROW_TYPE_REALTY_KIEV = 2;


	protected $patterns_price = array(
		'#([0-9,]+ye)#si', // обе англ
		'#([0-9,]+yе)#si', // первая англ
		'#([0-9,]+уe)#si', // вторая англ
		'#([0-9,]+уе)#si',
		'#([0-9,]+\$)#si',
		'#([0-9,]+usd)#si',
		'#(\$[0-9,]+)#si',
		'#([0-9,]+туе)#si',
		'#([0-9,]+грн)#si',
		'#([0-9,]+uah)#si',
		'#([0-9,]+uan)#si',
		'#([0-9,]+грив)#si',
		'#([0-9,]+руб)#si',
		'#([0-9,]+\+оф)#si',
		'#([0-9,]+тыс)#si',
		'#([0-9,]+долларов)#si',
		'#([0-9,]+доларов)#si',
		'#([0-9,]+\+ку)#si',
		'#([0-9,]+к/у)#si',
		'#([0-9,]+\+к/у)#si',
		'#([0-9,]+к\\у)#si',
		'#([0-9,]+\+к\\у)#si',
		//'#([0-9,]+ком)#si',
		'#([0-9,]+\+ком)#si',
		'#([0-9,]+торг)#si',
		'#([0-9,]+дол)#si',
		'#(цена[0-9,]+)#si',
		'#(стоимость[0-9,]+)#si',
		'#(стоит[0-9,]+)#si',
		'#бюджет до ([0-9,]+)#si',
		'#бюджет ([0-9,]+)#si',
		'#арендная плата ([0-9,]+)#si',
	);

	protected $phone_codes_ru = '900|901|902|903|904|905|906|908|909|910|911|912|913|914|915|916|917|918|919|920|921|922|923|924|925|926|927|928|929|930|931|932|933|934|936|937|938|939|941|950|951|952|953|954|955|956|958|960|961|962|963|964|965|966|967|968|969|970|971|980|981|982|983|984|985|987|988|989|991|992|993|994|995|996|997|999|978';

	protected $auto_marks = 'acura|aixam|alfa romeo|apache|aro|artega|asia|aston martin|audi|austin|autobianchi|barkas|baw|bedford|bentley|bertone|bio auto|bmw|bova|brilliance|bugatti|buick|byd|cadillac|caterham|chana|changan|changhe|chery|chevrolet|chrysler|citroen|dacia|dadi|daewoo|daf|daihatsu|daimler|diau|dkw|dodge|dong feng|eagle|faw|feldbinder|ferrari|fiat|fisker|ford|fso|fuqi|geely|geo|gmc|gonow|great wall|groz|hafei|hanomag|hansa|honda|huabei|huanghai|humber|hummer|hyundai|infiniti|isuzu|iveco|jac|jaguar|jeep|jiangnan|jmc|karosa|kia|koenigsegg|lamborghini|lancia|land rover|landwind|ldv|lexus|lifan|lincoln|lotus|maple|maruti|maserati|maybach|mazda|mclaren|mega|mercedes-benz|mercury|mg|mini|mitsubishi|nissan|nysa|oldsmobile|oltcit|opel|peugeot|plymouth|polonez|pontiac|porsche|proton|renault|rolls-royce|rover|saab|saipa|samand|samsung|saturn|sceo|sea-doo brp|seat|shelby|shuanghuan|skoda|sma|smart|soueast|soyat|spyker|ssangyong|star|studebaker|subaru|sunbeam|suzuki|syrena|talbot|tarpan honker|tata|tatra|tesla|tianma|tiger|toyota|trabant|triumph|van hool|vauxhall|volkswagen|volvo|wanderer|wanfeng|wartburg|willys|wuling|xiaolong|xin kai|xinkai|yugo|zastava|zhong|zimmer|zx|авіа|азлк|богдан|бронто|ваз|вис|газ|голаз|гужевой транспорт|ераз|жук|заз|зил|зим|зис|иж|луаз|москвич|ретро автомоби|сеаз|смз|там|уаз|mersedes - benz|mitsubihi|litan|mayback|ssang yong|bently|rolls-royse|oltsit|trinmph|mcharen|di au|pagani|tarpan hanker|лада|lada|vw|мерс|mercedes|ниссан|форд|тойота|тойоту|нисан|митсубиши|додж|вольцваген|вольво|опель|астон мартин|ауди|бьюик|кадилак|шевроле|форт|хёндай|хундай|хёндэ|хамер|инфинити|ягуар|киа|кия|митцубиси|мицубиши|митсуби|рэнджь ровер|рэндж ровер|майбах|ролс ройс|волксвэган|хонд|эстон|фольксваген|шкода|шкоду|mersedes|бмв|фиат|лексус|мазда|мазду|пэжо|пежо|рено|субару|тойот|тоёот|lancer|лансер|lanos|ланос|aveo|дэу|деу|джилли|мерседес|нива|ниву|таври|хонда|хонду|хюндай|ситроен|сеат|фольцвагин|чери|мицубиси|волгу|волга|джели|джили|аudi|gelly|гранд|дачия|калину|калина|сенс|славута|камаз|лимузин|avia';

	protected $user_agent = array(
		'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0',
		'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20120101 Firefox/29.0',
		'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/29.0',
		'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36',
		'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.15 (KHTML, like Gecko) Chrome/24.0.1295.0 Safari/537.15',
	);

	public function __construct()
	{
		global $db;
		$this->db = $db;
		error_reporting(E_ALL ^ E_NOTICE);
		@ini_set('error_reporting', E_ALL ^ E_NOTICE);
		@ini_set('display_errors', true);
		@ini_set('html_errors', false);

		setlocale(LC_ALL, array('ru_RU.CP1251', 'rus_RUS.1251', 'Russian_Russia.1251'));

		date_default_timezone_set('Europe/Kiev');
		$this->http = new Http();

		$this->image = null;
		$agent = mt_rand(0, count($this->user_agent) - 1);
		$this->http->setUseragent($this->user_agent[$agent]);

		$address = new Address();
		$this->interested_cities = $address->getInterestedCities();
		set_time_limit(0);
	}

	function loadImageParser()
	{
		include_once(ROOT_DIR . 'core/Image_Parser.php');
		$this->image_parser = new Image_Parser();
		$this->image_parser->setUseragent($this->http->getUseragent());
		$this->image_parser->http->website_id = $this->website_id;
		$this->image_parser->http->thread_id = $this->thread_id;
	}

	public function finishSleepingParsers()
	{
		$date_add = date("Y-m-d H:i:s");
		$date_old = date("Y-m-d H:i:s", time() - (60 * 60 * 8));
		// устанавливаем флаг
		$this->db->query("UPDATE " . PREFIX . "_siteparser_stats
			SET
			parser_stop='1',
			is_error_stop='1',
			date_stop='{$date_add}'
			WHERE date_run<'{$date_old}' AND parser_stop='0'");
	}

	public function parser_before_run($thread_id = 0)
	{
		$this->time_start = time();
		$this->thread_id = intval($thread_id);

		if ($this->website_id > 0) {

			$this->http->website_id = $this->website_id;
			$this->http->thread_id = $this->thread_id;

			$date_add = date("Y-m-d H:i:s");
			$date_old = date("Y-m-d H:i:s", time() - (60 * 60 * 8));
			// устанавливаем флаг
			$this->finishSleepingParsers();

			$this->db->query("SELECT * FROM " . PREFIX . "_siteparser_stats WHERE
				website_id='{$this->website_id}' AND
				thread_id='{$this->thread_id}' AND
				parser_stop='0'");

			if ($this->db->num_rows() > 0) {
				echo 'parser for website_id: ' . $this->website_id . ':' . $this->thread_id . ' is already running.';
				exit;
			}

			$this->db->query("INSERT INTO dle_siteparser_stats (
				date_run, website_id, thread_id
			) VALUES (
				'{$date_add}', '{$this->website_id}', '{$this->thread_id}'
			)");

			$this->run_id = $this->db->insert_id();

			register_shutdown_function(array(&$this, "__destruct"));
		}
	}

	public function check_city( $city ) {
		foreach($this->interested_cities as $key => $el){
			if($city == $el[0]) return $key;
		}
		return false;
	}

	public function get_list_city() {
		$city=array();
		foreach($this->interested_cities as $el){
			$city[]=$el[0];
		}
		return $city;
	}

	public function get_coordinate_city($city) {
		$coor=array('lat'=>0, 'lon'=>0);
		foreach($this->interested_cities as $el){
			if($city == $el[0]) {
				$coor['lat'] = $el[1];
				$coor['lon'] = $el[2];
			}
		}
		return $coor;
	}

	public function check_coordinate($city, &$attributes, $update = true){
		$coor = $this->get_coordinate_city($city);
		if(is_numeric($attributes['latitude']) AND is_numeric($attributes['longitude'])) {
			if ($this->calcdistance($attributes['latitude'], $attributes['longitude'], $coor['lat'], $coor['lon']) < 50000) return true;
			echo "coordinates is so far\n";
		}
		echo "set coordinates in the downtown\n";
		if($update) {
			$attributes['downtown'] = '1';
			$attributes['latitude'] = $coor['lat'];
			$attributes['longitude'] = $coor['lon'];
		}
		return false;
	}

	// Функция определения расстояния между точками
	public function calcdistance($lat1, $lon1, $lat2, $lon2){
		$pi_div_180 = pi() / 180.0;
		$d_fak = 6371000.0;
		$d2 = 2.0;
		$latx = $lat1 * $pi_div_180;
		$lonx = $lon1 * $pi_div_180;
		$laty = $lat2 * $pi_div_180;
		$lony = $lon2 * $pi_div_180;
		$sinlat = sin(($latx - $laty) / $d2);
		$sinlon = sin(($lonx - $lony) / $d2);
		return $d2 * asin(sqrt($sinlat * $sinlat + $sinlon * $sinlon * cos($latx) * cos($laty))) * $d_fak;
	}

	public function geocoding($addressText, &$attributes)
	{
		$address = new Address();
		if($address->geocoding($addressText)) {
			if($address->getIsDowntown()) $attributes['downtown'] = '1';
			$attributes['latitude'] = str_replace(',', '.', $address->lat);
			$attributes['longitude'] = str_replace(',', '.', $address->lon);
			$attributes['geocoding_log'] = $addressText;
			return str_replace(',', '.', $address->lon) . ' ' . str_replace(',', '.', $address->lat);
		}
		return '';
	}

	public function geocodingRaw( $address ) {
		$yandex = new GeoYandex();
		$yandex->get_coordinate($address);

		if (strlen($yandex->response) > 0) {
			echo 'length response: ' . strlen($yandex->response) . "\n";
			return json_decode($yandex->response);
		}

		echo "length response: 0 \n";
		return false;
	}

	public function translate_city_to_ru($city_ua)
	{
		$ar = file(dirname(__FILE__).'/streets/city.txt',FILE_IGNORE_NEW_LINES);
		if(is_array($ar)) {
			foreach ($ar as $el) {
				$city = explode(':', $el);
				if (stripos($city_ua.' ', $city[1].' ') !== false) {
					return $city[0];
				}
			}
		}
		return '';
	}

	public function get_address_street($adv_text,$city,&$attributes)
	{
		$address = new Address($adv_text, $city);
		// если координаты не найдены - устанавливается флаг центра города
		$address->extractCoordinate();
		if($address->getIsDowntown()) $attributes['downtown'] = '1';

		$attributes['latitude'] = str_replace(',', '.', $address->lat);
		$attributes['longitude'] = str_replace(',', '.', $address->lon);
		$attributes['geocoding_log'] = $address->geocodingStepAsString();
	}

	public function check_center($x,$y){

		$default_centers = [
			[30.523487,50.450412],
			[33.525432,44.616687],
			[37.584398,48.738978],
			[37.58435, 48.738967],
			[37.605346,48.853201],
			[37.605939,48.852347],
			[30.732597,46.484579],
			[35.046181,48.464717],
			[30.523000,50.450000],
		];

		$dx = 0.001;
		$dy = 0.001;

		foreach ($default_centers as $center_one) {
			if(abs($center_one[0]-$x) < $dx && abs($center_one[1]-$y) < $dy){
				echo "\nPoint: ".$x." ".$y."\n".$center_one[0]." ".$center_one[1]."\n";
				return true;
			}
		}
		return false;
	}

	public function save_statistics($id,$n_images,$city_name,$op_type,$url)
	{
		/// statistics for parser_counter
		switch($op_type){
			case 'Сдают':
				$sub_sql='num_hire = num_hire+1,';
				break;
			case 'Снимают':
				$sub_sql='num_rent = num_rent+1,';
				break;
			case 'Покупают':
				$sub_sql='num_buy = num_buy+1,';
				break;
			case 'Продают':
				$sub_sql='num_sale = num_sale+1,';
				break;
			default:
				return;

		}
		$query_str='UPDATE dle_parser_counter
    						              SET num_img=num_img+'.$n_images.',
    						                  num_adv=num_adv+1,'
			.$sub_sql.
			'last_time=\''.date("Y-m-d H:i:s", time()).'\',
    						                  last_id=\''.$id.'\',
    						                  adv_url=\''.$url.'\'
    						              WHERE city_name=\''.$city_name.'\'
    						                  AND website_id=\''.$this->website_id.'\'';
		$this->db->query($query_str);
		if($this->db->db_id->affected_rows<=0){
			switch($op_type){
				case 'Сдают':
					$fields_value=',1,0,0,0';
					break;
				case 'Снимают':
					$fields_value=',0,1,0,0';
					break;
				case 'Покупают':
					$fields_value=',0,0,1,0';
					break;
				case 'Продают':
					$fields_value=',0,0,0,1';
					break;
				default:
					return false;

			}
			$query_str='INSERT dle_parser_counter
                                (num_img,
			                     num_adv,
		                         num_hire,
		                         num_rent,
		                         num_buy,
		                         num_sale,
                                 cur_date,
                                 city_name,
			                     website_id,
			                     last_time,
    						     last_id,
			                     adv_url)
			              VALUES(
			                  '.$n_images.',
			                   1'
				.$fields_value.
				',\''.date('Y-m-d',time()).'\',
			                  \''.$city_name.'\',
			                  \''.$this->website_id.'\',
			                  \''.date("Y-m-d H:i:s", time()).'\',
    						  \''.$id.'\',
    						  \''.$url.'\'
			                      )';
			$this->db->query($query_str);
		}

		//monthly statistics
		$query_str='UPDATE dle_parser_counter_monthly
    						              SET num_img=num_img+'.$n_images.',
    						                  num_adv=num_adv+1,'
			.$sub_sql.
			'last_time=\''.date("Y-m-d H:i:s", time()).'\',
    						                  last_id=\''.$id.'\',
    						                  adv_url=\''.$url.'\'
    						              WHERE city_name=\''.$city_name.'\'
    						                  AND website_id=\''.$this->website_id.'\'';
		$this->db->query($query_str);
		if($this->db->db_id->affected_rows<=0){
			switch($op_type){
				case 'Сдают':
					$fields_value=',1,0,0,0';
					break;
				case 'Снимают':
					$fields_value=',0,1,0,0';
					break;
				case 'Покупают':
					$fields_value=',0,0,1,0';
					break;
				case 'Продают':
					$fields_value=',0,0,0,1';
					break;
				default:
					return false;

			}
			$query_str='INSERT dle_parser_counter_monthly
                                (num_img,
			                     num_adv,
		                         num_hire,
		                         num_rent,
		                         num_buy,
		                         num_sale,
                                 cur_date,
                                 city_name,
			                     website_id,
			                     last_time,
    						     last_id,
			                     adv_url)
			              VALUES(
			                  '.$n_images.',
			                   1'
				.$fields_value.
				',\''.date('Y-m-d',time()).'\',
			                  \''.$city_name.'\',
			                  \''.$this->website_id.'\',
			                  \''.date("Y-m-d H:i:s", time()).'\',
    						  \''.$id.'\',
    						  \''.$url.'\'
			                      )';
			$this->db->query($query_str);
		}


		return true;
	}

	public function parser_after_run()
	{
		$this->__destruct();
	}

	public function __destruct()
	{
		if ($this->run_id && $this->website_id) {
			$run_id = $this->run_id;

			$this->run_id = 0;

			$date_add = date("Y-m-d H:i:s");

			$time_parser = time() - $this->time_start;
			if ($time_parser < 0) {
				$time_parser = 0;
			}

			$this->db->query("UPDATE dle_siteparser_stats
				SET
					items_add='{$this->new_items}',
					time_parser='{$time_parser}',
					parser_stop='1',
					date_stop='{$date_add}'
			WHERE id='{$run_id}'");
		}
	}

	public function strip_all($text)
	{
		$text = str_replace('&nbsp;', ' ', $text);
		$text = trim(strip_tags(html_entity_decode($text, ENT_QUOTES)));
		$text = preg_replace('/\s[\s]+/', ' ', $text);
		return $text;
	}

	public function strip_html($text)
	{
		$text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $text);//by roman
		$text = str_replace('&nbsp;', ' ', $text);
		$text = str_replace('><', '> <', $text);
		$text = trim(strip_tags(html_entity_decode($text, ENT_QUOTES)));
		$text = preg_replace('/\s[\s]+/', ' ', $text);
		return $text;
	}

	public function search_cat_title($text)
	{
		if ($text == '') return $text;

		$text = strtolower($text);

		if ($this->strpos_arr($text, array('сдают', 'сдавай', 'сдавайте', 'сдаем', 'сдает', 'сдаете', 'сдаешь', 'сдаю', 'сдаём', 'сдаёт', 'сдам', 'здам', 'здаю', 'здаєтьс', 'аренда')) !== false) {
			return 'Сдают';
		}

		if ($this->strpos_arr($text, array('снимем', 'снять', 'арендовать', 'возьму в аренду', 'снимаем', 'снимает', 'снимаете', 'снимайте', 'снимал', 'снимаю', 'снимают', 'сниму', 'снимет', 'арендую ')) !== false) {
			return 'Снимают';
		}

		if ($this->strpos_arr($text, array('продают', 'продаем', 'продает', 'продаете', 'продаю', 'продаём', 'продаёт', 'продаёшь', 'продажа', 'продам', 'реализу', 'покупай')) !== false) {
			return 'Продают';
		}

		if ($this->strpos_arr($text, array('покупаем', 'покупает', 'покупаете', 'покупай', 'покупайте', 'покупаю', 'покупают', 'покупка', 'куплю', 'купим', 'купить')) !== false) {
			return 'Покупают';
		}

		if ($this->strpos_arr($text, array('меняем', 'меняет', 'меняете', 'меняешь', 'меняй', 'меняйте', 'меняю', 'меняют', 'меняя', 'обмен')) !== false) {
			return 'Меняют';
		}

		if ($this->strpos_arr($text, array('предлагаю')) !== false) {
			return 'Продают';
		}

		return '';
	}

	public function search_cat_name($text)
	{
		if ($text == '') return $text;
		$text = strtolower($text);
		if ($this->row_type == self::ROW_TYPE_AVTO) {
			return $this->search_cat_name_avto($text);
		} else {
			return $this->search_cat_name_realty($text);
		}
	}

	public function is_auto_by_mark($text)
	{
		if (preg_match('#(' . $this->auto_marks . ')#is', $text)) {
			return 'Автомобиль';
		}
		return false;
	}

	public function search_cat_name_avto($text)
	{
		// точное вхождение

		if ($this->strpos_arr($text, array('прицеп')) !== false) {
			return 'Прицеп';
		}
		if ($this->strpos_arr($text, array('спецтехника', 'цементовоз', 'экскаватор', 'погрузчик')) !== false) {
			return 'Строительная техника';
		}

		if ($this->strpos_arr($text, array('грузовик', 'грузовые')) !== false) {
			return 'Грузовик';
		}

		if ($this->strpos_arr($text, array('автодом')) !== false) {
			return 'Автодом';
		}

		if ($this->strpos_arr($text, array(
				'воздушный транспорт', 'самолет', 'самолёт', 'вертолет', 'вертолёт'
			)) !== false
		) {
			return 'Воздушный транспорт';
		}

		if ($this->strpos_arr($text, array(
				'автомобиль', 'машина', 'легковые'
			)) !== false
		) {
			return 'Автомобиль';
		}

		// чтобы не определялось как "велик"
		$text = str_replace('невелик', ' ', $text);
		if ($this->strpos_arr($text, array('велосип', 'велосеп', 'вилосип', 'велосеп', 'велик', 'велос.')) !== false) {
			return 'Велосипед';
		}

		if ($this->strpos_arr($text, array(
				'запчасть', 'з/части', 'з/ч', 'автозапчасти', 'детал', 'з.части', 'з.ч', 'запчасти'
			)) !== false
		) {
			return 'Запчасть';
		}

		if ($this->strpos_arr($text, array('автобус')) !== false) {
			return 'Автобус';
		}

		if ($this->strpos_arr($text, array(
				'мотоцикл', 'скутер', 'мопед', 'ява ', 'ява-', 'дырчик', 'байк', 'мот. ',
				'урал', 'днепр', 'карпаты', 'юпитер', 'планета', 'viper', 'kymco',
				'kawasaki', 'sport energy', 'bashan', 'чоппер', 'bombardier', 'yamaha',
				'ducati', 'stinger wind', 'sabur', 'трайк', 'hyosung', 'кавасаки', 'моп. '
			)) !== false
		) {
			return 'Мотоцикл';
		}

		if ($this->strpos_arr($text, array('сельхоз', 'трактор', 'комбайн', 'лошадь')) !== false) {
			return 'Сельхоз. техника';
		}

		if ($this->strpos_arr($text, array(
				'лодка', 'лодку', 'яхту', 'яхта', 'катер', 'паром', 'гидроцикл', 'дебаркадер',
				'джетборд', 'подводный скутер', 'понтон', 'теплоход', 'водный транспорт'
			)) !== false
		) {
			return 'Водный транспорт';
		}

		if (preg_match('#(продам|продаю|продается|продаеться|сдаю|сдам|поменяю|меняю|внедорожник|минивэн|срочно|обменять|куплю|свой)([ "\':\-]*)(' . $this->auto_marks . ')#ies', $text)) {
			return 'Автомобиль';
		}

		if ($this->strpos_arr($text, array('минивэн', 'внедорожник', 'машина')) !== false) {
			return 'Автомобиль';
		}

		if ($this->strpos_arr($text, array(
				'хреновин', 'замок', 'карданн',
				'шестерн', 'зарядное', 'бензобак', 'фонар', 'мост', 'ступиц',
				'головк', 'тормоз', 'стекло', 'форсунк',
				'шаровою', 'колпак', 'резин', 'видеорегистратор', 'шины', 'бампер',
				'коробку передач', 'блок двигателя', 'стартер', 'чехол', 'сабвуфер',
				'втулк', 'коврик', 'ветровички', 'трамблер', 'колесо', 'редуктор',
				'радиатор', 'насос', 'фильтр', 'гидрокомпенсатор', 'клапан',
				'дверь', 'заглушк', 'зеркало', 'тяга', 'трос', 'фар', 'указатель',
				'крыло', 'решетк', 'термостат', 'поршнев', 'ролик', 'прокладк',
				'сальник', 'корзин', 'шрус', 'опор', 'амортизатор', 'шаровая',
				'пыльник', 'колодк', 'датчик', 'реле', 'провод', 'катушка', 'монитор',
				'шлем', 'компьютер', 'капот', 'бензонасос', 'розетк', 'парктроник',
				'колонки', 'крестовин', 'стабилизатор', 'коленвал', 'антирадар',
				'брызговики', 'карбюратор', 'подголовник', 'тнвд', 'торсион', 'иммобилайзер',
				'шторк', 'колеса', 'сиден', 'рессор', 'блок управления', 'замки',
				'потенциометр', 'пружин', 'резина', 'боковое', 'окно', 'резину',
				'аккумулятор', 'контроллер', 'таксометр', 'картер', 'тахометр', 'резину',
				'омыватель', 'лонжерон', 'регистратор', 'расходомер', 'приборку', 'сигнализацию', 'покрышку'
			)) !== false
		) {
			return 'Запчасть';
		}

		if ($this->strpos_arr($text, array('грузов')) !== false) {
			return 'Грузовик';
		}

		if ($this->strpos_arr($text, array('кран', 'цементовоз', 'экскаватор')) !== false) {
			return 'Строительная техника';
		}

		if ($this->strpos_arr($text, array('легков', 'иномарк', 'автомобил', 'москвич', 'газель', 'авто')) !== false) {
			return 'Автомобиль';
		}

		if ($this->strpos_arr($text, array(
				'диск', 'магнитол', 'резину', 'ризину', 'бампер', 'спойлер', 'двигатель', 'запчасти', 'двери',
				'покрышку', 'чехлы'
			)) !== false
		) {
			return 'Запчасть';
		}
		/*
		+Легковые
		+Грузовые
		+Автобусы
		+Прицепы
		+Мотоциклы
		+велосипеды
		Сельхоз. техника
		+Запчасти
		*/
		return '';
	}

	public function search_rooms_count($text)
	{
		$text = strtolower($text);
		$rooms_count = 0;
		if (preg_match('#([0-9]+?)[-\s]?(комнатн|ком\.кв|ком\. кв|ком кв|к кв)#is', $text, $matches)) {
			$rooms_count = intval($matches[1]);
		}
		if ($rooms_count < 0 || $rooms_count > 10) {
			$rooms_count = 0;
		}
		return $rooms_count;
	}

	public function search_cat_name_realty($text)
	{

		// Следующие замены для лучшего распознавания квартир
		// кв. - это так же квартира, исправление бага:
		// Офисное помещ-ие с ремонтом, 51 кв. м, Шкадинова, 52. Тел. : 0506542085.

		$text = str_replace('рядом', ' ', $text); // распознавалось как дои
		$text = str_replace('подача', ' ', $text); // распознавалось как Дача
		$text = str_replace('ком.кв', ' квартиру ', $text);
		$text = str_replace('ком. кв', ' квартиру ', $text);
		$text = str_replace('ком кв', ' квартиру ', $text);

		$text = preg_replace('#[0-9]+кв#', '', $text);
		$text = preg_replace('#[0-9]+ кв#', '', $text);

		$text = str_replace('кв.м', '', $text);
		$text = str_replace('кв. м', '', $text);

		$text = str_replace('м.кв', '', $text);
		$text = str_replace('м. кв', '', $text);

		// Сдача квартир - это не "Дачный участок"
		$text = str_replace('сдача', 'сдам', $text);

		if (strpos($text, 'гостевой дом') !== false) {
			return 'Гостевой дом';
		}

		if ($this->strpos_arr($text, array(
				'продам дом ',
				'сдам дом ',
				'продается дом ',
				'сдается дом ',
				'куплю дом ',
				'свой дом ',
				'большой дом ',
				'хороший дом'
			)) !== false
		) {
			return 'Дом';
		}

		if ($this->strpos_arr($text, array('гостинка', 'гостинку')) !== false) {
			return 'Гостинный номер';
		}

		if ($this->strpos_arr($text, array('эллинг', 'элинг')) !== false) {
			return 'Эллинг';
		}

		if ($this->strpos_arr($text, array(
				'помещения свободного назначения',
				'коммерческ',
				'для бизнеса',
				'кабинет',
				'в аренду сто',
				'в аренду действующее кафе',
				'продам действующее кафе',
				'в аренду кафе',
				'в аренду бар',
				'кафе действующее продам',
				'торговые площади',
			)) !== false
		) {
			return 'Коммерческая';
		}

		// нужно выше квартиры и дома
		if ($this->strpos_arr($text, array('дачный участок', 'дачу', 'дача', 'дачи')) !== false) {
			return 'Дача';
		}

		if ($this->strpos_arr($text, array('малосем')) !== false) {
			return 'Малосемейка';
		}


		if ($this->strpos_arr($text, array('комнату', 'койко место',
				'койкоместо', 'койко-место', 'подселение',
				'сдам комнату', 'продам комнату',
				'сдаю комнат', 'сдается комнат',
				'продается комнат',
			)) !== false
		) {
			return 'Комната';
		}

		if ($this->strpos_arr($text, array(
				'кв-ру', 'квартир',
				'брежневка', 'сталинк', 'чешка', 'чешку', 'хрущевк',
				'к/к', 'кв.', 'квapтиpу', 'комн.', 'кв-ра',
				'кваритиру', 'кварт', 'двушку', 'двушкa', 'кв-ры', 'ком.кв.',
				'1-комнатная', '2-комнатная', '3-комнатная', '4-комнатная', '5-комнатная',
				'1-комнатные', '2-комнатные', '3-комнатные', '4-комнатные', '5-комнатные',
				'4-5-комнатные', '1кв ', '1кв. ', 'трехкомнатная', 'двухкомнатная'
			)) !== false
		) {
			return 'Квартира';
		}

		if (strpos($text, 'гостинный номер') !== false) {
			return 'Гостинный номер';
		}

		if ($this->strpos_arr($text, array('дом', 'коттедж', 'котедж', 'времянк', 'усадьб')) !== false) {
			return 'Дом';
		}

		if ($this->strpos_arr($text, array(
				'участки',
				'участок',
				'уч-к',
				'зем. участк',
				'зем.участк',
				'земельный участок',
				'земельные участки',
				'землю',
				'продажа земли',
				'продам земел',
				'сдам земел',
				'аренда земл',
				'аренда земел',
				'земельный пай',
				'пай земельный',
				'пай земли',
				'земельного пая',
			)) !== false
		) {
			return 'Земельный участок';
		}

		if (strpos($text, 'временная постройка') !== false) {
			return 'Временная постройка';
		}

		if (strpos($text, 'демонтаж') !== false) {
			return 'Демонтаж';
		}

		if (strpos($text, 'недострой') !== false) {
			return 'Недострой';
		}

		if ($this->strpos_arr($text, array('комната', 'комнаты')) !== false) {
			return 'Комната';
		}

		if (strpos($text, 'гараж') !== false) {
			return 'Гараж';
		}

		//if ( $this->strpos_arr($text, array('бар') ) !== false ) {
		if (preg_match('#([\.,;\- ]+)бар([\.,;\- ]+)#is', $text)) {
			return 'Бар';
		}

		if ($this->strpos_arr($text, array(
				'склад'
			)) !== false
		) {
			return 'Склад';
		}

		if ($this->strpos_arr($text, array('офис')) !== false) {
			return 'Офис';
		}

		if ($this->strpos_arr($text, array('гостиница', 'гостиницу', 'гостиничный бизнес')) !== false) {
			return 'Гостиница';
		}

		if ($this->strpos_arr($text, array('комнатную', 'балкон', 'однокомнатная')) !== false) {
			return 'Квартира';
		}

		if ($this->strpos_arr($text, array('магазин')) !== false) {
			return 'Магазин';
		}

		if ($this->strpos_arr($text, array(
				'промбаз', 'производственно-складск', 'производственно-промышленн'
			)) !== false
		) {
			return 'Промбаза';
		}

		if ($this->strpos_arr($text, array(
				'коммерческая', 'помещение', 'павильон', 'ларек', 'ларьк',
				'здание', 'парикмахерск', 'офис', 'контейнер',
				'торг. площ', 'цех ', 'цех. ', 'бизнес', 'мастерску', 'киоск', 'медцентр',
				'пай', 'торговая площадь', 'базу отдых', 'базa отдых', 'кабинет'
			)) !== false
		) {
			return 'Коммерческая';
		}

		return '';
	}

	public function strpos_arr($haystack, $needle)
	{
		if (!is_array($needle)) $needle = array($needle);
		foreach ($needle as $what) {
			if (($pos = strpos($haystack, $what)) !== false) return $pos;
		}
		return false;
	}

	public function search_price($text)
	{
		$_text = $text;
		$text = strip_tags(strtolower($text));

		// Если точки перед числами с пробелами, то разделяем, это точно не разделитель чисел
		// Решает проблему "1кв. на Колобова,18. 45000у.е. "
		//$text = str_replace(array('. ', ', '), '|', $text);
		$text = preg_replace('#([0-9]+?\.)#is', '|', $text);
		$text = preg_replace('#([0-9]+?\,)#is', '|', $text);

		// Решает проблему " Малахова кургана 1/2 24500у.е. Продам "
		$text = str_replace(array('1/2', '1/3', '1/4', '1/5', '1/6'), '|', $text);

		// Решает проблему "квартиру до 2000 с к/у сниму"
		$text = str_replace(array(' с ', ' без '), ' ', $text);

		$text = str_replace('тыс', '000', $text);

		$text = str_replace(array(' ', '.', ',', '*', '~', '\''), '', $text);

		$text = $this->check_pattern($this->patterns_price, $text);

		if ($text == '') {
			// не нашло совпадений, пробуем все пропарсить без удаления пробелов
			$text = str_replace(array('.', ',', '\'', '!'), ' ', $_text);
			$patterns = array(
				'#([0-9,] гр )#si', // обе англ
				'#([0-9,]гр )#si', // первая англ
			);
			$text = $this->check_pattern($patterns, $text);
		}

		$text = str_replace('туе', '000уе', $text);

		return $text;
	}

	private function check_pattern($pattern, $text)
	{
		if (!is_array($pattern)) {
			$pattern = array($pattern);
		}
		foreach ($pattern as $pat) {
			if (preg_match($pat, $text, $matches)) {
				return $matches[1];
			}
		}
		return false;
	}

	public function phone_exsists($phone)
	{

		if (!is_array($phone)) {
			$phone = array($phone);
		}
		$phones_result = array();
		foreach ($phone as $phone_row) {
			$phone_row = preg_replace('#[^0-9]+#is', '', $phone_row);
			if ($phone_row > 0) {
				$phones_result[] = $phone_row;
			}
		}

		if (count($phones_result) > 0) {
			$phones = implode(',', $phones_result);
			$count = $this->db->super_query("SELECT COUNT(*) as count FROM  " . PREFIX . "_siteparser_phone WHERE phone IN({$phones})");
			if ($count['count'] > 0) {
				return $count;
			}
		}
		return false;
	}

	public function phone_save($adv_id, $phone)
	{
		if (!is_array($phone)) {
			$phone = array($phone);
		}

		$inserts = array();
		$count = 0;

		foreach ($phone as $phone_row) {
			$phone_row = preg_replace('#[^0-9]+#is', '', $phone_row);
			if ($phone_row > 0) {
				$phone_row = $this->db->safesql($phone_row);
				$inserts[] = "('{$this->website_id}', '{$adv_id}', '{$phone_row}')";
				$count++;
			}
		}

		if (count($inserts) > 0) {
			$inserts = implode(',', $inserts);
			$this->db->query("INSERT IGNORE INTO " . PREFIX . "_siteparser_phone(website_id, adv_id, phone) VALUES {$inserts}");
		}

		return $count;
	}

	public function cp1251_to_utf($text)
	{
		return iconv('CP1251', 'UTF-8', $text);
	}

	public function utf_to_1251($text, $ignore = false)
	{
		//Change from translit to ignore IGNORE
		if($ignore) {
			return iconv('UTF-8', 'CP1251//IGNORE', $text);
		}else{
			return iconv('UTF-8', 'CP1251//TRANSLIT', $text);
		}
	}

	public function getImagesSrc($text, $validate_extension = false)
	{
		$images = array();
		preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $text, $media);
		$data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

		foreach ($data as $url) {
			if ($validate_extension) {
				$info = pathinfo($url);
				if (isset($info['extension'])) {
					if (($info['extension'] == 'jpg') ||
						($info['extension'] == 'jpeg') ||
						($info['extension'] == 'gif') ||
						($info['extension'] == 'png')
					)
						array_push($images, $url);
				}
			} else {
				array_push($images, $url);
			}
		}
		return $images;
	}

	public function getImagesHref($text, $validate_extension = false)
	{
		$images = array();
		preg_match_all('/(href)=("|\')[^"\'>]+/i', $text, $media);
		$data = preg_replace('/(href)("|\'|="|=\')(.*)/i', "$3", $media[0]);

		foreach ($data as $url) {
			if ($validate_extension) {
				$info = pathinfo($url);
				if (isset($info['extension'])) {
					if (($info['extension'] == 'jpg') ||
						($info['extension'] == 'jpeg') ||
						($info['extension'] == 'gif') ||
						($info['extension'] == 'png')
					)
						array_push($images, $url);
				}
			} else {
				array_push($images, $url);
			}
		}
		return $images;
	}

	public function find_currency($text)
	{
		// грн-1, дол-2, евро-3, руб-4;
		$text = strtolower($text);
		$signatures = array();
		$signatures[1] = 'грн|uan|гр\.|грив|uah';
		$signatures[2] = 'yе|уe|уе|у\.е\.|\$|usd|долларов|доларов|дол';
		$signatures[3] = 'eur|евро|эвро|€';
		$signatures[4] = 'руб|rub|rur';

		foreach ($signatures as $id => $base) {
			if (preg_match('#(' . $base . ')#si', $text, $matches)) {
				return $id;
			}
		}
		return 0;
	}

	function images_parse($urls)
	{
		$images = array();
		foreach ($urls as $image_url) {
			if (strlen($image_url) < 5) {
				continue;
			}
			if (strpos($image_url, 'http://') === false && strpos($image_url, 'https://') === false) {
				$image_url = 'http://' . $image_url;
			}
			$image = $this->image_parser->fetchImage($image_url);
			$image = $this->validateImage($image, ROOT_DIR . 'img/');
			if ($image) {
				$images[] = $image;
			}
			if (count($images) > 9) {
				break;
			}
		}
		return $images;
	}

	function phones_parse($adv_text)
	{
		$phones = array();
		$items = $this->find_phones(str_replace(' ', '', $adv_text));
		foreach ($items as $item) {
			$item = $this->getNumbers($item);
			if (strlen($item) > 3 && strlen($item) < 16) {
				$phones[] = $item;
			}
		}
		return $phones;
	}

	function find_phones($text, $min_phone_length = 6, $allow_digit_regexp = false)
	{
		$text = preg_replace('/\s[\s]+/', ' ', $text); // Strip multiple spaces

		$patterns_price = $this->patterns_price;
		foreach ($patterns_price as $index => $pattern) {
			$patterns_price[$index] = str_replace('0-9', '0-9\s', $pattern);
		}

		foreach ($patterns_price as $pattern) {
			$text = preg_replace($pattern, ' ', $text);
		}

		$text = preg_replace('#[0-9]{2}-[0-9]{2}-[0-9]{4}-[0-9]{5}#si', ' = ', $text);

		$text = preg_replace('#([0-9\s]\s*км)#si', '', $text);

		$text = str_replace(array(
			'000000',
			'00000',
			//'0000',
			'00 000',
			'0 000'
		), '__', $text);

		$numbers_str = array(
			'один' => '1',
			'два' => '2',
			'три' => '3',
			'четыре' => '4',
			'пять' => '5',
			'шесть' => '6',
			'семь' => '7',
			'восемь' => '8',
			'девять' => '9',
		);
		$text = str_replace(array_keys($numbers_str), array_values($numbers_str), $text);


		$phones_vals = array();
		$phone_codes = '039|050|063|066|067|068|091|093|094|095|096|097|098|099|090';

		// русские кода
		$phone_codes .= '|' . $this->phone_codes_ru;

		// стационарные телефоны
		$phone_codes .= '|031|032|033|034|035|036|037|038|041|042|043|044|045|046|047|048|049|051|052|053|054|055|056|057|058|059|061|062|063|064|065|069';
		$phone_codes_no_zero = ltrim(str_replace('|0', '|', $phone_codes), '0');

		$pattern_phone = array(
			'#\(?\d{3}\)[-\s.]?\d{3}[-\s.]\d{4}#si', // (021) 423-2323
			'#\(?\d{3}\)[-\s.]?\d{4}[-\s.]\d{3}#si', // (050)4244-015
			'#\(?\d{3}\)[-\s.]?\d{3}[-\s.]\d{2}[-\s.]\d{2}#si', // (021) 423-23-23
			'#\(\d{3}\)[-\s]*\d{3}[-\s]*\d{2}[-\s]*\d{2}[-\s]*\(\d{3}\)#si',
			'#\(\d{3}\)[-\s]*\d{3}[-\s]*\d{2}[-\s]*\d{2}[-\s]*\(\d{2}\)#si',
			'#\(\d{3}\)[-\s]*\d{3}[-\s]*\d{2}[-\s]*\d{2}[-\s]*\(\d{1}\)#si',
			'#\(?\d{3}\)[-\s.]?\d{2}[-\s.]\d{2}[-\s.]\d{3}#si', // (021) 42-32-323
			'#\(?\d{3}\)[-\s.]?\d{2}[-\s.]\d{3}[-\s.]\d{2}#si', // (021) 42-322-32
			'#\(?\d{3}\)[-\s.]?\d{1}[-\s.]\d{3}[-\s.]\d{3}#si', // (066) 9-300-200
			'#0\(?\d{2}\)[-\s.]?\d{3}[-\s.]\d{2}[-\s.]\d{2}#si', // 0(50)425-29-39 - // slavyansk.biz там все такие
			'#\(?\d{4}\)[-\s.]?\d{2}[-\s.]\d{2}[-\s.]\d{2}#si', // (0692) 48-17-50
			'#\(?\d{3}\)[-\s.]?\d{7}#si', // +38(099)1966892
			'#0\d{2}[-\s.]?\d{3}[-\s.]\d{6}#si', // 380 991 966892
			'#\(?\d{3}\)[-\s.]?\d{7}#si', // (021) 4232323
			'#\(?\d{2}\)[-\s.]?\d{7}#si', // (21) 4232323

			'#\d{3}[-\s.]\d{2}[-\s.]\d{2}[-\s.]\d{1}[-\s.]\d{2}#si', // 066-91-23-8-23
			'#\d{3}[-\s.]\d{3}[-\s.]\d{2}[-\s.]\d{2}#si', // 095-315-39-71
			'#\d{3}[-\s.]\d{2}[-\s.]\d{2}[-\s.]\d{3}#si', // 050-60-81-555
			'#\d{3}[-\s.]\d{2}[-\s.]\d{3}[-\s.]\d{2}#si', // 099-14-980-14
			'#\d{3}[-\s.]\d{1}[-\s.]\d{3}[-\s.]\d{3}#si', // 066-9-300-200
			'#\d{6}[-\s.]\d{2}[-\s.]\d{2}#si', // 099940 28 27
			'#\d{3}[-\s.]\d{3}[-\s.]\d{3}[-\s.]\d{1}#si', // 095 523 526 0
			'#\d{3}[-\s.]\d{2}[-\s.]\d{2}[-\s.]\d{2}[-\s.]\d{1}#si', // 095-50-44-44-6
			'#380[-\s.]\d{3}[-\s.]\d{6}#si', //must have pririty
			'#\d{3}[-\s.]\d{3}[-\s.]\d{4}#si', // 095-315-3971
			'#\d{4}[-\s.]\d{3}[-\s.]\d{3}#si', // 0999-116-777

			/*
			 * http://allmobile.ua/forum/interesnoe-i-poleznoe/3087-telefonnye-kody-mobil-nyh-operatorov-ukrainy-obnovleno.html
+ 380 39 xxx xx xx - Киевстар (ex-Golden Telecom, Beeline)
+ 380 50 xxx xx xx - МТС-Украина
+ 380 63 xxx xx xx - life:)
+ 380 66 xxx xx xx - МТС-Украина
+ 380 67 xxx xx xx - Киевстар
+ 380 68 xxx xx xx - Киевстар (ex-Beeline UA)
+ 380 91 xxx xx xx - ОГО!Мобильный (ex-Utel)
+ 380 92 xxx xx xx - PEOPLEnet
+ 380 93 xxx xx xx - life:)
+ 380 94 xxx xx xx - Интертелеком
+ 380 95 xxx xx xx - МТС-Украина
+ 380 96 xxx xx xx - Киевстар (включая djuice)
+ 380 97 xxx xx xx - Киевстар (включая djuice)
+ 380 98 xxx xx xx - Киевстар (включая djuice)
+ 380 99 xxx xx xx - МТС-Украина
+ 380 90 xxx xx xx - (контент-провайдеры)

			 * */
			'#(' . $phone_codes . '){1}\d{7}#si', // 0504232323
			'#(телефон|т.|тел)[-\s.]\d{3}[-\s.]\d{2}[-\s.]\d{2}#si', // телефон 423-23-23
			'#(телефон|т.|тел)[-\s.]\d{2}[-\s.]\d{2}[-\s.]\d{2}#si', // телефон 23-23-23
			'#(телефон|т.|тел)[-\s.]\d{1}[-\s.]\d{2}[-\s.]\d{2}#si', // телефон 3-23-23
			'#\d{3}[-\s]\d{3}[-\s]\d{2}#si', // 095-439-26
			'#\d{3}[-\s]\d{2}[-\s]\d{2}#si', // 423-23-23
			'#(' . $phone_codes_no_zero . '){1}\d{7}#si', // 0504232323
		);

		if ($min_phone_length < 7) {
			$pattern_phone[] = '#\d{2}[-\s]\d{2}[-\s]\d{2}#si'; // 23-23-23  (убрал точки, чтобы даты не распознавались)
		}

		if ($min_phone_length < 6) {
			$pattern_phone[] = '#\d{1}[-\s]\d{2}[-\s]\d{2}#si'; // 3-23-23 (убрал точки, чтобы даты не распознавались)
		}

		if ($allow_digit_regexp) {
			$pattern_phone[] = '#\d{' . $min_phone_length . ',11}#si';
		}

		foreach ($pattern_phone as $pattern) {

			preg_match_all($pattern, $text, $phones, PREG_PATTERN_ORDER);
			$text = preg_replace($pattern, '', $text);

			foreach ($phones[0] as $phone) {
				switch ($pattern) {
					case '#\(\d{3}\)[-\s]*\d{3}[-\s]*\d{2}[-\s]*\d{2}[-\s]*\(\d{3}\)#si':
						$ph1=preg_replace('#[^0-9]#is', '', $phone);
						$phones_vals[]=substr($ph1, 0,10);
						$phones_vals[]=substr($ph1, 0,7).substr($ph1, 10);
						break;
					case '#380[-\s.]\d{3}[-\s.]\d{6}#si':
						$ph1 = preg_replace('#[^0-9]#is', '', $phone);
						$phones_vals[]=substr($ph1, 2);
						break;

					case '#\(\d{3}\)[-\s]*\d{3}[-\s]*\d{2}[-\s]*\d{2}[-\s]*\(\d{2}\)#si':
						$ph1=preg_replace('#[^0-9]#is', '', $phone);
						$phones_vals[]=substr($ph1, 0,10);
						$phones_vals[]=substr($ph1, 0,8).substr($ph1, 10);
						break;

					case '#\(\d{3}\)[-\s]*\d{3}[-\s]*\d{2}[-\s]*\d{2}[-\s]*\(\d{1}\)#si':
						$ph1=preg_replace('#[^0-9]#is', '', $phone);
						$phones_vals[]=substr($ph1, 0,10);
						$phones_vals[]=substr($ph1, 0,9).substr($ph1, 10);
						break;

					default:
						$phones_vals[] = preg_replace('#[^0-9]#is', '', $phone);
						break;
				}

			}
		}

		$text = str_replace(array('-', ' '), '', $text);
		preg_match_all('#(' . $phone_codes . '){1}\d{7}#si', $text, $phones, PREG_PATTERN_ORDER); // 0504232323
		foreach ($phones[0] as $phone) {
			$phones_vals[] = preg_replace('#[^0-9]#is', '', $phone);
		}


		foreach ($phones_vals as $index => $phone) {
			// исправление $phone_codes_no_zero
			if (strlen($phone) == 9) {
				$phone = '0' . $phone;
				$phones_vals[$index] = $phone;
			}

			// убираем вначале 8
			if (strlen($phone) == 11 && $phone{0} == 8) {
				$phone = substr($phone, 1);
				$phones_vals[$index] = $phone;
			}
		}

		$phones_vals = array_unique($phones_vals);

		$phone_codes_ru = explode('|', $this->phone_codes_ru);
		// ищем кацапов
		foreach ($phones_vals as $index => $phone) {
			if (in_array(substr($phone, 0, 3), $phone_codes_ru) && strlen($phone) == 10) {
				$phone = '7' . $phone;
				$phones_vals[$index] = $phone;
			}
		}
		$phones_vals = array_unique($phones_vals);
		return $phones_vals;
	}

	public function id_exists($id)
	{
		// ждем 0.02 секунды
		usleep(20000);
		$id = intval($id);
		$q = $this->db->super_query("SELECT id FROM dle_siteparser WHERE website_id='{$this->website_id}' AND url_id='{$id}'");
		$id = intval($q['id']);
		if ($id < 1) {
			return false;
		}
		return $id;
	}

	public function adv_exists($sql = '')
	{
		// ждем 0.02 секунды
		usleep(20000);
		$q = $this->db->super_query("SELECT id FROM dle_siteparser WHERE website_id='{$this->website_id}'" . $sql);
		$id = intval($q['id']);
		if ($id < 1) {
			return false;
		}
		return $id;
	}

	public function md5_exists($md5)
	{
		$q = $this->db->super_query("SELECT id FROM dle_siteparser WHERE website_id='{$this->website_id}' AND adv_md5='{$md5}'");
		$id = intval($q['id']);
		if ($id < 1) {
			return false;
		}
		return $id;
	}

	public function check_md5($var,$id){
		//var_dump($var);
		$md5=md5(serialize($var));
		echo "md5: {$md5} url_id: {$id}\n";
		//if changed id but same data

		// $this->db->query("
		// 	DELETE FROM dle_siteparser
		// 	WHERE website_id='{$this->website_id}'
		// 	AND adv_md5='{$md5}'
		// 	AND url_id<'{$id}'
		// ");
		$this->delete_adv_list("
			website_id='{$this->website_id}'
			AND adv_md5='{$md5}'
			AND url_id<'{$id}'
		");


		//Check if adv exists in DB
		//select last adv with same data and url_id
		$q = $this->db->super_query("
			SELECT id
			FROM dle_siteparser
			WHERE website_id='{$this->website_id}'
			AND adv_md5='{$md5}'
			ORDER BY id DESC
			LIMIT 1
		");

		//if these data are in DB
		if(!empty($q['id'])){
			$id_last=$q['id'];
			echo "\n-----Previous id was: {$q['id']}\n";
			// $this->db->query("
			// 	DELETE FROM dle_siteparser
			// 	WHERE website_id='{$this->website_id}'
			// 	AND (adv_md5='{$md5}' OR url_id='{$id}')
			// 	AND id!='{$id_last}'
			// ");
			$this->delete_adv_list("
				website_id='{$this->website_id}'
				AND (adv_md5='{$md5}' OR url_id='{$id}')
				AND id!='{$id_last}'
			");
			echo "\nI already have this adv.\n";
			return false; //have this adv in DB
		}else{
			// $this->db->query("
			// 	DELETE FROM dle_siteparser
			// 	WHERE website_id='{$this->website_id}'
			// 	AND url_id='{$id}'
			// ");
			$this->delete_adv_list("
				website_id='{$this->website_id}'
				AND url_id='{$id}'
			");
		}
		return $md5;
	}


	public function log_error($line = 0, $exeption = '', $comment = '')
	{
		$website_id = intval($this->website_id);
		$backtrace = debug_backtrace();
		$class = $this->db->safesql($backtrace[1]['class']);
		$function = $this->db->safesql($backtrace[1]['function']);
		$base_url = $this->db->safesql($this->base_url);
		$date_add = date("Y-m-d H:i:s");
		$http_error = $this->db->safesql($this->http->error);

		$file_contents = $this->db->safesql(file_get_contents($backtrace[1]['file']));
		$website_contents = $this->http->result;

		if (mb_check_encoding($website_contents, 'UTF-8') && !mb_check_encoding($website_contents, 'Windows-1251')) {
			$website_contents = mb_convert_encoding($website_contents, 'Windows-1251', 'UTF-8');
		}

		if (strlen($website_contents) > 150000) {
			$website_contents = substr($website_contents, 0, 150000);
		}

		$website_contents = $this->db->safesql($website_contents);
		$exeption = $this->db->safesql($exeption);
		$backtrace = $this->db->safesql(serialize($backtrace));
		$comment = $this->db->safesql($comment);

		$website_contents = ''; //$this->db->safesql($this->http->result);
		$backtrace = '';
		$file_contents = '';

		$this->db->query("INSERT INTO errors (
            website_id, class, function, line, url, date_add, http_error, file_contents, website_contents, exeption, backtrace, comment
        ) VALUES (
            '$website_id', '$class', '$function', '$line', '$base_url', '{$date_add}', '$http_error', '$file_contents', '$website_contents', '{$exeption}', '{$backtrace}', '{$comment}'
        )");
	}

	public function phoneFix($phones)
	{
		if (!is_array($phones)) {
			$phones = explode('|', $phones);
		}
		$phones_fix = array();
		if (is_array($phones)) {
			foreach ($phones as $phone) {
				$phone = $this->getNumbers($phone);
				$len = strlen($phone);

				if ($len < 4 || $len > 18) {
					continue;
				}
				if ($len == 9) {
					// 67 123 12 12 - ошибочный не хватает 0
					$phone = '0' . $phone;
				}

				if ($len > 11 && $phone{0} == 3 && $phone{1} == 8) {
					// 380 067 123 12 12 - ошибочный лишний 0
					// 38 050 03 10 161
					$phone = substr($phone, -10);
				}

				if ($len == 11 && $phone{0} == 8) {
					// 8 050 03 10 161
					$phone = substr($phone, -10);
				}
				$phones_fix[] = $phone;
			}
		}
		return $phones_fix;
	}

	public function parsePhones($string)
	{
		$phones = array();
		$items = $this->find_phones($string);
		foreach ($items as $item) {
			$item = $this->getNumbers($item);
			if (strlen($item) > 4 && strlen($item) < 15) {
				$phones[] = $item;
			}
		}
		return $phones;
	}

	/**
	 * проверяет наличие телефона в базе телефонов риєлторов
	 * @param $phone
	 * @return bool
	 */
	public function is_phone_rieltor($phone){
		if(strlen($phone) < 10) return false;
		// исключения
		if($phone == '0000000000') return false;
		if($phone == '0800509100') return false;
		if($phone == '0443311721') return false;

		$phone = substr($phone, -10);
		$q = $this->db->super_query("SELECT COUNT(id) AS is_phone FROM `phone_rieltor` WHERE `phone` LIKE '%".$phone."'");
		return intval($q['is_phone'])>0;
	}

	public function save_phone_rieltor($phone, $city, $email, $name){
		if(strlen($phone) < 10) return false;
		$phone = substr($phone, -10);
		$sql = 'INSERT INTO `phone_rieltor` SET `phone` = "'.$phone.'", `city` = "'.$city.'", `email` = "'.$email.'", `name`="'.$name.'", `dt_update`=NOW() ON DUPLICATE KEY UPDATE `city` = "'.$city.'", `email` = "'.$email.'", `name`="'.$name.'", `count`=`count`+1, `dt_update`=NOW()';
		$this->db->query($sql);
		return true;
	}

	/**
	 * Добавляет в базу новый пост
	 *
	 * @param mixed $url - ссылка обхявлекния на сайте
	 * @param mixed $url_id - идентификатор страницы объявления на сайте
	 * @param array | string $phones -
	 * @param mixed $cat_name_type
	 * @param mixed $cat_title_type_object
	 * @param mixed $adv_text
	 * @param mixed $author_adv
	 * @param mixed $city
	 * @param mixed $adv_md5
	 * @param mixed $price
	 * @return int
	 */
	public function save_post($url, $url_id, $phones, $cat_name_type, $cat_title_type_object, $adv_text, $city, $adv_md5, $price, $currency = 0, $post_data = array())
	{


		// максимальное время работы парсера 4 часа
		$time_parser = time() - $this->time_start;
		if ($time_parser > (60 * 60 * 4)) {
			$this->log_error(__LINE__,'forced termination of the program', 'total time: '.$time_parser.' sec');

		}
		if(strlen($cat_name_type) == 0){
			echo "Core.php -> Type object is empty\n";
			return false;
		}

		if(strlen($cat_title_type_object) == 0){
			echo "Core.php -> Type operation is empty\n";
			return false;
		}

		$adv_text = str_replace(array("\n", "\r", "\t"), ' ', $adv_text);
		$adv_text = str_replace(chr(160), " ", $adv_text); // non-breaking space character
		$adv_text = preg_replace('/\s[\s]+/', ' ', $adv_text); // Strip multiple spaces
		$adv_text = strip_tags($adv_text);
		$adv_text = htmlspecialchars_decode($adv_text);
		$adv_text = str_replace(array('•'), ' ', $adv_text);
		$adv_text = preg_replace('/\s[\s]+/', ' ', $adv_text); // Strip multiple spaces
		$adv_text = trim($adv_text);

		if ($this->strpos_arr($adv_text, array('болгария', 'болгарии')) !== false) {
			$attributes['disabled'] = 1;
		}
		$price= intval(preg_replace('/[^\d-]/', '', $price));

		//If empty price - remember approved=2 and mark not for output
		// =====> решение Александра 14.11.2018 - вносим все объекты
		// =====> решение Александра 27.03.2019 - не импортируем 'Сдают', 'Снимают', 'Меняют',
		// =====> решение Александра 29.03.2019 - не импортируем 'Снимают', 'Меняют',
		if (( $cat_title_type_object == 'Снимают' || $cat_title_type_object == 'Меняют')) {
			if (!empty($post_data['images']))
				foreach ($post_data['images'] as $image_path) {
					@unlink(ROOT_DIR . '/img/' . $image_path);
				}
			$post_data['approved'] = 2;
		}

		//If low price - remember approved=2 and mark not for output
		// =====> решение Александра 14.11.2018 - вносим все объекты
		// =====> решение Александра 13.03.2019 - От 1 до 3000 дол - не грузим. Со стоимостью = 0 - загружаем
		// есть источники, для которых в любом случае цена загружается
		// https://realtors-partners.club/tasks/view/204535
		// Прошу установить минимальную цену для объектов 2000 долл Каменское
		$priceUAH = 81000;
		$priceUSD = 3000;
		$priceEUR = 3000;
		$priceRUS = 180000;
		if ($city == 'Каменское') {
			$priceUAH = 54000;
			$priceUSD = 2000;
			$priceEUR = 2000;
			$priceRUS = 180000;
		}

		if (!in_array($this->website_id, array(100, 104))) {
			if ($cat_title_type_object == 'Продают') {
				if ($price > 0) {
					if (($currency == 1 && $price < $priceUAH)
						|| ($currency == 2 && $price < $priceUSD)
						|| ($currency == 3 && $price < $priceEUR)
						|| ($currency == 4 && $price < $priceRUS)
					) {
						if (!empty($post_data['images']))
							foreach ($post_data['images'] as $image_path) {
								@unlink(ROOT_DIR . '/img/' . $image_path);
							}
						$post_data['approved'] = 2;
					}
				}
			}
		}


		//If low price - remember approved=2 and mark not for output
		// =====> решение Александра 22.10.2019 - От 1 до 3000 грн - не грузим. Со стоимостью = 0 - загружаем
		// есть источники, для которых в любом случае цена загружается
		if(!in_array($this->website_id,array(100, 104))) {
			if ($cat_title_type_object == 'Сдают') {
				if ($price > 0) {
					if (($currency == 1 && $price < 3000)
						|| ($currency == 2 && $price < 120)
						|| ($currency == 3 && $price < 120)
						|| ($currency == 4 && $price < 7600)
					) {
						if (!empty($post_data['images']))
							foreach ($post_data['images'] as $image_path) {
								@unlink(ROOT_DIR . '/img/' . $image_path);
							}
						$post_data['approved'] = 2;
					}
				}
			}
		}

		//If garage or dacha - remember approved=2 and mark not for output
		// =====> решение Александра 14.11.2018 - вносим все объекты
		// =====> решение Александра 29.03.2019 - не вносим фильтруем гараж и дачный участок
		// есть источники, для которых в любом случае все типы загружаются
		if(!in_array($this->website_id,array(100, 104))) {
			if ($city != 'Севастополь') {
				if ($cat_name_type == 'Дача') {
					if (!empty($post_data['images']))
						foreach ($post_data['images'] as $image_path) {
							@unlink(ROOT_DIR . '/img/' . $image_path);
						}
					$post_data['approved'] = 2;
				}
			}
		}
		//If garage or dacha - remember approved=2 and mark not for output
		// =====> решение Александра 14.11.2018 - вносим все объекты
		// =====> решение Александра 29.03.2019 - не вносим фильтруем гараж и дачный участок
		if( $cat_name_type == 'Гараж' ){
			if(!empty($post_data['images'] ))
				foreach ($post_data['images'] as  $image_path) {
					@unlink(ROOT_DIR . '/img/'.$image_path);
				}
			$post_data['approved'] = 2;
		}

		// =====> решение Александра 12.10.2020 - не вносим фильтруем МАФ (киоск) ларек павильонов, ларьков
		if ($this->strpos_arr($adv_text, array(
				'МАФ', 'киоск', 'ларек', 'ларёк', 'павильон'
			)) !== false
		) {
			if (!empty($post_data['images']))
				foreach ($post_data['images'] as $image_path) {
					@unlink(ROOT_DIR . '/img/' . $image_path);
				}
			$post_data['approved'] = 2;
		}

		if(!is_numeric($url_id))$url_id = intval($url_id);

		$phones = $this->phoneFix($phones);
		$phones = array_unique($phones);

		// нет телефона - нет объявления
		if(count($phones) == 0){
			echo "phone is empty\n";
			return false;
		}

		// есть источники, для которых в любом случае все телефоны НЕ риэлторы, поэтому проверка не выполняется
		if(!in_array($this->website_id,array(100, 104))) {
			if ((isset($post_data['is_realtor'])) AND ($post_data['is_realtor'] > 0)) {
				$email = isset($post_data['email']) ? $post_data['email'] : '';
				$email = str_replace(array(" ", "\r\n", "\r", "\n"), '', $email);

				$name = isset($post_data['author']) ? Helper::onlyText($post_data['author'], 3) : '';
				foreach ($phones as $phone) {
					$this->save_phone_rieltor($phone, $city, $email, $name); // сохранение риэлтора в базе
				}
			} else {
				foreach ($phones as $phone) {
					if ($this->is_phone_rieltor($phone)) $post_data['is_realtor'] = 1; // признак риелтора, определенного по телефонной базе
				}
			}
		}

		// =====> решение Александра 18.06.2019 не импортируем Риэлтор-Покупают Риэлтор-Снимают
		if ((isset($post_data['is_realtor'])) AND ($post_data['is_realtor'] > 0)) {
			if ($cat_title_type_object == 'Покупают') {
				echo "rieltor-bye is not imported\n";
				return false;
			}
			if ($cat_title_type_object == 'Снимают') {
				echo "rieltor-rent is not imported\n";
				return false;
			}
		}

		// Анализ цены 15.05.19 Изменения Андрея
		$price_n = 0;
		if ($currency == 1) {
			$price_n = $price; // Гривны
		}
		if ($currency == 2) {
			$price_n = $price * 28; // Долары
		}
		if ($currency == 4) {
			$price_n = $price * 0.36; // Рубли
		}

		if (isset($post_data['area_all']) && $price_n > 0 && $post_data['area_all'] > 0) {
			if ($price_n < 90000 && $cat_title_type_object == 'Продают' && $post_data['area_all'] < 50) {
				$inserts['approved'] = 5;
			}
			if ($price_n < 3000 && $cat_title_type_object == 'Сдают' && $post_data['area_all'] < 50) {
				$inserts['approved'] = 5;
			}
		}

		$phones_sql = implode('|', $phones);

		$phones_sql = $this->db->safesql($phones_sql);
		$date_add = date("Y-m-d H:i:s");

		// определение адреса обратным геокодированием и сохранение его значения
		GeocogingCache::get_geogoding_address($post_data['latitude'], $post_data['longitude']);

		$inserts = array(
			'website_id' => $this->website_id,
			'url' => $url,
			'url_id' => $url_id,
			'phone' => $phones_sql,
			'type' => $cat_name_type,
			'type_object' => $cat_title_type_object,
			'alltext' => $adv_text,
			'date_add' => $date_add,
			'city_name' => $city,
			'adv_md5' => $adv_md5,
			'price' => $price,
			'currency' => $currency,
			'row_type' => $this->row_type,
			'approved' => empty($post_data['approved']) ? 1 : intval($post_data['approved'])
		);

		$email = '';

		$extra_fields = array();

		if (count($post_data) > 0) {
			foreach ($post_data as $data_key => $value) {
				$value_original = '';
				if ($data_key != 'images') {
					$value_original = $value;
					$value = strtolower($value);
				}
				switch ($data_key) {
					case 'images':
						if (is_array($value)) {
							$inserts['images'] = implode('|', $value);
						}
						break;
					case 'rooms_count':
						$inserts['rooms_count'] = intval($value);
						break;
					case 'area_all':
						$value = str_replace(' ', '', Helper::cleanAttributeText($value));
						$value = str_replace(array('м2', 'm2', 'м.2', 'm.2'), '', $value);
						$inserts['area_all'] = intval($value);
						break;
					case 'area_living':
						$value = str_replace(array('м2', 'm2', 'м.2', 'm.2'), '', $value);
						$inserts['area_living'] = intval($value);
						break;
					case 'area_kitchen':
						$value = str_replace(array('м2', 'm2', 'м.2', 'm.2'), '', $value);
						$inserts['area_kitchen'] = intval($value);
						break;
					case 'floor_all':
						$inserts['floor_all'] = intval($value);
						break;
					case 'floor':
						$inserts['floor'] = intval($value);
						break;
					case 'ground':
						// в 1С нужно передавать в гектарах
						// одна сотка (100 м?) равна одной сотой части гектара
						$value = str_replace(' ', '', Helper::cleanAttributeText($value));
						$array = array(
							'сот'
						);
						if ($this->strpos_arr($value, $array) !== false) {
							$value = floatval($value);
							if ($value > 0) {
								$value = $value / 100;
							}
						} else {
							$value = floatval($value);
						}
						if ($value > 0) {
							$value = number_format($value, 3, '.', '');
						}
						$inserts['ground'] = $value;
						break;
					case 'walls':
						$inserts['walls'] = Helper::cleanAttributeText($value);
						break;
					case 'geocoding_log':
						$inserts['geocoding_log'] = $value_original;
						break;
					case 'is_realtor':
						$inserts['is_realtor'] = intval($value);
						break;
					case 'email':
						$email = Helper::findEmail($value_original);
						break;
					case 'row_type':
						$row_type = intval($value);
						$inserts['row_type'] = $row_type;
						break;
					default:
						$extra_fields[$data_key] = $value_original;//Helper::encodeExtraRow($data_key, $value_original);
						break;
				}
			}
		}

		if ($attributes['disabled'] && $inserts['images']) {
			$images = explode('|', stripslashes($inserts['images']));
			foreach ($images as $image) {
				@unlink(ROOT_DIR . 'img/' . $image);
			}
		}

		if (empty($email)) {
			$email_array = Helper::findEmail($adv_text);
			$email = reset($email_array);
		}

		if (!empty($email)) {
			$extra_fields['email'] = implode('|',$email);
		}

		if (!empty($extra_fields['author'])) {
			$extra_fields['author'] = Helper::onlyText($extra_fields['author'], 3);
		}

		$extra_fields_sql = array();
		foreach ($extra_fields as $extra_key => $extra_val) {
			$extra_fields_sql[] = Helper::encodeExtraRow($extra_key, $extra_val);
		}
		$extra_fields_sql = implode('||', $extra_fields_sql);
		$inserts['extra_fields'] = $extra_fields_sql;

		$insert_id = 0;
		if ($this->debug_no_insert) {
			if ($this->new_items < $this->debug_no_insert_limit) {
				echo '<table width="100%" border="1" cellspacing="0" cellpadding="1">';
				foreach ($inserts as $key => $val) {
					echo '<tr><td width="200" bgcolor="#eeeeec">' . $key . '</td><td bgcolor="#eeeeec">' . htmlspecialchars($val) . '</td></tr>';
				}
				echo '</table><br />';
			}
		} else {
			//print_r($post_data);
			if (empty($post_data['insert_id'])) {
				$inserts_keys = implode(', ', array_keys($inserts));
				$inserts_values = array_values($inserts);
				$inserts_values_str = array();
				foreach ($inserts_values as $val) {
					$inserts_values_str[] = "'" . $this->db->safesql($val) . "'";
				}
				$inserts_values_str = implode(', ', $inserts_values_str);


				$sql = "INSERT INTO dle_siteparser (" . $inserts_keys . ') VALUES (' . $inserts_values_str . ')';
				//echo $sql."********************\n";

				$this->db->query($sql);
				$insert_id = $this->db->insert_id();
			} else {
				// загрузка старых данных с 1с
				$sql = "SELECT * FROM dle_siteparser WHERE id=" . $insert_id;
				$row = $this->db->super_query($sql);
				if ($row['phone'] != '' && $inserts['phone'] == '') {
					echo "\n\n!!!! ============== unset phone ============== ({$insert_id}) !!!!\n\n";
					unset($inserts['phone']);
				}
				if ($row['type'] != '' && $inserts['type'] == '') {
					echo "\n\n!!!! ============== unset type ============== ({$insert_id}) !!!!\n\n";
					unset($inserts['type']);
				}
				if ($row['city_name'] != '' && $inserts['city_name'] == '') {
					echo "\n\n!!!! ============== unset city_name ============== ({$insert_id}) !!!!\n\n";
					unset($inserts['type']);
				}
				if ($row['type_object'] != '' && $inserts['type_object'] == '') {
					echo "\n\n!!!! ============== unset type_object ============== ({$insert_id}) !!!!\n\n";
					unset($inserts['type_object']);
				}
				if ($row['images']) {
					$images = explode('|', stripslashes($row['images']));
					foreach ($images as $image) {
						@unlink(ROOT_DIR . 'img/' . $image);
					}
				}

				if ($row['phone'] != '' && $row['phone'] != $inserts['phone']) {
					echo "\n\n" . '!!!! ============== OLD phone ' . $row['phone'] . ' NEW phone: ' . $row['phone'] . " ============== ({$insert_id}) !!!!\n\n";
				}

				if ($row['url'] != '') {
					unset($inserts['url']);
				}


				$sql = array();
				$inserts['approved'] = empty($post_data['approved']) ? 1 : intval($post_data['approved']);
				foreach ($inserts as $field => $data) {
					$sql[] = $field . "='" . $this->db->safesql($data) . "'";
				}
				$sql = implode(', ', $sql);
				$insert_id = (int)$post_data['insert_id'];
				//echo "\nUpdate $insert_id\n";
				$sql = "UPDATE dle_siteparser SET " . $sql . " WHERE id=" . $insert_id;
				$this->db->query($sql);
			}

			// if error on save then delete linked images
			if($insert_id === false && !empty($post_data['images']))
				foreach ($post_data['images'] as  $image_path) {
					@unlink(ROOT_DIR . '/img/'.$image_path);
				}
			// ждем 0.04 секунды
			usleep(40000);
		}

		$this->new_items++;

		if ($this->debug_die_after > 0 && $this->new_items >= $this->debug_die_after) {
			die_me();
		}
		return $insert_id;
	}


	public function save_rieltor($url, $url_id, $phones, $rieltor_name, $city,$region,$district, $adv_md5)
	{

		if(!is_numeric($url_id))$url_id = intval($url_id);

		$phones = $this->phoneFix($phones);
		$phones = array_unique($phones);
		$phones_sql = implode('|', $phones);
		$phones_sql = $this->db->safesql($phones_sql);
		$rieltor_name = $this->db->safesql($rieltor_name);
		$date_add = date("Y-m-d H:i:s");

		$inserts = array(
			'website_id' => $this->website_id,
			'url' => $url,
			'url_id' => $url_id,
			'phone' => $phones_sql,
			'rieltor_name' => $rieltor_name,
			'date_add' => $date_add,
			'city_name' => $city,
			'region' => $region,
			'district' => $district,
			'adv_md5' => $adv_md5,
		);

		$inserts_keys = implode(', ', array_keys($inserts));
		$inserts_values = array_values($inserts);
		$inserts_values_str = array();
		foreach ($inserts_values as $val) {
			$inserts_values_str[] = "'" . $this->db->safesql($val) . "'";
		}
		$inserts_values_str = implode(', ', $inserts_values_str);

		$sql = "INSERT IGNORE INTO dle_rieltor (" . $inserts_keys . ') VALUES (' . $inserts_values_str . ')';
		$this->db->query($sql);
		$insert_id = $this->db->insert_id();

		return $insert_id;
	}


	protected function competitorAdvLog($attributes)
	{
		$url = $attributes['url'];
		$adv_md5 = $attributes['adv_md5'];
		$url_id = (int)$attributes['url_id'];
		$phones = $attributes['phones'];
		$page = isset($attributes['page']) ? (int)$attributes['page'] : (int)$this->page;
		$city_name = isset($attributes['city_name']) ? (int)$attributes['city_name'] : $this->def_city;

		$sql_add = array();
		if ($url_id) {
			$sql_add[] = "url_id='{$url_id}'";
		}
		if ($adv_md5) {
			$adv_md5 = $this->db->safesql($adv_md5);
			$sql_add[] = "adv_md5='{$adv_md5}'";
		}

		if (count($sql_add) > 0) {
			$sql_add = ' AND ' . implode(' AND ', $sql_add);
		} else {
			return false;
		}

		$this->db->query("SELECT id FROM dle_competitor WHERE website_id='{$this->website_id}'{$sql_add} LIMIT 1");
		if ($this->db->num_rows() != 0) {
			return false;
		}

		$phones = $this->phoneFix($phones);

		$phones_sql = is_array($phones) ? implode('|', $phones) : $phones;
		$date_add = date("Y-m-d H:i:s");
		$sql = "INSERT INTO dle_competitor (
			website_id,
			url,
			url_id,
			adv_md5,
			phone,
			date_add,
			city_name,
			row_type,
			page
		) VALUES (
			'{$this->website_id}',
			'" . $this->db->safesql($url) . "',
			'{$url_id}',
			'{$adv_md5}',
			'{$phones_sql}',
			'{$date_add}',
			'{$city_name}',
			'{$this->row_type}',
			'{$page}'
		)";
		$this->db->query($sql);
		$insert_id = $this->db->insert_id();
		return $insert_id;
	}

	protected function competitorPhonesSave($data)
	{
		$inserted = 0;
		$data['phones'] = $this->phoneFix($data['phones']);
		$phones = is_array($data['phones']) ? $data['phones'] : explode('|', $data['phones']);
		foreach ($phones as $phone) {
			$phone = $this->getNumbers($phone);
			if (strlen($phone) > 4 && strlen($phone) < 15) {
				$phone = $this->db->safesql($phone);
				$row = $this->db->super_query("SELECT * FROM dle_competitor_phones WHERE phone='{$phone}'");
				if (isset($row['id']) && $row['id'] > 0) {
					// found
					$this->db->query("UPDATE dle_competitor_phones SET count=count+1 WHERE id='{$row['id']}'");
				} else {
					$fio = $this->db->safesql($data['fio']);
					$url = $this->db->safesql($data['url']);
					$city_name = $this->db->safesql($data['city_name']);
					$competitor_id = $this->db->safesql($data['competitor_id']);
					$website_id = $this->db->safesql($data['website_id']);
					$date_add = date("Y-m-d H:i:s");
					$this->db->query("INSERT INTO dle_competitor_phones (
						phone, fio, date_add, url, competitor_id, city_name, website_id
					) VALUES (
						'{$phone}', '{$fio}', '{$date_add}', '{$url}', '{$competitor_id}', '{$city_name}', '{$website_id}'
					)");
					$inserted++;
				}
			}
		}

		return $inserted;
	}

	public function insert_prepare($inserts)
	{
		//$allowed_arguments = array('url_id', 'adv_md5');
		if (empty($inserts['url_id']) && empty($inserts['adv_md5'])) {
			return false;
		}

		$date_add = date("Y-m-d H:i:s");
		$inserts['date_add'] = $date_add;
		$inserts['website_id'] = $this->website_id;
		$inserts['approved'] = 0;

		$inserts_keys = implode(', ', array_keys($inserts));
		$inserts_values = array_values($inserts);
		$inserts_values_str = array();
		foreach ($inserts_values as $val) {
			$inserts_values_str[] = "'" . $this->db->safesql($val) . "'";
		}
		$inserts_values_str = implode(', ', $inserts_values_str);

		$sql = "INSERT INTO dle_siteparser (" . $inserts_keys . ') VALUES (' . $inserts_values_str . ')';
		echo $sql . "\n";

		$this->db->query($sql);
		return $this->db->insert_id();
	}

	public function getNumbers($string)
	{
		return preg_replace('/\D/', '', $string);
	}

	public function __get($property)
	{
		if ($property == 'tpl') {
			include_once(ROOT_DIR . 'core/Fix_Templates.php');
			$this->tpl = new Fix_Templates();
			return $this->tpl;
		}
	}

	public function correct_address($addr){
		$res=array();
		$a = explode(',', $addr);
		if(count($a)>1) {
			foreach ($a as $el) {
				if (strlen($el) > 2) {
					$res[] = $el;
				} else {
					if (strlen($this->getNumbers($el)) > 0) $res[] = $el;
				}
			}
		}else{
			return $addr;
		}
		return implode(',',$res);
	}

	/**
	 * Удаление списка объявлений вместе с фото
	 * @param $sql_where
	 */
	protected function delete_adv_list($sql_where)
	{
		$this->db->query("SELECT id, images FROM `dle_siteparser` WHERE " . $sql_where . " LIMIT 500");
		$ids = array();
		while ($row = $this->db->get_row()) {
			if ($row['images']) {
				$images = explode('|', $row['images']);
				foreach ($images as $image) {
					@unlink(ROOT_DIR . 'img/' . $image);
				}
			}
			$ids[] = $row['id'];
		}

		if (count($ids) > 0) {
			$ids_sql = implode(',', $ids);
			$this->db->query("DELETE FROM `dle_siteparser` WHERE id IN ($ids_sql)");
		}
	}

	public function validateImage($file_name, $dir)
	{
		if ($file_name == '') {
			return false;
		}

		$pathinfo = pathinfo($file_name);
		$new_file_name = $file_name;
		if ($pathinfo['extension'] !== 'jpg') {
			$new_file_name = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.jpg';

			/**
			 * Проверяем существование такого файла
			 */
			while (file_exists($dir . $new_file_name) == true) {
				$new_file_name = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-' . Helper::randomString(mt_rand(1, 8)) . '.jpg';
			}

			if (!rename($dir . $file_name, $dir . $new_file_name)) {
				// ошибка, возвращаем старое ися файла
				return $file_name;
			}
		}

		list($width, $height, $type, $attr) = @getimagesize($dir . $new_file_name);
		if ($type == 2) {
			$image = @imagecreatefromjpeg($dir . $new_file_name);
		} elseif ($type == 3) {
			$image = @imagecreatefrompng($dir . $new_file_name);
		} elseif ($type == 1) {
			$image = @imagecreatefromgif($dir . $new_file_name);
		} else {
			if (!@unlink($dir . $file_name)) {
				@chmod($dir . $file_name, 0666);
				@unlink($dir . $file_name);
			}
			return false;
		}
		//protection from Fatal error: Allowed memory size of 536870912 bytes exhausted
		// $npixels=$width*$height;
		// $rat23=$npixels/8000000;
		// if($rat23>1){
		// 	$width=ceil($width/$rat23);
		// 	$height=ceil($height/$rat23);
		// }
		if($width>3000){
			$cr_width=3000;
			$dx=ceil(($width-3000)/2);
		}else{
			$cr_width=$width;
			$dx=0;
		}
		if($height>3000){
			$cr_height=3000;
			$dy=ceil(($height-3000)/2);
		}else{
			$cr_height=$height;
			$dy=0;
		}

		$image_new = imagecreatetruecolor($cr_width, $cr_height);
		imagefill($image_new, 0, 0, imagecolorallocate($image_new, 255, 255, 255));
		imagealphablending($image_new, true);
		imagecopy($image_new, $image, 0, 0, $dx, $dy, $cr_width, $cr_height);
		imagedestroy($image);

		if (!imagejpeg($image_new, $dir . $new_file_name, 85)) {
			imagedestroy($image_new);

			if (!@unlink($dir . $file_name)) {
				@chmod($dir . $file_name, 0666);
				@unlink($dir . $file_name);
			}

			return false;
		}
		imagedestroy($image_new);

		if(!file_exists($dir . $new_file_name)){
			$this->log_error(__LINE__, $new_file_name.' file does not exist');
			return false;
		}
		return $new_file_name;
	}

	protected function advUpGetCurrentDate()
	{
		return date('d.m.Y');
	}

	protected function findPerDay($text, $add_signatures = array())
	{
		$array = array(
			'Дома посуточно, почасово',
			'Квартиры с почасовой оплатой',
			'Квартиры посуточно',
			'Квартира посуточно',
			'Квартиру посуточно',
			'Комнаты посуточно',
			'сдам посуточно',
			'сдаю посуточно',
			'свою посуточно',
			'своя посуточно',
			'сдается посуточно',
			'оплата посуточно',
			'оплату посуточно',
			'посуточно сво',
			'посуточно сда',
			'посуточно, почасово',
			'почасово, посуточно',
			'(посуточно)',
			'(почасово)',
			'омнаты посуточно',
			'омнату посуточно',
			', посуточно,',
			'посуточно однокомна',
			'посуточно двухкомнат',
			'посуточно трехкомн',
			' посуточно от',
			' посуточно - от',
			'дом посуточно',
			'посуточная аренд',
			'посуточно/почасово',
			'посуточно / почасово',
			'аренда посуточная',
		);

		$array = array_merge($array, $add_signatures);

		if ($this->strpos_arr($text, $array) !== false) {
			return true;
		}
		return false;
	}

	protected function isDisabled($id)
	{
		$adv = $this->db->super_query("SELECT extra_fields FROM dle_siteparser WHERE id='{$id}'");
		$extra_fields = stripslashes($adv['extra_fields']);
		$extra_fields = Helper::decodeExtraFields($extra_fields);
		if ($extra_fields['disabled']) {
			echo "\n\n\n !!!!!!!!!!!!!!!!!! DISABLED !!!!!!!!!!!!!!!!!! \n\n\n";
			return true;
		}
		return false;
	}

	protected function advUpId($adv_id, $last_date_add_new, $my_method = false)
	{
		// если нужно обноявлять объявление, то загружаем дату последнего обновления
		$extra_fields = $this->db->super_query("SELECT extra_fields FROM dle_siteparser WHERE id='{$adv_id}'");
		$extra_fields = stripslashes($extra_fields['extra_fields']);
		$extra_fields = Helper::decodeExtraFields($extra_fields);
		//echo "\n\n EXTRA FIELDS:\n";
		//var_dump($extra_fields);

		$my_method_ok = false;
		if ($my_method) {
			$last_date_add_new = $this->advUpGetCurrentDate();

			$last_date_add_new_time = strtotime($last_date_add_new);
			$last_date_add_old_time = 0;
			if ($extra_fields['last_date_add']) {
				$last_date_add_old_time = strtotime($extra_fields['last_date_add']);
			}
			if (
			($last_date_add_new_time
				<
				$last_date_add_old_time + (60 * 60 * 24 * 5))
			) {
				$my_method_ok = true;
			}
		}

		if ($my_method_ok) {
			echo "\n LAST DATE ADD IS: " . $last_date_add_new . " NOT CHANGED for id $adv_id BY MY METHOD\n";
			return false;
		} elseif ($extra_fields['last_date_add'] == $last_date_add_new) {
			echo "\n LAST DATE ADD IS: " . $last_date_add_new . " NOT CHANGED for id $adv_id\n";
			return false;
		} else {
			echo "\n LAST DATE ADD IS: " . $last_date_add_new . " IS CHANGED for id $adv_id!!!!!!!!!! ";
			$last_date_add_old = $extra_fields['last_date_add'];
			$extra_fields['last_date_add'] = $last_date_add_new;
			$extra_fields = Helper::encodeExtraFields($extra_fields);
			$extra_fields = $this->db->safesql($extra_fields);
			$this->db->query("UPDATE dle_siteparser SET extra_fields='{$extra_fields}' WHERE id='{$adv_id}'");
			// Делаем обновление обяъвления
			$new_id = $this->updateAdvId($adv_id, $last_date_add_new, $last_date_add_old);
			echo "NEW ID IS {$new_id}\n";
			return $new_id;
		}
	}

	protected function updateAdvId($id, $last_date_add_new, $last_date_add_old)
	{
		$insert_id = 0;
		try {
			$this->db->query("START TRANSACTION");
			$result = $this->db->query('SHOW COLUMNS FROM dle_siteparser');
			$columns = array();
			while ($row = $this->db->get_row($result)) {
				$columns[] = $row['Field'];
			}
			unset($columns[0]);
			$row = $this->db->super_query("SELECT " . implode(",", $columns) . " FROM `dle_siteparser` WHERE `id` = " . $id);
			if (!empty($row['phone'])) {
				$this->db->insert('siteparser', $row);
				$insert_id = $this->db->insert_id();

				$this->db->query("DELETE FROM dle_siteparser WHERE id='{$id}'");
				$date_add = date("Y-m-d H:i:s");
				$this->db->insert(
					'change_id_log',
					array(
						'website_id' => $this->website_id,
						'old_id' => $id,
						'new_id' => $insert_id,
						'url_id' => $row['url_id'],
						'last_date_add_old' => $last_date_add_old,
						'last_date_add_new' => $last_date_add_new,
						'date_add' => $date_add
					)
				);
			}

			$this->db->query("COMMIT");
		} catch (Exception $e) {
			$this->db->query("ROLLBACK");
		}
		return $insert_id;
	}

	protected function advUp($id)
	{
		$insert_id = 0;
		try {
			$this->db->query("START TRANSACTION");
			$result = $this->db->query('SHOW COLUMNS FROM dle_siteparser');
			$columns = array();
			while ($row = $this->db->get_row($result)) {
				$columns[] = $row['Field'];
			}
			unset($columns[0]);
			$row = $this->db->super_query("SELECT " . implode(",", $columns) . " FROM `dle_siteparser` WHERE `id` = " . $id);
			$this->db->insert('siteparser', $row);
			$insert_id = $this->db->insert_id();
			$this->db->query("DELETE FROM dle_siteparser WHERE id='{$id}'");
			$this->db->query("COMMIT");
		} catch (Exception $e) {
			$this->db->query("ROLLBACK");
		}
		return $insert_id;
	}

	protected function updatePhonesAdv($adv_id, $phones)
	{
		$phones = $this->phoneFix($phones);
		$phones_sql = implode('|', $phones);
		$phones_sql = $this->db->safesql($phones_sql);
		$sql = "UPDATE dle_siteparser SET phone=" . $phones_sql . " WHERE id=" . $adv_id;
		$this->db->query($sql);
		return true;
	}

	// заглушка для функций перезагрузки фото
	public function reload()
	{
		//    echo $_SERVER['REMOTE_ADDR'];exit;
		echo '[]';
		exit;
	}
	// логировние
	public function l()
	{
		$arrs = func_get_args();
		foreach ($arrs as $arr) {
			if (!empty($arr)) {
				file_put_contents(__DIR__ . '/../res.log', print_r($arr, true) . "\n", FILE_APPEND);
			}else{
				file_put_contents(__DIR__ . '/../res.log', "EMPTY\n", FILE_APPEND);
			}
		}
	}
}
