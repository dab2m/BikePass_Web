<?php
// Bike statusleri
//0 : servis dışı
//1 : uygun
//2 : meşgul
//3 : rezerve

include('../db.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer\src\Exception.php';
require 'PHPMailer\src\PHPMailer.php';
require 'PHPMailer\src\SMTP.php';

$status = "";
$message = "";
$bikes = "";
$bike_usage = [];
$json = array();
$post_json = json_decode(file_get_contents("php://input"), true);

//Recovery question
if (isset($post_json["usernamerec"])) {

    $username = $post_json["usernamerec"];
    $sql = "SELECT * FROM user WHERE username='$username'";
    $username_result = mysqli_query($db, $sql);
    if (mysqli_num_rows($username_result) > 0) {

        if ($row = mysqli_fetch_assoc($username_result)) {

            $message = "User exist";
            $status = "1";
            $myObj = new stdClass();
            $myObj->question = $row['question'];
            $myObj->email = $row['email'];
            $myObj->answer = $row['answer'];
            $recovery_data[] = $myObj;
        }
    } else {
        $message = "There is no such a user";
        $status = "0";
    }

    $json  = array(
        'status' => $status,
        'message' => $message,
        'data' => $recovery_data
    );

    echo json_encode($json);
}

//Register User
if (isset($post_json["username"]) && isset($post_json["password"]) && isset($post_json["email"]) && empty($post_json["id"]) && isset($post_json["question"]) && isset($post_json["answer"])) {

    $email = $post_json["email"];
    $username = $post_json["username"];
    $password = $post_json["password"];
    $question = $post_json["question"];
    $answer = $post_json["answer"];

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
            $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO user(username,password,email,question,answer) VALUES ('$username','$hashedPwd','$email','$question','$answer')";
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
        $pwdCheck = password_verify($password, $login['password']);
        if ($pwdCheck == true) {
            $message = "Login succesful. Welcome back " . $username;
            $status = "0";
        } else if ($pwdCheck == false) {
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

    $sql = "SELECT * FROM bikes ";
    $result = mysqli_query($db, $sql);
    $bikes_array = array();
    $i = 0;
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Uzaklık hesabı yapılacak yer
            $bikes_array[$i]['name'] = 'bike' . $row['id'];
            $bikes_array[$i]['lat'] = $row['lat'];
            $bikes_array[$i]['long'] = $row['lng'];
            $bikes_array[$i]['status'] = $row['status'];
            $i++;
        }
        $status = "0";
        $bikes = $bikes_array;
    }
    create_response($status, $message, $bikes);
}
//Rezerve bike
if (isset($post_json["bike_id"]) && isset($post_json["usernameres"])) {
    $bike_id = $post_json["bike_id"];
    $username = $post_json["usernameres"];
    date_default_timezone_set('Europe/Istanbul');
    $timestamp = date("H:i");
    $sql = "SELECT user_id FROM user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $user_id = $row["user_id"];
        $sql = "SELECT status FROM bikes WHERE id='$bike_id'";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $status = $row["status"];
            if ($status == 1) {
                $sql = "UPDATE bikes SET status=3,timestamp='$timestamp',reserve_user_id='$user_id' WHERE id=$bike_id";
                $result = mysqli_query($db, $sql);
                if ($result) {
                    $status = "0";
                    $message = "Bike status updated";
                } else {
                    $status = "4";
                    $message = "Can't update bikes status!";
                }
            } else {
                $status = "3";
                $message = "Bike is not available for reserving";
            }
        } else {
            $status = "2";
            $message = "Unidentified bike_id";
        }
    } else {
        $status = "1";
        $message = "No user found with username " . $username;
    }

    create_response($status, $message, null);
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
            //Meşgul bike status kodu 2 olarak varsayılan yer
            $status = mysqli_fetch_assoc($result);
            if ($status["status"] == 2) {
                $date = date('Y-m-d');
                $bike_km = $post_json['bike_km'];
                $bike_time = $post_json['bike_time'];
                $sql = "INSERT INTO data (user_id,bike_id,bike_km,bike_using_time,date) VALUES ($user_id,$bike_id,$bike_km,$bike_time,'$date')";
                $result = mysqli_query($db, $sql);
                if ($result) {
                    $sql = "UPDATE bikes SET status=1 WHERE id=$bike_id";
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
            if ($status["status"] == "1") {
                $sql = "UPDATE bikes SET status=2,user_id='$user_id' WHERE id=$bike_id";
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

        $sql = "SELECT $select,date FROM data WHERE user_id='$user_id' AND date BETWEEN '$start' AND '$end'";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $myObj = new stdClass();
                $myObj->day = strftime('%A', strtotime($row['date']));
                $myObj->$select = $row[$select];
                $bike_usage[] = $myObj;
            }
            $status = 0;
            $message = "Returned data";
        } else {
            $status = 2;
            $message = "No data between " . $start . " - " . $end;
        }
    } else {
        $status = "1";
        $message = "Unkown username " . $username;
    }

    $json  = array(
        'status' => $status,
        'message' => $message,
        'data' => $bike_usage
    );

    echo json_encode($json);
}

