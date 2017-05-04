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
$coin_default=1;
//获取参数
$where_case = array();
//获取模式
$mode= isset($_GET["mode"]) && !empty($_GET["mode"]) ? intval($_GET["mode"]) : $mode=$mode_default;
$coin = isset($_GET["coin"]) && !empty($_GET["coin"]) ? intval($_GET["coin"]) : $coin=$coin_default;
switch ($coin){
    case 1:
        $table_mini="m";
        $table_mid="Master";
        $table=" mastermajorview as m ";
        $col=" m.Major ";
        break;
    case 2:
        $table_mini="p";
        $table_mid="Phd";
        $table=" phdmajorview as p ";
        $col=" p.Major ";
        break;
    default:
        $table_mini="m";
        $table_mid="Master";
        $table=" mastermajorview as m ";
        $col=" m.Major ";
        break;
}


//获取省份
if(isset($_GET["l"]) && !empty($_GET["l"]) ){
    $Location=" (SELECT u.Location FROM universitysheet AS u WHERE ".$table_mini.".UniversityCode=u.UniversityCode)= ";
    $Location.="'".strval($_GET["l"])."'";
    array_push($where_case,$Location);
}
//获取学校
if(isset($_GET["u"]) && !empty($_GET["u"]) ){
    $University= $table_mini.".University='".strval($_GET["u"])."' ";
    array_push($where_case,$University);
}
if(isset($_GET["uc"]) && !empty($_GET["uc"])){
    $UniversityCode=$table_mini.".UniversityCode= ".strval(intval($_GET["uc"]));
    array_push($where_case,$UniversityCode);
}
if(isset($_GET["s"]) && !empty($_GET["s"])){
    $School=$table_mini.".School='".strval($_GET["s"])."' ";
    array_push($where_case,$School);
}
if(isset($_GET["sc"]) && !empty($_GET["sc"])){
    $SchoolCode=$table_mini.".SchoolCode= ".strval(intval($_GET["sc"]));
    array_push($where_case,$SchoolCode);
}
if(isset($_GET[$table_mini]) && !empty($_GET[$table_mini]) ){
    if($mode==2){
        $Major= $table_mini.".Major LIKE '%".strval($_GET["$table_mini"])."%' ";
    }
    else{
        $Major= $table_mini.".Major='".strval($_GET["$table_mini"])."' ";
    }

    array_push($where_case,$Major);
}
if(isset($_GET[$table_mini."c"]) && !empty($_GET[$table_mini."c"])){
    $MajorCode=$table_mini.".MajorCode= ".strval(intval($_GET[$table_mini."c"]));
    array_push($where_case,$MajorCode);
}

$start=isset($_GET["start"]) && !empty($_GET["start"]) ? $_GET["start"]: $start_default;
$count=isset($_GET["count"]) && !empty($_GET["count"]) ? $_GET["count"]: $count_default;


//组合SQL语句
switch ($mode){
    case 1:
        $col_name=$col.",".$table_mini.".MajorCode AS Id ";break;
    case 2:
        $col_name=$col.",".$table_mini.".MajorCode AS Id, ".$table_mini.".University, ".$table_mini.".School,".$table_mini.".SchoolCode, (SELECT s.Icon FROM schoolsheet AS s WHERE ".$table_mini.".SchoolCode=s.SchoolCode ) AS Icon,(SELECT s.Image FROM schoolsheet AS s WHERE ".$table_mini.".SchoolCode=s.SchoolCode ) AS Image " ;break;
    case 3:
        $col_name=$table_mini.".Name,".$table_mini.".Id,".$table_mini.".Field, (SELECT t.Title FROM teacherview AS t WHERE t.Id=".$table_mini.".Id ) AS Title, (SELECT t.Degree FROM teacherview AS t WHERE t.Id=".$table_mini.".Id ) AS Degree, (SELECT t.Icon FROM teacherview AS t WHERE t.Id=".$table_mini.".Id ) AS Icon ";break;
    default:
        $col_name=$col.",".$table_mini.".MajorCode AS Id ";break;
}
$sql ="SELECT ". $col_name." FROM ".$table." WHERE ";
$num = count($where_case);
for ($i=0;$i<$num-1;$i++){
    $sql .= $where_case[$i] . " AND ";
}
$sql .= $where_case[$i];

if($mode!=3){
    $sql .= " AND " . $col . "!='' ";
    $sql .= " GROUP BY " . $col;
}
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

if ($mode == 2 || $mode == 3) {
    $sql .= " LIMIT " . $start . " , " . $count;
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