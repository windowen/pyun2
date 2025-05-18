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
class wx_controller extends adminCommon{
	 

	function setwx_search(){
		/*高级搜索相关代码*/
		$status	=	array('2'=>'未登录','1'=>'已登录');

		$time	=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');

		$this->yunset('status',$status);

		$this->yunset('time',$time);

		/*高级搜索结束*/
		$search_list[]	=	array('param'=>'status','name'=>'状态','value'=>$status);

		$search_list[]	=	array('param'=>'time','name'=>'登录时间','value'=>$time);

		$this->yunset('search_list',$search_list);
	}

	function index_action(){

		$this->yuntpl(array('admin/admin_wx'));

	}

	

	function save_action(){
 		if($_POST['msgconfig']){

			//实例化
			$configM	=	$this->MODEL('config');

			unset($_POST['msgconfig']);

			$configM->setConfig($_POST);

			$this->web_config();

			$this->ACT_layer_msg('微信配置更新成功！',9,$_SERVER['HTTP_REFERER'],2,1);
		}
	}

	function binduser_action(){

		//实例化
		$userInfoM =	$this->MODEL('userinfo');

		$where['PHPYUNBTWSTART']		=	'';

		$where['wxid']			=	array('<>','');

		$where['wxid']			=	array('notnull');

		$where['PHPYUNBTWEND']	=	'';

		if(trim($_GET['keyword'])){

			$where['username']	=	array('like',trim($_GET['keyword']));

			$urlarr['keyword']	=	$_GET['keyword'];

		}

		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');

		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('member',$where,$pageurl,$_GET['page']);

		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

		    //limit order 只有在列表查询时才需要
			$where['orderby']		=	'wxbindtime,desc';

		    $where['limit']			=	$pages['limit'];

			$urlarr['order']		=	$_GET['order'];

			$urlarr['t']			=	$_GET['t'];

		    $List	=	$userInfoM -> getList($where,array('field'=>'`uid`,`username`,`wxid`,`wxbindtime`'));

			$this->yunset('userList',$List);
		}

