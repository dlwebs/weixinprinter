<?php
namespace Admin\Controller;

class SystemController extends BaseController {

    public function settingAction(){
        $systemobj = D('system');
        $systemArray = I("post.");
        if(count($systemArray)){
            $systemnumber = $systemobj->updateSystem($systemArray);
        }
        $systeminfo = $systemobj->getSystemInfo();
        $this->assign('systeminfo', $systeminfo);
        $this->display();
    }
}