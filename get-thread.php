<?php

$offset = $_POST['offset'];
$limit = 10;

session_start();
$userid = $_SESSION['email'];

try{
    $dbh = new PDO('データベース情報');
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    echo 'データベースに接続失敗'.PHP_EOL;
    echo $e->getMessage();
}
try{
    $sql = "select * from users left join posttext on users.identify=posttext.identify where uniqid = any (select follow from follow where email=:userid) or posttext.identify=:userid order by created desc limit $offset, $limit";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':userid',$userid);
    $stmt->execute();

    $userpost = $stmt->fetchAll(PDO::FETCH_ASSOC);

}catch(Exception $e){
    echo $e->getMessage().PHP_EOL;
}

?>
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