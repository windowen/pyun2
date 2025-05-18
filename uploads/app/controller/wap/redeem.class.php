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
class redeem_controller extends common{
	function index_action(){
		$statisM	=	$this->MODEL("statis");
		$redeemM	=	$this->MODEL("redeem");
		$userinfoM	=	$this->MODEL("userinfo");
		
		$lipin		=	$redeemM->getChangeList(array('status'=>1,'orderby'=>'id,desc','limit'=>10),array("field"=>"username,name,integral,gid"));
		$this->yunset("lipin",$lipin['list']);
		
		$statis		=	$statisM->getInfo($this->uid,array('usertype'=>$this->usertype,'field'=>'`integral`'));
		$this->yunset("statis",$statis);
		
		$user 		= 	$userinfoM->getUserInfo(array('uid'=>$this->uid),array('usertype'=>$this->usertype));
		
		if($this->usertype==1 || $this->usertype==3){
			
			$photo = $user['photo'];
			
		}else if($this->usertype==2 || $this->usertype==4){
			
			$photo = $user['logo'];
		}
		$this->yunset('photo',$photo);
		
		$this->yunset('headertitle',$this->config['integral_pricename'].'商城');
		$this->seo("redeem");
		$this->yuntpl(array('wap/redeem'));
	}
	function list_action(){
		$CacheM		=	$this->MODEL('cache');
        $CacheList	=	$CacheM->GetCache(array('redeem'));
		$this->yunset($CacheList);
		
		foreach($_GET as $k=>$v){
			if($k!=""){
				$searchurl[]	=	$k."=".$v;
			}
		}
		$searchurl	=	@implode("&",$searchurl);
		$this->yunset("searchurl",$searchurl);
		
		$this->seo('redeem');
		$this->yunset('headertitle',$this->config['integral_pricename'].'积分商城');
		$this->yuntpl(array('wap/redeemlist'));
	}
	function show_action(){
		
		$redeemM	=	$this->MODEL("redeem");
		$statisM	=	$this->MODEL("statis");
		
		$where['gid']		=	(int)$_GET['id'];
		$where['status']	=	1;
		//分页链接
		$urlarr['c'] 		= 	$_GET['c']; 
		$urlarr['a'] 		= 	$_GET['a']; 
		$urlarr['id'] 		= 	(int)$_GET['id']; 
		$urlarr['page'] 	= 	"{{page}}";
		$pageurl			=	Url('wap',$urlarr);
		
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
		$row		=	$redeemM->getInfo(array("id"=>(int)$_GET['id']));
		if($row['id']==''){
			$this->ACT_msg_wap(Url('redeem'),"没有找到相关商品！",2,5);
		}
		$this->yunset("row",$row);
		
		if($this->uid){
			$statis	=	$statisM->getInfo($this->uid,array("usertype"=>$this->usertype,"field"=>"`integral`"));
		}
		$this->yunset("statis",$statis);
		
		$this->seo('redeem');
		$this->yunset('headertitle',$row['name']);
		$this->yuntpl(array('wap/redeemshow'));
	}
	function dh_action(){
		if(!$this->uid && !$this->username){
		     $this->ACT_layer_msg('您还没有登录，请先登录！',8,$_SERVER['HTTP_REFERER']);
		}
		$userinfoM		=	$this->MODEL("userinfo");
		$redeemM	=	$this->MODEL("redeem");
		$statisM	=	$this->MODEL("statis");
		
		$CacheM		=	$this->MODEL('cache');
		$CacheList	=	$CacheM->GetCache(array('city','hy','com'));
		$this->yunset($CacheList);
		
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
		}elseif($this->usertype==4){
			
			$uinfo['linkman']		=	$link['linkman'];
			
			if($link['linktel']){
				$uinfo['moblie']	=	$link['linktel'];
			}elseif($link['linkphone']){
				$uinfo['moblie']	=	$link['linkphone'];
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
		
		/*显示实付款*/
		$integral	=	$row['integral']*(int)$_GET['num'];
		$this->yunset('integral',$integral);
		
		$this->yunset('headertitle','兑换确认 ');
		$this->seo('redeem');
		$this->yuntpl(array('wap/redeemdh'));
	}
	function savedh_action(){
		$CacheM		=	$this->MODEL('cache');
		$CacheList	=	$CacheM->GetCache(array('city'));
		$data=array(
			'uid'			=>	$this->uid,
			'username'		=>	$this->username,
			'usertype'		=>	$this->usertype,
			'linkman'		=>	$_POST['linkman'],
			'linktel'		=>	$_POST['linktel'],
			'num'			=>	$_POST['num'],
      'bodyt'			=>	$_POST['body'],
			
			'id'			=>	(int)$_POST['id'],
			'provinceid'	=>	$CacheList['city_name'][$_POST['provinceid']],
			'cityid'		=>	$CacheList['city_name'][$_POST['cityid']],
			'three_cityid'	=>	$CacheList['city_name'][$_POST['three_cityid']],
			'address'		=>	$_POST['address'],
			'other'			=>	$_POST['other'],
			'password'		=>	$_POST['password'],
			'utype'			=>	'wap'
		);
		$redeemM	=	$this->MODEL("redeem");
		$return		=	$redeemM->AddChange($data);
		echo json_encode($return);die;		
	}
}
?>