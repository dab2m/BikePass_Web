<?php
include('../db.php');
date_default_timezone_set('Europe/Istanbul');
$now_s = date("H:i");
$now = strtotime($now_s);
$sql = "SELECT * FROM bikes";
$result = mysqli_query($db, $sql);
if(mysqli_num_rows($result) > 0){
    while ($row = mysqli_fetch_assoc($result)) {
        $bike_id = $row["id"];
        $time = strtotime($row["timestamp"]);
        $diff = $now - $time;
        if($diff > 600 && $row["status"] == 3){
            $sql = "UPDATE bikes SET status=1,timestamp='$now_s',reserve_user_id='0' WHERE id=$bike_id";
            $result = mysqli_query($db, $sql);
        }
    }
}
?>