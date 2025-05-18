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
class hotkey_model extends model
{

    /**
     * 引用log类，添加用户日志
     */
    private function addAdminLog($content, $opera = '', $type = '', $opera_id = '')
    {
        require_once ('log.model.php');

        $LogM = new log_model($this->db, $this->def);

        return $LogM->addAdminLog($content, $opera = '', $type = '', $opera_id = '');
    }

    function getHotkeyOne($Where = array(), $data = array())
    {
        $field = $data['field'] ? $data['field'] : '*';

        $info = $this->select_once('hot_key', $Where, $field);
        return $info;
    }

    function upHotkey($Where = array(), $data = array())
    {
        $nid = $this->update_once('hot_key', $data, $Where);

        return $nid;
    }

    function getList($whereData, $data = array())
    {
        $List = $this->select_all('hot_key', $whereData);

        return $List;
    }

    function addInfo($setData)
    {
        $nid = $this->insert_into('hot_key', $setData);
        
        return $nid;
    }

    public function delHotkey($whereData = array())
    {
        
        $return =   array('layertype' => 0);
        
        if (! empty($whereData)) {
        
            if (! empty($whereData['id']) && $whereData['id'][0] == 'in') {
            
                $return['layertype']    =   1;
            }
            $return['id']       =   $this->delete_all('hot_key', $whereData, '');

            $return['msg']      =   '关键字';
            $return['errcode']  =   $return['id'] ? '9' : '8';
            $return['msg']      =   $return['id'] ? $return['msg'] . '删除成功！' : $return['msg'] . '删除失败！';
        } else {
            
            $return['msg']      =   '请选择要删除的内容！';
            $return['errcode']  =   8;
        }
        
        return $return;
    }

    public function recupHotkey($setData = array())
    {
        if (! empty($setData)) {
            
            $type   =   $setData['type'];
            
            $nid    =   $this -> upHotkey(array('id' => $setData['id']), array($type => $setData['rec']));
            
            $row    =   $this -> getHotkeyOne(array('id' => $setData['id']));
            
            if ($type == "bold") {
            
                $this->addAdminLog("对关键字 " . $row['name'] . " 是否加粗进行设置");
            } elseif ($type == "tuijian") {
                
                $this->addAdminLog("对关键字 " . $row['name'] . " 是否推荐进行设置");
            } elseif ($type == "check") {
                
                $this->addAdminLog("对关键字 " . $row['name'] . " 是否审核进行设置");
            }

            return $nid;
        }
    }
}
?>