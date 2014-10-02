<?php
namespace Admin\Model;

class ResourceModel extends BaseModel {

    public function getUserById($userid = '') {
        return $this->where('user_id = "'.$userid.'"')->find();
    }

}