<?php
include('../db.php');
$status = "";
$message = "";
$bikes;
$post_json = json_decode(file_get_contents("php://input"), true);

//Register User
if (isset($post_json["username"]) && isset($post_json["password"]) && isset($post_json["email"])) {

    $email = $post_json["email"];
    $username = $post_json["username"];
    $password = $post_json["password"];

    if (!preg_match('/^[a-z\d_]{2,20}$/', $username)) {
        $message = "Username includes non letter characters or not between 2-20 characters";
        $status = "1";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email must be a valid email address";
        $status = "2";
    } else if (!preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,12}$/', $password)) {
        $message = "The password does not meet the requirements!";
        $status = "3";
    } else {

        $sql = "SELECT email FROM user WHERE email='$email'";
        $email_result = mysqli_query($db, $sql);
        $sql = "SELECT username FROM user WHERE username='$username'";
        $username_result = mysqli_query($db, $sql);
        if (mysqli_num_rows($email_result) == 1) {
            $message = "Email already exists";
            $status = "4";
        } else if (mysqli_num_rows($username_result) == 1) {
            $message = "Username already exists";
            $status = "5";
        } else {
            $sql = "INSERT INTO user(username,password,email) VALUES ('$username','$password','$email')";
            $register = mysqli_query($db, $sql);
            $status = "0";
            $message = "Account is created! Welcome to BikePass " . $username;
        }
    }
}

//Login User
if (isset($post_json["username"]) && isset($post_json["password"]) && empty($post_json["email"])) {

    $username = $post_json["username"];
    $password = $post_json["password"];
    $sql = "SELECT username,password FROM user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) == 1) {
        $login = mysqli_fetch_assoc($result);
        if ($login["password"] == $password) {
            $message = "Login succesful. Welcome back " . $username;
            $status = "0";
        } else {
            $message = "Wrong username/password!";
            $status = "1";
        }
    } else {
        $message = "No user found for username " . $username;
        $status = "2";
    }
}

//Sending Location and Getting bikes
if (isset($post_json["lat"]) && isset($post_json["long"])) {

    $sql = "SELECT * FROM bikes WHERE status='0'";
    $result = mysqli_query($db, $sql);
    $bikes_array = array();
    $i = 0;
    if (mysqli_num_rows($result) > 1) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Uzaklık hesabı yapılacak yer
            $bikes_array[$i]['name'] = 'bike' . $row['id'];
            $bikes_array[$i]['lat'] = $row['lat'];
            $bikes_array[$i]['long'] = $row['long'];
            $i++;
        }
        $status = "0";
        $bikes = $bikes_array;
    } else {
        $status = "1";
        $message = "No available bikes near user";
    }
}

//Data send
if(isset($post_json["username"]) && isset($post_json["bike_id"]) && isset($post_json["bike_time"]) && isset($post_json["bike_km"])){
    $username = $post_json["username"];
    $sql = "SELECT user_id from user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) == 1) {
        $user_id = mysqli_fetch_assoc($result);
        $user_id = $user_id['user_id'];
        $bike_id = $post_json["bike_id"];
        $sql = "SELECT status from bikes WHERE id=$bike_id";
        $result = mysqli_query($db, $sql);
        if(mysqli_num_rows($result) == 1) {
            //Meşgul bike status kodu 1 olarak varsayılan yer
            $status = mysqli_fetch_assoc($result);
            if($status["status"] == 1){
                $date = date('Y-m-d');
                $bike_km = $post_json['bike_km'];
                $bike_time = $post_json['bike_time'];
                $sql = "INSERT INTO data (user_id,bike_id,bike_km,bike_using_time,date) VALUES ($user_id,$bike_id,$bike_km,$bike_time,'$date')";
                $result = mysqli_query($db, $sql);
                if ($result){
                    $sql = "UPDATE bikes SET status=0 WHERE id=$bike_id";
                    $result = mysqli_query($db, $sql);
                    if ($result){
                        $status = "0";
                        $message = "Data stored and bike" . $bike_id . " is available again";
                    }else{
                        $status = "5";
                        $message = "Can't update bikes status!";
                    }
                }else{
                    $status = "4";
                    $message = $result;
                    //$message = "Can't add bike using data!";
                }
            }else{
                $status = "3";
                $message = "Non-busy bike detected!";
            }
        }else{
            $status = "2";
            $message = "Invalid bike id!";
        }
    }else{
        $status = "1";
        $message = "Can't find user with username " . $username;
    }
}

//Image
if (isset($_FILES['myFile'])) {
    $message = "successful";
    $status = 1;
}

if(!empty($bikes))
    $json = array("status" => $status, "message"=> "Returned array of bikes", "bikes" => $bikes);
else 
    $json = array("status" => $status, "message" => $message);
echo json_encode($json, JSON_FORCE_OBJECT);
