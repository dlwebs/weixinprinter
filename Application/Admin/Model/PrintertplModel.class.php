<?php
namespace Admin\Model;

class PrintertplModel extends BaseModel {

    public function getPrintertplById($printer_id) {
        return $this->where('printertpl_printer = "'.$printer_id.'" and printertpl_content != ""')->select();
    }
    public function getPrintertplByCondition($condition){
        return $this->where($condition)->select();
    }

    public function addPrintertpl($data = array()) {
        foreach($data as $key=>$value){
            $insert[$key] = $value;
        }
        return $this->add($insert);
    }

    public function updatePrintertpl($data = array()) {
        foreach($data as $key=>$value){
            //if($key != "printertpl_id"){
                $insert[$key] = $value;
           //}
        }
        return $this->where('printertpl_id ="'.$data['printertpl_id'].'"')->save($insert);
    }
    public function delPrintertplByPrinterId($printer_id){
        $printTplArray = $this->where('printertpl_printer = "'.$printer_id.'"')->select();
        for($i = 0; $i < count($printTplArray); $i ++){
            if($printTplArray[$i]["printertpl_content"] && substr($printTplArray[$i]["printertpl_content"], 0,4) != "http"){
                unlink("./upload/".$printTplArray[$i]["printertpl_content"]);
            }
        }
        return $this->where('printertpl_printer = "'.$printer_id.'"')->delete();
    }

    public function delPrintertplByPrintertplId($printertpl_id){
        $printTplArray = $this->where('printertpl_id = "'.$printertpl_id.'"')->select();
        for($i = 0; $i < count($printTplArray); $i ++){
            if($printTplArray[$i]["printertpl_content"] && substr($printTplArray[$i]["printertpl_content"], 0,4) != "http"){
                unlink("./upload/".$printTplArray[$i]["printertpl_content"]);
            }
        }
        return $this->where('printertpl_id = "'.$printertpl_id.'"')->delete();
    }
}