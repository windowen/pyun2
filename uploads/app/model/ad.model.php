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
class ad_model extends model{

    // 查询广告类别
    function getAdClassList($whereData = array(), $data = array())
    {
        $ListNew = array();
        $data['field'] = empty($data['field']) ? '*' : $data['field'];
        $List = $this->select_all('ad_class', $whereData, $data['field']);
        
        if (! empty($List)) {
            
            if ($data['href']) {
                foreach ($List as $k => $v) {
                    
                    $List[$k]['hrefn'] = checkpic($v['href']);
                }
            }
            
            $ListNew['list'] = $List;
        }
        
        return $ListNew;
    }

    // 查询单个广告类
    function getAdClassInfo($whereData = array(), $data = array())
    {
        if ($whereData) {
            $data['field'] = empty($data['field']) ? '*' : $data['field'];
            
            $acInfo = $this->select_once('ad_class', $whereData, $data['field']);
        }
        
        return $acInfo;
    }

    // 更新广告类
    function upAdClass($whereData = array(), $data = array())
    {
        if (! empty($whereData)) {
            
            $nid = $this->update_once('ad_class', $data, $whereData);
        }
        return $nid;
    }

    // 添加广告类
    function addAdClass($setData = array())
    {
        if (! empty($setData)) {
            
            $nid = $this->insert_into('ad_class', $setData);
        }
        
        return $nid;
    }

    // 删除广告
    function delAdClass($whereData, $data)
    {
        if ($data['type'] == 'one') { // 单个删除
            
            $limit = 'limit 1';
        }
        
        if ($data['type'] == 'all') { // 多个删除
            
            $limit = '';
        }
        
        $result = $this->delete_all('ad_class', $whereData, $limit);
        
        return $result;
    }

    // 查询广告
    function getList($whereData = array(), $data = array())
    {
        $ListNew = array();
        $data['field']      =   empty($data['field']) ? '*' : $data['field'];
        
        if($this->config['did']){
            $whereData['did']   =   $this->config['did'];
        }
        $List = $this->select_all('ad', $whereData, $data['field']);
        
        if (! empty($List)) {
            
            $ListNew['list'] = $List;
        }
        
        return $ListNew;
    }

    // 添加广告
    function addAd($setData = array())
    {
        if (! empty($setData)) {
            
            $nid = $this->insert_into('ad', $setData);
        }
        
        return $nid;
    }

    // 查询广告
    function getInfo($whereData = array(), $data = array())
    {
        if ($whereData) {
            $data['field'] = empty($data['field']) ? '*' : $data['field'];
            
            $info = $this->select_once('ad', $whereData, $data['field']);
            if ($info['pic_url']) {
                $info['pic_url_n'] = checkpic($info['pic_url']);
            }
        }
        
        return $info;
    }

    // 更新广告
    function upInfo($whereData = array(), $data = array())
    {
        if (! empty($whereData)) {
            
            $nid = $this->update_once('ad', $data, $whereData);
        }
        return $nid;
    }

    // 删除广告
    function delAd($whereData, $data)
    {
        if ($data['type'] == 'one') { // 单个删除
            
            $limit = 'limit 1';
        }
        
        if ($data['type'] == 'all') { // 多个删除
            
            $limit = '';
        }
		 
        $result = $this->delete_all('ad', $whereData, $limit);
        
        return $result;
    }
  
