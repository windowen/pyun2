<?php
//根据m和c获取对应的伪静态路径
function get_seo_url($paramer,$config,$seo,$type=''){
	global $adminDir;
	$rewrite_url = '';
	if($paramer['url']){
		$urNewArr = @explode(',',$paramer['url']);
		foreach($urNewArr as $key=>$value){
			if($value){
				$valueNewArr = @explode(':',$value);
				$paramer[$valueNewArr[0]] = $valueNewArr[1];
			}
		}
		unset($paramer['url']);
	}

	if(!$paramer['m']){
		$paramer['m'] = 'index';
	}
	//是否绑定二级域名
	if($config['sy_'.$type.'domain'] && $type!='index'){
		if($config['sy_'.$type.'ssl']=='1'){
			$protocol = 'https://';
		}else{
			$protocol = 'http://';
		}
	  
		$defaultUrl = $protocol.$config['sy_'.$type.'domain'];
	}elseif(trim($config['sy_'.$type.'dir'])){
		$defaultUrl = $config['sy_weburl']."/".$config['sy_'.$type.'dir']."/";
	}else{
		$defaultUrl = $config['sy_weburl']."/";
	}

	if(trim($config['sy_'.$type.'dir'])){
		//$typeDir = $config['sy_'.$type.'dir'];
        $typeDir=$type;
	}
	
	$url="index.php?";

	foreach($seo as $k=>$v){
		$v = reset($v);
		$urlFileds=array();
		if($v['rewrite_url'] && $v['php_url']){
		    /*后台seo配置中，简历详情页等只配置了参数id，导致传参数uid的链接有问题(/resume/{id}.html)。(此注释不要删除)
		    if ($paramer['uid'] && !$paramer['id'] && strpos($v['rewrite_url'], "{id}")!==false){
		        break;
		    }
		    */
			//拆分url格式
			$vUrl = @explode('?',$v['php_url']);
			$urlArray = array();
			if($vUrl[1]){
				$urlArray = @explode("&",$vUrl[1]);
				foreach($urlArray as $key=>$value){
					$valueArray = @explode('=',$value);
					if($valueArray[0]){
						$urlFileds[$valueArray[0]] = $valueArray[1];
					}
				}
			}

			if($type!=''){//有二级目录

				if($config['sy_'.$type.'dir']){
					$defaultUrl  = str_replace('/'.$config['sy_'.$type.'dir'].'/','/',$defaultUrl);
					$urlDir = array_filter(@explode('/',$vUrl[0]));
					//符合传入的要求
					if(reset($urlDir) == $typeDir){
						if($paramer['c']==$urlFileds['c'] && $paramer['a']==$urlFileds['a']){
							$rewrite_url=$defaultUrl.(substr($v['rewrite_url'],0,1)=='/'?substr($v['rewrite_url'],1):$v['rewrite_url']);
							break;
						}
					}
				}

			}else{
				//没有二级目录 直接匹配M C
				if(!$urlFileds['m']){
					$urlFileds['m'] = 'index';
				}
				if((!$paramer['c'] && $paramer['m']==$urlFileds['m'] && !$urlFileds['c']) || ($paramer['c'] && $paramer['m']==$urlFileds['m'] && $paramer['c']==$urlFileds['c'])){
					$rewrite_url=$type.$v['rewrite_url'];
					break;
				}
			}
		}
	}
	//xxx.com/com_0_0_0_0_0_123.html
	if($rewrite_url){
		foreach($paramer as $key=>$value){
			$rewrite_url = str_replace("{".$key."}",$value,$rewrite_url);
		}
        $model=(($config['sy_'.$m.'_web']==1)&&(trim($config['sy_'.$m.'dir']))?$config['sy_'.$m.'dir']:$paramer['m']);
		//$rewrite_url = str_replace('{page}',"1", $rewrite_url);
		$rewrite_url = preg_replace('/{(.*?)}/',"", $rewrite_url);
        $rewrite_url=str_replace('m='.$paramer['m'],'m='.$model, $rewrite_url);
        $rewrite_url=str_replace('m_'.$paramer['m'],'m_'.$model, $rewrite_url);
        $rewrite_url=str_replace('/'.$paramer['m'].'/','/'.$model.'/', $rewrite_url);

		return $rewrite_url;
	}
	return null;
}
//格式化smarty标签、$_GET、$_POST参数
function formatparamer($paramer,$_smarty_tpl){
    foreach($paramer as $key=>$value){
        $NewUrl=$value;
        if(strstr($NewUrl,'`')){
            $NewValue='';
            $ValueList=explode('`',$NewUrl);
            foreach($ValueList as $k=>$v){
                if(trim($v)!=''){
                    if($k%2==1){
                        if(strstr($v,'$')){
                            $ValueList1=explode('$',$v);
                            $ValueList2=explode('.',$ValueList1[1]);
                            $CurrentValue=null;
                            foreach($ValueList2 as $kk=>$vv){
                                if(trim($vv)!=''){
                                    if($kk==0){
                                        $CurrentValue=$_smarty_tpl->tpl_vars[$vv]->value;
                                    }else{
                                        $CurrentValue=$CurrentValue[$vv];
                                    }
                                }
                            }
                            $NewValue.=$CurrentValue;
                        }
                    }else{
                        $NewValue.=$v;
                    }
                }
            }
            $paramer[$key]=$NewValue;
        }
    }
    return $paramer;
}
//URL生成函数
function get_url($paramer,$config,$seo,$type='',$index='',$_smarty_tpl=''){
    /**新加**/
	global $ModuleName,$adminDir;

    if($_smarty_tpl){
		$paramer=formatparamer($paramer,$_smarty_tpl);
	}

    if($type){
        if($config['sy_'.$type.'domain'] && $type!='index'){
			
			if($config['sy_'.$type.'ssl']=='1'){
				$protocol = 'https://';
			}else{
				$protocol = 'http://';
			}
			
            if(strpos($config['sy_'.$type.'domain'],$protocol)===false){
				$defaultUrl = $protocol.$config['sy_'.$type.'domain'];
			}else{
				$defaultUrl = $config['sy_'.$type.'domain'];
			}
            $defaultUrlRewrite = $defaultUrl;
            unset($paramer['m']);
        }else{
			if(($ModuleName!=$adminDir) || !$ModuleName){
				$typeDir = $config['sy_'.$type.'dir'];
			}
			if($config['sy_web_site'] == '1' && $type == 'wap' && $config['sy_indexdomain']){
				$defaultUrl = $config['sy_indexdomain'];
				$defaultUrlRewrite = $config['sy_indexdomain'];
			}else{
				$defaultUrl = $config['sy_weburl'];
				$defaultUrlRewrite = $config['sy_weburl'];
			}
            
        }
    }else{
        $defaultUrl = $config['sy_weburl'];
        $defaultUrlRewrite = $config['sy_weburl'];
    }

	if($typeDir){
		$defaultUrl .= "/".$typeDir;
        $defaultUrlRewrite .= "/".$typeDir;
		unset($paramer['m']);
	}else{
		if(empty($paramer['m']) && (!$config['sy_'.$type.'domain'] || $type=='index')){
			$m='index';
		}else{
			$m=$paramer['m'];
		}
	}
	
	if(is_array($paramer)){
	    //处理数组，将c移到数组第一个
	    foreach($paramer as $k=>$v){
	        if($k!="m" && $k!="con"){
	            if ($k=="c"){
	                unset($paramer[$k]);
	                $c=array('c'=>$v);
	            }
	        }
	    }
	    if ($c){
	        $paramer = array_merge($c,$paramer);
	    }
		foreach($paramer as $k=>$v){
			if($k!="m" && $k!="con"){
				$paramers[]=$k.":".$v;
			}
		}
	}
    if($index=='admin'){
        global $ModuleName;
        $url=$ModuleName.'/'.$url;
    }
	

    if($config['sy_seo_rewrite'] && $index!='admin' && $index!='member' && $paramer['m']!='ajax' && $paramer['m']!='member'){
		
		$seourl=get_seo_url($paramer,$config,$seo,$type);

        if($seourl){
            return $seourl;
        }

        if($m!='index' && !empty($m)){
            $urlarr['m']=str_replace('_','',str_replace('-','',$m));
        }
        if($paramers){
            $p='';
            foreach($paramers as $v){
                if(!empty($v)){
                    $url_info = @explode(":",$v);
					$urlarr[$url_info[0]]=str_replace('_','',str_replace('-','',$url_info[1]));
                }
            }
        }
        if($urlarr){
            foreach($urlarr as $k=>$v){
                $a[]=$k.'_'.$v;
            }
            $urltemp=@implode('-',$a);
            $url.=$urltemp.'.html';
            $url=$defaultUrlRewrite."/".$url;
        }else{
            $url=$defaultUrlRewrite."/";
        }
    }else{
        if($index=='member'){
            $url=$url.'member/';
        }

        if($index!='admin' && ($config['sy_'.$m.'_web']==1)&&(trim($config['sy_'.$m.'dir']))&&(!trim($config['sy_'.$m.'domain']))){
            $url=$config['sy_'.$m.'dir'].'/'.$url;unset($m);unset($paramer['m']);
        }

        if($m=='index'){
            $url.='index.php';
        }elseif($m=='member'){
            $url.='member/index.php?';
        }else{
			if($m){
				$url.='index.php?'.($m?'&m='.$m:'');
			}
        }

        if($paramers){
            $p='';
            foreach($paramers as $v){
                if(!empty($v)){
					$url_info = @explode(":",$v);
					$p.='&'.$url_info[0].'='.$url_info[1];
                }
            }
            if(strpos($url,'?')){
                $url.=$p;
            }else if($m=='index'){
                $url.='?'.substr($p,1);
            }else{
            	$url.='index.php?'.substr($p,1);
            }
        }
        $url=$defaultUrl.'/'.$url;
    }

	$url=FormatUrl($url);
    return $url;
}
//格式化URL，去除链接中的index.php
function FormatUrl($url){
	
    $url=str_replace('?&','?',$url);
    return $url;
}
//添加关键字
function addkeywords($type,$keyword){
    global $db,$db_config,$config;
    $info = $db->DB_select_once("hot_key","`key_name`='$keyword' AND `type`='$type'");
    if(is_array($info)){
        $db->update_all("hot_key","`num`=`num`+1","`key_name`='$keyword' AND `type`='$type'");
    }else{
        $db->insert_once("hot_key","`key_name`='$keyword',`num`='1',`type`='$type',`check`='0'");
    }
	$uachar = '/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i';
	$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
	
	if(preg_match($uachar, $ua)){

		//生成COOKIE
		if($type==3){
			$key_history_name = 'job_key_history';
		}elseif($type==5){
			$key_history_name = 'resume_key_history';
		}
		$history = array();
		if($_COOKIE[$key_history_name]){
			$history = explode(',',$_COOKIE[$key_history_name]);
			$newHistory = array_reverse($history,true);
			$i=0;
			foreach($newHistory as $k=>$v){
				
				$i++;
				if($i>20){//大于20个 清除老数据
					unset($history[$k]);
				}
			}
		}
		if(empty($history) || !in_array($keyword,$history)){
			$history[] = $keyword;
			SetCookie($key_history_name,@implode(',',$history),time()+864000,"/");
		}
		
	}
	

}
//smarty自定义标签的分页函数
function PageNav($paramer,$get,$table,$where,$Purls,$table2="",$islt='0',$_smarty_tpl,$pagewhere="",$joinwhere=""){

    global $db,$db_config,$config;
	$url=array();
    if($paramer['islt']){
        $islt=$paramer['islt'];
    }
    $page=$get['page']<1?1:$get['page'];
    if($get['c']){
        $urlarr["c"]=$get['c'];
        $Purl['c'] = $get['c'];
    }
    if($get['a']){
        $urlarr["a"]=$get['a'];
        $Purl["a"]	=$get['a'];
    }
    $urlarr["page"]="{{page}}";
    $Purl["page"]="{{page}}";
	if(!empty($Purls)){
		$Purl = array_merge($Purl,$Purls);
	}
	

    if(is_array($Purl)){
        foreach($Purl as $key=>$value){
            if($value!=""){
                $urlarr[$key] = $value;
            }
        }
    }
    if($islt=="2"){
        foreach($urlarr as $k=>$v){
            $url[$k]=$v;
        }
        $pageurl = Url('ask',$url);
    }else if($islt=="3"){
		unset($get['page']);
        foreach($get as $k=>$v){
            $url[]=$k."=".$v;
        }
        $memberurl=@implode("&",$url);
        $pageurl = $config['sy_weburl']."/member/index.php?".$memberurl."&page={{page}} ";
    }elseif($islt=='4'){
        foreach($Purl as $k=>$v){
            if(!trim($v)){
                unset($Purl[$k]);
            }
        }
        $pageurl = Url('wap',$Purl);	
    }elseif($islt=='5'){
        if(($config['sy_news_rewrite']=='2' && $get['nid'])||$Purl['cache']){
        	
            $pageurl = $config['sy_weburl']."/news/".$get['nid']."/{{page}}.html";
        }else{
            $urlarr['page'] = '*page*';
            $pageurl = Url('article',$urlarr,"1");
            $pageurl = str_replace('*page*',"{{page}}", $pageurl);
        }
    }else{
        foreach($Purl as $k=>$v){
            if(!trim($v)){
                unset($Purl[$k]);
            }
        }
		if(in_array($Purl['m'],array('job','resume','company')) && $Purl['c']!='msg'){
			$pageurl = searchListRewrite($Purl,$config);
		}else{
			$pageurl = Url($Purl['m'],$Purl);	
		}
    }
	
	if($table2!=""){
		$list = $db->select_alls($table,$table2,$where,"count(b.id) as count");
		
		$count = $list[0]['count'];
	}else{
		if($table=='company' || $table=='member'){
			$field = '`uid`';
		}else{
			$field = '`id`';
		}
		if($table=="resume_expect"){
          if($joinwhere){
				$joinwhere.=' and ';
			}
			$select = "select ".$select." DISTINCT a.".$field;
			$sql = " from `".$db_config['def'].$table."` a".$pagewhere." where ".$joinwhere.$where;
			if($config['sy_indexpage']>0){
				$isMax = $db->DB_query_all($select.$sql." LIMIT ".$config['sy_indexpage'].",1",'all');
			}
			if(!empty($isMax)){
				$count = $config['sy_indexpage'];
			}else{
				$select = "select DISTINCT a.".$field." ";
				$tall = $db->DB_query_all("select count(*) as num from (".$select.$sql.") aa",'all');
				$count = $tall[0]['num'];
			}
		}else{
			if($config['sy_indexpage']>0){
				$isMax = $db->select_all($table,$where." LIMIT ".$config['sy_indexpage'].",1",$field);
			}

			if(!empty($isMax)){
				$count = $config['sy_indexpage'];
			}else{
				$count = $db->select_num($table,$where);
			}
		}
	}
	if ($count > 0){
	    $pagesize = PageLimit($page,$count,$paramer['limit'],$pageurl,$paramer['notpl'],$_smarty_tpl);
	    
	    if($config['sy_indexpage']>0){
	        
	        if($config['sy_indexpage']<$paramer['limit']){
	            $paramer['limit'] = $config['sy_indexpage'];
	        }
	        $nowPageSize = ($config['sy_indexpage']-$pagesize)>0?($config['sy_indexpage']-$pagesize):0;
	        if($paramer['limit']>$nowPageSize){
	            
	            $paramer['limit'] = $nowPageSize;
	        }
	    }
	    return " limit $pagesize,$paramer[limit]";
	    
	}else {
	    
	    return ' limit 0';
	}
}
//生成分页信息，返回limit信息
function PageLimit($pagenum, $num, $limit, $pageurl, $notpl = false, $_smarty_tpl, $pagenavname = 'pagenav')
{


    global $db, $db_config, $config;
	$pagesize = $pagesize >=0 ? $pagesize:0;

    
    include_once (LIB_PATH . "page.class.php");
    
    $pages     = ceil($num / $limit);
    $pagenum   = $pagenum < 1 ? 1 : $pagenum;
    $pagenum   = $pagenum < $pages ? $pagenum : $pages;
    $ststrsql  = ($pagenum - 1) * $limit;
    
    $page     =  new page($pagenum, $limit, $num, $pageurl, 2, true, $notpl);
    $pagenav  =  $page->numPage(1);
    if ($_smarty_tpl) {
        $_smarty_tpl->tpl_vars['limit'] = new Smarty_Variable();
        $_smarty_tpl->tpl_vars['pages'] = new Smarty_Variable();
        $_smarty_tpl->tpl_vars['total'] = new Smarty_Variable();
        $_smarty_tpl->tpl_vars[$pagenavname] = new Smarty_Variable();
        $_smarty_tpl->tpl_vars['limit']->value = $limit;
        $_smarty_tpl->tpl_vars['pages']->value = $pages;
        $_smarty_tpl->tpl_vars['total']->value = $num;
        $_smarty_tpl->tpl_vars['pagenum']->value = $pagenum;
        $_smarty_tpl->assignByRef('total', $num);
        
        $_smarty_tpl->tpl_vars['totalshow']->value = "<script>document.getElementById('totalshow').innerHTML='" . $num . "'</script>";
        
        if ($pages > 1) {
            $_smarty_tpl->tpl_vars[$pagenavname]->value = $pagenav;
        }
    }
    return $ststrsql;
}
//生成分页信息，返回分页代码
function Page($pagenum,$num,$limit,$pageurl,$notpl=false,$_smarty_tpl,$pagenavname='pagenav',$isadmin=false){
    global $db,$db_config,$config;
    include_once(LIB_PATH."page.class.php");
	
    $pagenum=$pagenum<1?1:$pagenum;
    $ststrsql=($pagenum-1)*$limit;
    $pages=ceil($num/$limit);
    $page = new page($pagenum,$limit,$num,$pageurl,2,true,$notpl);
    $pagenav=$page->numPage(1);
	if($num>$limit){
		if($_smarty_tpl){
			$_smarty_tpl->tpl_vars['limit'] = new Smarty_Variable;
			$_smarty_tpl->tpl_vars['pages'] = new Smarty_Variable;
			$_smarty_tpl->tpl_vars['total'] = new Smarty_Variable;
			$_smarty_tpl->tpl_vars[$pagenavname] = new Smarty_Variable;
			$_smarty_tpl->tpl_vars['limit']->value=$limit;
			$_smarty_tpl->tpl_vars['pages']->value=$pages;
			$_smarty_tpl->tpl_vars['total']->value=$num;
			$_smarty_tpl->tpl_vars['pagenum']->value=$pagenum;
			$_smarty_tpl->assignByRef('total',$num);
			$_smarty_tpl->tpl_vars['totalshow']->value = "<script>document.getElementById('totalshow').innerHTML='".$num."'</script>";
			$_smarty_tpl->tpl_vars[$pagenavname]->value=$pagenav;
		}
		return $pagenav;
	}else{//没有分页的情况下，显示后台统计数据的搜索结果
		if($_smarty_tpl){
			$_smarty_tpl->tpl_vars['total'] = new Smarty_Variable;
			$_smarty_tpl->tpl_vars['total']->value=$num;
			$_smarty_tpl->assignByRef('total',$num);
		}
	}
	
}
//生成URL
function Url($m='index',$paramer=array(),$index=""){

    global $db,$db_config,$config,$seo;
    $paramer['m'] = $m;

    $url  =  get_url($paramer,$config,$seo,$m,$index);
    return $url;
}
//生成图片的URL
function FormatPicUrl($paramer){
    global $config;
    $UploadPath=$paramer['path'];
    if(strstr($UploadPath,'http://') || strstr($UploadPath,'https://')){
        if(!$UploadPath){
            $UploadPath='/'.$config['sy_lt_icon'];
        }else{
            return $UploadPath;
        }
        return $config['sy_ossurl'].$UploadPath;
    }
    switch($paramer['type']){
        case 'ltlogo':
            $UploadPath=trim($UploadPath)?$UploadPath:$config['sy_lt_icon'];
            break;
        case 'comlogo':
            $UploadPath=trim($UploadPath)?$UploadPath:$config['sy_unit_icon'];
            break;
    }
    $PathSplitList=explode('/',$UploadPath);
    switch(trim($PathSplitList[0])){
        case '.':$UploadPath=str_replace('./','/',$UploadPath);break;
        case '..':$UploadPath=str_replace('../','/',$UploadPath);break;
        default:$UploadPath='/'.$UploadPath;break;
    }
    if($paramer['type']){
        if(!$UploadPath){
            switch($paramer['type']){
                case 'ltlogo':
                    $UploadPath='/'.$config['sy_lt_icon'];
                    break;
                case 'comlogo':
                    $UploadPath='/'.$config['sy_unit_icon'];
                    break;
            }
        }
    }
    if(!$UploadPath&&(substr($UploadPath,0,7)=='/upload')){
        $UploadPath='/data'.$UploadPath;
    }
    return checkpic($UploadPath);
}
//获取smarty自定义标签参数
function GetSmarty($arr,$get,$_smarty_tpl=''){
    $arr = str_replace("\"","",$arr);
    $arr = str_replace("'","",$arr);
    if(is_array($arr)){

        foreach($arr as $key=>$value){
            $val = mb_substr($value,0,5);
            if(preg_match ("/auto./i", $value)){
                $nval = str_replace("auto.","",$value);
                $purl[$key] = $get[$nval];
                $arr[$key] = $get[$nval];
                if($get[$nval]!=""){
                	if($key=="keyword"){
						$arr[$key]=trim($get[$key]);
                	}
                    $url.="&".$key."=".$get[$key];
                }
            }
            if(preg_match ("/@./i", $value)){

                $nval = str_replace("@","",$value);
                $nval = str_replace("\"","",$nval);
                $nval = @explode(".",$nval);

                if(is_array($nval)){
                    $smarty_val = $_smarty_tpl->tpl_vars;
                    foreach($nval as $k=>$v){

						if($smarty_val[$v]->value){
							$smarty_val = $smarty_val[$v]->value;
						}else{
							$smarty_val = $smarty_val[$v];
						}
                    }
                    $arr[$key] = $smarty_val;
                }
            }
            if(substr($value,0,5)=='Array'){
                $arr[$key]=str_replace('Array','$_smarty_tpl->tpl_vars',$value);
            }
        }
    }
    return array("purl"=>$purl,"arr"=>$arr);
}
//生成smarty自定义标签的缓存代码
function SmartyOutputStr($smarty,$compiler,$_attr,$tagname,$from,$OutputStr){
    $item = $_attr['item'];
    if (isset($_attr['key'])){
        $key = $_attr['key'];
    } else {
        $key = null;
    }
    $smarty->openTag($compiler, $tagname, array($tagname, $compiler->nocache, $item, $key));
    // maybe nocache because of nocache variables
    $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
    if (isset($_attr['name'])) {
        $name = $_attr['item'];
        $has_name = true;
        $SmartyVarName = '$smarty.foreach.' . trim($name, '\'"') . '.';
    } else {
        $name = null;
        $has_name = false;
    }
    $ItemVarName = '$' . trim($item, '\'"') . '@';
    if ($has_name) {
        $usesSmartyFirst = strpos($compiler->lex->data, $SmartyVarName . 'first') !== false;
        $usesSmartyLast = strpos($compiler->lex->data, $SmartyVarName . 'last') !== false;
        $usesSmartyIndex = strpos($compiler->lex->data, $SmartyVarName . 'index') !== false;
        $usesSmartyIteration = strpos($compiler->lex->data, $SmartyVarName . 'iteration') !== false;
        $usesSmartyShow = strpos($compiler->lex->data, $SmartyVarName . 'show') !== false;
        $usesSmartyTotal = strpos($compiler->lex->data, $SmartyVarName . 'total') !== false;
    } else {
        $usesSmartyFirst = false;
        $usesSmartyLast = false;
        $usesSmartyTotal = false;
        $usesSmartyShow = false;
    }
    $usesPropFirst = $usesSmartyFirst || strpos($compiler->lex->data, $ItemVarName . 'first') !== false;
    $usesPropLast = $usesSmartyLast || strpos($compiler->lex->data, $ItemVarName . 'last') !== false;
    $usesPropIndex = $usesPropFirst || strpos($compiler->lex->data, $ItemVarName . 'index') !== false;
    $usesPropIteration = $usesPropLast || strpos($compiler->lex->data, $ItemVarName . 'iteration') !== false;
    $usesPropShow = strpos($compiler->lex->data, $ItemVarName . 'show') !== false;
    $usesPropTotal = $usesSmartyTotal || $usesSmartyShow || $usesPropShow || $usesPropLast || strpos($compiler->lex->data, $ItemVarName . 'total') !== false;

    $output = "<?php ";
    $output .= " \$_smarty_tpl->tpl_vars[$item] = new Smarty_Variable; \$_smarty_tpl->tpl_vars[$item]->_loop = false;\n";
    if ($key != null) {
        $output .= " \$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable;\n";
    }
    $output .= $OutputStr.$from." = $from; if (!is_array(".$from.") && !is_object(".$from.")) { settype(".$from.", 'array');}\n";
    if ($usesPropTotal) {
        $output .= " \$_smarty_tpl->tpl_vars[$item]->total= \$_smarty_tpl->_count(".$from.");\n";
    }
    if ($usesPropIteration) {
        $output .= " \$_smarty_tpl->tpl_vars[$item]->iteration=0;\n";
    }
    if ($usesPropIndex) {
        $output .= " \$_smarty_tpl->tpl_vars[$item]->index=-1;\n";
    }
    if ($usesPropShow) {
        $output .= " \$_smarty_tpl->tpl_vars[$item]->show = (\$_smarty_tpl->tpl_vars[$item]->total > 0);\n";
    }
    if ($has_name) {
        if ($usesSmartyTotal) {
            $output .= " \$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$name]['total'] = \$_smarty_tpl->tpl_vars[$item]->total;\n";
        }
        if ($usesSmartyIteration) {
            $output .= " \$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$name]['iteration']=0;\n";
        }
        if ($usesSmartyIndex) {
            $output .= " \$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$name]['index']=-1;\n";
        }
        if ($usesSmartyShow) {
            $output .= " \$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$name]['show']=(\$_smarty_tpl->tpl_vars[$item]->total > 0);\n";
        }
    }
    $output .= "foreach (".$from." as \$_smarty_tpl->tpl_vars[$item]->key => \$_smarty_tpl->tpl_vars[$item]->value) {\n\$_smarty_tpl->tpl_vars[$item]->_loop = true;\n";
    if ($key != null) {
        $output .= " \$_smarty_tpl->tpl_vars[$key]->value = \$_smarty_tpl->tpl_vars[$item]->key;\n";
    }
    if ($usesPropIteration) {
        $output .= " \$_smarty_tpl->tpl_vars[$item]->iteration++;\n";
    }
    if ($usesPropIndex) {
        $output .= " \$_smarty_tpl->tpl_vars[$item]->index++;\n";
    }
    if ($usesPropFirst) {
        $output .= " \$_smarty_tpl->tpl_vars[$item]->first = \$_smarty_tpl->tpl_vars[$item]->index === 0;\n";
    }
    if ($usesPropLast) {
        $output .= " \$_smarty_tpl->tpl_vars[$item]->last = \$_smarty_tpl->tpl_vars[$item]->iteration === \$_smarty_tpl->tpl_vars[$item]->total;\n";
    }
    if ($has_name) {
        if ($usesSmartyFirst) {
            $output .= " \$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$name]['first'] = \$_smarty_tpl->tpl_vars[$item]->first;\n";
        }
        if ($usesSmartyIteration) {
            $output .= " \$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$name]['iteration']++;\n";
        }
        if ($usesSmartyIndex) {
            $output .= " \$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$name]['index']++;\n";
        }
        if ($usesSmartyLast) {
            $output .= " \$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$name]['last'] = \$_smarty_tpl->tpl_vars[$item]->last;\n";
        }
    }
    $output .= "?>";
    return $output;
}
//URL路径正规化，缺少增补方法
function FetchMCA2NavUrl($Url){
    if(!strpos($Url,'?')){
        return str_replace('index.php','',$Url.'?m=index&c=index&a=index');
    }else{
        $UrlSplit1=explode('&',substr($Url,strpos($Url,'?')+1));
        $ParamsMCA=array('m'=>'index','c'=>'index','a'=>'index');
        foreach($UrlSplit1 as $k1=>$v1){
            $UrlSplit2=explode('=',$v1);
            $ParamsUrl[$UrlSplit2[0]]=$UrlSplit2[1];
        }
        $ParamsUrl=array_merge($ParamsMCA,$ParamsUrl);
        $ParamsUrlNew=array('m'=>$ParamsUrl['m'],'c'=>$ParamsUrl['c'],'a'=>$ParamsUrl['a']);
        foreach($ParamsUrl as $k1=>$v1){
            if(!in_array($k1,array('m','c','a'))){
                 $ParamsUrlNew[$k1]=$v1;
            }
        }
        $UrlNew=substr($Url,0,strpos($Url,'?')+1);
        foreach($ParamsUrlNew as $k1=>$v1){
            $UrlNew.=$k1.'='.$v1.'&';
        }
        return substr($UrlNew,0,strlen($UrlNew)-1);
    }
}
//判断URL格式化导航样式
function navcalss($menu,$classname){
  global $ModuleName,$config;
	$CurrentAllPath='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$get = $_GET;
	 if($menu['name']=="首页"){
        if($CurrentAllPath==$config['sy_weburl']."/" || $CurrentAllPath==$config['sy_weburl'] ){
            return $classname;
        }
	 }else{
		$PathArr = @explode('?',$menu['url']);
		if(count($PathArr)>1){
			$endpath = end($PathArr);
			$PathFile = @explode("&",$endpath);
			foreach($PathFile as $key=>$value){
				$Vfiles = @explode("=",$value);
				if(is_array($Vfiles) && !empty($Vfiles)){
					$VfilesArr[$Vfiles[0]] = $Vfiles[1];
				}
			}
			if($VfilesArr['a'] || $get['a']){
				if($get['a'] == $VfilesArr['a'] && $get['c'] == $VfilesArr['c'] && $get['m'] == $VfilesArr['m']){
					return $classname;
				}
			}elseif($VfilesArr['c'] || $get['c']){
				if($get['c'] == $VfilesArr['c'] && $get['m'] == $VfilesArr['m']){
					return $classname;
				}
			}elseif($VfilesArr['m'] || $get['m']){
				if($get['m'] == $VfilesArr['m']){
					return $classname;
				}
			}
		}else{
		    $path=str_replace($config['sy_weburl'], '', $CurrentAllPath);
			if(strpos($path,$menu['url'])!==false){
				return $classname;
			}
		}
	 }
}
	//搜索列表URL重写
	function searchListRewrite($paramer,$config){
		
		$get = $_GET;
		if(is_array($paramer)){
			foreach($paramer as $key=>$value){
				if(!in_array($key,array('type','utype','v'))){
					$get[$key] = $value;
				}
			}
		}

		//职位类别
		if($paramer['type']=="job1"){
			$job=$paramer['v'];

		}elseif($paramer['type']=="job1_son" && $paramer['v']!=0){
			$job=$get['job1']."_".$paramer['v'];

		}elseif($paramer['type']=="job_post" && $paramer['v']!=0){
			$job=$get['job1']."_".$get['job1_son']."_".$paramer['v'];

		}else{
			if($get['job1']&&!$get['job1_son']&&!$get['job_post']){
				$job=$get['job1'];
			}elseif($get['job1_son']&&!$get['job_post']){
				$job=$get['job1']."_".$get['job1_son'];
			}elseif($get['job_post']){
				$job=$get['job1']."_".$get['job1_son']."_".$get['job_post'];
			}else{
				$job=0;	
			}	
		}

		//去除职位类别
		if($paramer['untype']=="job1"){
		    $job=0;
			unset($get['job1']);
		}elseif($paramer['untype']=="job1_son"){
		    $job=$get['job1'];
			unset($get['job1_son']);
		}elseif($paramer['untype']=="job_post"){
		    $job=$get['job1']."_".$get['job1_son'];
			unset($get['job_post']);
		}
		
		if($paramer['t']=="index"){//单独传值使用方法
			if($paramer['type']=='job1'){
				$job=$paramer['job1'];
			}elseif($paramer['type']=="job1_son"){
				$job=$paramer['job1']."_".$paramer['job1_son'];
			}elseif($paramer['type']=='job_post'){
				$job=$paramer['job1']."_".$paramer['job1_son']."_".$paramer['job_post'];
			}
		}
		$jobename = '';$enameValue='';
		if(!empty($job)){
			include	PLUS_PATH.'jobename.cache.php';
			if(in_array($paramer['type'],array('job1','job1_son','job_post'))){
				$enameValue	=	$paramer['v'];
			}elseif($get['job_post']){
				$enameValue	=	$get['job_post'];
			}elseif($get['job1_son']){
				$enameValue	=	$get['job1_son'];
			}elseif($get['job1']){
				$enameValue	=	$get['job1'];
			}
			if($job_ename[$enameValue]){
				$jobename = $job_ename[$enameValue];
			}
		}
		
		//城市类别
		if($paramer['type']=="provinceid"){
			$city=$paramer['v'];
		}elseif($paramer['type']=="cityid" && $paramer['v']!=0){
			if($paramer['searchpid']){
				$city=$paramer['searchpid']."_".$paramer['v'];
			}else{
				$city=$get['provinceid']."_".$paramer['v'];
			}
		}elseif($paramer['type']=="three_cityid" && $paramer['v']!=0){
			$city=$get['provinceid']."_".$get['cityid']."_".$paramer['v'];
		}else{
			if($get['provinceid']&&!$get['cityid']&&!$get['three_cityid']){
				$city=$get['provinceid'];
			}elseif($get['cityid']&&!$get['three_cityid']){
				$city=$get['provinceid']."_".$get['cityid'];
			}elseif($get['three_cityid']){
				$city=$get['provinceid']."_".$get['cityid']."_".$get['three_cityid'];
			}else{
				$city=0;	
			}	
		}
		
		//去除城市类别
		if($paramer['untype']=="provinceid"){
		    $city=0;
			unset($get['provinceid']);
		}elseif($paramer['untype']=="cityid"){
		    $city=$get['provinceid'];
			unset($get['cityid']);
		}elseif($paramer['untype']=="three_cityid"){
		    $city=$get['provinceid']."_".$get['cityid'];
			unset($get['three_cityid']);
		}
		if($paramer['type']=="city"){//单独传值
			$city=$paramer['v'];
		}

		$cityename = '';$enameValue='';
		if(!empty($city)){
			include	PLUS_PATH.'cityename.cache.php';
			if(in_array($paramer['type'],array('provinceid','cityid','three_cityid'))){
				$enameValue	=	$paramer['v'];
			}elseif($get['three_cityid']){
				$enameValue	=	$get['three_cityid'];
			}elseif($get['cityid']){
				$enameValue	=	$get['cityid'];
			}elseif($get['provinceid']){
				$enameValue	=	$get['provinceid'];
			}
			if($city_ename[$enameValue]){
				$cityename = $city_ename[$enameValue];
			}
		}
		if ($get['minsalary']||$get['minsalary']){
		    $min=$get['minsalary']?$get['minsalary']:0;
		    $max=$get['maxsalary'];
		    $salary=$min.'_'.$max;
		    $salary=$paramer['untype']=="salary"?0:$salary;
		}else{
		    $salary=$get['salary']?$get['salary']:0;
		    $salary=$paramer['untype']=="salary"?0:$salary;
		}
		$salary=$paramer['type']=="salary"?$paramer['v']:$salary;

		
		
		$hebing=array();
		$hy=$get['hy']?$get['hy']:0;
		$hy=$paramer['untype']=="hy"?0:$hy;
		$hy=$hebing[]=$paramer['type']=="hy"?$paramer['v']:$hy;
		
		$edu=$get['edu']?$get['edu']:0;
		$edu=$paramer['untype']=="edu"?0:$edu;
		$hebing[]=$paramer['type']=="edu"?$paramer['v']:$edu;
		
		$exp=$get['exp']?$get['exp']:0;
		$exp=$paramer['untype']=="exp"?0:$exp;
		$hebing[]=$paramer['type']=="exp"?$paramer['v']:$exp;
		
		$sex=$get['sex']?$get['sex']:0;
		$sex=$paramer['untype']=="sex"?0:$sex;
		$hebing[]=$paramer['type']=="sex"?$paramer['v']:$sex;
		
		$report=$get['report']?$get['report']:0;
		$report=$paramer['untype']=="report"?0:$report;
		$hebing[]=$paramer['type']=="report"?$paramer['v']:$report;
		$uptime=$get['uptime']?$get['uptime']:0;
		$uptime=$paramer['untype']=="uptime"?0:$uptime;
		$hebing[]=$paramer['type']=="uptime"?$paramer['v']:$uptime;

		if($paramer['m']=="resume"){//找人才列表页
			if ($get['minage']||$get['minage']){
				$min=$get['minage']?$get['minage']:0;
				$max=$get['maxage'];
				$age=$min.'_'.$max;
				$age=$paramer['untype']=="age"?0:$age;
			}else{
				$age=$get['age']?$get['age']:0;
				$age=$paramer['untype']=="age"?0:$age;
			}
			$age=$paramer['type']=="age"?$paramer['v']:$age;

			$idcard=$get['idcard']?$get['idcard']:0;
			$idcard=$paramer['untype']=="idcard"?0:$idcard;
			$hebing[]=$paramer['type']=="idcard"?$paramer['v']:$idcard;
			$work=$get['work']?$get['work']:0;
			$work=$paramer['untype']=="work"?0:$work;
			$hebing[]=$paramer['type']=="work"?$paramer['v']:$work;
			$tag=$get['tag']?$get['tag']:0;
			$tag=$paramer['untype']=="tag"?0:$tag;
			$hebing[]=$paramer['type']=="tag"?$paramer['v']:$tag;
			$rtype=$get['rtype']?$get['rtype']:0;
			$rtype=$paramer['untype']=="rtype"?0:$rtype;
			$hebing[]=$paramer['type']=="rtype"?$paramer['v']:$rtype;

			$type=$get['type']?$get['type']:0;
			$type=$paramer['untype']=="type"?0:$type;
			$hebing[]=$paramer['type']=="type"?$paramer['v']:$type;
			
			$integrity=$get['integrity']?$get['integrity']:0;
			$integrity=$paramer['untype']=="integrity"?0:$integrity;
			$hebing[]=$paramer['type']=="integrity"?$paramer['v']:$integrity;
		}elseif($paramer['m']=="company"){//公司列表页
			$mun=$get['mun']?$get['mun']:0;
			$mun=$paramer['untype']=="mun"?0:$mun;
			$mun=$paramer['type']=="mun"?$paramer['v']:$mun;
			
			$welfare=$get['welfare']?$get['welfare']:0;
			$welfare=$paramer['untype']=="welfare"?0:$welfare;
			$welfare=$paramer['type']=="welfare"?$paramer['v']:$welfare;
			
			$pr=$get['pr']?$get['pr']:0;
			$pr=$paramer['untype']=="pr"?0:$pr;
			$pr=$paramer['type']=="pr"?$paramer['v']:$pr;
			
			$rec=$get['rec']?$get['rec']:0;
			$rec=$paramer['untype']=="rec"?0:$rec;
			$rec=$paramer['type']=="rec"?$paramer['v']:$rec;
		}elseif($paramer['m']=="part"){//兼职列表页
			$part_type=$get['part_type']?$get['part_type']:0;
			$part_type=$paramer['untype']=="part_type"?0:$part_type;
			$part_type=$paramer['type']=="part_type"?$paramer['v']:$part_type;
			
			$cycle=$get['cycle']?$get['cycle']:0;
			$cycle=$paramer['untype']=="cycle"?0:$cycle;
			$cycle=$paramer['type']=="cycle"?$paramer['v']:$cycle;
		}elseif($paramer['m']=="tiny"){//普工列表页
			$exp=$get['exp']?$get['exp']:0;
			$exp=$paramer['untype']=="exp"?0:$exp;
			$exp=$paramer['type']=="exp"?$paramer['v']:$exp;
			
			$sex=$get['sex']?$get['sex']:0;
			$sex=$paramer['untype']=="sex"?0:$sex;
			$sex=$paramer['type']=="sex"?$paramer['v']:$sex;
			
			$add_time=$get['add_time']?$get['add_time']:0;
			$add_time=$paramer['untype']=="add_time"?0:$add_time;
			$add_time=$paramer['type']=="add_time"?$paramer['v']:$add_time;
		}elseif($paramer['m']=="once"){//店铺列表页
			$add_time=$get['add_time']?$get['add_time']:0;
			$add_time=$paramer['untype']=="add_time"?0:$add_time;
			$add_time=$paramer['type']=="add_time"?$paramer['v']:$add_time;
		}elseif($paramer['m']=="redeem"){//商城列表
			$nid=$get['nid']?$get['nid']:0;
			$nid=$paramer['untype']=="nid"?0:$nid;
			$nid=$paramer['type']=="nid"?$paramer['v']:$nid;
			//商品子类
			$tnid=$get['tnid']?$get['tnid']:0;
			if($get['tnid']&&$paramer['untype']=="nid"||$paramer['untype']=="tnid"){
				$tnid=0;
			}
			$tnid=$paramer['type']=="tnid"?$paramer['v']:$tnid;
			$intinfo=$get['intinfo']?$get['intinfo']:0;
			$intinfo=$paramer['untype']=="intinfo"?0:$intinfo;
			$intinfo=$paramer['type']=="intinfo"?str_replace('-','_',$paramer['v']):$intinfo;
		}else{//找工作列表页
			$cert=$get['cert']?$get['cert']:0;
			$cert=$paramer['untype']=="cert"?0:$cert;
			$cert=$paramer['type']=="cert"?$paramer['v']:$cert;
			if($cert==0){
			    $certd='';
			    $certs='-0';
			}else{
			    $certd="&cert=".$cert;
			    $certs="-".$cert;
			}
			$welfare=$get['welfare']?$get['welfare']:0;
			$welfare=$paramer['untype']=="welfare"?0:$welfare;
			$hebing[]=$paramer['type']=="welfare"?$paramer['v']:$welfare;
			$jobtype=$get['jobtype']?$get['jobtype']:0;
			$jobtype=$paramer['untype']=="jobtype"?0:$jobtype;
			$hebing[]=$paramer['type']=="jobtype"?$paramer['v']:$jobtype;
		}
		
		$keyword=$get['keyword']?$get['keyword']:0;
		$keyword=$paramer['untype']=="keyword"?0:$keyword;
		$keyword=$paramer['type']=="keyword"?$paramer['v']:$keyword;
		$keyword=urlencode($keyword);
		
		$hebings=implode("_",$hebing);//合并多个参数
		
		$tp=$get['tp']?$get['tp']:0;
		$tp=$paramer['type']=="tp"?$paramer['v']:$tp;
		
		$order=$get['order']?$get['order']:0;
		$order=$paramer['type']=="order"?$paramer['v']:$order;

		//$page = $get['page']?$get['page']:1;
		$page = 1;
		$page =$paramer['page']?$paramer['page']:$page;
		$url	= '';
		$urln	=	array();
		if($config['sy_seo_rewrite']==1){

			//职位、城市设置目录名称后
			if($jobename || $cityename){
				if($cityename){
					$url=$cityename.'/';
				}elseif (!empty($city)){
				    $urln[]='city='.$city;
				}
				
				if($jobename){
					if($paramer['m'] == 'resume'){
						$url.='qz'.$jobename.'/';
					}else{
						$url.='zp'.$jobename.'/';
					}
				}elseif (!empty($job)){
				    $urln[]='job='.$job;
				}
				
				if($salary)
					$urln[]='salary='.$salary;
				if($paramer['m'] == 'resume' && $age)
					$urln[]='age='.$age;
				if($order)
					$urln[]='order='.$order;
				if($keyword)
					$urln[]='keyword='.$keyword;
				if($config['sy_default_comclass']=='1'&&(!$paramer['m']||$paramer['m']=='job')){
					$sdc="c=search&";
				}elseif ($config['sy_default_userclass']=='1'&&$paramer['m']=='resume'){
					$sdc="c=search&";
				}
				
				if(!empty($urln)){
					$url.="index.php?".$sdc.implode('&',$urln)."&all=".$hebings."&tp=".$tp.$certd;
				}else{
					
					if(($hebings!='0_0_0_0_0_0_0_0' && $hebings!='0_0_0_0_0_0_0_0_0_0') || $tp || $certd || $page!='1'){
						$url.="index.php?".$sdc."all=".$hebings."&tp=".$tp.$certd;
					}
					
				}
				if($page!='1'){
					$url .='&page='.$page;
				}
			}else{
				if ($keyword){
					if($paramer['m'] == 'resume'){
						$url="list/".$job."-".$city."-".$salary."-".$age."-".$hebings."-".$tp.$certs."-".$order."-".$page.".html?".$keyword;
					}else{
						$url="list/".$job."-".$city."-".$salary."-".$hebings."-".$tp.$certs."-".$order."-".$page.".html?".$keyword;
					}
				}else{
					if($paramer['m'] == 'resume'){
						$url="list/".$job."-".$city."-".$salary."-".$age."-".$hebings."-".$tp.$certs."-".$order."-".$page.".html";
					}else{
						$url="list/".$job."-".$city."-".$salary."-".$hebings."-".$tp.$certs."-".$order."-".$page.".html";
					}
				}
			}
		    
		}else{
			if($job)
			    $urln[]='job='.$job;
			if($city)
			    $urln[]='city='.$city;
			if($salary)
			    $urln[]='salary='.$salary;
			if($age)
			    $urln[]='age='.$age;
			if($order)
			    $urln[]='order='.$order;
			if($keyword)
			    $urln[]='keyword='.$keyword;

		    if($config['sy_default_comclass']=='1'&&(!$paramer['m']||$paramer['m']=='job')){
		        $sdc="c=search&";
		    }elseif ($config['sy_default_userclass']=='1'&&$paramer['m']=='resume'){
		        $sdc="c=search&";
		    }
		    if(!empty($urln)){
				$url="index.php?".$sdc.implode('&',$urln)."&all=".$hebings."&tp=".$tp.$certd;
			}else{
				$url="index.php?".$sdc."all=".$hebings."&tp=".$tp.$certd;
			}
			if($page){
			    $url .='&page='.$page;
			}
		}
		if($paramer['m']=="company"){
			if($config['sy_seo_rewrite']==1){
				$url="list/".$city."-".$mun."-".$welfare."-".$hy."-".$pr."-".$rec."-".$keyword."-".$page.".html";
			}else{
				if($city)
				 $urln[]='cityid='.$city;
				 
				if($mun)
				 $urln[]='mun='.$mun;
				
				if($welfare)
					$urln[]='welfare='.$welfare;
				 
				 if($hy)
				 $urln[]='hy='.$hy;
				 
				 if($pr)
				 $urln[]='pr='.$pr;
				 
				 if($rec)
				 $urln[]='rec='.$rec;
				 
				 if($keyword)
				 $urln[]='keyword='.$keyword;

				 if($page){
					$urln[]='page='.$page;
				 }
				 if(!empty($urln)){
					$url="index.php?".implode('&',$urln);
				 }
				
			}
		}
		if($paramer['m']=="part"){
		    if($config['sy_seo_rewrite']==1){
		        if ($keyword){
		            $url="list/".$city."-".$part_type."-".$cycle."-".$order."-".$page.".html?".$keyword;
		        }else{
		            $url="list/".$city."-".$part_type."-".$cycle."-".$order."-".$page.".html";
		        }
		    }else{
		        if($city)
		            $urln[]='city='.$city;
		        	
		        if($part_type)
		            $urln[]='part_type='.$part_type;
		        	
		        if($cycle)
		            $urln[]='cycle='.$cycle;
		        	
		        if($order)
		            $urln[]='order='.$order;
		        	
		        if($keyword)
		            $urln[]='keyword='.$keyword;
		
		        if($page){
		            $urln[]='page='.$page;
		        }
		        if(!empty($urln)){
		            $url="index.php?".implode('&',$urln);
		        }
		
		    }
		}
		if($paramer['m']=="redeem"){
		    if($config['sy_seo_rewrite']==1){
		        if ($keyword){
		            $url="list/".$intinfo."-".$nid."-".$tnid."-".$page.".html?".$keyword;
		        }else{
		            $url="list/".$intinfo."-".$nid."-".$tnid."-".$page.".html";
		        }
		    }else{
		        if($intinfo)
		            $urln[]='intinfo='.$intinfo;
		        	
		        if($nid)
		            $urln[]='nid='.$nid;
		        if($tnid)
					$urln[]='tnid='.$tnid;
		        if($order)
		            $urln[]='order='.$order;
		        	
		        if($keyword)
		            $urln[]='keyword='.$keyword;
		
		        if($page){
		            $urln[]='page='.$page;
		        }
		        if(!empty($urln)){
		            $url="index.php?c=".$paramer['c']."&".implode('&',$urln);
		        }
		
		    }
		}
		if($paramer['m']=="tiny"){
		    if($config['sy_seo_rewrite']==1){
		        if ($keyword){
		            $url="list/".$city."-".$sex."-".$exp."-".$add_time."-".$page.".html?".$keyword;
		        }else{
		            $url="list/".$city."-".$sex."-".$exp."-".$add_time."-".$page.".html";
		        }
		    }else{
		        if($city)
		            $urln[]='city='.$city;
		        	
		        if($sex)
		            $urln[]='sex='.$sex;
		        	
		        if($exp)
		            $urln[]='exp='.$exp;
		        	
		        if($add_time)
		            $urln[]='add_time='.$add_time;
		        	
		        if($keyword)
		            $urln[]='keyword='.$keyword;
		
		        if($page){
		            $urln[]='page='.$page;
		        }
		        if(!empty($urln)){
		            $url="index.php?".implode('&',$urln);
		        }
		
		    }
		}
		if($paramer['m']=="once"){
		    if($config['sy_seo_rewrite']==1){
		        if ($keyword){
		            $url="list/".$city."-".$add_time."-".$page.".html?".$keyword;
		        }else{
		            $url="list/".$city."-".$add_time."-".$page.".html";
		        }
		    }else{
		        if($city)
		            $urln[]='city='.$city;
		        	
		        if($add_time)
		            $urln[]='add_time='.$add_time;
		        	
		        if($keyword)
		            $urln[]='keyword='.$keyword;
		
		        if($page){
		            $urln[]='page='.$page;
		        }
		        if(!empty($urln)){
		            $url="index.php?".implode('&',$urln);
		        }
		
		    }
		}
		$m=$paramer['m']?$paramer['m']:"job";
		unset($paramer);
		return $config['sy_weburl'].'/'.$m.'/'.$url;
	}
?>