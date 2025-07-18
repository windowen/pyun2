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
class model{
    // 操作状态
    const MODEL_INSERT          =   1;      //  插入模型数据
    const MODEL_UPDATE          =   2;      //  更新模型数据
    const MODEL_BOTH            =   3;      //  包含上面两种方式
    const MUST_VALIDATE         =   1;      // 必须验证
    const EXISTS_VALIDATE       =   0;      // 表单存在字段则验证
    const VALUE_VALIDATE        =   2;      // 表单值不为空则验证

    //网站配置信息
    protected $config           =   null;
    // 当前数据库操作对象
    protected $db               =   null;
    // 主键名称
    protected $pk               =   'id';
    // 主键是否自动增长
    protected $autoinc          =   false;
    // 数据表前缀
    protected $def              =   null;
    // 模型名称
    protected $name             =   '';
    // 数据库编码
    protected $coding           =   '';
    // 数据库名称
    // 数据库名称
    protected $dbName           =   '';
    //数据库配置
    protected $connection       =   '';
    // 数据表名（不包含表前缀）
    protected $tableName        =   '';
    // 实际数据表名（包含表前缀）
    protected $trueTableName    =   '';
    // 最近错误信息
    protected $error            =   '';
    // 字段信息
    protected $fields           =   array();
    // 数据信息
    protected $data             =   array();
    // 查询表达式参数
    protected $options          =   array();
    protected $_validate        =   array();  // 自动验证定义
    protected $_auto            =   array();  // 自动完成定义
    protected $_map             =   array();  // 字段映射定义
    protected $_scope           =   array();  // 命名范围定义
    // 是否自动检测数据表字段信息
    protected $autoCheckFields  =   true;
    // 是否批处理验证
    protected $patchValidate    =   false;
    // 链操作方法列表
    protected $methods          =   array('order','alias','having','group','lock','distinct','auto','filter','validate','result','token');
    //开启分站功能的数据表
    protected $sitetable        =   array('admin_announcement','zhaopinhui','zhaopinhui_com','zhaopinhui_pic','news_base','news_content','resume_expect','member','company_job','resume','user_entrust_record','down_resume','look_resume','company','company_cert','company_news','company_order','company_pay','company_product','once_job','resume_tiny','userid_msg','userid_job','look_job','admin_link','report','company_statis','partjob','hotjob','company_show','resume_show','banner','rebates','lt_talent','member_log','login_log','user_change');

	protected $admindir;
	

	protected $logininfo;

	public function __construct($db,$def,$logininfo = array(),$tpl=''){
		global $coding,$config,$adminDir;

		$this->db = $db;
		$this->tp = '';//未查到在哪用到
		$this->def = $def;
		$this->md = $coding;
        $this->config = $config;
        if (!empty($logininfo)){
            $this->uid = $logininfo['uid'];
            $this->username = $logininfo['username'];
            $this->usertype = $logininfo['usertype'];
        }
		$this->admindir = $adminDir;
		
		$this->tpl = $tpl;
		
		if(!($this->config['sy_wapdomain'])){
			
			$this->config['sy_wapdomain'] = $this->config['sy_weburl'].'/'.$this->config['sy_wapdir'];
		}else{
			
			if($config['sy_wapssl']=='1'){
				$protocol = 'https://';
			}else{
				$protocol = 'http://';
			}

			if(strpos($this->config['sy_wapdomain'],'http://')===false && strpos($this->config['sy_wapdomain'],'https://')===false)
			{
				$this->config['sy_wapdomain'] = $protocol.$this->config['sy_wapdomain'];
			}
		}
		if($adminDir){
			//$this->sitetadd = array();
			$this->sitetable = array();
		}
		// oss关闭时，oss地址为服务器地址
		if (!$this->config['sy_oss'] || ($this->config['sy_oss'] && $this->config['sy_oss'] == 2)){
		    
		    $this->config['sy_ossurl'] = $this->config['sy_weburl'];
		}
	}

    /**
     * 设置数据对象的值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return void
     */
    public function __set($name,$value) {
        // 设置数据对象属性
        $this->data[$name]  =   $value;
    }

    /**
     * 获取数据对象的值
     * @access public
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name) {
        return isset($this->data[$name])?$this->data[$name]:null;
    }

    /**
     * 检测数据对象的值
     * @access public
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name) {
        return isset($this->data[$name]);
    }

    /**
     * 销毁数据对象的值
     * @access public
     * @param string $name 名称
     * @return void
     */
    public function __unset($name) {
        unset($this->data[$name]);
    }

    // 回调方法 初始化模型
    protected function _initialize() {}

 

