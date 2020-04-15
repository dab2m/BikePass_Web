<?php
include 'db.php';

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
        $rad = $row["radius"];

        $sql = "SELECT * FROM bikes WHERE status=1";
        $result_b = mysqli_query($db,$sql);
        if(mysqli_num_rows($result_b) > 0){
            while ($row2 = mysqli_fetch_assoc($result_b)) {
                $b_lat = $row2["lat"];
                $b_lng = $row2["lng"];
                if(verifyArea($h_lat,$h_lng,$b_lat,$b_lng,$rad)){
                    $bikePresent = true;
                    break;
                }
            }
        }

        if(!$bikePresent){
            $sql = "INSERT INTO requests(user_id,request_time,lat,lng) VALUES(0,'$now_s',$h_lat,$h_lng)";
            $result = mysqli_query($db,$sql);
        }
    }
}

// Haversine Formulü implementi 
// Kaynak https://www.it-swarm.dev/tr/php/php-ile-haversine-formulu/1071435883/
function verifyArea($latitude1, $longitude1, $latitude2, $longitude2, $radius) {
    $earth_radius = 6371;
    $radius = $radius / 1000;
    $dLat = deg2rad($latitude2 - $latitude1);
    $dLon = deg2rad($longitude2 - $longitude1);

    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * asin(sqrt($a));
    $d = $earth_radius * $c;

    if( $d < $radius)
        return true;
    else
        return false;
}
?>