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
class upload_controller extends common{
	
	private $tokenSalt = 'phpyun';//加密的salt

	//生成二维码传图的密钥（放到二维码中）
	private function generateToken($type, $uid){
		//考虑到二维码承载的信息长度有限，tokenSalt和password都限定字符串长度，以得到较短的token
		$userinfoM	=	$this->MODEL('userinfo');
		$row 		= 	$userinfoM->getInfo(array('uid'=> $uid),array('field'=>'`password`'));
		$password 	= isset($row['password']) ? $row['password'] : '';
		$password 	= substr($password, 0, 8);
		
		$this->tokenSalt	= $this->config['sy_safekey'];
		return yunEncrypt("{$type}|{$uid}|{$password}", $this->tokenSalt);
	}

	//验证二维码传图的密钥（提交图片之前进行验证）
	private function checkToken($token){
		$token		= 	urldecode($token);
		
		$this->tokenSalt = $this->config['sy_safekey'];
		$str 		= 	yunDecrypt($token, $this->tokenSalt);
		$arr 		= 	explode('|', $str);
		if(count($arr) != 3 || $arr[1] == ''){
			return false;
		}
		//根据uid查询password，对比password
		$uid = $arr[1];
		$userinfoM	=	$this->MODEL('userinfo');
		$row 		= 	$userinfoM->getInfo(array('uid'=> $uid),array('field'=>'`password`'));
		
		$password 	= 	isset($row['password']) ? $row['password'] : '';
		$password 	= 	substr($password, 0, 8);
		if($password != $arr[2]){
			return false;
		}
		return array('uid' => $uid, 'type' => $arr[0]);
	}

	//生成二维码（扫码上传入口）
	public function qrcode_action(){
		if(!$this->uid){
			exit('请先登录登录');
		}
		//传入上传类型 type , save_action中根据类型选择不同的保存路径
		$type 	= isset($_GET['type']) ? $_GET['type'] : '';
		if($type == ''){
			exit('扫码上传图片可选类型type：1企业营业执照上传，2个人身份证上传，3个人头像，4企业logo');
		}
		$token 	= $this->generateToken($type, $this->uid);
		$token 	= urlencode($token);
		$url 	= Url('wap',array('c'=> 'upload', 'a' => 'p', 't' => $token) );

		include_once LIB_PATH."yunqrcode.class.php";
		YunQrcode::generatePng2($url, 4);
	}

	//wap站扫码上传页面
	public function p_action(){

		$userinfoM	=	$this->MODEL('userinfo');

		$token 	= isset($_GET['t']) ? $_GET['t'] : '';
		$arr 	= $this->checkToken($token);
		if($arr == false || !isset($arr['type']) || !isset($arr['uid']) ){
			exit('抱歉，功能维护中');
		}
		$this->yunset('token', $token);
		$this->yunset('type', $arr['type']);
		
		if($arr['type'] == 3 || $arr['type'] == 4 || $arr['type'] == 5 || $arr['type'] == 6){//上传头像
			$pic	=	$icon	=	'';
		    if ($arr['type']==3){
				
				$photo 		= 	$userinfoM->getUserInfo(array('uid'=>$arr['uid']),array('usertype'=>1,'field'=>'`photo`,`sex`'));

		        if(!$photo['photo']){
		            if ($photo['sex']==1){
		                $icon	=	$this->config['sy_member_icon'];
		            }else{
		                $icon	=	$this->config['sy_member_iconv'];
		            }
		        }else{
		            $pic		=	$photo['photo'];
		        }
		    }elseif ($arr['type']==4){

				$photo 		= 	$userinfoM->getUserInfo(array('uid'=>$arr['uid']),array('usertype'=>2,'field'=>'logo`'));

		        if(!$photo['logo']){
		            $icon	=	$this->config['sy_unit_icon'];
		        }else{
		            $pic	=	$photo['logo'];
		        }
		    }elseif ($arr['type']==5){

				$photo 		= 	$userinfoM->getUserInfo(array('uid'=>$arr['uid']),array('usertype'=>3,'field'=>'photo`'));
		        if(!$photo['photo']){
		            $icon	=	$this->config['sy_lt_icon'];
		        }else{
		            $pic	=	$photo['photo'];
		        }
		    }
		    $photo['photo']	=	checkpic($pic,$icon);
			$this->yunset('photo',$photo['photo']);

			$this->seo("wap_upload");
			$this->yuntpl(array('wap/uploadimg_userlogo'));
		}else{
			$this->yuntpl(array('wap/uploadimg'));
		}
	}

