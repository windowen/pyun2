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
class map_controller extends common
{

    function index_action()
    {
        $this -> get_moblie();
        
        if ($_GET['x'] && $_GET['y']){
            
            $this->yunset(array('mapx'=>$_GET['x'],'mapy'=>$_GET['y']));
            
        }else{
            
            $user_agent = (!isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
            
            if (($_COOKIE['mapx'] && $_COOKIE['mapx']>0) && ($_COOKIE['mapy'] && $_COOKIE['mapy']>0) && strpos($user_agent, 'Android') && is_weixin()){
                
                $this->yunset(array('mapx'=>$_COOKIE['mapx'],'mapy'=>$_COOKIE['mapy']));
                
            }else{
                
                $this->yunset(array('mapx'=>0,'mapy'=>0));
            }
        }
        $this -> seo('map');
        $this -> yunset('headertitle', '附近职位');
        $this -> yuntpl(array('wap/map'));
    }

    function maplist_action()
    {
        $this -> get_moblie();
        
        if ($_GET['x'] && $_GET['y']){
            
            $this->yunset(array('mapx'=>$_GET['x'],'mapy'=>$_GET['y']));
            
        }else{
            $user_agent = (!isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
            
            if (($_COOKIE['mapx'] && $_COOKIE['mapx']>0) && ($_COOKIE['mapy'] && $_COOKIE['mapy']>0) && strpos($user_agent, 'Android') && is_weixin()){
                
                $this->yunset(array('mapx'=>$_COOKIE['mapx'],'mapy'=>$_COOKIE['mapy']));
            }else{
                
                $this->yunset(array('mapx'=>0,'mapy'=>0));
            }
        }
        $this -> seo('map');
        $this -> yunset('headertitle', '附近职位');
        $this -> yuntpl(array('wap/maplist'));
    }

    function joblist_action()
    {
        $this -> get_moblie();
        
        $jobM   =   $this->MODEL('job');
        
        // 根据sql距离计算公式计算距离并排序
        $select =   "`id`,`uid`,`name`,`minsalary`,`maxsalary`,`lastupdate`, `is_link`, 6371 * acos(cos(radians(" . $_POST['y'] . ")) * cos(radians(`y`)) * cos(radians(`x`) - radians(" . $_POST['x'] . ")) + sin(radians(" . $_POST['y'] . ")) * sin(radians(`y`))) AS `distance`";
        
        $page   =   $_POST['page'] ? $_POST['page'] : 1;
        
        if ($this->config['sy_indexpage']) {
            $indexPageNum   =   ceil($this->config['sy_indexpage'] / 10);
            
            if ($page > $indexPageNum) {
                $page   =   $indexPageNum;
            }
        }
        
        $pagenav    =   ($page - 1) * 10;
        
        $limit      =   array($pagenav,10);
		
        $where      =   array(
            
            'state'     =>  1,
            'r_status'  =>  1,
            'status'    =>  0,
            'x'         =>  array('>', 0),
            'y'         =>  array('>', 0),
            'orderby'   =>  'distance, asc',
            'limit'     =>  $limit
        );
		if($this->config['did']>0){
			
			$where['did']	=	$this->config['did'];
		}else{
			$where['PHPYUNBTWSTART']	=	'';
			
			$where['did'][]	=	array('isnull');
          
          	$where['did'][]	=	array('=','0','OR');
			
			$where['PHPYUNBTWEND']	=	'';
		}
		
        $jobListA   =   $jobM -> getList($where, array('field' => $select, 'link' => 'yes', 'from'=> 'wap_map'));
        
        $rows       =   $jobListA['list'];
        
        if (!empty($rows)) {
        
            $uids       =   array();
            
            foreach ($rows as $v) {
                
                $uids[] = $v['uid'];
            }
            
            $comM       =   $this->MODEL('company');
            
            $comListA   =   $comM->getList(array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`name`,`shortname`,`address`'));
            
            $list       =   array();
            
            foreach ($rows as $k => $v) {
                
                $list[$k]['id']         =   $v['id'];
                $list[$k]['name']       =   mb_substr($v['name'], 0, 16, 'utf-8');
                $list[$k]['salary_n']   =   $v['job_salary'];
                if(!empty($v['address'])){
                    $list[$k]['address']  =  $v['address'];
                }
                
                if ($v['welfare_n']) {
                    $list[$k]['welfare']=   $v['welfare_n'];
                }
                if ($v['distance'] <= 1) {
                    $list[$k]['dis']    =   ceil($v['distance'] * 1000) . 'm';
                } else {
                    $list[$k]['dis']    =   round($v['distance'], 2) . 'km';
                }
                
                $list[$k]['joburl']     =   Url('wap', array('c' => 'job','a' => 'comapply', 'id' => $v['id'] ));
                $list[$k]['comurl']     =   Url('wap', array('c' => 'company', 'a' => 'show', 'id' => $v['uid']));
                $list[$k]['addressurl'] =   Url('wap', array('c' => 'map', 'a' => 'jobmap', 'id' => $v['uid']));
                
                foreach ($comListA['list'] as $val) {
                    
                    if ($val['uid'] == $v['uid']) {
                        
                        if ($v['shortname']) {
                            
                            $list[$k]['com_name']   =   mb_substr($val['shortname'], 0, 16, 'utf-8');
                        } else {
                            
                            $list[$k]['com_name']   =   mb_substr($val['name'], 0, 16, 'utf-8');
                        }
                        if(empty($v['address'])){
                            
                            $list[$k]['address']    =   $val['address'];
                        }
                    }
                }
            }
        }
		$numWhere      =   array(
            
            'state'     =>  1,
            'r_status'  =>  1,
            'status'    =>  0,
            'x'         =>  array('>', 0),
            'y'         =>  array('>', 0)
        );
		if($this->config['did']>0){
			
			$numWhere['did']	=	$this->config['did'];
		}else{
			$numWhere['PHPYUNBTWSTART']	=	'';
			
			$numWhere['did'][]	=	array('isnull');
			
			$numWhere['did'][]	=	array('=','0','OR');
			
			$numWhere['PHPYUNBTWEND']	=	'';
		}
        
        // 计算总条数，并计算分页
        $jobNum     =   $jobM -> getJobNum($numWhere);

        $pageCount  =   ceil($jobNum / 10);
        
        if ($this->config['sy_indexpage']) {
        
            $indexPageNum   =   ceil($this->config['sy_indexpage'] / 10);
            
            if ($pageCount > $indexPageNum) {
                
                $pageCount  =   $indexPageNum;
            }
        }
        
        $data           =   array();
        
        $data['list']   =   count($list) > 0 ? $list : array();
        
        $prev           =   '';
        
        if ($page > 1) {
            
            $prev       =   Url('wap', array('c' => 'map', 'page' => $page - 1, 'x' => $_POST['x'], 'y' => $_POST['y']));
        }
        
        $option         =   '';
        
        for ($x = 1; $x <= $pageCount; $x ++) {
            
            $selected = '';
            
            if ($x == $page) {
            
                $selected   =   "selected='selected'";
            }
            
            $option     .=  "<option value='".Url('wap', array('c' => 'map', 'page' => $x, 'x' => $_POST['x'], 'y' => $_POST['y']))."'".$selected.">".$x."</option>";
        }
        
        if ($pageCount > $page) {
        
            $next       =   Url('wap', array('c' => 'map', 'page' => $page + 1, 'x' => $_POST['x'], 'y' => $_POST['y']));
        }
        $data['prev']   =   $prev;
        $data['next']   =   $next;
        $data['option'] =   $option;
        $data['pagecount']  =   $pageCount;
        $data['error']  =   0;
        
        echo json_encode($data);
        die();
    }

    function comlist_action()
    {
        // where语句中HAVING `distance`<20 20是20km范围内的
        // SQL 语句 HAVING 一般和 GROUP BY 一起使用
        $select =   "`uid`,`name`,`shortname`,`x`,`y`,6371 * acos(cos(radians(" . $_POST['y'] . ")) * cos(radians(`y`)) * cos(radians(`x`) - radians(" . $_POST['x'] . ")) + sin(radians(" . $_POST['y'] . ")) * sin(radians(`y`))) AS `distance`";
        
        $page   =   $_POST['page'] ? $_POST['page'] : 1;
        
        $pagenav    =   ($page - 1) * 10;
        
        $limit      =   array($pagenav,10);
        
        $comM       =   $this->MODEL('company');
        
        $where      =   array(
            
            'name'      =>  array('<>', ''),
            'hy'        =>  array('<>', ''),
            'r_status'  =>  1,
            'orderby'   =>  'distance, asc',
            'groupby'   =>  'uid',
            'having'    =>  array(
                'distance' => array('<', 20, '')
            )
        );
        if($this->config['did']>0){
			
			$where['did']	=	$this->config['did'];
		}else{
			$where['PHPYUNBTWSTART']	=	'';
			
			$where['did'][]	=	array('isnull');
          
          	$where['did'][]	=	array('=','0','OR');
			
			$where['PHPYUNBTWEND']	=	'';
		}
        $comWhere   =   array_merge($where, array('limit' => $limit));
        
        $comListA   =   $comM -> getList($comWhere, array('field' => $select));
        
        if (!empty($comListA['list'])) {
            
            $comListAllA    =   $comM -> getList($where, array('field' => $select));
            
            $pageCount      =   ceil(count($comListAllA['list']) / 10);
            
            $uids           =   array();
            
            foreach ($comListA['list'] as $v) {
                
                $uids[]     =   $v['uid'];
            }
            
            $jobM           =   $this->MODEL('job');
            
            $jobListA       =   $jobM -> getList(array('r_status' => 1, 'status' => 0, 'state' => 1, 'uid' => array('in', pylode(',', $uids))), array('field' => '`id`, `uid`, `name`'));
            
            if (!empty($jobListA['list'])) {
                
                $list       =   array();
                
                foreach ($comListA['list'] as $k => $v) {
                    
                    if ($v['shortname']) {
                        
                        $list[$k]['com_name']   =   mb_substr($v['shortname'], 0, 16, 'utf-8');
                    } else {
                        
                        $list[$k]['com_name']   =   mb_substr($v['name'], 0, 16, 'utf-8');
                    }
                    
                    $list[$k]['comurl']         =   Url('wap', array('c' => 'company', 'a' => 'show', 'id' => $v['uid']));
                    $list[$k]['x']              =   $v['x'];
                    $list[$k]['y']              =   $v['y'];
                    
                    $list[$k]['joblist']        =   array();
                    
                    foreach ($jobListA['list'] as $val) {
                        
                        if ($val['uid'] == $v['uid']) {
                            
                            $val['joburl']          =   Url('wap', array('c' => 'job', 'a' => 'comapply', 'id' => $val['id']));
                            $list[$k]['joblist'][]  =   $val;
                        }
                    }
                }
                
                // 去掉没有职位的企业
                foreach ($list as $k => $v) {
                    if (count($list[$k]['joblist']) < 0) {
                        
                        unset($list[$k]);
                    }
                }
            }
        }
        
        $data               =   array();
        $data['list']       =   count($list) > 0 ? $list : array();
        $data['pagecount']  =   $pageCount ? $pageCount : 0;
        $data['error']      =   0;
        
        echo json_encode($data);
        die();
    }

    function jobmap_action()
    {
        $this -> get_moblie();
        $this -> yunset('headertitle', '企业位置');
        
        $comid  =   intval($_GET['id']);
        
        $comM   =   $this -> MODEL('company');
        $comA   =   $comM -> getInfo($comid, array('field' => '`uid`, `name`, `cityid`, `address`, `x`, `y`'));
        
        $CacheM =   $this->MODEL('cache');
        $CacheArr   =   $CacheM->GetCache(array('city'));
        
        $cityName   =   $CacheArr['city_name'][$comA['cityid']];
        
        $this->yunset('cityname', $cityName);
        
        $this->yunset('com', $comA);
        
        $user_agent =   (! isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
        
        if ($_COOKIE['mapx'] > 0 && $_COOKIE['mapy'] > 0 && strpos($user_agent, 'Android')) {
            
            $this->yunset(array('mapx' => $_COOKIE['mapx'], 'mapy' => $_COOKIE['mapy']));
        } else {
            
            $this->yunset(array('mapx' => 0, 'mapy' => 0));
        }
        $this -> seo('map');
        $this->yuntpl(array('wap/job_map'));
    }
}
?>