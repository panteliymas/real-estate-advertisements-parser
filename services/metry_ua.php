<?php
include_once(ROOT_DIR . 'core/Image/Loader.php');
require ROOT_DIR . '/core/City_Selector.php';

use DiDom\Document;
require('vendor/autoload.php');

class metry_ua extends Core {
  public $website_url = 'https://metry.ua/';
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
  protected $use_proxy = true;
  // php index.php --p=metry_ua

    public function parse(){
      $this->website_id = 77;
      $this->website_url = 'https://metry.ua/search/';
  		echo "\nOk. Let's go!\n";
  		$this->thread_id = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : 0;
  		$this->parser_before_run($this->thread_id);
  		$this->http->setTimeout(65);
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




      // ['kiev', 'Киев', 1],
      // 	 |  	   |    |--- группа запуска
      //   |         |-------- город
      //   |------------------ город в URL
      $city_array = [
          ['kiev',            'Киев', 1],
          //['baryshevka',      'Барышевка', 1],
          //['belaya-cerkov',   'Белая Церковь', 1],
          //['berezan',         'Березань', 1],
          //['boguslav',        'Богуслав', 1],
          ['borispol',        'Борисполь', 1],
          //['borodyanka',      'Бородянка', 1],
          ['brovary',         'Бровары', 1],
          ['bucha',           'Буча', 1],
          ['vasilkov',        'Васильков', 1],
          //['volodarka',       'Володарка', 1],
          ['vyshgorod',       'Вышгород', 1],
          //['zgurovka',        'Згуровка', 1],
          //['ivankov',         'Иванков', 1],
          ['irpen',           'Ирпень', 1],
          //['kagarlyk',        'Кагарлык', 1],
          ['kievo-svyatoshinskiy', 'Киево-Святошинский', 1],
          ['makarov',         'Макаров', 1],
          //['mironovka',       'Мироновка', 1],
          ['obuhov',          'Обухов', 1],
          ['pereyaslav-hmelnickiy', 'Переяслав-Хмельницкий', 1],
          //['polesskoe',       'Полесское', 1],
          //['rakitnoe',        'Ракитное', 1],
          ['rzhishchev',      'Ржищев', 1],
          //['skvira',          'Сквирa', 1],
          ['slavutich',       'Славутич', 1],
          //['stavishche',      'Ставище', 1],
          //['tarashcha',       'Тараща', 1],
          //['tetiev',          'Тетиев', 1],
          ['fastov',          'Фастов', 1],
          //['yagotin',         'Яготин', 1],

          ['lvov',            'Львов',  2],
          ['vinnica',         'Винница', 2],
          ['rovno',           'Ровно', 2],
          ['harkov',          'Харьков', 2],
          ['dnepr',           'Днепр', 2],
          ['krivoy-rog',      'Кривой Рог', 2],
          ['odessa',          'Одесса', 2],
          ['zaporozhe',       'Запорожье', 2],
          ['nikolaev',        'Николаев', 2],
          ['kremenchug',      'Кременчуг', 2],
          ['kropivnickiy',    'Кропивницкий', 2],

          ['druzhkovka',      'Дружковка', 3],
          ['konstantinovka',  'Константиновка', 3],
          ['kramatorsk',      'Краматорск', 3],
          ['mariupol',        'Мариуполь', 3],
          ['slavyansk',       'Славянск', 3],
          //['krasnoarmeysk',   'Красноармейск', 3],

          ['herson',          'Херсон', 4],
          //['hmelnickiy',      'Хмельницкий', 4],
          ['kamenec-podolskiy',      'Каменец-Подольский', 4],
          //['zhitomir',        'Житомир', 4],
          ['yuzhnyy',         'Южный', 4],
          ['zatoka',          'Затока', 4],
          //['sevastopol',      'Севастополь', 4],
          //['yalta',           'Ялта', 4],
          ['chernovcy',       'Черновцы', 4],
          ['cherkassy',       'Черкассы', 4],
          ['poltava',         'Полтава', 4],

          ['chernigov',         'Чернигов', 5]
      ];

      $operations_array = array();

      $document = new Document('https://metry.ua', true);
      $operation_list = $document->find('.custom-item a');
      // с главной страницы получаем список всех операций (продажа дома, аренда офиса и тд)
      foreach($operation_list as $operations) {
        $operations = $operations->href;
        if(strpos($operations, '/search/kiev/') !== false){
          $operations = str_replace('/search/kiev/', '', str_replace('.html', '', $operations));
          array_push($operations_array, $operations);
        }
      }

      // var_dump($operations_array);

      $counter = 0;
      foreach ($city_array as $city) {
        if(array_keys($city, $_SERVER['argv'][2])){ // получаем нужную группу городов из вводимого параметра
          foreach ($operations_array as $operation) { // 2-ой цикл для всех комбинаций горо-тип операции
            if($counter >= 1){ //счётчик для тестов
              break;
            }
            $this->city_url = 'https://metry.ua/search/' . $city[0] . '/' . $operation . '.html'; // комбинируем все города и типы операций и получаем контент с этих страниц
            echo 'url combinations: ' . $this->city_url . "\n";
            $pages = new Document($this->city_url, true); // из болученных страниц вытягиваем блоки объявлений
            $titles = $pages->find('.listing__description .item_link');
            foreach($titles as $title) { // разбираем блоки объявлений по кусочкам
              // определяем тип объекта и тип операции
              if (preg_match('/kvartir/', $title->href)) {$type_object = 'Квартира';}
              if (preg_match('/domov/', $title->href)) {$type_object = 'Дом';}
              if (preg_match('/garage/', $title->href)) $type_object = 'Гараж';
              if (preg_match('/odnokomnatnyih-kvartir/', $title->href)) {$type_object = 'Однокомнатная квартира';}
              if (preg_match('/dvuhkomnatnyih-kvartir/', $title->href)) {$type_object = 'Двухкомнатная квартира';}
              if (preg_match('/trehkomnatnyih-kvartir/', $title->href)) {$type_object = 'Трехкомнатная квартира';}
              if (preg_match('/land/', $title->href)) {$type_object = 'Земельный участок';}
              if (preg_match('/offices/', $title->href)) {$type_object = 'Офис';}

              if (preg_match('/prodaga/', $title->href)) {$type_operation = 'Продажа';}
              if (preg_match('/arenda/', $title->href)) {$type_operation = 'Аренда';}
              if (preg_match('/arenda_posutochno-kvartir/', $title->href)) {$type_operation = 'Аренда квартир посуточно';}

              echo 'website_id: ' . $this->website_id . "\n";
              echo 'title: ' . $title->text() . "\n";
              echo 'url: ' . $title->href . "\n";
              preg_match('/(?<=-)([^-]+?)(?=\.)/', $title->href, $id); // из ссылок на объявления получаем id объявления
              echo 'id: ' . $id[0] . "\n";
              echo 'city: ' . $city[1] . "\n"; // проверить правильно ли определяет город при тестовом заливе в БД
              echo "time when parsed: " .  date("Y-m-d") . " " . date("H:i:s") . "\n\n"; // дата и время когда спаршено
              echo 'type_operation: ' . $type_operation . "\n"; // проверить правильно ли определяет тип объекта и операции при тестовом заливе в БД
              echo 'type_object: ' . $type_object . "\n\n";
              $id_content = $pages->find('#' . $id[0]);
              foreach($id_content as $con) {
                $addresses = $con->find('.listing__address div');
                foreach($addresses as $address) {
                  echo 'address: ' . preg_replace('/\s+/', ' ', $address->text()) . "\n";
                }
                $prices = $con->find('.info-total');
                foreach($prices as $price) {
                  echo 'price: ' . preg_replace('/\s+/', ' ', $price->text()) . "\n";
                }
                $areas = $con->find('.area');
                foreach($areas as $area) {
                  echo 'area: ' . preg_replace('/\s+/', ' ', $area->text()) . "\n";
                }
              }

              $url = 'https://metry.ua/search/' . $title->href;
              $page_content = new Document($url, true);
              $content_on_page = $page_content->find('#descriptionParametersContainer .margin-b10');
              foreach($content_on_page as $content) {
              echo 'description: ' . preg_replace('/\s+/', ' ', $content->text()) . "\n";
                if(preg_match('/Подробно:/', $content->text()) !== false){
                  preg_match('/[\da-f]{32}/', $content->text(), $adv_md5);
                  echo "\n" . 'adv_md5: ' . $adv_md5[0] . "\n";
                  // var_dump($adv_md5);
                  // if(preg_match('/показати телефон/', $content->text()) !== false){
                  //   $adv_md5_part = $content->find('.showlastContact');
                  //   foreach($adv_md5_part as $code_part) {
                  //     echo 'code_part: ' . $code_part->text() . "\n";
                  //     // $content = str_replace('показати телефон', $code_part->text(), $content);
                  //   }
                  // }
                } else {
                  echo 'adv_md5: 0' . "\n";
                }
              }
              // в целом работает, но нужно немного доработать
              $photos = $page_content->find('.to-center img');
              foreach($photos as $photo) {
                if(preg_match('/data-original:/', $photo) !== false) {
                  preg_match('/data-original="(.+).jpg"/', $photo, $photo_url);
                  echo 'photo: ' . $photo_url[1] . '.jpg' . "\n";
                } else {
                  preg_match('/src="(.+).jpg"/', $photo, $photo_url);
                  echo 'photo: ' . $photo_url[1] . '.jpg' . "\n";
                }
              }
              $main_parametrs = $page_content->find('#mainParameters');
              foreach($main_parametrs as $m_params) {
                $parametrs = $m_params->find('.list-item');
                foreach($parametrs as $params) {
                  echo 'main_parametrs: ' . preg_replace('/\s+/', ' ', $params->text()) . "\n";
                }
              }



              echo "\n\n\n";

              // echo "\n";
            }
            $counter++;
          }
        }
      }
  }
}
?>
