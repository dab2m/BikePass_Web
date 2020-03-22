<?php 
    include 'db.php';
    session_start();
    if(!isset($_SESSION['username']))
        header("location:index.php");
    $query = "SELECT * FROM `user` WHERE `user_id` = " . $_SESSION['id'];
    $res = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($res);
    
    
    if (isset($_POST['username'])) {
        $answer = $_POST['answer'];
        
        if ($answer == $_SESSION['answer'])
            header("location:changepassword.php");
    }
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
				<h1>Security Question</h1>
			</div>
			
		</div>
	</div>

	<div class="page-content">
	
		<div class="container">

			
			<div class="portlet light">
			<div class="portlet box blue-hoki">
				<div class="portlet-title">
					<div class="caption">
						<i class="fa fa-gift"></i>Forgot Password ? 
					</div>
				</div>
				<div class="portlet-body form">
					<!-- BEGIN FORM-->
					<form action="forgotpassword.php" class="form-horizontal" name="signup" method="post">

						<div class="form-body">
							<div class="form-group">
								<label class="col-md-3 control-label">Username</label>
								<div class="col-md-4">
									<input readonly type="text" class="form-control" placeholder="Enter text" value="<?php echo $row['username']; ?>" name="username">
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Email Address</label>
								<div class="col-md-4">
									<div class="input-group">
										<span class="input-group-addon">
										<i class="fa fa-envelope"></i>
										</span>
										<input readonly type="email" class="form-control" placeholder="Email Address" value="<?php echo $row['email']; ?>" name="email">
									</div>
								</div>
							</div>			
							<!-- 				
							<div class="form-group">
								<label class="col-md-3 control-label">Security Question</label>
								<div class="col-md-4">
									<div class="input-group">
										<input readonly type="text" class="form-control" placeholder="Answer" value="<?php echo $row['question']; ?>" name="question">
										<span class="input-group-addon">
										<i class="fa fa-question"></i>
										</span>
									</div>
								</div>
							</div>
							 -->
							<div class="form-group">
								<label class="col-md-3 control-label">Security Answer</label>
								<div class="col-md-4">
									<div class="input-group">
										<input type="password" class="form-control" placeholder="<?php echo $row['question'] . "?"; ?>" value="" name="answer">
										<span class="input-group-addon">
										<i class="fa fa-key"></i>
										</span>
									</div>
								</div>
							</div>
							
						</div>
						<div class="form-actions top">
							<div class="row">
								<div class="col-md-offset-3 col-md-9">
									<button type="submit" class="btn blue">Submit</button>
									<button type="button" class="btn default" onclick="location.href='main.php';">Cancel</button>
								</div>
							</div>
						</div>
					</form>
					<!-- END FORM-->
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