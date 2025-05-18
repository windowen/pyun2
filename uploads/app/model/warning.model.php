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
class warning_model extends model
{

    /**
     * @desc    预警提醒
     * @param   int $type: 1-发布职位 2-简历下载 3-发布简历 4-充值 5-短信
     */
    function warning($type, $uid = null)
    { 
        $today      =   strtotime('today');
        
        if ($type == 1 && $this->config['warning_addjob']>0) { 
        
            $num1    =   $this->select_num('company_job', array('uid' => $uid, 'sdate' => array('>', $today)));
            $num2    =   $this->select_num('partjob', array('uid' => $uid, 'sdate' => array('>', $today)));
            
            $num     =   $num1 + $num2 ;
            
            if ($num >= $this->config['warning_addjob']) {
                
                $this -> send_warning($type, $uid);
            }
            
        } elseif ($type == 2 && $this->config['warning_downresume']>0) { 
            
            $num    =   $this->select_num('down_resume', array('comid' => $uid, 'downtime' => array('>', $today)));
            
            if ($num >= $this->config['warning_downresume']) {
                
                $this -> send_warning($type, $uid);
            }
            
        } elseif ($type == 3 && $this->config['warning_addresume']>0) { 
            
            $num    =   $this->select_num('resume_expect',  array('uid' => $uid, 'ctime' => array('>', $today)));
            
            if ($num >= $this->config['warning_addresume']) {
                
                $this -> send_warning($type, $uid);
            }
            
        } elseif ($type == 4 && $this->config['warning_recharge'] > 0) { 
            
            $order  =   $this -> select_all('company_order', array('uid' => $uid , 'order_state' => 2, 'integral' => array('>', 0), 'order_time' => array('>', $today)), 'sum(`integral`) as `total`');
            
            if (!empty($order)) {
                
                $price  =   ceil($order[0]['total'] / $this->config['integral_proportion']);
                
                if ($price >= $this->config['warning_recharge']) {
                    
                    $this -> send_warning($type, $uid);
                }
            }
            
        } else if ($type == 5 && $this->config['sy_hour_msgnum'] > 0) { 
                       
            $time   =   time() - 3600;
            $num    =   $this->select_num('moblie_msg', array('ctime' => $time));
            $msg    =   "系统一小时内已发送" . $num . "条短信！";
            
            if ($num >= $this->config['sy_hour_msgnum']) {
                
                $this -> send_warning($type,$uid,$msg);
            }
        }
    }
           
    /**
     * @desc  预警提醒
     * @param int $type 
     * @param string $emailcoment
     */
    function send_warning($type, $uid = null, $emailcoment = null)
    {
        
        $today  =   strtotime('today');
        
        $row    =   $this->select_once('warning', array('type' => $type, 'uid' => $uid, 'ctime' => array('>', $today)));
        
        if (empty($row)) {
            
            $this ->insert_into('warning', array('type' => $type , 'uid' => $uid, 'ctime' => time()));
            
            if ($uid) {
                
                $member     =   $this -> select_once('member', array('uid' => $uid), '`email`,`username`,`usertype`');
                
                $email      =   $member['email'];
                $username   =   trim($member['username']);
                $usertype   =   intval($member['usertype']);
            }
            
            require_once ('notice.model.php');
            $notice =   new notice_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));
            
            require_once ('cookie.model.php');
            $cookie = new cookie_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));

            if ($type == 1) { 
                
                $emailcoment    =   '用户：【'. $username . '】发布职位超出规定数目，请检查是否有问题';
                
                if ($this->config['warning_addjob_type'] == 1) { 
                    
                    $this -> update_once('member', array('status' => 2, 'lock_info' => '发布职位超出规定数目'), array('uid' => $uid));
                    
                    if ($usertype == 2) {
                        $this -> update_once('company', array('r_status' => 2), array('uid' => $uid));
                        
                        $this -> update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                        $this -> update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                    }
                    
                    $notice->sendEmailType(array(
                        'email'     =>  $email,
                        'uid'       =>  $uid,
                        'name'      =>  $username,
                        'lock_info' =>  '发布职位超出规定数目',
                        'type'      =>  'lock'
                    ));
                    $cookie->unset_cookie();
                }
            } elseif ($type == 2) { 
                
                $emailcoment    =   '用户：【'.$username.'】下载简历超出规定数目，请检查是否有问题';
                
                if ($this->config['warning_downresume_type'] == 1) { 
                    
                    $this -> update_once('member', array('status' => 2, 'lock_info' => '下载简历超出规定数目'), array('uid' => $uid));
                    
                    if ($usertype == 2) {
                        $this -> update_once('company', array('r_status' => 2), array('uid' => $uid));
                        
                        $this -> update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                        $this -> update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                    }
                    
                    $notice -> sendEmailType(array(
                        'email'     =>  $email,
                        'uid'       =>  $uid,
                        'name'      =>  $username,
                        'lock_info' =>  '下载简历超出规定数目',
                        'type'      =>  'lock'
                    ));
                    
                    $cookie->unset_cookie();
                }
                
            } elseif ($type == 3) { 
                
                $emailcoment    =   '用户：【'.$username.'】简历发布超出规定数目，请检查是否有问题';
                
                if ($this->config['warning_addresume_type'] == 1) { 
                    
                    $this -> update_once('member', array('status' => 2, 'lock_info' => '简历发布超出规定数目'), array('uid' => $uid));
                    
                    $this -> update_once('resume', array('r_status' => 2) , array('uid' => $uid));
                    $this -> update_once('resume_expect', array('r_status' => 2) , array('uid' => $uid));
                    
                    $notice -> sendEmailType(array(
                        'email'     =>  $email,
                        'uid'       =>  $uid,
                        'name'      =>  $username,
                        'lock_info' =>  '简历发布超出规定数目',
                        'type'      =>  'lock'
                    ));
                    
                    $cookie->unset_cookie();
                    
                }
                
            } elseif ($type == 4) { 
                
                $emailcoment    =   '用户：【'.$username.'】充值超出规定金额，请检查是否有问题';
                
                if ($this->config['warning_recharge_type'] == 1) { 
                    
                    
                    $this -> update_once('member', array('status' => 2, 'lock_info' => '充值超出规定金额'), array('uid' => $uid));
                    
                    if ($usertype == 1) {
                        
                        $this -> update_once('resume', array('r_status' => 2), array('uid' => $uid));
                        $this -> update_once('resume_expect', array('r_status' => 2), array('uid' => $uid));
                    }else if ($usertype == 2) {
                        
                        $this -> update_once('company', array('r_status' => 2), array('uid' => $uid));
                        $this -> update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                        $this -> update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                    }
                    
                    $notice -> sendEmailType(array(
                        'email'     =>  $email,
                        'uid'       =>  $uid,
                        'name'      =>  $username,
                        'lock_info' =>  '充值超出规定金额',
                        'type'      =>  'lock'
                    ));
                    $cookie->unset_cookie();
                }
                
            } else if ($type == 5 && $this->config['warning_closemsg_type'] == 1) {
                
                $this -> update_once('admin_config', array('config' => 2, 'name' => 'sy_msg_isopen'));
                
            }
             
            $emailData  =   array(
                'email'     =>  $this->config['sy_webemail'],
                'subject'   =>  '预警提醒',
                'content'   =>  $emailcoment
            );
            $notice->sendEmail($emailData);
        }
    }
}
?>