		$this->yuntpl(array('admin/admin_wxbind'));
	}

	function keyword_action(){

		$hotKeyM	=	$this->MODEL('hotkey');

		$where['type']	=	'8';

		if(trim($_GET['keyword'])){

			$where['key_name']	=	array('like',trim($_GET['keyword']));

			$urlarr['keyword']	=	trim($_GET['keyword']);
		}

		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');

		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('hot_key',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){

			$where['orderby']		=	'num,desc';

		    $where['limit']			=	$pages['limit'];

			$urlarr['order']		=	$_GET['order'];

			$urlarr['t']			=	$_GET['t'];

		    $List	=	$hotKeyM -> getList($where);

			$this->yunset('keyList',$List);
		}

		$this->yuntpl(array('admin/admin_wxkey'));
	}

	function wxnav_action(){
		//实例化
		$weiXinM			=	$this->MODEL('weixin');

		$where['id']		=	array('>',0);

		$where['orderby']	=	'sort,asc';

  		$navlist 			= 	$weiXinM->getWxNavList($where);

		$this->yunset('navlist',$navlist);

		$this->yuntpl(array('admin/admin_wxnav'));
	}

	 

	function wxqrcodelog_action(){
		$this->setwx_search();

		//实例化
		$weiXinM	=	$this->MODEL('weixin');

		if(trim($_GET['keyword'])){

			if($_GET['wtype']=='1'){

				$where['re_nick']	=	array('like',trim($_GET['keyword']));

		    }elseif($_GET['wtype']=='2'){

				$where['username']	=	array('like',trim($_GET['keyword']));

			}

			$where['PHPYUNBTWSTART']		=	'';

			$where['username']		=	array('like',trim($_GET['keyword']));

			$where['re_nick']		=	array('like',trim($_GET['keyword']),'or');

			$where['PHPYUNBTWEND']	=	'';

			$urlarr['keyword']		=	trim($_GET['keyword']);

			$urlarr['keyword']		=	trim($_GET['keyword']);
		}

		if($_GET['status']){

			if($_GET['status']=='2'){

				$status = 0;

			}else{

				$status = $_GET['status'];

			}
			$where['status']		=	$status;

			$urlarr['status']		=	trim($_GET['status']);
		}

		if($_GET['usertype']){

			$where['usertype']		=	$_GET['usertype'];

			$urlarr['usertype']		=	trim($_GET['usertype']);
		}

		if($_GET['time']){

			if($_GET['time']=='1'){

				$where['time']		=	array('>',strtotime(date('Y-m-d 00:00:00')));

			}else{

				$where['time']		=	array('>',strtotime('-'.intval($_GET['time']).' day'));
			}

				$urlarr['time']		=	$_GET['time'];

		}

		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');

		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('wxqrcode',$where,$pageurl,$_GET['page']);

		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

		    //limit order 只有在列表查询时才需要
			$where['orderby']		=	'time,desc';

		    $where['limit']			=	$pages['limit'];

			$urlarr['order']		=	$_GET['order'];

			$urlarr['t']			=	$_GET['t'];

			$List	=	$weiXinM -> getWxQrcodeList($where);

			$this->yunset('logList',$List['list']);
		}

		$this->yuntpl(array('admin/admin_wxqrcodelog'));
	}

	function edit_action(){

		//实例化
		$weiXinM	=	$this->MODEL('weixin');

		$logM		=	$this->MODEL('log');

		if($_POST['name'] && $_POST['keyid']!==''){

			$data['name']		=	$_POST['name'];

			$data['key'] 		= 	$_POST['key'];

			$data['keyid'] 		= 	$_POST['keyid'];

			$data['url'] 		= 	$_POST['url'];

			$data['type'] 		= 	$_POST['type'];

			$data['sort'] 		= 	$_POST['sort'];

			$data['appid'] 		= 	$_POST['appid'];

			$data['apppage'] 	= 	$_POST['apppage'];

			$where['name']		= 	$_POST['name'];

			if($_POST['keyid']>0){

				if(!$_POST['key'] && $_POST['type']=='click'){

					echo 1;exit();

				}elseif($_POST['type']=='miniprogram' && (!$_POST['url'] || !$_POST['appid'] || !$_POST['apppage'])){

					echo 1;exit();

				}elseif($_POST['type']=='view' && !$_POST['url']){

					echo 1;exit();

				}else{

					$where['PHPYUNBTWSTART']		=	'';

					$where['name']			=	$_POST['name'];

					$where['keyid']			=	$_POST['keyid'];

					$where['PHPYUNBTWEND']	=	'';

				}
			}

			if($_POST['navid']>0){

				$where['id']	=	array('<>',$_POST['navid']);

			}

 			$nav = $weiXinM->getWxNavNum($where);

			if($nav>0){

				echo 2;exit();

			}

			unset($_POST['pytoken']);

			if($_POST['navid']>0){

				$navid = $_POST['navid'];

				unset($_POST['navid']);

				$upWhere['id']	=	$navid;

				$weiXinM->upWxNavInfo($upWhere,$data);

				$logM	->addAdminLog('微信菜单(ID:'.$navid.')修改成功');

			}else{

				$navid	=	$weiXinM->addWxNavInfo($data);

				$logM	->addAdminLog('微信菜单(ID:'.$navid.')添加成功');
			}

			echo 3;	exit();

		}else{

			echo 1;	exit();

		}

	}

	function creat_action(){

		//实例化
		$weiXinM			=	$this->MODEL('weixin');

		$where['id']		=	array('>',0);

		$where['orderby']	=	array('keyid,asc','sort,asc');

  		$creatNav	=	$weiXinM->creatWxNavList($where);

		if(is_array($creatNav))	{

 			$Token = getToken($this->config);

 			$DelUrl = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$Token;
			CurlPost($DelUrl);

			$Url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$Token;
			$result = CurlPost($Url,urldecode(json_encode($creatNav)));

			$Info = json_decode($result);

			if($Info->errcode=='0' || $Info->errmsg=='ok'){

				echo 1;die;

			}else{

				echo '错误代码:'.$Info->errcode.',错误信息:'.$Info->errmsg;die;
			}
		}
	}

 	function delnav_action(){

		//实例化
		$weiXinM	=	$this->MODEL('weixin');

		if($_POST['del']){

			$where['id']	=	array('in',pylode(',',$_POST['del']));

			$where['keyid']	=	array('in',pylode(',',$_POST['del']),'or');

			$weiXinM->delWxNav($where,array('type'=>'all'));

			$this->layer_msg('微信菜单(ID:'.pylode(',',$_POST['del']).')删除成功！',9,1,$_SERVER['HTTP_REFERER']);
		}
		if((int)$_GET['delid']){

			$this->check_token();

			$where['id']	=	(int)$_GET['delid'];

			$where['keyid']	=	array('=',(int)$_GET['delid'],'or');

			//删除微信菜单及子菜单
			$id	=	$weiXinM->delWxNav($where,array('type'=>'one'));

			$id?$this->layer_msg('微信菜单(ID:'.$_GET['delid'].')删除成功！',9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}

 	function deluser_action(){

		//实例化
		$userInfoM	=	$this->MODEL('userinfo');
		if($_GET['del']){

			$this->check_token();

			$where['wxid']	=	array('in',pylode(',',$_GET['del']));

			$data['wxid']	=	'';

			$userInfoM->upInfo($where,$data);

			$this->layer_msg('微信用户(ID:'.pylode(',',$_GET['del']).')取消绑定成功！',9,1,$_SERVER['HTTP_REFERER']);
		}

	}

	function ajax_action(){
		//实例化
		$weiXinM	=	$this->MODEL('weixin');

		$logM		=	$this->MODEL('log');

		if($_POST['sort']){

			$upWhere['id']	=	$_POST['id'];

			$data['sort']	=	$_POST['sort'];

			$weiXinM->upWxNavInfo($upWhere,$data);

			$logM->addAdminLog('微信菜单(ID:'.$_POST['id'].')排序修改成功');
		}

		if($_POST['name']){

			$upWhere['id']	=	$_POST['id'];

			$data['name']	=	$_POST['name'];

			$weiXinM->upWxNavInfo($upWhere,$data);

			$logM->addAdminLog('微信菜单(ID:'.$_POST['id'].')名称修改成功');
		}

		echo '1';die;
	}

	function zdkeyword_action(){
		//实例化
		$weiXinM	=	$this->MODEL('weixin');


		if(trim($_GET['keyword'])){

			$where['keyword']	=	array('like',trim($_GET['keyword']));

			$urlarr['keyword']	=	trim($_GET['keyword']);
		}

		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');

		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('wxzdkeyword',$where,$pageurl,$_GET['page']);

		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

		    //limit order 只有在列表查询时才需要
			$where['orderby']		=	'time,desc';

		    $where['limit']			=	$pages['limit'];

			$urlarr['order']		=	$_GET['order'];

			$urlarr['t']			=	$_GET['t'];

		    $List	=	$weiXinM->getWxzdkeywordList($where);

			$this->yunset('keyList',$List['list']);
		}

		$this->yuntpl(array('admin/admin_zdkeyword'));
	}

	//一键清除三天以上记录
	function clearwx_action(){

		//实例化
		$weiXinM	=	$this->MODEL('weixin');

		$where['time']	=	array('<',strtotime('-3 days'));

		$del		=	$weiXinM->delWxqrcode($where,array('type'=>'all'));

		echo $del ? '清理完成！' : '删除失败！';

	}

	function zdaddkeyword_action(){

		//实例化
		$weiXinM		=	$this->MODEL('weixin');

		$where['id']	=	(int)$_GET['id'];

		$row	=	$weiXinM->getWxzdkeyword($where);

	    $this->yunset('row',$row);

		if($_POST['submit']){

			if(trim($_POST['title'])==''){

				$this->ACT_layer_msg('规则名称不能为空！',8);

			}

			if(trim($_POST['keyword'])==''){

				$this->ACT_layer_msg('关键字不能为空！',8);

			}

			if(trim($_POST['content'])==''){

				$this->ACT_layer_msg('回复内容不能为空！',8);

			}

			$data['title']		=	trim($_POST['title']);

			$data['keyword']	=	trim($_POST['keyword']);

			$data['content']	=	trim($_POST['content']);

			$data['time']		=	time();

			$weiXinM->addWxzdkeyword($data);

			$this->ACT_layer_msg('添加成功！',9,'index.php?m=wx&c=zdkeyword',2,1);
		}

		if($_POST['update']){

			$data['title']		=	trim($_POST['title']);

			$data['keyword']	=	trim($_POST['keyword']);

			$data['content']	=	trim($_POST['content']);

			$data['time']		=	time();

			$zdWhere['id']		=	$_POST['id'];

			$weiXinM->upWxzdkeyword($zdWhere,$data);

		    $this->ACT_layer_msg('修改成功！',9,'index.php?m=wx&c=zdkeyword',2,1);

		}

		$this->yuntpl(array('admin/admin_zdaddkeyword'));
	}

	function delkeyword_action(){

		//实例化
		$weiXinM	=	$this->MODEL('weixin');

		if(is_array($_POST['del'])){

			$where['id']	=	array('in',pylode(',',$_POST['del']));

			$del			=	$weiXinM->delWxzdkeyword($where,array('type'=>'all'));

			$layer_type		=	1;

			$delid			=	pylode(',',$_POST['del']);

		}else{

			$this->check_token();

			$where['id']	=	(int)$_GET['id'];

			$del			=	$weiXinM->delWxzdkeyword($where,array('type'=>'one'));

			$layer_type		=	0;

			$delid			=	(int)$_GET['id'];
		}

		if(!$delid){

			$this->layer_msg('请选择要删除的内容！',8);

		}

		$del?$this->layer_msg('(ID:'.$delid.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,$layer_type,$_SERVER['HTTP_REFERER']);
	}
	/**
	 * 微信发布工具
	 */
	function pubtool_action(){

		$CacheM		=	$this->MODEL('cache');
        $CacheList	=	$CacheM->GetCache(array('city','user','domain'));

        
		$this->yunset('userdata',$CacheList['userdata']);

       
		$this->yunset('userclass_name',$CacheList['userclass_name']);

		
		$this->yunset('domain',$CacheList['Dname']);
		
	    $this->yuntpl(array('admin/admin_wxpubtool'));
	}

	//微信发布工具信息
	function Getpubtool_action(){
		$get = $_GET;
		// var_dump($get);exit;
		//查询类型 0职位列表1简历列表2企业列表
		$type = $get['type'];
		
		@include(DATA_PATH.'api/wxpay/wxpay_data.php');
        
        $this->yunset('wxpaydata',$wxpaydata);

		switch ($type) {
			case '1':
				$this->Getjob($get);
				break;
			case '2':
				$this->Getresume($get);
				break;
			case '3':
				$this->Getcompany($get);
				break;
			default:
				echo "暂无信息";
				break;
		}
	}

	
	//职位列表
	protected function Getjob($GET){

		$jobM  = $this->MODEL('job');
		$time  = time();
		$where = array();
		$data  = array();
		//职位参数 0置顶 1紧急 2推荐
		if($GET['param'] != ''){
			$param = explode(',',$GET['param']);
			//置顶
			if(in_array('0',$param)){
				$where['xsdate'] = array('>',$time);
			}
			//紧急
			if(in_array('1',$param)){
				$where['urgent_time'] = array('>',$time);
			}
			//推荐
			if(in_array('2',$param)){
				$where['rec_time'] = array('>',$time);
			}
		}

		//职位发布时间
		if($GET['times'] != '0'){
			$times = $GET['times'];
			if($times  ==  '1'){
                $where['sdate'] = array('>',strtotime(date('Y-m-d 00:00:00')));
            }else{
                $where['sdate'] = array('>',strtotime('-'.intval($times).' day'));
            }
		}

		//指定企业职位企业id
		if($GET['copos'] != ''){
			$where['uid'] = intval($GET['copos']);
		}

		//职位展示限制
		if($GET['rule'] != '0'){
			$data['rule'] = $GET['rule'];
		}

		//关键词
		if($GET['keyword'] != ''){
			$where['com_name'] = array('like',$GET['keyword'],'or');
			$where['name'] = array('like',$GET['keyword'],'or');
		}

		//信息数量
		$num = $GET['num'];
		$where['limit']	= array("0",$num);
		
		$where['state']		= '1';
		$where['r_status']	= '1';
		$where['status']	= '0';
		$where['orderby'] =	'lastupdate,desc';
		$lists = $jobM->Getpubtool($where,$data);
		$this->yunset('lists',$lists);
		$this->yunset('ewmtype',$GET['ewmtype']);
		//风格模板
		$tpl = $GET['tpl']?$GET['tpl']:1;
		$this->yuntpl(array("admin/wxpubtpl/job/".$tpl));

	}

	//简历列表
	protected function Getresume($GET){
		$resumeM = $this->MODEL('resume');
		$time = time();
		$where = array();
		// var_dump($GET);exit;

		//简历类型
		if($GET['cvkind'] != ''){
			//置顶
		    $where['top'] 		  =  1;
		    $where['topdate']	  =  array('>',$time);
		}

		//意向职位
		if($GET['ideapos'] != ''){
			$where['name'] = array('like',$GET['ideapos']);
		}

		//意向地区
		if($GET['ideaarea'] != ''){

			$CacheM		=	$this->MODEL('cache');
            $CacheList	=	$CacheM->GetCache(array('city'));

            $area   = array_flip($CacheList['city_name']);

            $areaid = $area[$GET['ideaarea']]?$area[$GET['ideaarea']]:0;

            $where['city_classid']   = array('like',$areaid);
		}

		//学历
		if($GET['bd'] != ''){
			$where['edu'] = array('in',$GET['bd']);
		}

		//经验
		if($GET['exp'] != ''){
			$where['exp'] = array('in',$GET['exp']);
		}

		//简历完整度
		$whole = $GET['whole'];//0 40%   1 60%   2 80%
		if($whole== 0){
	        $where['PHPYUNBTWSTART']   =   '';
	        $where['integrity'][]	=	array('>=', '40','AND');
	        $where['integrity'][]	=	array('<','60','AND');
	        $where['PHPYUNBTWEND']     =   '';
	    }elseif($whole== 1){
	        $where['PHPYUNBTWSTART']   =   '';
	        $where['integrity'][]	=	array('>=', '60', 'AND');
	        $where['integrity'][]	=	array('<','80','AND');
	        $where['PHPYUNBTWEND']     =   '';
	    }elseif($whole== 2){
	        $where['PHPYUNBTWSTART']   =   '';
	        $where['integrity'][]	=	array('>=', '80', 'AND');
	        $where['integrity'][]	=	array('<=','100','AND');
	        $where['PHPYUNBTWEND']     =   '';
	    }

		//信息数量
		$num = $GET['num'];
		$where['limit']	= $num;


		$where['defaults'] = 1;
		$where['state']    = 1;
		$where['status']   = 1;
		$where['r_status'] = 1;
		$where['orderby'] = "lastupdate,desc";
		$lists = $resumeM->Getresume($where);
		
		$this->yunset('lists',$lists);
		$this->yunset('ewmtype',$GET['ewmtype']);
		//风格模板
		$tpl = $GET['tpl']?$GET['tpl']:1;
		$this->yuntpl(array("admin/wxpubtpl/resume/".$tpl));
	}
	//企业列表
	protected function Getcompany($GET){
		$companyM = $this->MODEL('company');
		$where = array();
		$data  = array();
		$time  = time();
		//职位参数 0置顶 1紧急 2推荐
		if($GET['param'] != ''){
			$param = explode(',',$GET['param']);
			//置顶
			if(in_array('0',$param)){
				$data['jWhere']['xsdate'] = array('>',$time);
			}
			//紧急
			if(in_array('1',$param)){
				$data['jWhere']['urgent_time'] = array('>',$time);
			}
			//推荐
			if(in_array('2',$param)){
				$data['jWhere']['rec_time'] = array('>',$time);
			}
		}


		//站点
		if($GET['did'] != ''){
			$where['did'] = $GET['did'];
		}

		//企业类型
		if($GET['bekind'] != ''){
			$where['rec'] = 1;
		}

		//指定企业职位 企业ID
		if($GET['copos'] != ''){
			$where['uid'] = $GET['copos'];
		}

		//关键词
		if($GET['keyword'] != ''){
			$where['name'] = array('like',$GET['keyword']);
		}

		//信息数量
		$num = $GET['num'];
		$where['limit']	= $num;

		$where['name']		= array('<>','');
		$where['r_status']	= 1;
		$where['orderby']	= "lastupdate,desc";
		$lists = $companyM->Getcompany($where,$data);
		
		$this->yunset('lists',$lists);
		$this->yunset('ewmtype',$GET['ewmtype']);
		//风格模板
		$tpl = $GET['tpl']?$GET['tpl']:1;
		
		$this->yuntpl(array("admin/wxpubtpl/company/".$tpl));
	}

}

?>