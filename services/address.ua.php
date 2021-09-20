<?php
//require ROOT_DIR . '/core/PhoneOcr.php';
include_once(ROOT_DIR . 'core/Image/Loader.php');
//require ROOT_DIR . '/core/Region_Selector.php';
setlocale(LC_ALL, 'ru_RU.cp1251');

class address_ua extends Core
{
    //  php index.php --p=address.ua
    public $website_url = 'https://address.ua/';
    public $quick_mode = false;
    public $category_cur;
    public $region_cur;
    public $operation_cur;


    public $category_url = array(
        'kvartir',
        'domov',
        'zemli',
        'komnat',
        'ofisov',
        'torgovyh-ploschadey',
        'garazey',
        'promyshlennyh-ploschadey',
        'rekreacionnyh-obektov',
        'skladov'
    );

    public $region_url = array(
        ['kiev', '', 'Киев', 0],
        ['kharkov', '', 'Харьков', 0],
        ['sevastopol', '', 'Севастополь', 0],
        //['yalta','?searchstring=%u041a%u0440%u044b%u043c%2c+%u042f%u043b%u0442%u0430','Ялта',0],
        ['dnepr', '', 'Днепр', 0],
        ['krivojj-rog', '', 'Кривой Рог', 0],
        ['odessa', '', 'Одесса', 0],
        ['vinnica', '', 'Винница', 0],
        ['kherson', '', 'Херсон', 0],
        ['mariupol', '', 'Мариуполь', 0],
        ['poltava', '', 'Полтава', 0],

        ['dn', 'g-kramatorsk-203', 'Краматорск', 0],
        ['dn', 'g-slavyansk-207', 'Славянск', 0],
        ['dn', 'g-artemovsk-196', 'Артемовск', 0],
        ['dn', 'g-druzhkovka-201', 'Дружковка', 0],
        ['dn', 'g-konstantinovka-202', 'Константиновка', 0],
        ['dn', 'g-pokrovsk-204', 'Красноармейск', 0],
        ['dn', 'g-dobropolskijj', 'Доброполье', 0],

        ['lg', 'g-severodoneck-244', 'Северодонецк', 0],
        ['lg', 'g-lisichansk-239', 'Лисичанск', 0],

        ['kh', 'g-izyumskijj', 'Изюм', 0],

        ['ko', 'g-vyshgorod-361', 'Вышгород', 0],
        ['ko', 'g-starye-petrovcy', 'Старые Петровцы', 0],
        ['ko', 'g-novye-petrovcy/', 'Новые Петровцы', 0],
        ['ko', 'g-vyshgorodskijj/?searchstring=%u041a%u0438%u0435%u0432%u0441%u043a%u0430%u044f+%u043e%u0431%u043b%u0430%u0441%u0442%u044c%2c+%u0412%u044b%u0448%u0433%u043e%u0440%u043e%u0434%u0441%u043a%u0438%u0439+%u0440%u0430%u0439%u043e%u043d%2c+%u0441%u0435%u043b%u043e+%u0425%u043e%u0442%u044f%u043d%u043e%u0432%u043a%u0430', 'Хотяновка', 0],
        ['ko', 'g-sofievskaya-borshhagovka', 'Софиевская Борщаговка', 0],
        ['ko', 'g-petropavlovskaya-borshhagovka', 'Петропавловская Борщаговка', 0],
        ['ko', 'g-vishnevoe', 'Вишневое', 0],
        ['ko', 'g-chajjki', 'Чайки', 0],
        ['ko', 'g-vasilkov-298', 'Васильков', 0],

        ['mk', 'g-nikolaev', 'Николаев', 0],
        ['rovno', '', 'Ровно', 0],

        ['zp', '', 'Запорожье', 0],

        ['lvov', '', 'Львов', 0],
        ['kremenchug', '', 'Кременчуг', 0],
        ['od', 'g-yuzhnyjj', 'Южный', 0],

        ['chernovcy', '', 'Черновцы', 0],

        ['cherkassy', '', 'Черкассы', 0],

        ['chernigov', '', 'Чернигов', 0],

        ['kropivnickijj', '', 'Кропивницкий', 0],
        //['km', 'g-kamenec-podolskijj', 'Каменец-Подольский', 0],

    );

    public $operation_url = array(
        'prodazha',
        'arenda',
        'posutochnaya-arenda',
        'obmen'
    );

