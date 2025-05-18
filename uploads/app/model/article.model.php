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
class article_model extends model{

	/*
	 * 获取新闻列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组  tlen :20 截取标题 property:1 新闻属性 group ：提取新闻类别 content:1 提取新闻内容 field 查询字段
	 */
	function getList($whereData, $data=array()){

		$ListNew	=	array();
		$field		=	$data['field'] ? $data['field'] : '*';
		$List		=	$this -> select_all('news_base', $whereData, $field);

		if(!empty( $List )){

			if($data['group']){

				if(!is_array($data['group'])){
					//根据DATA数组传入条件 判断是否需要处理数据
					foreach($List	as	$v)
					{
						$classid[$v['nid']]	=	$v['nid'];
					}
					//新闻分类查询条件
					$groupWhere['id']	=	array('in',pylode(',', $classid ) );

					//提取新闻分类
					$ListNew			=	$this -> getClass( $groupWhere );
					$groupList			=	$ListNew['list'];

				}else{
					$groupList			=	$data['group'];
				}
			}

			//提取新闻属性
			if($data['property'])
			{
				$property			=	$this -> getProperty();
				if(!empty($property)){

					foreach($property as $key=>$value)
					{
						$propertyList[$value['value']]	=	$value['name'];
					}
				}
				$ListNew['property']	=	$propertyList;
			}
			//提取新闻内容
			if($data['content'])
			{
				if(!is_array($data['content'])){
					foreach($List	as	$v)
					{
						$nid[$v['id']]	=	$v['id'];
					}
					$content			=	$this -> select_all('news_content',array('nbid'=>array('in',pylode(',', $nid))));
					if(!empty($content)){

						foreach($content as $key=>$value)
						{
							$contentList[$value['nbid']]	=	$value;
						}
					}
				}
			}

			foreach($List as $k=>$v)
			{
				$List[$k]['url']		=	Url('article',array('c'=>'show','id'=>$v['id']));
				$List[$k]['classurl']	=	Url('article',array('c'=>'list','nid'=>$v['nid']));
				$List[$k]['picurl']		=	checkpic($v['newsphoto']);

				if (!empty($v['datetime'])){
				    $List[$k]['datetime_n']	=	date('Y-m-d',$v['datetime']);
				}
				if (!empty($groupList[$v['nid']]['name'])){
				    $List[$k]['name']		=	$groupList[$v['nid']]['name'];
				}
				if (!empty($contentList[$v['id']]['content'])){
				    $List[$k]['content']	=	$contentList[$v['id']]['content'];
				}
				if(!empty($v['description'])){
				    $List[$k]['description_n']	=	mb_substr($v['description'],0,30,"utf-8");
				}

				$type					=	'';

				if($data['tlen']){
					$List[$k]['title']	=	mb_substr($v['title'],0,$data['tlen'],"utf-8");
				}
				if($data['property'])
				{
					if($v['newsphoto'] != '')
					{
						$type		.=	" 图";
					}
					if($v['describe'] != "")
					{
						$describe	 =	explode(",",$v['describe']);
						foreach($describe as $desv)
						{
							$type	 .=		" ".$propertyList[$desv];
						}
					}
					if(trim($type)){
						$List[$k]['titype']	=	"[<font color='red'>".$type."</font>]";
					}


				}
			}
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
	}

    /**
     * 修改news_base    详情
     * $whereData       修改条件数据
     * $upData          修改的数据
     * $data 		    自定义处理数组
     */
	public function upBase($whereData = array(), $upData = array(), $data = array()){
        $nid            			=   0;
	    if (!empty($upData) && !empty($whereData)){
	        $nid        			=	$this -> update_once('news_base', $upData, $whereData);
        }
        return $nid;
	}
    /**
     * 删除rebates      详情
     * $whereData       查询条件
     */
    public function delNews($whereData) {

		$nid	=	0;
		$nid	=	$this -> delete_all('news_base', $whereData, '');
		if(isset($whereData['id'])){
			$this -> delete_all('news_content', array('nbid' => $whereData['id']), '');
		}
        return $nid;
    }
	/**
     * 获取news_base	详情
     * $whereData       查询条件
     * $data            自定义处理数组 scene 场景值，定制不同场景返回的数据
     */
    public function getInfo($whereData=array(),$data=array())
    {
		$data['field']  =  empty($data['field']) ? '*' : $data['field'];
		$Info  		    =  $this -> select_once('news_base', $whereData, $data['field']);

		if(!empty($Info)){
			$Info['datetime_n']	=	date('Y-m-d',$Info['datetime']);

			$Info['author_n']	=	mb_substr($Info['author'],0,30,"utf-8");

			$Info['newsphoto_n']  =  checkpic($Info['newsphoto']);

			if($data['iscon'] == 1 && $Info['id'])
			{
			    $news_content  =  $this -> select_once('news_content',array('nbid' => $Info['id']));

			    if(!empty($news_content['content'])){

			        $content  =  str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $news_content['content']);

			        $content  =  htmlspecialchars_decode($content);

			        preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/',$content,$res);

			        if(!empty($res[3])){
			            foreach($res[3] as $v){
			                if(strpos($v,'http:')===false && strpos($v,'https:')===false){

			                    $content  =  str_replace($v,$this->config['sy_ossurl'].$v,$content);
			                }
			            }
			        }
			        preg_match_all('/<a(.*?)href=("|\'|\s)?(.*?)(?="|\'|\s)/',$content,$res);

			        if(!empty($res[3])){
			            foreach($res[3] as $v){
			                if(strpos($v,'http:')===false && strpos($v,'https:')===false){

			                    $content  =  str_replace($v,$this->config['sy_weburl'].$v,$content);
			                }
			            }
			        }

			        $Info['content']  =  $content;
			    }
			}
		}
		return $Info;
	}
	/**
	 * 创建新闻
	 * $id : 新闻ID
	 * $iscon : 是否需要查询新闻详细内容
	 *
	 */
	function addNews($setData){
		$res						=	array(
            'errcode'   			=>	8,
            'layertype' 			=>	0,
			'msg'       			=>	'',
			'data'					=>	0
        );
		if(empty($setData)){
			$res['msg']				=	'缺少参数！';
			return $res;
		}

		$newsId						=	intval($setData['id']);

		//id不为空时，判断此id是否存在
		if(!empty($newsId)){
			$news_base				=	$this -> getInfo(array('id'=>$newsId));
			if(empty($news_base)){
				$res['msg']			=	'新闻不存在！';
				return $res;
			}
		}

		// 处理编辑器内图片
		if(!empty($setData['content'])){

		    $content  =  str_replace(array('"','\''),array('',''),$setData['content']);
		    preg_match_all('/<img[^>]+src=(.*?)\s[^>]+>/im',$content,$match);

		    if(!empty($match[1])){
		    	$realpicArr = array();
		    	foreach($match[1] as $mk=>$mv){
		    		if(!strstr($mv,'fileTypeImages')){
		    			$realpicArr[]= $mv;
		    		}
		    	}
		        // 有的图片是网络路径，，\后面的截取出来需过滤掉
		        $mbstr  =  substr(strrchr($realpicArr[0], "\\"), 1);
		        $str    =  str_replace($mbstr,'',$realpicArr[0]);
		        $str    =  str_replace("\\",'',$str);
		    }
		    $contentRep			 =	array('&amp;',"background-color:#ffffff","background-color:#fff","white-space:nowrap;");
		    $contentStr			 =	str_replace($contentRep, array('&','', '', ''), $setData['content']);
		    if($this->config['sy_outlinks']==1){
		      $contentStr			 =	$this -> _contentProcess($contentStr);
		    }
		    $setData['content']  =	$contentStr;
		}
		// 处理上传缩略图
		if($setData['file']['tmp_name']){

		    include_once('upload.model.php');

		    $uploadM  =  new upload_model($this->db, $this->def);

		    $upArr    =  array(
		        'file'  =>  $setData['file'],
		        'dir'   =>  'news',
		        'type'	=>	'news'
		    );

		    $pic  =  $uploadM->newUpload($upArr);

		    if (!empty($pic['msg'])){

		        $res['msg']  =  $pic['msg'];

		        return $res;

		    }elseif (!empty($pic['picurl'])){

		        $pictures 	=  	$pic['picurl'];

		        $thumburl 	=  	$pic['thumburl'];
		    }
		    unset($setData['file']);
		}
		//处理缩略图
		if(isset($pictures)){
		    $setData['newsphoto']  =  $pictures;

		}elseif(isset($str) && $news_base['newsphoto'] == ''){

		    if(strpos($str,'http:')===false && strpos($str,'https:')===false){
		        $setData['newsphoto']  =  '.'.$str;
		    }else{
		        $setData['newsphoto']  =  $str;
		    }
		}
		if(isset($thumburl)){

		    $setData['s_thumb']	   =  $thumburl;

		}elseif(isset($str) && $news_base['s_thumb']==''){

		    if(strpos($str,'http:')===false && strpos($str,'https:')===false){
		        $setData['s_thumb']  =  '.'.$str;
		    }else{
		        $setData['s_thumb']  =  $str;
		    }
		}
		//处理传入的参数
		$setData['describe']	   =  pylode(',', $setData['describe']);
		$timeStr				   =  time();
		$setData['lastupdate']     =  $timeStr;
		if(empty($setData['keyword'])){
			require_once(LIB_PATH."lib_splitword_class.php");
			$sp					   =  new SplitWord();
			$keywordarr			   =  $sp -> getkeyword(strip_tags($contentStr));
			$setData['keyword']	   =  pylode(',', $keywordarr);
		}
		$setData['did']		   =  $setData['did'] == '' ? 0 : $setData['did'];

		/* if($this->config['sy_outlinks']!=1){
			include_once('keyword.model.php');
			foreach($keyword as $key=>$val){
				$keyname[]	=	$val['key_name'];
			}
			$str 			= 	htmlspecialchars_decode($setData['content']);
			foreach($keyname as $key=>$val){

				$str 	= 	preg_replace("/<a[^>]*>(".$val.".*)<\/a>/is", "$1", $str);
			}

			$setData['content']	=	$str;

		} */

		$setData['starttime'] = strtotime($setData['starttime']);//开始时间
		$setData['endtime']  = strtotime($setData['endtime']);//结束时间


		//内容数组
		$contentArr  =  array(
			'content'  =>  $setData['content'],
			'did'      =>  $setData['did']
		);
		//根据id来判断是新增还是修改
		if(empty($newsId)){
			$setData['datetime']	=	$timeStr;
			if(empty($setData['sort'])){
				$setData['sort']	=	0;
			}
			$nbid					=	$this -> insert_into('news_base', $setData);
			if(empty($nbid)){
				$res['msg']			=	'新增数据错误！';
				return $res;
			}
			$contentArr['nbid']		=	$nbid;
			$cont					=	$this -> insert_into("news_content", $contentArr);
			$res['msg']				=	'新闻(ID:'.$nbid.')添加成功！';
			$res['data']			=	$nbid;
		}else{
			$nbid					=	$this -> upBase(array('id' => array('=', $newsId)), $setData);
			$row					=	$this -> select_once('news_content', array('nbid' => $newsId), '`nbid`');
			if(!empty($row)){
				$cont 				=	$this -> update_once('news_content', $contentArr, array('nbid' => array('=', $newsId)));
			}else{
				$contentArr['nbid']	=	$newsId;
				$cont				=	$this -> insert_into('news_content', $contentArr);
			}
			$res['msg']				=	'新闻(ID:'.$newsId.')更新成功！';
			$res['data']			=	$newsId;
		}
		$res['errcode']				=	9;
		return $res;

	}

