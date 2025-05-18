<?php

class Sizer {
	
	private $_path;

	private $_count;
	
	private $_paramsArr;
	
	private $_oriImg;
	
	private $_resultImgArr;
	
	private $ossurl;
	
	function Sizer($path, $ossurl = ''){
		
		$this->_path=$path;
		$this->_paramsArr=array();
		$this->_count=0;
		$this->ossurl = $ossurl;
	}
	
	function sizeIt($picdelete='1'){
		
		if(isset($_POST['count'])){
			$this->_count=intval($_POST['count']);
		}
		
		for($i=1;$i<=$this->_count;$i++){
		    
		    $p = array('width'=>$_POST['width'],
		        'height'=>$_POST['height'],
		        'x'=>$_POST['x'],
		        'y'=>$_POST['y'],
		        'scale'=> $_POST['scale']); 
		    
	        if ($this->ossurl != ''){
	            $p['img']  =  str_replace($this->ossurl.'/data', '../data', $_POST['img'.$i]);
	        }else{
	            $p['img']  =  $_POST['img'.$i];
	        }
	        $this->_paramsArr[$i-1]  =  $p;
		}
		if(!file_exists($this->_path)){
			@mkdir($this->_path,0777,true);
		}
		$idx=0;
		foreach($this->_paramsArr as $key=>$value){
			$idx++;
			$parts=explode('.',$value['img']);
			
			$t_name[$idx] = $this->resizeThumbnailImage(
										$this->_path.(time().rand(100,999)).'_'.$idx.'.'.end($parts),
										$value['img'],
										$value['width'],
										$value['height'],
										$value['x'],
										$value['y'],
										$value['scale']);
		}

		
		if($picdelete=='1')
		{
			$deleteImg = $this->_path.end(explode('/',$_POST['img1']));
			$pictype=getimagesize($deleteImg);
			if($pictype[2]=='1' || $pictype[2]=='2' || $pictype[2]=='3')
			{
				@unlink($deleteImg);
			}
		}elseif($picdelete=='2' && $idx=='1'){
			$t_name[2] = $_POST['img1'];
		}

		global $config;
		
		if($config['is_watermark']=='1'){
			
        	
			$this->waterMarkimg($t_name[1]);
		}
		return $t_name;
	}
	
	
	function resizeThumbnailImage(	$thumb_image_name,   
									$image, 			 
									$width, 			 
									$height, 			 
									$start_width, 		 
									$start_height, 		 
									$scale				 
									)
	{
		
		list($imagewidth, $imageheight, $imageType) = getimagesize($image);
		$imageType = image_type_to_mime_type($imageType);
		
		$newImageWidth = ceil($width * $scale);
		$newImageHeight = ceil($height * $scale);
		switch($imageType) {
			case "image/gif":
				$source=imagecreatefromgif($image); 
				break;
			case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
				$source=imagecreatefromjpeg($image); 
				break;
			case "image/png":
			case "image/x-png":
				$source=imagecreatefrompng($image); 
				break;
		}

		if (function_exists("imagecreatetruecolor")){
			$newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
			if(in_array($imageType, array('image/gif', 'image/png', 'image/x-png'))){
				$color = imagecolorallocate($newImage,255,255,255);
				imagecolortransparent($newImage,$color);
				imagefill($newImage,0,0,$color);
			}
			ImageCopyResampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);
		}else{
			$newImage = imagecreate($newImageWidth, $newImageHeight);
			if(in_array($imageType, array('image/gif', 'image/png', 'image/x-png'))){
				$color = imagecolorallocate($newImage,255,255,255);
				imagecolortransparent($newImage,$color);
				imagefill($newImage,0,0,$color);
			}
			ImageCopyResized($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);
		}

		switch($imageType) {
			case "image/gif":
				imagegif($newImage,$thumb_image_name); 
				break;
			case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
				imagejpeg($newImage,$thumb_image_name,90); 
				break;
			case "image/png":
			case "image/x-png":
				imagepng($newImage,$thumb_image_name);  
				break;
		}
		
		chmod($thumb_image_name, 0666);
		return $thumb_image_name;
	}
	 
	function waterMarkimg($destination)
	{	
		global $config;
		$waterimg = APP_PATH.$config['sy_watermark'];
		$image_size = getimagesize($destination);
	    $iinfo=getimagesize($destination,$iinfo); 
        $iinfo2=getimagesize($waterimg,$iinfo2);
        $nimage=imagecreatetruecolor($image_size[0],$image_size[1]);
        $white=imagecolorallocate($nimage,255,255,255);
        $black=imagecolorallocate($nimage,0,0,0);
        $red=imagecolorallocate($nimage,255,0,0);
        imagefill($nimage,0,0,$white);
        
        switch ($iinfo[2])
        {
            case 1:
            $simage =imagecreatefromgif($destination);
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
            default:$this->errorType=5; 
            return;
        }
        imagecopy($nimage,$simage,0,0,0,0,$image_size[0],$image_size[1]);
        
           switch ($iinfo2[2]){
            case 1:
            $simage1 =imagecreatefromgif($waterimg);
            break;
            case 2:
            $simage1 =imagecreatefromjpeg($waterimg);
            break;
            case 3:
            $simage1 =imagecreatefrompng($waterimg);
            break;
            case 6:
            $simage1 =imagecreatefromwbmp($waterimg);
            break;
            default:

            $this->errorType=6; 
            return;
        }
        
		$gifsize=getimagesize($waterimg); 
       switch($config['wmark_position']){ 
		  case 1: 
          imagecopy($nimage,$simage1,0,0,0,0,$gifsize[0],$gifsize[1]);  
          break;
		  case 2: 
          imagecopy($nimage,$simage1,($image_size[0]-$gifsize[0])/2,0,0,0,$gifsize[0],$gifsize[1]); 
          break;
		  case 3: 
		  imagecopy($nimage,$simage1,$image_size[0]-$gifsize[0],0,0,0,$gifsize[0],$gifsize[1]);
		  break;
		  case 4: 
          imagecopy($nimage,$simage1,0,($image_size[1]-$gifsize[1])/2,0,0,$gifsize[0],$gifsize[1]);  
          break;
		  case 5: 
		  imagecopy($nimage,$simage1,($image_size[0]-$gifsize[0])/2, ($image_size[1]-$gifsize[1])/2,0,0,$gifsize[0],$gifsize[1]);
		  break;
		  case 6: 
          imagecopy($nimage,$simage1,$image_size[0]-$gifsize[0],($image_size[1]-$gifsize[1])/2,0,0,$gifsize[0],$gifsize[1]);  
          break;
          case 9: 
          imagecopy($nimage,$simage1,$image_size[0]-$gifsize[0], $image_size[1]-$gifsize[1],0,0,$gifsize[0],$gifsize[1]);
          break;
		  case 7: 
          imagecopy($nimage,$simage1,0,$image_size[1]-$gifsize[1],0,0,$gifsize[0],$gifsize[1]); // 左下
          break;
		  case 8: 
          imagecopy($nimage,$simage1,($image_size[0]-$gifsize[0])/2,$image_size[1]-$gifsize[1],0,0,$gifsize[0],$gifsize[1]);  
          break;
        }
       imagedestroy($simage1); 
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
        imagedestroy($nimage);	
        imagedestroy($simage);	
        return $destination;
	}
}

?>