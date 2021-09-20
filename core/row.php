<?php

class Orm extends Sphorm {
	
    // mapping
	static $mapping = array(
		'table' => 'dle_siteparser',
		'id' => array(
			'column'=> 'id',
			'generator' => 'auto'
		),

			'columns' => array(
				'website_id' => 'website_id',
				'url' => 'url',
				'url_id' => 'url_id',
				'phone' => 'phone',
				'type' => 'type',
				'type_object' => 'type_object',
				'alltext' => 'alltext',
				'date_add' => 'date_add',
				'images' => 'images'
			)
		);

	public function __construct(array $init = array()) {
		parent::__construct($init);
	}
}