    function insert_into($table,$data=array()){

		
		$value=array();
      
		$this->db->connect();
        include(PLUS_PATH.'dbstruct.cache.php');
        $TableFullName=$this->def.$table;
        if(is_array($$TableFullName)){
            $fields=array_keys($$TableFullName);
        }else{
			return false;
		}

		if(is_array($fields)){
		
			if(is_array($data)){
				foreach($data as $key=>$v){
					if(in_array($key,$fields)){
						$v = $this->FilterStr($v);
						$value[]="`".$key."`='".$this->db->escape_string($v)."'";
					}
				}
			}
		}
		$value=@implode(",",$value);
		return $this->DB_insert_once($table,$value);
	}
	function update_once($table,$data=array(),$where=array()){ 
		
		$this->db->connect();
		$value=array();
		include(PLUS_PATH.'dbstruct.cache.php');
        $TableFullName=$this->def.$table;
        if(is_array($$TableFullName)){
            $fields=array_keys($$TableFullName);
        }else{
			return false;
		}
		
		if(is_array($fields)){

			if(is_array($data)){
			    
				foreach($data as $key=>$v){
				 
					if(in_array($key,$fields)){
					     
               
						if(is_array($v)){
	    
						    if($v[0] == '+'){
						        
						        $value[]  =  '`'.$key.'` = `'.$key.'` + '.$this->db->escape_string($v[1]);
                            }elseif ($v[0] == '-'){
						        
						        $value[]  =  '`'.$key.'` = `'.$key.'` - '.$this->db->escape_string($v[1]);
						        
						    }else if($v[0] == '='){
						        
						        $value[]  =  '`'.$key.'`  = '.$this->db->escape_string($v[1]);

						        
						    }elseif ($v[0] == 'CASE'){
						        
						        
						        $casesql  =  '`'.$key.'` =  CASE `'.$this->db->escape_string($v[1]).'`';
						        
						        foreach ($v[2] as $ck=>$cv){
						            
						            $casesql  .=  " WHEN '".$ck."' THEN '".$this->db->escape_string($cv)."' ";
						        }
						        
						        $casesql  .=  'END';
						        
						        $value[]  =  $casesql;
						    }elseif ($v[0] == 'DATE_ADD'){
								
								$value[]  =  '`'.$key.'` = DATE_ADD(`'.$key.'` , INTERVAL '.$this->db->escape_string($v[1]).' DAY )';
								
							}elseif ($v[0] == 'concat'){
								
								$value[]  =  '`'.$key.'` = concat(`'.$key.'` , ",'.$this->db->escape_string($v[1]).' ")';
							}else{
							    
							    $this->db->show_error();
							}
							
						}else{
               
						    $v = $this->FilterStr($v);
						    
						    $value[] = "`".$key."`='".$this->db->escape_string($v)."'";
						}
					}  
				}
			}

			
			$whereNew  =  $this->checkWhere($where);
			
			$value     =  @implode(',',$value);
			
			if($value !='' && $whereNew !=''){

				return $this->DB_update_all($table,$value,$whereNew);
			}else{
				return false;
			}
		}
	}
	
	function FilterStr($str){
		$str = stripslashes($str);
		return $str;
	}
    function Memcache_set($name,$value="")
    {

		global $config;

		if(isset($config['ismemcache']) && $config['ismemcache']==2){
			return false;
		}
		if (!empty($config['memcachehost']) && !empty($config['memcacheport']) && !empty($config['memcachetime'])){
		    
		    $memcachehost  =  $config['memcachehost'];//所在服务器
		    $memcacheport  =  $config['memcacheport'];//所在服务器端口
		    $memcachezip   =  0;//是否支持解压缩
		    $memcachetime  =  $config['memcachetime'];//缓存时间
		    
		    $name=md5(str_replace(array(" ","`","'",".","=","!"),"",$name));
		    
		    if(!extension_loaded('memcache'))return;
		    
		    $memcache =new memcache();
		    
		    if(!@class_exists($memcache)){return;}
		    
		    $memcache->connect($memcachehost,$memcacheport) or die ("Memcache连接失败或您的服务器不支持Memcache,请在后台关闭！");
		    
		    $val  =  $memcache->get($name);
		    
		    if(!is_array($val)){
		        $val  =  $value;
		        $memcache->set($name,$value,$memcachezip,$memcachetime);
		    }
		    $memcache->close();
		    return $val;
		}else{
		    return false;
		}
	}
	/**
     * 通用数量查询$tablename,$where = 1, $select="*"
     */
	function DB_select_num($tablename, $where = '', $select = "*",$tablename2='',$special=''){
		if(!$this->checkTableName($tablename)){
			
			return false;
		}
		$cachename=$tablename.$where;
		if(!$return=$this->Memcache_set($cachename)){//获取是否存在memcache
			if($tablename2){
				

				$SQL = "SELECT count($select) as num FROM " . $this->def . $tablename . " as a," . $this->def . $tablename2 . " as b  WHERE $where";
			}else{


				
				$SQL = "SELECT count($select) as num FROM " . $this->def . $tablename;

				if($where)
				{
					$SQL	.=	" WHERE ".$where;
				}
			}
			$query = $this->db->query($SQL);
			while($row=$this->db->fetch_array($query)){$return=$row['num'];}
			$this->Memcache_set($cachename,$return);//设置memcache
		}
		if($return<1){$return='0';}
		return $return;
	}

