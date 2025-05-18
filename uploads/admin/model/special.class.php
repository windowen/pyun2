<?php
/*
 * Created on 2012
 * Link for shyflc@qq.com
 * This System Powered by PHPYUN.com
 */
class special_controller extends adminCommon{
	function index_action(){
		$specialM	=	$this->MODEL("special");
		if(trim($_GET['keyword'])){
			$where['title']	=	array('like',trim($_GET['keyword']));
		}

		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');

		//排序
		if($_GET['order']){

			$where['orderby'] = $_GET['t'].','.$_GET['order'];
			$urlarr['order']  =	$_GET['order'];
			$urlarr['t']	  =	$_GET['t'];

		}else{
			$where['orderby']		=	'id,desc';
		}

		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('special',$where,$pageurl,$_GET['page']);

		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

		    $where['limit']		=	$pages['limit'];

		    $List		=	$specialM->getSpecialList($where,array('utype'=>'admin'));
			$this->yunset("rows",$List['list']);
		}

		$this->yuntpl(array('admin/admin_special'));
	}

    function addlist_action(){
		$specialM		=	$this->MODEL("special");
		$jobM			=	$this->MODEL("job");
		$companyM		=	$this->MODEL("company");

		$special 		= 	$specialM->getSpecialOne(array('id'=>$_GET['sid']),array('field'=>'`title`'));
		$this			->	yunset("special",$special);
        $companylsit	=	$jobM->getList(array('state'=>1,'groupby'=>'uid'),array('field'=>'`uid`'));

        foreach($companylsit['list'] as $vv){
            $companyuids[]		=	$vv['uid'];
        }
        $where['uid']			=	array('in', pylode(',', $companyuids));
		if(trim($_GET['keyword'])){
			$where['name']		=	array('like',trim($_GET['keyword']));
			$urlarr['keyword']	=	$_GET['keyword'];
		}


		//排序
		if($_GET['order']){

			$where['orderby'] = $_GET['t'].','.$_GET['order'];
			$urlarr['order']  =	$_GET['order'];
			$urlarr['t']	  =	$_GET['t'];

		}else{
			$where['orderby']	=	'uid,desc';
		}

		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$urlarr['sid']	=	$_GET['sid'];
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');

		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('company',$where,$pageurl,$_GET['page']);

		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

			if($_GET['order']){
				$where['orderby']	=	$_GET['t'].','.$_GET['order'];

			}else{
				$where['orderby']	=	'uid,desc';

			}
		    $where['limit']			=	$pages['limit'];

			$urlarr['order']		=	$_GET['order'];

			$urlarr['t']			=	$_GET['t'];

		    $List					=	$companyM->getList($where,array('utype'=>'special','sid'=>$_GET['sid']));
			$this->yunset("rows",$List['list']);
		}

        $this->yunset("sid", $_GET['sid']);
		$this->yunset("get_type", $_GET);
		$this->yuntpl(array('admin/admin_special_company'));
    }

	function add_action(){
		$specialM	=	$this->MODEL("special");
		$ratingM	=	$this->MODEL("rating");

		if($_GET['id']){
			$row			=	$specialM->getSpecialOne(array('id'=>$_GET['id']));
			$row['rating']	=	@explode(',',$row['rating']);
			$this->yunset("row",$row);
		}

		$qy_rows	=	$ratingM->getList(array('category'=>1,'orderby'=>'sort,desc'));
		$publicdir 	= 	"../app/template/".$this->config['style']."/special/";
		$filesnames = 	@scandir($publicdir);
		if(is_array($filesnames)){
			foreach($filesnames as $key=>$value){

				if($value!='.' && $value!='..' ){

					 $typearr = explode('.',$hostdir.$value);

					 if(in_array(end($typearr),array('htm'))) {

						 if($value!="index.htm"){

							$file[]  =  $value;
						 }
					 }
				 }
			}
		}

		$this->yunset("file",$file);
		$this->yunset("qy_rows",$qy_rows);
		$this->yuntpl(array('admin/admin_special_add'));
	}
	function save_action(){

		$specialM	=	$this->MODEL("special");
	    $id			=	(int)$_POST['id'];
		$data['title']		=	$_POST['title'];
		$data['tpl']		=	$_POST['tpl'];
		$data['display']	=	(int)$_POST['display'];
		$data['integral']	=	(int)$_POST['integral'];
		$data['com_bm']		=	(int)$_POST['com_bm'];
	    $data['sort']		=	(int)$_POST['sort'];
	    $data['limit']		=	(int)$_POST['limit'];
	    $data['etime']		=	strtotime($_POST['etime']);
	    $data['ctime']		=	time();
	    $data['intro']		=	str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'','',''),$_POST["intro"]);

		if($_POST['rating']	&&	is_array($_POST['rating'])){
	        $data['rating']	=	implode(',',$_POST['rating']);
	    }else{
	        $data['rating']	=	'';
	    }
		if(!empty($_FILES)){
			if($_FILES['sl']['tmp_name']){
				$upArrSl    =  array(
					'file'  	=>  $_FILES['sl'],
					'dir'   	=>  'special',

				);
			}
	 		if($_FILES['bg']['tmp_name']){
				$upArrBg    =  array(
					'file'  	=>  $_FILES['bg'],
					'dir'   	=>  'special',

				);
			}
			//缩率图参数

			$uploadM  =  $this->MODEL('upload');
			if(isset($upArrSl)){

				$picSl      =  $uploadM->newUpload($upArrSl);//缩略图

			}
			if(isset($upArrBg)){

				$picBg      =  $uploadM->newUpload($upArrBg);//背景图

			}
			if ( (   isset($picSl) && !empty($picSl['msg'])  )
				 || (   isset($picBg) &&  !empty($picBg['msg'])    )
				){

				$msg	=	$picSl['msg'] ? $picSl['msg'] : $picBg['msg'];

				$this	->	ACT_layer_msg($msg,8);

			}else{

				if (!empty($picSl['picurl'])){

					$data['pic'] 		=  	$picSl['picurl'];
				}
				if (!empty($picBg['picurl'])){

					$data['background']	=  	$picBg['picurl'];
				}
			}
	 	}

		if(!$id){
			$nid	=	$specialM->addSpecial($data);
	        $name	=	"专题招聘（ID：".$nid."）添加";

	    }else{
			$nid	=	$specialM->upSpecial(array('id'=>$id),$data);
	        $name	=	"专题招聘（ID：".$id."）更新";
	    }
		$nid?$this->ACT_layer_msg($name."成功！",9,"index.php?m=special",2,1):$this->ACT_layer_msg($name."失败！",8,"index.php?m=special");
	}
	function com_action(){
		$specialM	=	$this->MODEL("special");

		$where['sid']	=	(int)$_GET['id'];

		//排序
		if($_GET['order']){

			$where['orderby'] = $_GET['t'].','.$_GET['order'];
			$urlarr['order']  =	$_GET['order'];
			$urlarr['t']	  =	$_GET['t'];

		}else{
			$where['orderby']		=	array('status,asc','uid,desc');
		}

		// 分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$urlarr['id']	=	$_GET['id'];
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');

		// 提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('special_com',$where,$pageurl,$_GET['page']);

		// 分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

		    $where['limit']			=	$pages['limit'];

		    $List					=	$specialM->getSpecialComList($where,array('utype'=>'admin'));
			$this->yunset("rows",$List['list']);
		}

		$special	= 	$specialM->getSpecialOne(array('id'=>(int)$_GET['id']),array('field'=>'`title`,`limit`'));
        $applynum 	=	$specialM->getSpecialComNum(array("sid"=>(int)$_GET['id'],'status'=>'1'));
		if($special['limit']>$applynum){
			$this->yunset("applyman",'1');
		}
		$this->yunset("special",$special);
		$this->yunset("sid",$_GET['id']);
		$this->yuntpl(array('admin/admin_special_com'));
	}
	function statuscom_action(){
		$specialM	=	$this->MODEL("special");
		$IntegralM	=	$this->MODEL('integral');

		$pid		=	$_POST['pid'];
		$status		=	(int)$_POST['status'];
		$statusbody	=	trim($_POST['statusbody']);

		if($status=='2'){
			$iWhere['id']		=	array('in',$pid);
			$iWhere['status']	=	array('<>','2');
			$idata['field']		=	"`uid`,`integral`";
			$rows				=	$specialM->getSpecialComList($iWhere,$idata);
		}

		$upWhere['id']			=	array('in',$pid);
		$upWhere['status']		=	array('<>','2');
		$upData['status']		=	$status;
		$upData['statusbody']	=	$statusbody;
		$id						=	$specialM->upSpecialCom($upWhere,$upData);

		if($id&&is_array($rows['list'])){
			foreach($rows['list'] as $val){
				if($val['integral']>0){
					$IntegralM->company_invtal($val['uid'],2,$val['integral'],true,"专题招聘未通过审核，退还".$this->config['integral_pricename'],true,2,'integral');
				}
			}
		}

		$lWhere['id']		=	array('in',$pid);
		$ldata['field']		=	"`sid`,`uid`";
		$list				=	$specialM->getSpecialComList($lWhere,$ldata);

		if($list['list']){
			/* 消息前缀 */
			$sysmsgM			=	$this->MODEL('sysmsg');

			$tagName  			=	'专题报名';

			foreach($list['list'] as $v){

				 $sid  			=	$v['sid'];

			}
			$special			= 	$specialM->getSpecialOne(array('id'=>$sid),array('field'=>'`title`'));

			//发送企业
			foreach($list['list'] as $v){

				 $uids[]  =  $v['uid'];

				/* 处理审核信息 */
				if ($_POST['status'] == 2){

					$statusInfo  =  '您参与的专题'.$special['title'].':报名审核未通过';

					if($_POST['statusbody']){

						$statusInfo  .=  ' , 原因：'.$_POST['statusbody'];

					}

					$msg[$v['uid']]  =  $statusInfo;

				}elseif($_POST['status'] == 1){

					$msg[$v['uid']]  =   '您参与的专题'.$special['title'].':报名已审核通过';

				}
			}

			//发送系统通知
			$sysmsgM -> addInfo(array('uid'=>$uids,'usertype'=>2, 'content'=>$msg));
		}
		$id?$this->ACT_layer_msg("操作成功！",9,$_SERVER['HTTP_REFERER']):$this->ACT_layer_msg("操作失败！",8,$_SERVER['HTTP_REFERER']);
	}
	function getinfo_action(){
		$specialM		=	$this->MODEL("special");

		$where['id']	=	intval($_POST['id']);

		$data['field']	=	'`statusbody`';

		$row			=	$specialM->getSpecialComOne($where,$data);
		echo $row['statusbody'];die;
	}
	function delcom_action(){
		$specialM		=	$this->MODEL("special");

		$this->check_token();

	    if($_GET['del']||$_GET['id']){

    		if(is_array($_GET['del'])){
    			$layer_type	=	1;
				$del		=	pylode(',',$_GET['del']);
	    	}else{
	    		$layer_type	=	0;
	    		$del		=	$_GET['id'];
	    	}
			$specialM->delSpecialCom(array('id'=>array('in',$del)),array('type'=>'all'));

			$this->layer_msg("公司申请(ID:".$del.")删除成功！",9,$layer_type,$_SERVER['HTTP_REFERER']);
    	}else{
			$this->layer_msg("请选择您要删除的信息！",8,1);
    	}
	}
	function del_action(){
		$specialM		=	$this->MODEL("special");

		$_GET['id']		=	(int)$_GET['id'];

		if($_GET['del']||$_GET['id']){
			if(is_array($_GET['del'])){
				$layer_type	=	1;
				$del		=	pylode(',',$_GET['del']);
			}else{
				$layer_type	=	0;
				$del		=	$_GET['id'];
			}
			$specialM->delSpecial(array('id'=>array('in',$del)),array('type'=>'all'));

			$this->layer_msg("专题(ID:".$del.")删除成功！",9,$layer_type,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg("请选择您要删除的信息！",8,1);
		}
	}

    function savespecial_action(){
		$UserInfoM		=	$this->MODEL('userinfo');
		$IntegralM		=	$this->MODEL('integral');
		$SpecialM		=	$this->MODEL('special');
		$JobM			=	$this->MODEL('job');

		$id				=	intval($_POST['sid']);
		$uid			=	intval($_POST['uid']);

		$info			=	$SpecialM->getSpecialOne(array("id"=>$id,"display"=>1));
		$isapply		=	$SpecialM->getSpecialComNum(array("uid"=>$uid,"sid"=>$id));
		$applynum 		=	$SpecialM->getSpecialComNum(array("sid"=>$id,'status'=>'1'));

		if($info['com_bm']!='1'){
		   echo 1;die;
		}
		if($isapply){
			echo 2;die;
		}
		$jobnum		=	$JobM->getJobNum(array("uid"=>$uid,"state"=>'1',"edate"=>array('>',time()),"sdate"=>array('<',time())));

		if($info['limit']<=$applynum){
			echo 3;die;
		}else{

			$rows		=	$SpecialM->addSpecialCom(array("sid"=>$id,"uid"=>$uid,'integral'=>$info['integral'],'status'=>'1','time'=>time()));

			if($rows){
				echo 5;die;
			}else{
				echo 6;die;
			}
		}
    }

    function recommend_action(){
		$specialM	=	$this->MODEL('special');
		$logM		=	$this->MODEL('log');

		if($_GET['type']=="rec_display"){
			$data['display']	=	$_GET['rec'];
			$where['id']		=	$_GET['id'];

			$nid	=	$specialM->upSpecial($where,$data);
			$logM	->	addAdminLog("专题名称(ID:".$_GET['id'].")");
		}

		echo $nid?1:0;die;

	}

	function ajaxsort_action(){
		if($_POST['id']){
			$specialM	=	$this->MODEL('special');
			if (!empty($_POST['sort'])){
				$uparr['sort']  =  intval($_POST['sort']);
			}
			$specialM->upSpecialCom(array('id'=>$_POST['id']),$uparr);
			echo '1';die;
		}
	}

	//排序设置
	function setOrder_action(){

		$post = $_POST;

		$specialM	=	$this->MODEL('special');

		$where = array('id' => $post['id']);
		$data  = array('sort' => $post['sort']);
		$res = $specialM->upSpecial($where,$data);
		echo 1;

	}


}
?>