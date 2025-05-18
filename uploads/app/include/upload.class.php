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
class Upload {
	var $imgname='';
	var $previewname='';			     // 添加这两个变量用于记录，图片全名，以及缩略图全名
	var $upfiledir='/upload';
	var $maxsize='10240';				 //图片最大小KB
	var $addpreview=true;			     //是否生成缩略图
	var $addwatermark=false;		     //$addwatermark=1加水印，其它不加水印
	var $watertype='img';			     //水印类型(txt为文字,img为图片)
    var $waterimg='/images/logo.png';    //水印图片
	var $waterstring='www.phpyun.com';	 //水印字符
	var $ttf='';		                 //默认字体
	var $alpha='50';				     //透明度
    var $position=1;				     //水印位置 1左上,2:上中3:右上4:中左5居中6中右7左下8底中9底右
	var $destination_folder;             //缩略图保存路径，带日期文件夹
	var $errorType; 
	var $pic_type;                       //图片类型
	function Upload($paras=array()){
		if(count($paras)==false){$paras=array('maxsize'=>'10kb','addwatermark'=>true,'watertype'=>'txt/img','waterimg'=>'images/logo.png','waterstring'=>'','ttf'=>'','position'=>'1/2/3/4');}
		foreach($paras as $key => $value){
			$key=strtolower($key);
			$this->$key=$value;
		}
		$this->upfiledir=rtrim($this->upfiledir,'/');
		$this->maxsize=intval($this->maxsize);

		//错误类型：无错误为0
		$this->errorType=0;
	}
	/*
		上传图片
		$file
	*/
	function picture($file,$index=false,$type=''){
		if($index===false){
			if($type){
				$imageInfo	= $this->getInfo($file);
				$fileName=$imageInfo["name"];
				$filetmpname=$file;
			}else{
				$fileName=$file["name"];
				$filetmpname=$file["tmp_name"];
			}
		}else{
			$fileName=$file["name"][$index];
			$filetmpname=$file["tmp_name"][$index];
		}
		$uptypes=$this->pic_type;
		$nameArr=@explode(".",$fileName);
		$num=count($nameArr)-1;
		$this->destination_folder=$this->upfiledir.'/'.date('Ymd').'/'; //上传文件路径
		if(!file_exists($this->upfiledir)){
			@mkdir($this->upfiledir,0777,true);
		}
		if(!file_exists($this->destination_folder)){
			@mkdir($this->destination_folder,0777,true);
		}
		$imgpreviewsize=1/2;    //缩略图比例
		if(@filesize($filetmpname) > ($this->maxsize*1024)){//检查文件大小
			return $this->errorType=1;//文件太大
   		 }
		//in_array($file["type"], $uptypes
		if(!in_array(strtolower($nameArr[$num]),$uptypes)){//检查文件类型
			return $this->errorType=2;//文件类型不符
		}
		if(stripos($nameArr['0'],"<?php")!==false){//检查文件名称是否含有非法字符
			return $this->errorType=6;//非法字符
		}
		
		//$image_size = getimagesize($filetmpname);
		list($width,$height,$ptype,$attr) =getimagesize($filetmpname);
		switch ($ptype)
		{
			case 1:	//gif
				$img = imagecreatefromgif($filetmpname);
				break;
			case 2:	//jpg
				$img = imagecreatefromjpeg($filetmpname);
				break;
			case 3:	//png
				$img = imagecreatefrompng($filetmpname);
				break;
			default:
				return 0;
				break;
		}
		if (!$img){
			
			return $this->errorType=6;//非法字符
		}
	
		$pinfo  =  pathinfo($fileName);
		$ftype  =  $pinfo['extension'];
		$fname  =  $this->generateImgName($ftype);
		$destination  =  $this->destination_folder.$fname;
		if($type){
			if(!copy($filetmpname,$destination)){
				return $this->errorType=5;//复制文件出错
			}
		}else{
			if (file_exists($destination)){
				return $this->errorType=3;//同名文件已经存在了
			}
			// 分辨率限制条件
			$fbl  =  1920;
			if ($height > $fbl || $width > $fbl){
			    // 图片尺寸过大，直接缩略，创建图片
			    $this->imgname = $this->makeThumb($file["tmp_name"], $fbl, $fbl, '', false, $fname);
			    
			    return $this->imgname;
			    
			}elseif(!move_uploaded_file($filetmpname,$destination)){
			    
				return $this->errorType=4;//移动文件出错
			}
		}
		$this->imgname=$destination;
		//if($this->addpreview){
		if($ptype != 1 || $this->addpreview){
			if($this->config['is_picthumb']==1){
                $this->imgname = $this->makeThumb($this->imgname,$width,$height,'',true);
            }
		}
		//}
		if($this->addwatermark){

			$this->makewatermark($this->imgname);
		}
		return ($this->imgname);
	}
    /**
     * base64图片上传
     */
	function imageBase($data){

		preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $result);
		
