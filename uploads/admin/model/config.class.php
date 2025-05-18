<?php

/*
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class config_controller extends adminCommon{

    /**
     * 系统-网站设置
     */
    function index_action(){
        if (strpos($this->config['sy_weburl'], 'https') !== false) {
            
            $this->config['mapurl'] = 'https://api.map.baidu.com/api?v=2.0&ak=' . $this->config['map_key'] . '&s=1';
        } else {
            $this->config['mapurl'] = 'http://api.map.baidu.com/api?v=2.0&ak=' . $this->config['map_key'];
        }
        if ($this->config['sy_ossurl'] == ''){
            $this->config['sy_ossurl']  =  $this->config['sy_weburl'];
        }
        $this->yunset("config", $this->config);

        $this->yunset('sphinxSearchd', $sphinxSearchd);
        
        $this->yuntpl(array('admin/admin_web_config'));
    }

    /**
     * 系统-网站设置-网站logo
     */
    function save_logo_action(){
        if ($_POST['waterconfig']) {
            
            $this->web_config();
            
            $this->ACT_layer_msg('网站LOGO配置设置成功！', 9, $_SERVER['HTTP_REFERER'], 2, 1);
        }
    }

    // 保存
    function save_action(){
        if ($_POST['config']) {
            
            if ($_POST['config'] == 'uploadconfig'){
                
                // 上传参数为空，保存默认值
                if (!$_POST['pic_maxsize'] || ($_POST['pic_maxsize'] == '' || $_POST['pic_maxsize'] < 1)){
                    $_POST['pic_maxsize'] = 5;
                }
                if (!$_POST['file_maxsize'] || ($_POST['file_maxsize'] == '' || $_POST['file_maxsize'] < 1)){
                    $_POST['file_maxsize'] = 5;
                }

                if (!$_POST['pic_type']){
                    $_POST['pic_type'] = 'jpg,png,jpeg,bmp,gif';
                }else{
					$pic_type			=  explode(',',str_replace(' ','',$_POST['pic_type']));

					//禁止后台设定可执行程序后缀
					foreach($pic_type as $pickey => $picvalue){

						$new_pic_type	=	strtolower(str_replace('.','',trim($picvalue)));
						if(in_array($new_pic_type,array('php','asp','aspx','jsp','exe','do'))){
						
							unset($pic_type[$pickey]);
						}
					}
					$_POST['pic_type']	=	implode(',',$pic_type);
				}
                if (!$_POST['file_type']){
                    $_POST['file_type'] = 'rar,zip,doc,docx,xls';
                }else{
					$file_type			=  explode(',',str_replace(' ','',$_POST['file_type']));
					
					//禁止后台设定可执行程序后缀
					foreach($file_type as $filekey => $filevalue){

						$new_file_type	=	strtolower(str_replace('.','',trim($filevalue)));
						if(in_array($new_file_type,array('php','asp','aspx','jsp','exe','do'))){
						
							unset($file_type[$filekey]);
						}
					}
					$_POST['file_type']	=	implode(',',$file_type);
				}
                if (!$_POST['wmark_position']){
                    $_POST['wmark_position'] = 1;
                }
            }
            unset($_POST['config']);
            unset($_POST['pytoken']);
            if ($_POST['map_key']) {
                if (strpos($this->config['sy_weburl'], 'https') !== false) {
                    
                    $_POST['mapurl'] = 'https://api.map.baidu.com/api?v=2.0&ak=' . $_POST['map_key'] . '&s=1';
                } else {
                    $_POST['mapurl'] = 'http://api.map.baidu.com/api?v=2.0&ak=' . $_POST['map_key'];
                }
            }
            if ($_POST['sy_oss'] == 2){
                $_POST['sy_ossurl']  =  '';
            }
            $configM  =  $this->MODEL('config');
            
            $configM -> setConfig($_POST);
            
            // 判断验证字符
            if ($_POST['code_strlength'] < 5) {
                $this->web_config();
                $this->layer_msg("网站配置设置成功！", 9, 1);
            } else {
                $this->layer_msg("验证码字符数不要大于4！", 8, 1, '');
            }
        }
    }

    // 加载模板缓存
    function settplcache_action(){
        include (CONFIG_PATH . "db.data.php");
        include (PLUS_PATH . "cache.config.php");
        $modelconfig = $arr_data['modelconfig'];
        
        foreach ($modelconfig as $key => $value) {
            $newModel[$key]['value'] = $value;
            $newModel[$key]['cache'] = $cache_config['sy_' . $key . '_cache'];
        }
        $this->yunset('newModel', $newModel);
        $this->yunset('cache_config', $cache_config);
        
        $this->yuntpl(array(
            'admin/admin_tplcache'
        ));
    }

    // 保存设置模板缓存
    function savetplcache_action(){
        if ($_POST["config"]) {
            unset($_POST["config"]);
            include (CONFIG_PATH . "db.data.php");
            $modelconfig = array_keys($arr_data['modelconfig']);
            $config_new = array();
            foreach ($_POST as $key => $v) {
                $model = explode('_', $key);
                if (in_array($model[1], $modelconfig) || $model[1] == 'index') {
                    $config_new[$key] = $v;
                }
            }
            
            made_web(PLUS_PATH . 'cache.config.php', ArrayToString($config_new), 'cache_config');
            $this->ACT_layer_msg("模块缓存设置修改成功！", 9, "index.php?m=config&c=settplcache", 2, 1);
        }
    }

    // 刷新sphinx主索引
    function refresh_sphinx_main_index_action(){
        require_once (APP_PATH . 'app/include/cron/sphinx_indexer_main.php');
        echo '1';
        exit();
    }

    // 开启sphinx时，检查searchd是否运行，生成sphinx.conf配置文件
    function check_usesphinx_action(){
        include_once (LIB_PATH . "sphinx.class.php");
        $useSphinx = false;
        if (sphinx::isRun($this->config['sphinxhost'], $this->config['sphinxport'])) {
            $useSphinx = true;
        }
        
        if ($useSphinx) {
            require_once (LIB_PATH . 'sphinxhelper.class.php');
            $helper = new sphinxhelper();
            $helper->generateConf();
            echo '1';
            exit();
        } else {
            echo '2';
            exit();
        }
    }

    // 生成sphinx.conf配置文件
    function generate_conf_action(){
        require_once (LIB_PATH . 'sphinxhelper.class.php');
        $helper = new sphinxhelper();
        $helper->generateConf();
        echo '1';
        exit();
    }

    // 启动/停止sphinx的searchd搜索服务
    public function sphinx_searchd_action(){
        if (! isset($_POST['status']) || ($_POST['status'] != 'start' && $_POST['status'] != 'stop')) {
            echo '2';
            exit();
        }
        
        if (isServerOsWindows()) { // windows服务器
            if ($_POST['status'] == 'start') {
                exec('net start SphinxSearch');
            } else {
                exec('net stop SphinxSearch');
            }
        } else { // linux服务器
            if ($_POST['status'] == 'start') {
                exec('searchd');
            } else {
                exec('searchd --stop');
            }
        }
        echo '1';
        exit();
    }
    //后台专用，layui上传图片公共方法
    function layui_upload_action()
    {
        
        if($_FILES['file']['tmp_name']){

            $data  =  array(
                'name'      =>  $_POST['name'],
                'path'      =>  $_POST['path'],
                'imgid'     =>  $_POST['imgid'],
                'file'      =>  $_FILES['file']
            );
            

            $UploadM=$this->MODEL('upload');
            
            $return = $UploadM->layUpload($data);
            
            if (!empty($_POST['name']) && $return['code'] == 0){
                // 后台上传logo后，重新生成缓存
                $this->web_config();
            }
        }else{
            $return  =  array(
                'code'  =>  1,
                'msg'   =>  '请上传文件',
                'data'  =>  array()
            );
        }
        echo json_encode($return);
    }
}
?>