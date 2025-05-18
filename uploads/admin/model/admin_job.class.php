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
class admin_job_controller extends adminCommon{
	function index_action(){
		$categoryM				=	$this -> MODEL('category');
		$whereData['keyid'] 	=	'0';
		$whereData['orderby'] 	=	'sort,asc';
		$position				=	$categoryM -> getJobClassList($whereData);
		$this	->	yunset("position",$position);
		$this	->	yuntpl(array('admin/admin_job'));
	}
	function classadd_action(){
	    
		$categoryM				=	$this -> MODEL('category');
		$position				=	$categoryM -> getJobClassList(array('keyid'=>'0','orderby'=>'sort,asc'));
		
		if($_GET['id']){
			$info		=	$categoryM -> getJobClass(array('id'=>$_GET['id']));
			$job		=	$categoryM -> getJobClass(array('id'=>$info['keyid']));
			$class2		=	$categoryM -> getJobClassList(array('keyid'=>$job['keyid'],'orderby'=>'sort,asc'));

			$this	->	yunset("type","three");
			$this	->	yunset("info",$info);
			$this	->	yunset("class2",$class2);
			$this	->	yunset("job",$job);
		}elseif($_GET['tid']){
			$info		=	$categoryM -> getJobClass(array('id'=>$_GET['tid']));
			
			$this	->	yunset("type","two");
			$this	->	yunset("info",$info);
		}

		$this	->	yunset("position",$position);
		$this	->	yunset($this->MODEL('cache')->GetCache(array('job')));
		$this	->	yuntpl(array('admin/admin_job_classadd'));
	}
	
	//添加职位
	function save_action(){
		$_POST	=	$this -> post_trim($_POST);
		if($_POST['submit']){
			$data	=	array(
				'id'		=>	intval($_POST['id']),
				'nid'		=>	intval($_POST['nid']),
				'name'		=>	$_POST['position'],
				'e_name'	=>	$_POST['e_name'],
				'keyid'		=>	intval($_POST['keyid']),
				'sort'		=>	$_POST['sort'],
				'content'	=>	$_POST['content']
			);

			$categoryM		=	$this -> MODEL('category');
			
			$return			=	$categoryM -> addJobClass($data);


			$this	->	ACT_layer_msg($return['msg'],$return['errcode'],$return['url'],2,$return['type']);

		}
	}
	//职位管理
	function up_action(){
		$categoryM	=	$this -> MODEL('category');
		//查询子类别
		if((int)$_GET['id']){
			$id			=	(int)$_GET['id'];
			$onejob		=	$categoryM	->	getJobClass(array('id'=>$_GET['id']));
			$return		=	$categoryM	->	getJobClassList(array('keyid'=>$_GET['id'],'orderby'=>'sort,asc'),'*',array('type'=>'oneall'));
			$twojob		=	$return['twojob'];
			$threejob	=	$return['threejob'];

			$this->yunset("id",$id);
			$this->yunset("onejob",$onejob);
			$this->yunset("twojob",$twojob);
			$this->yunset("threejob",$threejob);
		}
		$position		=	$categoryM	->	getJobClassList(array('keyid'=>'0'));
		$this->yunset("position",$position);
		$this->yuntpl(array('admin/admin_job'));
	}
	
	//删除
	function del_action(){
		$categoryM				=	$this	->	MODEL('category');
		if((int)$_GET['delid']){
			$this	->	check_token();
			$whereData['id']	=	$_GET['delid'];
			$data['type']		=	'one';	
		}else if($_POST['del']){ //批量删除
			$whereData['id']	=	array('in',pylode(',',$_POST['del']));
			$data['type']		=	'all';	
		}

		$return	=	$categoryM	->	delJobClass($whereData,$data);
		$this   ->  layer_msg( $return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1);
	}
	function move_action(){
		$keyid				=	$_GET['keyid'];
		$nid				=	$_GET['nid'];
		$pid				=	$_GET['pid'];
		$categoryM			=	$this	->	MODEL('category');
		
		$addData['keyid']	=	$keyid	?	$keyid	:	$nid;
		$whereData['id']	=	$pid;
		
		$return				=	$categoryM	->	upJobClass($addData,$whereData);
		$msg				=	$return	?	"职位类别(ID:".$pid.")移动成功！"	:	"移动失败！";
		$cod				=	$return	?	9	:	8;
		$this				->	ACT_layer_msg($msg,$cod,$_SERVER['HTTP_REFERER'],2,1);
 	}
	function ajax_action(){
		$categoryM			=	$this	->	MODEL('category');
		$whereData['id']	=	$_POST['id'];
		$addData['sort']	=	$_POST['sort'];
		$addData['name']	=	$_POST['name'];
		$addData['e_name']	=	$_POST['e_name'];
		
		$categoryM	->	upJobClass($addData,$whereData);
		echo '1';
		die;
	}
	function get_class_action(){
		if($_POST['nid']){
			$categoryM		=	$this	->	MODEL('category');
			$typeclass		=	$categoryM	->	getJobClassList(array('keyid'=>intval($_POST['nid']),'orderby'=>'sort,asc'),'id,name,keyid');

			echo json_encode($typeclass);die;
			
		}
	}
	function ajaxpinyin_action(){
		$where['e_name'][]	=	array('isnull');
		$where['e_name'][]	=	array('=','','OR');
		$where['orderby']	=	'sort,desc';
		$data['field']	=	'`id`,`name`,`e_name`';
		$data['type']	=	'job';
		$data['post']	=	$_POST;
		$categoryM	=	$this -> MODEL('category');
		$return		=	$categoryM -> setPinYin($where,$data);
		echo json_encode($return);die;
	}
	function ajaxchachong_action(){
		$where['e_name']	=	array('<>','');
		$where['groupby']	=	'e_name';
		$where['having']	=	array('enum'=>array('>','1'));
		$data	=	array(
			'field'	=>	'*,count(*) as enum',
			'page'	=>	$_POST['page'],
			'type'	=>	'job'
		);
		$categoryM	=	$this -> MODEL('category');
		$list		=	$categoryM -> setChaChong($where,$data);
		echo json_encode($list);die;
	}
	function clearpinyin_action(){
		
		$categoryM	=	$this -> MODEL('category');
		
		$categoryM -> clearPinYin('job_class');
		
	}

}

?>