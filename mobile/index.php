<!DOCTYPE HTML>
<html>
<?php
		session_start();
		$wp_root = '../../../..';
		require_once($wp_root.'/wp-load.php');
		if($_SESSION["redirect"] == ""){
			$_SESSION["redirect"]=get_bloginfo('url').$_SERVER['REQUEST_URI'];
		}
?>		
<head>
<meta name="viewport" content="initial-scale=1.0; maximum-scale=1.0;">
<meta charset="utf-8">
<title><?php echo get_bloginfo('name');?></title>
<link href="css/style.css" rel="stylesheet" type="text/css"  />
<link rel="apple-touch-icon-precomposed" href="images/calendar.png" />
<link rel="icon" type="image/png" href="images/calendar.png">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=1.3"></script>
<script type="text/javascript" src="http://dev.baidu.com/wiki/static/map/API/examples/script/convertor.js"></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&key=AIzaSyAKfssbZkAgWUXcIPiA24fhJ73b69065RM"></script>
</head>
<?php
	if(!is_user_logged_in()){
			echo '<script language="javascript">';
			echo 'location="'.get_bloginfo('url').'/wp-login.php?action=login&redirect_to='.$_SESSION["redirect"].'";';
			echo '</script>';			
			die();
		}else{
			$user = wp_get_current_user();
			$_SESSION['user'] = $user -> user_login;
		}
		
		global $table_prefix;
		$table_prefix = ( isset( $table_prefix ) ) ? $table_prefix : $wpdb->prefix;

		define('WP_LIVE_CALENDAR_TABLE', $table_prefix . 'live_calendar');
		define('WP_LIVE_CALENDAR_CONFIG_TABLE',  $table_prefix . 'live_calendar_config');
		define('WP_LIVE_CALENDAR_CATEGORIES_TABLE',  $table_prefix . 'live_calendar_categories');
		define('WP_LIVE_CALENDAR_LOCATIONS_TABLE',  $table_prefix . 'live_calendar_locations');
	
		global $wpdb;	
