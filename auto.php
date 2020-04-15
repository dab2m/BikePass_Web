<?php
include 'db.php';
// For verify area function
include 'API/app.php';
date_default_timezone_set('Europe/Istanbul');
$now_s = date("H:i");
$now = strtotime($now_s);
$sql = "SELECT * FROM bikes WHERE status=3";
$result = mysqli_query($db, $sql);
if(mysqli_num_rows($result) > 0){
    while ($row = mysqli_fetch_assoc($result)) {
        $bike_id = $row["id"];
        $time = strtotime($row["timestamp"]);
        $diff = $now - $time;
        if($diff > 600){
            $sql = "UPDATE bikes SET status=1,timestamp='$now_s',reserve_user_id='0' WHERE id=$bike_id";
            $result = mysqli_query($db, $sql);
        }
    }
}

$sql = "SELECT * FROM requests WHERE user_id != 0";
$result = mysqli_query($db, $sql);
if(mysqli_num_rows($result) > 0){
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row["id"];
        $time = strtotime($row["request_time"]);
        $diff = $now - $time;
        if($diff > 1800){
            $sql = "DELETE FROM requests WHERE id=$id";
            $result = mysqli_query($db, $sql);
        }
    }
}


$sql = "SELECT * FROM hotpoints";
$result_h = mysqli_query($db,$sql);
if(mysqli_num_rows($result_h) > 0){
    while ($row = mysqli_fetch_assoc($result_h)) {
        $bikePresent = false;
        $h_id = $row["id"];
        $h_lat = $row["lat"];
        $h_lng = $row["lng"];

        $sql = "SELECT * FROM bikes WHERE status=1";
        $result_b = mysqli_query($db,$sql);
        if(mysqli_num_rows($result_b) > 0){
            while ($row2 = mysqli_fetch_assoc($result_b)) {
                $b_lat = $row2["lat"];
                $b_lng = $row2["lng"];
                if(verifyArea($h_lat,$h_lng,$b_lat,$b_lng)){
                    $bikePresent = true;
                    break;
                }
            }
        }

        if(!$bikePresent){
            $sql = "INSERT INTO requests(user_id,request_time,lat,lng) VALUES(0,$now_s,$h_lat,$h_lng)";
            $result = mysqli_query($db,$sql);
        }
    }
}
?>