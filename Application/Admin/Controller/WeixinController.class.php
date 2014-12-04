<?php
namespace Admin\Controller;

class WeixinController extends BaseController {

    public function fansAction() {
        $is_follow = $_GET['is_follow'];
        $search_token = $_GET['search_token'];

        $group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
        $userobj = D('user');
        $weixin = D('weixin');
        $resource = D('resource');
        $fans = array();
        if ($group_id == 1) {
            $ownWeixin = $weixin->getWeixinList('all');
            $ownWeixin = $ownWeixin['data'];
            $where = 'user_pw = ""';
            if ($is_follow) {
                $where .= ' and user_follow = "'.$is_follow.'"';
            }
            if ($search_token) {
                $where .= ' and user_weixin = "'.$search_token.'"';
            }
            $fansList = $userobj->getUserList($where);
        } else {
            $ownWeixin = $weixin->getOwnWeixinById('', $user_id);
            $weixin_number = array();
            foreach ($ownWeixin as $wx) {
                $weixin_number[] = $wx['weixin_token'];
            }
            $user_weixin = implode('","', $weixin_number);
            $fansList = $userobj->getUserList('user_weixin in ("'.$user_weixin.'")');
        }
        foreach ($fansList['data'] as $userfans) {
            $wxinfo = $weixin->getWeixinByToken($userfans['user_weixin']);
            $userfans['weixin_name'] = $wxinfo['weixin_name'];
            $userfans['resource_number'] = $resource->countResourceByUserid($userfans['user_id']);
            $fans[] = $userfans;
        }
        $this->assign('is_follow', $is_follow);
        $this->assign('search_token', $search_token);
        $this->assign('wxlist', $ownWeixin);
        $this->assign('fanslist', $fans);
        $this->assign('page', $fansList['page']);
        $this->display();
    }

    public function blacklistAction() {
        $group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
        $userobj = D('user');
        $weixin = D('weixin');
        $resource = D('resource');
        $blacklist = array();
        $fansList = $userobj->getBlackList();
        foreach ($fansList['data'] as $userfans) {
            $wxinfo = $weixin->getWeixinByToken($userfans['user_weixin']);
            $userfans['weixin_name'] = $wxinfo['weixin_name'];
            $userfans['resource_number'] = $resource->countResourceByUserid($userfans['user_id']);
            $blacklist[] = $userfans;
        }
        $this->assign('blacklist', $blacklist);
        $this->assign('page', $fansList['page']);
        $this->display();
    }

    public function listAction(){
        $group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
        $weixin = D('weixin');
        if ($group_id == 1) {
            $wxdata = $weixin->getWeixinList();
        } else {
            $wxdata = $weixin->getWeixinList('', 'weixin_userid = "'.$user_id.'"');
        }
        $this->assign('wxlist', $wxdata['data']);
        $this->assign('page', $wxdata['page']);
        $this->display();
    }

    public function addAction(){
        $randToken = \Org\Util\String::randString(10, 5);
        $callbackUrl = 'http://'.$_SERVER['SERVER_NAME'].'/index.php/api/'.$randToken;
        $this->assign('token', $randToken);
        $this->assign('callbackUrl', $callbackUrl);
        
        $userobj = D('user');
        $userlist = $userobj->getUserList('id != 1 and user_pw != ""', 'all');
        $this->assign('userlist', $userlist);
        $this->display();
    }

    public function modweixinAction(){
        $weixin_id = I('get.wxid');
        $weixin = D('weixin');
        
        $group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
        if ($group_id == 1) {
            $wxinfo = $weixin->getWeixinById($weixin_id);
        } else {
            $wxinfo = $weixin->getOwnWeixinById($weixin_id, $user_id);
        }
        if (!$wxinfo) {
            $this->error('公众号不存在');
        }
        $this->assign('wxinfo', $wxinfo);
        
        $userobj = D('user');
        $userlist = $userobj->getUserList('id != 1 and user_pw != ""', 'all');
        $this->assign('userlist', $userlist);
        $this->display();
    }

    public function delweixinAction(){
        $weixin_id = I('get.wxid');
        $weixin = D('weixin');
        $group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
        $wxinfo = $weixin->getWeixinById($weixin_id);
        if ($wxinfo) {
            if ($group_id == 1) {
                $isok = $weixin->deleteWeixin($weixin_id);
            } else {
                $isok = $weixin->deleteOwnWeixin($weixin_id, $user_id);
            }
        }

        if ($isok) {
            $resource = D('resource');
            $isresourcedel = $resource->deleteResourceByWx($wxinfo['weixin_token']);
            $this->success('删除公众号成功');
        } else {
            $this->error('删除公众号失败');
        }
    }

    public function saveAction() {
        $post = filterAllParam('post');
        if (!$post['id'] && !$post['weixin_number']) {
            $this->error('请填写公众号原始ID');
        }
        if (!$post['weixin_callbackurl']) {
            $this->error('请填写回调地址');
        }
        if (!$post['weixin_name']) {
            $this->error('请填写公众号名称');
        }
        if (!$post['weixin_appid']) {
            $this->error('请填写AppID');
        }
        if (!$post['weixin_appsecret']) {
            $this->error('请填写AppSecret');
        }
        if (!$post['weixin_token']) {
            $this->error('请填写Token');
        }
        if (!$post['weixin_userid']) {
            $post['weixin_userid'] = $this->userInfo['user_id'];
        }
        $weixin = D('weixin');
        if (!isset($post['id']) || !$post['id']) {
            $systemobj = D("system");
            $sysinfo = $systemobj->getSystemInfoByUser($post['weixin_userid']);
            $ownWxNum = $weixin->countOwnWeixinById($post['weixin_userid']);
            if ($sysinfo['system_wxnum'] <= $ownWxNum) {
                $this->error('最多只允许添加'.$sysinfo['system_wxnum'].'个微信公众号');
            }
        }
        
        $isdelcopyrightimage = $post['delweixin_copyright'];
        if ($isdelcopyrightimage) {
            $post['weixin_copyright'] = '';
            unlink('./upload/copyright/'.$isdelcopyrightimage);
        }
        if ($_FILES['weixin_copyright']['name']) {
            $upload = new \Think\Upload();
            $upload->maxSize = 3145728;//3M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = './upload/copyright/';
            $uploadinfo = $upload->uploadOne($_FILES['weixin_copyright']);
            if(!$uploadinfo) {
                $this->error($upload->getError());
            }
            $post['weixin_copyright'] = $uploadinfo['savepath'].$uploadinfo['savename'];
        }

        $isdelimage = $post['delweixin_imgcode'];
        if ($isdelimage) {
            $post['weixin_imgcode'] = '';
            unlink('./upload/'.$isdelimage);
        }
        if ($_FILES['weixin_imgcode']['name']) {
            $upload = new \Think\Upload();
            $upload->maxSize = 3145728;//3M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = './upload/';
            $uploadinfo = $upload->uploadOne($_FILES['weixin_imgcode']);
            if(!$uploadinfo) {
                $this->error($upload->getError());
            }
            $post['weixin_imgcode'] = $uploadinfo['savepath'].$uploadinfo['savename'];
        } else {
            if ($isdelimage) {
                $this->error('请上传公众号二维码');
            }
        }
        if (isset($post['id']) && $post['id']) {
            $edunumber = $weixin->updateWeixin($post);
            if ($edunumber) {
                $id = $post['id'];
            } else {
                $id = '';
            }
        } else {
            $id = $weixin->addWeixin($post);
        }
        if ($id) {
            $this->success('保存公众号成功', 'list');
        } else {
            $this->error('保存公众号失败');
        }
    }
}