	function select_num($tablename, $where = array(), $select = "*",$tablename2='',$special=''){
		if(!$this->checkTableName($tablename)){
			
			return false;
		}
	
		$cachename=$tablename.$where;
		if(!$return=$this->Memcache_set($cachename)){//获取是否存在memcache
			if($tablename2){
				

				$SQL = "SELECT count($select) as num FROM " . $this->def . $tablename . " as a," . $this->def . $tablename2 . " as b  WHERE $where";
			}else{

				
				
				$whereNew	=	$this->checkWhere($where);
 
			
				$SQL = "SELECT count($select) as num FROM " . $this->def . $tablename . $whereNew;

				
			}
			 
			$query = $this->db->query($SQL);
			while($row=$this->db->fetch_array($query)){$return=$row['num'];}
			$this->Memcache_set($cachename,$return);//设置memcache
		}
		if($return<1){$return='0';}
		return $return;
	}
	/**
     * 通用query查询 $tablename,$where = 1, $select="*"
	 *$special:标记某些特殊查询，不使用did参数。
	 *2016-1-14		LGL
     */
	function DB_select_query($tablename, $where = 1, $select = "*",$special='') {
		if(!$this->checkTableName($tablename)){
			
			return false;
		}
		
	    $SQL = "SELECT $select FROM " . $this->def . $tablename . " WHERE $where";
		$query=$this->db->query($SQL);
		return $query;
	}
	/**
     * 通用all查询 $tablename,$where = 1, $select="*"
	 *$special:标记某些特殊查询，不使用did参数。
     */
	function DB_select_all($tablename, $where = 1, $select = "*",$special='') {
		if(!$this->checkTableName($tablename)){
			
			return false;
		}

		$cachename=$tablename.$where;
		if(!$row_return=$this->Memcache_set($cachename)){//获取是否存在memcache
			$row_return=array();  
			 
			$SQL = "SELECT $select FROM `" . $this->def . $tablename . "`";
			
			if( $where ){
				$SQL .= " WHERE ".$where;
			}

			$query=$this->db->query($SQL);
            while($row=$this->db->fetch_array($query)){$row_return[]=$row;}
            $this->Memcache_set($cachename,$row_return);//设置memcache
		}
        return $row_return;
	}

	//新多条查询 
	function select_all($tablename, $where = array(), $select = "*",$special='') {
		if(!$this->checkTableName($tablename)){
			
			return false;
		}
       
		
		$whereNew	=	$this->checkWhere($where);
		
		$cachename  =  $tablename.$whereNew;
		
		if(!$row_return=$this->Memcache_set($cachename)){//获取是否存在memcache
		    
			$row_return=array();  
			
			
			
			$SQL = "SELECT $select FROM `" . $this->def . $tablename . "`" . $whereNew;
			$query = $this->db->query($SQL);
			
            while($row=$this->db->fetch_array($query)){
                
                $row_return[]  =  $row;
            }
            $this->Memcache_set($cachename,$row_return);//设置memcache
		}
        return $row_return;
	}
	/**
     * 通用all查询双表 $tablename1,$tablename2, $where = 1, $select = "*"
     */
	function DB_select_alls($tablename1,$tablename2, $where = 1, $select = "*") {
		if(!$this->checkTableName($tablename1)){
			
			return false;
		}
		if(!$this->checkTableName($tablename2)){
			
			return false;
		}
		$cachename=$tablename1.$tablename2.$where;
		if(!$row_return=$this->Memcache_set($cachename)){//获取是否存在memcache


			$SQL = "SELECT $select FROM " . $this->def . $tablename1. " as a," . $this->def . $tablename2 . " as b WHERE $where";

			$query=$this->db->query($SQL);
            while($row=$this->db->fetch_array($query)){$row_return[]=$row;}
            $this->Memcache_set($cachename,$row_return);//设置memcache
		}

        return $row_return;
	}
	/**

     * 单表单条插入 $tablename, $value
     */
	function DB_insert_once($tablename, $value){
		if(!$this->checkTableName($tablename)){
			
			return false;
		}
		if(in_array($tablename,$this->sitetable) && strpos($value,'`did`')===false){
			$value.=",`did`='".$this->config['did']."'";
		} 
		$SQL = "INSERT INTO `" . $this->def . $tablename . "` SET ".$value;
 
		$this->db->query("set sql_mode=''");
		$this->db->query($SQL);
		$nid= $this->db->insert_id();

         // 写日志到 data/sql_log.txt
        if (defined('SQL_LOG') && SQL_LOG === true) {
            $time = date('Y-m-d H:i:s');
            $line = "[{$time}] {$SQL}\n";
            $logFile = defined('APP_PATH') ? APP_PATH . 'insert_once-log.txt' : dirname(__FILE__) . '/../../sqlDB_insert_once_log.txt';
            file_put_contents($logFile, $line, FILE_APPEND);
        }


		return $nid;
	}

