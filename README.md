# portfolio

#### 概要

* アカウント登録制の短文投稿サイトです。

* フレームワークを使用せず素のPHPで書いています。

#### 使用技術

* PHP 7.4.10

* MySQL 5.7.31

* Apache 2.4.46

#### データベース

* usersテーブル 

|id|int(11)|
| --- | --- |
|identify|varchar(255)|
|userid|varchar(100)|
|username|varchar(100)|
|password|varchar(255)|
|uniqid|varchar(23)|
|icon|varchar(50)|

#### 機能

* ユーザー登録、ログイン機能

* 投稿機能

* フォロー機能(Ajax)

* 無限スクロール(Ajax)

* 検索機能
