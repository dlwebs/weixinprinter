<?php
namespace Admin\Controller;

use Think\Controller;

class IndexController extends Controller {

    public function indexAction(){
        $userInfo = session('userinfo');
        if(empty($userInfo) || $userInfo['user_type'] != 1){
            $this->redirect('Index/login');
        } else {
            $this->display();
        }
    }

    public function loginAction() {
        $userInfo = session('userinfo');
        if(empty($userInfo) || $userInfo['user_type'] != 1){
            $this->display();
        } else {
            $this->redirect('Index/index');
        }
    }

    public function dologinAction(){
        $userInfo = session('userinfo');
        if(!empty($userInfo) && $userInfo['user_type'] == 1){
            $this->redirect('Index/index');
        }
        $user = M("User");
        $_POST['user_id'] = I('post.user_id');
        $_POST['user_pw'] = md5(I('post.user_pw'));
        $_POST['user_status'] = 1;
        $userInfo = $user->where($_POST)->field('user_pw', true)->find();
        if(!empty($userInfo) && $userInfo['user_type'] == 1){
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