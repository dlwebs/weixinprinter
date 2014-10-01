<?php
namespace Admin\Controller;

class IndexController extends BaseController {

    public function indexAction(){
        $this->display();
    }

    public function loginAction() {
        $this->display();
    }

    public function dologinAction(){
        $userobj = D("user");
        $user_id = I('post.user_id');
        $user_pw = I('post.user_pw');
        $userInfo = $userobj->login($user_id, $user_pw);
        if(!empty($userInfo)){
            session('userinfo', $userInfo);
            $this->success('登录成功', 'index');
        } else {
            $this->error('登录失败', 'login');
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