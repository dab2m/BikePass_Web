<?php
// Bike statusleri
//0 : servis dışı
//1 : uygun
//2 : meşgul
//3 : rezerve

include('../db.php');

$status = "";
$message = "";
$bikes = [];
$spots = [];
$requests = [];
$bike_usage = [];
$messages = [];
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

//Reset password

if (isset($post_json["usernamerecovery"]) && isset($post_json["passwordrecovery"])) {

    $username = $post_json["usernamerecovery"];
    $password = $post_json["passwordrecovery"];

    $hashedPwd = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE user SET password='$hashedPwd' WHERE username='$username' ";
    $result = mysqli_query($db, $sql);

    if (mysqli_affected_rows($db) > 0) {
        $status = "0";
        $message = "Password updated";
    } else {
        $status = "1";
        $message = "Password couldn't updated!";
    }
    create_response($status, $message, null);
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
if (isset($post_json["lat"]) && isset($post_json["long"]) && isset($post_json["usernamebikes"])) {
    $username = $post_json["usernamebikes"];
    $useraddress = getAddress($post_json["lat"], $post_json["long"])["results"][0]["formatted_address"];
    $sql = "SELECT user_id FROM user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user["user_id"];
        $sql = "SELECT * FROM bikes ";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $bike = new stdClass();
                $bike->name = 'bike' . $row['id'];
                $bike->lat = $row['lat'];
                $bike->long = $row['lng'];
                $bike->status = $row['status'];
                $bike->address = getAddress($row["lat"], $row["lng"])["results"][0]["formatted_address"];
                $bikes[] = $bike;
            }

            $sql = "SELECT * FROM requests WHERE user_id='$user_id'";
            $result = mysqli_query($db, $sql);
            if (mysqli_num_rows($result) > 0) {
                $request = mysqli_fetch_assoc($result);
                $lat = $request["lat"];
                $long = $request["lng"];
                $address = getAddress($request["lat"], $request["lng"])["results"][0]["formatted_address"];
                $status = "0";
                $message = "Returned array of bikes";
            } else {
                $status = "2";
                $message = "Returned array of bikes with no request";
            }
        }
    } else {
        $status = "1";
        $message = "No user found with name " . $username;
    }

    $json = array(
        "address" => $address,
        "useraddress" => $useraddress,
        "status" => $status,
        "message" => "Returned array of bikes",
        "bikes" => $bikes,
        "lat" => $lat,
        "long" => $long,

    );
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
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
if (isset($post_json["username"]) && isset($post_json["bike_id"]) && isset($post_json["bike_time"]) && isset($post_json["lat"]) && isset($post_json["long"])) {
    $credit = 0;
    date_default_timezone_set('Europe/Istanbul');
    $username = $post_json["username"];
    $lat = $post_json["lat"];
    $long = $post_json["long"];
    $sql = "SELECT user_id,bike_using_time, total_credit from user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['user_id'];
        $bike_id = $post_json["bike_id"];
        $bike_using_time = $user["bike_using_time"];
        $total_credit = $user["total_credit"];

        $sql = "SELECT status from bikes WHERE id=$bike_id";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) == 1) {
            //Meşgul bike status kodu 2 olarak varsayılan yer
            $status = mysqli_fetch_assoc($result);
            if ($status["status"] == 2) {
                // tüm hotpointsleri tarayıp uygun olanların işlekliğini arttır
                $sql = "SELECT * FROM hotpoints";
                $result_h = mysqli_query($db, $sql);
                if (mysqli_num_rows($result_h) > 0) {
                    while ($row = mysqli_fetch_assoc($result_h)) {
                        if (verifyArea($row["lat"], $row["lng"], $lat, $long, $row["radius"])) {
                            $hot_id = $row["id"];
                            $freq = $row["frequency"];
                            $freq_new = $freq + 1;
                            $sql = "UPDATE hotpoints SET frequency=$freq_new WHERE id=$hot_id";
                            $result = mysqli_query($db, $sql);
                            $credit = 1000;
                        }
                    }
                }

                // talep noktaları geçerli ise kapat
                $sql = "SELECT * FROM requests ORDER BY request_time ASC";
                $result_r = mysqli_query($db, $sql);
                if (mysqli_num_rows($result_r) > 0) {
                    while ($row = mysqli_fetch_assoc($result_r)) {
                        if (verifyArea($row["lat"], $row["lng"], $lat, $long, $row["radius"])) {
                            $request_time = $row["request_time"];
                            $time = date('H:i');
                            $credit_new = 2000 - (strtotime($time) - strtotime($request_time));
                            $credit = $credit + $credit_new;
                            $sql = "DELETE FROM requests WHERE id='$hot_id'";
                            $result_r = mysqli_query($db, $sql);
                            // En son açılanı sil gerisi
                            break;
                        }
                    }
                }

                $date = date('Y-m-d H:i');
                $bike_time = $post_json['bike_time'];
                $sql = "INSERT INTO data (user_id,bike_id,bike_using_time,date) VALUES ($user_id,$bike_id,$bike_time,'$date')";
                $result = mysqli_query($db, $sql);
                if ($result) {
                    $new_total_credit = ($total_credit - $bike_time) + $credit; //update credit 
                    $update_user_sql = "UPDATE user SET total_credit = '$new_total_credit' WHERE user_id = '$user_id'"; //update user table for credit
                    $sql_status = mysqli_query($db, $update_user_sql);
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
                    $message = "Can't add bike using data!";
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

    $json  = array(
        'status' => $status,
        'message' => $message,
        'credit' => $credit
    );

    echo json_encode($json);
}

// Unlock bike
if (isset($post_json["bike_id"]) && isset($post_json["username"]) && isset($post_json["lat"]) && isset($post_json["long"]) && empty($post_json["bike_time"])) {

    $lat = $post_json["lat"];
    $long = $post_json["long"];
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
                // tüm hotpointsleri tarayıp uygun olanların işlekliğini arttır
                $sql = "SELECT * FROM hotpoints";
                $result_h = mysqli_query($db, $sql);
                if (mysqli_num_rows($result_h) > 0) {
                    while ($row = mysqli_fetch_assoc($result_h)) {
                        if (verifyArea($row["lat"], $row["lng"], $lat, $long, $row["radius"])) {
                            $hot_id = $row["id"];
                            $freq = $row["frequency"];
                            $freq_new = $freq + 1;
                            $sql = "UPDATE hotpoints SET frequency=$freq_new WHERE id=$hot_id";
                            $result = mysqli_query($db, $sql);
                        }
                    }
                }
                // Talep eden bisiklet kilidi açınca talebini sil
                $sql = "DELETE FROM requests WHERE user_id='$user_id'";
                $result_r = mysqli_query($db, $sql);

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

    $isWeekly = true;
    $dayNames = true;
    if (isset($post_json["all"])) {
        $isWeekly = false;
    }
    if (isset($post_json["date"])) {
        $dayNames = false;
    }

    $username = $post_json["username"];
    $sql = "SELECT user_id FROM user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user["user_id"];
        $select = ($post_json["type"] == "time") ? 'bike_using_time' : 'bike_km';
        $start = date("Y-m-d", strtotime("this week"));
        $end = date("Y-m-d");
        if ($isWeekly) {
            $sql = "SELECT $select,date FROM data WHERE user_id='$user_id' AND date BETWEEN '$start' AND '$end'";
        } else {
            $sql = "SELECT $select,date FROM data WHERE user_id='$user_id'";
        }
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $myObj = new stdClass();
                if ($dayNames) {
                    $myObj->day = strftime('%A', strtotime($row['date']));
                } else {
                    $myObj->day = strftime('%B %d %Y', strtotime($row['date']));
                }
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
        $total_credit = $user['total_credit'];

        $json  = array(
            'status' => 0,
            'message' => 'User settings for ' . $username,
            'user id' => $id,
            'email' => $email,
            'password' => $password,
            'bike using time' => $bike_using_time,
            'total_credit' => $total_credit
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

//Return bike status
if (isset($post_json["bike_id"]) && empty($post_json["bike_time"]) && empty($post_json["username"])) {
    $bike_id = $post_json["bike_id"];
    $sql = "SELECT * FROM bikes WHERE id='$bike_id'";
    $result = mysqli_query($db, $sql);
    $username = "";
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $user_id = $row["reserve_user_id"];
        if ($user_id != "0") {
            $sql = "SELECT * FROM user WHERE user_id='$user_id'";
            $username_result = mysqli_query($db, $sql);
            $user = mysqli_fetch_assoc($username_result);
            $username = $user["username"];
        }
        switch ($row["status"]) {
            case "0":
                $message = "Bike is unavailable";
                break;
            case "1":
                $message = "Bike is available";
                break;
            case "2":
                $message = "Bike is busy";
                break;
            case "3":
                $message = "Bike is reserved for user " . $username;
                break;
        }
        $status = $row["status"];
    }
    $json  = array(
        'status' => $status,
        'message' => $message
    );
    echo json_encode($json);
}

// Location initalize
if (isset($post_json["lat"]) && $post_json["long"] && isset($post_json["usernameloc"])) {
    $lat = $post_json["lat"];
    $long = $post_json["long"];
    $username = $post_json["usernameloc"];

    $data = getAddress($lat, $lng);
    $city =  $data["results"][0]["address_components"]["0"]["long_name"];

    $sql = "UPDATE user SET location='$city' WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if ($result) {
        $status = "0";
        $message = "Location set to " . $city;
    } else {
        $status = "1";
        $message = "Unable to set location";
    }
    $json  = array(
        'status' => $status,
        'message' => $message
    );
    echo json_encode($json);
}

//Return Credit
if (isset($post_json["usernamecredit"]) && empty($post_json["credit"])) {
    $username = $post_json["usernamecredit"];
    $sql = "SELECT * from user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $status = "0";
        $message = $row["total_credit"];
    } else {
        $status = "1";
        $message = "Database error";
    }

    $json  = array(
        'status' => $status,
        'message' => $message
    );
    echo json_encode($json);
}

//Update Credit
if (isset($post_json["usernamecredit"]) && isset($post_json["credit"])) {
    $username = $post_json["usernamecredit"];
    $credit = $post_json["credit"];
    $sql = "UPDATE user SET total_credit='$credit' WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if ($result) {
        $status = "0";
        $message = "Updated Credit";
    } else {
        $status = "1";
        $message = "Database error";
    }

    $json  = array(
        'status' => $status,
        'message' => $message
    );
    echo json_encode($json);
}

