<?php

//connect to database
// $conn = mysqli_connect('localhost', 'dilan', 'S8wzpZ66E', 'bitirme');
//connect to database with default user
// $conn = mysqli_connect('localhost', 'root', '', 'bitirme');

// mysql://b1605779e7e37a:3e0cafa2@us-cdbr-iron-east-05.cleardb.net/heroku_6c92dd43fc9515d?reconnect=true

$conn = mysqli_connect('us-cdbr-iron-east-05.cleardb.net','b1605779e7e37a','3e0cafa2','heroku_6c92dd43fc9515d');

//check connection
if (!$conn) {
    echo 'Connection error:' . mysqli_connect_error();
}
?>
