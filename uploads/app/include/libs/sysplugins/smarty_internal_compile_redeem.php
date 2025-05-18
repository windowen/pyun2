<?php
class Smarty_Internal_Compile_Redeem extends Smarty_Internal_CompileBase{
    public $required_attributes = array('item');
    public $optional_attributes = array('name', 'key',  'limit', 'ispage','order','sort','nid','islt','hot','rec','intinfo','tnid');
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
        
        //自定义标签START
        $OutputStr=''.$name.'=array();$time=time();$paramer='.ArrayToString($_attr,true).';
		global $db,$db_config,$config;
		$ParamerArr = GetSmarty($paramer,$_GET,$_smarty_tpl);
		$paramer = $ParamerArr[arr];
		$Purl =  $ParamerArr[purl];
        global $ModuleName;
        if(!$Purl["m"]){
            $Purl["m"]=$ModuleName;
        }
		$where = "`status`=1 ";
		if($paramer[hot]){
			$where.=" AND `hot`=".$paramer[hot]."";
		}
		if($paramer[rec]){
			$where.=" AND `rec`=".$paramer[rec]."";
		}
		if($paramer[nid]){
			$where.=" AND `nid`=".$paramer[nid]."";
		}
		if($paramer[tnid]){
			$where.=" AND `tnid`=".$paramer[tnid]."";
		}
		if($paramer[intinfo]){
			$bninfo=@explode(\'_\',$paramer[intinfo]);
			if($bninfo[1]){
				$where.=" and `integral` between \'".$bninfo[0]."\' and \'".$bninfo[1]."\'";
			}else{
				$where.=" and `integral` <".$bninfo[0]."";
			}
			
		}
		//查询条数
		if($paramer[limit]){
			$limit=" LIMIT ".$paramer[limit];
		}else{
			$limit=" LIMIT 20";
		}
		if($paramer[ispage]){
			$limit = PageNav($paramer,$_GET,"reward",$where,$Purl,"",$paramer[islt]?$paramer[islt]:"6",$_smarty_tpl);
		}
		//排序字段（默认按照开始时间排序）
		if($paramer[order]){
			$where .= " ORDER BY $paramer[order] ";
		}else{
			$where .= " ORDER BY integral ";
		}
		//排序规则（默认按照开始时间排序倒序）
		if($paramer[sort]){
			$where .= " $paramer[sort]";
		}else{
			$where .= " asc ";
		}
		'.$name.'=$db->select_all("reward",$where.$limit);
		if(is_array('.$name.')){
			foreach('.$name.' as $key=>$value){
				'.$name.'[$key][\'pic\']= checkpic($value[\'pic\'],$config[\'{yun:}$config.sy_imgsc_mr{/yun}\']);
				if($paramer[islt]==6){
					'.$name.'[$key][url] = Url("wap",array("c"=>"redeem","a"=>"show","id"=>$value[id]));
				}else{
					'.$name.'[$key][url] = Url("redeem",array("c"=>"show","id"=>$value[id]));
				}
			}
		}
		';
        //自定义标签 END
      //  global $DiyTagOutputStr;
      //  $DiyTagOutputStr[]=$OutputStr;
        return SmartyOutputStr($this,$compiler,$_attr,'redeem',$name,$OutputStr,$name);
    }
}
class Smarty_Internal_Compile_Redeemelse extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);

        list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('redeem'));
        $this->openTag($compiler, 'redeemelse', array('redeemelse', $nocache, $item, $key));

        return "<?php }\nif (!\$_smarty_tpl->tpl_vars[$item]->_loop) {\n?>";
    }
}
class Smarty_Internal_Compile_Redeemclose extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('redeem', 'redeemelse'));

        return "<?php } ?>";
    }
}