    public function parse()
    {
        if (!empty($_SERVER['argv'][2]) && $_SERVER['argv'][2] > 0) {
            $this->quick_mode = true;
        }

        $this->website_id = 49;
        $this->http->is_proxy = true;
        //$this->http->is_secure = true; //exper
        $this->thread_id = mt_rand(2, 12);
        $this->parser_before_run($this->thread_id);
        $this->http->request_headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4,uk;q=0.2',
            'Accept-Encoding: gzip,deflate',
            'Connection' => 'keep-alive'
        );

        $this->http->setReferrer($this->website_url);
        $this->http->setUseragent('Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.114 Safari/537.36');

        $this->http->useCurl(true);
        $this->loadImageParser();
        $this->http->cookiePath = ROOT_DIR . 'cookies/website-' . $this->website_id . '-' . $this->thread_id . '.txt';
        @unlink($this->http->cookiePath);

        if (!count($this->category_url)) return false;
        if (!count($this->region_url)) return false;
        if (!count($this->operation_url)) return false;


        // for test
        // $this->category_cur = 'domov';
        // $this->region_cur = 'odessa';
        // $this->def_city = 'Одесса';
        // $this->operation_cur = 'prodazha';
        // $this->parse_adv('//address.ua/odessa/prodazha-kvartir-malinovskijj-s-yadova-24641748/');
        // exit;

