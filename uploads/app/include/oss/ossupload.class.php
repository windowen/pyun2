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

require_once 'autoload.php';
require_once 'Config.php';

use OSS\OssClient;
use OSS\Core\OssException;


class ossUpload {
	
	const endpoint = Config::OSS_ENDPOINT;// img.douxu.com 图片服务器别名路径 
    const accessKeyId = Config::OSS_ACCESS_ID; //阿里OSS ACCESS
    const accessKeySecret = Config::OSS_ACCESS_KEY;//阿里OSS ACCESSkey
    const bucket = Config::OSS_TEST_BUCKET; //BUCKET 名称 即目录 或者 盘符
	const userdomain = Config::OSS_USERDOMAIN; //图片配置域名（远程OSS 地址 image.douxu.com）
	
	private $ossClient;
	private $maxsize;
	private $pic_type;
	private $is_picself;
	private $is_picthumb;
		
	
	function __construct($paras = array()){
		
	    $this->maxsize      =  $paras['maxsize'];      // 上传大小
	    $this->pic_type     =  $paras['pic_type'];     // 图片类型
	    $this->is_picself   =  $paras['is_picself'];   // 安全验证
	    $this->is_picthumb  =  $paras['is_picthumb'];  // 强制压缩
	    //实例化 ossClient true为开启CNAME。CNAME是指将自定义域名绑定到存储空间上。
		$this->ossClient    =  new OssClient(self::accessKeyId, self::accessKeySecret, self::endpoint, false);
	}
	/**
     * 上传本地图片至 OSS 
     * $file 本地文件路径
     * $dir  OSS 目录名称
	 * $type 后缀名
	 * $thumb array  crop 将图最长边限制在 100 像素，短边按比例处理
     */
	public function uploadImg($file,$dir='img',$thumb=array()){
		
		//根据本地文件路径获取后缀名
		$imgType=end(@explode('.',$file['name']));
		//判断后缀名是否合法
		if(!$file['tmp_name']){

			$returnMsg['msg'] = '未找到相关图片';

		}elseif(!in_array(strtolower($imgType),$this->pic_type)){
			
			$returnMsg['msg'] = '上传文件类型不符';
		
		}elseif($file['size'] > $this->maxsize * 1024){//判断大小是否超限
			
			$returnMsg['msg'] = '上传图片太大';
		
		}else{
			if($dir){
				$dir.='/'.date('Ymd').'/';
			}

			//重置文件名称 时间戳+随机数
			$newImgName =time().rand(1000,9999).'.'.$imgType;
			
			$object = $dir.$newImgName;//OSS 新文件名称 可带目录 如 user com 。。
			
			$filePath = $file['tmp_name'];//本地上传文件路径
			
			try {
			    $this->ossClient->uploadFile(self::bucket, $object, $filePath);
				//是否需要缩略 按传入长度取最长边长度，短边等比例压缩。
			    if(!empty($thumb['crop'])){
				    // 图片缩放
			        $picurl = '/'.$object.'?x-oss-process=image/resize,l_'.$thumb['crop'];
    			    //将动态图片保存为静态传上OSS
    			    $picInfo = curlPost(self::userdomain.$picurl);
    			    
    			    
    			    $newImgName =time().rand(1000,9999).'.'.$imgType;
    			    
    			    $Newobject = $dir.$newImgName;//OSS 新文件名称 
    			    
    			    $this->ossClient->putObject(self::bucket, $Newobject, $picInfo);
    			    
    			    $this->delObject($object);
    			    
    			    $object  =  $Newobject;
				}
				if (strpos($dir, 'logo') !== false){
				    $returnMsg['picurl'] = $object;
				}else{
				    $returnMsg['picurl'] = './'.$object;
				}
				return $returnMsg;

			} catch (OssException $e) {
				//错误信息输出
				$returnMsg['msg'] = $e->getMessage();
			}
		}
		return $returnMsg;
	}
	

