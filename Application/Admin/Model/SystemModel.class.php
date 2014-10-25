<?php
namespace Admin\Model;

class SystemModel extends BaseModel {

    public function getSystemInfo($system_id='1') {
        $data['system_id'] = $system_id ;
        $systemInfo = $this->where($data)->find();
        return $systemInfo;
    }
	public function getSystemInfoByUser($user_id) {
        $data['system_user'] = $user_id ;
        $systemInfo = $this->where($data)->find();
        return $systemInfo;
    }
    public function getSystemList($show = '', $where='system_id = 1') {
        if ($show == 'all') {
            $systemlist = $this->where($where)->select();
            $pageinfo = array();
        } else {
            $count = $this->where($where)->count();
            $page = new \Think\Page($count, 10);
            $systemlist = $this->where($where)->limit($page->firstRow.','.$page->listRows)->select();
            $pageinfo = $page->show();
        }
        return array('data' => $grouplist, 'page' => $pageinfo);
    }
	public function getSystemById($systemid = '') {
        return $this->where('system_id = "'.$systemid.'"')->find();
    }
	public function addSystem($data = array()) {
		foreach($data as $key=>$value){
			$insert[$key] = $value;
		}
        return $this->add($insert);
    }
	public function updateSystem($data = array()) {
		foreach($data as $key=>$value){
			$insert[$key] = $value;
		}
		if($data['system_user']){
			$array = $this->getSystemInfoByUser($data['system_user']);
			if(count($array)){
				return $this->where('system_user ='.$data['system_user'])->save($insert);
			}else{
				return $this->addSystem($insert);
			}
		}
    }
	public function deleteSystemById($systemid = '') {
        return $this->where('system_id = "'.$systemid.'"')->delete();
    }
}