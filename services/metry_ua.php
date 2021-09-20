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


    public function parse(){
      $this->website_id = 77;
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

      $operations_array = array();
      $cities_array = array();

      $document = new Document('https://metry.ua', true);
      $operation_list = $document->find('.custom-item a');
      foreach($operation_list as $operations) {
        $operations = $operations->href;
        if(strpos($operations, '/search/kiev/') !== false){
          $operations = str_replace('/search/kiev/', '', str_replace('.html', '', $operations));
          array_push($operations_array, $operations);
        } else {
          $operations = str_replace('/', '', str_replace('.html', '', $operations));
          array_push($cities_array, $operations);
        }
      }
      // php index.php --p=metry_ua

      // var_dump($operations_array);
      // var_dump($cities_array);
      $counter = 0;
      foreach ($cities_array as $city){
        foreach ($operations_array as $operation){
          if($counter >= 1){
            break;
          }
          $this->city_url = 'https://metry.ua/search/' . $city . '/' . $operation . '.html';
          echo 'url combinations: ' . $this->city_url . "\n";
          $pages = new Document($this->city_url, true);
          $titles = $pages->find('.listing__description .item_link');
          foreach($titles as $title) {
            echo 'website_id: ' . $this->website_id . "\n";
            echo 'title: ' . $title->text() . "\n";
            echo 'url: ' . $title->href . "\n";
            preg_match('/(?<=-)([^-]+?)(?=\.)/', $title->href, $id);
            echo 'id: ' . $id[0] . "\n";
            echo "time when parsed: " .  date("Y-m-d") . " " . date("H:i:s") . "\n";

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
            echo 'description: ' . preg_replace('/\s+/', ' ', $content) . "\n";
              if(preg_match('/Подробно:/', $content->text()) !== false){
                preg_match('/[\da-f]{32}/', $content->text(), $adv_md5);
                echo 'adv_md5: ' . $adv_md5[0] . "\n";
                // var_dump($adv_md5);
                if(preg_match('/показати телефон/', $content->text()) !== false){
                  $adv_md5_part = $content->find('.showlastContact');
                  foreach($adv_md5_part as $code_part) {
                    echo 'code_part: ' . $code_part->text() . "\n";
                    // $content = str_replace('показати телефон', $code_part->text(), $content);
                  }
                }
              } else {
                echo 'adv_md5: 0' . "\n";
              }

            }

            echo "\n";

            // echo "\n";
          }
          $counter++;
        }
      }



      // $document = new Document('https://metry.ua/search/kiev/prodaga-kvartir.html', true);
      // $title = $document->find('.listing__description a');
      // foreach($title as $post) {
      //     echo $post->text(), "\n";
          // $document = new Document($post->html, true);
          // $title = $document->find('.item_link');
          // $rooms = $document->find('.listing-data a');
          // $area = $document->find('span.area');
          // $floor = $document->find('span.floor');
          // $address = $document->find('.listing__address');
          // $price = $document->find('.info-total');
          // $price_for_m = $document->find('.info-price');
          // $desc = $document->find('.listing__detailes');
          // echo 'title - ' . $title . "\n";
          // echo 'rooms - ' . $rooms . "\n";
          // echo 'area - ' . $area  . "\n";
          // echo 'floor - ' . $floor . "\n";
          // echo 'address - ' . $address . "\n";
          // echo 'price - ' . $price . "\n";
          // echo 'price_for_m - ' . $price_for_m . "\n";
          // echo 'desc - ' . $desc  . "\n";
      // }
  }
}
?>