    //一次插入多条数据，方便本地测试数据库构造数据进行测试
    function DB_insert_multi($tablename, $valueArr){
        // INSERT INTO `roles` (`uid`,`rid`) VALUES
        //    (534,14),(535,14),(536,14),(537,14),(539,14)
	    if(!$this->checkTableName($tablename)){
		    return false;
	    }
        include(PLUS_PATH.'dbstruct.cache.php');
        $TableFullName=$this->def.$tablename;
        if(is_array($$TableFullName)){
            $fieldsArr = array_keys($$TableFullName);
        }
        if($fieldsArr){
            $fields = array();
            foreach($valueArr[0] as $f => $v){
                if(in_array($f, $fieldsArr)){
                    $fields [] = "`$f`";
                }
            }
            $fields = implode(',', $fields);
          
            $values = array();
            foreach($valueArr as $r){
                $arr = array();
                foreach($r as $k => $v){
                    if(in_array($k, $fieldsArr)){
                        $arr [] = "'" . $this->db->escape_string($v) . "'";
                    }
                }
                $values [] = '(' . implode(',', $arr) . ')';
            }
            $values = implode(',', $values);
            $SQL = "INSERT INTO `{$this->def}{$tablename}` ($fields) VALUES $values";
            $this->db->query("set sql_mode=''");
            $return=$this->db->query($SQL);
            return $return;
        }
    }
	/**
     * 更新 $tablename, $value, $where = 1
     */
	function DB_update_all($tablename, $value, $where ,$pecial=''){
		if(!$this->checkTableName($tablename)){
			
			return false;
		}
        
		if(empty($where) || empty($value)){
		    return false;
		}
		
        $SQL = "UPDATE `" . $this->def . $tablename . "` SET $value  ".$where; 

 
        $this->db->query("set sql_mode=''");
		$return=$this->db->query($SQL);


		// 写日志到 data/sql_log.txt
    	if (defined('SQL_LOG') && SQL_LOG === true) {
    	    $time = date('Y-m-d H:i:s');
    	    $line = "[{$time}] {$SQL}\n";
    	    $logFile = defined('APP_PATH') ? APP_PATH . 'update_all_log.txt' : dirname(__FILE__) . '/../../db_update_all_log.txt';
    	    file_put_contents($logFile, $line, FILE_APPEND);
    	}

		return $return;
	}

