<?php
include('../db.php');
$status = "";
$message = "";
$bikes = "";
$bike_usage = [];
$json = array();
$post_json = json_decode(file_get_contents("php://input"), true);

//Register User
if (isset($post_json["username"]) && isset($post_json["password"]) && isset($post_json["email"]) && empty($post_json["id"])) {

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

    create_response($status, $message, null);
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

    create_response($status, $message, null);
}

//Sending Location and Getting bikes
if (isset($post_json["lat"]) && isset($post_json["long"])) {

    $sql = "SELECT * FROM bikes WHERE status='0'";
    $result = mysqli_query($db, $sql);
    $bikes_array = array();
    $i = 0;
    if (mysqli_num_rows($result) > 0) {
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
    create_response($status, $message, $bikes);
}

//Data send
if (isset($post_json["username"]) && isset($post_json["bike_id"]) && isset($post_json["bike_time"]) && isset($post_json["bike_km"])) {
    $username = $post_json["username"];
    $sql = "SELECT user_id from user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) == 1) {
        $user_id = mysqli_fetch_assoc($result);
        $user_id = $user_id['user_id'];
        $bike_id = $post_json["bike_id"];
        $sql = "SELECT status from bikes WHERE id=$bike_id";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) == 1) {
            //Meşgul bike status kodu 1 olarak varsayılan yer
            $status = mysqli_fetch_assoc($result);
            if ($status["status"] == 1) {
                $date = date('Y-m-d');
                $bike_km = $post_json['bike_km'];
                $bike_time = $post_json['bike_time'];
                $sql = "INSERT INTO data (user_id,bike_id,bike_km,bike_using_time,date) VALUES ($user_id,$bike_id,$bike_km,$bike_time,'$date')";
                $result = mysqli_query($db, $sql);
                if ($result) {
                    $sql = "UPDATE bikes SET status=0 WHERE id=$bike_id";
                    $result = mysqli_query($db, $sql);
                    if ($result) {
                        $status = "0";
                        $message = "Data stored and bike" . $bike_id . " is available again";
                    } else {
                        $status = "5";
                        $message = "Can't update bikes status!";
                    }
                } else {
                    $status = "4";
                    $message = $result;
                    //$message = "Can't add bike using data!";
                }
            } else {
                $status = "3";
                $message = "Non-busy bike detected!";
            }
        } else {
            $status = "2";
            $message = "Invalid bike id!";
        }
    } else {
        $status = "1";
        $message = "Can't find user with username " . $username;
    }

    create_response($status, $message, null);
}

// Unlock bike
if (isset($post_json["bike_id"]) && isset($post_json["username"]) && empty($post_json["bike_time"])) {

    $bike_id = $post_json["bike_id"];
    $username = $post_json["username"];
    $sql = "SELECT user_id FROM user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user["user_id"];
        $sql = "SELECT status FROM bikes WHERE id=$bike_id";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) > 0) {
            $status = mysqli_fetch_assoc($result);
            if ($status["status"] == "0") {
                $sql = "UPDATE bikes SET status=1,user_id='$user_id' WHERE id=$bike_id";
                $result = mysqli_query($db, $sql);
                if ($result) {
                    $status = "0";
                    $message = "Bike is unlocked";
                } else {
                    $status = "4";
                    $message = "Database error! Couldn't update bike's status";
                }
            } else {
                $status = "3";
                $message = "Bike is not available";
            }
        } else {
            $status = "1";
            $message = "Unidentified bike_id";
        }
    } else {
        $status = "2";
        $message = "Unkown username " . $username;
    }

    create_response($status, $message, null);
}

// Weekly data (from monday to today)
if (isset($post_json["username"]) && isset($post_json["type"])) {

    $username = $post_json["username"];
    $sql = "SELECT user_id FROM user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user["user_id"];
        $select = ($post_json["type"] == "time") ? 'bike_using_time' : 'bike_km';
        $start = date("Y-m-d", strtotime("this week"));
        $end = date("Y-m-d");

        $sql = "SELECT $select FROM data WHERE user_id='$user_id' AND date BETWEEN '$start' AND '$end'";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) > 0) {
            $data = 0;
            while ($row = mysqli_fetch_assoc($result))
                //SPLIT days for graph 
                $data += $row[$select];
            $status = 0;
            $message = $data;
        } else {
            $status = 2;
            $message = "No data between " . $start . " - " . $end;
        }
    } else {
        $status = "1";
        $message = "Unkown username " . $username;
    }

    create_response($status, $message, null);
}

//Return settings
if(isset($post_json["username"]) && empty($post_json["type"]) && empty($post_json["id"])) {

    $username = $post_json["username"];
    $sql = "SELECT * FROM user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $id = $user["user_id"];
        $email = $user["email"];
        $password = $user["password"];
        $card_hash = $user["card_hash"];
        $card_last_digits = $user["card_last4"];

        $json  = array(
            'status' => 0,
            'message' => 'User settings for ' . $username,
            'user id' => $id,
            'email' => $email,
            'password' => $password,
            'card digest' => $card_hash,
            'card last four digits' => $card_last_digits
        );
        echo json_encode($json);

    }else{
        $status = "1";
        $message = "Unkown username " . $username;
        create_response($status, $message, null);
    }
}

//Send settings
if (isset($post_json["id"]) && isset($post_json["username"]) && isset($post_json["email"]) && isset($post_json["password"])){
    $id = $post_json["id"];
    $username = $post_json["username"];
    $email = $post_json["email"];
    $password = $post_json["password"];
    $sql = "SELECT * FROM user WHERE user_id='$id'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (preg_match('/^[a-z\d_]{2,20}$/', $username)) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if (preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,12}$/', $password)) {
                    $sql = "UPDATE user SET username = '$username', email = '$email', password = '$password' WHERE user_id=$id";
                    $result = mysqli_query($db, $sql);
                    if($result){
                        $message = "Updated user with id " . $id;
                        $status = "0";
                    }else{
                        $message = mysqli_error($db);
                        $status = "5";
                    }
                }else{
                    $message = "Invalid password";
                    $status = "4";
                }
            }else{
                $message = "Invalid email address";
                $status = "3";
            }
        }else{
            $message = "Invalid username";
            $status = "2";
        }
    }else{
        $message = "Unknown user id";
        $status = "1";
    }
    create_response($status, $message, null);
}

//Image
if (isset($_FILES['myFile'])) {
    $message = "successful";
    $status = 1;

    create_response($status, $message, null);
}

//Location

if (isset($post_json["location"])) {

    $location = $post_json["location"];

    $sql = "SELECT * from user where location='$location'";
    $result = mysqli_query($db, $sql);


    if (mysqli_affected_rows($db) > 0) {

        while ($row = mysqli_fetch_assoc($result)) {

            $myObj = new stdClass();
            $myObj->user_name = $row["username"];
            $myObj->bike_using_time = $row["bike_using_time"];
            $bike_usage[] = $myObj;
        }
    }


    $json  = array(

        'bike_users' => $bike_usage,
    );

    echo json_encode($json);
}

function create_response($status, $message, $bikes)
{
    if (!empty($bikes))
        $json = array("status" => $status, "message" => "Returned array of bikes", "bikes" => $bikes);
    else
        $json = array("status" => $status, "message" => $message);
    echo json_encode($json, JSON_FORCE_OBJECT);
}
