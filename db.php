<?php    
    // mysql://b1605779e7e37a:3e0cafa2@us-cdbr-iron-east-05.cleardb.net/heroku_6c92dd43fc9515d?reconnect=true
    
    $db = mysqli_connect('us-cdbr-iron-east-05.cleardb.net','b1605779e7e37a','3e0cafa2','heroku_6c92dd43fc9515d');
    
    //check connection
    if (!$db) {
        echo 'Connection error:' . mysqli_connect_error();
    }
?>