	/**
     * 删除 $tablename, $value, $where = 1
     */
	function DB_delete_all($tablename, $where, $limit = 'limit 1',$pecial='', $norecycle = ''){
		if(!$this->checkTableName($tablename)){
			
			return false;
		}
		if(empty($where)){
			return false;
		}
		if($pecial!=$tablename){
			//快速创建简历保存数据不进入回收站
			if(!in_array($tablename,array('temporary_resume')) && $norecycle != '1'){
				$this->insert_recycle($tablename,$this->site_fetchsql($where,$tablename));//先执行回收站
			}
			$SQL = "DELETE FROM `" . $this->def . $tablename . "` WHERE ".$this->site_fetchsql($where,$tablename)." $limit";
		}else{
			//快速创建简历保存数据不进入回收站
			if(!in_array($tablename,array('temporary_resume')) && $norecycle != '1'){
				$this->insert_recycle($tablename,$where);//先执行回收站
			}
			$SQL = "DELETE FROM `" . $this->def . $tablename . "` WHERE ".$where." $limit";
		}
		
		$this->db->query("set `sql_mode`=''");
		return $this->db->query($SQL);
	}
	/**
	 *	@desc 删除数据库
	 *  @param	$tablename	数据表名； 
				$where		删除数据表数据查询条件;
				$limit		删除记录数
				$pecial		暂未发现使用
				$norecycle	1：不做回收站插入操作
	 */
	function delete_all($tablename, $where = array(), $limit = 'limit 1', $pecial='', $norecycle = '')
	{
		
		if(!$this->checkTableName($tablename)){
			
			return false;
		}
		include(PLUS_PATH.'dbstruct.cache.php');
        
		

		$whereNew	=	$this->checkWhere($where);

		 
		if(!empty($whereNew)){
			
			//快速创建简历保存数据不进入回收站
			if(!in_array($tablename,array('temporary_resume')) && $norecycle!='1'){
		
				$this->insert_recycle($tablename,$whereNew);//先执行回收站
			}

			$SQL	= "DELETE FROM `" . $this->def . $tablename . "` ".$whereNew." ".$limit;

		}else{

			return false;
		}
		 
		$this->db->query("set `sql_mode`=''");
		return $this->db->query($SQL);
	}
	/**
     * 删除进入回收站
     */
	function insert_recycle($tablename,$where){

		if(!$this->checkTableName($tablename)){
			
			return false;
		}
		//回收站同一次操作生成数据MD5用作识别同一次操作的标识符
		if(!$this -> __isset('recyclemd5')){

			$recyclemd5 = md5($tablename.$where);

			$this -> __set('recyclemd5',$recyclemd5);

		}else{
			//获取当前操作模块的统一标识符
			$recyclemd5 =  $this -> __get('recyclemd5');
		}

		
		
		if(!isset($_GET['isdel']) || $_GET['isdel']!="all"){

			$value	=	"tablename = '".$tablename."', ";
			$value	.=	"ctime = '".time()."', ";
			
			if(isset($_SESSION['auid']) && isset($_SESSION['ausername']) && $_SESSION['auid'] && $_SESSION['ausername']){

				$username	=	$_SESSION['ausername'];
			}else{

				$username	=	isset($_COOKIE['username']) ? $_COOKIE['username'] : '';
			}

			$value	.=	"username = '".$username."', ";

			$query = $this->db->query("SELECT * FROM " . $this->def . $tablename . "  $where");
			$row_del = array();
			while($row=$this->db->fetch_assoc($query)){$row_del[]=$row;}
			
			if(!empty($row_del)){

				$recIds	=	array();
 
				foreach($row_del as $delvalue){
					
					$this->DB_insert_once("recycle",$value."`body`='".serialize($delvalue)."',`ident`='".$recyclemd5."'");

					//$recIds[]	=	$recId;
				}
				/*
				if(!empty($data)){

					$tRecycle	=	$this->select_once('recycle', array('body' =>$data['body'], 'type' => '1'));

					if(!empty($tRecycle)){

						$this->update_once('recycle', array('rids' => $tRecycle['rids'].','.pylode(',', $recIds)), array('body' => $data['body'], 'type' => 1));
						
					}else{
					

						//	插入一条总记录数据
						$tValue	=	array(
							'username'	=>	'',
							'tablename'	=>	'',
							'body'		=>	$data['body'],
							'ctime'		=>	time(),
							'type'		=>	1,
							'rids'		=>	pylode(',', $recIds)
						);
						$this->insert_into('recycle',$tValue);
					}
				}
				*/

			}
		
		}
	}
    /**
     * 自定义SQL执行 便于多表联合、left join等组合查询语句
     */
	function DB_query_all($sql,$type='one'){

		$query = $this->db->query($sql);
		if($type=='all'){
			 while($row=$this->db->fetch_array($query)){$return[]=$row;}
		}else{
			$return=$this->db->fetch_array($query);
		}
		return $return;
	}
	/**
     * 通用单条查询$tablename,$where = 1, $select="*"
	
     */
	function DB_select_once($tablename, $where = 1, $select = "*",$special='') {

		if(!$this->checkTableName($tablename)){
			
			return false;
		}
		
		$cachename=$tablename.$where;

		if(!$return=$this->Memcache_set($cachename)){//获取是否存在memcache
			
			$SQL = 'SELECT '.$select.' FROM ' . $this->def . $tablename;
			if($where)
			{
				$SQL .= ' WHERE '.$where;
			}
			$SQL .= ' LIMIT 1';
			$query = $this->db->query($SQL);
			$return=$this->db->fetch_array($query);
			$this->Memcache_set($cachename,$return);//设置memcache
		}
		return $return;
	}
	//新单条查询
	function select_once($tablename, $where = array(), $select = "*",$special='') {

		if(!$this->checkTableName($tablename)){
			
			return false;
		}
		
		
		$whereNew = $this->checkWhere($where);
		
		$cachename=$tablename.$whereNew;

		if(!$return=$this->Memcache_set($cachename)){//获取是否存在memcache
			
			$SQL = 'SELECT '.$select.' FROM ' . $this->def . $tablename . $whereNew;
			
			$SQL .= ' LIMIT 1';
  
			$query = $this->db->query($SQL);
			$return=$this->db->fetch_array($query);
			$this->Memcache_set($cachename,$return);//设置memcache
		}
		return $return;
	}

