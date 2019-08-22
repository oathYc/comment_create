<?php
require_once './db.php';
?>
<?php //获取栏目信息
//$category = $dbh->query('select * from wp_terms');
//$html = '';
//foreach($category as $row){
//    $html .= "<div style='margin: 5px;font-size: 16px;height:31px;'>".$row['name']."&nbsp;&nbsp;&nbsp;<input type='text' class='inputCate' name='target[]' style='position: absolute;height: 30px;right: 34%;width: 350px;' data-id='".$row['term_id']."'  /></div>";
//}
//$html .= "<div class='col-sm-7' style='margin:20px;'><button onclick='commentCate()' class='btn btn-success  center' name='btn_save' id='btn_save' style='font-size:18px'>提交</button></div>";
?>
<?php //获取文章
//$category = $dbh->query('select ID,post_title from wp_posts');
//$post = '';
//foreach($category as $row){
//    $post .= "<div style='margin: 5px;font-size: 16px;height:31px;'>".$row['post_title']."&nbsp;&nbsp;&nbsp;<input type='text' class='inputPost' name='target[]' data-id='".$row['ID']."' style='position: absolute;height: 30px;right: 34%;width: 350px;'  /></div>";
//}
//$post .= "<div class='col-sm-7' style='margin:20px;'><button onclick='commentPost()'  class='btn btn-success  center' name='btn_save' id='btn_save' style='font-size:18px'>提交</button></div>";
?>

<?php //文章添加评论
    if(isset($_POST['postSet']) && $_POST['postSet'] ==1){
        $content = $_POST['content'];
        $array = [];
        $comment = $dbh->query("select * from wp_comment_data ");
        if(!$comment){
            die(json_encode(['code'=>0,'msg'=>'评论库里面没有内容，请先添加内容']));
        }
        $max = 0;
        foreach($content as $k => $v){//数据初始化  分割数据
            $explode = explode('=',$v);
            if(count($explode) != 2){
                die(json_encode(['code'=>0,'msg'=>'第'.($k+1).'条数据格式有问题']));
            }else{
                $con = explode(';',$explode[1]);
                if(count($con) != 3){
                    die(json_encode(['code'=>0,'msg'=>'第'.($k+1).'条数据格式有问题']));
                }else{
                    $postId = $explode[0];
                    if($max < $con[0]){
                        $max = $con[0];
                    }
                    $array[] = ['postId'=>$postId,'total'=>$con[0],'beginTime'=>$con[1],'endTime'=>$con[2]];
                }
            }
        }
        $userTotal = $dbh->query("select * from wp_comment_user")->rowCount();
        if($userTotal < $max){
            die(json_encode(['code'=>0,'msg'=>'用户集数量不够']));
        }
        foreach($array as $p => $y){
            $postId = $y['postId'];
            $total = $y['total'];
            $beginTime = strtotime($y['beginTime']);
            $endTime = strtotime($y['endTime']);
            $userIds = [];
            for($i=0;$i<$total;$i++){
                //自动生成评论
                //随机获取用户名信息
                $uidStr = implode(',',$userIds);
                if($uidStr){
                    $user = $dbh->query("select * from wp_comment_user where id not in ($uidStr) order by rand() limit 1");
                }else{
                    $user = $dbh->query('select * from wp_comment_user  order by rand() limit 1');
                }
                $user = $user->fetch();
                $username = $user['username'];
                $userImage = $user['image'];
                $userId = $user['id'];
                //随机获取一条评论内容
                $comment = $dbh->query("select * from wp_comment_data order by rand() limit 1");
                $comment = $comment->fetch();
                $comment = $comment['comment'];
                $time = rand($beginTime,$endTime);
                $date = date('Y-m-d H:i:s',$time);
                $date_gmt = date('Y-m-d H:i:s',($time-3600*8));
                $sql = "insert into wp_comments(comment_post_ID,comment_author,comment_date,comment_date_gmt,comment_content,user_id) value('$postId','$username','$date','$date_gmt','$comment','$userId')";
                $dbh->query($sql);
                if(!in_array($userId,$userIds)){
                    $userIds[]= $userId;
                }
            }
        }
        die(json_encode(['code'=>1]));
    }
