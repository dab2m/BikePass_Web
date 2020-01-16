<?php 
    include 'db.php';
    session_start();
    if(!isset($_SESSION['username']))
        header("location:login.php");
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
		<div class="container">
			<!-- BEGIN PAGE TITLE -->
			<div class="page-title">
				<h1>Anasayfa</h1>
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
								<div id="map" class="gmaps margin-bottom-40" style="height:400px;">
								</div>
							</div>
							<div class="row margin-bottom-20">
								<div class="col-md-6">
									<div class="space20">
									</div>
									<h3 class="form-section">Profil</h3>
									<div class="well">
									
										<address>
										<strong>Kullanici Adi:</strong>
										<p><?php echo $row['username'] ?></p>
										</address>
										
										<address>
										<strong>E-Mail:</strong><br>
										<a href="mailto:#">
										<?php echo $row['email']; ?> </a>
										</address>
										
										<address>
										<strong>Mesafe (Km):</strong><br>
										<p> <?php echo $row['bike_km']; ?> </p>
										</address>
									</div>
								</div>

								<div class="col-md-6">
									<div class="space20">
									</div>
									<h3 class="form-section">Kart Bilgileri</h3>
									<div class="well">
									
										<address>
										<strong>Kart Numarasi:</strong>
										<p><?php echo $row['card_num'] ?></p>
										</address>
										
										<address>
										<strong>Kart CCV:</strong><br>
										<a href="mailto:#">
										<?php echo $row['card_ccv']; ?> </a>
										</address>
										
										<address>
										<strong>Kart Son Kullanma Tarihi:</strong><br>
										<p> <?php echo $row['card_date']; ?> </p>
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