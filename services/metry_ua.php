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




      // ['kiev', '����', 1],
      // 	 |  	   |    |--- ������ �������
      //   |         |-------- �����
      //   |------------------ ����� � URL
      $city_array = [
          ['kiev',            '����', 1],
          //['baryshevka',      '���������', 1],
          //['belaya-cerkov',   '����� �������', 1],
          //['berezan',         '��������', 1],
          //['boguslav',        '��������', 1],
          ['borispol',        '���������', 1],
          //['borodyanka',      '���������', 1],
          ['brovary',         '�������', 1],
          ['bucha',           '����', 1],
          ['vasilkov',        '���������', 1],
          //['volodarka',       '���������', 1],
          ['vyshgorod',       '��������', 1],
          //['zgurovka',        '��������', 1],
          //['ivankov',         '�������', 1],
          ['irpen',           '������', 1],
          //['kagarlyk',        '��������', 1],
          ['kievo-svyatoshinskiy', '�����-������������', 1],
          ['makarov',         '�������', 1],
          //['mironovka',       '���������', 1],
          ['obuhov',          '������', 1],
          ['pereyaslav-hmelnickiy', '���������-�����������', 1],
          //['polesskoe',       '���������', 1],
          //['rakitnoe',        '��������', 1],
          ['rzhishchev',      '������', 1],
          //['skvira',          '�����a', 1],
          ['slavutich',       '��������', 1],
          //['stavishche',      '�������', 1],
          //['tarashcha',       '������', 1],
          //['tetiev',          '������', 1],
          ['fastov',          '������', 1],
          //['yagotin',         '������', 1],

          ['lvov',            '�����',  2],
          ['vinnica',         '�������', 2],
          ['rovno',           '�����', 2],
          ['harkov',          '�������', 2],
          ['dnepr',           '�����', 2],
          ['krivoy-rog',      '������ ���', 2],
          ['odessa',          '������', 2],
          ['zaporozhe',       '���������', 2],
          ['nikolaev',        '��������', 2],
          ['kremenchug',      '���������', 2],
          ['kropivnickiy',    '������������', 2],

          ['druzhkovka',      '���������', 3],
          ['konstantinovka',  '��������������', 3],
          ['kramatorsk',      '����������', 3],
          ['mariupol',        '���������', 3],
          ['slavyansk',       '��������', 3],
          //['krasnoarmeysk',   '�������������', 3],

          ['herson',          '������', 4],
          //['hmelnickiy',      '�����������', 4],
          ['kamenec-podolskiy',      '�������-����������', 4],
          //['zhitomir',        '�������', 4],
          ['yuzhnyy',         '�����', 4],
          ['zatoka',          '������', 4],
          //['sevastopol',      '�����������', 4],
          //['yalta',           '����', 4],
          ['chernovcy',       '��������', 4],
          ['cherkassy',       '��������', 4],
          ['poltava',         '�������', 4],

          ['chernigov',         '��������', 5]
      ];

      $operations_array = array();

      $document = new Document('https://metry.ua', true);
      $operation_list = $document->find('.custom-item a');
      // � ������� �������� �������� ������ ���� �������� (������� ����, ������ ����� � ��)
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
        if(array_keys($city, $_SERVER['argv'][2])){ // �������� ������ ������ ������� �� ��������� ���������
          foreach ($operations_array as $operation) { // 2-�� ���� ��� ���� ���������� ����-��� ��������
            if($counter >= 1){ //������� ��� ������
              break;
            }
            $this->city_url = 'https://metry.ua/search/' . $city[0] . '/' . $operation . '.html'; // ����������� ��� ������ � ���� �������� � �������� ������� � ���� �������
            echo 'url combinations: ' . $this->city_url . "\n";
            $pages = new Document($this->city_url, true); // �� ���������� ������� ���������� ����� ����������
            $titles = $pages->find('.listing__description .item_link');
            foreach($titles as $title) { // ��������� ����� ���������� �� ��������
              // ���������� ��� ������� � ��� ��������
              if (preg_match('/kvartir/', $title->href)) {$type_object = '��������';}
              if (preg_match('/domov/', $title->href)) {$type_object = '���';}
              if (preg_match('/garage/', $title->href)) $type_object = '�����';
              if (preg_match('/odnokomnatnyih-kvartir/', $title->href)) {$type_object = '������������� ��������';}
              if (preg_match('/dvuhkomnatnyih-kvartir/', $title->href)) {$type_object = '������������� ��������';}
              if (preg_match('/trehkomnatnyih-kvartir/', $title->href)) {$type_object = '������������� ��������';}
              if (preg_match('/land/', $title->href)) {$type_object = '��������� �������';}
              if (preg_match('/offices/', $title->href)) {$type_object = '����';}

              if (preg_match('/prodaga/', $title->href)) {$type_operation = '�������';}
              if (preg_match('/arenda/', $title->href)) {$type_operation = '������';}
              if (preg_match('/arenda_posutochno-kvartir/', $title->href)) {$type_operation = '������ ������� ���������';}

              echo 'website_id: ' . $this->website_id . "\n";
              echo 'title: ' . $title->text() . "\n";
              echo 'url: ' . $title->href . "\n";
              preg_match('/(?<=-)([^-]+?)(?=\.)/', $title->href, $id); // �� ������ �� ���������� �������� id ����������
              echo 'id: ' . $id[0] . "\n";
              echo 'city: ' . $city[1] . "\n"; // ��������� ��������� �� ���������� ����� ��� �������� ������ � ��
              echo "time when parsed: " .  date("Y-m-d") . " " . date("H:i:s") . "\n\n"; // ���� � ����� ����� ��������
              echo 'type_operation: ' . $type_operation . "\n"; // ��������� ��������� �� ���������� ��� ������� � �������� ��� �������� ������ � ��
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
                if(preg_match('/��������:/', $content->text()) !== false){
                  preg_match('/[\da-f]{32}/', $content->text(), $adv_md5);
                  echo "\n" . 'adv_md5: ' . $adv_md5[0] . "\n";
                  // var_dump($adv_md5);
                  // if(preg_match('/�������� �������/', $content->text()) !== false){
                  //   $adv_md5_part = $content->find('.showlastContact');
                  //   foreach($adv_md5_part as $code_part) {
                  //     echo 'code_part: ' . $code_part->text() . "\n";
                  //     // $content = str_replace('�������� �������', $code_part->text(), $content);
                  //   }
                  // }
                } else {
                  echo 'adv_md5: 0' . "\n";
                }
              }
              // � ����� ��������, �� ����� ������� ����������
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
