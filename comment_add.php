<?php
session_start();
include("./db.php");

if(isset($_POST['btn_save']))
{
    $comment = $_POST['comment'];
    if($comment){
        $time = time();
        $dbh->query("insert into wp_comment_data(comment,createTime) values ('$comment','$time')")
        or die ("Query 1 is inncorrect........");
        header("location: comment_data.php");
    }else{
        die("<script>alert('请输入评论内容！');setTimeout(function(){history.go(-1);},1000)</script>");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>后台管理</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/k.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">

    <div class="col-sm-9 " style="margin:100px 10px 10px 10%">
        <div class="panel-heading" style="background-color:#c4e17f;">
            <h1>添加评论  </h1></div><br>

        <form action="comment_add.php" name="form" method="post">
            <div class="col-sm-6">
                <p>评论内容</p>
                <textarea name="comment" style="width: 300px;height: 130px;"></textarea>
<!--                <input name="comment" class="input-lg" type="text"  id="comment" style="font-size:18px; width:330px" placeholder="名称" autofocus required><br><br>-->
            </div>
            <div class="col-sm-7" style="margin:20px;margin-left:90px;">
                <button type="submit" class="btn btn-success  center" name="btn_save" id="btn_save" style="font-size:18px">提交</button></div>
        </form>
    </div></div>
<?php include("./js.php"); ?>
</body>
</html>