?>
<body>
<?php
	if($_REQUEST['action'] == "view"){
		if($_REQUEST['event_id'] != ""){
			$data = $wpdb->get_results("SELECT * FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE event_id='" . mysql_escape_string($_REQUEST['event_id']) . "' LIMIT 1");
			$event = $data[0];
?>
    <div class="header">
        <button id="menu_left">Edit <span>+</span> <span style="display:none;">-</span></button>
        <button id="menu_right" onclick="location.href='?action=list'">Back</button>
        <div class="clear"></div>
    </div>
    <div class="nav_left">
        <ul>
            <li><a href="?action=edit&do=edit&event_id=<?php echo $event->event_id; ?>">Edit</a></li>
            <li><a href="?action=edit&do=delete&event_id=<?php echo $event->event_id;?>" onclick="{if(confirm('Are you sure to delete it ?')){return true;}else{return false;}}">Delete</a></li>
        </ul>
    </div>
    <div class="content">
       <div class="calendar">
            <h1><?php echo $event->event_title; ?></h1>
            <div class="calendar-content">
            	<p>
            		<div class="time">
            		<?php            			
            			if($event->event_date_end == $event->event_date_begin){
            				echo date("d M, Y",strtotime($event->event_date_end));
            				if($event->event_time_end == $event->event_time_begin){
            					echo  "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . date("H:i",strtotime($event->event_time_begin));
            				}else{
            					echo  "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . date("H:i",strtotime($event->event_time_begin)) . "&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;" . date("H:i",strtotime($event->event_time_end));
            				}
            			}else{
            				echo date("d M, Y",strtotime($event->event_date_begin)) . "&nbsp;&nbsp;" . date("H:i",strtotime($event->event_time_begin)) ."&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;" . date("d M, Y",strtotime($event->event_date_end)). "&nbsp;&nbsp;" . date("H:i",strtotime($event->event_time_end)) ;
            			}
            			echo  "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $event->event_location_name;
								?>
								</div>
							 </p>							
            	<p><?php echo $event->event_desc; ?></p>
<?php 
					if(($event->event_location_longitude != "") && ($event->event_location_latitude != "")){
?>
            	<div style="width:100%;height:200px;border:1px solid gray" id="map"></div>
            	<script type="text/javascript">
            			translateCallback = function (point){
    									map.clearOverlays();
    									var marker = new BMap.Marker(point);
    									map.addOverlay(marker);
    									map.setCenter(point);
									}	
									var map = new BMap.Map("map"); 
									var point = new BMap.Point(<?php echo $event->event_location_longitude;?>,<?php echo $event->event_location_latitude;?>);
									map.centerAndZoom(point,15);  									
									var gpsPoint = new BMap.Point(<?php echo $event->event_location_longitude;?>,<?php echo $event->event_location_latitude;?>);
									BMap.Convertor.translate(gpsPoint,0,translateCallback);								
							</script>
            </div>
<?php
					}
?>
       </div>
    </div> 
<?php
		}else if($_REQUEST['type'] == "map"){
				$sql = 'SELECT * FROM ' . WP_LIVE_CALENDAR_TABLE . ' where event_location_longitude !="" and event_location_latitude !="" ORDER BY event_date_begin DESC, event_time_begin DESC, event_id DESC limit 0,30' ;	
				$events = $wpdb->get_results($sql);	
			if (!empty($events)){			
?>
		<div class="header">
			 	<button id="menu_left">Last 30</button>
        <button id="menu_right" onclick="location.href='?action=list'">Back</button>
        <div class="clear"></div>
    </div>
    <div class="content">
      <div class="calendar">
        <div class="calendar-content">
				<div style="width:100%;height:308px;border:1px solid gray" id="map"></div>
<?php 
					if($_REQUEST['map'] == "baidu"){
?>
      	<script type="text/javascript">
      		translateCallback = function (gpsPoint){
    					var marker = new BMap.Marker(gpsPoint);
  						map.addOverlay(marker);
    					map.setCenter(gpsPoint);
    					map.centerAndZoom(gpsPoint, 13);
					}	
      		var map = new BMap.Map("map");					
        	function addMarker(gpsPoint){
        			BMap.Convertor.translate(gpsPoint,0,translateCallback);		
					}
<?php
					foreach ( $events as $event ){
?>
  					gpsPoint = new BMap.Point(<?php echo $event->event_location_longitude;?>,<?php echo $event->event_location_latitude;?>);	
  					addMarker(gpsPoint);
<?php
					}								
?>					
				</script>
<?
			}else{
?>
<script type="text/javascript">
    var myOptions = {
      zoom: 12,
      center: new google.maps.LatLng(<?php echo $events[count($events)-1]->event_location_latitude;?>,<?php echo $events[count($events)-1]->event_location_longitude;?>),
      disableDefaultUI: true,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
    };
    var map = new google.maps.Map(document.getElementById("map"),myOptions);
<?php
		$count=0;
		foreach ( $events as $event ){
?>
    var beachMarker_<?php echo $count++;?> = new google.maps.Marker({
      position:  new google.maps.LatLng(<?php echo $event->event_location_latitude;?>,<?php echo $event->event_location_longitude;?>),
      map: map,
  });
<?php
		}								
?>
</script>
<?
			}
?>
			</div>
		</div>
	</div>
<?php
			}
		}
	}else if($_REQUEST['action'] == "edit"){
		if(isset($_REQUEST['do']) && ($_REQUEST['do'] == "delete")){
				$sql = "delete FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE  user = '".$_SESSION['user']."' AND event_id='" .$_REQUEST['event_id']."'";
	    	$result = $wpdb->get_results($sql);
	    	echo '<script language="javascript">';
				echo 'location="?action=list";';
				echo '</script>';			
				die();
		}
		$event_id = !empty($_REQUEST['event_id']) ? $_REQUEST['event_id'] : '';
		if(isset($_REQUEST['do']) && ($_REQUEST['do'] == "save")){
				$title = !empty($_REQUEST['event_title']) ? $_REQUEST['event_title'] : '';
				$category = !empty($_REQUEST['event_category']) ? $_REQUEST['event_category'] : '';
	  		$status = !empty($_REQUEST['event_status']) ? $_REQUEST['event_status'] : '';
				$location_name = !empty($_REQUEST['event_location_name']) ? $_REQUEST['event_location_name'] :"";
				$location_longitude = !empty($_REQUEST['event_location_longitude']) ? $_REQUEST['event_location_longitude'] : "";
				$location_latitude = !empty($_REQUEST['event_location_latitude']) ? $_REQUEST['event_location_latitude'] : "";
	  		$desc = !empty($_REQUEST['event_desc']) ? $_REQUEST['event_desc'] : '';
	  		$date_begin = !empty($_REQUEST['event_date_begin']) ? $_REQUEST['event_date_begin'] : '';
	  		$date_end = !empty($_REQUEST['event_date_end']) ? $_REQUEST['event_date_end'] : '';
	  		$time_begin = !empty($_REQUEST['event_time_begin']) ? $_REQUEST['event_time_begin'] : '';
	  		$time_end = !empty($_REQUEST['event_time_end']) ? $_REQUEST['event_time_end'] : '';
	  		$default_link =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE  user = '".$_SESSION['user']."' AND config_item='default_link'"));
    		$linky = !empty($_REQUEST['event_link']) ? $_REQUEST['event_link'] : $default_link;
				if($title != ""){
					if($event_id != ""){
						$sql = "UPDATE " . WP_LIVE_CALENDAR_TABLE . " SET event_title='" . $title
		             		. "',event_location_name='" .$location_name. "',event_location_longitude='" .$location_longitude. "',event_location_latitude='" .$location_latitude. "', event_desc='" .$desc . "', event_date_begin='" . $date_begin
                  	. "', event_date_end='" . $date_end . "', event_time_begin='" . $time_begin . "', event_time_end='" . $time_end . "', event_category=".$category.", event_status='".$status."', event_link='".$linky."' WHERE event_id='" .$event_id . "'";
			      $wpdb->get_results($sql);
					}else{
						$sql = "INSERT INTO " . WP_LIVE_CALENDAR_TABLE . " SET user = '".$_SESSION['user']."', event_title='" .$title. "',event_location_name='" .$location_name. "',event_location_longitude='" .$location_longitude. "',event_location_latitude='" .$location_latitude. "',event_desc='" .$desc. "', event_date_begin='" . $date_begin. "', event_date_end='" .$date_end. "', event_time_begin='" .$time_begin . "', event_time_end='" .$time_end . "', event_category='".$category."', event_status='".$status."', event_link='".$linky."'";	     
	    			$wpdb->get_results($sql);		
					}
				}
				echo '<script language="javascript">';
				echo 'location="?action=list";';
				echo '</script>';			
				die();
		}
		if(isset($_REQUEST['do']) && ($_REQUEST['do'] == "edit") && ($event_id != "")){			     
			  $data = $wpdb->get_results("SELECT * FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE event_id='" . $event_id . "' LIMIT 1");
				$data = $data[0];	
		}
?> 	<div class="header">
        <button id="menu_left" onclick="document.getElementById('calenarform').submit();">Save</button>
        <button id="menu_right" onclick="location.href='?action=list'">Back</button>
        <div class="clear"></div>
    </div>
    <div class="content">
       <div class="calendar">
					<form name="calenarform" id="calenarform" class="wrap" method="post" action="?action=edit&do=save">
					<input type="hidden" name="event_id" value="<?php echo stripslashes($event_id); ?>">
					<input type="text" name="event_title" class="input" size="20" maxlength="20" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_title)); ?>" /></td>
					<textarea name="event_desc" class="input" rows="5" cols="20"><?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_desc)); ?></textarea></td>
					<table><tr><td><select name="event_category">
					<?php
							$sql = "SELECT * FROM " . WP_LIVE_CALENDAR_CATEGORIES_TABLE." WHERE user = '".$_SESSION['user']."'";
	            $cats = $wpdb->get_results($sql);
              foreach($cats as $cat){
						     echo '<option value="'.stripslashes($cat->category_id).'"';
                 if (!empty($data)){
							 			if ($data->event_category == $cat->category_id){
							     			echo 'selected="selected"';
							   		}
						     } 
						  	 echo '>'.stripslashes($cat->category_name).'</option>';
						  }
        	?>
       	</select></td><td>&nbsp;</td><td align="right">      
					<select name="event_status">
					<?php
							if($data->event_status == 'private'){
									echo '<option value="public" >'.__('public','live-calendar').'</option>';		
									echo '<option value="private" selected = "selected" >'.__('private','live-calendar').'</option>';		
							}else{
									echo '<option value="public" selected = "selected" >'.__('public','live-calendar').'</option>';		
									echo '<option value="private" >'.__('private','live-calendar').'</option>';															
							}
          ?>
        </select></td></tr></table> 
        	<input type="text" id="event_location_name" name="event_location_name" class="input"  value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_location_name)); ?>" />
        	<input type="text" id="event_location_longitude" name="event_location_longitude" class="input" size="20" maxlength="15" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_location_longitude )); ?>" />
        	<input type="text" id="event_location_latitude" name="event_location_latitude" class="input" size="20" maxlength="15" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_location_latitude)); ?>" />
