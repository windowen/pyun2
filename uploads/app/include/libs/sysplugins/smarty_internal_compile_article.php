<?php
class Smarty_Internal_Compile_Article extends Smarty_Internal_CompileBase{
  public $required_attributes = array('item');
  public $optional_attributes = array('name', 'key', 't_len', 'rec', 'limit', 'pic', 'd_len', 'type', 'urlstatic','print','order','ispage','nid','islt','cache','keyword');
  public $shorttag_order = array('from', 'item', 'key', 'name');

  public function compile($args, $compiler, $parameter){
    $_attr = $this->getAttributes($compiler, $args);

    $from = $_attr['from'];
    $item = $_attr['item'];
    $name = $_attr['item'];
    $name=str_replace('\'','',$name);
    $name=$name?$name:'list';$name='$'.$name;
    if (!strncmp("\$_smarty_tpl->tpl_vars[$item]", $from, strlen($item) + 24)) {
      $compiler->trigger_template_error("item variable {$item} may not be the same variable as at 'from'", $compiler->lex->taglineno);
    }

    //自定义标签 START
    //数据库操作
    $OutputStr = '
		global $db,$db_config,$config;
		include PLUS_PATH.\'/group.cache.php\';'
		.$name.'=	array();
		$rs		=	null;
		$nids	=	null;

		$paramer	=	'.ArrayToString($_attr,true).';

		$ParamerArr	=	GetSmarty($paramer,$_GET,$_smarty_tpl);

		$paramer	=	$ParamerArr[\'arr\'];

		$Purl		=	$ParamerArr[\'purl\'];

		if($paramer[cache]){

			$Purl	=	"{{page}}.html";
		}

		global $ModuleName;

		if(!$Purl["m"]){
			$Purl["m"]=$ModuleName;
		}

		$where=1;

		$where .=" and (`starttime`<=".time()." or `starttime`=0 or `starttime` is null)";

        $where .=" and (`endtime`>".time()." or `endtime`=0 or `endtime` is null)";

		if($config[\'did\']){

			$where	.=	" and (`did`=\'".$config[\'did\']."\' or `did`=-1)";
		}else{
			
			$where	.=	" and (`did`=-1 OR `did`=0 OR did=\'\')";
		}

		include PLUS_PATH."/group.cache.php";
		if($paramer[\'nid\']){
			$nid_s = @explode(\',\',$paramer[nid]);
			foreach($nid_s as $v){
				if($group_type[$v]){
					$paramer[nid] = $paramer[nid].",".@implode(\',\',$group_type[$v]);
				}
			}
		}

		if($paramer[\'nid\']!="" && $paramer[\'nid\']!=0){
			$where .=" AND `nid` in ($paramer[nid])";
			$nids = @explode(\',\',$paramer[\'nid\']);
			$paramer[\'nid\']=$paramer[\'nid\'];
		}else if($paramer[\'rec\']!=""){
			$nids=array();
			if(is_array($group_rec)){
				foreach($group_rec as $key=>$value){
					if($key<=2){
						$nids[]=$value;
					}
				}
				$paramer[nid]=@implode(\',\',$nids);
			}
		}

		if($paramer[\'type\']){
			$type = str_replace("\"","",$paramer[type]);
			$type_arr =	@explode(",",$type);
			if(is_array($type_arr) && !empty($type_arr)){
				foreach($type_arr as $key=>$value){
					$where .=" AND FIND_IN_SET(\'".$value."\',`describe`)";
					if(count($nids)>0){
						$picwhere .=" AND FIND_IN_SET(\'".$value."\',`describe`)";
					}
				}
			}
		}

		//拼接补充SQL条件
		if($paramer[\'pic\']!=""){
			$where .=" AND `newsphoto`<>\'\'";
		}

		 //新闻搜索
		if($paramer[\'keyword\']!=""){
			$where .=" AND `title` LIKE \'%".$paramer[keyword]."%\'";
		}

		//拼接查询条数
		if(intval($paramer[\'limit\'])>0){
			$limit = intval($paramer[\'limit\']);
			$limit = " limit ".$limit;
		}

		if($paramer[\'ispage\']){
			if($Purl["m"]=="wap"){
				$limit = PageNav($paramer,$_GET,"news_base",$where,$Purl,"","6",$_smarty_tpl);
			}else{
				$limit = PageNav($paramer,$_GET,"news_base",$where,$Purl,"","5",$_smarty_tpl);
			}
		}

		//拼接字段排序
		if($paramer[\'order\']!=""){
			$where .=" ORDER BY $paramer[order]";
		}else{
			$where .=" ORDER BY `datetime`";
		}

		//排序方式默认倒序
		if($paramer[\'sort\']){
			$where.=" ".$paramer[sort];
		}else{
			$where.=" DESC";
		}

		//多类别新闻查找
		if(!intval($paramer[\'ispage\']) && count($nids)>0){

			$nidArr = @explode(\',\',$paramer[nid]);
			$rsnids = \'\';
			if(is_array($group_type)){
				foreach($group_type as $key=>$value){
					if(in_array($key,$nidArr)){
						if(is_array($value)){
							foreach($value as $v){
								$rsnids[$v] = $key;
								$nidArr[] = $v;
							}
						}
					}
				}
			}

			$where = " `nid` IN (".@implode(\',\',$nidArr).")";

			if($config[\'did\']){
				$where.=" and `did`=\'".$config[\'did\']."\'";
			}

			//查询带图新闻
			if($paramer[\'pic\']){
				if(!$paramer[\'piclimit\']){
					$piclimit = 1;
				}else{
					$piclimit = $paramer[\'piclimit\'];
				}

				$db->query("set @f=0,@n=0");

				$query = $db->query("select * from (select id,title,color,datetime,description,newsphoto,@n:=if(@f=nid,@n:=@n+1,1) as aid,@f:=nid as nid from $db_config[def]news_base  WHERE ".$where." AND `newsphoto` <>\'\'  order by nid asc,datetime desc) a where aid <=".$piclimit);

				$conque = $db->select_all("news_content","1 order by nbid desc".$limit);

				foreach($conque as $cv){
					$newcon[$cv[nbid]]=$cv;
				}
				while($rs = $db->fetch_array($query)){

					if($rsnids[$rs[\'nid\']]){
						$rs[\'nid\'] = $rsnids[$rs[\'nid\']];
					}

					//处理标题长度
					if(intval($paramer[t_len])>0){
						$len = intval($paramer[t_len]);
						$rs[title] = mb_substr($rs[title],0,$len,"utf-8");
					}

					if($rs[color]){
						$rs[title] = "<font color=\'".$rs[color]."\'>".$rs[title]."</font>";
					}

					//处理描述内容长度
					if(intval($paramer[d_len])>0){
						$len = intval($paramer[d_len]);
						$rs[description] = mb_substr($rs[description],0,$len,"utf-8");
					}

					$rs[\'name\'] = $group_name[$rs[\'nid\']];

					//构建资讯静态链接
					if($config[sy_news_rewrite]=="2"){
						$rs["url"]=$config[\'sy_weburl\']."/news/".date("Ymd",$rs["datetime"])."/".$rs[id].".html";
					}else{
						$rs["url"] = Url("article",array("c"=>"show","id"=>$rs[id]),"1");
					}

					if(mb_substr($rs[newsphoto],0,4)=="http"){
						$rs["picurl"]=$rs[newsphoto];
					}else{
						if($rs[\'newsphoto\']==""){
							$content=str_replace(array(\'"\',"\'"),array("",""),$newcon[$rs[id]]["content"]);
							preg_match_all("/<img[^>]+src=(.*?)\s[^>]+>/im",$content,$res);
							$str=str_replace("\\\\","",$res[1][0]);
							if($str){
								$rs[newsphoto]=".".$str;
							}
						}
						$nopic=$config[sy_weburl]."/app/template/".$config[style]."/images/nopic.gif";

						$rs["picurl"] = checkpic($rs[\'newsphoto\'],$nopic);

					}

					$rs[time]=date("Y-m-d",$rs[datetime]);
					$rs[\'datetime\']=date("m-d",$rs[datetime]);
					if(count('.$name.'[$rs[\'nid\']][\'pic\'])<$piclimit){
					  '.$name.'[$rs[\'nid\']][\'pic\'][] = $rs;
					}
				}//end while
			}

			$db->query("set @f=0,@n=0");
			$query = $db->query("select * from (select id,title,datetime,color,description,newsphoto,@n:=if(@f=nid,@n:=@n+1,1) as aid,@f:=nid as nid from $db_config[def]news_base  WHERE ".$where." order by nid asc,datetime desc) a where aid <=$paramer[limit]");

			while($rs = $db->fetch_array($query)){
				if($rsnids[$rs[\'nid\']]){
					$rs[\'nid\'] = $rsnids[$rs[\'nid\']];
				}

				//处理标题长度
				if(intval($paramer[t_len])>0){
					$len = intval($paramer[t_len]);
					$rs[title] = mb_substr($rs[title],0,$len,"utf-8");
				}

				if($rs[color]){
					$rs[title] = "<font color=\'".$rs[color]."\'>".$rs[title]."</font>";
				}

				//处理描述内容长度
				if(intval($paramer[d_len])>0){
					$len = intval($paramer[d_len]);
					$rs[description] = mb_substr($rs[description],0,$len,"utf-8");
				}

				//获取所属类别名称
				$rs[\'name\'] = $group_name[$rs[\'nid\']];

				//构建资讯静态链接
				if($config[sy_news_rewrite]=="2"){
					$rs["url"]=$config[\'sy_weburl\']."/news/".date("Ymd",$rs["datetime"])."/".$rs[id].".html";
				}else{
					$rs["url"] = Url("article",array("c"=>"show","id"=>$rs[id]),"1");
				}

				if(mb_substr($rs[newsphoto],0,4)=="http"){
					$rs["picurl"]=$rs[newsphoto];
				}else{
					if($rs[\'newsphoto\']==""){
						$rs["picurl"] = $config[sy_weburl]."/app/template/".$config[style]."/images/nopic.gif";
					}else{
						$rs["picurl"] = checkpic($rs[\'newsphoto\']);
					}
				}

				$rs[time]=date("Y-m-d",$rs[datetime]);
				$rs[datetime]=date("m-d",$rs[datetime]);
				if(count('.$name.'[$rs[\'nid\']][\'arclist\'])<$paramer[limit]){
					'.$name.'[$rs[\'nid\']][\'arclist\'][] = $rs;
				}
			}//end while

		}//end if(!intval($paramer[\'ispage\']) && count($nids)>0)
		else{
			$query = $db->query("SELECT * FROM `$db_config[def]news_base` WHERE ".$where.$limit);

			while($rs = $db->fetch_array($query)){
				//处理标题长度
				if(intval($paramer[t_len])>0){
					$len = intval($paramer[t_len]);
					$rs[title] = mb_substr($rs[title],0,$len,"utf-8");
				}

				if($rs[color]){
					$rs[title] = "<font color=\'".$rs[color]."\'>".$rs[title]."</font>";
				}

				//处理描述内容长度
				if(intval($paramer[d_len])>0){
					$len = intval($paramer[d_len]);
					$rs[description] = mb_substr($rs[description],0,$len,"utf-8");
				}

				//获取所属类别名称
				 $rs[\'name\'] = $group_name[$rs[\'nid\']];

				//构建资讯静态链接
				if($config[sy_news_rewrite]=="2"){
					$rs["url"]=$config[\'sy_weburl\']."/news/".date("Ymd",$rs["datetime"])."/".$rs[id].".html";
				}else{
					$rs["url"] = Url("article",array("c"=>"show","id"=>$rs[id]),"1");
				}

				if(mb_substr($rs[newsphoto],0,4)=="http"){
					$rs["picurl"]=$rs[newsphoto];
				}else{
					if($rs[\'newsphoto\']==""){
						$rs["picurl"] = $config[sy_weburl]."/app/template/".$config[style]."/images/nopic.gif";
					}else{
						$rs["picurl"] = checkpic($rs[\'newsphoto\']);
					}
				}

				$rs[time]=date("Y-m-d",$rs[datetime]);
				$rs[datetime]=date("m-d",$rs[datetime]);
				'.$name.'[] = $rs;
			}//end while
		}';
		//自定义标签 END
		//global $DiyTagOutputStr;
		//$DiyTagOutputStr[]=$OutputStr;
		return SmartyOutputStr($this,$compiler,$_attr,'article',$name,$OutputStr,$name);
	}
}
class Smarty_Internal_Compile_Articleclose extends Smarty_Internal_CompileBase{
public function compile($args, $compiler, $parameter){
$_attr = $this->getAttributes($compiler, $args);
if ($compiler->nocache) {
$compiler->tag_nocache = true;
}

list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('article', 'articleelse'));

return "<?php } ?>";
}
}
