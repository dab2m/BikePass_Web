
<?php
include('db.php');

$username = $passsword = "";
if ($_POST['username'])
    $username =   $_POST['username'];
if ($_POST['password'])
    $password =   $_POST['password'];

$sqlLoginUser = "SELECT * FROM user where username='$username' AND password='$password'";
$resultLoginUser = mysqli_query($db, $sqlLoginUser);


if (mysqli_affected_rows($db) > 0) {
    echo 1;
} else {
    echo 0;
}
