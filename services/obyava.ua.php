<?php
include_once(ROOT_DIR . 'core/Image/Loader.php');
require ROOT_DIR . '/core/Region_Selector.php';

class obyava_ua extends Core
{
    //  php index.php --p=obyava.ua
    public $website_url = 'https://obyava.ua';
    public $def_city = '';
    public $type_operation = array();
    protected $use_proxy = true; // для тестирования можно выставить в false

    public function parse() {
        $this->website_id = 76;
        echo "\nOk obyava. Let's go!\n";
        $this->thread_id = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : 0; // thread_id соответствует индексу групп областей (городов)
        $this->parser_before_run($this->thread_id);
        $this->http->setReferrer($this->website_url);
        $this->http->is_proxy = $this->use_proxy;
        $this->http->is_secure = $this->use_proxy; //exper
        $this->http->useCurl(true);
        $this->loadImageParser();

        // ['kiev', 'Киев', 1],
        // 	 |  	   |    |--- группа запуска
        //   |         |-------- город
        //   |------------------ город URL
        $city_array=[
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

            ['chernigov',         'Чернигов', 5],

        ];

        $type_operation_array = [
            ['prodazha-kvartir',            818],
            //['prodazha-komnat',             820],
            ['prodazha-domov',              822],
            ['prodazha-dach',               50],
            ['prodazha-zemli',              824],
            ['prodazha-garazhey-stoyanok',  826],
            ['prodazha-pomescheniy',        828],

            ['arenda-kvartir',              819],
            //['arenda-komnat',               821],
            ['arenda-domov',                823],
            ['arenda-zemli',                825],
            ['arenda-garazhey-stoyanok',    827],
            ['arenda-pomescheniy',          829],
        ];

        // for test
        //$url = 'https://obyava.ua/ru/4-sezona-3-sekciya-kvartira-100kv-m-16855684.html';
        //$this->def_city = 'Одесса';
        //$this->type_operation = ['prodazha-kvartir', 818];
        //$this->parse_element($url);
        //exit;

        echo "parse_page. start\n";
        foreach($city_array as $city) {
            foreach($type_operation_array as $type_operation) {
                if( $this->thread_id>0) {
                    if($city[2] == $this->thread_id) { // если соответствует группе, то выполняем
                        $this->parse_REALTY_city($type_operation, $city);
                    }
                } else {
                    $this->parse_REALTY_city($type_operation, $city);
                }
            }
        }
        echo "Done\r\n";
    }

    private function parse_REALTY_city($type_operation, $city) {
        $this->def_city = $city[1];
        $this->type_operation = $type_operation;
        $this->http->execute($this->website_url . '/ru/nedvizhimost/' . $type_operation[0] . '/' . $city[0]);
        echo $this->website_url . ' length: ' . strlen($this->http->result) . "\r\n";
        $this->parse_ads();
    }

    private function parse_ads(){
        $dom = new DOMDocument();
        @$dom->loadHTML($this->http->result);
        $xpath = new DOMXPath($dom);
        foreach($xpath->evaluate('//h2[@class="classified-title "]/a/@href') as $childNode) {
            $this->parse_element($childNode->textContent);
        }
    }

