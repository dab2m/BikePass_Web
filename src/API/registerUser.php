
<?php
include('db.php');

$username = $passsword = $email = "";
if ($_POST['username'])
    $username =   $_POST['username'];
if ($_POST['password'])
    $password =   $_POST['password'];
if ($_POST['email'])
    $email =  $_POST['email'];


$sqlSelectUser = "SELECT * FROM user where username='$username' AND email='$email'";
$resultSelectUser = mysqli_query($db, $sqlSelectUser);


if (mysqli_affected_rows($db) > 0) {
    echo 0;
} else {
    $sqlAddUSer = "INSERT INTO user (username,password,email) values ('$username','$password','$email')";
    $resultAddUser = mysqli_query($db, $sqlAddUSer);
    echo 1;
}