	//保存上传
	public function uploadimg_save_action(){
	    $token = isset($_POST['token']) ? $_POST['token'] : '';
		if($token == ''){
			echo json_encode(array('status' => -1, 'msg' => '二维码传图出错，请联系网站管理员'));
			exit;
		}
		$arr = $this->checkToken($token);
		if($arr == false || !isset($arr['type']) || !isset($arr['uid']) ){
			echo json_encode(array('status' => -1, 'msg' => '操作超时，请刷新pc端网页二维码重试' . $token));
			exit;
		}

		$path = $this->uploadimg_save_path($arr['type'], $arr['uid']);
		
		echo json_encode($path);exit;

		if($path != ''){
			echo json_encode(array('status' => 1, 'path' => $path));
			exit;
		}else{
			echo json_encode(array('status' => -1, 'msg' => '上传失败，请重试'));
			exit;
		}
	}

	//根据上传图片的不同环境，保存图片路径到对应的地方，及执行其他逻辑
	private function uploadimg_save_path($type, $uid){
		
		$companyM 	= 	$this->MODEL('company');
		$resumeM 	= 	$this->MODEL('resume');
		$UserinfoM	=	$this->MODEL('userinfo');

		$uid 		= 	addslashes($uid);

		switch($type){
			case 1://上传企业营业执照		
				$pic 		= 	$this->upload();
				$path 		= 	$pic;
				
				$cert 		=   $companyM -> getCertInfo(array('uid' => $uid, 'type' => '3'));

				$postData   =   array(
					'status'	=> 	$this -> config['com_cert_status'] == '1' ? 0 : 1,
					'check'		=> 	$path,
					'ctime'		=> 	time()
				);
				if (!empty($_POST['preview'])) {
				    $postData['base']   =  $_POST['preview'];
				}
				
				if (!empty($cert) && is_array($cert) && $cert['ctime']) {
			        
			        $return   =   $companyM -> upCertInfo(array('id'=>intval($cert['id']), 'uid' => $uid), $postData, array('yyzz' => '1', 'usertype' => 2, 'com_name'=>trim($_POST['com_name'])));
			    }else{
					$postData['uid']		=	$uid;
					$postData['type']		=	'3';
					$postData['step']		=	'1';
					$postData['did']		=	$this ->config['did'];
					$postData['usertype']	=	2;
					$postData['com_name']	=	trim($_POST['com_name']);
					
			        $return	=	$companyM -> addCertInfo($postData);
				}
				return $return;

				break;
			case 2://个人上传身份证
			
				$pic 	= $this->upload();
				$path 	= $pic;

				$data	=	array(
				    'usertype'		=>	1,
				    'name'			=>	$_POST['name'],
					'idcard'		=>	$_POST['idcard'],
					'idcard_pic'	=>	$path,
				);
				$return	=	$UserinfoM -> upidcardInfo(array('uid'=>$uid,'wap'=>'1'),$data);
				if($return['errcode']==9){
					$_COOKIE['uid'] 		= 	$uid;
    		    	$_COOKIE['usertype']	= 	1;
				}
				return $return;
			break;
			case 3://个人上传头像
				$return   =  $resumeM -> upPhoto(array('uid'=>$uid),array('utype'=>'user','base'=>$_POST['uimage']));
				return $return;
			break;
			case 4://企业上传logo

				$return   =  $companyM -> upLogo(array('uid'=>$uid),array('utype'=>'user','base'=>$_POST['uimage']));
				return $return;
		    break;
		}
		return '';
	}

	private function upload($path=''){
		
		if($_POST['preview']){
			$upArr   =  array(
                'dir'      =>  'cert',
                'base'     =>  $_POST['preview'],
            );
            
            $result  =  $this -> newupload($upArr);
            
            if (!empty($result['msg'])){
                
                $return['msg']      =  $result['msg'];
                
                echo json_encode(array('msg' => $result['msg']));exit;
                
            }elseif (!empty($result['picurl'])){
                
                return   $result['picurl'];
            }
            
		}else{
			echo json_encode(array('status' => -1, 'msg' => '请上传图片'));exit;
		}
	}
	/**
      * @desc 处理单个图片上传
      * @param file/需上传文件; dir/上传目录; type/上传图片类型; base/需上传base64; preview/pc预览即上传
     */
    private function newupload($data = array('file'=>null,'dir'=>null,'type'=>null,'base'=>null,'preview'=>null)){
          
        $UploadM =	$this->MODEL('upload');
          
        $upArr   =  array(
            'file'     =>  $data['file'],
            'dir'      =>  $data['dir'],
            'type'     =>  $data['type'],
            'base'     =>  $data['base'],
            'preview'  =>  $data['preview']
        );
        $return  =  $UploadM -> newUpload($upArr);
        return $return;
    }
	/**
	1.统计已经上传图片地方、上传文件地方， （搜索Upload_pic）
	有哪些操作，验证了什么，保存了什么信息（文件路径），

	前台：企业上传logo、上传企业执照照片、，个人上传简历照片，

	2.把共用的验证逻辑写到上传类中，
	
	4.写一个手机端拍照/相册上传的页面

	5.写一个生产二维码扫码上传的接口

	6.pc端需要上传的地方，添加扫码上传的二维码展示

	7. to do : 手机端上传成功，让pc端页面触发刷新（参考微信二维码付款，订单页面触发刷新）
	
	*/
}
?>