    private function parse_element($url) {
        echo $url."\r\n";

        $type_operation = in_array($this->type_operation[1], array(818, 820, 822, 50, 824, 826, 828)) ? 'Продают' : 'Сдают';
        $attributes['approved'] = 1;

        $this->http->execute($url);
        $dom = new DOMDocument();
        $this->http->result = mb_convert_encoding($this->http->result, 'HTML-ENTITIES', 'UTF-8');
        // удаление комментариев
        $this->http->result = preg_replace('/<!--(.*?)-->/', '', $this->http->result);
        @$dom->loadHTML($this->http->result);
        $xpath = new DOMXPath($dom);

        // номер объявления
        $id_ad = 0;
        $id_ad_node = $xpath->evaluate("//div[@class='classified-id']");
        if ($id_ad_node->length > 0) {
            $id_ad = preg_replace("/[^0-9]/", '', $id_ad_node->item(0)->nodeValue);
            echo $id_ad."\r\n";
        }

        // дата объявления
        $attributes['last_date_add'] = '';
        $time_ad_node = $xpath->evaluate("//div[@class=\"time\"]");
        if ($time_ad_node->length > 0) {
            $attributes['last_date_add'] = $this->get_date(trim($time_ad_node->item(0)->nodeValue));
        }

        // поиск существующей записи
        // если объвление уже найдено в базе, то пропускаем его
        if($insert_id = $this->md5_exists(md5($url))){
            // если объвление уже найдено в базе, то тогда определяем нужно ли обновлять
            if( $db_id_new = $this->advUpId($insert_id, $attributes['last_date_add']) ) {
                $attributes['insert_id'] = $db_id_new;
            } else {
                return;
            }
        }

        // телефон
        $phones = array();
        $phone_ad_node = $xpath->evaluate("//span/@data-rebmun");
        for($i=0; $i<$phone_ad_node->length; $i++){
            $tel = base64_decode($phone_ad_node->item($i)->nodeValue);
            $phone = preg_replace("/[^0-9]/", '', $tel);
            if( !in_array($phone, $phones) ) $phones[] = $phone;
        }

        // если нет телефона, объявление в базу не добавляем
        if(count($phones) == 0) return;

        // имя автора
        $author_nodes = $xpath->evaluate("//span[@class=\"name\"]");
        if ($author_nodes->length > 0) {
            $attributes['author'] = trim($this->utf_to_1251(($author_nodes->item(0)->nodeValue)));
        }

        // стоимость
        $price = 0;
        $priceCurrency = 1;
        $param_ad_node = $xpath->evaluate("//div[@id='all-classified-parameters']/section/div/div/span");
        $price_str = $dom->saveHTML($param_ad_node->item(0));
        if (preg_match('#>(.*?)<#is', $price_str, $matches)) {
            $price = preg_replace("/[^0-9]/", '', trim($matches[1]));
            $param_ad_node = $xpath->evaluate("//div[@id='all-classified-parameters']/section/div/div/span/sup");
            // валюта
            $priceCurrency = $this->find_currency($this->utf_to_1251($param_ad_node->item(0)->nodeValue));
        }
        
        // параметры
        $name_type = $this->get_params($dom, $xpath, $attributes);

        // оглавление объявления
        $title = '';
        $title_node = $xpath->evaluate("//h1");
        if ($title_node->length > 0) {
            $title = trim(($title_node->item(0)->nodeValue));
        }
        // тест объявления
        $param_ad_node = $xpath->evaluate("//div[@id='all-classified-parameters']/section[@class='group full']/div/div[@class='text']");
        $description = $title . ' ' . strip_tags(preg_replace("/\s{2,}/", " ", $dom->saveHTML($param_ad_node->item(0)))) . ' ' . $attributes['address_region'];
        unset($attributes['address_region']);

        if(stripos($description, 'без комисси') !== false) $attributes['commission'] = 0;

        // сохранение изображения
        $images_list = array();
        $param_ad_node = $xpath->evaluate("//div[@data-image]");
        foreach ($param_ad_node as $node) {
            if (!in_array($node->GetAttribute("data-image"), $images_list)) {
                $images_list[] = $node->GetAttribute("data-image");
            }
        }

        $images = array();
        foreach ($images_list as $img_el) {
            echo "fetchImage: $img_el\n";
            $this->image_parser->http->is_proxy = false;
            $image = $this->image_parser->fetchImage($img_el, '', 's' . $this->website_id);
            if ($image) {
                $img = Image::factory(ROOT_DIR . 'img/' . $image);
                $img->save(ROOT_DIR . 'img/' . $image, 80);
                unset($img);

                $image = $this->validateImage($image, ROOT_DIR . 'img/');
                if ($image) {
                    $this->image_parser->crop(ROOT_DIR . 'img/' . $image, -63);
                    $images[] = $image;
                } else {
                    echo "$img_el Bad image!\n";
                }
                if (count($images) > 19) {
                    break;
                }
            }
        }
        $attributes['images'] = $images;

        if (isset($attributes['street'])) {
            $this->get_address_street($this->utf_to_1251($attributes['street']), $this->utf_to_1251($this->def_city), $attributes);
            unset($attributes['street']);
        }

        if (isset($attributes['downtown'])) {
            unset($attributes['downtown']);
            $this->get_address_street($this->utf_to_1251($description), $this->utf_to_1251($this->def_city), $attributes);
        }

        $insert_id = $this->save_post(
            $url,
            $id_ad,
            $phones,
            $this->utf_to_1251($name_type),
            $this->utf_to_1251($type_operation),
            $this->utf_to_1251($description),
            $this->utf_to_1251($this->def_city),
            md5($url),
            $this->utf_to_1251($price),
            $priceCurrency,
            $attributes
        );

        if ($insert_id) {
            // statistics for parser_counter
            $this->save_statistics($insert_id,  count($images), $this->utf_to_1251($this->def_city), $this->utf_to_1251($type_operation), $url);
        }
        return  true;
    }

