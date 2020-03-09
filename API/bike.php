<?php
include('../db.php');
$post_json = json_decode(file_get_contents("php://input"), true);
if (isset($post_json["lat"]) && isset($post_json["long"]) && isset($post_json["bike_id"])) {

    $lat = $post_json["lat"];
    $long = $post_json["long"];
	$bike_id = $post_json["bike_id"];
    $sql = "UPDATE bikes SET lat=$lat WHERE id=$bike_id";
	$result = mysqli_query($db, $sql);
	$sql = "UPDATE bikes SET long=$long WHERE id=$bike_id";
	$result = mysqli_query($db, $sql);

	
    
}
?>