<?php
namespace Admin\Model;

class WeixinModel extends BaseModel {

    public function deleteWeixin($id = '') {
        return $this->where('weixin_id = "'.$id.'"')->delete();
    }

    public function getWeixinList($show = '') {
		if ($show == 'all') {
            $wxlist = $this->order('weixin_regdate')->where($where)->select();
            $pageinfo = array();
        } else {
			$count = $this->count();
			$page = new \Think\Page($count, 10);
			$wxlist = $this->order('weixin_regdate')->limit($page->firstRow.','.$page->listRows)->select();
			$pageinfo = $page->show();
		}
        return array('data' => $wxlist, 'page' => $pageinfo);
    }

    public function searchWeixin($weixin_number = '') {
        $where['weixin_number'] = array('like', '%'.$weixin_number.'%');
        $count = $this->where($where)->count();
        $page = new \Think\Page($count, 10);
        $wxlist = $this->where($where)->order('weixin_regdate')->limit($page->firstRow.','.$page->listRows)->select();
        $pageinfo = $page->show();
        return array('data' => $wxlist, 'page' => $pageinfo);
    }

    public function getWeixinById($id) {
        return $this->where('weixin_id = "'.$id.'"')->find();
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