		$uimage=str_replace($result[1], '', str_replace('#','+',$data));
		
		if(in_array(strtolower($result[2]),array('jpg','png','gif','jpeg'))){
			$new_file = time().rand(1000,9999).".".$result[2];
		}else{
 			$new_file = time().rand(1000,9999).".jpg";
		}
		
		$im = imagecreatefromstring(base64_decode($uimage));
		
		if ($im !== false) {
			
			$this->destination_folder=$this->upfiledir.'/'.date('Ymd').'/'; //上传文件路径
			if(!file_exists($this->upfiledir)){
				@mkdir($this->upfiledir,0777,true);
			}
			if(!file_exists($this->destination_folder)){
				@mkdir($this->destination_folder,0777,true);
			}

			$destination =$this->destination_folder.$new_file;

			$re=file_put_contents($destination, base64_decode($uimage));

			chmod($destination,0777);

			$this->imgname=$destination;

			if($re){

				if(!$this->addpreview && $this->is_picself=='1'){
					//读取图片信息 判断非法字符
					$image_data = fread(fopen($this->imgname, 'r'), filesize($this->imgname));

					if(strpos($image_data,'<?php')!==false){

						$this->addpreview = true;
					}
				}
				if($this->addpreview){
					list($width,$height,$type,$attr) = getimagesize($this->imgname);
					$this->imgname = $this->makeThumb($this->imgname,$width,$height,'',$this->addpreview);
				}
			}else{
				return $this->errorType=4;//写入文件出错
			}
		}else{
			return $this->errorType=6;//不合法的图片内容
		}
		if($this->addwatermark){

			$this->makewatermark($this->imgname);
		}
		