<?php
					if($_REQUEST['do'] == ""){
?>	
       		<script type="text/javascript">
									navigator.geolocation.getCurrentPosition(showMap);									
									function showMap(position) {
										latitude = position.coords.latitude;
										longitude = position.coords.longitude;
										document.getElementById("event_location_latitude").innerHTML = latitude;
										document.getElementById("event_location_longitude").innerHTML = longitude;
									}
								
									var watchId = navigator.geolocation.watchPosition(scrollMap,handleError);									
									function scrollMap(position) {
										latitude = position.coords.latitude;
										longitude = position.coords.longitude;
										document.getElementById("event_location_latitude").value = latitude;
										document.getElementById("event_location_longitude").value = longitude;
									}		
							
									function handleError(error) {
										document.getElementById("event_location_latitude").innerHTML = error;
										document.getElementById("event_location_longitude").innerHTML= error;
									}
									
					</script>
<?php
				}
?>
       		<table><tr><td>
       		<input type="text" name="event_date_begin" size="12" value="<?php 
											if ( !empty($data) ) {
														echo htmlspecialchars(stripslashes($data->event_date_begin));
											}
											else{
														echo date("Y-m-d",ctwo());
											} 
									?>" 
					/> 
					</td><td>&nbsp;</td><td>
					<input type="text" name="event_time_begin" value="<?php 
							if ( !empty($data) ) {
									echo date("H:i",strtotime(htmlspecialchars(stripslashes($data->event_time_begin))));
							}else{
									echo date("H:i",ctwo());
							}
						?>" 
					/>
					</td></tr><tr><td>
					<input type="text" name="event_date_end" size="12" value="<?php 
								if ( !empty($data) ) {
										echo htmlspecialchars(stripslashes($data->event_date_end));
								}
								else{
										echo date("Y-m-d",ctwo());
								}
						?>" 
					/>
					</td><td>&nbsp;</td><td>
					<input type="text" name="event_time_end" value="<?php 
							if ( !empty($data) ) {
											echo date("H:i",strtotime(htmlspecialchars(stripslashes($data->event_time_end))));
							}else{
									echo date("H:i",ctwo());
							}
						?>" 
					/></td></tr></table>	
			</form>
    </div>
   </div>
