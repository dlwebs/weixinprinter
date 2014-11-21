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

    public function getDetailById($resourceid) {
        $data['resource_id'] = $resourceid;
        $resourceInfo = $this->where($data)->join(' left join wxp_user u on resource_user=u.user_id left join wxp_weixin w on resource_weixin=w.weixin_token')->find();
        /**
        if($resourceInfo['resource_type'] == '1'){
            $weixin_appsecret = $resourceInfo["weixin_appsecret"];
            $weixin_appid = $resourceInfo["weixin_appid"];
            $curl_url = ("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$weixin_appid."&secret=".$weixin_appsecret);
            $output = getPage($curl_url);
            $jsoninfo = json_decode($output, true);
            $access_token = $jsoninfo["access_token"];
            $resource_url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token."&media_id=".$resourceInfo['resource_mediaid'];
            $output = getPage($resource_url);
            $resourceInfo['resource_content'] = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token."&media_id=".$resourceInfo['resource_mediaid'];
            print_r($resourceInfo);
            $access_token = ""; //下载图片 
            $mediaid = "QQ9nj-7ctrqA8t3WKU3dQN24IuFV_516MfZRZNnQ0c-BFVkk66jUkPXF49QE9L1l"; 
            $url = "$access_token&media_id=$mediaid"; 
            $fileInfo = downloadWeixinFile($url); 
            $filename = "down_image.jpg"; 
            saveWeixinFile($filename, $fileInfo["body"]); 
        }
        */
        return $resourceInfo;
    }
    public function getResourceList($where='1') {
        $page = new \Think\Page($count, 10);
        $grouplist = $this->join(' left join wxp_printer p on left(resource_printer, 3)=p.printer_code left join wxp_weixin w on resource_weixin=w.weixin_token')->order("resource_date desc")->limit($page->firstRow.','.$page->listRows)->where($where)->select();
        $pageinfo = $page->show();
        return array('data' => $grouplist, 'page' => $pageinfo);
    }

    public function updateResource($data = array()) {
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
        $data['resource_status'] = 1;
        $data['resource_print'] = 1;
        $data['resource_date'] = date('Y-m-d H:i:s');
        $data['resource_checker'] = '';
        $data['resource_checkdate'] = '';

        $where = array('resource_print'=>1, 'resource_weixin'=>$post['toUserName'], 'resource_user'=>$post['fromUserName'], 'resource_printer'=>'');
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
    
    public function updateResourcePrintStatus($rid, $printstatus) {
        return $this->where('resource_id="'.$rid.'"')->setField('resource_print', $printstatus);
    }

    public function deleteResourceByWx($resource_weixin = '') {
        return $this->where('resource_weixin = "'.$resource_weixin.'"')->delete();
    }

    public function countResourceByUserid($userid) {
        return $this->where('resource_user = "'.$userid.'"')->count();
    }
    
    public function getResourceByPrinter($code) {
        $where['resource_printer'] = array('like', $code.'%');
        $where['resource_status'] = '2';
        $where['resource_print'] = '1';
        return $this->where($where)->order('resource_date desc')->limit(1)->find();
    }
    
    public function countResourceByPrinter($code, $daterange = array()) {
        $where['resource_printer'] = array('like', $code.'%');
        if (count($daterange)) {
            $where['resource_date']  = array('between', $daterange);
        }
        $where['resource_print'] = '2';
        return $this->where($where)->count();
    }
    
    public function countResourcePrinted($weixin = array(), $daterange = array()) {
        if (count($weixin)) {
            $where['resource_weixin'] = array('in', $weixin);
        }
        if (count($daterange)) {
            $where['resource_date']  = array('between', $daterange);
        }
        $where['resource_print'] = '2';
        return $this->where($where)->count();
    }
    
    public function countTotalResource($weixin = array()) {
        $where = array();
        if (count($weixin)) {
            $where['resource_weixin'] = array('in', $weixin);
        }
        return $this->where($where)->count();
    }
}