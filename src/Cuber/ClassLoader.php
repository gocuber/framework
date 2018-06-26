<?php

/**
 * ClassLoader
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class ClassLoader
{

    private static $_instance = null;

    private $_class_map = null;

    private $_include_path = null;

    public static function getInstance()
    {
        if(!isset(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * init
     *
     * @return void
     */
    public static function init()
    {
        $_class_map = include(CUBE_DIR . 'common/autoload_classmap.php');
        !empty($GLOBALS['_G']['class_map']) and is_array($GLOBALS['_G']['class_map']) and $_class_map = array_merge($_class_map, $GLOBALS['_G']['class_map']);

        $_include_path = [APP_DIR, ''];
        !empty($GLOBALS['_G']['include_path']) and is_array($GLOBALS['_G']['include_path']) and $_include_path = array_merge($_include_path, $GLOBALS['_G']['include_path']);

        self::getInstance()->addClassMap($_class_map)->addIncludePath($_include_path)->register();
        unset($_class_map,$_include_path);
    }

    /**
     * addClassMap
     *
     * @param array $map
     *
     * @return $this
     */
    public function addClassMap($map = array())
    {
        if(empty($map) or !is_array($map)){
            return $this;
        }
        if(isset($this->_class_map)){
            $this->_class_map = array_merge($this->_class_map, $map);
        }else{
            $this->_class_map = $map;
        }
        return $this;
    }

    /**
     * addIncludePath
     *
     * @param array $path
     * @param bool $prepend 如果是 true 会添加到队列之首 而不是队列尾部 默认false
     *
     * @return $this
     */
    public function addIncludePath($path = array(), $prepend = false)
    {
        if(empty($path) or !is_array($path)){
            return $this;
        }
        if(isset($this->_include_path)){
            if($prepend){
                $this->_include_path = array_merge($path, $this->_include_path);
            }else{
                $this->_include_path = array_merge($this->_include_path, $path);
            }
        }else{
            $this->_include_path = $path;
        }
        return $this;
    }

    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register(array($this,'loadClass'));
    }

    /**
     * loadClass
     *
     * @param string $class
     *
     * @return void
     */
    private function loadClass($class = '')
    {
        if($file = $this->findFile($class)){
            include $file;
        }
    }

    /**
     * findFile
     *
     * @param string $class
     *
     * @return string
     */
    private function findFile($class = '')
    {
        // class map
        if(isset($this->_class_map[$class])){
            return $this->_class_map[$class];
        }

        $_class = strtr($class, array('\\'=>'/','_'=>'/'));
        $include_path = $this->_include_path;
        if(!empty($include_path) and is_array($include_path)){
            foreach($include_path as $path){
                $file = $path . $_class . '.php';
                if(is_file($file)){
                    return $file;
                }
            }
        }

        try {
            throw new CubeException("Class '{$class}' not found");
        } catch (CubeException $e) {
            $e->log();
        }
    }

}
