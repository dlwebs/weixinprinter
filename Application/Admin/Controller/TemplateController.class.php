<?php
namespace Admin\Controller;

class TemplateController extends BaseController {

    public function listAction(){
        $template = D('Template');
        $tpldata = $template->getTemplateList();
        $this->assign('tpllist', $tpldata['data']);
        $this->assign('page', $tpldata['page']);
        $this->display();
    }

    public function addAction(){
        $this->display();
    }

    public function modtplAction(){
        $template_id = I('get.tid');
        $template = D('Template');
        $tplinfo = $template->getTemplateById($template_id);
        if (!$tplinfo) {
            $this->error('模板不存在');
        }
        $this->assign('tplinfo', $tplinfo);
        $this->display();
    }

    public function deltplAction(){
        $template_id = I('get.tid');
        $template = D('Template');
        $tplinfo = $template->getTemplateById($template_id);
        if ($tplinfo) {
            $isok = $template->deleteTemplate($template_id);
        }
        if ($isok) {
            unlink('./upload/'.$tplinfo['template_pic']);
            unlink(APP_PATH.'Home/View/Index/'.$tplinfo['template_code'].'.html');
            $printer = D('Printer');
            $printer->updatePrinterTpl($tplinfo['template_id']);
            $printerlist = $printer->getPrinterByTpl($tplinfo['template_id']);
            $printertpl = D('Printertpl');
            foreach ($printerlist as $p) {
                $printertpl->delPrintertplByPrinterId($p['printer_id']);
            }
            $this->success('删除模板成功');
        } else {
            $this->error('删除模板失败');
        }
    }

    public function saveAction() {
        $tpl_content = $_POST['template_content'];
        $post = filterAllParam('post');
        $template = D('Template');
        $isHaveCode = $template->getTemplateByCode($post['template_code']);
        if (!isset($post['id']) && $isHaveCode) {
            $this->error('模板代码重复');
        }
        if(!preg_match ("/^[A-Za-z0-9_]+$/", $post['template_code'])){
            $this->error("模板文件名称只能是数字、字母和下划线");
        }
        $isdelimage = $post['deltemplate_pic'];
        if ($isdelimage) {
            $post['template_pic'] = '';
            unlink('./upload/'.$isdelimage);
        }
        if ($_FILES['template_pic']['name']) {
            $upload = new \Think\Upload();
            $upload->maxSize = 3145728;//3M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = './upload/';
            $uploadinfo = $upload->uploadOne($_FILES['template_pic']);
            if(!$uploadinfo) {
                $this->error($upload->getError());
            }
            $post['template_pic'] = $uploadinfo['savepath'].$uploadinfo['savename'];
        }
        if (isset($post['id']) && $post['id']) {
//            if (!$isdelimage && !$post['template_pic']) {
//                $post['template_pic'] = $isHaveCode['template_pic'];
//            }
            $tplnumber = $template->updateTemplate($post);
            if ($tplnumber) {
                $id = $post['id'];
            } else {
                $id = '';
            }
        } else {
            $post['template_content'] = $tpl_content;
            $post['template_video'] = substr_count($tpl_content, '{$video');
            $post['template_image'] = substr_count($tpl_content, '{$image');
            $post['template_word'] = substr_count($tpl_content, '{$word');
            $id = $template->addTemplate($post);
            if ($id) {
                $tplFile = APP_PATH.'Home/View/Index/'.$post['template_code'].'.html';
                touch($tplFile);
                chmod($tplFile, 0777);
                file_put_contents($tplFile, $tpl_content);
            }
        }
        if ($id) {
            $this->success('保存模板成功', 'list');
        } else {
            $this->error('保存模板失败');
        }
    }
}