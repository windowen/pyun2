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
class database_controller extends adminCommon{
	function get_table(){
		include(LIB_PATH."dbbak.class.php");
		$dbbak	=	new DBManagement("phpyun",CONFIG_PATH."backup/",$this->obj,$this->db);
		return $dbbak;
	}
	function index_action(){
		$dbbak	=	$this->get_table();
		$table	=	$dbbak->GetTablesName();
		$list	=	array_chunk($table, 4, true);
		$this->yunset("list",$list);
		$this->yunset('basename',date("Y-m-d",strtotime("today").'phpyun.sql'));
		$this->yuntpl(array('admin/admin_database'));
	}
	function down_sql_action(){
		$file	=	$this->config[sy_weburl]."/data/backup/$_GET[name]";
		header('Content-type: application/sql');
		header('Content-Disposition: attachment; filename="'.$_GET[name].'"');
		readfile($file);
	}
	function backup_action(){
        include(LIB_PATH.'/dbbackup/class/functions.php');
        global $db_config;
        $maxfilesize	=	$_POST['maxfilesize'];
		
        $maxfilesize	=	is_numeric($maxfilesize)?$maxfilesize:300;
        
		$DBParameter	=	array(
        	'phome'			=>	'',
        	'mydbname'		=>	$db_config['dbname'],
        	'servername'	=>	$db_config['dbhost'],
        	'oldtablepre'	=>	$db_config['def'],
        	'newtablepre'	=>	$db_config['def'],
        	'bktype'		=>	0,
        	'filesize'		=>	300,
        	'bakline'		=>	500,
        	'autoauf'		=>	'',
        	'bakstru'		=>	'',
        	'bakdatetype'	=>	'',
        	'mypath'		=>	'',
        	'waitbaktime'	=>	'',
        	'tablename'		=>	$_POST['table'],
        	'dbchar'		=>	$db_config['charset'],
        	'backuppath'	=>	PLUS_PATH.'/bdata/',
        	'filesize'		=>	$maxfilesize
        		
        );
        BackupDatabaseInit($DBParameter);die;
		global $db_config;
		extract($_POST);
		$dbbak	=	$this -> get_table();
		$fw		=	$dbbak -> backup_action($table,10000000000,$db_config);
		$fw?$this->layer_msg('备份成功！',9,1,'index.php?m=database&c=backin'):$this->layer_msg('备份失败！',8,1,$_SERVER['HTTP_REFERER']);
	}
    function BackupDatabaseFileSize_action(){
        include(LIB_PATH.'/dbbackup/class/functions.php');
        $t			=	$_GET['t'];
        $s			=	$_GET['s'];
        $p			=	$_GET['p'];
        $mypath		=	$_GET['mypath'];
        $alltotal	=	$_GET['alltotal'];
        $thenof		=	$_GET['thenof'];
        $fnum		=	$_GET['fnum'];
        $stime		=	$_GET['stime'];
        BackupDatabaseFileSize($t,$s,$p,$mypath,$alltotal,$thenof,$fnum,$stime);
    }
    function BackupDatabaseRecordNum_action(){
        include(LIB_PATH.'/dbbackup/class/functions.php');
        $t			=	$_GET['t'];
        $s			=	$_GET['s'];
        $p			=	$_GET['p'];
        $mypath		=	$_GET['mypath'];
        $alltotal	=	$_GET['alltotal'];
        $thenof		=	$_GET['thenof'];
        $fnum		=	$_GET['fnum'];
        $auf		=	$_GET['auf'];
        $aufval		=	$_GET['aufval'];
        $stime		=	$_GET['stime'];
        BackupDatabaseRecordNum($t,$s,$p,$mypath,$alltotal,$thenof,$fnum,$auf,$aufval,$stime);
    }
	function backin_action(){
		$filedb		=	array();
		$dbbak		=	$this->get_table();
		$sqlarr		=	$this->BackupList();
		$this->yunset("sqlarr",$sqlarr);
		$this->yuntpl(array('admin/admin_database_back'));
	}
    function BackupList(){
        global $db_config;
		$filedb		=	array();
		$handle		=	opendir(PLUS_PATH.'/bdata/');
		while($file = readdir($handle)){
			if(preg_match("/^".$db_config['dbname']."/",$file) &&is_dir(PLUS_PATH.'/bdata/'.$file)){
                include(PLUS_PATH.'/bdata/'.$file.'/config.php');
				$time		=	date("Y-m-d",filemtime(PLUS_PATH.'/bdata/'.$file));
				$filedb[]	=	array(
					'name'		=>	$file,
					'version'	=>	$b_version,
					'time'		=>	$time,
					'dbname'	=>	$b_dbname,
					'charset'	=>	$b_dbchar
				);
			}
		}
		return $filedb;
	}
	function del_action(){
		$this -> check_token();
        $handle		=	opendir(PLUS_PATH.'/bdata/'.$_GET['sql']);
		while($file = readdir($handle)){
			$filedb[]=$file;
            @unlink(PLUS_PATH.'/bdata/'.$_GET['sql'].'/'.$file);
		}
        $delid		=	rmdir(PLUS_PATH.'/bdata/'.$_GET['sql']);
		//$delid=@unlink(CONFIG_PATH."backup/".$_GET['sql']);
		($delid==true)?$this->layer_msg('数据库备份删除成功！',9,0,$_SERVER['HTTP_REFERER'],1):$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER'],1);
	}
	function sql_action(){
		extract($_GET);
		//OPTIMIZE TABLE `zbcms_uploads` 优化表
		//REPAIR TABLE `zbcms_uploads` 修复表
		//$dbbak=$this->get_table();
		//$fw=$dbbak->backup_action($table,10000000000,$db_config);//备份表
		//TRUNCATE `fanwe_weight`;清空表

		if($type==1){
            include(LIB_PATH.'/dbbackup/class/functions.php');
            global $db_config;$maxfilesize=$_POST['maxfilesize'];$maxfilesize=is_numeric($maxfilesize)?$maxfilesize:300;
            $DBParameter=array(
            	'phome'			=>	'',
            	'mydbname'		=>	$db_config['dbname'],
            	'servername'	=>	$db_config['dbhost'],
            	'oldtablepre'	=>	$db_config['def'],
            	'newtablepre'	=>	$db_config['def'],
            	'bktype'		=>	0,
            	'filesize'		=>	300,
            	'bakline'		=>	500,
            	'autoauf'		=>	'',
            	'bakstru'		=>	'',
            	'bakdatetype'	=>	'',
            	'mypath'		=>	'',
            	'waitbaktime'	=>	'',
            	'tablename'		=>	$_GET['name'],
            	'dbcahr'		=>	$db_config['charset'],
            	'backuppath'	=>	PLUS_PATH.'/bdata/',
            	'filesize'		=>	$maxfilesize
            		
            );
            BackupDatabaseInit($DBParameter);die; 
			/*global $db_config;
			$this->check_token();
			$dbbak=$this->get_table();
			$fw=$dbbak->backup_action(array($name),10000000000,$db_config);//备份表
			$type_name="备份".$name;*/
		}
		if($type==2){
			$fw			=	$this -> db ->query("REPAIR TABLE `".$name."`");
			$type_name	=	"修复".$name;
		}
		if($type==3){
			$fw 		=	$this -> db -> query("OPTIMIZE TABLE  `".$name."`");
			$type_name	=	"优表".$name;
		}
 		$fw?$this->layer_msg($type_name."成功！",9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg($type_name."失败！",8,0,$_SERVER['HTTP_REFERER']);
	}
	function backincheck_action(){        
        include(LIB_PATH.'/dbbackup/class/functions.php');
        global $db_config;
        $add	=	array('mydbname'=>$db_config['dbname'],'waitbaktime'=>0);
        $mypath	=	$_GET['mypath'];
        RecoverData($add,$mypath);die;
		$this -> check_token();
		global $db_config;
		extract($_GET);
		$dbbak	=	$this->get_table();
		$dbbak	=	$dbbak->bakindata($sql);
		$dbbak?$this->layer_msg("数据库恢复成功！",9,0,$_SERVER['HTTP_REFERER']):$this->ACT_msg("恢复成功！",8,0,$_SERVER['HTTP_REFERER']);
	}
	function optimizing_action(){
	    $dbbak	=	$this->get_table();
	    $table	=	$dbbak->GetTablesName();
	    $num	=	0;
	    $list	=	array();
	    foreach ($table as $v){
	        $ret = $this->db->query('SHOW TABLE STATUS LIKE \'' . $v['name'] . '%\'');
 	        while ($row = $this->obj->fetch_assoc()) {
 	            $num   += $row['Data_free'];
 	            $list[] = array(
					'name'		=>	$row['Name'],
					'type'		=>	$row['Engine'],
					'num'		=>	$row['Rows'],
					'size'		=>	sprintf(' %.2f KB', $row['Data_length'] / 1024),
					'rec_index'	=>	$row['Index_length'],
					'chip'		=>	$row['Data_free'],
					'status'	=>	'',
					'charset'	=>	$row['Collation']
				);
 	        }
	    }
		$this->yunset("list",$list);
	    $this->yunset("table",$table);
	    
	    $this->yuntpl(array('admin/admin_database_optimizing'));
	}
	function run_optimize_action(){
	    $dbbak	=	$this  -> get_table();
	    $table	=	$dbbak -> GetTablesName();
	    foreach ($table as $k=>$v){
	        $fw = $this->db->query("OPTIMIZE TABLE  `".$v['name']."`");
	        if ($fw){
	            if (($fw['Msg_type'] == 'error') && (strpos($fw['Msg_text'], 'repair') !== false)) {
	                $this->db->query("REPAIR TABLE `".$v['name']."`");
	            }
	        }
	    }
	    $this->layer_msg('优化成功',9);
	}
	
	function clearList_action(){

		$this->yuntpl(array('admin/admin_database_clear'));
		
	}

	function clear_action(){
		
		if($_POST){

 			$clearData=@implode(',',$_POST['clear_data']);
			
  			if($clearData==""){

				$this->ACT_layer_msg("请选择需要清理的数据！",8);

			}else if($_POST['clear_time']==""){

				$this->ACT_layer_msg("请选择清理时间！",8);

			}else{

				$logM		=	$this -> MODEL('log');
				
				$jobM		=	$this -> MODEL('job');

				$clearTime = time() - $_POST['clear_time'] * 86400 ;

				$data['norecycle']		=	'1';
			 	
			 	if(in_array("userid_job",$_POST['clear_data'])){//职位申请记录

					$where['datetime']	=	array('<', $clearTime);
					
					$data['where']		=	$where;
					
			 		
					$return	=	$jobM -> delSqJob('',$data);

					$nid	=	$return['id'];
 			 	}
			 	
			 	if(in_array("userid_msg",$_POST['clear_data'])){//面试邀请记录

					$where['datetime']	=	array('<',$clearTime);
					
					$data['where']		=	$where;
 			 		
					$return	=	$jobM -> delYqms("",$data);

					$nid	=	$return['id'];
 			 	}
 			 	
			 	if(in_array("down_resume",$_POST['clear_data'])){//简历下载记录

					$where['downtime']	=	array('<',$clearTime);
					
					$data['where']		=	$where;
			 		
					$nid = $this -> MODEL('downresume') ->delInfo("",$data);
 			 	}
 			 	
			 	if(in_array("talent_pool",$_POST['clear_data'])){//简历收藏记录
				
					$where['ctime']		=	array('<',$clearTime);
					
					$data['where']		=	$where;
					
					$nid = $this -> MODEL('resume') -> delTalentPool("",$data);
 			 	}
			 	
			 	if(in_array("look_resume",$_POST['clear_data'])){//浏览简历记录

					$where['datetime']	=	array('<',$clearTime);
					
					$data['where']		=	$where;
					
					$nid	= $this -> MODEL('lookresume') ->delInfo($data);
 			 	}
			 	
			 	if(in_array("look_job",$_POST['clear_data'])){//浏览职位记录

					$where['datetime']	=	array('<',$clearTime);
					
					$data['where']		=	$where;
			 		
					$return =	$jobM -> delLookJob("",$data);

					$nid	=	$return['id'];
			 	}
			 	
			 	
			 	if(in_array("email_msg", $_POST['clear_data'])){//邮件发送记录
					
					$where['ctime']		=	array('<',$clearTime);
					
			 		$nid = $this -> MODEL('email') -> delEmailMsg($where, $data);
 			 	}
 		
 			 	if(in_array("moblie_msg", $_POST['clear_data'])){//短信发送记录
					
					$where['ctime']		=	array('<',$clearTime);
					
			 		$nid = $this -> MODEL('mobliemsg') -> delMoblieMsg($where, $data);
 			 	}
 			 	
			 	if(in_array("member_log", $_POST['clear_data'])){//会员日志
					
					$where['ctime']		=	array('<',$clearTime);
					
			 		$return = $logM -> delMemlog($where, $data);
					
			 		$nid	= $return['id'];
 			 	}
 			 	
			 	if(in_array("admin_log",$_POST['clear_data'])){//管理员日志
					
					$where['ctime']		=	array('<',$clearTime);
					
					$data['where']		=	$where;
					
			 		$return = $logM -> delAdminlog('',$data);

					$nid	=	$return['id'];
 			 	}
 			 	
 			 	if(in_array("recycle", $_POST['clear_data'])){//回收站
					
					$where['ctime']		=	array('<',$clearTime);
					
			 		$return = $this -> MODEL('recycle') -> delRecycle($where);
					
			 		$nid	= $return['id'];
 			 	}
			 	
			 	if($nid){
			 	    
			 	    $this->ACT_layer_msg('数据清理成功！',9,'index.php?m=database&c=clearList',2,1);
					
			 	}else{
					
			 	    $this->ACT_layer_msg('数据清理成功！',8,'index.php?m=database&c=clearList');
			 	}
 			}
		}
 	    
	}
}
?>