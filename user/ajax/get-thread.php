<?php

$offset = $_POST['offset'];
$limit = 10;

//個人ページ用だからuser下のディレクトリに入ってる
require_once('function.php');
session_start();
//ajaxで飛ばされたパラメーター
$user = h($_POST['user']);

try{
    $dbh = new PDO('データベース情報');
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    echo 'データベースに接続失敗'.PHP_EOL;
    echo $e->getMessage();
}
try{
    $sql = "select posttext.username,created,posttext,users.icon from posttext join users on posttext.identify=users.identify where users.userid=:user order by created desc limit $offset, $limit";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':user',$user);
    $stmt->execute();

    //取得結果が$userpost(配列)に格納,$userpostにはレコードのデータが保存される
    $userpost = $stmt->fetchAll(PDO::FETCH_ASSOC);

}catch(Exception $e){
    echo $e->getMessage().PHP_EOL;
}

?>
<?php if(isset($userpost)): ?>
    <?php foreach($userpost as $postvalue): ?>
        <div id="maincontents">
                <div class="usericon">
                    <img src="<?php if(isset($postvalue['icon']) && $postvalue['icon'] !== ""){echo "../".$postvalue['icon'];}else{echo "../../icon/default_icon.png";}?>" alt="ユーザーネーム">
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