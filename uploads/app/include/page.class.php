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
class page{
	var $pagelink="{{page}}.html";
	var $selectpagelink= "'{{link}}_'+this.value+'.html'";
	var $page = null;
	var $maxPage = null;
	var $autoHideInput=false;
	function page($page,$limit,$total,$pagelink="",$num=3,$html=true,$notpl=false){	 
		$this->maxPage = 0;
		if($total>0)
		$this->maxPage = intval(ceil($total/$limit));
		$this->page = min($this->maxPage , $page);
		$this->total = $total;
		if($pagelink){
			$this->pagelink=$pagelink;
		}
		$this->notpl = $notpl;
		$this->maxShowNum=$num;
		$this->autoHideInput=false;
		$this->setTpl();
	}
	//设置自定义属性
	function extend( $optionArr ){
		if($optionArr['tpl']){
			foreach( $this->tpl as $tk=>$tv ){
				if( array_key_exists( $tk , $optionArr['tpl'] ) )
					$this->tpl[$tk]=$optionArr['tpl'][$tk];
			}
		}
		if($optionArr['numTpl']){
			foreach( $this->numTpl as $tk=>$tv ){
				if( array_key_exists( $tk , $optionArr['numTpl'] ) )
					$this->numTpl[$tk]=$optionArr['numTpl'][$tk];
			}
		}
	}
	function setTpl(){
		global $pageType;
		
		//自定义部分
		$tpl['prestr']='<a href="[prelink]">上一页</a>';
		$tpl['nextstr']='<a href="[nextlink]">下一页</a>';
		
		if($pageType!='wap' && $pageType!='wapadmin'){
			$tpl['str']='[prestr][numstr][nextstr][inputstr]';
			//数字部分自定义
			$numTpl['linkstr']='<a href="[link]">[num]</a>';
			$numTpl['curr']='<a href="#" class="selected">[num]</a>';
			$numTpl['shenglue']='<em style="padding:0 10px;">...</em>';
		}else{
			$tpl['str']='[prestr][numselect][nextstr]<em>共[maxPage]页</em>[selectjs]';
			$numTpl['curr']='<a href="#" class="selected">[num]</a>';
		}
		if(!$this->notpl){
			$this->numTpl=$numTpl;
		}
		$this->tpl=$tpl;
	}
	//获取翻页字符串
	function numPage($num=2){
		if($this->maxPage==0)return;
		$prep = max(1,($this->page-1));
		$nextp = min($this->maxPage,($this->page+1));
		$firstlink =  str_replace("{{page}}","1",$this->pagelink);
		$lastlink = str_replace("{{page}}",$this->maxPage,$this->pagelink);
		$prelink = str_replace("{{page}}",$prep,$this->pagelink);
		$nextlink = str_replace("{{page}}",$nextp,$this->pagelink);
		$str ="";
		$prestr = str_replace("[prelink]",$prelink, $this->tpl['prestr']);
		
		if($this->maxPage>1){
			global $pageType;
			if($pageType=='wap' || $pageType=='wapadmin'){
				$numselect = '<select onchange="toPages()" id="pageChange">';
				//下拉框选择
				$startI = 1;
				if($this->maxPage>20){
					if($this->page>=10){
						$maxpage = $this->page+10;
						$startI = $this->page-10;
					}else{
						$maxpage = 20;
					}
				}else{
					$maxpage = $this->maxPage;
				}
				for($i=$startI;$i<=$maxpage;$i++){
					$selected = '';
					$pageSelectLink = str_replace('{{page}}',$i,$this->pagelink);
					if($this->page==$i){
						$selected= 'selected=selected';
					}
					$numselect .='<option value="'.$pageSelectLink.'" '.$selected.'>'.$i.'</option>';
				}
				$numselect.="</select>";
				$selectjs = '<script>
				    function toPages(url){
				        var obj=document.getElementById("pageChange");
						window.location.href=obj.options[obj.selectedIndex].value;
			        }
				   </script>';
			}
			$numstr = "";
			$n=$this->maxPage; 
			$istart = $this->page-$num;
			$istart = max($istart,1);
			$iend = $istart+$this->maxShowNum;
			$iend = min($iend,$n);
			if(($iend-$istart)<$this->maxShowNum){
				$istart = $iend-$this->maxShowNum;
				$istart = max($istart,1);
			}
			
			if($istart==2){
				$numstr .=$this->getnum(1,1);
			}
			elseif($istart==3){
				$numstr .=$this->getnum(1,2);
			}
			elseif($istart>3){
				$numstr .=$this->getnum(1,2);
				$numstr .= $this->numTpl['shenglue'];
			}
			$numstr .=$this->getnum($istart,$iend);
			$endi = $this->maxPage-1;
			if($iend<($endi-1)){
				$numstr .= $this->numTpl['shenglue'];
				$numstr .=$this->getnum($endi,$this->maxPage);
			}
			elseif($iend<$endi){
				$numstr .=$this->getnum($endi,$this->maxPage);
			}
			elseif($iend==$endi){
				$numstr .=$this->getnum($this->maxPage,$this->maxPage);
			}
			
		}
		else{//只有一页
			$numstr = str_replace("[num]",1, $this->numTpl['curr'] );
		}
		$prestr = str_replace("[prelink]",$prelink, $this->tpl['prestr'] );
		$nextstr = str_replace("[nextlink]",$nextlink, $this->tpl['nextstr'] );
		
		$str = $this->tpl['str'];
		$str =str_replace("[firstlink]",$firstlink,$str);
		$str =str_replace("[lastlink]",$lastlink,$str);
		$str =str_replace("[prestr]",$prestr,$str);
		$str =str_replace("[nextstr]",$nextstr,$str);
		$str =str_replace("[numstr]",$numstr,$str);
		$str =str_replace("[page]",$this->page,$str);
		$str =str_replace("[total]",$this->total,$str);
		$str =str_replace("[maxPage]",$this->maxPage,$str);
		$str =str_replace("[numselect]",$numselect,$str);
		$str =str_replace("[selectjs]",$selectjs,$str);
		//替换跳转js代码
		$inputid=rand(1001,3333);
		$this->jsfunc = "turnPages('".$this->pagelink."','$inputid',".$this->maxPage.");";

		$js='<script>function turnPages(url,num,pages){
        	var page=$("#gotoPage"+num).val();
        	if(page>pages){
        		page=pages;
        	}
        	url=url.replace("{{page}}",page);
        	window.location.href=url;
            }</script>';
        $css='<style>.input-num{width:46px;height:30px;line-height:30px;border:1px solid #ccc;border-radius: 4px;margin-left: 5px;}.diggg .pages_b_no{border:none;}
        .bt-confirm{background:#f60;border:none;color: #FFFFFF; cursor: pointer;height:25px; width: 48px;border-radius:3px; margin-left:3px;}.pages_b_no{padding-left:10px;}</style>';
		$input = '<input type="text" value="" class="input-num" id="gotoPage'.$inputid.'" onkeyup="this.value=this.value.replace(/[^0-9]/g,\'\')" onkeydown="if(event.keyCode==13){return false;}"/>';	
		if($this->maxPage>8 && $this->maxPage<12000){
			$inputstr='<em class="pages_b_no">转到第 '.$input.' 页<input class="bt-confirm" type="button" value="确定" onclick="'.$this->jsfunc.'" /></em>'.$js.$css;
		}
		//自动替换掉跳转的input
		if($this->autoHideInput && $this->maxPage<5){
		    $str= str_replace("[inputstr]","",$str);
		}
		else{
		    $str= str_replace("[inputstr]",$inputstr,$str);
		}
		return $str;
	}
	function getnum($istart,$iend){
		$numstr="";
		for ($i=$istart;$i<=$iend ;$i++ ){
			$link = str_replace("{{page}}",$i,$this->pagelink);
			if($i==$this->page){
				 $numstr .=  str_replace("[num]",$i, $this->numTpl['curr'] );
			}
			else{
				$linkstr = str_replace("[link]",$link, $this->numTpl['linkstr'] );
				$linkstr = str_replace("[num]",$i, $linkstr );
				$numstr .= $linkstr;
			}
		}
		return $numstr;
	}
}//end class
?>