?>
<?php //栏目标签文章添加
    if(isset($_POST['cateSet']) && $_POST['cateSet'] ==1){
        $content = $_POST['content'];
        $array = [];
        $comment = $dbh->query("select * from wp_comment_data ");
        if(!$comment){
            die(json_encode(['code'=>0,'msg'=>'评论库里面没有内容，请先添加内容']));
        }
        $isTax = 0;
        $isPost = 0;
        foreach($content as $k => $v){//数据初始化  分割数据
            $explode = explode('=',$v);
            if(count($explode) != 2){
                die(json_encode(['code'=>0,'msg'=>'第'.($k+1).'条数据格式有问题']));
            }else{
                $con = explode(';',$explode[1]);
                if(count($con) != 3){
                    die(json_encode(['code'=>0,'msg'=>'第'.($k+1).'条数据格式有问题']));
                }else{
                    $termId = $explode[0];
                    //获取关联id
                    $taxId = $dbh->query("select * from wp_term_taxonomy where term_id = $termId limit 1")->fetch()['term_taxonomy_id'];
                    if(!$taxId){
                        $isTax = 1;
                        break;
                    }
                    //获取栏目下的文章id
                    $postIds = $dbh->query("select object_id from wp_term_relationships where term_taxonomy_id = $taxId");
                    if(!$postIds){
                        $isPost = 1;
                        break;
                    }
                    $array[] = ['termId'=>$termId,'total'=>$con[0],'beginTime'=>$con[1],'endTime'=>$con[2]];
                }
            }
        }
        if($isTax ==1){
            die(json_encode(['code'=>0,'msg'=>'该栏目没有文章（无关联id）']));
        }
        if($isPost ==1){
            die(json_encode(['code'=>0,'msg'=>'该栏目下没有文章']));
        }
        foreach($array as $t => $y){//评论生成
            $termId = $y['termId'];
            $total = $y['total'];
            $beginTime = strtotime($y['beginTime']);
            $endTime = strtotime($y['endTime']);
            //获取关联id
            $taxId = $dbh->query("select * from wp_term_taxonomy where term_id = $termId limit 1")->fetch()['term_taxonomy_id'];
            //获取栏目下的文章id
            $postIds = $dbh->query("select object_id from wp_term_relationships where term_taxonomy_id = $taxId");
            $count = $postIds->rowCount();//文章数
            $postIds = $postIds->fetchAll();
            $ids = [];
            foreach($postIds as $val){
                $ids[] = $val['object_id'];
            }
            for($i=0;$i<$total;$i++){
                $key = rand(0,$count-1);//随机生成键文章id
                $postId = $ids[$key];//获取对应键的文章id
                //自动生成评论
                //随机获取用户名信息
                $user = $dbh->query('select * from wp_comment_user order by rand() limit 1');
                $user = $user->fetch();
                $username = $user['username'];
                $userImage = $user['image'];
                $userId = $user['id'];
                //随机获取一条评论内容
                $comment = $dbh->query("select * from wp_comment_data order by rand() limit 1");
                $comment = $comment->fetch();
                $comment = $comment['comment'];
                $time = rand($beginTime,$endTime);
                $date = date('Y-m-d H:i:s',$time);
                $date_gmt = date('Y-m-d H:i:s',($time-3600*8));
                $sql = "insert into wp_comments(comment_post_ID,comment_author,comment_date,comment_date_gmt,comment_content,user_id) value('$postId','$username','$date','$date_gmt','$comment','$userId')";
                $dbh->query($sql);
            }
        }
        die(json_encode(['code'=>1]));
    }
