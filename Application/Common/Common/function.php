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

function getPage($url = '', $method = 'get', $data = array()) {
    if (!$url) {
        return '';
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($method == 'post' || $method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    if (substr($url, 0, 5) == "https"){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}