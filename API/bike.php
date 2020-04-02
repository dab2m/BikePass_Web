<?php
include('../db.php');
$post_json = json_decode(file_get_contents("php://input"), true);
if (isset($post_json["lat"]) && isset($post_json["long"]) && isset($post_json["bike_id"])) {

    $lat = $post_json["lat"];
    $long = $post_json["long"];
	$bike_id = $post_json["bike_id"];
	
	$old_lat = -1;
	$old_long = -1;
	$old_time = -1;
	$user_id = -1;
	
	/* For gathering related bike infos */
	$bike_info_sql = "SELECT * FROM bikes WHERE id = $bike_id";
	$bike_info_res = mysqli_query($db, $bike_info_sql);
	if($bike_info_res)
	{
	    $bike_info_row = mysqli_fetch_assoc($bike_info_res);
	    $user_id = $bike_info_row['user_id'];
	    $old_lat = $bike_info_row['lat'];
	    $old_long = $bike_info_row['lng'];
	}
	
    $sql = "UPDATE bikes SET lat= '$lat',lng = '$long' WHERE id=$bike_id";
	$result = mysqli_query($db, $sql);
	if($result){
		$status = 0;

		/* For gathering related user infos to update bike_using_time and bike_km */
		if($user_id != -1)
		{
		    $user_sql = "SELECT * FROM user WHERE user_id = '$user_id'";
		    $user_res = mysqli_query($db, $user_sql);
		    if($user_res)
		    {
		        $user_row = mysqli_fetch_assoc($user_res);
		        $km = $user_row['bike_km'];
		        if($old_lat != -1 && $old_long != -1)
		            $km += sqrt( pow( abs($old_lat - floatval($lat)), 2) + pow( abs($old_long - floatval($long)), 2) );
		        $time = $user_row['bike_using_time'] + 1; /* Increment time 1 sec. */ 

		        $update_user_sql = "UPDATE user SET bike_km = '$km', bike_using_time = '$time' WHERE user_id = $user_id"; /* UPDATE USER KM & TIME SQL */
		        $sql_status = mysqli_query($db, $update_user_sql);
				
				$update_user_sql_2 = "UPDATE data SET bike_km = '$km' WHERE user_id = $user_id"; 
		        $sql_status_2 = mysqli_query($db, $update_user_sql_2);
		    }
		}

	}else{
		$status = 1;

	}
	$json = array("status" => $status);
    echo json_encode($json, JSON_FORCE_OBJECT);
}
?>