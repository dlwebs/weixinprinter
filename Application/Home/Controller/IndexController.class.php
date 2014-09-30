<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller {

    public function indexAction(){
        $userInfo = session('userinfo');
        if(empty($userInfo) || $userInfo['user_type'] == 1){
            $this->redirect('Index/login');
        } else {
            $this->display();
        }
    }
    
    public function registAction() {
        $userInfo = session('userinfo');
        if(empty($userInfo) || $userInfo['user_type'] == 1){
            $this->display();
        } else {
            $this->redirect('Index/index');
        }
    }
    
    public function doregistAction() {
        $post = filterAllParam('post');
        if (!$post['user_id']) {
            $this->error(L('alert_empty_userid'), 'regist');
        }
        if (!$post['user_pw']) {
            $this->error(L('alert_empty_userpw'), 'regist');
        }
        $user = M("User");
        $userInfo = $user->where('user_id="'.$post['user_id'].'"')->field('id')->find();
        if ($userInfo) {
            $this->error(L('alert_exists_userid'), 'regist');
        }
//        if ($_FILES['user_pic']['name']) {
//            import('ORG.Net.UploadFile');
//            $upload = new UploadFile();
//            $upload->maxSize = 3145728;//3M
//            $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');
//            $upload->savePath = './upload/';
//            if(!$upload->upload()) {
//                $this->error($upload->getErrorMsg());
//            }else{
//                $info = $upload->getUploadFileInfo();
//            }
//            $post['user_pic'] = $info[0]['savename'];
//        }
        $post['user_pw'] = md5($post['user_pw']);
        $post['user_type'] = 2;
        $userid = $user->add($post);
        if (!$userid) {
            $this->error(L('alert_adduser_fail'), 'regist');
        }
        $this->redirect('Index/login');
    }

    public function loginAction(){
        $userInfo = session('userinfo');
        if(empty($userInfo) || $userInfo['user_type'] == 1){
            $this->display();
        } else {
            $this->redirect('Index/index');
        }
    }

    public function dologinAction(){
        $userInfo = session('userinfo');
        if(!empty($userInfo) && $userInfo['user_type'] != 1){
            $this->redirect('Index/index');
        }
        $user = M("User");
        $_POST['user_id'] = I('user_id');
        $_POST['user_pw'] = md5(I('user_pw'));
        $_POST['user_status'] = 1;
        $userInfo = $user->where($_POST)->field('user_pw', true)->find();
        if(!empty($userInfo) && $userInfo['user_type'] != 1){
            session('userinfo', $userInfo);
            $this->redirect('Index/index');
        } else {
            $this->redirect('Index/login');
        }
    }

    public function logoutAction() {
        $userInfo = session('userinfo');
        if(!empty($userInfo)){
            session('userinfo', null);
        }
        $this->redirect('Index/login');
    }
}