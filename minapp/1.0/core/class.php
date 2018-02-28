<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_core.php 28824 2012-03-14 06:41:27Z zhangguosheng $
 */

define('DISCUZ_CORE_DEBUG', false);

set_exception_handler(array('core', 'handleException'));

if(DISCUZ_CORE_DEBUG) {
	set_error_handler(array('core', 'handleError'));
	register_shutdown_function(array('core', 'handleShutdown'));
}

if(function_exists('spl_autoload_register')) {
	spl_autoload_register(array('core', 'autoload'));
} else {
	function __autoload($class) {
		return core::autoload($class);
	}
}

C::creatapp();

class core
{
	private static $_tables;
	private static $_imports;
	private static $_app;
	private static $_memory;

	public static function app() {
		return self::$_app;
	}

	public static function creatapp() {
		if(!is_object(self::$_app)) {
			self::$_app = discuz_application::instance();
		}
		return self::$_app;
	}

	public static function t($name) {
		$pluginid = null;
		if($name[0] === '#') {
			list(, $pluginid, $name) = explode('#', $name);
		}
		$classname = 'table_'.$name;
		if(!isset(self::$_tables[$classname])) {
			if(!class_exists($classname, false)) {
				self::import(($pluginid ? 'plugin/'.$pluginid : 'class').'/table/'.$name);
			}
			self::$_tables[$classname] = new $classname;
		}
		return self::$_tables[$classname];
	}

    public static function m($name) {
        $args = array();
        if(func_num_args() > 1) {
            $args = func_get_args();
            unset($args[0]);
        }
        return self::_make_obj($name, 'model', true, $args);
    }

    protected static function _make_obj($name, $type, $extendable = true, $p = array()) {
        $pluginid = null;
        if($name[0] === '#') {
            list(, $pluginid, $name) = explode('#', $name);
        }
        $cname = $type.'_'.$name;
        if(!isset(self::$_tables[$cname])) {
            if(!class_exists($cname, false)) {
                self::import(($pluginid ? 'plugin/'.$pluginid : 'class').'/'.$type.'/'.$name);
            }
            if($extendable) {
                self::$_tables[$cname] = new discuz_container();
                switch (count($p)) {
                    case 0:	self::$_tables[$cname]->obj = new $cname();break;
                    case 1:	self::$_tables[$cname]->obj = new $cname($p[1]);break;
                    case 2:	self::$_tables[$cname]->obj = new $cname($p[1], $p[2]);break;
                    case 3:	self::$_tables[$cname]->obj = new $cname($p[1], $p[2], $p[3]);break;
                    case 4:	self::$_tables[$cname]->obj = new $cname($p[1], $p[2], $p[3], $p[4]);break;
                    case 5:	self::$_tables[$cname]->obj = new $cname($p[1], $p[2], $p[3], $p[4], $p[5]);break;
                    default: $ref = new ReflectionClass($cname);self::$_tables[$cname]->obj = $ref->newInstanceArgs($p);unset($ref);break;
                }
            } else {
                self::$_tables[$cname] = new $cname();
            }
        }
        return self::$_tables[$cname];
    }

	public static function memory() {
		if(!self::$_memory) {
			self::$_memory = new discuz_memory();
			self::$_memory->init(self::app()->config['memory']);
		}
		return self::$_memory;
	}

	public static function import($name, $folder = '', $force = true) {
		$key = $folder.$name;
		if(!isset(self::$_imports[$key])) {
			$path = dirname(__FILE__).'/';
			if(strpos($name, '/') !== false) {
				$pre = basename(dirname($name));
				$filename = dirname($name).'/'.$pre.'_'.basename($name).'.php';
			} else {
				$filename = $name.'.php';
			}

			if(!is_file($path.'/'.$filename)) {
				$path = DISCUZ_ROOT.'/source/'.$folder;
			}

			if(!is_file($path.'/'.$filename)) {
				$path = DISCUZ_ROOT.'/'.$_G['minbbs_config']['minbbs_type'].'/core/'.$folder;
			}

			if(is_file($path.'/'.$filename)) {
				self::$_imports[$key] = true;
				return include $path.'/'.$filename;
			} elseif(!$force) {
				return false;
			} else {
				throw new Exception('Oops! System file lost: '.$filename);
			}
		}
		return true;
	}

	public static function handleException($exception) {
		discuz_error::exception_error($exception);
	}


	public static function handleError($errno, $errstr, $errfile, $errline) {
		if($errno & DISCUZ_CORE_DEBUG) {
			discuz_error::system_error($errstr, false, true, false);
		}
	}

	public static function handleShutdown() {
		if(($error = error_get_last()) && $error['type'] & DISCUZ_CORE_DEBUG) {
			discuz_error::system_error($error['message'], false, true, false);
		}
	}

	public static function autoload($class) {
		$class = strtolower($class);
		if(strpos($class, '_') !== false) {
			list($folder) = explode('_', $class);
			$file = 'class/'.$folder.'/'.substr($class, strlen($folder) + 1);
		} else {
			$file = 'class/'.$class;
		}

		try {

			self::import($file);
			return true;

		} catch (Exception $exc) {

			$trace = $exc->getTrace();
			foreach ($trace as $log) {
				if(empty($log['class']) && $log['function'] == 'class_exists') {
					return false;
				}
			}
			discuz_error::exception_error($exc);
		}
	}
}

class C extends core {}
class DB extends discuz_database {}

?>