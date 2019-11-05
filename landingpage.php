<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>BikePass</title>
    <link rel="icon"  type="image/png" href="images/favicon.png">
  </head>

  <!-- Font Awesome Link -->
  <link rel="stylesheet" href="fontawesome/css/all.css">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <!-- Bootstrap scripts -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

  <link rel="stylesheet" href="styles/main.css">

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
            <a class="nav-link" href="">Features</a>
            </li>
          <li class="nav-item">
            <a class="nav-link" href="">Sign up</a>
            </li>
          <li class="nav-item">
            <a class="nav-link" href="">Login</a>
            </li>
        </ul>
      </div>

    </nav>
  </header>

  <body>

    <!-- Catchphrase sectionı -->
    <section class="creme">

      <div class="container-fluid">
        <div class="row">

          <div class="col-lg-5 offset-lg-1">
            <h1 class="slogan">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Bibendum est ultricies integer quis auctor.</h1>
            <button type="button" name="button" class="btn btn-lg btn-dark custom">
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

  </body>
</html>