	/**
     * 上传本地文件至 OSS 
     * $file 本地文件路径
     * $dir  OSS 目录名称
     */
	public function uploadDoc($file,$dir='doc'){

		//允许上传文件后缀
		$upTypes=array('doc','docx','rar','zip','txt','xls');
		
		//根据本地文件路径获取后缀名
		$docType=end(@explode('.',$file['name']));
		//判断后缀名是否合法
		if(!$file['tmp_name']){

			$returnMsg['error'] = '未找到相关文件';

		}elseif(!in_array(strtolower($docType),$upTypes)){
			
			$returnMsg['error'] = '仅允许上传 doc,docx,xls,rar,zip,txt格式文件';
		
		}elseif($file['size']>10*1024*1024){//判断大小是否超限 限定10M
			
			$returnMsg['error'] = '上传文件大小不能超过10M';
		
		}else{
			if($dir){
				$dir.='/'.date('Ymd').'/';
			} 
			//重置文件名称 时间戳+随机数
			$newDocName =time().rand(1000,9999).'.'.$docType;
			
			$object = $dir.$newDocName;//OSS 新文件名称 可带目录
			 
			
			$filePath = $file['tmp_name'];//本地上传文件路径
			$options = array();//暂不需要，为空即可
			
			try {
				$this->ossClient->uploadFile(self::bucket, $object, $filePath, $options);
				$returnMsg['docurl'] = '/'.$object;
				
			} catch (OssException $e) {
				//错误信息输出
				$returnMsg['msg'] = $e->getMessage();
				
			}
			return $returnMsg;
		}
		
	}
	/*删除文件
	 *$fileUrl:OSS 文件存储地址 如 img/xxxx.jpg
	 *$fileUrl： array 可传入数组形式 多文件删除

	*/
	public function delObject($fileUrl)
	{
		//批量删除多文件
		if(is_array($fileUrl)){
			$object=array();
			foreach($fileUrl as $value){
			//判断是否存储的缩略图格式 即带@ 符号
				if(strstr($value,'@')){
					$fileUrls = explode('@',$value);
					$object[] = $fileUrls[0];
				}else{
					$object[] = $value;
				}
			}
			if($object&&is_array($object)){
				foreach($object as $k=>$v){
					$v=str_replace('//','',$v);
					if(substr($v,0,1)=='/'){
						$v=substr($v,1);
					}
					$object[$k]=$v;
				}
			}
			try{
				$this->ossClient->deleteObjects(self::bucket, $object);

				return true;

			} catch(OssException $e) {

				//return $returnMsg['error'] = $e->getMessage();//失败具体原因 仅供调试需要
				return false;
				
			}
		}else{
		
			//判断是否存储的缩略图格式 即带@ 符号
			if(strstr($fileUrl,'@')){
				$fileUrls = explode('@',$fileUrl);
				$object = $fileUrls[0];
			}else{
				$object = $fileUrl;
			}
			$object=str_replace('//','',$object);
			if(substr($object,0,1)=='/'){
				$object=substr($object,1);
			}
			try{
				$this->ossClient->deleteObject(self::bucket, $object);

				return true;

			} catch(OssException $e) {

				//return $returnMsg['error'] = $e->getMessage();//失败具体原因 仅供调试需要
				return false;
				
			}
		}
	}
	/*OSS 同项目内文件复制 
	* $from_object:源文件
	* $to_object ：复制后新文件
	*/
	function copyObject($from_object, $to_object)
	{
		$to_object=str_replace('//','',$to_object);
		if(substr($to_object,0,1)=='/'){
			$to_object=substr($to_object,1);
		}
		$options = array();
		try{
			$this->ossClient->copyObject(self::bucket, $from_object, self::bucket, $to_object, $options);


			//return $returnMsg['error'] = 'ok';
			return true;
		} catch(OssException $e) {

			//return $returnMsg['error'] = $e->getMessage();
			return false;
		}
	}
	//图片裁剪 缩放 水印 等
	function cutPic($img,$dir,$x,$y,$width,$height,$scale=1){		
		
		$object = $dir.'/'.date('Ymd').'/'.end(explode('/',$img));

		//图片裁剪
		if($dir){
			$dir.='/'.date('Ymd').'/';
		} 
		$imgType=end(@explode('.',$object));
		//重置文件名称 时间戳+随机数
		$newImgName =time().rand(1000,9999).'.'.$imgType;
	
		$file = $dir.$newImgName;//OSS 新文件名称 可带目录

		$newImageWidth = $width;
		$newImageHeight = $height;
	
		try {
			
			//将动态图片保存为静态传上OSS
			$picInfo = curlPost(self::userdomain.'/'.$object.'?x-oss-process=image/crop,w_'.$width.',h_'.$height.',x_'.$x.',y_'.$y);

			$this->ossClient->putObject(self::bucket, $file, $picInfo);
			
			$returnMsg['picurl'] = './'.$file;

			$this->delObject($object);
			return $returnMsg;
		} catch (OssException $e) {
			
			//错误信息输出
			$returnMsg['error'] = $e->getMessage();
			
			return $returnMsg;
		}
	}
	/**
	 * base64图片上传
	 * @param string $data  base64
	 * @param string $dir   上传位置
	 * @param string $local 网站根目录
	 * @return string
	 */
	function imageBase($data, $dir = 'img', $local = ''){
	    
	    preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $result);
	    
