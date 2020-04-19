<?php
include 'db.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("location:index.php");
}

if (isset($_POST['id']) && isset($_POST['sendId']) && isset($_POST['message']) && isset($_POST['head'])) {

    $sql = "INSERT INTO messages (from_user,to_user,head,body,unread) VALUES ('".$_POST['sendId']."','".$_POST['id']."','".$_POST['head']."','".$_POST['message']."',0)";
    if (mysqli_query($db, $sql)) {
        echo json_encode("ok");
    } else {
        echo json_encode("fail");
    }
}
?>