//Request Bike
if (isset($post_json["usernamereq"]) && isset($post_json["lat"]) && isset($post_json["long"])) {
    $username = $post_json["usernamereq"];
    $lat = $post_json["lat"];
    $long = $post_json["long"];

    $data = getAddress($lat, $long);
    $address = $data["results"][0]["formatted_address"];

    $sql = "SELECT * from user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $user_id = $row["user_id"];
        $sql = "SELECT * from requests WHERE user_id='$user_id'";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) > 0) {
            $status = "3";
            $message = "Already have request!";
        } else {
            date_default_timezone_set('Europe/Istanbul');
            $timestamp = date("H:i");
            $sql = "INSERT INTO requests(user_id,request_time,lat,lng) VALUES ('$user_id','$timestamp','$lat','$long')";
            $result = mysqli_query($db, $sql);
            if ($result) {
                $status = "0";
                $message = "Request created";
            } else {
                $status = "2";
                $message = "Database error";
            }
        }
    } else {
        $status = "1";
        $message = "No user found with usename " . $username;
    }

    $json  = array(
        'status' => $status,
        'message' => $message,
        'address' => $address
    );
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
}

//Return hotpoints and requests
if (isset($post_json["hotpoints"])) {


    $sql = "SELECT * from hotpoints";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $spot = new stdClass();
            $spot->point_name = $row['point_name'];
            $spot->lat = $row['lat'];
            $spot->long = $row['lng'];
            $spot->radius = $row['radius'];
            $spot->frequency = $row['frequency'];
            $spots[] = $spot;
        }

        $sql = "SELECT * FROM requests";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $request = new stdClass();
                $user_id = $row['user_id'];
                $sql_user = "SELECT * from user WHERE user_id='$user_id'";
                $result_user = mysqli_query($db, $sql_user);
                $user = mysqli_fetch_assoc($result_user);
                $request->username = $user['username'];
                $request->request_time = $row['request_time'];
                $request->lat = $row['lat'];
                $request->long = $row['lng'];
                $request->radius = $row['radius'];
                $request->id = $row["id"];
                $request->address = getAddress($row["lat"], $row["lng"])["results"][0]["formatted_address"];
                $requests[] = $request;
            }

            $sql = "SELECT * FROM bikes WHERE status=1";
            $result = mysqli_query($db, $sql);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $bike = new stdClass();
                    $bike->name = 'bike' . $row['id'];
                    $bike->lat = $row['lat'];
                    $bike->long = $row['lng'];
                    $bike->status = $row['status'];
                    $bike->address = getAddress($row["lat"], $row["lng"])["results"][0]["formatted_address"];
                    $bikes[] = $bike;
                }
                $status = "0";
                $message = "Returned hotpoints, requests and available bikes";
            } else {
                $status = "3";
                $message = "No available bikes";
            }
        } else {
            $status = "2";
            $message = "No requests";
        }
    } else {
        $status = "1";
        $message = "No hotpoints";
    }


    $json = array(
        "status" => $status,
        "message" => $message,
        "hotpoints" => $spots,
        "requests" => $requests,
        "bikes" => $bikes,

    );
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
}