	function member_log($content,$opera='',$type='',$uid=null,$usertype=null){//会员日志
	    if(!$uid && !$usertype){
	        $uid = intval($_COOKIE['uid']);
	        $usertype = intval($_COOKIE['usertype']);
	    }
	    if($uid && $usertype){
	        $value="`uid`='".$uid."',";
	        $value.="`usertype`='".$usertype."',";
			$value.="`content`='".$content."',";
			$value.="`opera`='".$opera."',";
			$value.="`type`='".$type."',";
			$value.="`ip`='".fun_ip_get()."',";
			$value.="`ctime`='".time()."'";
			$this->DB_insert_once("member_log",$value);
		}
	}
    function FormatOptions($Options){
        if(!is_array($Options)){return array('field'=>'*','where'=>'');}
        $WhereStr='';
        if($Options['field']){
            $Field=$Options['field'];
            unset($Options['field']);
        }else{
            $Field='*';
        }
		if($Options['special']){
            $special=$Options['special'];
            unset($Options['special']);
        }
		if($Options['groupby']){
            $WhereStr.=' group by '.$Options['groupby'];
        }
        if($Options['orderby']){
            $WhereStr.=' order by '.$Options['orderby'];
        }

		if($Options['desc']){
            $WhereStr.=" ".$Options['desc'];
        }
        if($Options['limit']){
            $WhereStr.=' limit '.$Options['limit'];
        }
        return array('field'=>$Field,'order'=>$WhereStr,"special"=>$special);
    }
	function FormatWhere($Where){
        $WhereStr='1';
        foreach($Where as $k=>$v){
            //两种方式：1，'uid'=>1 ； 2，'`uid` in (1,2,3)'
            //TODO：检查字段是否存在
            if(is_numeric($k)){
                if((substr(trim($v),0,3)=='and')||(substr(trim($v),0,2)=='or')){
                    $WhereStr.=' '.$v;
                }elseif($v){
                    $WhereStr.=' and '.$v;
                }
            }else{
				/* where允许的格式：
					$where = array('uid' => $this->uid,
						'name <> ' => '',
						'age > '   => '18',
						'height <' => '65'
					);
				*/
				if(strpos($k,'<>') > 0){
					$position = strpos($k,'<>');
					$fieldName = trim(substr($k,0,$position));
					$WhereStr .=' and `'.$fieldName.'` <> \''.$v.'\'';
				}
				elseif(strpos($k,'<') > 0){
					$position = strpos($k,'<');
					$fieldName = trim(substr($k,0,$position));
					$WhereStr .=' and `'.$fieldName.'` < \''.$v.'\'';
				}
				elseif(strpos($k,'>') > 0){
					$position = strpos($k,'>');
					$fieldName = trim(substr($k,0,$position));
					$WhereStr .=' and `'.$fieldName.'` > \''.$v.'\'';
				}
				else{
					$WhereStr .=' and `'.$k.'`=\''.$v.'\'';
				}
            }
        }
        return $WhereStr;
    }
	function FormatValues($Values){
        $ValuesStr='';
        foreach($Values as $k=>$v){
            //两种方式：1，'uid'=>1 ； 2，'`uid` in (1,2,3)'
            //TODO：检查字段是否存在
			if(preg_match("/^[a-zA-Z0-9_]+$/",$k)){
				 if(preg_match('/^[0-9]+$/', $k)){
					$FiledList = @explode(',',$v);
					if(is_array($FiledList)){
						foreach($FiledList as $Fv){
							$FvList = @explode('=',$Fv);
							if($FvList[1]){
									if(strpos($FvList[1],'+') > 0){
										$FiledV = @explode('+',$FvList[1]);
										$ValuesStr.=',`'.str_replace("`",'',$FvList[0]).'`=`'.str_replace("`",'',$FiledV[0]).'`+\''.intval(str_replace("'",'',$FiledV[1])).'\'';
									}
									if(strpos($FvList[1],'-') > 0){
										$FiledV = @explode('-',$FvList[1]);
										$ValuesStr.=',`'.str_replace("`",'',$FvList[0]).'`=`'.str_replace("`",'',$FiledV[0]).'`-\''.intval(str_replace("'",'',$FiledV[1])).'\'';
									}

							}
						}
					}
				}else{
					$ValuesStr.=',`'.$k.'`=\''.$v.'\'';
				}
			}

        }
        return substr($ValuesStr,1);
    }
	
	//提醒处理
    function RemindDeal($TableName,$Values=array(),$Where=array()){
		if(!$this->checkTableName($tablename)){
			
			return false;
		}
        $ValuesStr.=$this->FormatValues($Values);
        $WhereStr='';
        $WhereStr.=$this->FormatWhere($Where);
        $this->DB_update_all($TableName,$ValuesStr,$WhereStr);
    }
    function site_fetchsql($Where,$TableName,$SplitChar=' and '){
		if(!$this->checkTableName($TableName)){
			
			return false;
		}
        if(in_array($TableName,$this->sitetable)){
            if(is_array($Where)&&is_numeric($this->config['did'])){
				$Where['did']=$this->config['did'];
            }else if(is_numeric($this->config['did'])){
				$Where='`did`='.$this->config['did'].$SplitChar.$Where;
            }
        }
        return $Where;
    }

