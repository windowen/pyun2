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
class index_controller extends common{
	function public_action(){
		if($this->config['sy_redeem_web']=="2"){
			header("location:".Url('error'));
		}
	}
	function index_action(){
		$this->public_action();
		$statisM	=	$this->MODEL("statis");
		$redeemM	=	$this->MODEL("redeem");
		
		$statis	=	$statisM->getInfo($this->uid,array('usertype'=>$this->usertype,'field'=>'`integral`'));
		$this->yunset("statis",$statis);
		
		$lipin	=	$redeemM->getChangeList(array('status'=>1,'orderby'=>'id,desc','limit'=>10),array("field"=>"uid,username,name,ctime,integral,gid"));
		$this->yunset("lipin",$lipin['list']);
		
		$this->seo("redeem");
		$this->yun_tpl(array('index'));
	}
	function list_action(){
		$CacheM		=	$this->MODEL('cache');
        $CacheList	=	$CacheM->GetCache(array('redeem'));
		$this->yunset($CacheList);
		$integlist	=	array('1-300','300-1000','1000-5000','5000-10000','10000-50000','50000-100000','100000-500000');
		$this->yunset('integlist',$integlist);
		
		if($_GET['intinfo']){
			$intinfo	=	str_replace('_','-',$_GET['intinfo']);
			$this->yunset('intinfo',$intinfo);
		}
		$this->public_action();
		$this->seo("redeem");
		$this->yun_tpl(array('list'));
	}
	function show_action(){
		$this->public_action();
		$redeemM	=	$this->MODEL("redeem");
		$statisM	=	$this->MODEL("statis");
		
		$where['gid']		=	(int)$_GET['id'];
		$where['status']	=	1;
		//分页链接
		$urlarr['c'] 		= 	$_GET['c']; 
		$urlarr['id'] 		= 	(int)$_GET['id']; 
		$urlarr['page'] 	= 	"{{page}}";
		$pageurl			=	Url('redeem',$urlarr);
		
		//提取分页
		$pageM				=	$this  -> MODEL('page');
		$pages				=	$pageM -> pageList('change',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
			
			$where['orderby']	=	'id,desc';
			
		    $where['limit']		=	$pages['limit'];
			
		    $List				=	$redeemM->getChangeList($where);
			
			$this->yunset("jilu",$List['list']);
		}
		$row	=	$redeemM->getInfo(array("id"=>(int)$_GET['id']));
		if($row['id']==''){
			$this->ACT_msg(Url('redeem'),"没有找到相关商品！");
		}
		$this->yunset("row",$row);
		$info	=	$statisM->getInfo($this->uid,array("usertype"=>$this->usertype,"field"=>"`integral`"));
		$this->yunset("info",$info);
		$this->seo("redeem");
		$this->yun_tpl(array('show'));
	}
	function dh_action(){
		$CacheM		=	$this->MODEL('cache');
		$CacheList	=	$CacheM->GetCache(array('city'));
		$this->yunset($CacheList);
		
		$this->public_action();
		
		$userinfoM		=	$this->MODEL("userinfo");
		$redeemM	=	$this->MODEL("redeem");
		$statisM	=	$this->MODEL("statis");
		
		if(!$this->uid && !$this->username){
		     $this->ACT_msg($_SERVER['HTTP_REFERER'],"您还没有登录，请先登录！");
		}
		
		$info		=	$statisM->getInfo($this->uid,array("usertype"=>$this->usertype,"field"=>"`integral`"));
		$gift		=	$redeemM->getInfo(array("id"=>intval($_GET['id'])));
		
		$integral	=	$gift['integral']*intval($_GET['num']);
		
		if($info['integral']<$integral){
			
			$this->ACT_msg($_SERVER['HTTP_REFERER'],"您的".$this->config['integral_pricename']."是".$info['integral'].$this->config['integral_priceunit']."，不够兑换商品！");
			
		}
		$link		=	$userinfoM->getUserInfo(array("uid"=>(int)$this->uid),array('usertype'=>$this->usertype));
		
		if($this->usertype==1){
			
			$uinfo['linkman']		=	$link['name'];
			
			if($link['telphone']){
				$uinfo['moblie']	=	$link['telphone'];
			}elseif($link['telhome']){
				$uinfo['moblie']	=	$link['telhome'];
			}
		}elseif($this->usertype==2){
			
			$uinfo['linkman']		=	$link['linkman'];
			
			if($link['linktel']){
				$uinfo['moblie']	=	$link['linktel'];
			}elseif($link['linkphone']){
				$uinfo['moblie']	=	$link['linkphone'];
			}
		}elseif($this->usertype==3){
			
			$uinfo['linkman']		=	$link['realname'];
			
			if($link['moblie']){
				$uinfo['moblie']	=	$link['moblie'];
			}elseif($link['phone']){
				$uinfo['moblie']	=	$link['phone'];
			}
		}
		$this->yunset("uinfo",$uinfo);
		
		$row	=	$redeemM->getInfo(array("id"=>(int)$_GET['id']),array('field'=>'`id`,`name`,`pic`,`integral`'));
		$this->yunset("row",$row);
		
		$change = 	$redeemM->getChangeInfo(array('uid'=>$this->uid),array('orderby'=>'id','desc'=>'desc','field'=>'body'));
		
		$body	=	explode(' ',$change['body']);
		
		if(count($body)>2){
			
			$change['province']		=	str_replace("收货地址：",'', $body['0']);
			$change['city']			=	$body['1'];
			$change['threecity']	=	$body['2'];
			$change['address']		=	$body['3'];
			$change['cityname']		=	str_replace("收货地址：",'', $body['0']).' '.$body['1'].' '.$body['2'];
			$change['body']			=	$change['cityname'].' '.$body['3'];
			$this->yunset('change',$change);
		}
		
		$this->yunset("title","兑换确认 - ".$this->config['sy_webname']);
		$this->yun_tpl(array('dh_show'));
	}
	function savedh_action(){
		$CacheM		=	$this->MODEL('cache');
		$CacheList	=	$CacheM->GetCache(array('city'));
		$post=array(
			
		);
		$data=array(
			'uid'			=>	$this->uid,
			'username'		=>	$this->username,
			'usertype'		=>	$this->usertype,
			'linkman'		=>	$_POST['linkman'],
			'linktel'		=>	$_POST['linktel'],
			'num'			=>	$_POST['num'],
			
			'id'			=>	(int)$_POST['id'],
			'provinceid'	=>	$CacheList['city_name'][$_POST['provinceid']],
			'cityid'		=>	$CacheList['city_name'][$_POST['cityid']],
			'three_cityid'	=>	$CacheList['city_name'][$_POST['three_cityid']],
			'address'		=>	$_POST['address'],
			'other'			=>	$_POST['other'],
			'password'		=>	$_POST['password'],
			'utype'			=>	'pc'
		);
		$redeemM	=	$this->MODEL("redeem");
		$return		=	$redeemM->AddChange($data);
		if($return['url']){
			$this->ACT_layer_msg($return['msg'],$return['errcode'],$return['url']);
		}else{
			$this->ACT_layer_msg($return['msg'],$return['errcode']);
		}
	}
}
?>