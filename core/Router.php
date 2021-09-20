<?php

class Router {
	public static $directory = 'services/';
	public static $method = 'parse';
	public static $uri = '';
	/**
	 * @var Core
	 */
	public static $controller;

	public static function setDefaultMethod ($method) {
		self::$method = $method;
	}

	public static function setDirectory ($directory) {
		self::$directory = $directory;
	}

	/**
	 * @return Core
	 */
	public static function getController() {
		return self::$controller;
	}

	public static function route () {
        $file = '';
        
        if (PHP_SAPI === 'cli') {
			foreach ($_SERVER['argv'] as $arg) {
				if (strpos($arg, '--p=') === 0) {
					$arg = explode('=', $arg);
					$arg = $arg[1];
					$file = $arg;
				}
			}
        } else {
        	$file = isset($_GET['q']) ? $_GET['q'] : '';
			$file = preg_replace('#([^A-Za-z_/\-.])#si' , '', $file);
        }

        self::runParser($file);
    }
    
    public static function runParser($file) {
		$file = trim(trim(strtolower($file)), '/');
		self::$uri = $file;

		if (strpos($file, 'fix.') === 0) {
			self::$directory = 'fix/';
			$file = substr($file, 4);
		}

		if (strpos($file, '/')) {
			$file_parts = explode('/', $file);
			$file = $file_parts[0];
			
			if (isset($file_parts[1])) {
				self::$method = $file_parts[1];
			}
		}
		
        if (file_exists(ROOT_DIR . self::$directory . $file . '.php')) {
			//die(ROOT_DIR . self::$directory . $file . '.php');
			include ROOT_DIR . self::$directory . $file . '.php';
        }
        $class = str_replace(array('.', '-'), '_', $file);
        if (class_exists($class)) {
			self::$controller = new $class;
            $method = self::$method;
            if (method_exists(self::$controller, $method)) {
				self::$controller->$method();
            } else {
                throw new BadMethodCallException("Method, $method, not supported.");
            }
        } else {
            throw new Exception("Please, say action.");
        }
    }
}
