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
class search_controller extends map_controller
{

    function index_action()
    {
        $comM       =   $this->MODEL('company');
        
        $jobM       =   $this->MODEL('job');
        
        $page       =   (int) $_POST['page'];
        
        $pagesize   =   10;


        
        if ($_POST['keyword']) {
            
            $keyWhere   =   array('name' => array('like', trim($_POST['keyword'])));
            
            $keyword    =   $_POST['keyword'];
        }
        
        if ($_POST['min_lng'] && $_POST['max_lng']) {
            
            $keyWhere['PHPYUNBTWSTART_A']  =   '';
            $keyWhere['x'][]   =   array('>=', $_POST['min_lng'], 'AND');
            $keyWhere['x'][]   =   array('<=', $_POST['max_lng'], 'AND');
            $keyWhere['PHPYUNBTWEND_A']    =   '';
            
            $keyWhere['PHPYUNBTWSTART_B']  =   '';
            $keyWhere['y'][]   =   array('>=', $_POST['min_lat'], 'AND');
            $keyWhere['y'][]   =   array('<=', $_POST['max_lat'], 'AND');
            $keyWhere['PHPYUNBTWEND_B']    =   '';
            
        }
        
        if (trim($_POST['type']) == 'company') { // 企业搜索
            $comwhere   =   array('name' => array('<>', ''), 'r_status' => 1);

            $comwhere   =   array_merge($comwhere, $keyWhere);

            $allNum     =   $comM -> getCompanyNum($comwhere);
        } else {//职位搜索
            
            $jobWhere   =   array('state' => 1, 'r_status' => 1, 'status' => 0); 
             
            $jobWhere   =   array_merge($jobWhere, $keyWhere);
            
            $allNum     =   $jobM -> getJobNum($jobWhere);
            
        }
        if ($page == 0) {
            
            $pagelimit  =   10;
        } else if ($page <= ($allNum / $pagesize)) {
            
            //$pagelimit  =   $pagesize * $page.','.$pagesize;
            $pagelimit  =   array($pagesize * $page,$pagesize);

        } else {
            
            //$pagelimit  =   ($allNum / $pagesize) * $page .','. $pagesize;
            $pagelimit  =   array(($allNum / $pagesize) * $page,$pagesize);
        }
        
        $jsonList       =   array();
        if (trim($_POST['type']) == 'company') {
            
            $comwhere   =   array_merge($comwhere, $keyWhere, array('limit' => $pagelimit));
            
            $List       =   $comM -> getList($comwhere);

            if(count($List['list'])){
                $uid        =   array();
                $jobsByCom  =   array();
                foreach ($List['list'] as $key => $value) {
                    $uid[]  =   $value['uid'];
                }
                if(count($uid)){
                    $comjobs=   $jobM -> getList(
                        array(
                            'uid'=>array('in',pylode(',',$uid)),
                            'state' => 1, 
                            'r_status' => 1, 
                            'status' => 0
                        )
                    );
                    foreach ($comjobs['list'] as $cjk => $cjv) {
                        $jobsByCom[$cjv['uid']][] = $cjv;
                    }
                }
                

                foreach ($List['list'] as $k => $cv) {
            
                    $jsonList[$k]['name']       =   mb_substr($cv['name'], 0, 10, 'utf-8');
                    $jsonList[$k]['x']          =   $cv['x'];
                    $jsonList[$k]['y']          =   $cv['y'];
                    $jsonList[$k]['address']    =   $cv['address'];
                    $jsonList[$k]['com_url']    =   Url('company', array('c' => 'show', 'id' => $cv['uid']));
                    if($jobsByCom[$cv['uid']]){
                        foreach ($jobsByCom[$cv['uid']] as $v2) {
        
                            $jsonList[$k]['joblist'][]  =   array(
                                'name'      => mb_substr($v2['name'], 0, 10, 'utf-8'),
                                'job_url'   => Url('job', array('c' => 'comapply', 'id' => $v2['id'])),
                                'job_salary'=> $v2['job_salary'],
                                'job_exp'   => $v2['job_exp'] ? $v2['job_exp'] : '',
                                'job_edu'   => $v2['job_edu'] ? $v2['job_edu'] : '',
                            );
                        }  
                    }
                }
            }
            
            
        } else {
            $jobWhere   =   array_merge($jobWhere, array('limit' => $pagelimit));
            $List       =   $jobM -> getList($jobWhere);

            if(count($List['list'])){
                foreach ($List['list'] as $jk => $jv) {
                    $jsonList[$jk]['name']       =   mb_substr($jv['com_name'], 0, 10, 'utf-8');
                    $jsonList[$jk]['x']          =   $jv['x'];
                    $jsonList[$jk]['y']          =   $jv['y'];
                    $jsonList[$jk]['com_url']    =   Url('company', array('c' => 'show', 'id' => $jv['uid']));
                    
                
                    $jsonList[$jk]['joblist'][]  =   array(
                        'name'      =>  mb_substr($jv['name'], 0, 10, 'utf-8'),
                        'job_url'   =>  Url('job', array('c' => 'comapply', 'id' => $jv['id'])),
                        'job_salary'=>  $jv['job_salary'],
                        'job_exp'   =>  $jv['job_exp'] ? $jv['job_exp'] : '',
                        'job_edu'   =>  $jv['job_edu'] ? $jv['job_edu'] : '',
                        'welfare_n' =>  count($jv['welfare_n']) ? $jv['welfare_n'] : array(),
                    );
                }
            }
        }
        
        
        
        echo json_encode(array(
            'date'  =>  $List,
            'list'  =>  $jsonList,
            'count' =>  $allNum,
            'k'     =>  $keyword
        ));
        
        die();
    }
    
}
?>