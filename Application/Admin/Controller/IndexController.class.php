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
        $printerwxobj = D("printerwx");
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

            $shopuserName = array();
            $shopuserPrint = array();
            $shopuserPrinterNum = array();
            $shopuserFansNum = array();
            $shopuserResNum = array();
            $shopusers = $userobj->getShopUser(0);
            foreach ($shopusers as $value) {
                $shopuserName[] = $value['user_name'];

                $own_weixin = array();
                $ownWx = $wxobj->getOwnWeixinById('', $value['user_id']);
                foreach ($ownWx as $value1) {
                    $own_weixin[] = $value1['weixin_token'];
                }

                if (count($own_weixin)) {
                    foreach ($daterange as $key => $value2) {
                        $searchrange = array($value2.' 00:00:00', $value2.' 23:59:59');
                        $dateprintedNumber[$key] = $resourceobj->countResourcePrinted($own_weixin, $searchrange);
                    }
                    $printer_num = $printerwxobj->countPrinterNumber($own_weixin);
                    $fans_num = $userobj->countFans($own_weixin);
                    $resource_num = $resourceobj->countTotalResource($own_weixin);
                } else {
                    $dateprintedNumber = array(0,0,0,0,0,0,0);
                    $printer_num = 0;
                    $fans_num = 0;
                    $resource_num = 0;
                }
                $shopuserPrint[] = '{name:"'.$value['user_name'].'", type:"line", stack:"总量", data:['.  implode(',', $dateprintedNumber).']}';
                $shopuserPrinterNum[] = '{value:'.$printer_num.', name:"'.$value['user_name'].'"}';
                $shopuserFansNum[] = '{value:'.$fans_num.', name:"'.$value['user_name'].'"}';
                $shopuserResNum[] = '{value:'.$resource_num.', name:"'.$value['user_name'].'"}';
            }
            $this->assign('flotchart_name', implode('","', $shopuserName));
            $this->assign('flotchart_data', implode(',', $shopuserPrint));
            $this->assign('flotchart_title', '一周商户打印统计');

            $this->assign('piechart_name', implode('","', $shopuserName));
            $this->assign('piechart_data', implode(',', $shopuserPrinterNum));
            $this->assign('piechart_title', '商户开通打印机数量');

            $this->assign('donutchart_name', implode('","', $shopuserName));
            $this->assign('donutchart_data', implode(',', $shopuserFansNum));
            $this->assign('donutchart_title', '商户拥有粉丝总数量');

            $this->assign('realchart_name', implode('","', $shopuserName));
            $this->assign('realchart_data', implode(',', $shopuserResNum));
            $this->assign('realchart_title', '商户拥有资源总数量');
        } else {
            $shopuserName = array();
            $shopuserPrint = array();
            $shopuserPrinterNum = array();
            $shopuserFansNum = array();
            $shopuserResNum = array();
            $own_weixin = array();
            $ownWx = $wxobj->getOwnWeixinById('', $user_id);
            $weixin_name = array();
            $totalfans_num = 0;
            foreach ($ownWx as $value) {
                $own_weixin[] = $value['weixin_token'];

                $weixin_name[] = $value['weixin_name'];
                $printer_num = $printerwxobj->countPrinterNumber($value['weixin_token']);
                $shopuserPrinterNum[] = '{value:'.$printer_num.', name:"'.$value['weixin_name'].'"}';
                $fans_num = $userobj->countFans($value['weixin_token']);
                $shopuserFansNum[] = '{value:'.$fans_num.', name:"'.$value['weixin_name'].'"}';
                $resource_num = $resourceobj->countTotalResource($value['weixin_token']);
                $shopuserResNum[] = '{value:'.$resource_num.', name:"'.$value['weixin_name'].'"}';
                $totalfans_num = $totalfans_num + $fans_num;
            }
            if (count($own_weixin)) {
                $printedNumber = $resourceobj->countResourcePrinted($own_weixin);
                $resourceNumber = $resourceobj->countTotalResource($own_weixin);
            } else {
                $printedNumber = 0;
                $resourceNumber = 0;
            }
            $this->assign('printed_number', $printedNumber);
            $this->assign('res_number', $resourceNumber);
            $this->assign('totalfans_number', $totalfans_num);

            if (count($own_weixin)) {
                $printerids = $printerwxobj->getPrinterByWx($own_weixin);
                $printerdata = $printerobj->getPrinterList('all', array('printer_id'=>array('in', $printerids)));
                $printerdata = $printerdata['data'];
            } else {
                $printerdata = array();
            }
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
            $this->assign('flotchart_title', '一周打印机使用统计');

            $this->assign('piechart_name', implode('","', $weixin_name));
            $this->assign('piechart_data', implode(',', $shopuserPrinterNum));
            $this->assign('piechart_title', '公众号绑定打印机数量');

            $this->assign('donutchart_name', implode('","', $weixin_name));
            $this->assign('donutchart_data', implode(',', $shopuserFansNum));
            $this->assign('donutchart_title', '公众号拥有粉丝数量');

            $this->assign('realchart_name', implode('","', $weixin_name));
            $this->assign('realchart_data', implode(',', $shopuserResNum));
            $this->assign('realchart_title', '公众号拥有资源数量');
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

    public function exportflotAction() {
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

        Vendor('PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("聚优客")->setLastModifiedBy("聚优客")->setTitle("数据EXCEL导出")->setSubject("数据EXCEL导出")->setDescription("导出数据")->setKeywords("excel")->setCategory("result file");
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '商户名称')
                                            ->setCellValue('B1', $daterange[0])
                                            ->setCellValue('C1', $daterange[1])
                                            ->setCellValue('D1', $daterange[2])
                                            ->setCellValue('E1', $daterange[3])
                                            ->setCellValue('F1', $daterange[4])
                                            ->setCellValue('G1', $daterange[5])
                                            ->setCellValue('H1', $daterange[6]);
        $alldata = array();
        if ($group_id == 1) {
            $fileName = '一周商户打印统计';
            $shopusers = $userobj->getShopUser(0);
            foreach ($shopusers as $key => $value) {
                $own_weixin = array();
                $ownWx = $wxobj->getOwnWeixinById('', $value['user_id']);
                foreach ($ownWx as $value1) {
                    $own_weixin[] = $value1['weixin_token'];
                }
                if (count($own_weixin)) {
                    foreach ($daterange as $value2) {
                        $searchrange = array($value2.' 00:00:00', $value2.' 23:59:59');
                        $dateprintedNumber[] = $resourceobj->countResourcePrinted($own_weixin, $searchrange);
                    }
                } else {
                    $dateprintedNumber = array(0,0,0,0,0,0,0);
                }
                $num = $key + 2;
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$num, $value['user_name'])
                                                    ->setCellValue('B'.$num, $dateprintedNumber[0])
                                                    ->setCellValue('C'.$num, $dateprintedNumber[1])
                                                    ->setCellValue('D'.$num, $dateprintedNumber[2])
                                                    ->setCellValue('E'.$num, $dateprintedNumber[3])
                                                    ->setCellValue('F'.$num, $dateprintedNumber[4])
                                                    ->setCellValue('G'.$num, $dateprintedNumber[5])
                                                    ->setCellValue('H'.$num, $dateprintedNumber[6]);
            }
        } else {
            $fileName = '一周打印机使用统计';
            $ownWx = $wxobj->getOwnWeixinById('', $user_id);
            $own_weixin = array();
            foreach ($ownWx as $value) {
                $own_weixin[] = $value['weixin_token'];
            }
            if (count($own_weixin)) {
                $printerids = $printerwxobj->getPrinterByWx($own_weixin);
                $printerdata = $printerobj->getPrinterList('all', array('printer_id'=>array('in', $printerids)));
                $printerdata = $printerdata['data'];
            } else {
                $printerdata = array();
            }
            foreach ($printerdata as $key => $value) {
                $dateprintedNumber = array();
                foreach ($daterange as $value2) {
                    $searchrange = array($value2.' 00:00:00', $value2.' 23:59:59');
                    $dateprintedNumber[] = $resourceobj->countResourceByPrinter($value['printer_code'], $searchrange);
                }
                $num = $key + 2;
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$num, $value['printer_name'])
                                                    ->setCellValue('B'.$num, $dateprintedNumber[0])
                                                    ->setCellValue('C'.$num, $dateprintedNumber[1])
                                                    ->setCellValue('D'.$num, $dateprintedNumber[2])
                                                    ->setCellValue('E'.$num, $dateprintedNumber[3])
                                                    ->setCellValue('F'.$num, $dateprintedNumber[4])
                                                    ->setCellValue('G'.$num, $dateprintedNumber[5])
                                                    ->setCellValue('H'.$num, $dateprintedNumber[6]);
            }
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function exportpieAction() {
        
    }

    public function exportrealAction() {
        
    }

    public function exportdonutAction() {
        
    }
}