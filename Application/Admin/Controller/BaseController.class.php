<?php
namespace Admin\Controller;

use Think\Controller;

class BaseController extends Controller {

    protected $userInfo = array();

    public function __construct(){
        $this->userInfo = session('userinfo');
        if(empty($this->userInfo)){
            $this->display('Index:login');
            exit;
        }
        $this->assign('current_c', MODULE_NAME);
        $this->assign('current_a', ACTION_NAME);
    }
}