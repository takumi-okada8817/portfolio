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

|Column|Type|Options|
| :---: | :---: | :---: |
|id|int(11)|null:false|
|identify|varchar(255)|null:false|
|userid|varchar(100)|null:false|
|username|varchar(100)|null:false|
|password|varchar(255)|null:false|
|uniqid|varchar(23)|null:false|
|icon|varchar(50)|null:false|

* posttextテーブル

|Column|Type|Options|
| :---: | :---: | :---: |
|id|int(11)|null:false|
|identify|varchar(255)|null:false|
|posttext|varchar(140)|null:false|
|created|timestamp|null:false,current_timestamp|
|username|varchar(100)|null:false|

* followテーブル

|Column|Type|Options|
| :---: | :---: | :---: |
|followid|int(11)|null:false|
|email|varchar(255)|null:false|
|follow|varchar(255)|null:false|


#### 機能

* ユーザー登録、ログイン機能

* 投稿機能

* フォロー機能(Ajax)

* 無限スクロール(Ajax)

* 検索機能
