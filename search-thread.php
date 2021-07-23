<?php

$offset = $_POST['offset'];
$limit = 10;

session_start();
$userid = $_SESSION['email'];
$getsearch = $_SESSION['getsearch'];

try{
    $dbh = new PDO('データベース情報');
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    echo 'データベースに接続失敗'.PHP_EOL;
    echo $e->getMessage();
}
try{
    $sql = "select posttext.username,created,posttext,userid,icon from posttext join users on posttext.identify=users.identify where posttext.username like '%$getsearch%' or posttext.posttext like '%$getsearch%' order by created desc limit $offset, $limit";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    $upost = $stmt->fetchAll(PDO::FETCH_ASSOC);

}catch(Exception $e){
    echo $e->getMessage().PHP_EOL;
}

?>
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
                <a class="user" href=<?=$user;?>><img src="<?=$icon?>" alt="ユーザーネーム"></a>
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