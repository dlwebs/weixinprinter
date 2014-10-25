<?php
namespace Admin\Controller;

use Think\Controller;
use Think\Auth;

class BaseController extends Controller {

    protected $userInfo = array();

    public function __construct(){
        parent::__construct();
        $this->userInfo = session('userinfo');
        if (ACTION_NAME != 'login' && ACTION_NAME != 'dologin') {
            if(empty($this->userInfo)){
                $this->display('Index:login');
                exit;
            }
            $auth = new Auth();
            if (!$auth->check('all', $this->userInfo['user_id'])) {
                if (!$auth->check('Admin-'.CONTROLLER_NAME.'-'.ACTION_NAME, $this->userInfo['user_id'])) {
                    $this->error('没有权限', U('Index/index'));
                }
            }
        }
        $this->assign('current_m', MODULE_NAME);
        $this->assign('current_c', CONTROLLER_NAME);
        $this->assign('current_a', ACTION_NAME);
    }
}