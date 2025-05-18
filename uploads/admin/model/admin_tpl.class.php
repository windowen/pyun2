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
class admin_tpl_controller extends adminCommon{
	function public_action(){
		include_once("model/model/style_class.php");//引用操作文件
	}
	//风格管理
	function index_action(){
		$this	->	public_action();
		$style	=	new style($this->obj);
		$list	=	$style	->	model_list_action();
		$this	->	yunset("sy_style",$this->config['style']);
		$this	->	yunset("list",$list);
		$this	->	yuntpl(array('admin/admin_style_list'));
	}
	function stylemodify_action(){
		$this		->	public_action();
		$style		=	new style($this->obj);
		$style_info	=	$style	->	model_modify_action($_GET['dir']);
		$this	->	yunset("style_info",$style_info);
		$this	->	yuntpl(array('admin/admin_style_modfy'));
	}
	function stylesave_action(){
		$this			->	public_action();
		$style			=	new style($this->obj);
	    $_POST['img']	=	$this	->	checksrc($_POST['img']);
		$style_info 	=	$style	->	model_save_action($_POST);
		$this	->	ACT_layer_msg("信息修改成功！",9,"index.php?m=admin_tpl",2,1);
	}
	function check_style_action(){
		if($_GET['dir']!=""){
			$data['style']	= 	$_GET['dir'];
			$configM		=	$this	->	MODEL('config');
			$configM	->	setConfig($data);
			$this		->	web_config();
			$this		->	layer_msg('模板风格更换成功！',9,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this		->	layer_msg('该目录无效！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}
	
	//企业模板
	function comtpl_action(){
		$tplM					=	$this	->	MODEL('tpl');
		$whereData['orderby']	=	'id,desc';
		$list					=	$tplM	->	getComtplList($whereData);
		$this	->	yunset("list",$list);
		$this	->	yuntpl(array('admin/admin_comtpl'));
	}
	function comtpladd_action(){
		if($_GET['id']){
			$tplM	=	$this	->	MODEL('tpl');
			$list	=	$tplM	->	getComtpl(array('id'=>$_GET['id']));
			$this	->	yunset("row",$list);
		}
		$this	->	yuntpl(array('admin/admin_comtpl_add'));
	}
	function comptplsave_action(){
	    
	    
		$tplM	=	$this -> MODEL('tpl');
	    
		if($_POST['pic']=='' && $_FILES['file']['tmp_name']==''){
	        $this	->	ACT_layer_msg("请上传缩略图！",8,"index.php?m=admin_tpl&c=comtpladd");
	    }
        if($_FILES['file']['tmp_name']){
	 		$upArr    =  array(
				'file'  	=>  $_FILES['file'],
				'dir'   	=>  'company',
				'type'		=>	'thumb',
				'thumb'    	=>	 array(
		 			'width'		=>	350,
		 			'height'	=>	350,
		 			'newNamePre'=>	'_S_'
		 		)
			);
			//缩率图参数

			$uploadM  =  $this->MODEL('upload');

			$pic      =  $uploadM->newUpload($upArr);

			if (!empty($pic['msg'])){

				$this->ACT_layer_msg($pic['msg'],8);

			}elseif (!empty($pic['picurl'])){

				$pictures 	=  	$pic['picurl'];
			}
	 	}
	    
	    if(isset($pictures)){

	    	$data['pic']		=	$pictures;

	    }
		$this->com_sava_action($_POST['url']);
		$data['name']			=	$_POST['name'];
		$data['url']			=	$_POST['url'];
		$data['status']			=	$_POST['status'];
		$data['price']			=	$_POST['price'];
		$data['service_uid']	=	$_POST['service_uid'];
		 
 		if($_POST['id']){
			
			$return		=	$tplM -> upComtpl($data,array("id"=>$_POST['id']));
		}else{
			
			$return		=	$tplM -> addComtpl($data);
		}
		
		$return['id']?$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=admin_tpl&c=comtpl",2,1):$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=admin_tpl&c=comtpl");
	}
	//企业站模板添加及修改
	function com_sava_action($url){
		//验证URL 必须只能是数字字母形式
		if(!ctype_alnum($url)){
			$this->ACT_layer_msg("目录名称只能是字母或数字！",8,$_SERVER['HTTP_REFERER'],2,1);
		}
		if(!is_dir("../app/template/company/".$url)){
			mkdir("../app/template/company/".$url,0777,true);
		}
	}
	function comtpldel_action(){
		$this->check_token();
		
		$del=$_GET['id'];
		
		if(!$del){$this->layer_msg('请先选择！',8,0,$_SERVER['HTTP_REFERER']);}
		
		$return	=	$this -> MODEL('tpl') -> delComtpl($del);
		
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	//简历模版
	function resumetpl_action(){
		$where['orderby']	=	array('id,desc');
		
		$list	=	$this -> MODEL('tpl') -> getResumetplList($where);
		
		$this->yunset("list",$list);
		
		$this->yuntpl(array('admin/admin_resumetpl'));
	}
	function resumetpladd_action(){
		if($_GET['id']){
			$where['id']	=	$_GET['id'];
			
			$list	=	$this -> MODEL('tpl') -> getResumetpl($where);
			
			$this->yunset("row",$list);
		}
		$this->yuntpl(array('admin/admin_resumetpl_add'));
	}
	function resumetplsave_action(){
		$tplM=$this -> MODEL('tpl');
		
		if($_POST['pic']=="" && $_FILES['file']['tmp_name']=='') {
			
		    $this->ACT_layer_msg("请上传缩略图！",8);

		}else{
			if($_FILES['file']['tmp_name']){

				$upArr    =  array(
					'file'  =>  $_FILES['file'],
					'dir'   =>  'resume'
				);

				$uploadM  =  $this->MODEL('upload');

				$picr      =  $uploadM->newUpload($upArr);
				
				if (!empty($picr['msg'])){

					$this->ACT_layer_msg($picr['msg'],8);
					

				}elseif (!empty($picr['picurl'])){

					$pictures 	=  	$picr['picurl'];
				}

			}
		 
		}
		if(isset($pictures)){
			$_POST['pic']	=	$pictures;
		}
		$this->resumetpl_sava_action($_POST['url']);
		
		unset($_POST['msgconfig']);
		
		if($_POST['id']){
			
			$return	=	$tplM -> upResumetpl($_POST,array("id"=>$_POST['id']));
		}else{
			
			$return	=	$tplM -> addResumetpl($_POST);
		}
		$return['id']?$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=admin_tpl&c=resumetpl",2,1):$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=admin_tpl&c=resumetpl");
	}
	function resumetpl_sava_action($url){
		if(!ctype_alnum($url)){
			$this->ACT_layer_msg("目录名称只能是字母或数字！",8,$_SERVER['HTTP_REFERER'],2,1);
		}
		if(!is_dir("../app/template/resume/".$url)){
			mkdir("../app/template/resume/".$url,0777,true);
		}
	}
	function resumetpldel_action(){
		$this->check_token();
		
		$del	=	$_GET['id'];
		
		if(!$del){$this->layer_msg('请先选择！',8,0,$_SERVER['HTTP_REFERER']);}
		
		$return	=	$this -> MODEL('tpl') -> delResumetpl($del);
		
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	//模板管理
	function templatepublic_action(){
		include_once("model/model/tmp_class.php");	
	}
	function template_action(){
		$publicdir = "../app/template/";	
		if($_GET['dir']){
			$dir = str_replace('.',"",$_GET['dir']);
		}
		if(!$dir){
			$hostdir = '';
		}else{
			$hostdir = $dir.'/';
			$row=explode('/',$hostdir);
			if(count($row)>2){
				$str_dir = array_slice($row,-2,1);
				$retrundir=str_replace("/".$str_dir[0]."/","",$hostdir);
			}else{
				$retrundir=str_replace($row[0]."/","",$hostdir);
			}	
			$floder[] = array('name'=>"返回上一级",'url'=>$retrundir);
		}	
		$filesnames = @scandir($publicdir.$hostdir);	
		if(is_array($filesnames)){
			foreach($filesnames as $key=>$value){
				if($value!='.' && $value!='..' ){
					if(is_dir($publicdir.$hostdir.$value)){
						$floder[] = array('name'=>$value,'url'=>$hostdir.$value);
					}elseif(is_file($publicdir.$hostdir.$value)){
						$typearr = explode('.',$hostdir.$value);
						if(in_array(end($typearr),array('txt','htm','html','xml','js','css'))){	
							$file[] = array('name'=>$value,'url'=>$hostdir.$value,'size'=>round((filesize($publicdir.$hostdir.$value)/1024),2)."KB",'time'=>date("Y-m-d H:i:s",filemtime($publicdir.$hostdir.$value)));
						}
					}
				}
			}
		}
		$this->yunset("floder",$floder);
		$this->yunset("file",$file);
		$this->yuntpl(array('admin/admin_template'));
	}
	function modify_action(){
		if($this->config['sy_istemplate']!='1'){
			$this->ACT_msg('index.php?m=config',"该功能已关闭，请到安全设置里开启后台模板修改功能！");
		}
		$hostdir = "../app/template/";
		$_GET['path'] = str_replace(array('./','../'),'',$_GET['path']);
		if(count(@explode('.',$_GET['path']))>2){
			$this->ACT_msg($_SERVER['HTTP_REFERER'],"非法的文件名！");
		}
		if(file_exists($hostdir.$_GET['path']) && $_GET['name']){	
			$path = $hostdir.$_GET['path'];
			$typearr = explode('.', $path);
			if(!in_array(end($typearr),array('txt','htm','html','xml','js','css'))){
				$this->ACT_msg($_SERVER['HTTP_REFERER'],"非法的文件名！");
			}
			$tp_info['name'] = $_GET['name'];
			$tp_info['path'] = $_GET['path'];
				
			$fp=@fopen($path,"r");
			$tp_info['content'] =@fread($fp,filesize($path));
			$tp_info['content'] = str_replace(array('<textarea>','</textarea>'),array('&lt;textarea&gt;','&lt;/textarea&gt;'),$tp_info['content']);
			fclose($fp);
			$this->yunset("safekey",$safekey);
				
			$tp_info['safekey'] = md5(md5($this->config['sy_safekey']).'admin_template');
			$this->yunset("tp_info",$tp_info);
			$this->yuntpl(array('admin/admin_template_modify'));
		}else{
			$this->ACT_msg($_SERVER['HTTP_REFERER'],"文件不存在！");
		}
	}
	function savetpl_action(){
		$hostdir = "../app/template/";
		$_GET['path'] = str_replace(array('./','../'),'',$_POST['tp_path']);
		if(count(@explode('.',$_POST['tp_path']))>2){
			$this->ACT_layer_msg("非法的文件名！",8);
		}	
		if(file_exists($hostdir.$_POST['tp_path']) && $_POST['code'] && md5(md5($this->config['sy_safekey']).'admin_template') == $_POST['safekey']){
			$path = $hostdir.$_POST['tp_path'];
			$typearr = explode('.',$path);
			if(!in_array(end($typearr),array('txt','htm','html','xml','js','css'))){
				$this->ACT_layer_msg($_SERVER['HTTP_REFERER'],"非法的文件名！");
			}	
			$fp=@fopen($path,"w");
			fwrite($fp,stripslashes($_POST['code']));
			fclose($fp);
			$this->ACT_layer_msg("模板(".$_POST['tp_path'].")更新成功！",9,$_SERVER['HTTP_REFERER'],2,1);	
		}else{	
			$this->ACT_layer_msg("模板不能为空！",8,$_SERVER['HTTP_REFERER']);
		}
	}
			
}