//Return settings
if (isset($post_json["username"]) && empty($post_json["type"]) && empty($post_json["id"]) && empty($post_json["bike_id"])) {

    $username = $post_json["username"];
    $sql = "SELECT * FROM user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $id = $user["user_id"];
        $email = $user["email"];
        $password = $user["password"];
        $bike_using_time = $user['bike_using_time'];
        $card_hash = $user["card_hash"];
        $card_last_digits = $user["card_last4"];

        $json  = array(
            'status' => 0,
            'message' => 'User settings for ' . $username,
            'user id' => $id,
            'email' => $email,
            'password' => $password,
            'bike using time' => $bike_using_time,
            'card digest' => $card_hash,
            'card last four digits' => $card_last_digits
        );
        echo json_encode($json);
    } else {
        $status = "1";
        $message = "Unkown username " . $username;
        create_response($status, $message, null);
    }
}

//Send settings
if (isset($post_json["id"]) && isset($post_json["username"]) && isset($post_json["email"]) && isset($post_json["password"])) {
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
                    if ($result) {
                        $message = "Updated user with id " . $id;
                        $status = "0";
                    } else {
                        $message = mysqli_error($db);
                        $status = "5";
                    }
                } else {
                    $message = "Invalid password";
                    $status = "4";
                }
            } else {
                $message = "Invalid email address";
                $status = "3";
            }
        } else {
            $message = "Invalid username";
            $status = "2";
        }
    } else {
        $message = "Unknown user id";
        $status = "1";
    }
    create_response($status, $message, null);
}

//Credit Card details
if (isset($post_json["id"]) && isset($post_json["card digest"]) && isset($post_json["last digits"])) {
    $id = $post_json["id"];
    $card_digest = $post_json["card digest"];
    $card_last_digits = $post_json["last digits"];
    $sql = "SELECT * FROM user WHERE user_id='$id'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $sql = "UPDATE user SET card_hash = '$card_digest', card_last4 = '$card_last_digits' WHERE user_id=$id";
        $result = mysqli_query($db, $sql);
        if ($result) {
            $message = "Updated card details for user with id " . $id;
            $status = "0";
        } else {
            $message = mysqli_error($db);
            $status = "2";
        }
    } else {
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
    $sql = "SELECT * from user ORDER BY bike_using_time DESC";
    $result = mysqli_query($db, $sql);

    if (mysqli_affected_rows($db) > 0) {
        $status = 0;
        $message = "Returned users";

        while ($row = mysqli_fetch_assoc($result)) {

            $myObj = new stdClass();
            $myObj->user_name = $row["username"];
            $myObj->bike_using_time = $row["bike_using_time"];
            $myObj->location = $row["location"];
            $bike_usage[] = $myObj;
        }
    } else {
        $status = 1;
        $message = "Database error";
    }

    $json  = array(
        'status' => $status,
        'message' => $message,
        'bike_users' => $bike_usage
    );

    echo json_encode($json);
}

//Today
if (isset($post_json["username_today"])) {
    $bike_km = 0;
    $bike_time = 0;
    $username = $post_json["username_today"];
    $sql = "SELECT * from user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user["user_id"];
        $today = date('Y-m-d');
        $sql = "SELECT * from data WHERE user_id='$user_id' AND date='$today'";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $bike_km += $row['bike_km'];
                $bike_time += $row['bike_using_time'];
            }
            $status = 0;
            $message = "Returned data for today";
        } else {
            $status = 2;
            $message = "No data for today";
        }
    } else {
        $status = 1;
        $message = "No user with username " . $username;
    }

    $json  = array(
        'status' => $status,
        'message' => $message,
        'bike_km' => $bike_km,
        'bike_time' => $bike_time
    );

    echo json_encode($json);
}
//Recover Email
if (isset($post_json["recovery_email"]) && isset($post_json["usernamerec"])) {
    $email = $post_json["recovery_email"];
    $username = $post_json["usernamerec"];
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'bikepass496@gmail.com';                     // SMTP username
        $mail->Password   = 'bil496graduationproject';                               // SMTP password
        $mail->SMTPSecure = 'tls';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom('recovery@bikepass.com', 'BikePass');
        $mail->addAddress($email, ' User ' . $username);     // Add a recipient
        $mail->addReplyTo('no-reply@bikepass.com', 'No reply');


        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Recovery mail from BikePass';
        $mail->Body    = '<b>Please click the link below to reset your password!</b><br>bikepass.herokuapp.com/changepassword.php';

        $mail->send();
        $status = 1;
        $message = "Message has been sent";
        // echo 'Message has been sent';
    } catch (Exception $e) {
        $status = 0;
        $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    create_response($status, $message, null);
}

function create_response($status, $message, $bikes)
{
    if (!empty($bikes))
        $json = array("status" => $status, "message" => "Returned array of bikes", "bikes" => $bikes);
    else
        $json = array("status" => $status, "message" => $message);
    echo json_encode($json, JSON_FORCE_OBJECT);
}