	/**
	 * 关键词替换
	 */
	public function _contentProcess($content){
		$hotKey			=	array('type' => array('=', 3), 'check' => array('=', 1));
		$keyname		=	$this -> select_all('hot_key', $hotKey, '`key_name`');
		if(empty($keyname)){
			return $content;
		}
		/*
		避免<a>关键字</a>这样的链接中文本被替换成<a> 导致<a>标签嵌套错误：
		先匹配出这样的<a>标签，记录下，并替换掉；
		再执行下面的$keynamearr替换；
		最后再替换回来。
		*/
		$aLink 			=	array();
		$aLinkFlag		=	array();
		foreach($keyname as $val){
			if(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$val['key_name'])){
				$keynamearr[]		=	$val['key_name'];
				$tmpStr				=	searchListRewrite(array('type' => 'keyword', 'v' => $val['key_name']), $this->config);
				$tmpaStr			=	'"target="_bank"><span style="color:#E53333;">';
				$href[]				=	'<a href="'.$tmpStr.$tmpaStr.$val['key_name'].'</span></a>';

				preg_match_all("/<a[^>]+>[^<]*{$val['key_name']}[^<]*<\/a>/u", $content, $matches);

				if(is_array($matches[0])){
					$aLinkTmp		=	array();
					$aLinkTmpFlag	=	array();

					foreach($matches[0] as $k => $v){
						$index 		=	count($aLink);
						$aLink[] 	=	$aLinkTmp[]		=	$v;
						$aLinkFlag[] =	$aLinkTmpFlag[]	=	"[flag]{$index}[flag]";
					}
					//这里先替换一遍，以防有类似这样大词套小词的两个关键词：“保安集团”、“保安”，重复匹配
					$content 		=	str_replace($aLinkTmp, $aLinkTmpFlag, $content);
				}
			}
		}
		$content		=	str_replace($aLink, $aLinkFlag, $content);
		$content		=	str_replace($keynamearr, $href, $content);
		$content		=	str_replace($aLinkFlag, $aLink, $content);
		return $content;
	}

	function getClass($whereData=array()){

		if($whereData['isson']){
			$sonClass	=	1;
			unset($whereData['isson']);
		}
		$List			=	$this->select_all('news_group', $whereData);

		if(!empty($List)){

			foreach($List as $key=>$value)
			{

				$ListNew['list'][$value['id']]	=	$value;

			}
		}
		//是否需要将新闻分类整合成子类显示
		if($sonClass == 1)
		{
			$a	=	0;	$b	=	0;

			if(is_array($ListNew['list']))
			{
				foreach($ListNew['list'] as $key=>$v){
					if($v['keyid']==0)
					{
						$one_class[$a]['id']		=	$v['id'];
						$one_class[$a]['name']		=	$v['name'];
						$a++;
					}
					if($v['keyid']!=0)
					{

						$two_class[$v['keyid']][$v['id']]	=	array('name' => $v['name'],'id' => $v['id']);

					}
				}
			}
			$ListNew['one_class']	=	$one_class;
			$ListNew['two_class']	=	$two_class;

		}

		return	$ListNew;

	}

	function getProperty($whereData=array()){

		$List	=	$this->select_all('property',$whereData);

		return	$List;

	}

	function addProperty($addData,$whereData = array())
	{
		if(!empty($whereData))
		{
			$return['id']		=	$this->update_once('property',$addData,$whereData);
			$return['msg']		=	'新闻属性(ID:'.$whereData['id'].')';
			$return['msg']		=	$return['id'] ? $return['msg'].'修改成功！' : $return['msg'].'修改失败！';
		}else{
			$return['id']		=	$this->insert_into('property',$addData);
			$return['msg']		=	'新闻属性(ID:'.$return['id'].')';
			$return['msg']		=	$return['id'] ? $return['msg'].'添加成功！' : $return['msg'].'添加失败！';
		}
		//操作状态 9：成功 8:失败 配合原有提示函数
		$return['errcode']		=	$return['id'] ? '9' :'8';

		return	$return;

	}

	function delProperty($delId)
	{

		if($delId)
		{
			if(is_array($delId))
			{
				$delId	=	pylode(',',$delId);

				$return['layertype']	=	1;
			}else{
				$return['layertype']	=	0;
			}

			$return['id']		=	$this -> delete_all("property",array('id' => array('in',$delId)),"");

			$return['msg']		=	'新闻属性(ID:'.$delId.')';
			$return['errcode']	=	$return['id'] ? '9' :'8';
			$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
		}else{
			$return['msg']		=	'请选择您要删除的新闻属性！';
			$return['errcode']	=	8;
		}

		return	$return;
	}
	/*
	* 获取新闻数量
	* $whereData	查询条件
	*
	*/
	function getNum($whereData=array()){

		$List	=	$this->select_num('news_base',$whereData);

		return	$List;

	}
	/*
	* 获取新闻类别详情
	* $whereData	查询条件 field 查询字段
	*
	*/
	public function getGroup($whereData=array(), $data=array()){

		$field	=	$data['field'] ? $data['field'] : '*';

		$List	=	$this->select_once('news_group', $whereData, $field);

		return	$List;

	}
	/*
	* 添加新闻类别
	* $addData		添加数据
	*
	*/
	public function addGroup($addData){

		$return['id']		=	$this->insert_into('news_group', $addData);
		$return['msg']		=	'新闻类别(ID:'.$return['id'].')';
		$return['msg']		=	$return['id'] ? $return['msg'].'添加成功！' : $return['msg'].'添加失败！';
		//操作状态 9：成功 8:失败 配合原有提示函数
		$return['errcode']	=	$return['id'] ? 9 : 8;

		return	$return;

	}
	/*
	* 修改新闻类别
	* $upData		修改数据
	*
	*/
	public function updGroup($whereData, $upData = array()){
		$return['id']		=	$this->update_once('news_group', $upData, $whereData);
		$return['msg']		=	'新闻类别(ID:'.$whereData['id'].')';
		$return['msg']		=	$return['id'] ? $return['msg'].'修改成功！' : $return['msg'].'修改失败！';
		//操作状态 9：成功 8:失败 配合原有提示函数
		$return['errcode']	=	$return['id'] ? 9 : 8;

		return	$return;

	}
	/*
	* 删除新闻类别
	* $delId		删除数据
	*
	*/
	public function delGroup($delId){
		if($delId)
		{
			if(is_array($delId))
			{
				$delId	=	pylode(',', $delId);

				$return['layertype']	=	1;
			}else{
				$return['layertype']	=	0;
			}

			$return['id']		=	$this -> delete_all("news_group",array('id' => array('in',$delId)), "");

			$return['msg']		=	'新闻类别(ID:'.$delId.')';
			$return['errcode']	=	$return['id'] ? '9' :'8';
			$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
		}else{
			$return['msg']		=	'请选择您要删除的新闻类别！';
			$return['errcode']	=	8;
		}

		return	$return;
	}

}
?>