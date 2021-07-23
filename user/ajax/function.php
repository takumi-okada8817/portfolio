<?php

//二重送信防止のトークン用関数
function generate_token(){
    return uniqid("",true);
}
//ユーザー特定用トークン
function usersuniq(){
    return uniqid("",true);
}
//サニタイズ
function h($str){
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
//空白チェック
function space_check($post){
    if($post === '' || !preg_match("/[^\s　]/", $post)){
        return $error_msg = '未入力または空白を除いて入力してください';
    }else{
        return null;
    }
}
//file_put_contentsで文字列を先頭に追加する関数
function file_put_beginning($str,$file_name){
    $contents = file_get_contents($file_name);
    $contents = $str . "\n" . $contents;
    file_put_contents($file_name,$contents);
}

?>