	    $uimage=str_replace($result[1], '', str_replace('#','+',$data));
	    
	    if(in_array(strtolower($result[2]),$this->pic_type)){
	        $imgType  = $result[2];
	    }else{
	        $imgType  =  'jpg';
	    }
	    $new_file = time().rand(1000,9999).'.' . $imgType;
	    
	    $im = imagecreatefromstring(base64_decode($uimage));
	    
	    if ($im !== false) {
	        
	        $objdir  =  $dir.'/'.date('Ymd').'/';
	        $dir    .=  '/';
	        
	        $object = $objdir.$new_file;
	        
	        $destination = $local.$dir.$new_file;
	        
	        if(!file_exists($local.$dir)){
	            @mkdir($local.$dir,0777,true);
	        }
	        
	        $re = file_put_contents($destination, base64_decode($uimage));
	        
	        chmod($destination,0777);
	        
	        if($re){
	            try {
	                
	                $this->ossClient->uploadFile(self::bucket, $object, $destination);
	                // 分辨率限制条件
	                $fbl      =  1280;
	                $imginfo  =  getimagesize($destination);
	                
	                if($imginfo[0] > $fbl || $imginfo[1] > $fbl){
	                    // 图片分辨率过大，需要缩放
	                    $picurl = '/'.$object.'?x-oss-process=image/resize,l_'. $fbl;
	                    //将动态图片保存为静态传上OSS
	                    $picInfo = curlPost(self::userdomain.$picurl);
	                    
	                    $newImgName =time().rand(1000,9999).'.'.$imgType;
	                    
	                    $Newobject = $objdir.$newImgName;//OSS 新文件名称
	                    
	                    $this->ossClient->putObject(self::bucket, $Newobject, $picInfo);
	                    
	                    $this->delObject($object);
	                    
	                    $object  =  $Newobject;
	                }
	                
	                unlink_pic($destination);
	                
	                if (strpos($dir, 'logo') !== false){
	                    $returnMsg['picurl'] = $object;
	                }else{
	                    $returnMsg['picurl'] = './'.$object;
	                }
	                
	            } catch (OssException $e) {
	                //错误信息输出
	                $returnMsg['msg'] = $e->getMessage();
	            }
	        }else{
	            $returnMsg['msg'] = '写入文件出错';
	        }
	    }else{
	        $returnMsg['msg'] = '不合法的图片内容';
	    }
	    
	    return $returnMsg;
	}
	/**
	 * 上传本地图片至 OSS,用户编辑器内图片上传
	 * $file 本地文件路径
	 */
	public function uploadLocalImg($file)
	{
	    if (strpos($file, 'data/upload') !== false){
	        
	        $object  =  strstr($file, 'data/upload');

	        try {
	            
	            $this->ossClient->uploadFile(self::bucket, $object, $file);
	            
	            $returnMsg['picurl'] = './'.$object;
	            
	        } catch (OssException $e) {
	            //错误信息输出
	            $returnMsg['msg'] = $e->getMessage();
	            
	        }
	        return $returnMsg;
	    }
	}
	public function uploadVoice($file,$dir='voice'){

		
		$fileType=end(@explode('.',$file['name']));
		if(!$file['tmp_name']){

			$returnMsg['msg'] = '录音文件不存在，请重试';

		}else{
			if($dir){
				$dir.='/'.date('Ymd').'/';
			} 
			$newVoiceName =time().rand(1000,9999).'.'.$fileType;
			
			$object = $dir.$newVoiceName;
			 
			
			$filePath = $file['tmp_name']; 
			$options = array(); 
			
			try {
				$this->ossClient->uploadFile(self::bucket, $object, $filePath, $options);
				$returnMsg['voiceurl'] = '/'.$object;
				
			} catch (OssException $e) {
				$returnMsg['msg'] = $e->getMessage();
				
			}
			return $returnMsg;
		}
		
	}
	
}
?>