<?php
	}else{
?>
		<div class="header">
        <button id="menu_left">Map <span>+</span> <span style="display:none;">-</span></button>
        <button id="menu_right">Menu <span>+</span> <span style="display:none;">-</span></button>
        <div class="clear"></div>
    </div>
		<div class="nav_left">
        <ul>
            <li><a href="?action=view&type=map&map=google">Google Map</a></li>
            <li><a href="?action=view&type=map&map=baidu">Baidu Map</a></li>
        </ul>
    </div>
    <div class="nav_right">
        <ul>
            <li><a href="?action=edit">Add New</a></li>
            <li><?php wp_loginout($_SESSION["redirect"]);?></li>
        </ul>
    </div>
    <div class="content">
<?php
		$per_page = 9;
		if(isset($_REQUEST['page']) && $_REQUEST['page'] != ""){
			$current = $_REQUEST['page'];
			$start = ($current -1)* $per_page;
		}else{
			$current = 1;
			$start = 0;
		}
		$sql = "SELECT count(event_id) as total FROM " . WP_LIVE_CALENDAR_TABLE;	
		$data = $wpdb->get_results($sql);	
		$total= $data[0]->total;
		$max_page = ceil($total / $per_page);		
		
		$sql = "SELECT * FROM " . WP_LIVE_CALENDAR_TABLE . " ORDER BY event_date_begin DESC, event_time_begin DESC, event_id DESC LIMIT " . $start . "," .$per_page;	
		$events = $wpdb->get_results($sql);	
		if (!empty($events)){				
			if($current < $max_page){
				$next = $current + 1;
			}else{
				$next = $current;
			}
			
			if($current > 1){
				$pre = $current - 1;
			}else{
				$pre = $current;
			}
			
			foreach ( $events as $event ){
?>
        <div class="calendar">
             <div class="event"><a href="?action=view&event_id=<?php echo $event->event_id; ?>"><?php echo $event->event_title; ?></a></div>
        </div>
<?php
			}
?>
			<div class="pagination">
          <table><tr>
              <td><li><a href="?action=list&page=<?php echo $pre; ?>"><img src="images/right-arrow.png" alt=""></a></li></td>
              <td><li><a href="?action=list&page=1">1</a></li></td>        
              <td><li class="current"><a href="#"><?php echo $current; ?></a></li></td>
              <td><li><a href="?action=list&page=<?php echo $max_page; ?>"><?php echo $max_page; ?></a></li></td>
              <td><li><a href="?action=list&page=<?php echo $next; ?>"><img src="images/left-arrow.png" alt=""></a></li></td>
           </tr></table>
        </div>
       </div>
<?php
		}
	} 
?>
 
<div class="footer"></div>
<script type="text/javascript">
	$('.nav_right').hide();
	$('#menu_right').click(function (){
		$(".nav_right").toggle();
		$('#menu_right > span').toggle();
	});
	
	$('.nav_left').hide();
	$('#menu_left').click(function (){
		$('.nav_left').toggle();
		$('#menu_left > span').toggle();
	});
</script>
</body>
</html>