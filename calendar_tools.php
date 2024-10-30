<?php
function live_calendar_tools(){
  global $wpdb;
  multi_user_update();
	
	$pv_menu = "
							<a href=\"admin.php?page=live-calendar-tools&pv_page=export\">".__('Export', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"admin.php?page=live-calendar-tools&pv_page=import\">".__('Import', 'post-views')."</a> \r\n
	            ";

	echo $pv_menu; 
	
	if($_POST['action'] == "export"){
			export_calendar($_GET['export-user']);
?>
<p><a href="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/live-calendar/from-live-calendar.ics";?>"> Click here to download the ics file if not automated download.</a></p>
		<script language= "javascript"> window.location.href="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/live-calendar/from-live-calendar.ics";?>"</script>
<?php
			die();
	}	
	
	if($_GET['pv_page'] == "import"){
			echo "<p> not released in this version.</p>";
	}else{
?>
<p>
<form name="userform" id="userform" class="wrap" method="post" action="<?php echo bloginfo('wpurl'); ?>/wp-admin/admin.php?page=live-calendar-tools&pv_page=export">
	<label for="live-calendar"><?php _e('Calendar to be exported:', 'live-calendar'); ?>
<?php
			if (current_user_can('level_10')){
					$sql = "select distinct user from ".WP_LIVE_CALENDAR_CONFIG_TABLE;
					$users_list = $wpdb->get_results($sql);
?>
					<select name="export-user">
						<option value="all"<?php selected('all', $user); ?>><?php _e('All Users', 'live-calendar'); ?></option>
						<?php foreach($users_list as $user_list){?>
						<option value="<?php echo $user_list->user;?>"<?php selected($user_list->user, $user); ?>><?php echo $user_list->user; ?></option>
						<?php }?>
					</select>
					
<?php
		}else{
?>
					<select name="export-user">
						<option value="<?php echo get_user(); ?>" selected><?php echo get_user(); ?></option>
					</select>
<?php
		}
?>
	</label>
	<input type="hidden" name="action" value="export">
	<input type="submit" name="save" class="button bold" value="<?php _e('Export','live-calendar'); ?> &raquo;" />
</form>
</p>
<?php
	}	
}

function export_calendar($user){

	$fp = fopen("../wp-content/plugins/live-calendar/from-live-calendar.ics", "w");
	if($fp) { 
			$flag=fwrite($fp,get_export_data($user)); 
			fclose($fp); 
	}
}

function get_timezone_offset($remote_tz, $origin_tz = null) {
    if($origin_tz === null) {
        if(!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}

function get_export_data($user){

	global $wpdb;
	$calendar = "";
	if(empty($user) || $user == "all"){
    	$user_str = ' 1=1 ';
  } else{
  		$user_str = " user ='".$user."'";
  } 

	$events = $wpdb->get_results("SELECT * FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE " .$user_str." ORDER BY event_date_begin ASC, event_time_begin ASC, event_id ASC");

  if (!empty($events)){  
  		$calendar .= "BEGIN:VCALENDAR\r\n";
			$calendar .= "PRODID:-//Live Calendar//ZIMING.ORG//EN\r\n";
			$calendar .= "VERSION:2.0\r\n";
			$calendar .= "CALSCALE:GREGORIAN\r\n";
			$calendar .= "METHOD:PUBLISH\r\n";
			$calendar .= "X-WR-CALNAME:Live Calendar\r\n";
			$calendar .= "X-WR-TIMEZONE:".get_option('timezone_string')."\r\n";
			$calendar .= "X-WR-CALDESC:Live Calendar\r\n";
			if(get_option('timezone_string') != ''){
				$offset = get_timezone_offset(get_option('timezone_string'));
			}else{
				$offset = get_option('gmt_offset') * 3600;
			}
         foreach($events as $event){
	   					$calendar .= "BEGIN:VEVENT\r\n";
	   					$calendar .= "DTSTART:".date("Ymd", strtotime($event->event_date_begin)).date("\THis\Z", strtotime($event->event_time_begin)+$offset)."\r\n";
							$calendar .= "DTEND:".date("Ymd", strtotime($event->event_date_end)).date("\THis\Z", strtotime($event->event_time_end)+$offset)."\r\n";
							$calendar .= "DTSTAMP:".date("Ymd", strtotime($event->event_date_begin)).date("\THis\Z", strtotime($event->event_time_begin)+$offset)."\r\n";
							$calendar .= "UID:Live_Calendar_Event_".$event->event_id."\r\n";
							$calendar .= "CREATED:".date("Ymd", strtotime($event->event_date_begin)).date("\THis\Z", strtotime($event->event_time_begin)+$offset)."\r\n";
							$calendar .= "DESCRIPTION:".$event->event_desc."\r\n";
							$calendar .= "LAST-MODIFIED:".date("Ymd", strtotime($event->event_date_begin)).date("\THis\Z", strtotime($event->event_time_begin)+$offset)."\r\n";
							$calendar .= "LOCATION:".$event->event_location_name."\r\n";
							$calendar .= "SEQUENCE:".$event->event_id."\r\n";
							$calendar .= "STATUS:CONFIRMED\r\n";
							$calendar .= "SUMMARY:".$event->event_title."\r\n";
							$calendar .= "TRANSP:TRANSPARENT\r\n";
							$calendar .= "END:VEVENT\r\n";
         }
    	$calendar .= "END:VCALENDAR";
	}
	return $calendar;
}

?>