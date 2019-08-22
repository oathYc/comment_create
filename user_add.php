<?php
session_start();
include("./db.php");

if(isset($_POST['btn_save']))
{
    $username = $_POST['username'];
    //picture coding
    $picture_name=$_FILES['image']['name'];
    $picture_type=$_FILES['image']['type'];
    $picture_tmp_name=$_FILES['image']['tmp_name'];
    $picture_size=$_FILES['image']['size'];

    if($picture_type=="image/jpeg" || $picture_type=="image/jpg" || $picture_type=="image/png" || $picture_type=="image/gif")
    {
        if($picture_size<=50000000)

        $pic_name=time()."_".$picture_name;
        move_uploaded_file($picture_tmp_name,"./images/".$pic_name);
    }
    if($username && $pic_name){
        $time = time();
        $dbh->query("insert into wp_comment_user(`username`,createTime,image) values ('$username','$time','$pic_name')")
        or die ("Query 1 is inncorrect........");
        header("location: comment_user.php");
    }else{
        die("<script>alert('请输入正确的用户信息！');setTimeout(function(){history.go(-1);},1000)</script>");
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
            <h1>添加用户  </h1></div><br>

        <form action="user_add.php" name="form" method="post" enctype="multipart/form-data">
            <div class="col-sm-6">
                <p>用户名</p>
                <input name="username" class="input-lg" type="text"  id="username" style="font-size:18px; width:330px" placeholder="用户名" autofocus required><br><br>
                <p>用户头像</p>
                <div >
                    <input type="file" style="width:330px" name="image" class="btn thumbnail" id="picture" >
                </div>
            </div>
            <div class="col-sm-7" style="margin:20px;margin-left:90px;">
                <button type="submit" class="btn btn-success  center" name="btn_save" id="btn_save" style="font-size:18px">提交</button></div>
        </form>
    </div></div>
<?php include("./js.php"); ?>
</body>
</html>