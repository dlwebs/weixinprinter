<?php
namespace Admin\Model;

class PrintertplModel extends BaseModel {

    public function getPrintertplById($printer_id) {
        return $this->where('printertpl_printer = "'.$printer_id.'"')->select();
    }
}