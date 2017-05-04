<?php

session_start();
if(isset($_SESSION['lasttime']) && time()-$_SESSION['lasttime'] < 2)
    exit;
else
    $_SESSION['lasttime'] = time();

//连接数据库
require_once('connect.php');

//默认参数
$start_default=0;
$count_default=10;


$col="j.Field ";
if(isset($_GET["f"]) && !empty($_GET["f"]) ){
    $Field="j.Field LIKE '%".strval($_GET["f"])."%' ";
}
$start=isset($_GET["start"]) && !empty($_GET["start"]) ? $_GET["start"]: $start_default;
$count=isset($_GET["count"]) && !empty($_GET["count"]) ? $_GET["count"]: $count_default;

$table=" mastermajorview";
$sql ="SELECT  j.Name, j.Id, j.Major, j.Field, j.University, j.School, (SELECT t.Icon FROM teacherview AS t WHERE t.Id=j.Id) AS Icon FROM ".$table." AS j  WHERE ";
$sql .= $Field;
$sql .= " AND " . $col . "!='' ";

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

$sql .= " LIMIT " . $start . " , " . $count;
//进行SQL查询
if($result=mysqli_query($conn,$sql)){
    //对结果集进行处理并输出
    $fullresult['result'] = array();
    $fullresult['total'] = strval($total);
    $fullresult['start'] = strval($start);
    $fullresult['count'] = $count<($fullresult['total']-$start)? strval($count):strval($fullresult['total']-$start);
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