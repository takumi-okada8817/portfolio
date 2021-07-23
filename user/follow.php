<?php

require('../function.php');
require('../home.php');

try{
    $dbh = new PDO('データベース情報');
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    echo 'データベースに接続失敗'.PHP_EOL;
    echo $e->getMessage();
}

//POSTされたuniqid
$followId = h($_POST['uniqid']);

//usersテーブルでユーザーが存在しているか確認
$sql = 'select uniqid from users where uniqid=:uniqid';
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':uniqid',$followId);
$stmt->execute();
//usersに登録されているuniqidの元データ
$follow = $stmt->fetch(PDO::FETCH_ASSOC);

//自分のアドレスとpostされたuniqidでfollowテーブルでフォローしているか確認
$sql = 'select follow from follow where email=:email and follow=:uniqid';
$stmt = $dbh->prepare($sql);
$stmt->bindValue('email',$userid);
$stmt->bindValue('uniqid',$followId);
$stmt->execute();
//followテーブルのuniqid
$checkfollow = $stmt->fetch(PDO::FETCH_ASSOC);

//フォローしてない時にnotice出さない用
$followcheck = $checkfollow["follow"] ?? false;

if($followId === $follow["uniqid"] && $follow["uniqid"] !== $followcheck){
    $sql = 'insert into follow(email,follow) values(:email,:follow)';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':email',$userid);
    $stmt->bindValue(':follow',$follow["uniqid"]);
    $stmt->execute();

}elseif($followId === $follow["uniqid"] && $follow["uniqid"] === $checkfollow["follow"]){
    $sql = 'delete from follow where email=:email and follow=:follow';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':email',$userid);
    $stmt->bindValue(':follow',$follow["uniqid"]);
    $stmt->execute();
}

?>