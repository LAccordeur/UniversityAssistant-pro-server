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
    $Location="  (SELECT u.Location FROM universitysheet AS u WHERE s.UniversityCode= u.UniversityCode)= ";
    $Location.= "'".strval($_GET["l"])."'";
    array_push($where_case,$Location);
}

//获取学校
$University= " (SELECT u.University FROM universitysheet AS u WHERE s.UniversityCode= u.UniversityCode)= ";
if(isset($_GET["u"]) && !empty($_GET["u"]) ){
    $University.= "'".strval($_GET["u"])."'";
    array_push($where_case,$University);
}
if(isset($_GET["uc"]) && !empty($_GET["uc"])){
    $UniversityCode=" s.UniversityCode= ".strval(intval($_GET["uc"]));
    array_push($where_case,$UniversityCode);
}
if(isset($_GET["s"]) && !empty($_GET["s"])){
    if($mode==2){
        $School=" S.School LIKE'%".strval($_GET["s"])."%' ";
    }
    else{
        $School=" s.School='".strval($_GET["s"])."'";
    }
    array_push($where_case,$School);
}
else{
    $School=" s.School!='' ";
    array_push($where_case,$School);
}
if(isset($_GET["sc"]) && !empty($_GET["sc"])){
    $SchoolCode=" s.SchoolCode= ".strval(intval($_GET["sc"]));
    array_push($where_case,$SchoolCode);
}
$start=isset($_GET["start"]) && !empty($_GET["start"]) ? $_GET["start"]: $start_default;
$count=isset($_GET["count"]) && !empty($_GET["count"]) ? $_GET["count"]: $count_default;


//组合SQL语句
switch ($mode){
    case 1:
        $col_name=" s.School, s.SchoolCode AS Id ";break;
    case 2:
        $col_name=" s.School, s.SchoolCode AS Id, s.Website, s.EstablishedTime, s.Icon, (SELECT u.University FROM universitysheet AS u WHERE s.UniversityCode= u.UniversityCode) AS University ";break;
    case 3:
        $col_name=" *, (SELECT u.University FROM universitysheet AS u WHERE s.UniversityCode= u.UniversityCode) AS University ";break;
    default:
        $col_name=" s.School,s.SchoolCode AS Id ";break;
}
$sql ="SELECT". $col_name."FROM schoolsheet AS s WHERE";
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