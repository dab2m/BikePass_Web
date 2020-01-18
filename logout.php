<?php
    
    if(!session_id())
        session_start();
    session_destroy();
    
    echo "<script> confirm('Logged out...'); window.location.href='index.php'; </script>";
?>