<?php
namespace Home\Model;

class PrintcodeModel extends BaseModel {

    public function getCode($code) {
        $where['p_code_number'] = array('like', $code.'%');
        $where['p_status'] = '0';
        $info = $this->where($where)->find();
        return $info['p_code_number'];
    }

    public function createCode($code) {
        $where['p_code_number'] = array('like', $code.'%');
        $where['p_status'] = '0';
        $info = $this->where($where)->setField('p_status','1');

        $data['p_status'] = '0';
        $number = rand(1000, 9999);
        $data['p_code_number'] = $code.$number;
        $id = $this->add($data);
        if ($id) {
            return $data['p_code_number'];
        } else {
            return false;
        }
    }
}