// Delete Request
if (isset($post_json["deletereq"])) {
    $username = $post_json["deletereq"];
    $sql = "SELECT user_id from user WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $user_id = $row["user_id"];
        $sql = "DELETE FROM requests WHERE user_id='$user_id'";
        $result = mysqli_query($db, $sql);
        if ($result) {
            $status = "0";
            $message = "Removed request";
        } else {
            $status = "2";
            $message = "Database error";
        }
    } else {
        $status = "1";
        $message = "No user with username " . $username;
    }

    $json  = array(
        'status' => $status,
        'message' => $message
    );
    echo json_encode($json);
}

//Send Message
if (isset($post_json["from"]) && isset($post_json["to"]) && isset($post_json["head"]) && isset($post_json["body"])) {
    $from = $post_json["from"];
    $to = $post_json["to"];
    $sql = "SELECT user_id FROM user WHERE username='$from'";
    $username_result = mysqli_query($db, $sql);
    if (mysqli_num_rows($username_result) == 1) {
        $user = mysqli_fetch_assoc($username_result);
        $from_id = $user["user_id"];
    } else {
        $status = "1";
        $message = "No sender with username " . $from;
    }
    $sql = "SELECT user_id FROM user WHERE username='$to'";
    $username_result = mysqli_query($db, $sql);
    if (mysqli_num_rows($username_result) == 1) {
        $user = mysqli_fetch_assoc($username_result);
        $to_id = $user["user_id"];
    } else {
        $status = "2";
        $message = "No receiver with username " . $to;
    }
    $head = $post_json["head"];
    $body = $post_json["body"];
    $sql = "INSERT INTO messages(from_user,to_user,head,body) VALUES ('$from_id','$to_id','$head','$body')";
    $result = mysqli_query($db, $sql);
    if ($result) {
        $status = "0";
        $message = "Message sent to " . $to;
    } else {
        $status = "3";
        $message = "Database error";
    }

    $json  = array(
        'status' => $status,
        'message' => $message
    );
    echo json_encode($json);
}

