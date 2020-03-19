<?php
include('../db.php');
$post_json = json_decode(file_get_contents("php://input"), true);
if (isset($post_json["lat"]) && isset($post_json["long"]) && isset($post_json["bike_id"])) {

    $lat = $post_json["lat"];
    $long = $post_json["long"];
	$bike_id = $post_json["bike_id"];
    $sql = "UPDATE bikes SET lat=$lat,lng=$long WHERE id=$bike_id";
	$result = mysqli_query($db, $sql);
	if($result){
		$status = 0;
	}else{
		$status = 1;
	}
	$json = array("status" => $status);
    echo json_encode($json, JSON_FORCE_OBJECT);
}
?>