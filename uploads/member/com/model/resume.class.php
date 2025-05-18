<?php
/* *
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class resume_controller extends company{
    function index_action(){
        $CacheM		=	$this -> MODEL('cache');
        $CacheList	=	$CacheM -> GetCache (array('city','user','job','hy','uptime'));
		$this -> yunset($CacheList);
        if(empty($CacheList['city_type'])){
            $this   ->  yunset('cionly',1);
        }
        if(empty($CacheList['job_type'])){
            $this   ->  yunset('jionly',1);
        }
        $this -> yunset("date",date("Y",0));
        $this -> yunset("time",date("Y",time()));

        $this -> yunset("type",$_GET['type']);
        $this -> public_action();
        $this -> company_satic();
        $this->yunset('comInfo',$this->comInfo);
        $this -> com_tpl('resume');
    }
}