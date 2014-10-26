<?php
namespace Admin\Model;

class ResourceModel extends BaseModel {

    public function getResourceInfo($resourceid) {
        $data['resource_id'] = $resourceid;
        $data['resource_print'] = 2;
        $data['resource_status'] = 2;
        $resourceInfo = $this->where($data)->find();
        return $resourceInfo;
    }

    public function getUserNoPrintResource($uid) {
        $data['resource_user'] = $uid;
        $data['resource_printer'] = '';
        $data['resource_print'] = '1';
        $resourceInfo = $this->where($data)->find();
        return $resourceInfo;
    }

    public function getResourceList($where='1') {
        $count = $this->join(' wxp_printer p on wxp_resource.resource_printer=p.printer_id')->where($where)->count();
        $page = new \Think\Page($count, 10);
        $grouplist = $this->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        $pageinfo = $page->show();
        return array('data' => $grouplist, 'page' => $pageinfo);
    }

    public function updatePrinter($data = array()) {
        foreach($data as $key=>$value){
            if($key != "id"){
                $insert[$key] = $value;
            }
        }
        return $this->where('resource_id ="'.$data['resource_id'].'"')->save($insert);
    }

    public function insertResource($post, $type = 2) {
        $data['resource_user'] = $post['fromUserName'];
        $data['resource_weixin'] = $post['toUserName'];
        if ($type == 2) {
            $data['resource_content'] = $post['picUrl'];
        } else {
            $data['resource_content'] = $post['thumbMediaId'];
        }
        $data['resource_mediaid'] = $post['mediaId'];
        $data['resource_type'] = $type;
        $data['resource_status'] = 2;
        $data['resource_print'] = 1;
        $data['resource_date'] = date('Y-m-d H:i:s');

        $where = array('resource_status'=>2, 'resource_print'=>1, 'resource_weixin'=>$post['toUserName'], 'resource_user'=>$post['fromUserName'], 'resource_printer'=>'');
        $hasprint = $this->where($where)->find();
        if ($hasprint) {
            return $this->where('resource_id="'.$hasprint['resource_id'].'"')->setField($data);
        } else {
            return $this->add($data);
        }
    }

    public function updateResourceCode($post) {
        $where = array('resource_status' => 2, 'resource_print' => 1, 'resource_weixin' => $post['toUserName'], 'resource_user' => $post['fromUserName'], 'resource_printer' => '');
        $hasprint = $this->where($where)->find();
        $result = 'a';
        if ($hasprint) {
            $printcode = new \Home\Model\PrintcodeModel();
            $isok = $printcode->where('p_code_number = "'.$post['content'].'" and p_status = "0"')->find();
            if ($isok) {
                $resetfield = array('resource_printer'=>$post['content'], 'resource_print' => 2);
                $isprint = $this->where('resource_id="'.$hasprint['resource_id'].'"')->setField($resetfield);
                if ($isprint) {
                    $printerCode = preg_replace('/\d+/i', '', $post['content']);
                    $result = $printcode->createCode($printerCode);
                }
            } else {
                $result = 'b';
            }
        }
        return $result;
    }

    public function updateResourceContent($rid, $rescontent) {
        return $this->where('resource_id="'.$rid.'"')->setField('resource_content', $rescontent);
    }

    public function deleteResourceByWx($resource_weixin = '') {
        return $this->where('resource_weixin = "'.$resource_weixin.'"')->delete();
    }
}