    // 查询广告列表
    function getAdList($whereData = array(), $data = array())
    {
        $ListNew = array();
        $List = $this->getList($whereData, $data['field']);
        
        // 提取广告类
        $class = $this->getAdClassList(array('orderby' => 'orders,desc'));
        $ListNew['class'] = $class['list'];
        
        if (is_array($class['list']) && $class) {
            foreach ($class['list'] as $val) {
                $nclass[$val['id']] = $val['class_name'];
            }
        }
        $ListNew['nclass'] = $nclass;
        
        if (! empty($List['list'])) {
            
            // 提取分站
            require_once ('cache.model.php');
            $cacheM   = new cache_model($this->db, $this->def);
            $domain   = $cacheM->GetCache('domain');
            
            $linkrows = $List['list'];
            if (is_array($linkrows)) {
                foreach ($linkrows as $key => $value) {
                    
                    $start = @strtotime($value['time_start']);
                    $end   = @strtotime($value['time_end'] . " 23:59:59");
                    $time  = time();
                    
                    $linkrows[$key]['class_name']  = $nclass[$value['class_id']];
                    if ($value['did']>0) {
                        $linkrows[$key]['d_title'] = $domain['Dname'][$value['did']];
					} elseif($value['did']==-1) {
                        $linkrows[$key]['d_title'] = '全站';	
                    } else {
                        $linkrows[$key]['d_title'] = '主站';
                    }
                    if ($value['is_check'] == "1") {
                        $linkrows[$key]['check'] = "<font color='green'>已审核</font>";
                    } else {
                        $linkrows[$key]['check'] = "<font color='red'>未审核</font>";
                    }
                    $value['pic_url'] = checkpic($value['pic_url']);
                    
                    switch ($value['ad_type']) {
                        case "word":
                            $linkrows[$key]['ad_typename'] = "文字广告";
                            break;
                        
                        case "pic":
                            $linkrows[$key]['ad_typename'] = '<a href="javascript:void(0)" class="preview admin_n_img" url="' . $value['pic_url'] . '"></a>';
                            break;
                        
                        case "flash":
                            $linkrows[$key]['ad_typename'] = "FLASH广告";
                            break;
                        
                        case "lianmeng":
                            $linkrows[$key]['ad_typename'] = "联盟广告";
                            break;
                    }
                    
                    if ($value['time_start'] != "" && $start != "" && ($value['time_end'] == "" || $end != "")) {
                        
                        if ($value['time_end'] == "" || $end > $time) {
                            
                            if ($value['is_open'] == '1' && $start < $time) {
                                
                                $linkrows[$key]['type'] = "<font color='green'>使用中..</font>";
                                
                            } else if ($start < $time && $value['is_open'] == '0') {
                                
                                $linkrows[$key]['type'] = "<font color='red'>已停用</font>";
                                
                            } elseif ($start > $time && ($end > $time || $value['time'] == "")) {
                                
                                $linkrows[$key]['type'] = "<font color='#ff6600'>广告暂未开始</font>";
                            }
                        } else {
                            
                            $linkrows[$key]['type'] = "<font color='red'>过期广告</font>";
                            $linkrows[$key]['is_end'] = '1';
                        }
                    } else {
                        $linkrows[$key]['type'] = "<font color='red'>无效广告</font>";
                    }
                }
            }
            $ListNew['list'] = $linkrows;
        }
        
        return $ListNew;
    }

    // 查询广告点击次数
    function getAdClickNum($Where)
    {
        return $this->select_num('adclick', $Where);
    }

    /**
     * 引用userinfo类，查询member列表信息
     */
    // 插入广告点击记录
    function addAdClick($data = array())
    {
		
        return $this->insert_into('adclick', $data);
    }

    function model_ad_arr()
    {
        $show .= "<?php\r\n\$ad_label=array();\r\n";
        
        $ad_list = $this->select_all("ad", array('is_open' => 1,'orderby' => array('sort,desc','id,desc')));
        
        if (is_array($ad_list)) {
            
            $time = time();
            
            foreach ($ad_list as $key => $value) {
                
                $start = strtotime($value['time_start'] . " 00:00:00");
                
                $end   = strtotime($value['time_end'] . " 23:59:59");
                
                if (! empty($value['time_end'])) {
                    
                    if ($end > $time) {
                        
                        $end_type = 1;
                    } else {
                        
                        $end_type = 2;
                    }
                } else {
                    
                    $end_type = 1;
                }
                if ($start && $start < $time && $end_type == 1 && $value['is_check'] == "1") {
                    
                    if ($value['ad_type'] == "word") {
                        
                        $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['html']=\"<a href='$value[word_url]'>$value[word_info]</a>\";\r\n";
                    } elseif ($value['ad_type'] == "pic") {
                        
                        if (@! stripos("ttp://", $value['pic_url'])) {
                            
                            $pic_url = checkpic($value['pic_url']);
                        }
                        
                        $height = $width = $alt = "";
                        
                        if ($value['pic_height']) {
                            
                            $height = "height='$value[pic_height]'";
                        }
                        if ($value['pic_width']) {
                            
                            $width  = "width='$value[pic_width]'";
                        }
                        if ($value['pic_content']) {
                            
                            $alt    = "alt='$value[pic_content]'";
                        }
                        if ($this->config['sy_seo_rewrite'] == '1') {
                            $pic_src = $this->config['sy_weburl'] . "/c_clickhits-id_" . $value['id'] . ".html";
                        } else {
                            
                            $pic_src = $this->config['sy_weburl'] . "/index.php?c=clickhits&id=" . $value['id'];
                        }
                        
                        if ($value['target'] == 2) {
                            
                            $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['html']=\"<a href='$pic_src' target='_blank' rel='nofollow'><img src='$pic_url'  " . $height . " " . $width . " " . $alt . "></a>\";\r\n";
							$show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['lay_html']=\"<a href='$pic_src' target='_blank' rel='nofollow'><img lay-src='$pic_url'  " . $height . " " . $width . " " . $alt . "></a>\";\r\n";


                        } else {
                            
                            $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['html']=\"<a href='$pic_src' rel='nofollow'><img src='$pic_url' " . $height . " " . $width . " " . $alt . " ></a>\";\r\n";

							$show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['lay_html']=\"<a href='$pic_src' rel='nofollow'><img src='$pic_url' " . $height . " " . $width . " " . $alt . " ></a>\";\r\n";
                        }
                        
                        $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['pic']=\"$pic_url\";\r\n";
                        
                        $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['src']=\"$pic_src\";\r\n";
                    } elseif ($value['ad_type'] == "flash") {
                        
                        if (! stripos("ttp://", $value['flash_url'])) {
                            
                            $flash_url = checkpic($value['flash_url']);
                        }
                        
                        $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['html']=\"<object type='application/x-shockwave-flash' data='$flash_url' width='$value[flash_width]' height='$value[flash_height]'><param name='movie' value='$flash_url' /><param value='transparent' name='wmode'></object>\";\r\n";
                    } elseif ($value['ad_type'] == "lianmeng") {
                        
                        $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['html']=\"" . str_replace('"', '\\"', $value['lianmeng_url']) . "\";\r\n";
                    }
                    $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['start']=\"" . strtotime(date('Y-m-d H:i:s', $start)) . "\";\r\n";
                    $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['end']=\"" . strtotime(date('Y-m-d H:i:s', $end)) . "\";\r\n";
                    $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['type']=\"" . $value['ad_type'] . "\";\r\n";
                    $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['name']=\"" . $value['ad_name'] . "\";\r\n";
                    $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['did']=\"" . $value['did'] . "\";\r\n";
                    $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['id']=\"" . $value['id'] . "\";\r\n";
                    $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['class_id']=\"" . $value['class_id'] . "\";\r\n";
                    if($value['appurl']){
                        $show .= "\$ad_label['$value[class_id]']['ad_$value[id]']['appurl']=\"" . $value['appurl'] . "\";\r\n";
                    }
                }
            }
        }
        $show .= "?>";
        $path = PLUS_PATH . "pimg_cache.php";
        $fp = @fopen($path, "w");
        $fw = @fwrite($fp, $show);
        @fclose($fp);
        @chmod($path, 0777);
        return $fw;
    }

