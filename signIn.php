<?php

    //新規登録画面

    //エラー表示(開発時のみ)
    ini_set('display_errors',1);
    error_reporting(E_ALL);

    require_once('function.php');

    session_start();

    if(isset($_POST['submit'])){

        //入力値のサニタイズ
        $clean = array();
        
        if(isset($_POST)){
            foreach($_POST as $key => $value){
                $clean[$key] = htmlspecialchars($value,ENT_QUOTES);
            }
            unset($value);
        }
        
        $identify = $clean['identify'];
        $userid = $clean['userid'];
        $username = $clean['username'];
        $password = $clean['password'];
            
        if(isset($identify) && isset($userid) && isset($username) && isset($password)){

            if(isset($userid)){
                $error_message = space_check($userid);
            }else{
                $error_message = "未入力です";
            }
            if(isset($username)){
                $error_message = space_check($username);
            }else{
                $error_message = "未入力です";
            }

            //有効なメールアドレスかどうかのフィルター
            $clean_email = $identify;
            if($_POST['identify'] === $clean_email && filter_var($clean_email,FILTER_VALIDATE_EMAIL)){
                $valid_identify = $clean_email;
            }else if($_POST['identify'] === '' || !preg_match("/[^\s　]/", $_POST['identify'])){
                $error_message = '未入力または空白を除いて入力してください';
            }else{
                $error_message = "このメールアドレスは有効ではありません"; //フォームの下に表示したい
            }

            //パスワードのハッシュ化
            if(preg_match('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i',$password)){
                $valid_password = password_hash($password,PASSWORD_DEFAULT);
            }else if(strlen($password)<8 || !strlen($password) === mb_strlen($password)){
                $error_message = "パスワードは数字を1文字以上含んだ8文字以上の半角英数字で設定してください";
            }else if($_POST['password'] === '' || !preg_match("/[^\s　]/", $_POST['password'])){
                $error_message = "未入力または空白を除いて入力してください";
            }

            //useridのバリデーション　文字数制限
            if(!preg_match('/[\W]+|[ |　]+/i',$userid) && $userid > 5){
                $valid_userid = $userid;
            }elseif(mb_strlen($userid)<=5){
                $error_message = "5文字以上入力してください";
                return false;
            }else{
                $error_message = "数字、ローマ字、アンダーバー以外の文字は無効です";
                return false;
            }

            try{
            $dbh = new PDO('データベース情報');
            $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            }catch(PDOException $e){
                echo 'データベースに接続失敗'.PHP_EOL;
                return $e->getMessage();
            }

            try{
                //メールアドレスの重複チェック
                $sql = "select identify from users where identify = :valid_identify";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':valid_identify',$valid_identify);
                $stmt->execute();
                $address = $stmt->fetch();

                $address = $address['identify'];

                //ユーザーIDの重複チェック
                $sql = "select userid from users where userid = :valid_userid";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':valid_userid',$valid_userid);
                $stmt->execute();
                $user_id_data = $stmt->fetch();

                $user_id_data = $user_id_data['userid'];
            
                if($address === $valid_identify){
                    throw new Exception("このアドレスはすでに登録されています");
                }elseif($user_id_data === $valid_userid){
                    throw new Exception("このIDはすでに登録されています");
                }else{

                    $usersuniqid = h(usersuniq());

                    $sql = 'insert into users(identify,userid,username,password,uniqid) values(:valid_identify,:userid,:username,:valid_password,:uniqid)';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(':valid_identify',$valid_identify);
                    //useridはユーザーIDとしてホーム画面のファイル名にしてタイムラインの名前のaタグから飛べるようにする
                    //だから、メールアドレスと同じように重複チェックをする必要がある
                    $stmt->bindValue(':userid',$userid);
                    $stmt->bindValue(':username',$username);
                    $stmt->bindValue(':valid_password',$valid_password);
                    $stmt->bindValue(':uniqid',$usersuniqid);
                    $stmt->execute();

                    session_regenerate_id(true);
                    $_SESSION['name'] = $username;
                    $_SESSION['email'] = $valid_identify;
                
                    setcookie('useridentify',$result['identify'],time()+60*60*24*7);

                    //ユーザーのフルネームでファイルを作成
                    $user = $userid;
                    $newuser = "user/";
                    $newuser .= $user;
                    $newuser .= ".php";
                    copy('home.php',$newuser);

                    header('Location:http://localhost:8000/timeline.php');

                    

                    //自動返信
                    /* $subject = "ご登録ありがとうございます。";
                    $message = "この度は、ご登録ありがとうございます。以下の内容でご登録を受け付けました。"."\n";
                    $message .= "メールアドレス:". $valid_identify."\n";
                    $message .= "フルネーム:".$userid."\n";
                    $message .= "ユーザーネーム:".$username."\n\n";
                    $message .= "Find a small happiness運営";
                    mb_send_mail($valid_identify,$subject,$message); */
                    exit;
                }

            }catch(Exception $e){
                $error_message = $e->getMessage();
            }

        }else{
            $error_message = "未入力の項目があります";
        }
    }

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>新規登録 / Find a small happiness</title>
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
            <a href="index.php" id="title">
                <h1>Find a small happiness</h1>
            </a>
        </header>

        <div class="wrapper">
            <main id="main">
                <div id="contents">
                    <div id="registration">
                        <h1>新規登録</h1>
                        <h2>小さなしあわせを探してみよう</h2>
                        <div class="borderline"></div>
                        <div id="contentsform">
                            <form action="signIn.php" method="POST" name="signinform">
                                <div id="form">
                                    <div>
                                        <label>メールアドレス<br>
                                        <input type="text" name="identify" value="">
                                        <span id="identify" style="display: none; color: rgb(255, 82, 82);"></span>
                                        <span class="e-msg"><?php if(isset($error_message)){echo $error_message;} ?></span>
                                        </label>
                                    </div>
                                    <div>
                                        <label>ユーザーID<br>
                                        <input type="text" name="userid" value="">
                                        <span id="userid" style="display: none; color: rgb(255, 82, 82);"></span>
                                        <span class="e-msg"><?php if(isset($error_message)){echo $error_message;} ?></span>
                                        </label>
                                        <span class="notice">ユーザーIDは一度登録すると変更できません。</span>
                                    </div>
                                    <div>
                                        <label>ユーザーネーム<br>
                                        <input type="text" name="username" value="">
                                        <span id="username" style="display: none; color: rgb(255, 82, 82);"></span>
                                        <span class="e-msg"><?php if(isset($error_message)){echo $error_message;} ?></span>
                                        </label>
                                    </div>
                                    <div class="password">
                                        <label>パスワード<br>
                                        <input type="password" name="password" required value="">
                                        <span id="password" style="display: none; color: rgb(255, 82, 82);"></span></label>
                                        <span class="notice">パスワードは半角英数字を1文字以上含んだ8文字以上で設定してください</span>
                                    </div>
                                </div>
                                <div id="submit">
                                    <button type="submit" name="submit" value="signup" disabled="disabled">登録する</button>
                                </div>
                                <p id="policy">登録することで、<a href="">Findの利用規約</a>、<a href="">データに関するポリシー</a>、<a href="">Cookieポリシー</a>に同意するものとします。</p>
                            </form>
                        </div>
                    </div>
                
                    <div id="tologin">
                        <p>アカウントをお持ちですか？<a href="index.php" >ログインする</a></p>
                    </div>
                </div>
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
        <script src="javascript/checkforms.js"></script>
        <script>
            $(function(){
                $('input[name="identify"],input[name="userid"],input[name="username"],input[name="password"]').on('keyup',()=>{
                    var value = $('input[name="identify"],input[name="userid"],input[name="username"],input[name="password"]').val();
                    if($('input[name="identify"]').val() === "" || $('input[name="userid"]').val() === "" || $('input[name="username"]').val() === "" || $('input[name="password"]').val() === "" || !value.match(/[^\s\t]/)){
                        $('button').prop("disabled",true);
                    }else{
                        $('button').removeAttr('disabled');
                    }
                });
            });
        </script>
    </body>
</html>