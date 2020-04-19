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
								<?php 
    								$bikequery = "SELECT * FROM `bikes` WHERE status < 2";
    								$bikeres = mysqli_query($db, $bikequery);
    								$lat = "";
    								$lng = "";
    								$icons = "";
    								$count = 0;
                                    while($bikerow = mysqli_fetch_assoc($bikeres))
                                    {
                                        $lat = $lat . "" . $bikerow['lat'] . ",";
                                        $lng = $lng . "" . $bikerow['lng'] . ",";
                                        if($bikerow['status'] == 0)
                                            $icons = $icons . "http://maps.google.com/mapfiles/ms/icons/red-dot.png,";
                                        elseif($bikerow['status'] == 1)
                                            $icons = $icons . "http://maps.google.com/mapfiles/ms/icons/blue-dot.png,";
                                        /*
										else //status = 2, so bike is busy or greather than 2
											$icons = "";
										*/
                                        $count++;
                                    }
                                    

                                    $lat = substr($lat, 0, -1);
                                    $lng = substr($lng, 0, -1);
                                    $icons = substr($icons, 0, -1);
                                    
                                    $hotpointquery = "SELECT * FROM hotpoints";
                                    $hotpointres = mysqli_query($db, $hotpointquery);
                                    $hotlat = "";
                                    $hotlng = "";
                                    $colors = "";
                                    $radius = "";
                                    $hotcount = mysqli_affected_rows($db);
                                    while ($hotrow = mysqli_fetch_assoc($hotpointres))
                                    {
                                        $hotlat = $hotlat . "" . $hotrow['lat'] . ",";
                                        $hotlng = $hotlng . "" . $hotrow['lng'] . ",";
                                        $radius = $radius . "" . $hotrow['radius'] . ",";
                                        if ($hotrow['frequency'] < 10)
                                            $colors = $colors . "#EDA895,";
										if ($hotrow['frequency'] > 9 && $hotrow['frequency'] < 20)
                                            $colors = $colors . "#DE3F24,";
										if ($hotrow['frequency'] > 19 && $hotrow['frequency'] < 30)
                                            $colors = $colors . "#D51F06,";
										if ($hotrow['frequency'] > 29 && $hotrow['frequency'] < 40)
                                            $colors = $colors . "#A91401,";
										if ($hotrow['frequency'] > 39)
                                            $colors = $colors . "#820C02,";
                                    }
                                    
                                    $hotlat = substr($hotlat, 0, -1);
                                    $hotlng = substr($hotlng, 0, -1);
                                    $colors = substr($colors, 0, -1);
                                    $radius = substr($radius, 0, -1);
								?>
								<script>

                                    function initMap() {

                                    	  var lat = '<?php echo $lat; ?>'
										  lat = lat.split(",")
										  var lng = '<?php echo $lng; ?>'
										  lng = lng.split(",")
										  var icons = '<?php echo $icons; ?>'

										  icons = icons.split(",")
										  var count = '<?php echo $count; ?>'
										  var hotPointCount = '<?php echo $hotcount; ?>'  
										  count = parseInt(count)
										  hotPointCount = parseInt(hotPointCount)
										  if(count != 0)
										  {  
    										  var location = {lat: parseFloat(lat[0]), lng: parseFloat(lng[0])};
                                        	  var map = new google.maps.Map(document.getElementById("map"), {zoom: 4, center: location});
    										  var geolocation = 'Something'

        										 if (navigator.geolocation) {
        											
      											    navigator.geolocation.getCurrentPosition(function(position) {
      											      var pos = {
      											        lat: position.coords.latitude,
      											        lng: position.coords.longitude
      											      };
      											      
      												  geolocation = new google.maps.Marker({position: pos, map: map, title: 'Your Location', icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'});
      												  map.setCenter({lat:position.coords.latitude, lng:position.coords.longitude });
      												  map.setZoom(11);
      											    });
      											  
      											 }
   											 


                                        	  for(var i = 0 ; i < count ; i++)
                                        	  {
                                            	location = {lat: parseFloat(lat[i]), lng: parseFloat(lng[i])};
                                        	  	let marker = new google.maps.Marker({position: location, map: map, title: 'Bike' + i, icon: icons[i]});
    
                                        	  	
                                          	  	google.maps.event.addListener(marker, 'click', function() {
                                              	  	
                                              	  	if(marker.icon == 'http://maps.google.com/mapfiles/ms/icons/red-dot.png')
                                              	  		alert('Status: On maintenance...')
                                              	  	else if(marker.icon == 'http://maps.google.com/mapfiles/ms/icons/green-dot.png')
                                                  	  	alert('Your Location')
                                              	  	else
                                              	  		alert('Status: Working...')
													

                                      	  		});
                                        	  }


                                        	  var hotlat = '<?php echo $hotlat; ?>'
                                        	  hotlat = hotlat.split(",")
    										  var hotlng = '<?php echo $hotlng; ?>'
    										  hotlng = hotlng.split(",")
    										  var colors = '<?php echo $colors; ?>'
    										  colors = colors.split(",")
    										  var radius = '<?php echo $radius; ?>'
    										  radius = radius.split(",")
                                        	  
                                        	  if (hotPointCount != 0)
                                        	  {
                                            	  
                                            	  for(var i = 0 ; i < hotPointCount ; i++)
                                            	  {
                                                	  
                                            		  	var hotloc = {lat: parseFloat(hotlat[i]), lng: parseFloat(hotlng[i])};
                                            		  	let marker = new google.maps.Marker({position: hotloc, map: map, icon: 'http://maps.google.com/mapfiles/ms/icons/black-dot.png'});
                                                  	    var sunCircle = {
                                                	            strokeColor: "#c3fc49",
                                                	            strokeOpacity: 0.8,
                                                	            strokeWeight: 2,
                                                	            fillColor: colors[i],
                                                	            fillOpacity: 0.35,
                                                	            map: map,
                                                	            center: hotloc,
                                                	            radius: parseInt(radius[i]) * 1000// in meters
                                                	        };
                                                	        cityCircle = new google.maps.Circle(sunCircle)
                                                	        cityCircle.bindTo('center', marker, 'position');

                                                	}
                                            	}
                                    		}
										  else
										  {
    										  var location = {lat: 36.234, lng: 38.123};
                                        	  var map = new google.maps.Map(document.getElementById("map"), {zoom: 4, center: location});
                                        	  alert('No bikes detected...')
										  }

										  /* For finding geolocation */

                                      	}
                                  		  
                                </script>
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="space20">
									</div>
									<h3 class="form-section">Profile</h3>
									<div class="well">
										<address>
										<strong>User Name:</strong><button type="button" class="btn blue" style="float:right;" onclick="location.href='edit_profile.php';">Edit</button>
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
										
										<address>
										<strong>Total Credit:</strong><br>
										<p> <?php echo $row['total_credit']; ?> </p>
										</address>
										
										<address>
										<button type="button" class="btn green" data-toggle='modal' data-target='#messageAllModal'>All Messages</button>
										<button type="button" class="btn red" data-toggle='modal' data-target='#messageUnreadModal'>Unread Messages</button>
										</address>
										
									</div>
								</div>

								<div class="col-md-4">
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

								<div class="col-md-4">
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
							<div class="row">
								<div class="col-md-12">
									<h3 class="form-section">Users:</h3>
									<ol>
										<?php 
										    $topsql = "SELECT * FROM `user` ORDER BY `bike_using_time` DESC";
										    $topres = mysqli_query($db, $topsql);
										    while ($toprow = mysqli_fetch_assoc($topres))
										    {
										        if($toprow['username'] != $_SESSION['username']){
										          echo "<div  class='col-md-3' style='margin-bottom: 5px;'><li><strong>" . $toprow['username'] .  "</strong>
                                                        <button type='button' class='btn green' data-toggle='modal' data-target='#messageModal' data-whatever=".$toprow['username']."
                                                        data-id='".$toprow['user_id']."' data-sendid='".$_SESSION['id']."' style='float:right;font-size: 10px;'>Send Message</button></li></div>";
										        }
										    }
                                        ?>
									</ol>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Modal Send Message-->
    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="messageModalLabel">New message</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form>
              <div class="form-group">
                <label for="recipient-name" class="col-form-label">Head:</label>
                <input type="text" class="form-control" id="head">
              </div>
              <div class="form-group">
                <label for="message-text" class="col-form-label">Message:</label>
                <textarea class="form-control" id="message-text"></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="send-massege">Send message</button>
            <input type="hidden" class="user-id" name="userId">
            <input type="hidden" class="send-id" name="sendId">
          </div>
        </div>
      </div>
    </div>
    <!-- Modal All Message-->
    <div class="modal fade" id="messageAllModal" tabindex="-1" role="dialog" aria-labelledby="messageAllModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="messageAllModalLabel">All Messages</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form>
              	<?php 
				    $topsql = "SELECT m.*,u.username FROM messages m,user u WHERE u.user_id = m.from_user AND m.to_user = ".$_SESSION['id']." ORDER BY m.id DESC ";
				    $topres = mysqli_query($db, $topsql);
				    while ($toprow = mysqli_fetch_assoc($topres))
				    {
				        echo "<div class='form-group'>";
				        echo "<label for='recipient-name' class='col-form-label'><strong>From: </strong>".$toprow['username']."</label>";
				        echo "<p><strong>Head: </strong>".$toprow['head']."</p>";
				        echo "<p><strong>Body: </strong>".$toprow['body']."</p>";
				        echo "</div>";
				    }
                ?>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Unread Message-->
    <div class="modal fade" id="messageUnreadModal" tabindex="-1" role="dialog" aria-labelledby="messageUnreadModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="messageUnreadModalLabel">Unread Messages</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form>
              	<?php 
				    $topsql = "SELECT m.*,u.username FROM messages m,user u WHERE u.user_id = m.from_user AND unread = 0 AND m.to_user = ".$_SESSION['id']." ORDER BY m.id DESC ";
				    $topres = mysqli_query($db, $topsql);
				    if($topres->num_rows > 0){
    				    while ($toprow = mysqli_fetch_assoc($topres))
    				    {
    				        echo "<div class='form-group'>";
    				        echo "<label for='recipient-name' class='col-form-label'><strong>From: </strong>".$toprow['username']."</label>";
    				        echo "<p><strong>Head: </strong>".$toprow['head']."</p>";
    				        echo "<p><strong>Body: </strong>".$toprow['body']."</p>";
    				        echo "</div>";
    				        $sql = "UPDATE messages SET unread = 1 WHERE id = '".$toprow['id']."'";
    				        mysqli_query($db, $sql);
    				    }
				    }
				    else{
				        echo "<div class='form-group'>";
				        echo "<label for='recipient-name' class='col-form-label'>There are no unread messages</label>";
				        echo "</div>";
				    }
                ?>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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

$('#messageModal').on('show.bs.modal', function (event) {
  	var button = $(event.relatedTarget);
  	var recipient = button.data('whatever');
  	var id = button.data('id'); 
  	var sendId = button.data('sendid');   
	var modal = $(this);
	modal.find('.modal-title').text('New message to ' + recipient);
	$('.user-id').val(id);
	$('.send-id').val(sendId);
});

$(document).on('click', '#send-massege', function(e){
    e.preventDefault();
    var id = $('.user-id').val();
    var sendId = $('.send-id').val();
    var message = $('#message-text').val();
    var head = $('#head').val();
    $.ajax({
        type: 'POST',
        url: 'send_message.php',
        data: {id:id,sendId:sendId,message:message,head:head},
        dataType: 'json',
        success: function(response){
        	if(response == 'ok'){
        		alert('Message send succesfully');
        	}
        	else{
        		alert('Message send failed');
        	}
        	location.reload();
        }
    });
});

$('#messageUnreadModal').on('hidden.bs.modal', function () {
	location.reload();
});
</script>
</body>
</html>