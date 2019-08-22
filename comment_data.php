<?php
require_once './db.php';
session_start();
if(isset($_GET['action']) && $_GET['action']!="" && $_GET['action']=='delete')
{
    $id=$_GET['id'];
    /*this is delet quer*/
    $dbh->query("delete from wp_comment_data where id='$id'")or die("query is incorrect...");
}
$page=isset($_GET['page'])?$_GET['page']:1;
$pageSize = 10;
if($page=="" || $page=="1")
{
    $page1=0;
}
else
{
    $page1=($page*$pageSize)-$pageSize;
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

<div class="col-sm-9" style="margin:100px 10px 10px 10%">
<div class="panel-heading" style="background-color:#c4e17f">
	<h1>评论库-Page-<?php echo $page?> </h1></div><br>

<div style="overflow-x:scroll;">
<table class="table table-bordered table-hover table-striped" style="font-size:18px">
	<tr>
			    <th>评论ID</th>
			    <th>评论内容</th>
	<th><a href="comment_add.php">新增</a></th>
    </tr>
<?php
$result=$dbh->query("select * from wp_comment_data order by id desc limit $page1,$pageSize")or die ("query 2 incorrect.......");

foreach($result as $row)
{
echo "<tr><td>".$row['id']."</td><td>".$row['comment']."</td>";

echo"<td>
<a href='comment_data.php?id=".$row['id']."&action=delete'>删除</a>
</td></tr>";
}
?>
</table>
    <nav align="center">


        <?php
        //counting paging

        $paging=$dbh->query("select * from wp_comment_data ");
        $count=$paging->rowCount();

        $a=$count/$pageSize;
        $a=ceil($a);
        echo "<bt>";echo "<bt>";
        for($b=1; $b<=$a;$b++)
        {
            ?>
            <ul class="pagination" style="border:groove #666">
                <li><a class="label-info" href="comment_data.php?page=<?php echo $b;?>"><?php echo $b." ";?></a></li></ul>
            <?php
        }
        ?>
    </nav>
</div>
</div></div>
<?php include("./js.php"); ?>
</body>
</html>