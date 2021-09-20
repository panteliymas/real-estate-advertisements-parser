<?php
if ( ! function_exists('__'))
{
	/**
	 * Kohana translation/internationalization function. The PHP function
	 * [strtr](http://php.net/strtr) is used for replacing parameters.
	 *
	 *    __('Welcome back, :user', array(':user' => $username));
	 *
	 * [!!] The target language is defined by [I18n::$lang].
	 *
	 * @uses    I18n::get
	 * @param   string  $string text to translate
	 * @param   array   $values values to replace in the translated text
	 * @param   string  $lang   source language
	 * @return  string
	 */
	function __($string, array $values = NULL, $lang = 'en-us')
	{
		return empty($values) ? $string : strtr($string, $values);
	}
}

if (! class_exists('Kohana_Exception')) {
	class Kohana_Exception extends Exception {
		public function __construct($message = "", array $variables = NULL, $code = 0, Exception $previous = NULL)
		{
			// Set the message
			$message = __($message, $variables);

			// Pass the message and integer code to the parent
			parent::__construct($message, (int) $code, $previous);

			// Save the unmodified code
			// @link http://bugs.php.net/39615
			$this->code = $code;
		}
	}
}

require_once 'Image.php';

abstract class Image extends Kohana_Image {}

if (extension_loaded('imagick') ){
	Image::$default_driver = 'GD';
}

require_once 'Image/GD.php';
require_once 'Image/Imagick.php';

