<?php

function filterAllParam($type = 'get') {
    $param = array();
    if ($type == 'get') {
        foreach ($_GET as $key => $value) {
            $param[$key] = I('get.'.$key);
        }
    } elseif ($type == 'post') {
        foreach ($_POST as $key => $value) {
            $param[$key] = I('post.'.$key);
        }
    } else {
        foreach ($_REQUEST as $key => $value) {
            $param[$key] = I('param.'.$key);
        }
    }
    return $param;
}