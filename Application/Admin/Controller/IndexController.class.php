<?php
namespace Admin\Controller;

class IndexController extends BaseController {

    public function indexAction(){
        $group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
        $userobj = D("user");
        $resourceobj = D("resource");
        $wxobj = D("weixin");
        $printerobj = D("printer");
        if ($group_id == 1) {
            $userNumber = $userobj->getShopUser();
            $this->assign('user_number', $userNumber);
            $printedNumber = $resourceobj->countResourcePrinted();
            $this->assign('printed_number', $printedNumber);
            $resourceNumber = $resourceobj->countTotalResource();
            $this->assign('res_number', $resourceNumber);
            
            $printerdata = $printerobj->getPrinterList('all');
        } else {
            $own_weixin = array();
            $ownWx = $wxobj->getOwnWeixinById('', $user_id);
            foreach ($ownWx as $value) {
                $own_weixin[] = $value['weixin_token'];
            }
            $printedNumber = $resourceobj->countResourcePrinted($own_weixin);
            $this->assign('printed_number', $printedNumber);
            $resourceNumber = $resourceobj->countTotalResource($own_weixin);
            $this->assign('res_number', $resourceNumber);
            
            $printerdata = $printerobj->getPrinterList('all', array('printer_weixin'=>array('in', $own_weixin)));
        }
        $printerdata = $printerdata['data'];
        
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