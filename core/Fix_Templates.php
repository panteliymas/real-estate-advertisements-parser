<?php


class Fix_Templates {

	public function __construct()
	{
		//$this->bodyStart();
	}

	public static function buildError( $error )
	{
		if (!is_array($error)) {
			$error = (array)$erorr;
		}
		
		if (count($error) == 0) {
			return '';
		}
		$html = '<ul class="error">';
		foreach($error as $err) {
			$html .= '<li>' . $err . '</li>';
		}
		$html .= '</ul>';
		return $html;
	}
	
	public function bodyStart()
	{
echo '<!DOCTYPE html>
<html>
<head>
<meta charset="windows-1251">
<title></title>
<meta name="robots" content="noindex" />
<link rel="stylesheet" href="' . HOME_URL . '/assets/css/style.css" type="text/css" />
<script type="text/javascript" src="' . HOME_URL . '/assets/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="' . HOME_URL . '/assets/js/jquery.thfloat-0.5.min.js"></script>
<script type="text/javascript" src="' . HOME_URL . '/assets/js/my.js"></script>
</head>
<body>

<div id="header">
	<a href="/realtyadmin/index/">Главная</a>
	<a href="/realtyadmin/parser/">Парсеры</a>
	<a href="/realtyadmin/parser/">Справочники</a>
</div>
';		
	}

	public function bodyEnd() {
		echo '</body></html>';
	}
	
	public function render( $template, $vars = array()) {
		extract($vars);
		include ADMIN_DIR . 'view/' . $template . '.php';
	}
	
	public function table_start($title, $cols) {
		$cols_html = '';
		foreach($cols as $col)
		{
			$cols_html .= '<th scope="col">' . $col .'</th>';
		}

echo <<<TABLE
<h1>{$title}</h1>
                            <table class="table table-striped table-bordered boo-table" width="500">
                                <thead>
                                    <tr>
{$cols_html}
                                    </tr>
                                </thead>
                                <tbody>


TABLE;

	}

	public function table_row() {
		$numargs = func_num_args();
		$arg_list = func_get_args();
		$td = '';
		for ($i = 0; $i < $numargs; $i++) {
			$td .= '<td>' . $this->explode_words( $arg_list[$i] ) . '</td>';
		}

		echo <<<TABLE
			<tr>
$td
			</tr>
TABLE;



	}

	public function explode_words($mass) {
		$str = explode(" ", $mass);
		$MESSAGE = '';
		$i=count($str);
		for($j=0;$j<=$i;$j++)
		{
			$limit=strlen($str[$j]);
			if($limit>20)
			{
				for($k=0; $k<=$limit; $k++)
				{
					if($k%20 == 0)
					{
						$MESSAGE .= " " . $str[$j][$k];
					} else
					{
						$MESSAGE .= $str[$j][$k];
					}
				}
			} else
			{
				$MESSAGE .= " " . $str[$j];
			}

		}
		return $MESSAGE;
	}

	public function table_end() {

echo <<<HTML

                                </tbody>
                            </table>
HTML;

	}
}

