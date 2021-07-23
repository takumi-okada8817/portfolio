<?php

    //ユーザー自身の投稿画面

    require_once('function.php');

    //エラー表示(開発時のみ)
    ini_set('display_errors',1);
    error_reporting(E_ALL);

    session_start();

    if(isset($_SESSION['name'])){
        $username = $_SESSION['name'];
        $userid = $_SESSION['email'];

        try{
            $dbh = new PDO('データベース情報');
            $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            echo 'データベースに接続失敗'.PHP_EOL;
            echo $e->getMessage();
        }
        try{
            $sql = "select username,userid,icon,uniqid from users where identify=:userid";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':userid',$userid);
            $stmt->execute();
            $user_name = $stmt->fetch(PDO::FETCH_ASSOC);

            $icon_path = $user_name['icon'] !== false ? $user_name['icon'] : false;

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
        
        //アカウント削除
        /* try{
            if(isset($_POST['acdel'])){
                if($_POST['token'] === $_SESSION['token']){    
                    $sql = "delete from users where identify=:userid";
                    $stmt->bindValue(':userid',$userid);
                    $stmt = $dbh->prepare($sql);
                    $stmt->execute();
                }
                session_regenerate_id(true);
                $token = h(generate_token());
                $_SESSION['token'] = $token;
            }
        }catch(Exception $e){
            echo $e->getMessage().PHP_EOL;
        } */
    }

    //ログアウト
    if(isset($_POST["logout"])){
        $_SESSION = array();
        setcookie(session_name(),'',time()-1800);
        setcookie('useridentify','',time()-1800);
        session_destroy();
        header('Location:http://localhost:8000/index.php');
    }
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>設定 / Find a small happiness</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="icon" type="image/x-icon" href="images/round_flight_takeoff_black_24dp.png">
        <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@300&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital@1&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@300&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Sawarabi+Mincho&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Work+Sans&display=swap" rel="stylesheet">
    </head>
    <body>
        <header>
            <div>
                <img class="userimg" src="<?php if(isset($icon_path) && $icon_path !== false){echo "../".$icon_path;}else{echo "../icon/default_icon.png";} ?>" alt="ユーザーネーム">
            </div>
            <a href="timeline.php" id="title">
                <h1>Find a small happiness</h1>
            </a>
        </header>

        <!-- ハンバーガーメニュー -->
        <div class="hamburger-menu" style="display: none;">
            <div class="border">
                <div id="username"><?php if(isset($_SESSION['name'])){echo $user_name['username'];} ?></div>
                <div id="follower">フォロワー : <?php if(isset($follower)){echo $follower;} ?></div>
                <div id="follow">フォロー : <?php if(isset($follow)){echo $follow;} ?></div>
            </div>
            <div class="border">
                <?php $hp = "user/".$user_name['userid'].".php"; ?>
                <div><a href="<?= $hp ?>"><img src="images/home-black-24dp.svg">HOME</a></div>
                <div><a href="setting.php"><img src="images/settings-black-24dp.svg">設定</a></div>
            </div>
            <div class="border">
                <div><a href="setting.php"><img src="images/help-black-24dp.svg">ヘルプ</a></div>
                <div><a href="">利用規約</a></div>
            </div>
            <div class="border"><a href="#" id="logout" onclick="logout()">ログアウト</a></div>
            <div id="hamburger-copyright">&copy;Find a small happiness.<br>All rights reserved. </div>
        </div>
        <div class="hamburger-overlay"></div>


        <div class="wrapper">
            <main id="homemain">

                <div>
                    <div class="chtop">
                        <a href="usernamechg.php">
                            > ユーザーネームの変更
                        </a>
                    </div>
                    <div class="ch">
                        <a href="iconchg.php">
                            > アイコンの変更
                        </a>
                    </div>
                    <div class="acdel">
                        <form action="" method="POST">
                            <button type="submit" name="acdel">
                                > 登録アカウントの削除
                            </button>
                            <input type="hidden" name="token" value="<?php echo $token ?>">
                        </form>
                    </div>
                </div>

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