    private function get_date($date_str) {
        if (stripos($date_str, 'Сегодня') !== false)    return date("Y-m-d");
        if (stripos($date_str, 'Сьогодні') !== false)    return date("Y-m-d");
        if (stripos($date_str, 'Вчера') !== false)    return date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
        if (stripos($date_str, 'Вчора') !== false)    return date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
        list($day, $hour) = explode(',', $date_str);
        $day_el = explode(' ', $day);

        switch (trim($day_el[1])) {
            case 'января':
                $m =  1;
                break;
            case 'февраля':
                $m =  2;
                break;
            case 'марта':
                $m =  3;
                break;
            case 'апреля':
                $m =  4;
                break;
            case 'мая':
                $m =  5;
                break;
            case 'июня':
                $m =  6;
                break;
            case 'июля':
                $m =  7;
                break;
            case 'августа':
                $m =  8;
                break;
            case 'сентября':
                $m =  9;
                break;
            case 'октября':
                $m = 10;
                break;
            case 'ноября':
                $m = 11;
                break;
            case 'декабря':
                $m = 12;
                break;
        }
        $d = intval($day_el[0])>0 ?  intval($day_el[0]) : date('d');
        $y = intval($day_el[2])>0 ?  intval($day_el[2]) : date('Y');
        return date("Y-m-d", mktime(0, 0, 0, $m, $d, $y));
    }

    private function get_params($dom, $xpath, &$attributes){
        $name_type = "";
        if( ($this->type_operation[1] == 818) OR ($this->type_operation[1] == 819) ) $name_type = 'Квартира'; // продажа  - аренда квартир
        if( ($this->type_operation[1] == 822) OR ($this->type_operation[1] == 823) ) $name_type = 'Дом' ; // prodazha-domov
        if(  $this->type_operation[1] == 50)  $name_type ='Дача' ; // prodazha-dach
        if( ($this->type_operation[1] == 824) OR  ($this->type_operation[1] == 825) ) $name_type = 'Земельный участок' ; // prodazha-zemli
        if( ($this->type_operation[1] == 826) OR  ($this->type_operation[1] == 827) ) $name_type = 'Гараж' ; // arenda-garazhey-stoyanok
        if( ($this->type_operation[1] == 828) OR  ($this->type_operation[1] == 829) ) $name_type = 'Коммерческая' ; // prodazha-pomescheniy
        for ($i = 1; $i < 10; $i++) {
            $param_ad_node = $xpath->evaluate("//div[@id='all-classified-parameters']/section[" . $i . "]/div/div[@class='parameters ']/table/tr");
            if($param_ad_node->length == 0 ) return $name_type;
            foreach ($param_ad_node as $tag) {
                $innerHTML = strip_tags(preg_replace("/\s{2,}/", " ", $dom->saveHTML($tag)));
                $innerHTML = strip_tags(preg_replace("/\n/", "", $innerHTML));
		        if (stripos($innerHTML, ':') === false) continue;
                list($name_el, $val_el) = explode(':', $innerHTML);
                $name_el=trim($name_el);
                $val_el=trim($val_el);

                if (stripos($name_el, 'Количество комнат') !== false) $attributes['rooms_count'] = preg_replace("/[^0-9]/", '', $val_el);
                if (stripos($name_el . ':', 'Этаж:') !== false) $attributes['floor'] = preg_replace("/[^0-9]/", '', $val_el);
                if (stripos($name_el, 'Площадь общая') !== false) {
                    $attributes['area_all'] = preg_replace("/м2/", '', $val_el);
                    $attributes['area_all'] = preg_replace("/[^0-9]/", '', $attributes['area_all']);
                }
                if (stripos($name_el, 'Площадь жилая') !== false) {
                    $attributes['area_living'] = preg_replace("/м2/", '', $val_el);
                    $attributes['area_living'] = preg_replace("/[^0-9]/", '', $attributes['area_living']);
                }
                if (stripos($name_el, 'Кухня') !== false) {
                    $attributes['area_kitchen'] = preg_replace("/м2/", '', $val_el);
                    $attributes['area_kitchen'] = preg_replace("/[^0-9]/", '', $attributes['area_kitchen']);
                }
                if( (stripos($name_el, 'Участок') !== false) OR (stripos($name_el, 'Площадь участка') !== false) ){
                    $attributes['ground'] = preg_replace("/[^0-9]/", '', $attributes['ground']);
                }

                if (stripos($name_el, 'Этажность') !== false) $attributes['floor_all'] = preg_replace("/[^0-9]/", '', $val_el);

                if (stripos($name_el, 'Район') !== false) $attributes['address_region'] = '( ' . trim($val_el) . ' )';

                if (stripos($name_el, 'Улица') !== false) $attributes['street'] = trim($val_el);
            }
        }
    }
}