	//调用分页,$table表名,$where条件，$pageurl分页链接,$limit条数,$rowsname模板接收变量,$pagenavname分页模板接收变量
	function get_page($table,$where='',$pageurl='',$limit=20,$field='*',$rowsname='rows'){
		if(!$this->checkTableName($table)){
			
			return false;
		}
		$rows=array();
		$page=$_GET['page']<1?1:$_GET['page'];
		$ststrsql=($page-1)*$limit;
		$num=$this->DB_select_num($table,$where);
		if($num>$limit){
			$pages=ceil($num/$limit);
            $pagenav=Page($page,$num,$limit,$pageurl,$notpl=false,null);
		}
		$rows=$this->DB_select_all($table,$where.' limit '.$ststrsql.','.$limit,$field);
		return array('total'=>$num,'pagenav'=>$pagenav,$rowsname=>$rows);
	}
	function fetch_assoc(){
	    return $this->db->fetch_assoc();
	}
	function checkTableName($table){
	
		 if (preg_match('/^[_a-z]{2,30}$/i',$table)){
		  return true;
		 }else {
		  return false;
		 } 
	
	}

	
	/*
	*	$data		:	where条件， 字符串value 默认==：$where[id] = '1'; 
	*								数组  value：$where['id'] = array('>','1','or/and');根据传入的or and 异或查询 默认AND
	*	$key		:	PHPYUNBTW PHPYUNBTWEND 使用（）组合优先级组合条件 如 (id>0 AND id<1)
	*	$whereNew	:	拼接后的SQL条件，默认以AND 连接 
 	*/
	function checkWhere($where){
		
		
		$whereNew	=	'';
		
		if(!empty($where) && is_array($where)){
		    
		    $limit  =  '';
		    
		    if(!empty($where['limit'])){
			
		        if(is_array($where['limit']))
				{

					$limit	=	' LIMIT '.(int)$where['limit'][0].','.(int)$where['limit'][1];

				}else{

				
					$limit	=	' LIMIT '.(int)$where['limit'];
				}

				unset($where['limit']);
				
			}
			
			$orderby  =  '';
			
			if(!empty($where['orderby'])){

				if(is_array($where['orderby']))
				{
					
					$orderby	=	' ORDER BY ';
					
					foreach($where['orderby'] as $key=>$value){
            
						if($key>0)
						{
							$orderby	.=	',';
						}
           
						$orders		 =	@explode(',',$value);

						$orderby	.=	$orders[0].' ';
						$orderby	.=	strtoupper(trim($orders[1])) == 'ASC' ? 'ASC':'DESC';
						
						$whereFiled[]	=	$orders[0];
						

					}
					
         

				} elseif (stripos($where['orderby'], 'CASE WHEN') !== false)
				{
				    //CASE THEN END 条件排序
				    
				    $orderby        =   ' ORDER BY '.$where['orderby'];
				    $whereFiled[]	=	'CASE';
				    
				}else{
         
					$orders		 =	@explode(',',$where['orderby']);
					
					$orderby	 =	' ORDER BY '.$orders[0].' ';
					$orderby	.=	strtoupper(trim($orders[1])) == 'ASC' ? 'ASC':'DESC';

					$whereFiled[]	=	$orders[0];
				}
				unset($where['orderby']);
				
			}
      
			if(!empty($where['orderbyfield'])){

				if(is_array($where['orderbyfield']) && count($where['orderbyfield']) == 2){
          
					$orderby	=	' ORDER BY field('.$where['orderbyfield'][0].','.$where['orderbyfield'][1].')';

					$whereFiled[]	=	$where['orderbyfield'][0];

				}

				unset($where['orderbyfield']);
			}
			
			$groupby  =  $having =   '';
			
			if(!empty($where['groupby'])){
                
			    //   GROUP BY    和   HAVING 联合查询
			    //   $key    :   having ;    $value  :   array(); 
			    //   例如            ：     array('having' => array('distance' => array('<', 20, ''), 'x' => array('<>', 0, 'AND')))  
			    if (!empty($where['having'])) {
			        
			        $groupby		=	' GROUP  BY '.$where['groupby'];
			        
					$whereFiled[]	=	$where['groupby'];

			        foreach ($where['having'] as $key => $value){
			            
			            if (is_array($value)) {
			                
			                //   HAVING 多条件查询; $value[2] : 异或查询 
			                if($value[2] == 'OR' || $value[2] == 'or'){
			                    
			                    $btnAndOR    =   ' OR ';
			                }else if($value[2] == 'AND' || $value[2] == 'and'){
			                    
			                    $btnAndOR    =   ' AND ';
			                }else{
			                    
			                    $btnAndOR    =   '';
			                }
			                
			                $having  =   $btnAndOR;
			                $having  .=  $this->spilceWhere($key, $value);
							
							$whereFiled[]	=	$key;
			            }
			            
			        }
			        
			        $groupby	.=	' HAVING '.$having;
			        
			        unset($where['having']);
			        
			    }else{
			        
			        $groupby	=	' GROUP  BY '.$where['groupby'];

					$whereFiled[] = $where['groupby'];
			    }

				unset($where['groupby']);
				
			}
			
			foreach($where as $key=>$value){
			    
			    //PHPYUNBTWSTART_A  PHPYUNBTWEND_A  PHPYUNBTWSTART_B  PHPYUNBTWEND_B 这种可以组成多个（）
				//考虑到部分需要使用（）执行优先级条件查询判断的情况
			    if(strpos($key, 'PHPYUNBTWSTART') !== false){

					if($whereNew!='')
					{
						$btwAndOr	=	($value=='OR' || $value == 'or') ?' OR ':' AND ';
					}else{
						$btwAndOr	=	'';
					}
					
					$whereNew .=	$btwAndOr.'(';

					//标识符 用于判断是否是（）内首项条件 非首项条件 必须使用 AND/OR 连接
					$btwStr    =	1;
				
			    }elseif(strpos($key, 'PHPYUNBTWEND') !== false){
			        
			        $whereNew .=	')';
			        
			        //取消标识符
			        $btwStr    =	0;
			        
			        
			    }else{
					
					//where格式不为纯字符串，则按照操作符函数进行处理
					if(!empty($value) && is_array($value))
					{
						
						//同一个字段 多项条件组合
						if(is_array($value[0])){
							
							foreach($value as $sonv){
								
								if($whereNew!='' && $btwStr != 1)
								{
									
								    $whereNew	.=	($sonv[2] == 'OR' || $sonv[2] == 'or') ? ' OR ' : ' AND ';
								}
								//根据value[0] 判断SQL操作符 并进行组合SQL语句
								
								$whereNew	.=	$this->spilceWhere($key,$sonv);

								$whereFiled[] = $key;
								
								$btwStr    =	0;
								
								}

						}else{
							if($whereNew!='' && $btwStr != 1)
							{
							    $whereNew	.=	(isset($value[2]) && ($value[2] == 'OR' || $value[2] == 'or')) ? ' OR ' : ' AND ';
							}
							//根据value[0] 判断SQL操作符 并进行组合SQL语句

							$whereNew	.=	$this->spilceWhere($key,$value);

							$whereFiled[] = $key;
						}
						
					}else{
						
						//首项条件 排除AND连接符

						if($whereNew!='' && $btwStr != 1)
						{
							$whereNew .=' AND ';
						}
						//纯字符串格式默认为 = 操作符
						$whereNew .= "`".$key."`='".$this->db->escape_string($value)."'";

						$whereFiled[] = $key;
					}
					$btwStr    =	0;
				}
			}
		}
		// 验证字段
		
		if(!empty($whereFiled)){
			foreach($whereFiled as $value){
				
				if(!preg_match("/^[a-zA-Z0-9_`()]{1,30}$/",$value)){

					$noFields = 1;
					break;
				}
			}
		}
		
		if(!isset($noFields)){
		
		
			if($whereNew)
			{
				$whereNew	=	' WHERE '.$whereNew;
			
			}
			$whereNew	.=	$groupby.$orderby.$limit;
			return $whereNew;
		}else{
			
		    $this->db->show_error();
		}
	
	
	}

