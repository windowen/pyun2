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
class announcement_controller extends common{
	function index_action(){
		if((int)$_GET['id']){
			
			$id				=	(int)$_GET['id'];
			$announcementM	=	$this->MODEL('announcement');
			$row			=	$announcementM->getInfo(array('id'=>$id));
			$this->yunset("row",$row);
			
			$data['gg_title']	=	$row['title'];
			$data['gg_desc']	=	$this->GET_content_desc($row['description']);
			$this->data			=	$data;
			$this->seo("announcement");

			$this->yunset("headertitle","网站公告");
			$this->yuntpl(array('wap/announcements'));
		}else{
			$this->yunset("headertitle","网站公告");
	        $this->seo("announcement_index");
			$this->yuntpl(array('wap/announcement'));
		}
		
	}	
}
?>