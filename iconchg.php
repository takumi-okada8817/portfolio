<?php

    //ユーザー自身の投稿画面

    require_once('function.php');

    //エラー表示(開発時のみ)
    ini_set('display_errors',1);
    error_reporting(E_ALL);

    session_start();

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

        //ユーザーネームとアイコン
        $sql = "select username,userid from users where identify=:userid";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':userid',$userid);
        $stmt->execute();
        $user_name = $stmt->fetch(PDO::FETCH_ASSOC);

        $hp_address = "user/".$user_name['userid'].".php";

        $sql = "select icon from users where identify=:userid";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':userid',$userid);
        $stmt->execute();
        $icon_path = $stmt->fetch(PDO::FETCH_ASSOC);

        $icon_path = $icon_path['icon'] === false ? "icon/default_icon.png" : $icon_path['icon'];

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

        try{
            if(isset($_FILES['newicon']['error']) && $_FILES['newicon']['error'] !== UPLOAD_ERR_OK){
                throw new Exception("<script>alert('ファイルがアップロードされていないか画像のサイズが大き過ぎます');</script>");
            }else{
                if(isset($_POST['token']) && $_POST['token'] === $_SESSION['token']){
                    switch($_FILES['newicon']['error']){
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            throw new Exception("<script>alert('ファイルがアップロードされていません');</script>");
                        case UPLOAD_ERR_INI_SIZE:
                            throw new Exception("<script>alert('画像のサイズが大き過ぎます');</script>");
                        default:
                            throw new Exception("<script>alert('エラーが発生しました。もう一度お試しください。');</script>");
                    }

                    $haystack = array(
                        'gif' => 'image/gif',
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png'
                    );
                    $result = array_search(mime_content_type($_FILES['newicon']['tmp_name']),$haystack,true);
                    if($result === false){
                        throw new Exception("<script>alert('ファイル形式が不正です');</script>");
                    }

                    $path = sprintf('icon/%1$s.%2$s',sha1_file($_FILES['newicon']['tmp_name']),$result);

                    if(move_uploaded_file($_FILES['newicon']['tmp_name'],$path)){
                        $sql = "update users set icon=:newiconpath where identify=:userid";
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(':newiconpath',$path);
                        $stmt->bindValue(':userid',$userid);
                        $stmt->execute();

                        echo "<script>alert('アイコン変更完了');</script>";
                        echo "<script>location.href = 'http://localhost:8000/setting.php';</script>";
                    }
                }
                session_regenerate_id(true);
                $token = h(generate_token());
                $_SESSION['token'] = $token;

            }
        }catch(Exception $e){
            echo $e->getMessage().PHP_EOL;
        }

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
                <img class="userimg" src="<?php if(isset($icon_path)){echo $icon_path;}else{echo "icon/default_icon.png";}?>" alt="ユーザーネーム">
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
                <div><a href="<?= $hp_address ?>"><img src="images/home-black-24dp.svg">HOME</a></div>
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
                <p>アイコン : <img class="userimg" src="<?php if(isset($icon_path)){echo $icon_path;}else{echo "images/preview2.png";}?>" alt="ユーザーアイコン" style="width:100px;height:100px;"></p>
                <p>新しいアイコン : <img id="preview" src="images/preview2.png"></p>
                <form  method="POST" enctype="multipart/form-data">
                    <input type="file" name="newicon" id="newicon" style="border: none;">
                    <input type="submit" id="change" value="変更する">
                    <input type="hidden" name="token" value="<?php echo $token ?>">
                </form>
                <span class="e-msg"><?php if(isset($error_message)){echo $error_message;} ?></span>

            </main>
        </div>

        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="javascript/modal.js"></script>
        <script>
            document.getElementById("newicon").addEventListener('change',(e)=>{
                let reader = new FileReader();
                reader.onload = (e)=>{
                    document.getElementById("preview").setAttribute('src',e.target.result);
                }
                reader.readAsDataURL(e.target.files[0]);
            });

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