	function spilceWhere($daraKey,$dataV){
	    
		$Operator = array('=','>','>=','<','<=','<>','in','notin','isnull','notnull','like','findin','between','unixtime','like%','dateformat','in_s');
		
		if(in_array($dataV[0],$Operator)){
		
		    switch($dataV[0]){
				
				case	'notin'		: $sqlString = "`$daraKey` not in (".$this->db->escape_string($dataV[1]).")";
				break;

				case	'in'		: $sqlString = "`$daraKey`  in (".$dataV[1].")";
				break;
				
				case	'isnull'	: $sqlString = "`$daraKey` is null";
				break;
				
				case	'notnull'	: $sqlString = "`$daraKey` is not null";
				break;
				
				case	'like'		: $sqlString = "`$daraKey` LIKE ('%".$this->db->escape_string($dataV[1])."%')";
				break;
				
				case	'findin'    : $sqlString = "FIND_IN_SET('".$this->db->escape_string($dataV[1])."',`$daraKey`)";
				break;
				
				case	'between'	: $betweens = explode(',', $dataV[1]);$sqlString = "$daraKey between $betweens[0] AND $betweens[1]";
				break;
				
				case    'unixtime'  : $sqlString =  "UNIX_TIMESTAMP(`$daraKey`) ".$this->db->escape_string($dataV[1])." '$dataV[2]'";
				break;
				
				case	'like%'		: $sqlString = "`$daraKey` LIKE ('".$this->db->escape_string($dataV[1])."%')";
				break;
				
				case	'dateformat': $sqlString = "date_format(`$daraKey`,'".$dataV[1]."')=date_format(now(),'".$this->db->escape_string($dataV[1])."')";
				break;
				// 字符串型的in
				case	'in_s'		: $sqlString = "`$daraKey`  in ('".$dataV[1]."')";
				break;
				
				default				: 
				    // 传入字段中包含sql函数的，两边不需要加`
				    $keystr     =  !preg_match("/[)]/",$daraKey) ? "`$daraKey` " : "$daraKey ";
				    $sqlString  =  $keystr.$this->db->escape_string($dataV[0])." '$dataV[1]'";
			}
		
		}else{
			
		    $this->db->show_error();
		}
		
		return $sqlString;
	
	}
	function uc_open(){
		include APP_PATH.'data/api/uc/config.inc.php';
		include APP_PATH.'/api/uc/include/db_mysql.class.php';
		include APP_PATH.'/api/uc/uc_client/client.php';

		return $ucinfo;
	}
}
?>