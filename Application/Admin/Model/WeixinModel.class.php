<?php
namespace Admin\Model;

class WeixinModel extends BaseModel {

    public function deleteWeixin($id = '') {
        return $this->where('weixin_id = "'.$id.'"')->delete();
    }
    
    public function deleteOwnWeixin($wxid, $userid) {
        return $this->where('weixin_id = "'.$wxid.'" and weixin_userid = "'.$userid.'"')->delete();
    }

    public function getWeixinList($show = '', $where = '') {
        if ($show == 'all') {
            $wxlist = $this->where($where)->order('weixin_regdate')->select();
            $pageinfo = array();
        } else {
            $count = $this->count();
            $page = new \Think\Page($count, 10);
            $wxlist = $this->where($where)->order('weixin_regdate')->limit($page->firstRow.','.$page->listRows)->select();
            $pageinfo = $page->show();
        }
        return array('data' => $wxlist, 'page' => $pageinfo);
    }

    public function getWeixinByToken($token) {
        return $this->where('weixin_token = "'.$token.'"')->find();
    }

    public function getWeixinById($id) {
        return $this->where('weixin_id = "'.$id.'"')->find();
    }
    
    public function getOwnWeixinById($wxid, $userid) {
        return $this->where('weixin_id = "'.$wxid.'" and weixin_userid = "'.$userid.'"')->find();
    }

    public function addWeixin($data = array()) {
        $data['weixin_regdate'] = date('Y-m-d H:i:s');
        return $this->add($data);
    }

    public function updateWeixin($data = array()) {
        $id = $data['id'];
        unset($data['id']);
        unset($data['delweixin_imgcode']);
        return $this->where('weixin_id="'.$id.'"')->setField($data);
    }
}