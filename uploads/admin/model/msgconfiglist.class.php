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
class msgconfiglist_controller extends adminCommon{

	function set_search(){
	
		$search_list[]	=	array("param"=>"state","name"=>'发送状态',"value"=>array("1"=>"发送成功","2"=>"发送失败"));
		
		$lo_time		=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		
		$search_list[]	=	array("param"=>"time","name"=>'时间',"value"=>$lo_time);

		$ports			=	array('1' => '网页', '2' => 'WAP',  '5' => '后台');
		$search_list[]	=	array("param"=>"port","name"=>'发送端口',"value" => $ports);
		
		$this->yunset(array('search_list' => $search_list, 'ports' => $ports));
		
	}
	
	//短信发送列表
	function index_action()
	{

		$this->set_search();		

	    include(CONFIG_PATH."db.data.php");
		
	    $this->yunset('msgreturn',$arr_data['msgreturn']);
		
		$mobliemsgM	=	$this->MODEL('mobliemsg');
		
		if(trim($_GET['keyword'])){
			
			$_GET['keyword']	=	trim($_GET['keyword']);

			if ($_GET['type']=='1'){
				
				$where['moblie']	=	array('like',$_GET['keyword']);
				
			}else if($_GET['type']=='2'){
				
				if($_GET['keyword'] == '系统'){

					$where['cuid']	=	0;

				}else{

					$mwhere=array(
						'1'=>array(
							'name'		=>	array('like',$_GET['keyword']),
							'limit'		=>	'50'
						),
						'2'=>array(
							'name'		=>	array('like',$_GET['keyword']),
							'limit'		=>	'50'
						),
						'3'=>array(
							'realname'	=>	array('like',$_GET['keyword']),
							'limit'		=>	'50'
						),
						'4'=>array(
							'name'		=>	array('like',$_GET['keyword']),
							'limit'		=>	'50'
						)
					);
					$userinfoM		=	$this		->	MODEL('userinfo');
					$muids			=	$userinfoM	->	getUidsByWhere($mwhere);
					$where['PHPYUNBTWSTART_A']  =   '';
					$where['cuid'][] =   array('in',  pylode(',', $muids));
					$where['cuid'][] =   array('<>', '0');
					$where['PHPYUNBTWEND_A']    =   '';
				}
				 
			}else if($_GET['type']=='3'){
				
				if($_GET['keyword']=='系统'){

					$where['uid']	=	0;

				}else{
					$mwhere=array(
						'1'=>array(
							'name'		=>	array('like',$_GET['keyword']),
							'limit'		=>	'50'
						),
						'2'=>array(
							'name'		=>	array('like',$_GET['keyword']),
							'limit'		=>	'50'
						),
						'3'=>array(
							'realname'	=>	array('like',$_GET['keyword']),
							'limit'		=>	'50'
						),
						'4'=>array(
							'name'		=>	array('like',$_GET['keyword']),
							'limit'		=>	'50'
						)
					);
					$userinfoM		=	$this		->	MODEL('userinfo');
					$muids			=	$userinfoM	->	getUidsByWhere($mwhere);
					$where['PHPYUNBTWSTART_A']  =   '';
					$where['cuid'][] =   array('in',  pylode(',', $muids));
					$where['cuid'][] =   array('<>', '0');
					$where['PHPYUNBTWEND_A']    =   '';
				}
				 
			}else if($_GET['type']=='4'){
				
				$where['content']	=	array('like',$_GET['keyword']);
				 
			}
			
			$urlarr['type']		=	$_GET['type'];
			
			$urlarr['keyword']	=	$_GET['keyword'];
		}

		if(($_GET['date']) && $_GET['time']<1){
			
			$times					=	@explode('~',$_GET['date']);
			
			$where['ctime'][0]		=	array('>=',strtotime($times[0]." 00:00:00"));
			
			$where['ctime'][1]		=	array('<',strtotime($times[1]." 23:59:59"));
			
			$urlarr['date']			=	$_GET['date'];
		}
		
		if($_GET['state']){

			$where['state']		=	$_GET['state'];
			$urlarr['state']	=	$_GET['state'];
		}		

		if($_GET['time']){
			
			if($_GET['time']=='1'){
				
				$where['ctime'][2]		=	array('>',strtotime(date("Y-m-d 00:00:00")));
			}else{
				
				$where['ctime'][3]		=	array('>',strtotime('-'.$_GET['time'].'day'));
			}
			unset($_GET['sdate']);
			unset($_GET['edate']); 
			
			$urlarr['time']	=	$_GET['time'];
		}

		if($_GET['port']){

			$where['port']	=	$_GET['port'];
			$urlarr['port']	=	$_GET['port'];
		}	
		
		if($_GET['order']=="asc"){
			
			$this->yunset("order","desc");
			
		}else{
			
			$this->yunset("order","asc");
			
		}
		
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c']; 
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('moblie_msg',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
		    //limit order 只有在列表查询时才需要
			
			if($_GET['order']){
			
				if($_GET['order']=="desc"){
					
					$where['orderby']	=	$_GET['t'].',desc';
					
				}else{
					
					$where['orderby']	=	$_GET['t'].',asc';
					
				}
				
			}else{
				
				$where['orderby']		=	'id,desc';
		
			}
			
			$where['limit']			=	$pages['limit'];
			
			$urlarr['order']		=	$_GET['order'];
				
			$urlarr['t']			=	$_GET['t'];
			
		    $List	=	$mobliemsgM -> getList($where);
			$this->yunset("rows",$List['list']);
		}
		
		
		
		$this->yunset("get_type", $_GET);
		
		$this->yuntpl(array('admin/admin_mobliemsg'));
		
	}
	function del_action(){

		$mobliemsgM	=	$this->MODEL('mobliemsg');
		
		if(is_array($_POST['del'])){
			
			$where['id']	=	array('in',pylode(',',$_POST['del']));
			
			$del			=	$mobliemsgM->delMoblieMsg($where,array('type'=>'all'));
			
			$layer_type		=	1;
			
			$delid			=	pylode(',',$_POST['del']);
			
		}else{
			
			$this->check_token();
			
			$where['id']	=	(int)$_GET['id'];
			
			$del			=	$mobliemsgM->delMoblieMsg($where,array('type'=>'one'));
			
			$layer_type		=	0;
			
			$delid			=	(int)$_GET['id'];
		}
	
		if(!$delid){
			
			$this->layer_msg('请选择要删除的内容！',8);
		
		}
		
		$del?$this->layer_msg('短信记录(ID:'.$delid.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,$layer_type,$_SERVER['HTTP_REFERER']);
	
	}	
	//失败短信重发
	function repeat_action(){
		
		
		if($_POST['id']){

			$mobliemsgM	=	$this->MODEL('mobliemsg');

			$msg		=	$mobliemsgM -> repeat($_POST['id']);

			echo $msg;
		}else{
			echo "请选择需要重发的短信！";
		}
		
		
	}
}

?>