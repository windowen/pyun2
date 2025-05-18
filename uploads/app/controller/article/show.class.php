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
class show_controller extends article_controller{
	function index_action(){   
		$id			=	(int)$_GET['id'];
		$articleM	=	$this->MODEL('article');
		$nwhere['id']=	$id;
		if($this->config['did']>0){
			$nwhere['PHPYUNBTWSTART_A']	=	'';
			$nwhere['did'][]	=	array('=',$this->config['did']);
			$nwhere['did'][]	=	array('=','-1','OR');
			$nwhere['PHPYUNBTWEND_A']	=	'';
		}
		$news		=	$articleM->getInfo($nwhere,array('iscon'=>1));
		$info		=	$news;
		
		if($news['id']==''){
			$this->ACT_msg(Url('article'),"没有找到该文章！");
		}
		
		$nlwhere['id']	=	array('<',$id);
		$nlwhere['nid']	=	$news['nid'];
		if($this->config['did']>0){
			$nlwhere['PHPYUNBTWSTART_A']	=	'';
			$nlwhere['did'][]	=	array('=',$this->config['did']);
			$nlwhere['did'][]	=	array('=','-1','OR');
			$nlwhere['PHPYUNBTWEND_A']		=	'';
		}
		$nlwhere['orderby']	=	'id,desc';
		
		$news_last		=	$articleM->getInfo($nlwhere);
		if(!empty($news_last)){ 
			if($this->config['sy_news_rewrite']=="2"){
				
				$news_last["url"]	=	$this->config['sy_weburl']."/news/".date("Ymd",$news_last["datetime"])."/".$news_last['id'].".html";
				
			}else{
				
				$news_last["url"]	= 	Url('article',array('c'=>'show',"id"=>$news_last['id']),"1"); 
			}
		}
		$info["last"]	=	$news_last;
		
		$nnwhere['id']	=	array('>',$id);
		$nnwhere['nid']	=	$news['nid'];
		if($this->config['did']>0){
			$nnwhere['PHPYUNBTWSTART_A']	=	'';
			$nnwhere['did'][]	=	array('=',$this->config['did']);
			$nnwhere['did'][]	=	array('=','-1','OR');
			$nnwhere['PHPYUNBTWEND_A']		=	'';
		}
		$nnwhere['orderby']	=	'id,desc';
		
		$news_next		=	$articleM->getInfo($nnwhere);
		
		if(!empty($news_next)){
			if($this->config['sy_news_rewrite']=="2"){
				
				$news_next["url"]	=	$this->config['sy_weburl']."/news/".date("Ymd",$news_next["datetime"])."/".$news_next['id'].".html";
				
			}else{
				
				$news_next["url"]	= 	Url('article',array('c'=>'show',"id"=>$news_next['id']),"1"); 
			} 
		}
		$info["next"]	=	$news_next;
		
		$class			=	$articleM->getGroup(array("id"=>$news['nid']));
		//相关文章,按照关键字获取
		if($news["keyword"]!=""){
			//分割关键字
			$keyarr = @explode(",",$news["keyword"]);
			if(is_array($keyarr) && !empty($keyarr)){
				$where['PHPYUNBTWSTART_A']	=	'' ;
				foreach($keyarr as $key=>$value){
					$where['keyword'][]		=	array('like',$value,'OR') ;
				}
				$where['PHPYUNBTWEND_A']	=	'' ;
				$where['id']				=	array('<>',$id);
				$where['orderby']			=	'id,desc';
				if($this->config['did']>0){
					$where['PHPYUNBTWSTART_B']	=	'';
					$where['did'][]	=	array('=',$this->config['did']);
					$where['did'][]	=	array('=','-1','OR');
					$where['PHPYUNBTWEND_B']	=	'';
				}
				$where['limit']				=	6;
				
				$aboutlist	=	$articleM->getList($where);//相关文章
				$about		=	$aboutlist['list'];
				
				if(is_array($about)){
					foreach($about as $k=>$v){
						if($this->config['sy_news_rewrite']=="2"){
							
							$about[$k]["url"]	=	$this->config['sy_weburl']."/news/".date("Ymd",$v["datetime"])."/".$v['id'].".html";
							
						}else{
							$about[$k]["url"]	= 	Url('article',array('c'=>'show',"id"=>$v['id']),"1"); 
						}
					}
				}
			}
		}
		$info["like"]		=	$about;
		$info["news_class"]	=	$class['name'];
		$this->yunset("Info",$info);
		
		$data['news_title']		=	$news['title'];//新闻名称
		$data['news_keyword']	=	$news['keyword'];//描述 
		$data['news_class']		=	$class['name'];//新闻类别
		$description			=	$news['description']?$news['description']:$news['content'];
		$data['news_desc']		=	$this->GET_content_desc($description);//描述 
		$this->data				=	$data;
		$this->seo("news_article");
		
		$this->news_tpl('show');
	}
}
?>