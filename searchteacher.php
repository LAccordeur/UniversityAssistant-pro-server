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
    $Location=" (SELECT u.Location FROM universitysheet AS u WHERE t.UniversityCode=u.UniversityCode)= ";
    $Location.="'".strval($_GET["l"])."'";
    array_push($where_case,$Location);
}

//获取学校
if(isset($_GET["u"]) && !empty($_GET["u"]) ){
    $University= " t.University='".strval($_GET["u"])."' ";
    array_push($where_case,$University);
}
if(isset($_GET["uc"]) && !empty($_GET["uc"])){
    $UniversityCode=" t.UniversityCode= ".strval(intval($_GET["uc"]));
    array_push($where_case,$UniversityCode);
}
if(isset($_GET["s"]) && !empty($_GET["s"])){
    $School=" t.School='".strval($_GET["s"])."' ";
    array_push($where_case,$School);
}
if(isset($_GET["sc"]) && !empty($_GET["sc"])){
    $SchoolCode=" t.SchoolCode= ".strval(intval($_GET["sc"]));
    array_push($where_case,$SchoolCode);
}
if(isset($_GET["n"]) && !empty($_GET["n"])){
    if($mode==2){
        $Name=" t.Name LIKE'%".strval($_GET["n"])."%' ";
    }
    else{
        $Name=" t.Name='".strval($_GET["n"])."'";
    }
    array_push($where_case,$Name);
}
if(isset($_GET["nc"]) && !empty($_GET["nc"])){
    $Id=" t.Id= ".strval(ceil(floatval($_GET["nc"])));
    array_push($where_case,$Id);
}
$start=isset($_GET["start"]) && !empty($_GET["start"]) ? $_GET["start"]: $start_default;
$count=isset($_GET["count"]) && !empty($_GET["count"]) ? $_GET["count"]: $count_default;


//组合SQL语句
switch ($mode){
    case 1:
        $col_name=" t.Name, t.Id ";break;
    case 2:
        $col_name=" t.Name, t.School, t.University, t.Title, t.Degree, t.Icon, t.Id ";break;
    case 3:
        $col_name=" *, (SELECT u.University FROM universitysheet AS u WHERE t.UniversityCode=u.UniversityCode) AS University, (SELECT s.School FROM schoolsheet AS s WHERE t.SchoolCode=s.SchoolCode) AS School ";break;
    default:
        $col_name=" t.Name, t.Id ";break;
}
$sql ="SELECT". $col_name."FROM teacherview AS t WHERE";
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
            if($key!="Email"){
                $res[$key] = stripslashes($value);
            }
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