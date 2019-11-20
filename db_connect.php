<?php

//connect to database
// $conn = mysqli_connect('localhost', 'dilan', 'S8wzpZ66E', 'bitirme');
//connect to database with default user
$conn = mysqli_connect('localhost', 'root', '', 'bitirme');

//check connection
if (!$conn) {
    echo 'Connection error:' . mysqli_connect_error();
}
?>
