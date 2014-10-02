<?php
namespace Admin\Controller;

class WeixinController extends BaseController {

    public function listAction(){
        $weixin_number = I('post.weixin_number');
        $weixin = D('weixin');
        if ($weixin_number) {
            $wxdata = $weixin->searchWeixin($weixin_number);
        } else {
            $wxdata = $weixin->getWeixinList();
        }
        $this->assign('weixin_number', $weixin_number);
        $this->assign('wxlist', $wxdata['data']);
        $this->assign('page', $wxdata['page']);
        $this->display();
    }

    public function addAction(){
        $this->display();
    }

    public function modweixinAction(){
        $weixin_id = I('get.wxid');
        $weixin = D('weixin');
        $wxinfo = $weixin->getWeixinById($weixin_id);
        $this->assign('wxinfo', $wxinfo);
        $this->display();
    }

    public function delweixinAction(){
        $weixin_id = I('get.wxid');
        $weixin = D('weixin');
        $wxinfo = $weixin->deleteWeixin($weixin_id);
        if ($wxinfo) {
            // todo $this->success('保存公众号成功', 'list');
        } else {
            $this->error('删除公众号失败');
        }
    }

    public function saveAction() {
        $post = filterAllParam('post');
        if (!$post['id'] && !$post['weixin_number']) {
            $this->error('请填写公众号帐号');
        }
        if (!$post['weixin_callbackurl']) {
            $this->error('请填写回调地址');
        }
        if (!$post['weixin_token']) {
            $this->error('请填写Token');
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
        $weixin = D('weixin');
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