<?php

    //ユーザー自身の投稿画面
    //コピー専用

    //SQLで使う変数
    $offset = 0;
    $limit = 10;

    require_once('../function.php');

    $token = h(generate_token());

    //エラー表示(開発時のみ)
    ini_set('display_errors',1);
    error_reporting(E_ALL);

    session_start();

    if(isset($_SESSION['name'])){
        $username = $_SESSION['name'];//自分の
        $userid = $_SESSION['email'];

        //検索→aタグで飛んだ先のアドレスでDBからポストを取得
        $url = h($_SERVER['REQUEST_URI']);
        preg_match('/(?P<user>\w+).php/',$url,$m);
        $user = h($m['user']);

        try{
            $dbh = new PDO('データベース情報');
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

            $icon_path = $user_data['icon'] === "" ? "../icon/default_icon.png" : $user_data['icon'];
            $user_name = $user_data['username'];

            //訪問したユーザーのデータ
            $sql = "select posttext.username,created,posttext,users.icon from posttext join users on posttext.identify=users.identify where users.userid=:user order by created desc limit $offset, $limit";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':user',$user);
            $stmt->execute();
            $userpost = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //訪問したユーザーページの上部に表示する名前とボタン用のuniqid
            $sql = "select username,uniqid,identify from users where userid=:user";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':user',$user);
            $stmt->execute();
            $uid = $stmt->fetch(PDO::FETCH_ASSOC);

            //フォローしているかどうか
            $sql = 'select count(follow) from follow where email=:email and follow=:uniqid';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue('email',$userid);
            $stmt->bindValue('uniqid',$uid['uniqid']);
            $stmt->execute();
            $check = $stmt->fetch(PDO::FETCH_ASSOC);

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

                    $sql = "select posttext.username,created,posttext from posttext join users on posttext.identify=users.identify where users.userid=:user order by created desc limit $offset, $limit";
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
        <title>ホーム / Find a small happiness</title>
        <link rel="stylesheet" href="../css/style.css">
        <link rel="icon" type="image/x-icon" href="../images/round_flight_takeoff_black_24dp.png">
        <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@300&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital@1&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@300&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Sawarabi+Mincho&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Work+Sans&display=swap" rel="stylesheet">
    </head>
    <body>
        <header>
            <div>
                <img class="userimg" src="<?php if(isset($icon_path) && $icon_path !== ""){echo $icon_path;}else{echo "../icon/default_icon.png";} ?>" alt="ユーザーネーム">
            </div>
            <a href="../timeline.php" id="title">
                <h1>Find a small happiness</h1>
            </a>
            <div>
                <?php if(isset($uid) && $uid['identify'] === $userid): ?>
                    <a style="cursor: pointer;" id="create" onclick="create()"><img src="../images/create-black-24dp.svg" alt="write contents"></a>
                <?php endif ?>
                <form action="../search.php" method="GET" style="display: flex;margin-bottom: 0px;">
                    <input name="searchfm" type="text">
                    <button type="submit" style="border: none;background: transparent;cursor: pointer;width: auto;"><img src="../images/search-black-18dp.svg"></button>
                </form>
            </div>
        </header>

        <!-- ハンバーガーメニュー -->
        <div class="hamburger-menu" style="display: none;">
            <div class="border">
                <div id="username"><?php if(isset($_SESSION['name'])){echo $user_name;}else{echo "ログインされていません";} ?></div>
                <div id="follower">フォロワー : <?php if(isset($follower)){echo $follower;} ?></div>
                <div id="follow">フォロー : <?php if(isset($follow)){echo $follow;} ?></div>
            </div>
            <div class="border">
                <div><a href="../timeline.php"><img src="../images/query_builder-black-24dp.svg">タイムライン</a></div>
                <div><a href="../setting.php"><img src="../images/settings-black-24dp.svg">設定</a></div>
            </div>
            <div class="border">
                <div><a href="">ヘルプ</a></div>
                <div><a href="">利用規約</a></div>
            </div>
            <div class="border"><a style="cursor: pointer;" id="logout" onclick="logout()">ログアウト</a></div>
            <div id="hamburger-copyright">&copy;Find a small happiness.<br>All rights reserved. </div>
        </div>
        <div class="hamburger-overlay"></div>

        <!-- 投稿フォーム -->
        <div class="form" style="display: none;">
            <form action="<?=$user;?>.php" method="POST" autocomplete="off">
                <textarea name="userpost" placeholder="<?php if(isset($error_message)){ echo $error_message; } ?>"></textarea>
                <input type="hidden" name="token" value="<?php echo $token ?>">
                <button type="submit" name="submit">投稿</button>
            </form>
        </div>
        <div class="form-overlay"></div>

        <div class="wrapper">
            <main id="homemain">

                <div>
                    <h1><?php if(isset($uid)){echo $uid['username'];}else{echo "ログインされていません";} ?></h1>
                    <?php if(isset($uid) && $uid['identify'] !== $userid): ?>
                        <?php if($check['count(follow)'] === "0"): ?>
                            <button type="button" id="flbt" name="follow" style="background-color: #7999b3;">follow</button>
                        <?php elseif($check['count(follow)'] > "0"):?>
                            <button type="button" id="flbt" name="follow" style="background-color: #d35978;">following</button>
                        <?php endif ?>
                        <input type="hidden" name="uniqid" value="<?=$uid['uniqid']?>">
                    <?php endif ?>
                </div>

                <?php if(isset($userpost)): ?>
                    <?php foreach($userpost as $postvalue): ?>
                        <div id="maincontents">
                                <div class="usericon">
                                    <img src="<?php if(isset($postvalue['icon']) && $postvalue['icon'] !== ""){echo $postvalue['icon'];}else{echo "../icon/default_icon.png";}?>" alt="ユーザーネーム">
                                </div>
                                <div class="post">
                                    <div id="details">
                                        <h4><?= $postvalue['username']; ?></h5>
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

                //ajax通信(読み込み)
                var set = 10;
                var ck = 0;
                var user = "<?php if(isset($user)){echo $user;}?>";

                $(window).on('scroll',()=>{

                    var space = $(document).innerHeight() - $(window).innerHeight();
                    var wintop = $(window).scrollTop();

                    if(space <= wintop+1){
                        ck++;
                        offset = set * ck;

                        $.ajax({
                            url: 'ajax/get-thread.php',
                            type: 'POST',
                            data: {
                                'offset': offset,
                                'user': user
                            },
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
                //ajax通信(follow)
                $('#flbt').on('click',()=>{

                    var uniqid = $('input[name="uniqid"]').val();

                    $.ajax({
                        url: 'follow.php',
                        type: 'POST',
                        data: {
                            'uniqid': uniqid
                        },
                        timeout: 10000,
                        dataType: 'text'
                    })
                    .done(function(){
                        if($('#flbt').text() == "follow"){
                            $('#flbt').text('following');
                            $('#flbt').css('background-color','#d35978');
                        }else{
                            $('#flbt').text('follow');
                            $('#flbt').css('background-color','#7999b3');
                        }
                    })
                    .fail(function(){
                        alert("フォロー出来ませんでした。もう一度お試しください。");
                    });
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