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

    $token = h(generate_token());

    if(isset($_SESSION['name'])){
        $username = $_SESSION['name'];
        $userid = $_SESSION['email'];
        $userpost = array();

        try{
            $dbh = new PDO('データベース情報');
            $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            echo 'データベースに接続失敗'.PHP_EOL;
            echo $e->getMessage();
        }
        try{

            //タイムライン
            $sql = "select * from users left join posttext on users.identify=posttext.identify where uniqid = any (select follow from follow where email=:userid) or posttext.identify=:userid order by created desc limit $offset, $limit";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':userid',$userid);
            $stmt->execute();
            $userpost = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    }

    if(isset($_POST['submit'])){

        if($_POST['userpost'] === '' || !preg_match("/[^\s　]/", $_POST['userpost'])){
            $error_message = "投稿内容を入力してください。";
        }else if(mb_strlen($_POST['userpost']) > 140){
            $error_message = "140文字以上は投稿できません。";
        }else{

            if($_POST['token'] === $_SESSION['token']){

                $posttext = filter_input(INPUT_POST,'userpost',FILTER_SANITIZE_SPECIAL_CHARS);

                try{
                    //投稿ボタンが押されたら内容をposttextに保存する
                    $sql = 'insert into posttext(identify,username,posttext) values(:userid,:username,:posttext)';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(':userid',$userid);
                    $stmt->bindValue(':username',$username);
                    $stmt->bindValue(':posttext',$posttext);
                    $stmt->execute();

                    $sql = "select * from users left join posttext on users.identify=posttext.identify where uniqid = any (select follow from follow where email=:userid) or posttext.identify=:userid order by created desc limit $offset, $limit";
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(':userid',$userid);
                    $stmt->execute();
                    $userpost = $stmt->fetchAll(PDO::FETCH_ASSOC);

                }catch(Exception $e){
                    echo $e->getMessage().PHP_EOL;
                }
            }
        }
        session_regenerate_id(true);
        $_SESSION['token'] = $token;
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
        <title>タイムライン / Find a small happiness</title>
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
                <img class="userimg" src="<?php if(isset($icon_path) && $icon_path !== ""){echo $icon_path;}else{echo "icon/default_icon.png";} ?>" alt="ユーザーネーム">
            </div>
            <a href="timeline.php" id="title">
                <h1>Find a small happiness</h1>
            </a>
            <div>
                <a style="cursor: pointer;" id="create" onclick="create()"><img src="images/create-black-24dp.svg" alt="write contents"></a>
                <form action="search.php" method="GET" style="display: flex;margin-bottom: 0px;">
                    <input name="searchfm" type="text">
                    <button type="submit" style="border: none;background: transparent;cursor: pointer;width: auto;"><img src="images/search-black-18dp.svg"></button>
                </form>
            </div>
        </header>

        <!-- ハンバーガーメニュー -->
        <div class="hamburger-menu" style="display: none;">
            <div class="border">
                <div id="username"><?php if(isset($_SESSION['name'])){echo $username;}else{echo "ログインされていません";} ?></div>
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

        <!-- 投稿フォーム -->
        <div class="form" style="display: none;">
            <form action="timeline.php" method="POST" autocomplete="off" enctype="multipart/form-data">
                <textarea name="userpost" placeholder="<?php if(isset($error_message)){ echo $error_message; } ?>"></textarea>
                <input type="file" name="image">
                <input type="hidden" name="token" value="<?php echo $token ?>">">
                <button type="submit" name="submit">投稿</button>
            </form>
        </div>
        <div class="form-overlay"></div>

        <div class="wrapper">
            <main id="homemain">

                <?php if(isset($userpost)): ?>
                    <?php foreach($userpost as $postvalue): ?>
                        <?php
                            $user = "user/";
                            $user .= $postvalue['userid'];
                            $user .= ".php";

                            $icon = $postvalue['icon'] !== "" ? $postvalue['icon'] : "icon/default_icon.png";
                        ?>
                        <div id="maincontents">
                            <div class="usericon">
                                <a href=<?=$user;?>><img src="<?= $icon; ?>" alt="ユーザーネーム"></a>
                            </div>
                            <div class="post">
                                <div id="details">
                                    <a href=<?=$user;?>><?= $postvalue['username']; ?></a>
                                    <h5><?= $postvalue['created']; ?></h5>
                                </div>
                                <p id="contents"><?= $postvalue['posttext']; ?></p>
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
                            url: 'get-thread.php',
                            type: 'POST',
                            data: {'offset': offset,},
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