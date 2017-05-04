<?php

session_start();
if(isset($_SESSION['lasttime']) && time()-$_SESSION['lasttime'] < 2)
    exit;
else
    $_SESSION['lasttime'] = time();

//连接数据库
require_once('connect.php');

$result = array('result'=>'');
$Problem="";
$Contact="";
$Detail="";
$time = time();

if(isset($_POST["con"]) && !empty($_POST["con"]) ){
    $Contact=strval($_POST["con"]);
}

if(isset($_POST["pro"]) && !empty($_POST["pro"]) ){
    $code=intval($_POST["pro"]);
    switch ($code){
        case 1:
            $Problem = "信息缺失";break;
        case 2:
            $Problem = "信息有误";break;
        case 3:
            $Problem = "其他";break;
        default:
            $Problem = "其他";break;
    }
}

if(isset($_POST["det"]) && !empty($_POST["det"]) ){
    $Detail=strval($_POST["det"]);
}
//if(isset($Problem) && isset($Detail)){
    $sql = "INSERT INTO feedbacksheet(Contact, Problem, Detail) VALUES ( '".$Contact."' , '".$Problem."','".$Detail."' )";
    if(mysqli_query($conn,$sql)){
        $result['result']='Success';
        echo json_encode($result);
    }
    else{
        $result['result']='Fail';
        echo json_encode($result);
    }
//}

//关闭数据库
mysqli_close($conn);
?>