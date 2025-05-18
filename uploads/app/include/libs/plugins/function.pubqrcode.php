<?php
function smarty_function_pubqrcode($paramer,&$smarty){
		global $config,$db_config,$db,$views;
		
		if($paramer['totype'] == 'wap'){//移动端二维码
			$wapUrl = Url('wap');
			if( isset($paramer['toid']) && $_GET['toid'] != ''){
				$wapUrl = Url('wap',array('c'=>$paramer['toc'],'a'=>$paramer['toa'],'id'=>(int)$paramer['toid']));
			}
			
			$qrcode = Url('ajax',array('c'=>'pubqrcode','toc'=>$paramer['toc'],'toa'=>$paramer['toa'],'toid'=>(int)$paramer['toid']));

		}elseif($paramer['totype'] == 'weixin'){//微信公众号带参数二维码
		
			if($paramer['toc'] == 'job'){
				$scene_str = "jobid_".$paramer['toid'];
			}elseif($paramer['toc'] == 'resume'){
				$scene_str = "resumeid_".$paramer['toid'];
			}elseif($paramer['toc'] == 'company'){
				$scene_str = "companyid_".$paramer['toid'];
			}
            
            include_once(APP_PATH.'app/model/weixin.model.php');
          
            $WxM  =  new weixin_model($db,$db_config['def']);
            
			$qrcode = $WxM->pubWxQrcode($scene_str);
		    
		

		}
		
		return $qrcode;

	}
?>