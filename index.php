<?php

include('db.php');

$username = $email = $password = $question = $answer = '';
$errors = array('username' => '', 'email' => '', 'password' => '', 'question' => '', 'answer' => '');
if (isset($_POST['signup'])) {


  //check username
  $username = $_POST['username'];
  if (!preg_match('/^[a-z\d_]{2,20}$/', $username)) {
    $errors['username'] = 'Username must be a valid username';
  }

  //check email
  $email = $_POST['email'];
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email must be a valid email address';
  }

  //check password{
  $password = $_POST['password'];
  if (!preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,12}$/', $password)) {
    $errors['password'] = 'The password does not meet the requirements!';
    }

  if (array_filter($errors)) {
    echo "<script type='text/javascript'>
      window.onload=function(){
        document.getElementById('myModalSıgnUp').style.display = 'block';
      };
      </script>";
  } else {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password = mysqli_real_escape_string($db, $_POST['password']);
    
    $question = mysqli_real_escape_string($db, $_POST['question']);
    $answer = mysqli_real_escape_string($db, $_POST['answer']);
    
    $hashedpassword = substr(md5($password),0,20);
    
    $sqlName = "SELECT * from user where username='$username'";
    $resultName = mysqli_query($db, $sqlName);

    $sqlEmail = "SELECT * from user where email='$email'";
    $resultEmail = mysqli_query($db, $sqlEmail);

    if (mysqli_num_rows($resultName) == 1) {

      $errors['username'] = 'This username already exists';
      echo "<script type='text/javascript'>
      window.onload=function(){
        document.getElementById('myModalSıgnUp').style.display = 'block';
      };
      </script>";
    } else if (mysqli_num_rows($resultEmail) == 1) {

      $errors['email'] = 'This email already exists';
      echo "<script type='text/javascript'>
      window.onload=function(){
        document.getElementById('myModalSıgnUp').style.display = 'block';
      };
      </script>";
    } else {

        $sql = "INSERT INTO user(username,email,password,question,answer,total_credit) VALUES ('$username','$email','$hashedpassword','$question','$answer',600)";

      if (mysqli_query($db, $sql)) {
        echo "<script type='text/javascript'>
        window.onload=function(){
          document.getElementById('myModalSıgnUp').style.display = 'none';
          document.getElementById('myModalLogIn').style.display = 'block';
        };
        </script>";
      } else {
        echo 'query error:' . mysql_error($db);
      }
    }
  }
}


$error = array('userloginerror' => '', 'username' => '', 'password' => '', 'both' => '', 'email' => '');