//Receive messages
if (isset($post_json["messages"])) {
    $username = $post_json["messages"];
    $all = false;
    $sql = "SELECT user_id FROM user WHERE username='$username'";
    $username_result = mysqli_query($db, $sql);
    if (mysqli_num_rows($username_result) == 1) {
        $user = mysqli_fetch_assoc($username_result);
        $user_id = $user["user_id"];
    } else {
        $status = "1";
        $message = "No user with username " . $username;
    }
    if (isset($post_json["all"])) {
        $all = true;
    }

    if ($all)
        $sql = "SELECT * FROM messages WHERE to_user='$user_id'";
    else
        // 0 okunmamış 1 okunmuş mesajlar
        $sql = "SELECT * FROM messages WHERE to_user='$user_id' AND unread='0'";
    $result_msg = mysqli_query($db, $sql);
    if (mysqli_num_rows($result_msg) > 0) {
        while ($row = mysqli_fetch_assoc($result_msg)) {
            $msg = new stdClass();
            $msg->from = $row["from_user"];
            $msg->to = $row["to_user"];
            $msg->head = $row["head"];
            $msg->body = $row["body"];
            $msg->unread = $row["unread"];
            $messages[] = $msg;

            $id = $row["id"];
            $read = "UPDATE messages SET unread=1 WHERE id='$id'";
            $result = mysqli_query($db, $read);
        }
        $status = "0";
        $message = "Returned messages";
    } else {
        $status = "1";
        $message = "No messages for " . $username;
    }
    $json  = array(
        'status' => $status,
        'message' => $message,
        'messages' => $messages
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

// Haversine Formulü implementi 
// Kaynak https://www.it-swarm.dev/tr/php/php-ile-haversine-formulu/1071435883/
function verifyArea($latitude1, $longitude1, $latitude2, $longitude2, $radius)
{
    $earth_radius = 6371;
    $radius = $radius / 1000;
    $dLat = deg2rad($latitude2 - $latitude1);
    $dLon = deg2rad($longitude2 - $longitude1);

    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * asin(sqrt($a));
    $d = $earth_radius * $c;

    if ($d < $radius)
        return true;
    else
        return false;
}

function getAddress($lat, $lng)
{
    $api_key = getenv("API_KEY");
    $parameters = "latlng=" . $lat . "," . $lng . "&sensor=true&key=" . $api_key . "&language=tr&region=tr";
    $location = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?$parameters");
    return json_decode($location, true);
}
