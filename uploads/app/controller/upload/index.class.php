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
class index_controller extends common{

    private $tokenSalt = 'phpyun';
    // 加密的salt
                                   
    // 生成二维码传图的密钥（放到二维码中）
    private function generateToken($type, $uid)
    {
        // 考虑到二维码承载的信息长度有限，tokenSalt和password都限定字符串长度，以得到较短的token
        $userinfoM = $this->MODEL('userinfo');
        
        $row = $userinfoM->getInfo(array('uid' => $uid), array('field' => 'password'));
        
        $password = isset($row['password']) ? $row['password'] : '';
        
        $password = substr($password, 0, 8);
        
        $this->tokenSalt = $this->config['sy_safekey'];
        
        return yunEncrypt("{$type}|{$uid}|{$password}", $this->tokenSalt);
    }

    // 生成二维码（扫码上传入口）
    public function qrcode_action()
    {
        if (! $this->uid) {
            exit('请先登录');
        }
        
        // 传入上传类型 type , save_action中根据类型选择不同的保存路径
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        if ($type == '') {
            exit('扫码上传图片可选类型type：1企业营业执照上传，2个人身份证上传，3个人头像');
        }
        
        $token = $this->generateToken($type, $this->uid);
        $token = urlencode($token);
        $url = Url('wap', array(
            'c' => 'upload',
            'a' => 'p',
            't' => $token
        ));
        
        include_once LIB_PATH . "yunqrcode.class.php";
        YunQrcode::generatePng2($url, 4);
    }
}
?>