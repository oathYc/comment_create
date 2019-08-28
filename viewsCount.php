<?php
require_once './db.php';
require_once './../wp-config.php';
$time = time()- 30*86400;
$sql = "select ID from wp_posts where unix_timestamp(post_date) > $time and post_status = 'publish'";
$posts = $dbh->query($sql)->fetchAll();
//循环增加阅读量
foreach($posts as $k => $v){
    $postId = $v['ID'];
    //获取文章现有的阅读量
    $views = $dbh->query("select * from wp_postmeta where post_id = $postId and meta_key = 'post_views_count'")->fetch();
    $rand = rand(2,10);//增加阅读量 2-10之间的随机数
    if(isset($views['meta_value']) && $views['meta_value']){
        $metaId = $views['meta_id'];
        $add = $views['meta_value'] + $rand;
        //更新
        $sql = "update wp_postmeta set meta_value = '$add' where meta_id = $metaId";
    }else{
        $add = $rand;
        //插入
        $sql = "insert into wp_postmeta(`post_id`,`meta_key`,`meta_value`) value($postId,'post_views_count','$add')";
    }
    $dbh->query($sql);
    delete_post_meta($postId, 'post_views_count');
    add_post_meta($postId, 'post_views_count', $add);
}