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

require_once 'aliyunemail/aliyun-php-sdk-core/Config.php';
use Dm\Request\V20151123 as Dm;

class aliyun_sendemail 
{  
    public $accesskey;   
    public $accesssecret;   
    public $accountname;   
    public $fromalias;   
    public $tagname;  

    public function __construct($accesskey,$accesssecret,$accountname,$fromalias,$tagname=null){
        $this->accesskey = $accesskey;
        $this->accesssecret = $accesssecret;
        $this->accountname = $accountname;
        $this->fromalias = $fromalias;
        $this->tagname = $tagname;
    }
   
    public function sendemail($email, $title, $content)  
    {  
	    
        $region = 'cn-hangzhou';  
        $accessKey = $this->accesskey;  
        $accessSecret = $this->accesssecret;  
        $accountName = $this->accountname;  
        $fromAlias = $this->fromalias;  
        $tagName = $this->tagname; 
        $iClientProfile = DefaultProfile::getProfile($region, $accessKey,$accessSecret);  
        $client = new DefaultAcsClient($iClientProfile); 

        $request = new Dm\SingleSendMailRequest(); 
        $request->setAccountName($accountName);  
        $request->setFromAlias($fromAlias);  
        $request->setAddressType(1);  
        $request->setTagName($tagName);  
        $request->setReplyToAddress("false");  
        $request->setToAddress($email);   
        $request->setSubject($title);  
           
        $request->setHtmlBody($content);       
         
        try {  
            $response = $client->getAcsResponse($request);  
            if ($response) {  
                return $response;  
            }  
        } catch (ClientException  $e) {  
            return $e->getErrorMessage();
        } catch (ServerException  $e) {   
            return $e->getErrorMessage();
        }  
    }
}  
?>