<?php

    //ログイン画面

    //エラー表示(開発時のみ)
    ini_set('display_errors',1);
    error_reporting(E_ALL);

    require_once('function.php');

    if(isset($_POST['login'])){

        //セッションスタート
        session_start();

        //入力値のサニタイズ
        $clean = array();

        if(isset($_POST)){
            foreach($_POST as $key => $value){
                $clean[$key] = htmlspecialchars($value,ENT_QUOTES);
            }
        }

        $useridentify = $clean['useridentify'];
        $loginpassword = $clean['loginpassword'];

        //有効なメールアドレスかどうかのフィルター
        $clean_email = $useridentify;
        if($_POST['useridentify'] === $clean_email && filter_var($clean_email,FILTER_VALIDATE_EMAIL)){
            $valid_identify = $useridentify;
        }else if($_POST['useridentify'] === '' || !preg_match("/[^\s　]/", $_POST['useridentify'])){
            $error_message = '未入力または空白を除いて入力してください';
        }else{
            $error_message = "このメールアドレスは有効ではありません";
        }

        try{
            $dbh = new PDO('データベース情報');
            $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            $error_message = 'データベースに接続失敗'.PHP_EOL;
            return $e->getMessage();
        }

        if(isset($valid_identify)){
            try{
                $sql = 'select * from users where identify = :valid_identify';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':valid_identify',$valid_identify);
                $stmt->execute();

                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            }catch(Exception $e){
                return $e->getMessage().PHP_EOL;
            }
        }

        //DBにメールアドレスがあるかの確認
        if(!isset($result['identify'])){
            $error_message = 'メールアドレスが間違っている可能性があります';
            $result['password'] = null;
        }

        //パスワードの認証
        if(password_verify($loginpassword,$result['password'])){

            session_regenerate_id(true);
            $_SESSION['name'] = $result['username'];
            $_SESSION['email'] = $result['identify'];
            
            setcookie('useridentify',$result['identify'],time()+60*60*24*7); //HttpOnlyを属性に追加してIDを盗まれる危険性を減らす

            header('Location: http://localhost:8000/timeline.php'); //普通は直書きしない
            exit();
        }else if($_POST['useridentify'] === '' || !preg_match("/[^\s　]/", $_POST['useridentify'])){
            $error_message = '未入力または空白を除いて入力してください';
        }else{
            $error_message = 'メールアドレスまたはパスワードが間違っています';
        }
    }

?>


<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Find a small happiness</title>
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
            <a id="title">
                <h1>Find a small happiness</h1>
            </a>
        </header>

        <div class="wrapper">
            <main id="main">
                <div id="contents">
                    <div id="registration">
                        <h1>Find a small happiness</h1>
                        <div id="contentsform">
                            <form action="index.php" method="POST" name="loginform">
                                <div id="form">
                                    <div id="useridentify">
                                        <label>メールアドレス<br>
                                        <input type="text" name="useridentify">
                                        <span style="display: none; color: rgb(255, 82, 82);"></span>
                                        <span class="e-msg"><?php if(isset($error_message)){echo $error_message;} ?></span>
                                        </label>
                                    </div>
                                    <div id="password">
                                        <label>パスワード<br>
                                        <input type="password" name="loginpassword" required>
                                        <span style="display: none; color: rgb(255, 82, 82);"></span>
                                        <span class="e-msg"><?php if(isset($error_message)){echo $error_message;}?></span>
                                        </label>
                                    </div>
                                </div>
                                <div id="submit">
                                    <button type="submit" name="login" disabled="disabled">ログイン</button>
                                </div>
                            </form>
                        </div>
                    </div>
                
                    <div id="tosignIn">
                        <p>アカウントを登録されますか？<a href="signIn.php" >新規登録する</a></p>
                    </div>
                </div>
                <br>
                <br>
                <br>
            </main>
        </div>

        <footer class="footer">
            <div>
                <div class="footerrow">
                    <div class="footernav">
                        <a href="">
                            <div>Find a small happinessについて</div>
                        </a>
                    </div>
                    <div class="footernav">
                        <a href="">
                            <div>利用規約</div>
                        </a>
                    </div>
                    <div class="footernav">
                        <a href="">
                            <div>データに関するポリシー</div>
                        </a>
                    </div>
                    <div class="footernav">
                        <a href="">
                            <div>ヘルプ</div>
                        </a>
                    </div>
                </div>
                    <div id="borderline"></div>
                <div class="footerrow">
                    <div id="language">
                        <span>
                            <select>
                                <option value="jp">日本語</option>
                                <option value="en">English</option>
                            </select>
                        </span>
                    </div>
                    <div id="copyright">&copy;Find a small happiness. All rights reserved. </div>
                </div>
            </div>
        </footer>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="javascript/logincheck.js"></script>
        <script>
            $('input[name="useridentify"],input[name="loginpassword"]').on('keyup',()=>{
                var value = $('input[name="useridentify"],input[name="loginpassword"]').val();
                if($('input[name="useridentify"]').val() === "" || $('input[name="loginpassword"]').val() === "" || !value.match(/[^\s\t]/)){
                    $('button[name="login"]').prop("disabled",true);
                }else{
                    $('button[name="login"]').removeAttr('disabled');
                }
            });
        </script>
    </body>
</html>