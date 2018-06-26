<?php

/**
 * SmartyCls
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class SmartyCls
{

    /**
     * display
     *
     * @param string $tpl
     * @param array $hash
     *
     * @return void
     */
    public function display($tpl = '', $hash = null)
    {

        /* 包含smarty */
        include_once (CUBE_DIR . 'cube/lib/smarty/libs/Smarty.class.php');
        $smarty = new Smarty();

        /* smarty关键配置 */
        $smarty->caching        = false; // 开启缓存
        $smarty->cache_lifetime = 3600;  // 缓存时间
        $smarty->compile_check  = true;  // 每次执行时都会检查当前模板是否被修改过，如果修改过，会重新编译那个模板，上面的缓存时间没过也会重新编译
        $smarty->force_compile  = false; // 强迫smarty每次都重新编译模板(优先级最高，设置为true时compile_check将无效，如开启了缓存caching，每次将会重新生成缓存文件，建议在线下开发时开启，产品正式上线后设置为false)

        /* 目录相关配置 */
        $smarty->template_dir = VIEWS_DIR; // 模板目录
        $smarty->compile_dir  = '/tmp/smarty/templates_c/'; // 编译目录
        $smarty->config_dir   = '/tmp/smarty/config/';      // 配置目录
        $smarty->cache_dir    = '/tmp/smarty/cache/';       // 缓存目录

        // is_mkdir($smarty->compile_dir); // smarty3以下版本不会自动创建多重目录
        // is_mkdir($smarty->config_dir);
        // is_mkdir($smarty->cache_dir);

        /* 其它配置 */
        $smarty->allow_php_tag   = true;   // smarty3默认禁止了{php}标签，如果要启用需要设置一下 true为启用
        $smarty->debugging       = false;  // 是否启动调试控制台 如打开将显示 smarty 变量和运行状态的调试窗口
        $smarty->left_delimiter  = '{{';   // 左右结束符
        $smarty->right_delimiter = '}}';

        /* 使用smarty */
        $smarty->assign($hash); // 赋值
        $smarty->display($tpl); // 显示调用模板

    }

}
