<?php

require_once  './db.php';

$add  = rand(2,10);
$res = $dbh->query("select * from wxw_look where id = 1")->fetch();
if($res){//该条数据存在
    $look = $res['look'];
    $look = $look?$look:0;
    $look += $add;
    $sql = " update wxw_look set look = '$look' where id = 1";
}else{//首次进入 新增
    $sql = "insert into wxw_look(`id`,`look`) value(1,$add)";
}
$dbh->query($sql);

?>
<?php
    require_once '/www/wwwroot/zmteh.gulu28.top/zidongpinglun/db.php';
    $number = $dbh->query("select * from wxw_look where id = 1")->fetch();
    $numberLook = $number['look']?$number['look']:0;
    echo '文章总数 '.$numberLook.'篇';
?>
