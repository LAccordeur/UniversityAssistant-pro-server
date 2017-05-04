<?php
session_start();
if(isset($_SESSION['lasttime']) && time()-$_SESSION['lasttime'] < 2)
    exit;
else
    $_SESSION['lasttime'] = time();

//连接数据库
require_once('connect.php');

//默认参数
$mode_default=1;
$start_default=0;
$count_default=10;

//获取参数
$where_case = array();
//获取模式
$mode= isset($_GET["mode"]) && !empty($_GET["mode"]) ? intval($_GET["mode"]) : $mode=$mode_default;
//获取省份
if(isset($_GET["l"]) && !empty($_GET["l"])){
    $Location=" Location='".strval($_GET["l"])."'";
    array_push($where_case,$Location);
}

//获取学校名称
if(isset($_GET["u"]) && !empty($_GET["u"])){
    if($mode==2){
        $University=" University LIKE'%".strval($_GET["u"])."%' ";
    }
    else{
        $University=" University='".strval($_GET["u"])."'";
    }
    array_push($where_case,$University);
}
//获取学校代码
if(isset($_GET["uc"]) && !empty($_GET["uc"])){
    $UniversityCode=" UniversityCode= "."'".intval($_GET["uc"])."'";
    array_push($where_case,$UniversityCode);
}
//获取LIMIT条件
$start=isset($_GET["start"]) && !empty($_GET["start"]) ? $_GET["start"]: $start_default;
$count=isset($_GET["count"]) && !empty($_GET["count"]) ? $_GET["count"]: $count_default;

//组合SQL语句
switch ($mode){
    case 1:
        $col_name=" University, UniversityCode AS Id ";break;
    case 2:
        $col_name=" University, UniversityCode AS Id, Location, Property, UType, Icon ";break;
    case 3:
        $col_name=" * ";break;
    default:
        $col_name=" University, UniversityCode AS Id ";break;
}
$sql ="SELECT". $col_name."FROM universitysheet WHERE";
$num = count($where_case);
for ($i=0;$i<$num-1;$i++){
    $sql .= $where_case[$i] . " AND ";
}
$sql .= $where_case[$i];

//预查询获取总条数
if($result=mysqli_query($conn,$sql)){
    $total= mysqli_num_rows($result);
    if($start>$total){
        $start=$start_default;
    }
}
else{
    echo "error: ".mysqli_error($conn);
}

//继续拼接LIMIT语句
if($mode==2){
    $sql.= " LIMIT ".$start." , ".$count;
}

//进行SQL查询
if($result=mysqli_query($conn,$sql)){
    //对结果集进行处理并输出
    $fullresult['result'] = array();
    $fullresult['total'] = strval($total);
    if($mode==2){
        $fullresult['start'] = strval($start);
        $fullresult['count'] = $count<($fullresult['total']-$start)? strval($count):strval($fullresult['total']-$start);
    }
    while ( $res = mysqli_fetch_assoc($result)){
        foreach ($res as $key => $value) {
            $res[$key] = stripslashes($value);
        }
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