    function model_saveadd($post, $pic = NULL)
    {
        if (empty($post['did'])) {
            
            $did = 0;
        }
        $value = array(
            
            'ad_name'    => $post['ad_name'],
            'target'     => $post['target'],
            'time_start' => $post['ad_time_start'],
            'time_end'   => $post['ad_time_end'],
            'ad_type'    => $post['ad_type'],
            'class_id'   => $post['class_id'],
            'is_check'   => $post['is_check'],
            'did'        => $post['did'],
            'is_open'    => $post['is_open'],
            'sort'       => $post['sort'],
            'remark'     => $post['remark'],
            'appurl'     => $post['appurl']
        );
        
        if ($post['ad_type']) {
            
            if ($post['ad_type'] == "word") {
				
                $value['word_info']	=	$post['word_info'];
                $value['word_url']	=	$post['word_url'];
            } elseif ($post['ad_type'] == "pic") {
                
                if ($pic != "") {
                    if (strpos($pic,$this->config['sy_weburl']) !== false){
                        $pic  =  str_replace($this->config['sy_weburl'].'/data','./data',$pic);
                    }
                    if (strpos($pic,$this->config['sy_ossurl']) !== false){
                        $pic  =  str_replace($this->config['sy_ossurl'].'/data','./data',$pic);
                    }
                    $value['pic_url']	=	$pic;
                }
                $pic_src = str_replace("amp;", "", $post['pic_src']);
				
                $value['pic_src']		=	$pic_src;
                $value['pic_content']	=	$post['pic_content'];
                $value['pic_width']		=	$post['pic_width'];
                $value['pic_height']	=	$post['pic_height'];
                
            } elseif ($post['ad_type'] == "flash") {
				if ($pic != "") {
                    $value['flash_url']	=	$pic;
                }
                $value['flash_width']	=	$post['flash_width'];
                $value['flash_height']	=	$post['flash_height'];
                
            } elseif ($post['ad_type'] == "lianmeng") {
                
                $value['lianmeng_url']	=	$post['lianmeng_url'];
            }
			if($post['id']){
				$nid 	= 	$this->update_once("ad", $value, array('id' => $post['id']));
				$msg	=	'广告更新';
			}else{
				$nid 	= 	$this->insert_into("ad", $value);
				$msg	=	'广告添加';
			}
            $this->model_ad_arr();
            
            if ($nid) {
                
                $return['msg']		= 	$msg."成功！";
                $return['url']		= 	"index.php?m=advertise";
                $return['errcode']	=	9;
            } else {
                
                $return['msg']		=	$msg."失败！";
                $return['url']		=	$_SERVER['HTTP_REFERER'];
                $return['errcode']	=	8;
            }
        } else {
            
            $return['msg'] 		= 	"您还未选择广告类型！";
            $return['url'] 		= 	'index.php?m=advertise&c=ad_add';
            $return['errcode'] 	= 	8;
        }
        return $return;
    }

	/**
	 * 未发现使用：   -----  【 sy  2020年6月12日 11:42:30】
	 * 暂时不做删除
	 */
    /**
	function model_del_ad($id)
    {
        
        if ($id) {

            $result =   $this->delete_all('ad', array('id' => $id));

            $return['msg']      =   $result ? '删除成功！' : '删除失败！';
            $return['url']      =   'index.php?m=advertise';
            $return['errcode']  =   $result ? 9 : 8;

            $this->model_ad_arr();
        }

        return $return;
    }
	*/
}
?>