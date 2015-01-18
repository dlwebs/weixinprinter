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

function saveWeixinMedia($url, $saveDir, $saveFile) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $package = curl_exec($ch);
    $httpinfo = curl_getinfo($ch);
    curl_close($ch);
    $media = array_merge(array('mediaBody' => $package), $httpinfo);

    preg_match('/filename=\"\w+\.(\w+)\"$/i', $media["Content-disposition"], $extmatches);
    $fileExt = $extmatches[1];
    $saveFile = $saveFile.'.'.$fileExt;
    if (!file_exists($saveDir)) {
        mkdir($saveDir, 0777, true);
    }
    file_put_contents($saveDir.$saveFile, $media['mediaBody']);
    return $saveDir.$saveFile;
}

//function downloadWeixinFile($url) {
//    $ch = curl_init($url);
//    curl_setopt($ch, CURLOPT_HEADER, 0);
//    curl_setopt($ch, CURLOPT_NOBODY, 0);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    $package = curl_exec($ch);
//    $httpinfo = curl_getinfo($ch);
//    curl_close($ch);
//    $imageAll = array_merge(array('header' => $httpinfo), array('body' => $package)); 
//    return $imageAll;
//}
// 
//function saveWeixinFile($filename, $filecontent) {
//    $local_file = fopen($filename, 'w');
//    if (false !== $local_file){
//        if (false !== fwrite($local_file, $filecontent)) {
//            fclose($local_file);
//        }
//    }
//}