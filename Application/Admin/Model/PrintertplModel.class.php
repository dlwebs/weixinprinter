<?php
namespace Admin\Model;

class PrintertplModel extends BaseModel {

    public function getPrintertplById($printer_id) {
        return $this->where('printertpl_printer = "'.$printer_id.'"')->select();
    }
	public function addPrintertpl($data = array()) {
        foreach($data as $key=>$value){
            $insert[$key] = $value;
        }
        return $this->add($insert);
    }

    public function updatePrintertpl($data = array()) {
        foreach($data as $key=>$value){
            if($key != "id"){
                $insert[$key] = $value;
            }
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
}