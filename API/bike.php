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

		        $update_user_sql = "UPDATE user SET bike_km = '$km', bike_using_time = '$time' WHERE user_id = $user_id"; /* UPDATE USER KM & TIME SQL FOR USER TABLE*/
		        $sql_status = mysqli_query($db, $update_user_sql);
				$update_user_sql_2 = "UPDATE data SET bike_km = '$km' WHERE user_id = $user_id";  //UPDATE user_km for data table
		        $sql_status_2 = mysqli_query($db, $update_user_sql_2);
				
				$user_bike_using_time = "SELECT bike_using_time,total_credit,bonusactivation FROM user WHERE user_id = '$user_id'";
				$result = mysqli_query($db, $user_bike_using_time);
				$user = mysqli_fetch_assoc($result);
				$bike_using_time = $user["bike_using_time"]; //user's bike_using_time
				$total_credit = $user["total_credit"]; //user's total_credit
				$bonusactivation = $user["bonusactivation"]; //user's bonusactivation
				
				if($bonusactivation < 5){ //kullanıcılar bonusu her kullanımda bir alabilir bunun için sayaç
					$bonusactivation = $bonusactivation + 1;
					$update_bonusactivation_sql = "UPDATE user SET bonusactivation = '$bonusactivation' WHERE user_id = $user_id";
					$sql_status_6 = mysqli_query($db, $update_bonusactivation_sql);
				}
				//find 1st on Top-10 list
				$max_bike_using_time = "SELECT MAX(bike_using_time) FROM user"; 
				$result_2 = mysqli_query($db, $max_bike_using_time);
				$max_time = mysqli_fetch_assoc($result_2);
				$max = $max_time["bike_using_time"];
				//find 2nd on Top-10 list
				$max2nd_bike_using_time = "SELECT MAX(bike_using_time) FROM user WHERE bike_using_time < (SELECT MAX(bike_using_time) FROM user)"; 
				$result_3 = mysqli_query($db, $max2nd_bike_using_time);
				$max2nd_time = mysqli_fetch_assoc($result_3);
				$max2nd = $max2nd_time["bike_using_time"];
				//find 3rd on Top-10 list
				$max3rd_bike_using_time = "SELECT MAX(bike_using_time) FROM user WHERE bike_using_time < ((SELECT MAX(bike_using_time) FROM user WHERE bike_using_time < (SELECT MAX(bike_using_time) FROM user))"; 
				$result_4 = mysqli_query($db, $max3rd_bike_using_time);
				$max3rd_time = mysqli_fetch_assoc($result_4);
				$max3rd = $max3rd_time["bike_using_time"];
				/*Bonus for Top 3 users on Top-10 List */
				if($max == $bike_using_time && $bonusactivation == 5){ //1st : Bonus 900 credit
					$total_credit = $total_credit+900;
					$bonusactivation = 0;
					$update_user_sql_3 = "UPDATE user SET total_credit = '$total_credit', bonusactivation = '$bonusactivation' WHERE user_id = '$user_id'";
					$sql_status_3 = mysqli_query($db, $update_user_sql_3);
				}
				if($max2nd == $bike_using_time && $bonusactivation == 5){ //2nd : Bonus 600 credit
					$total_credit = $total_credit+600;
					$bonusactivation = 0;
					$update_user_sql_4 = "UPDATE user SET total_credit = '$total_credit', bonusactivation = '$bonusactivation' WHERE user_id = '$user_id'";
					$sql_status_4 = mysqli_query($db, $update_user_sql_4);
				}
				if($max3rd == $bike_using_time && $bonusactivation == 5){ //3rd : Bonus 300 credit
				$total_credit = $total_credit+300;
				$bonusactivation = 0;
				$update_user_sql_5 = "UPDATE user SET total_credit = '$total_credit', bonusactivation = '$bonusactivation' WHERE user_id = '$user_id'";
				$sql_status_5 = mysqli_query($db, $update_user_sql_5);
				}
				
		    }
		}

	}else{
		$status = 1;

	}
	$json = array("status" => $status);
    echo json_encode($json, JSON_FORCE_OBJECT);
}
?>