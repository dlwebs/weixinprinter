<?php
namespace Admin\Model;

class TemplateModel extends BaseModel {

    public function deleteTemplate($id = '') {
        return $this->where('template_id = "'.$id.'"')->delete();
    }

    public function getTemplateList($show = '', $where = '') {
        if ($show == 'all') {
            $wxlist = $this->where($where)->select();
            $pageinfo = array();
        } else {
            $count = $this->count();
            $page = new \Think\Page($count, 10);
            $wxlist = $this->where($where)->limit($page->firstRow.','.$page->listRows)->select();
            $pageinfo = $page->show();
        }
        return array('data' => $wxlist, 'page' => $pageinfo);
    }
    
    public function getTemplateById($id) {
        return $this->where('template_id = "'.$id.'"')->find();
    }

    public function getTemplateByCode($code) {
        return $this->where('template_code = "'.$code.'"')->find();
    }

    public function addTemplate($data = array()) {
        return $this->add($data);
    }

    public function updateTemplate($data = array()) {
        $id = $data['id'];
        unset($data['id']);
        unset($data['deltemplate_pic']);
        return $this->where('template_id="'.$id.'"')->setField($data);
    }
}