if (isset($_POST['signin'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];
  if ($username != '' && $password != '') {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    $hashedpassword = substr(md5($password),0,20);
    
    $sql = "SELECT * from user where username='$username' and password='$hashedpassword'";

    $result = mysqli_query($db, $sql);

    $row = mysqli_fetch_assoc($result);

    if (mysqli_affected_rows($db) == 1) {
      session_start();
      $_SESSION['username'] = $username;
      $_SESSION['password'] = $password;
      $_SESSION['id'] = $row['user_id'];
      header('Location:main.php');
    } else if (mysqli_affected_rows($db) == 0) {
      $error['userloginerror'] = 'Your username or password is wrong!';
    } else {
      echo 'query error';
    }
  } else if ($username != '') {
    $error['password'] = 'Password cant be empty';
  } else if ($password != '') {
    $error['username'] = 'Username cant be empty';
  }
  if (array_filter($error)) {
    echo "<script type='text/javascript'>
      window.onload=function(){
        document.getElementById('myModalLogIn').style.display = 'block';
      };
      </script>";
  }
}

if (isset($_POST['forgotpassword'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];

    if ($username != '' && $email != '') {

        $username = mysqli_real_escape_string($db, $_POST['username']);
        $email = mysqli_real_escape_string($db, $_POST['email']);
        
        $sql = "SELECT * from user where username = '$username' and email = '$email'";
        
        $result = mysqli_query($db, $sql);
        
        $row = mysqli_fetch_assoc($result);
        
        if (mysqli_affected_rows($db) == 1) {

            session_start();
            $_SESSION['username'] = $username;
            $_SESSION['id'] = $row['user_id'];
            $_SESSION['question'] = $row['question'];
            $_SESSION['answer'] = $row['answer'];
            
            header('Location:forgotpassword.php');
            
        } else if (mysqli_affected_rows($db) == 0) {
            $error['userloginerror'] = 'Your username or email is wrong!';
        } else {
            echo 'query error';
        }
    } else if ($username != '') {
        $error['password'] = 'Password cant be empty';
    } else if ($email != '') {
        $error['email'] = 'Mail cant be empty';
    }
    if (array_filter($error)) {
        echo "<script type='text/javascript'>
      window.onload=function(){
        document.getElementById('myModalLogIn').style.display = 'block';
      };
      </script>";
    }
}


?>



<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <title>BikePass</title>
  <link rel="icon" type="image/png" href="images/favicon.png">
</head>
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<!-- Bootstrap scripts -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<link rel="stylesheet" href="styles/main.css">
<link rel="stylesheet" href="styles/modal.css">

<header>
  <nav class="navbar navbar-expand-lg" id=navbar-header>

    <img src="images/bikepass-logo.png" alt="BikePass Logo" class="logo">
    <h1 class="site-title">BikePass</h1>

    <button class="navbar-toggler toggler-button" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="true" aria-label="Toggle navigation">
      <span style="color: #cdeeaa"><i class="fas fa-bicycle fa-2x"></i></span>
    </button>

    <div class="collapse navbar-collapse" id="navbar">
      <ul class="navbar-nav ml-auto mr-5">
        <li class="nav-item">
          <a class="nav-link" href="#howitworks">How it works?</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#features">Features</a>
        </li>
        <li class="nav-item">
          <p class="nav-link likeButton" onclick="showSıgnUp()" href="">Sign up</p>
        </li>
        <li class="nav-item">
          <p class="nav-link likeButton" onclick="showLogIn()" href="">Login</p>
        </li>
      </ul>
    </div>

  </nav>
</header>

<body>

  <script>
    function showSıgnUp() {
      document.getElementById("myModalLogIn").style.display = "none";
      document.getElementById("myModalSıgnUp").style.display = "block";
    }

    function showLogIn() {
      document.getElementById("myModalSıgnUp").style.display = "none";
      document.getElementById("myModalLogIn").style.display = "block";
    }

    function showForgotPassword() {
        document.getElementById("myModalLogIn").style.display = "none";
        document.getElementById("myModalForgotPassword").style.display = "block";
      }
    

    window.onclick = function(event) {
      if (event.target == document.getElementById("myModalSıgnUp")) {
        document.getElementById("myModalSıgnUp").style.display = "none";
      }
      if (event.target == document.getElementById("myModalLogIn")) {
        document.getElementById("myModalLogIn").style.display = "none";
      }
      if (event.target == document.getElementById("myModalForgotPassword")) {
          document.getElementById("myModalForgotPassword").style.display = "none";
        }
    }

    function closeModal(modal){
     document.getElementById(modal).style.display="none";
    }

  </script>

  <!-- Catchphrase sectionı -->
  <section class="creme">

    <div class="container-fluid">
      <div class="row">

        <div class="col-lg-5 offset-lg-1">
          <h1 class="slogan">Better transportation for Nature and You</h1>
          <button type="button" name="button" class="btn btn-lg custom">
            <img src="images/google_play.png" alt="Playstore icon" class="icon">
            <a href=""> Get on Google PlayStore</a>
          </button>
        </div>

        <div class="col-lg-6">
          <img src="images/women-with-bike.png" alt="https://www.freepik.com/free-photos-vectors/people" class="col">
        </div>
      </div>
    </div>

  </section>

  <!-- How it works sectionı -->
  <section class="green" id="howitworks">

    <h1 class="text-center small-title">How it works ?</h1>
    <div class="container-fluid">
      <div class="row">

        <div class="col-lg-3 feature">
          <img src="images/enterphone.png" alt="enterphone" class="col">
          <h1 class="feature-text">Download our app on your Android phones. Enter your name, e-mail adress and credit card info to sign up</h1>
        </div>

        <div class="col-lg-3 feature">
          <img src="images/findbike.png" alt="findbike" class="col">
          <h1 class="feature-text">Once you signed up, you can look up for available bikes on map</h1>
        </div>

        <div class="col-lg-3 feature">
          <img src="images/usebike.png" alt="usebike" class="col">
          <h1 class="feature-text">Go next to one of our bikes and scan QR code located on them.<br>Enjoy cycling !</h1>
        </div>

        <div class="col-lg-3 feature">
          <img src="images/lockbike.png" alt="lockbike" class="col">
          <h1 class="feature-text">When you decide to conclude your travel, park bike to convenient spot and confirm from you app as well</h1>
        </div>

      </div>
    </div>
  </section>



  <!-- The Modal -->
  <div id="myModalSıgnUp" style="display:none;" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
      <div>
        <span class="close" onclick=closeModal("myModalSıgnUp")>&times;</span>
        <h2 class="welcome">Welcome to BikePass!</h2>
      </div>
      <div class="modal-body">

        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
        <div class="inputdiv">
          <input class="input" type="text" name="username" value="<?php echo htmlspecialchars($username) ?>" required>
            <label class="usernamePassword">
              <span class="form-span">Username</span>
            </label>
        </div>
          <div class="red-text"><?php echo $errors['username']; ?></div>
        <div class="inputdiv">
          <input class="input" type="text" name="email" value="<?php echo htmlspecialchars($email) ?>" required>
          <label class="usernamePassword">
            <span class="form-span">Email</span>
          </label>
        </div>
          <div class="red-text"><?php echo $errors['email']; ?></div>
        <div class="inputdiv">
        <input class="input" type="password" name="password" value="<?php echo htmlspecialchars($password) ?>" required>
          <label class="usernamePassword">
            <span class="form-span">Password</span>
          </label>  
        </div>
        <div class="inputdiv">
        <select class="input" name="question" value="<?php echo htmlspecialchars($question) ?>" required>
            <option value="What is your Mother's maiden name?">What is your Mother's maiden name?</option>
            <option value="What was the name of your first pet?">What was the name of your first pet?</option>
            <option value="What was the first record/CD you first bought?">What was the first record/CD you first bought?</option>
            <option value="What is your favourite place?">What is your favourite place?</option>
            <option value="What is the name of your first school?">What is the name of your first school?</option>
         </select>
        </div>
        
        <div class="inputdiv">
        <input class="input" type="text" name="answer" value="<?php echo htmlspecialchars($answer) ?>" required>
          <label class="usernamePassword">
            <span class="form-span">Security Answer</span>
          </label>  
        </div>
        
          <div class="red-text"><?php echo $errors['password']; ?></div>
          <div><input class=" signupButton" type="submit" name="signup" id="signup" value="SIGN UP" class="btn brand z-depth-0"><br />
            <p class="signup-text">You already have an account? <a class="signIn" onclick="showLogIn()">Log in<a>
                  <p>
          </div>
        </form>
      </div>
      <div>
      </div>
    </div>
  </div>



  <!-- The Modal -->
  <div id="myModalLogIn" style="display:none;" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
      <div>
        <span class="close" onclick=closeModal("myModalLogIn")>&times;</span>
        <h2 class="welcome">Welcome Back!</h2>
      </div>
      <div class="modal-body">

        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
        <div class="inputdiv">
          <input class="input" type="text" name="username" value="<?php echo htmlspecialchars($username) ?>" required>
            <label class="usernamePassword">
             <span class="form-span">Username</span>
            </label>
        </div>
          <div class="red-text"><?php echo $error['username']; ?></div>
          <div class="inputdiv">
            <input class="input" type="password" name="password" value="<?php echo htmlspecialchars($password) ?>" required>
            <label class="usernamePassword">
              <span class="form-span">Password</span>
          </label>
          </div>
          <div class="red-text"><?php echo $error['email']; ?></div>
          <div class="red-text"><?php echo $error['userloginerror']; ?></div>
          <div><input class=" signupButton" type="submit" name="signin" id="sign in" value="LOG IN" class="btn brand z-depth-0"><br />
            <p class="infoLocale">Dont you have an account? <a class="signIn" onclick="showSıgnUp()">Sign up!<a>
                  <p>
            <p class="infoLocale">Forgot Password? <a class="signIn" onclick="showForgotPassword()">Restore!<a>
                  <p>
          </div>
        </form>

      </div>
      <div>
      </div>
    </div>
  </div>
  
  
  
    <!-- The Modal -->
  <div id="myModalForgotPassword" style="display:none;" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
      <div>
        <span class="close" onclick=closeModal("myModalForgotPassword")>&times;</span>
        <h2 class="welcome">Forgot Password?</h2>
      </div>
      <div class="modal-body">

        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
        <div class="inputdiv">
          <input class="input" type="text" name="username" value="<?php echo htmlspecialchars($username) ?>" required>
            <label class="usernamePassword">
             <span class="form-span">Username</span>
            </label>
        </div>
          <div class="red-text"><?php echo $error['username']; ?></div>


        <div class="inputdiv">
          <input class="input" type="text" name="email" value="<?php echo htmlspecialchars($email) ?>" required>
          <label class="usernamePassword">
            <span class="form-span">Email</span>
          </label>
        </div>
          <div class="red-text"><?php echo $error['email']; ?></div>

          <div class="red-text"><?php echo $error['userloginerror']; ?></div>
          <div><input class=" signupButton" type="submit" name="forgotpassword" id="sign in" value="SUBMIT" class="btn brand z-depth-0"><br /></div>
        </form>

      </div>
      <div>
      </div>
    </div>
  </div>



  <!-- Features sectionı -->
  <section class="creme" id="features">
  <h1 class="text-center small-title">Features</h1>
    <div class="container-fluid">
      <div class="row text-center m-auto">

        <div class="col-lg-2 col-md-4 offset-lg-1 features">
          <h1 class="side-title">Easy to signup</h1>
          <span style="color: #97bd6f"><i class="fas fa-file-signature fa-5x"></i></span>
          <p class="feature-text">Enter your name and email address to sign up and start cycling!</p>
        </div>
        <div class="col-lg-2 col-md-4 features">
          <h1 class="side-title">Simple usage</h1>
          <span style="color: #97bd6f"><i class="fas fa-biking fa-5x"></i></span>
          <p class="feature-text">Go next to nearest bike and scan QR code located on it to start using.No need to wait!</p>
        </div>
        <div class="col-lg-2 col-md-4 features">
          <h1 class="side-title">Leaderboard</h1>
          <span style="color: #97bd6f"><i class="fas fa-medal fa-5x"></i></span>
          <p class="feature-text">View how much you cycle this month and compete with other bike enthusiasts globally!</p>
        </div>
        <div class="col-lg-2 col-md-4 features">
          <h1 class="side-title">Active Support</h1>
          <span style="color: #97bd6f"><i class="fas fa-phone-square-alt fa-5x"></i></span>
          <p class="feature-text">A problem occurred? Don't worry, you can reach us through app and report problem</p>
        </div>
        <div class="col-lg-2 col-md-4 features">
          <h1 class="side-title">Eco-Friendly</h1>
          <span style="color: #97bd6f"><i class="fab fa-pagelines fa-5x"></i></span>
          <p class="feature-text">We also believe in action needed to take in environment. So start using BikePass to reduce damage on environment caused by automobiles and traffic!</p>
        </div>

      </div>
    </div>
    </div>
  </section>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/js/all.min.js"></script>
</body>

<footer>
  <div class="m-auto text-center" style="padding-top: 1rem">
    <p class="site-title">
      Illustrations and images by
      <a href="https://www.freepik.com">Freepik</a> &
      <a href="https://www.freepik.com/johndory">Johndory</a> &
      <a href="https://www.freepik.com/studiogstock">Stduiogstock</a>
    </p>
  </div>
</footer>

</html>
