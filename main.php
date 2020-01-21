<?php 
    include 'db.php';
    session_start();
    if(!isset($_SESSION['username']))
        header("location:index.php");
    $query = "SELECT * FROM `user` WHERE `user_id` = " . $_SESSION['id'];
    $res = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>Anasayfa</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta content="" name="description"/>
<meta content="" name="author"/>

<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css">
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css">
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css">

<link href="assets/global/css/components-rounded.css" id="style_components" rel="stylesheet" type="text/css">
<link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css">
<link href="assets/admin/layout3/css/layout.css" rel="stylesheet" type="text/css">
<link href="assets/admin/layout3/css/themes/default.css" rel="stylesheet" type="text/css" id="style_color">
<link href="assets/admin/layout3/css/custom.css" rel="stylesheet" type="text/css">
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<body>
<div class="page-container">
	<!-- BEGIN PAGE HEAD -->
	<div class="page-head">
		<div class="top-menu">
				
				<ul class="nav navbar-nav pull-right">
					<li class="dropdown dropdown-extended dropdown-tasks ms-hover" id="header_task_bar">
						<a href="logout.php" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" onclick="window.location.href='logout.php';">
						<i class="icon-power"></i>
						</a>
					</li>
				</ul>
				
			</div>
		<div class="container">
			<div class="page-title">
				<h1>Main Page</h1>
			</div>
			
		</div>
	</div>



	<div class="page-content">
		<div class="container">	
			<div class="portlet light">
				<div class="portlet-body">
					<div class="row">
						<div class="col-md-12">
							<!-- Google Map -->
							<div class="row">
								<div id="map" style="height:400px;"></div>
								<script>
                                    function initMap() {
                                    	  // The location of Uluru
                                    	  var ankara = {lat: 39.933365, lng: 32.859741};
                                    	  // The map, centered at Uluru
                                    	  var map = new google.maps.Map(document.getElementById("map"), {zoom: 4, center: ankara});
                                    	  // The marker, positioned at Uluru
                                    	  var marker = new google.maps.Marker({position: ankara, map: map});
                                    	}  
                                </script>
							</div>
							<div class="row margin-bottom-20">
								<div class="col-md-3">
									<div class="space20">
									</div>
									<h3 class="form-section">Profile</h3>
									<div class="well">
										<address>
    										<strong>
    											<button type="button" class="btn blue" style="float:right;" onclick="location.href='edit_profile.php';">Edit</button>
    										</strong>
										</address>
										
										<address>
										<strong>User Name:</strong>
										<p><?php echo $row['username'] ?></p>
										</address>
										
										<address>
										<strong>E-Mail:</strong><br>
										<a href="mailto:#">
										<?php echo $row['email']; ?> </a>
										</address>
										
										<address>
										<strong>Distance (Km):</strong><br>
										<p> <?php echo $row['bike_km']; ?> </p>
										</address>
										
										<address>
										<strong>Bike Using Time:</strong><br>
										<p> <?php echo $row['bike_using_time']; ?> </p>
										</address>
										
									</div>
								</div>

								<div class="col-md-3">
									<div class="space20">
									</div>
									<h3 class="form-section">Credit Card Info</h3>
									<div class="well">
										<address>
    										<strong>
    											<button type="button" class="btn blue" style="float:right;" onclick="location.href='edit_card.php';">Edit</button>
    										</strong>
										</address>
										
										<address>
										<strong>Card Number:</strong>
										<p><?php echo $row['card_num'] ?></p>
										</address>
										
										<address>
										<strong>Card CCV:</strong><br>
										<a href="mailto:#">
										<?php echo $row['card_ccv']; ?> </a>
										</address>
										
										<address>
										<strong>Card Expiration Date:</strong><br>
										<p> <?php echo $row['card_date']; ?> </p>
										</address>

									</div>
								</div>
								
								<div class="col-md-3">
									<div class="space20">
									</div>
									<h3 class="form-section">Top 10 Distance:</h3>
									<div class="well">
									
										<address>
											<ol>
												<?php 
												    $topsql = "SELECT * FROM `user` ORDER BY `bike_km` DESC";
												    $topres = mysqli_query($db, $topsql);
												    $count = 0;
												    while ($toprow = mysqli_fetch_assoc($topres))
												    {
												        if($count > 9)
												            break;
												        echo "<li><strong>" . $toprow['username'] .  "</strong>: " . $toprow['bike_km'] . "</li>";
												        $count++;
												    }
												    while ($count < 10)
												    {
												        echo "<li> - </li>";
												        $count++;
												    }
                                                ?>
											</ol>
										</address>
									</div>
								</div>

								<div class="col-md-3">
									<div class="space20">
									</div>
									<h3 class="form-section">Top 10 Using Time:</h3>
									<div class="well">
									
										<address>
											<ol>
												<?php 
												    $topsql = "SELECT * FROM `user` ORDER BY `bike_using_time` DESC";
												    $topres = mysqli_query($db, $topsql);
												    $count = 0;
												    while ($toprow = mysqli_fetch_assoc($topres))
												    {
												        if($count > 9)
												            break;
												        echo "<li><strong>" . $toprow['username'] .  "</strong>: " . $toprow['bike_using_time'] . "</li>";
												        $count++;
												    }
												    while ($count < 10)
												    {
												        echo "<li> - </li>";
												        $count++;
												    }
                                                ?>
											</ol>
										</address>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAgSrYhr1zDnhNG5YonEuXrOWHyIVTYxxs&callback=initMap">
</script>


<div class="page-footer">
	<div class="container">
		 
	</div>
</div>
<div class="scroll-to-top">
	<i class="icon-arrow-up"></i>
</div>

<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>

<script src="assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>

<script src="http://maps.google.com/maps/api/js?sensor=true" type="text/javascript"></script>
<script src="assets/global/plugins/gmaps/gmaps.min.js" type="text/javascript"></script>
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout3/scripts/layout.js" type="text/javascript"></script>
<script src="assets/admin/layout3/scripts/demo.js" type="text/javascript"></script>
<script src="assets/admin/pages/scripts/contact-us.js"></script>
<script>
jQuery(document).ready(function() {    
   Metronic.init(); // init metronic core components
Layout.init(); // init current layout
Demo.init(); // init demo features
   ContactUs.init();
});
</script>
</body>
</html>