        foreach ($this->category_url as $cat) {
            $this->category_cur = $cat;
            foreach ($this->region_url as $reg) {
                $this->region_cur = $reg;
                foreach ($this->operation_url as $op) {
                    //if($reg[2] != 'Чернигов') continue;
                    $this->operation_cur = $op;
                    $sort = strpos($reg[1], '?') ? '&sortfield=date' : '?sortfield=date';
                    echo $this->website_url . $reg[0] . '/' . $op . '-' . $cat . '/' . $reg[1] . $sort."\n";

                    $this->parse_url($this->website_url . $reg[0] . '/' . $op . '-' . $cat . '/' . $reg[1] . $sort);

                }
            }
        }
        //$url=$this->website_url . $this->region_cur[0] . '/' . $this->operation_cur. '-' . $this->category_cur . '/' . $this->region_cur[1] . $sort;
        //echo "\nURL: $url\n";
        //$sort = strpos($this->region_cur[1],'?') ? '&sortfield=date' : '?sortfield=date';
        //$this->parse_url($url);

    }

    private function get_pages_count()
    {
        if (empty($this->http->result))
            return 0;
        $dom = new DOMDocument();
        @$dom->loadHTML($this->http->result);
        $xpath = new DOMXPath($dom);
        $last_page_node = $xpath->evaluate("(//div[@class='paging']/a)[last()]");

        if ($last_page_node->length > 0) {
          echo "\n";
          print_r(intval(preg_replace('/\D/', '', $last_page_node->item(0)->nodeValue)));
echo "\n";
          //exit;
            return intval(preg_replace('/\D/', '', $last_page_node->item(0)->nodeValue));

        } else
            return 1;
    }

    private function delete_not_parsed()
    {
        $date_add = date("Y-m-d H:i:s", time() - 360);
        $this->delete_adv_list("`website_id` = '49' AND `url` IS NULL AND date_add < '{$date_add}'");
        $this->delete_adv_list("`website_id` = '49' AND `phone` = '' AND date_add < '{$date_add}'");
    }

    private function parse_url($url)
    {
        $this->base_url = $url;
        $this->def_city = $this->region_cur[2];
        $this->http->execute($this->base_url);

        if (!$this->http->error) {
            $pages = $this->get_pages_count();
            echo "\n\n=========== 1 PAGES: $pages ===================\n";
            $this->page = 0;
            $have_new = $this->parse_page();
            if ($have_new === false) return;
            for ($i = 1; $i <= $pages; $i++) {
                echo "\n\n =========== 2 PAGE: $i ===================\n";
                $this->http->execute($this->base_url . '&pg=' . $i);
                $have_new = $this->parse_page();
                if ($have_new === false) return;
            }
        }
    }

    /**
     * Парсит страницу кратких объявлений
     */
    private function parse_page()
    {
        $result = false;
        $dom = new DOMDocument();
        //$this->http->result = mb_convert_encoding($this->http->result, 'HTML-ENTITIES', 'cp1251');
        @$dom->loadHTML($this->http->result);
        $xpath = new DOMXPath($dom);

        //$adv_nodes = $xpath->evaluate("//div[@class='item_center_right']/h2/a");
        $adv_nodes = $xpath->evaluate("//div[@class='item__head']/a");
        if ($adv_nodes->length == 0) {
            echo "adv_nodes->length == 0\n";
            return false;
        }
        foreach ($adv_nodes as $adv) {
            $adv_url = $adv->getAttribute('href');
            if (empty($adv_url) || (!$this->parse_adv($adv_url) && $this->quick_mode)) {
                return false;
            }
        }
        return true;
    }

    protected function parse_adv($url)
    {
        sleep(5);
        $url = preg_replace('/#.*/', '', $url);
        if (!preg_match('/-(\d+)\//', $url, $m) || empty($m[1])) {
            echo "\nWrong url format: $url\n";

            return false;
        }
        $id = $m[1];
        echo "\nAdv ID: $id\n";
        $url = 'https:' . $url;
        echo $url."\n";
        //exit;
        $this->http->execute($url);
        if ($this->http->error) {
            echo "\nhttp->execute->error: " . $this->http->error . "\n";
            return false;
        }

        $attributes = array();
        $attributes_text = '';

        $dom = new DOMDocument();
        //$this->http->result = mb_convert_encoding($this->http->result, 'HTML-ENTITIES', 'cp1251');
        @$dom->loadHTML($this->http->result);
        $xpath = new DOMXPath($dom);

        // get title
        $titleMain = '';
        $title_nodes_main = $xpath->evaluate("//h1[@class='h1']");
        if ($title_nodes_main->length > 0) {
            $str = $title_nodes_main->item(0)->nodeValue ;
            $str = htmlentities($str);
            $str = str_replace("&nbsp;",'',$str);
            $titleMain = $this->utf_to_1251(trim($str), true). ' ';
        }

        $title_nodes = $xpath->evaluate("//div[contains(@class,'card-description')]/div[@class='card-block__title']");
        if ($title_nodes->length == 0) {
            echo "title_nodes->length == 0\n";
            $this->log_error(__LINE__);
            return true;
        }
        $str = $title_nodes->item(0)->nodeValue;
        $title = $titleMain . $this->utf_to_1251(trim($str));
        if (strlen($title) > 0) $attributes['h1'] = $title;

        // get attributes that is parameters of object
        $attribute_nodes = $xpath->evaluate("//div[@class='prop-items']/div[@class='prop']");
        if ($attribute_nodes->length > 0) {
            foreach ($attribute_nodes as $attr) {
                $attr_title = '';
                $attr_title_nodes = $xpath->evaluate("./span[@class='title']", $attr);
                if ($attr_title_nodes->length > 0)
                    $attr_title = trim($this->utf_to_1251($attr_title_nodes->item(0)->nodeValue, ': \t\n\r\0\x0B'));

                $attr_value = '';
                $attr_value_nodes = $xpath->evaluate("./span[@class='value']", $attr);
                if ($attr_value_nodes->length > 0) {
                    $attr_value = trim($this->utf_to_1251(strip_tags($attr_value_nodes->item(0)->nodeValue)));
                    $attr_value = preg_replace('/\s+/', ' ', $attr_value);
                }

                switch ($attr_title) {
                    case 'Общая':
                        $attributes['area_all'] = intval($attr_value);
                        break;
                    case 'Полезная':
                        $attributes['area_living'] = intval($attr_value);
                        break;
                    case 'Кухни':
                        $attributes['area_kitchen'] = intval($attr_value);
                        break;
                    case 'Земли':
                        $value = floatval($attr_value);
                        if ($value > 0) {
                            if (strpos($attr_value, 'сот') !== false) {
                                $attributes['ground'] = $value / 100;
                            }
                        } else {
                            $attributes['ground'] = $value;
                        }
                        break;
                }
                $attributes_text .= $attr_title . ' ' . $attr_value . "\n";
            }
        }

        $attribute_nodes = $xpath->evaluate("//div[@class='card-character__all']/div[@class='prop']");
        if ($attribute_nodes->length > 0) {
            foreach ($attribute_nodes as $attr) {
                $attr_title = '';
                $attr_title_nodes = $xpath->evaluate("./span[@class='title']", $attr);
                if ($attr_title_nodes->length > 0)
                    $attr_title = trim($this->utf_to_1251($attr_title_nodes->item(0)->nodeValue, ': \t\n\r\0\x0B'));

                $attr_value = '';
                $attr_value_nodes = $xpath->evaluate("./span[@class='value']", $attr);
                if ($attr_value_nodes->length > 0) {
                    $attr_value = trim($this->utf_to_1251(strip_tags($attr_value_nodes->item(0)->nodeValue)));
                    $attr_value = preg_replace('/\s+/', ' ', $attr_value);
                } else {
                    $attr_value_nodes = $xpath->evaluate("./div[@class='value']", $attr);
                    if ($attr_value_nodes->length > 0) {
                        $attr_value = trim($this->utf_to_1251(strip_tags($attr_value_nodes->item(0)->nodeValue)));
                        $attr_value = preg_replace('/\s+/', ' ', $attr_value);
                    }
                }

                switch ($attr_title) {
                    case 'Комнат':
                        $attributes['rooms_count'] = intval($attr_value);
                        break;
                    case 'Этаж':
                        $floors = preg_replace('/[^0-9 ]/', '', $attr_value);
                        $floors = preg_replace('/\s+/', ' ', $floors);
                        $floors = explode(' ', $floors);
                        $attributes['floor'] = isset($floors[0]) ? intval($floors[0]) : 0;
                        $attributes['floor_all'] = isset($floors[1]) ? intval($floors[1]) : 0;
                        break;
                    case 'Сегмент рынка':
                        if (strpos($attr_title, 'Первичный') != false) {
                            //$attributes['is_realtor'] = 1;
                        }
                        break;

                }
                if (strpos($attr_title, 'посредник') != false) {
                    $attributes['is_realtor'] = 1;
                }
                $attributes_text .= $attr_title . ' ' . $attr_value . "\n";
            }
        }

        // get price
        $price_nodes = $xpath->evaluate("//div[@data-id='spnListingPrice']/span[@data-price-type='UAH']");
        if ($price_nodes->length > 0) {
            $price = $this->getNumbers($price_nodes->item(0)->nodeValue);
            $currency = 1;
        }

        // get address coordinates
        $pos = stripos($this->http->result, "Latitude:");
        if( $pos !== false ) {
            $tmp = substr($this->http->result, $pos);
            $tmp = substr($tmp, 0, stripos($tmp, ', '));
            $tmp = preg_replace('/,/', '.', $tmp);
            $tmp = preg_replace('/[^0-9.]/', '', $tmp);
            $attributes['latitude'] = $tmp;
        }

        $pos = stripos($this->http->result, "Longitude:");
        if( $pos !== false ) {
            $tmp = substr($this->http->result, $pos);
            $tmp = substr($tmp, 0, stripos($tmp, ', '));
            $tmp = preg_replace('/,/', '.', $tmp);
            $tmp = preg_replace('/[^0-9.]/', '', $tmp);
            $attributes['longitude'] = $tmp;
        }

        if(!is_numeric($attributes['latitude']) || !is_numeric($attributes['longitude'])) {
            echo "set coordinate in the downtown\n";
            $coor = $this->get_coordinate_city($this->utf_to_1251($this->def_city));
            echo 'lat:'.$coor['lat'].' lon:'.$coor['lon']."\n";
            $attributes['latitude'] = $coor['lat']; // широта
            $attributes['longitude'] = $coor['lon']; // долгота
        }

        // get description
        $adv_text = '';
        $descr_nodes = $xpath->evaluate("//div[@data-id='divDescriptionText']");
        if ($descr_nodes->length > 0)
            $adv_text = $this->utf_to_1251(trim(strip_tags($descr_nodes->item(0)->nodeValue)));
        $adv_text .= ' ' . $attributes_text;

        if (stripos($adv_text, 'без комиссии') !== false) $attributes['commission'] = 0;


        // get owner
        $owner_nodes = $xpath->evaluate("//div[@class='owner-details']/div/span");
        if ($price_nodes->length > 0) {
            $owner = $this->getNumbers($price_nodes->item(0)->nodeValue);
            if(stripos($owner, 'Посредник') !==false) $attributes['is_realtor'] = 1;
        }


        // get phones
        $phone_nodes = $xpath->evaluate("//div[@id='phone_a1']");
        if ($phone_nodes->length > 0) {
            $phone_text = $phone_nodes->item(0)->nodeValue;
            $phone_items = $this->find_phones($phone_text);
            foreach ($phone_items as $item) {
                if (strlen($item) > 6) {
                    $phones[] = $item;
                }
            }
        }

        // get email
        $email_nodes = $xpath->evaluate("//div[@id='mail_a1']");
        if ($email_nodes->length > 0) {
            $attributes['email'] = $email_nodes->item(0)->nodeValue;
        }

        // get author
        $author_nodes = $xpath->evaluate("//div[@class='pro-name']/a");
        if ($author_nodes->length > 0) {
            $attributes['author'] = trim($this->utf_to_1251($author_nodes->item(0)->nodeValue));
        }

        $this->cat_name = '';
        $this->cat_title = '';

        switch ($this->operation_cur) {
            case 'obmen':
                $this->cat_title = 'Меняют';
                break;
            case 'prodazha':
                $this->cat_title = 'Продают';
                break;
            case 'posutochnaya-arenda':
            case 'arenda':
                $this->cat_title = 'Сдают';
                break;
            default:
                return false;
        }

        switch ($this->category_cur) {
            case 'komnat':
                $this->cat_name = 'Комната';
                break;
            case 'kvartir':
                $this->cat_name = 'Квартира';
                break;
            case 'domov':
                $this->cat_name = 'Дом';
                break;
            case 'zemli':
                $this->cat_name = 'Земельный участок';
                break;
            case 'ofisov':
            case 'torgovyh-ploschadey':
            case 'promyshlennyh-ploschadey':
            case 'rekreacionnyh-obektov':
            case 'skladov':
                $this->cat_name = 'Коммерческая';
                break;
            default:
                return false;
        }

        $image_nodes = $xpath->evaluate('//a[@data-fancybox="gallery"]');
        $n_img = $image_nodes->length;
        echo "\nThere are $n_img images\n";


        $attributes['approved'] = 1;
        $attributes['row_type'] = $this->region_cur[3];
        $md5 = md5(serialize(array(
            $phones,
            $this->cat_name,
            $this->cat_title,
            $adv_text,
            $this->def_city,
            $price,
            $currency,
            $attributes,
            $n_img
        )));

        //if changed id but same data
        $q = $this->db->query("
             DELETE FROM dle_siteparser
             WHERE website_id='{$this->website_id}'
             AND adv_md5='{$md5}'
             AND url_id!='{$id}'
             ");
        //Check if adv exists in DB
        $q = $this->db->super_query("
             SELECT id
             FROM dle_siteparser
             WHERE website_id='{$this->website_id}'
             AND adv_md5='{$md5}'
             ORDER BY id DESC
             LIMIT 1
             ");//select last adv with same data and url_id

        if (!empty($q['id'])) {//if these data are in DB
            $id_last = $q['id'];
            $q = $this->db->query("
             DELETE FROM dle_siteparser
             WHERE website_id='{$this->website_id}'
             AND adv_md5='{$md5}'
             AND id!='{$id_last}'
             ");

            return false; //have this adv in DB
        }

       /* $this->get_address_street($attributes['address'], $this->def_city, $attributes);
        if ((strlen($adv_text) > 0) AND isset($attributes['downtown'])) {
            unset($attributes['downtown']);
            $this->get_address_street($adv_text, $this->def_city, $attributes);
        }*/

        //$attributes['last_date_add'] = $this->last_date_add;

        $images = array();
        if ($n_img > 0) {
            foreach ($image_nodes as $image_node) {
                $image_url = $image_node->getAttribute('href');
                echo 'url img: ' . $image_url . "\n";
                $image = $this->image_parser->fetchImage($image_url, '', 's' . $this->website_id);
                if ($image) {
                    $image = $this->validateImage($image, ROOT_DIR . 'img/');
                    if ($image) {
                        $img = Image::factory(ROOT_DIR . 'img/' . $image);
                        $img->save(ROOT_DIR . 'img/' . $image, 80);
                        unset($img);
                        $images[] = $image;
                    }
                    if (count($images) > 19) {
                        break;
                    }
                }
            }
        }
        $attributes['images'] = $images;

        $insert_id = $this->save_post($url, $id, $phones, $this->cat_name, $this->cat_title, $adv_text, $this->def_city, $md5, $price, $currency, $attributes);

        if ($insert_id) {
            $this->save_statistics($id, count($images), $this->def_city, $this->cat_title, $url);
            return true;
        } else
            return false;
    }
}
