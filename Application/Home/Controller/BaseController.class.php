<?php
namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller {

    protected $userInfo = array();

    public function __construct(){
        parent::__construct();
        $this->userInfo = session('userinfo');
        $this->assign('current_c', MODULE_NAME);
        $this->assign('current_a', ACTION_NAME);
    }
}