		return $this->imgname;
		 
	}
	//选择生成水印类型(文字或者图片)
	function makewatermark($destination){
        switch($this->watertype){
            case 'txt':   //加水印字符串
           // imagestring($nimage,2,3,$image_size[1]-15,$waterstring,$black);// 画出水平的字符串
		 	return $this->waterMarktxt($destination);
            break;
            case 'img':   //加水印图片
            return $this->waterMarkimg($destination);
		break;
        }
	}
	//==========================================
	// 函数: waterMarkimg($destination,$image_size,$destination_folder)
	// 功能: 给图片加水印
	// 参数: $destination 图片文件名
	// 参数: $image_size 大小数组(包含二个字符串)
	//destination_folder文件存放路径
	// 返回: 1 成功 成功时返回生成的图片路径
	//==========================================
	function waterMarkimg($destination)
	{	
		$image_size = getimagesize($destination);
	    $iinfo=getimagesize($destination,$iinfo);//取得GIF、JPEG、PNG或SWF图形的大小
        $iinfo2=getimagesize($this->waterimg,$iinfo2);//取得GIF、JPEG、PNG或SWF水印图形的大小
        $nimage=imagecreatetruecolor($image_size[0],$image_size[1]);//建立一个新的图形
        $white=imagecolorallocate($nimage,255,255,255);//分配图形的颜色
        $black=imagecolorallocate($nimage,0,0,0);//分配图形的颜色
        $red=imagecolorallocate($nimage,255,0,0);//分配图形的颜色
        imagefill($nimage,0,0,$white);//将图形着色
        
        switch ($iinfo[2])
        {
            case 1:
            $simage =imagecreatefromgif($destination);//从文件或URL建立一个新的图形
            break;
            case 2:
            $simage =imagecreatefromjpeg($destination);
            break;
            case 3:
            $simage =imagecreatefrompng($destination);
            break;
            case 6:
            $simage =imagecreatefromwbmp($destination);
            break;
            default:$this->errorType=5;//不支持的文件类型
            return;
        }
        imagecopy($nimage,$simage,0,0,0,0,$image_size[0],$image_size[1]);
       // imagefilledrectangle($nimage,1,$image_size[1]-15,80,$image_size[1],$white);//建立一个矩形并且填满颜色
        
           switch ($iinfo2[2]){
            case 1:
            $simage1 =imagecreatefromgif($this->waterimg);
            break;
            case 2:
            $simage1 =imagecreatefromjpeg($this->waterimg);
            break;
            case 3:
            $simage1 =imagecreatefrompng($this->waterimg);
            break;
            case 6:
            $simage1 =imagecreatefromwbmp($this->waterimg);
            break;
            default:

            $this->errorType=6;//不支持的水印文件类型
            return;
        }
        
		$gifsize=getimagesize($this->waterimg);//取得GIF、JPEG、PNG或SWF图形的大小
       switch($this->position){//水印位置
		  case 1:// 上左
          imagecopy($nimage,$simage1,0,0,0,0,$gifsize[0],$gifsize[1]); // 左下
          break;
		  case 2:// 上中
          imagecopy($nimage,$simage1,($image_size[0]-$gifsize[0])/2,0,0,0,$gifsize[0],$gifsize[1]); // 左下
          break;
		  case 3:// 右上
		  imagecopy($nimage,$simage1,$image_size[0]-$gifsize[0],0,0,0,$gifsize[0],$gifsize[1]);
		  break;
		  case 4:// 中左
          imagecopy($nimage,$simage1,0,($image_size[1]-$gifsize[1])/2,0,0,$gifsize[0],$gifsize[1]); // 左下
          break;
		  case 5:// 居中
		  imagecopy($nimage,$simage1,($image_size[0]-$gifsize[0])/2, ($image_size[1]-$gifsize[1])/2,0,0,$gifsize[0],$gifsize[1]);
		  break;
		  case 6:// 中右
          imagecopy($nimage,$simage1,$image_size[0]-$gifsize[0],($image_size[1]-$gifsize[1])/2,0,0,$gifsize[0],$gifsize[1]); // 左下
          break;
          case 9:// 右下
          imagecopy($nimage,$simage1,$image_size[0]-$gifsize[0], $image_size[1]-$gifsize[1],0,0,$gifsize[0],$gifsize[1]);
          break;
		  case 7:// 左下
          imagecopy($nimage,$simage1,0,$image_size[1]-$gifsize[1],0,0,$gifsize[0],$gifsize[1]); // 左下
          break;
		  case 8:// 底中
          imagecopy($nimage,$simage1,($image_size[0]-$gifsize[0])/2,$image_size[1]-$gifsize[1],0,0,$gifsize[0],$gifsize[1]); // 左下
          break;
        }
       imagedestroy($simage1);// 结束图形
       switch ($iinfo[2]){
            case 1:
            imagejpeg($nimage, $destination);
            break;
            case 2:
            imagejpeg($nimage, $destination);
            break;
            case 3:
            imagepng($nimage, $destination);
            break;
            case 6:
            imagewbmp($nimage, $destination);
            break;
        }
        //覆盖原上传文件
        imagedestroy($nimage);// 结束图形
        imagedestroy($simage);// 结束图形
        return $destination;
	}
	//==========================================
	// 函数: addwatermark($sourFile, $text)
	// 功能: 给图片加水印
	// 参数: $sourFile 图片文件名
	// 参数: $text 文本数组(包含二个字符串)
	//displayPath文件存放路径
	// 返回: 1 成功 成功时返回生成的图片路径
	//==========================================
	function waterMarktxt($sourFile){
		 $maxWidth  = 300;			//图片最大宽度
		 $maxHeight = 300;			//图片最大高度
		 $toFile	= true;			//是否生成文件
		$imageInfo	= $this->getInfo($sourFile);
		switch ($imageInfo["type"])
		{
			case 1:	//gif
			$newName	= substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".gif";
				break;
			case 2:	//jpg
			$newName	= substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".jpg";
				break;
			case 3:	//png
			$newName	= substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".png";
				break;
			default:
				return 0;
				break;
		}
		switch ($imageInfo["type"])
		{
			case 1:	//gif
				$img = imagecreatefromgif($sourFile);
				break;
			case 2:	//jpg
				$img = imagecreatefromjpeg($sourFile);
				break;
			case 3:	//png
				$img = imagecreatefrompng($sourFile);
				break;
			default:
				return 0;
				break;
		}
		if (!$img) {
			return 0;
		}
		$width  = ($maxWidth > $imageInfo["width"]) ? $imageInfo["width"] : $maxWidth;
		$height = ($maxHeight > $imageInfo["height"]) ? $imageInfo["height"] : $maxHeight;
		$srcW	= $imageInfo["width"];
		$srcH	= $imageInfo["height"];
		if ($srcW * $width > $srcH * $height)
			$height = round($srcH * $width / $srcW);
		else
			$width = round($srcW * $height / $srcH);
		//*
		if (function_exists("imagecreatetruecolor")) //GD2.0.1
		{
			$new = imagecreatetruecolor($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			imagecopyresampled($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}
		else
		{
			$new = imagecreate($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			imagecopyresized($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}
		$black = imagecolorallocate($new, 0, 0, 0);
		//$alpha = imagecolorallocatealpha($new, 230, 230, 230, 40);
		$alpha = imagecolorallocatealpha($new, 230, 230, 230,$this->alpha);
		//$rectW = max(strlen($text[0]),strlen($text[1]))*7;
		imagefilledrectangle($new, 0, $height-26, $width, $height, $alpha);
		$white = imagecolorallocate ($new, 0, 0, 0);
  		imagettftext ($new, 10, 0, 20, $height-8,$white, $this->ttf, $this->waterstring);
		//*/
        if ($toFile)
		{
			if (file_exists($this->destination_folder.$newName))
			 @unlink($this->destination_folder.$newName);
			imagejpeg($new, $this->destination_folder.$newName);
			imagedestroy($new);
			imagedestroy($img);
			return $this->destination_folder.$newName;
		}
		else
		{
			imagejpeg($new);
			imagedestroy($new);
			imagedestroy($img);
		}
	}
    //==========================================
	// 函数: getInfo($file)
	// 功能: 返回图像信息
	// 参数: $file 文件名称
	// 返回: 图片信息数组
	//==========================================
	function getInfo($file)
	{
		//$file=$file;
		$data=getimagesize($file);
		$imageInfo["width"]	= $data[0];
		$imageInfo["height"]= $data[1];
		$imageInfo["type"]	= $data[2];
		$imageInfo["name"]	= basename($file);
		$imageInfo["size"]  = filesize($file);
		return $imageInfo;
	}
	//==========================================
	// 函数: makeThumb($sourFile,$width=80,$height=60)
	// 功能: 生成缩略图(输出到浏览器)
	// 参数: $sourFile 图片源文件
	// 参数: $width 生成缩略图的宽度
	// 参数: $height 生成缩略图的高度
	// 返回: 0 失败 成功时返回生成的图片路径
	//==========================================
	function makeThumb($sourFile,$width=100,$height=100,$newNamePre='',$addpreview=false, $zsNewName = '')
	{
		
		$imageInfo	= $this->getInfo($sourFile);
        
		//原图片尺寸
		$srcW	=  $imageInfo["width"];
		$srcH	=  $imageInfo["height"];
		$isBig  =  false;
		//等比缩放
		if(floor($srcW/$srcH) >= 1){
		    $width  = ($width > $imageInfo["width"]) ? $imageInfo["width"] : $width;
		    $height=round($srcH*$width/$srcW);
		    //根据比例，加上宽度限制
		    if ($height>1080){
		        $height = 1080;
		        $width=round($srcW*$height/$srcH);
		        $isBig  =  true;
		    }
		}else{
		    $height = ($height > $imageInfo["height"]) ? $imageInfo["height"] : $height;
		    if ($height>1080){
		        $height = 1080;
		        $isBig  =  true;
		    }
		    $width=round($srcW*$height/$srcH);
		}
		if ($isBig){
		    // 图片尺寸比较大的需要动态设置内存大小
		    ini_set("memory_limit", "1024M");
		}
		
		if (!empty($zsNewName)){
		    // 图片尺寸过大，直接缩略，创建图片所用文件名，与上传时一致，不需要弄新的文件名
		    $newName  =  $zsNewName;
		    
		}else{
		    
		    switch ($imageInfo["type"])
		    {
		        case 1:	//gif
		            $newName  =  'make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".gif";
		            break;
		        case 2:	//jpg
		            $newName  =  'make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".jpg";
		            break;
		        case 3:	//png
		            $newName  =  'make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".png";
		            break;
		        default:
		            return 0;
		            break;
		    }
		}
		
		switch ($imageInfo["type"])
		{
			case 1:	//gif
				$img = imagecreatefromgif($sourFile);
				imagesavealpha($img,true);//这里很重要 意思是不要丢了$sourePic图像的透明色;
				break;
			case 2:	//jpg
				$img = imagecreatefromjpeg($sourFile);
				break;
			case 3:	//png
				$img = imagecreatefrompng($sourFile);
				imagesavealpha($img,true);//这里很重要 意思是不要丢了$sourePic图像的透明色;
				break;
			default:
				return 0;
				break;
		}
		if (!$img){

			return 0;
		}
		
		//生成图片
		if (function_exists("imagecreatetruecolor")) //GD2.0.1
		{
			$new = imagecreatetruecolor($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){
				/*
				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
				*/
				imagealphablending($new,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
				imagesavealpha($new,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
			}
			ImageCopyResampled($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}else{
			$new = imagecreate($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			ImageCopyResized($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}
		//*/
		if($addpreview){
			$this->previewname=$sourFile;
		}else{
			$this->previewname=$this->destination_folder . $newName;
		}
		
		if (file_exists($this->previewname)){
			@unlink($this->previewname);
		}
		if($imageInfo["type"]==3){
			imagepng($new, $this->previewname);
		}elseif($imageInfo["type"]==1){
			imagepng($new, $this->previewname);
		}else{
			ImageJPEG($new, $this->previewname,100);
		}

		ImageDestroy($new);
		ImageDestroy($img);

		return ($this->previewname);
	}
	function news_makeThumb($sourFile,$width=100,$height=100,$newNamePre=''){
		$imageInfo	= $this->getInfo($sourFile);
		$this->destination_folder=str_replace($imageInfo["name"],"",$sourFile);
		switch ($imageInfo["type"]){
			case 1:	//gif
			$newName	='make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".gif";
				break;
			case 2:	//jpg
			$newName	='make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".jpg";
				break;
			case 3:	//png
			$newName	='make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".png";
				break;
			default:
				return 0;
				break;
		}
		switch ($imageInfo["type"])
		{
			case 1:	//gif
				$img = imagecreatefromgif($sourFile);
				break;
			case 2:	//jpg
				$img = imagecreatefromjpeg($sourFile);
				break;
			case 3:	//png
				$img = imagecreatefrompng($sourFile);
				break;
			default:
				return 0;
				break;
		}
		if (!$img){
			return 0;
		}
		//原图片尺寸
		$srcW	= $imageInfo["width"];
		$srcH	= $imageInfo["height"];
		//等比缩放
		if(floor($srcW/$srcH) >= 1){
			$width  = ($width > $imageInfo["width"]) ? $imageInfo["width"] : $width;
			$height=round($srcH*$width/$srcW);
		}else{
			$height = ($height > $imageInfo["height"]) ? $imageInfo["height"] : $height;
			$width=round($srcW*$height/$srcH);
		}
		//生成图片
		if (function_exists("imagecreatetruecolor")) //GD2.0.1
		{
			//$new = imagecreatetruecolor($width, $height);
			$new = imagecreatetruecolor($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			ImageCopyResampled($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}else{
			$new = imagecreate($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			ImageCopyResized($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}
		//*/
		if (file_exists($this->destination_folder . $newName)){
			unlink($this->destination_folder . $newName);
		}
		
		if($imageInfo["type"]==3){
			imagepng($new, $this->destination_folder . $newName);
		}elseif($imageInfo["type"]==1){
			imagegif($new, $this->destination_folder . $newName);
		}else{
			ImageJPEG($new, $this->destination_folder . $newName,100);
		}

		ImageDestroy($new);
		ImageDestroy($img);
		$this->previewname=$this->destination_folder . $newName;
		return ($this->previewname);
	}
    //判断图片是否需要先缩，后上传.
	function getimage($name,$maxwidth=800,$maxheight=600){
		list($width,$height,$type,$attr) =getimagesize($name);
        $imgname=$name;
		if($width>$maxwidth){
           $imgname=$this->makeThumb($name,$maxwidth,$maxheight);//生成缩略图
           unlink($name);//删除原图
        }
        return $imgname;
	}
	function generateImgName($ftype){
		$imgname='';
		$microtime=@explode(" ",microtime());
		$imgname=ceil($microtime[0]*10000000)+$microtime[1];
		$imgname.=rand(1,9);
		$imgname.='.'.$ftype;
		return ($imgname);
	}
}
?>