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
class admin_xml_controller extends adminCommon{
	function index_action(){ 
		$this->yuntpl(array('admin/admin_xml'));
	}
	function archive_action(){

		if($_POST['limit']){
			
			$type=trim($_POST['type']);
			
			if($type=='ask'||$type=='sitemap'){
				
				$askM			=	$this -> MODEL('ask');
				
				if($_POST['order']=='uptime'){
					
					$order				=	'lastupdate';
					
					$where['orderby']	=	array('lastupdate,desc');
				}else{
					
					$order				=	'add_time';
					
					$where['orderby']	=	array('add_time,desc');
				}
				$where['limit']			=	intval($_POST['limit']);
				
				$qlist			=	$askM -> getList($where,array('field'=>"`id` as `id`,`".$order."` as `time`"));
				
				$rows['ask']	=	$qlist;
			}
			if($type=='news'||$type=='sitemap'){
				
				$articleM		=	$this -> MODEL('article');
				
				if($_POST['order']=='uptime'){
					
					$order='lastupdate';
					
					$where['orderby']	=	array('lastupdate,desc');
				}else{
					
					$order='datetime';
					
					$where['orderby']	=	array('datetime,desc');
				}
				$where['limit']			=	intval($_POST['limit']);
				
				$nlist			=	$articleM -> getList($where,array('field'=>"`id`,`".$order."` as `time`,`datetime`"));
				
				$rows['news']	=	$nlist['list'];
			}
			if($type=='company'||$type=='sitemap'){
				
				$companyM		=	$this -> MODEL('company');
				
				if($_POST['order']=='uptime'){
					
					$order				=	'lastupdate';
					
					$where['orderby']	=	array('lastupdate,desc');
				}else{
					
					$order				=	'jobtime';
					
					$where['orderby']	=	array('jobtime,desc');
				}
				$where['limit']			=	intval($_POST['limit']);
				
				$comlist		=	$companyM -> getList($where,array('field'=>"`uid` as `id`,`".$order."` as `time`"));
				
				$rows['company']=	$comlist['list'];
			}
			if($type=='job'||$type=='sitemap'){
				
				$jobM			=	$this -> MODEL('job');
				
				$where['sdate']			=	array('<',time());
				
				$where['state']			=	1;
				
				if($_POST['order']=='uptime'){
					
					$order				=	'lastupdate';
					
					$where['orderby']	=	array('lastupdate,desc');
				}else{
					
					$order				=	'sdate';
					
					$where['orderby']	=	array('sdate,desc');
				}
				$where['limit']			=	intval($_POST['limit']);
				
				$joblist		=	$jobM -> getList($where,array('field'=>"`id`,`".$order."` as `time`"));
				
				$rows['job']	=	$joblist['list'];
			}
			if($type=='resume'||$type=='sitemap'){
				
				$resumeM		=	$this -> MODEL('resume');
				
				$where['status']		=	1;
				
				$where['r_status']		=	1;
				
				$where['job_classid']	=	array('<>','');
			
				if($_POST['order']=='uptime'){
					
					$order				=	'lastupdate';
					
					$where['orderby']	=	array('lastupdate,desc');
				}else{
					
					$order				=	'addtime';
					
					$where['orderby']	=	array('addtime,desc');
				}
				$where['limit']			=	intval($_POST['limit']);
				
				$rlist			=	$resumeM -> getSimpleList($where,array('field'=>"`id`,`".$order."` as `time`"));
				
				$rows['resume']	=	$rlist;
			}
			if(strpos(trim($_POST['name']),'.xml')==true){
				
				$_POST['name']=substr(trim($_POST['name']),0,strpos(trim($_POST['name']),'.xml'));
			}

			if(is_array($rows)){
				
				$show	=	"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<urlset>\r\n";
				
				foreach($rows as $key=>$v){
					
					if($key=='ask'){
						
						foreach($v as $val){
							
							$url	=	Url('ask',array('c'=>'content','id'=>$val['id']));
							
							$url	=	str_replace("&","&amp;",$url);
							
							$show  .=	"<url><loc>".$url."</loc><lastmod>".date("Y-m-d",$val['time'])."</lastmod><changefreq>".$_POST['frequency']."</changefreq></url>\r\n";
						}
					}
					if($key=='news'&&$this->config["sy_news_rewrite"]=='2'){
						
						foreach($v as $val){
							
							$url	=	$this->config['sy_weburl']."/news/".date("Ymd",$val["datetime"])."/".$val[id].".html";
							
							$show  .=	"<url><loc>".$url."</loc><lastmod>".date("Y-m-d",$val['time'])."</lastmod><changefreq>".$_POST['frequency']."</changefreq></url>\r\n";
						}
					}
					if($key=='news'&&$this->config["sy_news_rewrite"]!='2'){
						
						foreach($v as $val){
							
							$url	=	Url("article",array("c"=>"show","id"=>$val[id]));
							
							$url	=	str_replace("&","&amp;",$url);
							
							$show  .=	"<url><loc>".$url."</loc><lastmod>".date("Y-m-d",$val['time'])."</lastmod><changefreq>".$_POST['frequency']."</changefreq></url>\r\n";
						}
					}
					if($key=='company'){
						
						foreach($v as $val){
							
							$url	=	Url('company',array('c'=>'show',"id"=>$val['id']));
							
							$url	=	str_replace("&","&amp;",$url);
							
							$show  .=	"<url><loc>".$url."</loc><lastmod>".date("Y-m-d",$val['time'])."</lastmod><changefreq>".$_POST['frequency']."</changefreq></url>\r\n";
						}
					}
					if($key=='job'){
						
						foreach($v as $val){
							
							$url	=	Url("job",array("c"=>"comapply","id"=>$val['id']));
							
							$url	=	str_replace("&","&amp;",$url);
							
							$show  .=	"<url><loc>".$url."</loc><lastmod>".date("Y-m-d",$val['time'])."</lastmod><changefreq>".$_POST['frequency']."</changefreq></url>\r\n";
						}
					}
					if($key=='resume'){
						
						foreach($v as $val){
							
							$url	=	Url("resume",array("c"=>'show',"id"=>$val['id']));
							
							$url	=	str_replace("&","&amp;",$url);
							
							$show  .=	"<url><loc>".$url."</loc><lastmod>".date("Y-m-d",$val['time'])."</lastmod><changefreq>".$_POST['frequency']."</changefreq></url>\r\n";
						}
					}
				}
				
				$show.="</urlset>";
				
				if(CheckRegUser($_POST['name'])==false){
					
					$this -> layer_msg("XML名称包含特殊字符！",8,0,'index.php?m=admin_xml');
				}
				$path	=	APP_PATH."/".$_POST['name'].".xml";
				
				$fp		=	@fopen($path,"w");
				
				@fwrite($fp,$show);
				
				@fclose($fp);
				
				@chmod($path,0777);
				
				$this -> layer_msg("生成成功！",9,0,'index.php?m=admin_xml');
			}else{
				
				$this -> layer_msg("没有数据可以生成！",9,0,'index.php?m=admin_xml');
			}
		}
	}

}
?>