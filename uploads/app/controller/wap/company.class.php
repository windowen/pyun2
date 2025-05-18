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
class company_controller extends common
{

    function index_action()
    {
        $this -> get_moblie();
        
        $CacheM     =   $this -> MODEL('cache');
        
        $CacheList  =   $CacheM->GetCache(array('city', 'hy', 'com'));
        
        $this -> yunset($CacheList);
        
        if (intval($_GET['three_cityid'])) {
            
            $this   ->   yunset('cityname', $CacheList['city_name'][intval($_GET['three_cityid'])]);
            
        }

        foreach ($_GET as $k => $v) {
            
            if ($k != '') {
                
                $searchurl[] = $k.'='.$v;
                
            }
            
        }
        
        $this -> seo('firm');
        
        $searchurl  =   @implode('&', $searchurl);
        
        $this -> yunset('searchurl', $searchurl);
        $this -> yunset('backurl', Url('wap'));
        $this -> yunset('topplaceholder', '请输入企业关键字,如：有限公司...');
        $this -> yunset('headertitle', '公司搜索');
        $this -> yuntpl(array('wap/company'));
    }


    function show_action()
    {
        
        $this -> get_moblie();
		
        $UserinfoM  =   $this -> MODEL('userinfo');
        $companyM   =   $this -> MODEL('company');
        $jobM       =   $this -> MODEL('job');
        
        $uid        =   intval($this -> uid);
        $cuid       =   intval($_GET['id']);
        
        $CacheM     =   $this -> MODEL('cache');
        $CacheList  =   $CacheM->GetCache(array('job', 'com', 'city', 'hy'));
        $this -> yunset($CacheList);
        
        $ComMember  =   $UserinfoM -> getInfo(array('uid'=> $cuid), array('field'=>'`status`'));
        
        $row        =   $companyM -> getInfo($cuid, array('logo' => '1'));
       
        
        $show       =   $companyM -> getCompanyShowList(array('uid'=>$cuid, 'status'=>'0'));
       
        $this -> yunset('show', $show);

        if (!is_array($row)) {
            
            $this -> ACT_msg_wap($_SERVER['HTTP_REFERER'], '没有找到该企业！');
            
        } elseif ($ComMember[status] == 0) {
            
            $this -> ACT_msg_wap($_SERVER['HTTP_REFERER'], '该企业正在审核中，请稍后查看！');
            
        } elseif ($ComMember[status] == 3) {
            
            $this -> ACT_msg_wap($_SERVER['HTTP_REFERER'], '该企业未通过审核！');
            
        } elseif ($row[r_status] == 2) {
            
            $this -> ACT_msg_wap($_SERVER['HTTP_REFERER'], '该企业暂被锁定，请稍后查看！');
            
        }
        
        $linkphone  =   explode('-', $row['linkphone']);
        
        if (strlen($linkphone[0]) == 4) {
            
            $row['callphone']   =   $linkphone[0] . $linkphone[1];
            
        } else if (strlen($linkphone[0] > 8)) {
            
            $row['callphone']   =   substr($row['linkphone'], 0, 12);
            
        } else {
            
            $row['callphone']   =   $row['linkphone'];
            
        }
		 //联系方式
		$link		=$jobM->getCompanyTel(array('com_id'=>$row['uid'],'uid'=>$this->uid,'usertype'=>$this->usertype));
		
		$this->yunset('link',$link);

        $statisM    =   $this -> MODEL('statis');
        
        $rows       =   $statisM -> getInfo($cuid, array('usertype' => '2', 'field' => '`rating`'));
       
        $ratingM    =   $this -> MODEL('rating');
        
        $comrat     =   $ratingM -> getInfo(array('id'=> intval($rows['rating'])), array('pic'=>'1'));
        
        $row['lastupdate']  =   date('Y-m-d', $row['lastupdate']);
        
        if ($row['infostatus'] == '2') {
            
            $row['linkphone'] = $row['linktel'] = $row['linkmail'] = $row['linkqq'] = '';
            
        }
        
        $welfare    =   @explode(',', $row['welfare']);
        
        foreach ($welfare as $k => $v) {
            if (! $v) {
                unset($welfare[$k]);
            }
        }
        $row['welfare_n']   =   $welfare;
        
        // 解决通过Editor上传的图片路径问题
        $row['content']     =   str_replace(array('ti<x>tle','“','”','&nbsp;'), array('title','','',' '), $row['content']);
        $row['content']     =   htmlspecialchars_decode($row['content']);
        
        preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/', $row['content'], $res);
        
        if (! empty($res[3])) {
            
            foreach ($res[3] as $v) {
                
                if (strpos($v, 'http:') === false && strpos($v, 'https:') === false) {
                    $ossv=checkpic($v);
                    $row['content'] = str_replace($v, $ossv, $row['content']);
                }
            }
            
        }
        
        $this -> yunset("row", $row);
        
        $this -> yunset("comrat", $comrat);
        
        if ($uid && $this->usertype == '1') {
            
            $atnM   =   $this -> MODEL('atn');
            
            $isatn  =   $atnM -> getAtnInfo(array('uid' => $uid, 'sc_uid' => $cuid));
            
            $this   ->  yunset('isatn', $isatn);
            
            $userid_job =   $jobM -> getSqJobInfo(array('uid' => $uid, 'com_id' => $cuid));
            
            $this   ->  yunset('userid_job', $userid_job);
        }
        
        
        $data['company_name']       =   $row['name'];
        $data['company_name_desc']  =   $row['content'];
        $this -> data   =   $data;
        $this -> seo('company_index');
        
        $this -> yunset('headertitle', '公司详情');
        if($this->config['sy_h5_share']==1){
			$this -> yunset("shareurl", Url('wap', array('c' => 'company','a' => 'share','id' => $cuid)));
		}else{
			$this -> yunset("shareurl",Url('wap',array('c'=>'company','a'=>'show','id'=>$cuid)));	
		}
        
        // 距离
        $user_agent = (! isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
        
        if (($_COOKIE['mapx'] && $_COOKIE['mapx'] > 0) && ($_COOKIE['mapy'] && $_COOKIE['mapy'] > 0) && strpos($user_agent, 'Android') && is_weixin()) {
           
            $this->yunset(array(
                'mapx' => $_COOKIE['mapx'],
                'mapy' => $_COOKIE['mapy']
            ));
            
        } else {
            
            $this->yunset(array(
                'mapx' => 0,
                'mapy' => 0
            ));
            
        }
        
		$departmentjobs		=	$jobM -> getList(array('uid'=>intval($_GET['id']),'status'=>'0','state'=>'1','r_status'=>1),array('field'=>'`zuid`'));
		foreach($departmentjobs['list'] as $val){
			$zuids[]	=	$val['zuid'];
		}
		
		$this->yunset("departmentNames",$departmentNames);
        $this->yuntpl(array('wap/company_show'));
    }
	
    function share_action()
    {
        
        $this   ->  get_moblie();

        $cuid       =   intval($_GET['id']);
        
        $comM       =   $this -> MODEL('company');
        
        $row        =   $comM -> getInfo($cuid, array('logo'=>'1'));

        $welfare    =   @explode(',', $row['welfare']);
        
        foreach ($welfare as $k => $v) {
        
            if (! $v) {
                
                unset($welfare[$k]);
            }
        }
        
        $row['welfare']     =   $welfare;
        
        $row['content']     =   strip_tags($row['content']);

        $row['content']     =   str_replace(array('&nbsp;'), array(' '), $row['content']);
        
        $jobM   =   $this->MODEL('job');
        
        $link   =   $jobM -> getCompanyTel(array('com_id'=>$row['uid'],'uid'=>$this->uid,'usertype'=>$this->usertype));
      
        $this->yunset('link',$link);
        
        $this -> yunset('row', $row);
        
        $show   =   $comM -> getCompanyShowList(array('uid' => $cuid), array('field' => '`picurl`'));
         
        $this -> yunset('show', $show);
        
        $product    =   $comM -> getCompanyProductList(array('uid' => $cuid, 'status' => '1'));
        
        $this -> yunset('product', $product);
        
        $CacheM     = $this->MODEL('cache');
        
        $CacheList  = $CacheM->GetCache(array('job', 'com', 'city', 'hy'));

        $this -> yunset($CacheList);
        
        $data['company_name']       =   $row['name'];
        $data['company_name_desc']  =   $row['content'];
        $this -> data   =   $data;
        
        $this -> seo('company_index');
        $this -> yunset('company_style', $this->config['sy_weburl'] . '/app/template/wap/company');
        $this -> yuntpl(array('wap/company/index'));
    }
   
    function whb_action()
    {
        
        $this ->seo('whb');
        if ($this->uid) {
            $backurl    =  Url('wap', array(), 'member');
        }else{
            $backurl    =  Url('wap', array());
        }
       
        $this -> yunset('backurl', $backurl);
        $this ->yunset('headertitle', '企业微海报生成');
        $this -> yuntpl(array('wap/hb/whb'));
    }

    function hbshow_action()
    {
        if ($_GET['id']){
            
            $id     =   intval($_GET['id']);
            
        }elseif ($_GET['uu'] && $_GET['token']){

            $_GET['id']	=	$_GET['uu'];
            $id			=	$_GET['uu'];
            
        }
        
        if (!empty($id)){
            
            $CacheM     =   $this->MODEL('cache');
            
            $CacheList  =   $CacheM->GetCache(array('job', 'com', 'city'));
            
            $this       ->  yunset($CacheList);
            
            $jobM   =   $this -> MODEL('job');
            
            if ($_GET['jobids']) {
                $jobids  =  @explode(',', $_GET['jobids']);
            }
            
            $where  =   array();
            
            $where['uid']       =   $id;
            $where['status']    =   '0';
            $where['state']     =   '1';
            $where['orderby']   =   'lastupdate,desc';
            if ($jobids) {
                $where['id']    =   array('in', pylode(',', $jobids));
            }
           
            
            $jobA   =   $jobM -> getList($where, array());
            
            $this   ->  yunset('jobs', $jobA['list']);
            
            $comM   =   $this -> MODEL('company');
            
            $com    =   $comM -> getInfo($id, array('field' => '`name`'));
            
            $this   ->  yunset(array('comid' => $id, 'comname' => $com['name']));
            
            $hb     =   intval($_GET['hb']);
            
            $this   ->  yunset('hb', $hb);
			
			//只有设置wap二级域名时才需要以下处理
	
			if(strpos($_SERVER['REQUEST_URI'],'/wap/') ===false){
				
				$this   ->  yunset('imgbase', '1');
				
			}
        }else{
            
            $this->ACT_msg_wap(Url('wap'), '参数错误，请重试！', 2, 3);
        }
        
        $this->yuntpl(array('wap/hb/'.$hb.'/tpl'));
    }
	function getHbBase_action(){
	
		if($_GET['hb']){
			
			$hb = intval($_GET['hb']);
			//获取背景图防止跨域
			$imgFile = TPL_PATH."wap/hb/images/hb".$hb.".png";

			$fp = fopen($imgFile, "r"); // 图片是否可读权限
			
			if ($fp) {

				$fileSize		=	filesize($imgFile);
				
				$content		=	fread($fp, $fileSize);
			}
			
			fclose($fp);
			
			header("Content-Type:image/png");
			    
			echo $content;		
		}
	
	
	}

    function getHb_action()
    {
        $CacheM     =   $this->MODEL('cache');
        
        $CacheList  =   $CacheM->GetCache(array('job', 'com', 'city'));

        $this       ->  yunset($CacheList);
        
        $jobM       =   $this -> MODEL('job');
        
        $id         =   intval($_GET['id']);

        $where              =   array();
        
        $where['uid']       =   $id;
        $where['status']    =   0;
        $where['state']     =   1;
        $where['orderby']   =   'lastupdate,desc';
        
        if ($_GET['jobids']) {
            
            $jobids         =   @explode(',', $_GET['jobids']);
            
            $where['id']    =   array('in', pylode(',', $jobids));
            
        }
        
        $jobA       =   $jobM -> getList($where);
        
        $this       ->  yunset('jobs', $jobA['list']);
       
        $comM       =   $this -> MODEL('company');
        
        $com        =   $comM -> getInfo($id, array('field' => '`name`'));
        
        $this       ->  yunset('comname', $com['name']);

        $hb         =   $_GET['hb'];
        
        $this -> yunset('hb', $hb);
        
        $this -> yuntpl(array('wap/hb/'.$hb.'/tpl'));
        
    }

	/**
     * @desc 海报新版，选择职位操作
	 * @time 2020-07-15
     */
	function getHbJob_action(){
		if($_POST['submit']){

			$jobM	=	$this->MODEL('job');
				
			$jobA	=   $jobM -> getList(array('uid' => $_POST['comid'], 'id' => array('in', pylode(',', $_POST['jobids']))));
        
			$jobs	=	$jobA['list'];
			
			echo json_encode($jobs);die;
		}
	}
	
	/**
     * @desc 海报新版，生成图片
	 * @time 2020-07-15
     */
	function upHb_action(){

		$companyM	=	$this->MODEL('company');
		
		if($_POST['submit']){
 		 
			$uploadM	=	$this -> MODEL('upload');
			$result		=	$uploadM -> newUpload(array('dir'=>'hb', 'base'=>$_POST['uimage']));
 
			$imgUrl		=	checkpic($result['picurl']);
			
			echo json_encode($imgUrl);die;
		} 
	}
}
?>