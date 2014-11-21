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
        $daterange = array();
        for ($i=7;$i>0;$i--){
            $tempdate = strtotime("-$i day");
            $daterange[] = date('Y-m-d', $tempdate);
        }
        $this->assign('daterange', implode('","', $daterange));
        if ($group_id == 1) {
            $userNumber = $userobj->getShopUser();
            $this->assign('user_number', $userNumber);
            $printedNumber = $resourceobj->countResourcePrinted();
            $this->assign('printed_number', $printedNumber);
            $resourceNumber = $resourceobj->countTotalResource();
            $this->assign('res_number', $resourceNumber);
            
            
            $this->assign('flotchart_title', '一周商户打印统计');
            $shopuserName = array();
            $shopuserPrint = array();
            $shopuserPrinterNum = array();
            $shopusers = $userobj->getShopUser(0);
            foreach ($shopusers as $value) {
                $shopuserName[] = $value['user_name'];
                
                $own_weixin = array();
                $ownWx = $wxobj->getOwnWeixinById('', $value['user_id']);
                foreach ($ownWx as $value1) {
                    $own_weixin[] = $value1['weixin_token'];
                }
                $dateprintedNumber = array();
                foreach ($daterange as $value2) {
                    $searchrange = array($value2.' 00:00:00', $value2.' 23:59:59');
                    $dateprintedNumber[] = $resourceobj->countResourcePrinted($own_weixin, $searchrange);
                }
                $printer_num = $printerobj->countPrinterNumber($own_weixin);
                $shopuserPrint[] = '{name:"'.$value['user_name'].'", type:"line", stack:"总量", data:['.  implode(',', $dateprintedNumber).']}';
                $shopuserPrinterNum[] = '{value:'.$printer_num.', name:"'.$value['user_name'].'"}';
            }
            $this->assign('flotchart_name', implode('","', $shopuserName));
            $this->assign('flotchart_data', implode(',', $shopuserPrint));
            
            $this->assign('piechart_name', implode('","', $shopuserName));
            $this->assign('piechart_data', implode(',', $shopuserPrinterNum));
            $this->assign('piechart_title', '商户开通打印机数量');
        } else {
            $own_weixin = array();
            $ownWx = $wxobj->getOwnWeixinById('', $user_id);
            $weixin_name = array();
            foreach ($ownWx as $value) {
                $own_weixin[] = $value['weixin_token'];
                
                $weixin_name[] = $value['weixin_name'];
                $printer_num = $printerobj->countPrinterNumber($value['weixin_token']);
                $shopuserPrinterNum[] = '{value:'.$printer_num.', name:"'.$value['weixin_name'].'"}';
            }
            $printedNumber = $resourceobj->countResourcePrinted($own_weixin);
            $this->assign('printed_number', $printedNumber);
            $resourceNumber = $resourceobj->countTotalResource($own_weixin);
            $this->assign('res_number', $resourceNumber);
            
            
            $this->assign('flotchart_title', '一周打印机使用统计');
            $printerdata = $printerobj->getPrinterList('all', array('printer_weixin'=>array('in', $own_weixin)));
            $printerdata = $printerdata['data'];
            $shopuserName = array();
            $shopuserPrint = array();
            foreach ($printerdata as $value) {
                $shopuserName[] = $value['printer_name'];
                
                $dateprintedNumber = array();
                foreach ($daterange as $value2) {
                    $searchrange = array($value2.' 00:00:00', $value2.' 23:59:59');
                    $dateprintedNumber[] = $resourceobj->countResourceByPrinter($value['printer_code'], $searchrange);
                }
                $shopuserPrint[] = '{name:"'.$value['printer_name'].'", type:"line", stack:"总量", data:['.  implode(',', $dateprintedNumber).']}';
            }
            $this->assign('flotchart_name', implode('","', $shopuserName));
            $this->assign('flotchart_data', implode(',', $shopuserPrint));
            
            $this->assign('piechart_name', implode('","', $weixin_name));
            $this->assign('piechart_data', implode(',', $shopuserPrinterNum));
            $this->assign('piechart_title', '公众号绑定打印机数量');
        }
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