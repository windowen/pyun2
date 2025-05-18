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
class index_controller extends zph_controller
{

    function index_action()
    {
        $this -> Zphpublic_action();
        //设置用户类型
        if($this->uid &&  $this->usertype &&  $this->usertype != 2){
            $typename               =   array('1' => '个人账户');
            $this -> yunset('usertypemsg', '您当前帐号名为：<span class="job_user_name_s">'.$this->username.'</span>，账户属于'.$typename[$this->usertype].'。');
        }
        $this -> yunset('typename',$typename);
        $this -> seo('zph');
        $this -> zph_tpl('index');
    }

    function review_action()
    {
        $this -> Zphpublic_action();
        $this -> seo('zph');
        $this -> zph_tpl('review');
    }

    /**
     * @desc    招聘会预定
     */
    function reserve_action()
    {
        $this -> Zphpublic_action();
        $id         =   intval($_GET['id']);
        $zphM       =   $this->MODEL('zph');

        $row        =   $zphM->getInfo(array( 'id' => $id ), array( 'pic' => 1 ));
        $this -> yunset('Info', $row);

        $rows       =   $zphM->getZphPicList(array('zid' => $id));
        $this -> yunset('Image_info', $rows);

        $space      =   $zphM->getZphSpaceInfo(array('id' => $row['sid']), array('pic' => 1,'field' => '`pic`,`content`'));
        $this -> yunset('space', $space);

        $spacelist  =   $zphM->getZphSpaceList(array('keyid' => $row['sid'], 'orderby' => 'sort,asc'), array( 'id' => $id, 'utype' => 'index' ));

        $this -> yunset('spacelist', $spacelist);

        if ($this->usertype == 2) {

            $ratingM    =   $this->MODEL('rating');
            $ratingList =   $ratingM->getList(array('display' => 1, 'orderby' => array('type,asc', 'sort,desc')));

            $rating_1 = $rating_2 = $raV = array();

            if (! empty($ratingList)) {

                foreach ($ratingList as $ratingV) {

                    $raV[$ratingV['id']] = $ratingV;

                    if ($ratingV['category'] == 1 && $ratingV['service_price'] > 0) {

                        if ($ratingV['type'] == 1) {

                            $rating_1[] = $ratingV;
                        } elseif ($ratingV['type'] == 2) {

                            $rating_2[] = $ratingV;
                        }
                    }
                }
            }
            $this->yunset('rating_1', $rating_1);
            $this->yunset('rating_2', $rating_2);

            $statisM    =   $this->MODEL('statis');
            $statis     =   $statisM->getInfo($this->uid, array( 'usertype' => 2));

            if (! empty($statis)) {
                $discount   =   isset($raV[$statis['rating']]) ? $raV[$statis['rating']] : array();
                $this -> yunset('discount', $discount);
                $this -> yunset('statis', $statis);
            }

            $add    =   $ratingM->getComSerDetailList(array('orderby' => array('type,asc', 'sort,desc')), array('pack' => '1'));
            $this -> yunset('add', $add);

        }

        $data['zph_title']  =   $row['title'];
        $data['zph_desc']   =   $this->GET_content_desc($row['body']); // 描述
        $this -> data       =   $data;
        $this -> seo("zph_reserve");
        $this -> zph_tpl('reserve');
    }
}
?>