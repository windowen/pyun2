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
class reportlist_controller extends common{
    /**
     * 简历详情
	 * 举报简历
	 * 2019-06-24
     */
	function index_action(){
        $eid    =   intval($_GET['eid']);
        $euid   =   intval($_GET['uid']);
        $data   =   array();
        if(!empty($_POST['submit'])){
            if($_POST['reason'] ==  ""){
                $data['msg']            =   '请选择举报原因！';
                $this -> yunset('layer', $data);                
            }else{
                $data1['c_uid']         =   $euid;
                $data1['inputtime']     =   time();
                $data1['p_uid']         =   $this->uid;
                $data1['did']           =   $this->userdid;
                $data1['usertype']      =   $this->usertype;
                $data1['eid']           =   $eid;
                $data1['r_name']        =   $_GET['r_name'];
                $data1['username']      =   $this->username;
                $data1['reason']      =   @implode(',', $_POST['reason']);
                $reportM				=	$this -> MODEL('report');

                $result             =   $reportM->ReportResume($data1);

                $data['msg']        =   $result['msg'];
                $data['url']        =   Url('wap',array('c' => 'resume', 'a' => 'show', 'id' => $eid));
                $this -> yunset('layer', $data);
                 
            }
        }
        $this -> yunset('layer', $data);
        $searchurl  =   @implode('&', $searchurl);
		$this -> yunset('searchurl', $searchurl);
		
		$this -> yunset($CacheArr);
		$this -> yunset('headertitle', '找人才');
        $this -> seo('report');
        $this -> yuntpl(array('wap/reportlist'));
	}
}
?>