?>
<?php //时间段评论添加
    if(isset($_POST['timeSet']) && $_POST['timeSet']){
        $timeSet = $_POST['timeSet'];
        $arr = explode(';',$timeSet);
        if(count($arr) !=5){
            $data = ['code'=>0,'msg'=>'内容格式错误'];
        }else{
            $total = $arr[0];
            $beginTime = strtotime($arr[1]);//文章发布开始时间
            $endTime = strtotime($arr[2]);//文章发布结束时间
            $commentBegin = strtotime($arr[3]);//评论开始时间
            $commentEnd = strtotime($arr[4]);//评论结束时间
            //获取时间段内的文章id
            $sql = "select ID from wp_posts where unix_timestamp(post_date) between $beginTime and $endTime";
            $result = $dbh->query($sql);
            $count = $result->rowCount();
            $result = $result->fetchAll();
            $ids = [];
            if(!$count){
                die(json_encode(['code'=>0,'msg'=>'该时间段内没有文章']));
            }
            $comment = $dbh->query("select * from wp_comment_data ");
            if(!$comment){
                die(json_encode(['code'=>0,'msg'=>'评论库里面没有内容，请先添加内容']));
            }
            foreach($result as $k => $v){
                $ids[]= $v['ID'];
            }
            for($i=0;$i<$total;$i++){
                $key = rand(0,$count-1);//随机生成键文章id
                $postId = $ids[$key];//获取对应键的文章id
                //自动生成评论
                //随机获取用户名信息
                $user = $dbh->query('select * from wp_comment_user order by rand() limit 1');
                $user = $user->fetch();
                $username = $user['username'];
                $userImage = $user['image'];
                $userId = $user['id'];
                //随机获取一条评论内容
                $comment = $dbh->query("select * from wp_comment_data order by rand() limit 1");
                $comment = $comment->fetch();
                $comment = $comment['comment'];
                $time = rand($commentBegin,$commentEnd);
                $date = date('Y-m-d H:i:s',$time);
                $date_gmt = date('Y-m-d H:i:s',($time-3600*8));
                $sql = "insert into wp_comments(comment_post_ID,comment_author,comment_date,comment_date_gmt,comment_content,user_id) value('$postId','$username','$date','$date_gmt','$comment','$userId')";
                $dbh->query($sql);
            }
            $data = ['code'=>1];
        }
        die(json_encode($data));
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
            <h1>评论生成 </h1></div><br>

        <div>
            <table class="table table-bordered table-hover table-striped" style="font-size:18px">
                <tr>
                    <th>文章</th>
                    <th>栏目标签</th>
                    <th>时间段</th>
                </tr>
                <tr>
                    <td>
                        &nbsp;&nbsp;&nbsp;<button class="btn btn-success  center" onclick="getContent(1)">开启</button>
                        &nbsp;&nbsp;&nbsp;<button class="btn btn-success  center" onclick="getContent(4)">关闭</button>
                        &nbsp;&nbsp;&nbsp;<button class="btn btn-success  center" onclick="addInput(1)">添加</button>
                    </td>
                    <td>
                        &nbsp;&nbsp;&nbsp;<button class="btn btn-success  center" onclick="getContent(2)">开启</button>
                        &nbsp;&nbsp;&nbsp;<button class="btn btn-success  center" onclick="getContent(4)">关闭</button>
                        &nbsp;&nbsp;&nbsp;<button class="btn btn-success  center" onclick="addInput(2)">添加</button>
                    </td>
                    <td>
                        &nbsp;&nbsp;&nbsp;<button class="btn btn-success  center" onclick="getContent(3)">开启</button>
                        &nbsp;&nbsp;&nbsp;<button class="btn btn-success  center" onclick="getContent(4)">关闭</button>
                    </td>
                </tr>
            </table>
            <div id="contentData" style="display: none;width: 100%;">

            </div>
            <div class='col-sm-7' id="dataButton" style='margin:20px;'></div>
        </div>
    </div></div>
<?php include("./js.php"); ?>

<script>
    function getContent(type){
        //type 1-文章添加 2-标签栏目添加 3-时间段添加 4-关闭
        if(type ==4){
            $('#dataButton').html('');
            $('#contentData').css('display','none')
        }
        if(type ==1){
            // var postContent = $('#postContent').html();
            var postContent = "<div style='margin: 5px;font-size: 16px;height:31px;'><input style='height: 30px;width: 350px;' type='text' placeholder='请填写文章ID' class='postId' oninput = \"value=value.replace(/[^\\d]/g,'')\"/> &nbsp;&nbsp;&nbsp;<input type='text' class='inputPost' name='target[]'  style='position: absolute;height: 30px;right: 34%;width: 350px;'  /></div>";
            $('#contentData').html(postContent);
            var but = "<button onclick='commentPost()' class='btn btn-success  center' name='btn_save' id='btn_save' style='font-size:18px'>提交</button>";
            $('#dataButton').html(but);
            $('#contentData').css('display','block')
        }
        if(type ==2){
            // var html = $('#category').html();
            var html  = "<div style='margin: 5px;font-size: 16px;height:31px;'><input style='height: 30px;width: 350px;' type='text' placeholder='请填写栏目标签ID' class='cateId' oninput = \"value=value.replace(/[^\\d]/g,'')\" /> &nbsp;&nbsp;&nbsp;<input type='text' class='inputCate' name='target[]'  style='position: absolute;height: 30px;right: 34%;width: 350px;'  /></div>";
            $('#contentData').html(html);
            var but = "<button onclick='commentCate()' class='btn btn-success  center' name='btn_save' id='btn_save' style='font-size:18px'>提交</button>";
            $('#dataButton').html(but);
            $('#contentData').css('display','block')
        }
        if(type ==3){
            var time = "<div style='margin: 5px;font-size: 16px;height:31px;'>设置评论数时间段&nbsp;&nbsp;&nbsp;<input type='text' id='timeSet' name='timeSet' style='position: absolute;height: 30px;right: 17%;width: 640px;' placeholder='6;2019-08-18 12:12:12;2019-08-25 12:12:58;2019-06-18 12:12:12;2019-06-25 12:12:58' /></div>" ;

            $('#contentData').html(time);
            var but = "<div class='col-sm-7' style='margin:20px;'><button onclick='commentTime()' class='btn btn-success  center' name='btn_save' id='btn_save' style='font-size:18px'>提交</button></div>";
            $('#dataButton').html(but);
            $('#contentData').css('display','block')
        }
    }
    function commentTime(){
        var timeSet = $('#timeSet').val();
        if(!timeSet){
            alert('请填写内容');
        }
        $.ajax({
            url:'comment_create.php',
            data:{
                timeSet:timeSet,
            },
            dataType:'json',
            type:'post',
            success:function(e){
                if(e.code==1){
                    alert('操作成功');
                    window.location.reload();
                }else{
                    alert(e.msg);
                }
            }
        });
    }
    function commentCate(){
        var arr = [];
        var i = 0;
        $("input.inputCate").each(function(inde,element){
            var value = $(element).val();
            if(value){
                // var termId = $(element).attr('data-id');
                var termId = $(element).siblings("input.cateId:first").val();
                if(termId){
                    arr[i] = termId+'='+value;
                    i++
                }
            }
        });
        $.ajax({
            url:'comment_create.php',
            data:{
                cateSet:1,
                content:arr,
            },
            dataType:'json',
            type:'post',
            success:function(e){
                if(e.code==1){
                    alert('操作成功');
                    window.location.reload();
                }else{
                    alert(e.msg);
                }
            }
        });
    }
    function commentPost(){
        var arr = [];
        var i = 0;
        $("input.inputPost").each(function(inde,element){
            var value = $(element).val();
            if(value){
                // var termId = $(element).attr('data-id');
                var termId = $(element).siblings("input.postId:first").val();
                if(termId){
                    arr[i] = termId+'='+value;
                    i++
                }
            }
        });
        $.ajax({
            url:'comment_create.php',
            data:{
                postSet:1,
                content:arr,
            },
            dataType:'json',
            type:'post',
            success:function(e){
                if(e.code==1){
                    alert('操作成功');
                    window.location.reload();
                }else{
                    alert(e.msg);
                }
            }
        });
    }
    function addInput(type){
        //type  1-添加文章 2-添加栏目
        if(type ==1){
            var content  = "<div style='margin: 5px;font-size: 16px;height:31px;'><input style='height: 30px;width: 350px;' type='text' placeholder='请填写文章ID' class='postId' oninput = \"value=value.replace(/[^\\d]/g,'')\" /> &nbsp;&nbsp;&nbsp;<input type='text' class='inputPost' name='target[]'  style='position: absolute;height: 30px;right: 34%;width: 350px;'  /></div>";
            $('#contentData').append(content);
        }
        if(type ==2){
            var content  = "<div style='margin: 5px;font-size: 16px;height:31px;'><input style='height: 30px;width: 350px;' type='text' placeholder='请填写栏目标签ID' class='cateId' oninput = \"value=value.replace(/[^\\d]/g,'')\" /> &nbsp;&nbsp;&nbsp;<input type='text' class='inputCate' name='target[]'  style='position: absolute;height: 30px;right: 34%;width: 350px;'  /></div>";
            $('#contentData').append(content);
        }
    }
</script>
</body>
</html>