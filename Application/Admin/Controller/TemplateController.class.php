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
            $this->success('删除模板成功');
        } else {
            $this->error('删除模板失败');
        }
    }

    public function saveAction() {
        $post = filterAllParam('post');
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
        $template = D('Template');
        if (isset($post['id']) && $post['id']) {
            $tplnumber = $template->updateTemplate($post);
            if ($tplnumber) {
                $id = $post['id'];
            } else {
                $id = '';
            }
        } else {
            $id = $template->addTemplate($post);
        }
        if ($id) {
            $this->success('保存模板成功', 'list');
        } else {
            $this->error('保存模板失败');
        }
    }
}