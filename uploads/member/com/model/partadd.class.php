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
class partadd_controller extends company{
    function index_action(){
        $partM                  =   $this -> MODEL('part');
        $statisM                =   $this -> MODEL('statis');

        $id                     =   intval($_GET['id']);
        
        if($id){
            $row    =   $partM->getInfo(array('uid'=>$this->uid,'id' => $id));
            $row['info']['workcishu']	=	count($row['info']['worktime_n']);
            $this->yunset('row',$row['info']);
        }else{
            $statics    =     $this->company_satic();
          
            if($statics['addjobnum']==0){//会员过期
                if($this->spid){
                    
                    $this->ACT_msg('index.php', '当前账号会员已到期，请联系主账号进行升级！', 8);
                }else{
                    
                    $this->ACT_msg("index.php?c=right","你的会员已到期！",8);
                }
            }
            if($statics['addjobnum']==2){//套餐会员，发布兼职套餐为0 
                if($this->config['integral_job']!='0'){
                    if($this->spid){
                        
                        $this->ACT_msg('index.php', '您的套餐已用完，请联系主账号进行分配！', 8);
                    }else{

                        $this->ACT_msg("index.php?c=right","你的套餐已用完！",8);
                    }
                }else{ 
					if($this->spid){
                        
                        $statisM->upInfo(array('job_num'=>1),array('uid'=>$this->spid,'usertype'=>$this->usertype));
                    }else{

                        $statisM->upInfo(array('job_num'=>1),array('uid'=>$this->uid,'usertype'=>$this->usertype));
                    }
                    
                } 
            }
        }
        
        $this->yunset($this->MODEL('cache')->GetCache(array('city','part')));
        
        $this->company_satic();
        $this->public_action();
        $this->yunset("today",date("Y-m-d"));
        
        $this->com_tpl('partadd');
    }
    function save_action(){
        
        if($_POST['submit']){
            
            $company    =   $this -> comInfo['info'];
           
            $rstaus     =   $company['r_status'];
           
            $_POST['r_status']  =      $rstaus;
         
            $partM	    =	  $this -> MODEL('part');

            $this->cookie->SetCookie("delay", "", time() - 60);
            
            if($_POST['timetype']){
                $_POST['edate']     =   "";
            }else{
              $_POST['edate']     =   strtotime($_POST['edate']);
            }
            $data       =   $_POST;

            $data['uid']        =   $this->uid;
            $data['spid']       =   $this->spid;
            $data['usertype']   =   $this->usertype;
            $data['utype']      =   'user';
            
            $return  =   $partM -> upPartInfo($data);
            
            $toUrl	=	$return['errcode']==9 ? 'index.php?c=part' : '';
            $this ->  ACT_layer_msg($return['msg'],$return['errcode'],$toUrl);
        }
    }
}
?>