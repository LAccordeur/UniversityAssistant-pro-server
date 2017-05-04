<?php

session_start();
if(isset($_SESSION['lasttime']) && time()-$_SESSION['lasttime'] < 2)
    exit;
else
    $_SESSION['lasttime'] = time();

//连接数据库
require_once('connect.php');

$sql ="SELECT Location FROM universitysheet GROUP BY Location";
//进行SQL查询
if($result=mysqli_query($conn,$sql)){
    //对结果集进行处理并输出
    $fullresult['result'] = array();
    $fullresult['total'] = strval(mysqli_num_rows($result));
    while ( $res = mysqli_fetch_assoc($result)){
        array_push($fullresult['result'],$res);
    }
    echo json_encode($fullresult);
}
else{
    echo "error: ".mysqli_error($conn);
}

//关闭数据库，释放资源
mysqli_free_result($result);
mysqli_close($conn);
?>