<?php
namespace Admin\Controller;

class AuthController extends BaseController {

    public function listAction(){
        $auth = D('authrule');
        $authdata = $auth->getAuthList();
        $this->assign('authlist', $authdata['data']);
        $this->assign('page', $authdata['page']);
        $this->display();
    }

    public function refreshAction() {
        $authobj = D('authrule');
        $authobj->deleteAuth();
        $authdesc = C('AUTH_NAME');
        $adminCtrlFiles = glob(APP_PATH.'Admin/Controller/*Controller.class.php');
        $adminAuth = array();
        foreach ($adminCtrlFiles as $ctrlFiles) {
            if (strpos($ctrlFiles, 'Base') !== false) {
                continue;
            }
            $controllerFileContents = file_get_contents($ctrlFiles);
            if (preg_match_all("/class (.*?)Controller/is", $controllerFileContents, $controllerMatches, PREG_SET_ORDER)) {
                if (preg_match_all("/public function ([^.]*?)Action/is", $controllerFileContents, $actionMatches)) {
                    $ctrlName = $controllerMatches[0][1];
                    foreach ($actionMatches[1] as $action) {
                        $authname = 'Admin-'.$ctrlName.'-'.$action;
                        $adminAuth[] = array('name'=>$authname, 'title'=>$authdesc[$authname]);
                    }
                }
            }
        }
        $authobj->addAll($adminAuth);

//        $homeCtrlFiles = glob(APP_PATH.'Home/Controller/*Controller.class.php');
//        $homeAuth = array();
//        foreach ($homeCtrlFiles as $ctrlFiles) {
//            if (strpos($ctrlFiles, 'Base') !== false) {
//                continue;
//            }
//            $controllerFileContents = file_get_contents($ctrlFiles);
//            if (preg_match_all("/class (.*?)Controller/is", $controllerFileContents, $controllerMatches, PREG_SET_ORDER)) {
//                if (preg_match_all("/public function ([^.]*?)Action/is", $controllerFileContents, $actionMatches)) {
//                    $ctrlName = $controllerMatches[0][1];
//                    foreach ($actionMatches[1] as $action) {
//                        $authname = 'Home-'.$ctrlName.'-'.$action;
//                        $homeAuth[] = array('name'=>$authname, 'title'=>$authdesc[$authname]);
//                    }
//                }
//            }
//        }
//        $authobj->addAll($homeAuth);
        $this->redirect('list');
    }
}