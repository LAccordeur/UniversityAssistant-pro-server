<?php
require_once('config.php');
//连库
if(!$conn = mysqli_connect(HOST,USERNAME,PASSWORD)){
    echo mysqli_error($conn);
}
//选库
if(!mysqli_select_db($conn,DATABASE)){
    echo mysqli_error($conn);
}
//字符集
if(!mysqli_set_charset($conn, 'utf8')){
    echo mysqli_error($conn);
}
?>