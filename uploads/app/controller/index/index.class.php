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
	function index_action(){ 
		
		//是否加载区域分站
		if(!$this->config['did'] && $this->config['sy_gotocity']=='1' && !$_COOKIE['gotocity']){


			go_to_city($this->config);//自动跳转分站

		}
		
		if($this->config['sy_jobdir']!=""){

			$jobclassurl	=	$this->config['sy_weburl']."/job/index.php?c=search&";

		}else{

			$jobclassurl	=	$this->config['sy_weburl']."/index.php?m=job&c=search&";

		}

		$this->yunset("jobclassurl",$jobclassurl);
	
		$where['status']	=	1;
		$where['r_status']	=	1;
		$where['rec_resume']=	1;
		if($this->config['did']>0){
			$where['did']	=	$this->config['did'];
		}else{
			$where['PHPYUNBTWSTART']=	'';
			$where['did'][]	=	array('isnull');
			$where['did'][]	=	array('=','0','OR');
			$where['PHPYUNBTWEND']	=	'';
		}
		$resumeM	=	$this->MODEL('resume');
		$topnum 	= 	$resumeM->getExpectNum($where);

		$this->yunset("topnum",$topnum);
		
		if($this->config["did"]){

			$this->seo("index",$this->config['sy_webtitle'],$this->config['sy_webkeyword'],$this->config['sy_webmeta']);

			$CacheM					=				$this->MODEL('cache');

			$DomainList				=				$CacheM->GetCache(array('domain'));

			$site_domain			=				$DomainList['site_domain'];

			if(is_array($site_domain)){

				foreach($site_domain as $d){

					if($d['id']==$this->config["did"]){

						$domain['tpl']	=	$d['tpl'];

					}

				}

			}

			if($domain['tpl']!=""){

				$tpl	=	@explode(".",$domain['tpl']);

				$this->yun_tpl(array($tpl[0]));

			}else{

				$this->yun_tpl(array('siteindex'));

			}

		}else{

			$this->seo("index");

			$this->yun_tpl(array('index'));

		}

	}
	function top_action(){

		$this->seo("top");

		$this->yun_tpl(array('top'));

	}
	function moblie_action(){

		$this->seo("moblie");

		$this->yun_tpl(array('moblie'));

	}
	function wap_action(){

		$this->seo("wap");

		$this->yun_tpl(array('wap'));

	}
	function weixin_action(){

		$this->seo("weixin");

		$this->yun_tpl(array('weixin'));

	}
	function site_action(){

		if($this->config["sy_web_site"]!="1"){

			$this->ACT_msg($_SERVER['HTTP_REFERER'], $msg = "暂未开启多站点模式！");

		}
		
		$siteinfo				=				getLocalCity();

		if($siteinfo){
			
	
			$site				=				1;
			if($siteinfo['mode']=='2'){
						
				$siteinfo['host']=$this -> config['sy_indexdomain'].'/'.$siteinfo['indexdir'].'/';
			}else{
				$siteinfo['host']='http://'.$siteinfo['host'];
			}
			$this->yunset("site",$site);

			$this->yunset("vurl",$siteinfo['host']);

			$this->yunset("siteinfo",$siteinfo);

		}

		$this->seo("index");

		$this->yun_tpl(array('site'));

	}
	function logout_action(){

		$this->logout();

	}
	//切换身份
	function changeusertype_action(){
      $memberM		=	$this -> MODEL('userinfo');
		
		$uid			=	$this->uid;
		
		if($this->usertype==2)
		{
			$changeType	=	1;
		}else{
			$changeType	=	2;
		}
		$res			=	$memberM->checkChangeApply($this->uid,$changeType);

		echo json_encode(array('msg'=>$res['msg'],'errcode'=>$res['errcode']));die;
    }
	
	function GetHits_action(){
    	if($_GET['id']){

			$articleM		=		$this->MODEL('article');

			$articleM->upBase(array('id'=>(int)$_GET['id']),array('hits'=>array('+',1)));
				
			$info			=		$articleM->getInfo(array('id'=>(int)$_GET['id']),array('field'=>'hits'));
			
			echo "document.write('".$info["hits"]."')";
			
		}
		
    }
	function get_action(){

		$descriptionM				=		$this->MODEL('description');
		
		session_start();

		if($_SESSION['auid']==""){

			$row					=		$descriptionM->getDes(array("id"=>(int)$_GET['id'],"is_nav"=>'1'));

		}else{

			$row					=		$descriptionM->getDes(array("id"=>(int)$_GET['id']));

		}
		
		$top						=		"";

		$footer						=		"";
		
		if(is_array($row)){

			if($row['top_tpl']==1){

				$top				=		APP_PATH."/app/template/".$this->config['style']."/header";

			}else if($row['top_tpl']==3){

				 $top				=		APP_PATH."/app/template/".$row['top_tpl_dir'];

			}
			if($row['footer_tpl']==1){

				$footer				=		APP_PATH."/app/template/".$this->config['style']."/footer";

			}else if($row['footer_tpl']==3){

				$footer				=		APP_PATH."/app/template/".$row['footer_tpl_dir'];

			}
			$seo['title']			=		$row['title'];

			$seo['keywords']		=		$row['keyword'];

			$seo['description']		=		$row['descs'];

			$this->yunset("seo",$seo);
			
			$this->yunset("name",$row['name']);

			$this->yunset("content",$row['content']);

			$this->header_desc($row['title'],$row['keyword'],$row['descs']);
			
			$make					=		APP_PATH."/app/template/".$this->config['style']."/make";

			$make_top				=		APP_PATH."/app/template/".$this->config['style']."/make_top";
			
			$this->yuntpl(array($make_top,$top,$make,$footer));

		}else{

			$this->ACT_msg($this->config['sy_weburl'],"请求页面不存在！");

		}

	}
   
	
    //广告记录
	function clickhits_action(){

		if((int)$_GET['id']){

			$adM					=			$this->MODEL("ad");

			$id						=			(int)$_GET['id'];

			$ad						=			$adM->getInfo(array('id'=>$id),array("field"=>"pic_src,id"));
			
			if(!empty($ad)){

				$ip					= 			fun_ip_get();

				if($this->config['sy_adclick']>0){
					
					$num			=			$adM->getAdClickNum(array('ip'=>$ip,'aid'=>$id,'addtime'=>array('>',strtotime('-'.$this->config['sy_adclick'].' hour'))));
					
					if($num>"0"){

						header('Location: '.$ad['pic_src']);

					}

				}

				$data['aid']		=			$id;

				$data['uid']		=			$this->uid;

				$data['ip']			=			$ip;

				$data['addtime']	=			time();
	
				$nid				=			$adM->addAdClick($data);

				if($nid){

					$adM->upInfo(array('id'=>$id),array('hits'=>array('+',1)));

				}
				if(!$ad['pic_src']){

					$ad['pic_src']	=			$this->config['sy_weburl'];

				}
				

				header('Location: '.$ad['pic_src']);

			}

		}

	}

	
}
?>