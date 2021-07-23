<?php

    //ユーザー自身の投稿画面

    require_once('function.php');

    //エラー表示(開発時のみ)
    ini_set('display_errors',1);
    error_reporting(E_ALL);

    session_start();

    //SQLで使う変数
    $offset = 0;
    $limit = 10;

    if(isset($_SESSION['name'])){
        $username = $_SESSION['name'];
        $userid = $_SESSION['email'];

        try{
            $dbh = new PDO('mysql:host=localhost;dbname=appsdatabase;charset=utf8','root','takumi_database_8817');
            $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            echo 'データベースに接続失敗'.PHP_EOL;
            echo $e->getMessage();
        }
        try{
            //自身のデータ
            $sql = "select username,userid,icon,uniqid from users where identify=:userid";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':userid',$userid);
            $stmt->execute();
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $hp_address = "user/".$user_data['userid'].".php";
            $icon_path = $user_data['icon'] === false ? "icon/default_icon.png" : $user_data['icon'];
            $user_name = $user_data['username'];

            //自分のフォロー数
            $sql = 'select count(email) from follow where email=:email';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue('email',$userid);
            $stmt->execute();
            $follow = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $follow = $follow['count(email)'];

            //自分のフォロワー数
            $sql = 'select count(follow) from follow where follow=:uniqid';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue('uniqid',$user_data['uniqid']);
            $stmt->execute();
            $follower = $stmt->fetch(PDO::FETCH_ASSOC);

            $follower = $follower['count(follow)'];

        }catch(Exception $e){
            echo $e->getMessage().PHP_EOL;
        }

        //GETを使った投稿検索/tokenはsnsでは更新が早いからいらない
        $check = isset($_GET['searchfm']) ? $_GET['searchfm'] : "";

        if($check !== ""){

            try{
                //htmlspecialchars
                $getsearch = h($_GET['searchfm']);

                $sql = "select posttext.username,created,posttext,userid,icon from posttext join users on posttext.identify=users.identify where posttext.username like '%$getsearch%' or posttext.posttext like '%$getsearch%' order by created desc limit $offset, $limit";
                $stmt = $dbh->prepare($sql);
                $stmt->execute();
                $upost = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $sql = "select count(*) from posttext where username like '%$getsearch%' or posttext like '%$getsearch%' order by created desc";
                $stmt = $dbh->prepare($sql);
                $stmt->execute();
                $count = $stmt->fetch(PDO::FETCH_ASSOC);

                $_SESSION['getsearch'] = $getsearch;

            }catch(Exception $e){
                echo $e->getMessage().PHP_EOL;
            }

        }
        $count = $count['count(*)'] === "" ? 0 : $count['count(*)'];

        //ログアウト
        if(isset($_POST["logout"])){
            $_SESSION = array();
            setcookie(session_name(),'',time()-1800);
            setcookie('useridentify','',time()-1800);
            session_destroy();
            header('Location:http://localhost:8000/index.php');
        }

    }
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>タイムライン / Find a small happiness</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="shortcut icon" href="images/round_flight_takeoff_black_24dp.png">
        <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@300&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital@1&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@300&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Sawarabi+Mincho&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Work+Sans&display=swap" rel="stylesheet">
    </head>
    <body>
        <header>
            <div>
                <img class="userimg" src="<?php if(isset($icon_path) && $icon_path !== ""){echo $icon_path;}else{echo "icon/default_icon.png";} ?>" alt="ユーザーネーム">
            </div>
            <a href="timeline.php" id="title">
                <h1>Find a small happiness</h1>
            </a>
            <form action="search.php" method="GET" style="display: flex;margin-bottom: 0px;">
                <input name="searchfm" type="text">
                <button type="submit" style="border: none;background: transparent;cursor: pointer;width: auto;"><img src="images/search-black-18dp.svg"></button>
            </form>
        </header>

        <!-- ハンバーガーメニュー -->
        <div class="hamburger-menu" style="display: none;">
            <div class="border">
                <div id="username"><?php if(isset($_SESSION['name'])){echo $user_name;}else{echo "ログインされていません";} ?></div>
                <div id="follower">フォロワー : <?php if(isset($follower)){echo $follower;} ?></div>
                <div id="follow">フォロー : <?php if(isset($follow)){echo $follow;} ?></div>
            </div>
            <div class="border">
                <div><a href="<?= $hp_address ?>"><img src="images/home-black-24dp.svg">HOME</a></div>
                <div><a href="setting.php"><img src="images/settings-black-24dp.svg">設定</a></div>
            </div>
            <div class="border">
                <div><a href="">ヘルプ</a></div>
                <div><a href="">利用規約</a></div>
            </div>
            <div class="border"><a href="#" id="logout" onclick="logout()">ログアウト</a></div>
            <div id="hamburger-copyright">&copy;Find a small happiness.<br>All rights reserved. </div>
        </div>
        <div class="hamburger-overlay"></div>

        <div class="wrapper">
            <main id="homemain">

            <p style="font-size: 15px;">検索結果 : <?php if(isset($count)){echo $count;}else{echo "0";} ?>件</p>

                <?php if(isset($upost)): ?>
                    <?php foreach($upost as $pval): ?>
                        <?php
                        $user = "user/";
                        $user .= $pval['userid'];
                        $user .= ".php";

                        $icon = $pval['icon'] !== "" ? $pval['icon'] : "icon/default_icon.png";
                        ?>
                        <div id="maincontents">
                            <div class="usericon">
                                <a class="user" href=<?=$user;?>><img src="<?= $icon ?>" alt="ユーザーネーム"></a>
                            </div>
                            <div class="post">
                                <div id="details">
                                    <a class="user" href=<?= $user; ?>><?= $pval['username']; ?></a>
                                    <h5><?= $pval['created']; ?></h5>
                                </div>
                                <p id="contents"><?= $pval['posttext']; ?></p>
                            </div>
                        </div>
                    <?php endforeach ?>
                <?php endif ?>
                <div class="add"></div>

            </main>
        </div>

        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="javascript/modal.js"></script>
        <script>
            $(function(){
                $('.userimg,.hamburger-overlay').on('click',()=>{
                    $('.hamburger-menu').animate({width:'toggle'},300);
                    $('.hamburger-overlay').fadeToggle(300);
                    $('.hamburger-menu,.hamburger-overlay').css('margin-top',height-70);
                });

                //ajax通信
                var set = 10;
                var ck = 0;

                $(window).on('scroll',()=>{

                    var space = $(document).innerHeight() - $(window).innerHeight();
                    var wintop = $(window).scrollTop() + 100;

                    if(space <= wintop){
                        ck++;
                        offset = set * ck;

                        $.ajax({
                            url: 'search-thread.php',
                            type: 'POST',
                            data: {'offset': offset,},
                            timeout: 10000,
                            dataType: 'text'
                        })
                        .done(function(data){
                            $('.add').append(data);
                        })
                        .fail(function(data){
                            alert("ajaxエラー");
                        })
                    }
                    return false;
                });
            });
        </script>
        <?php
        //ログインしてないときの処理
        if(!isset($_SESSION['email'])){
            echo <<<EOM
            <script>
                modal();
            </script>
            EOM;
            exit;
        }
        ?>
    </body>
</html>