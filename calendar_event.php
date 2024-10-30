<?php
function live_calendar_event_form($mode='add_save', $event_id=false){
	global $wpdb,$users_entries;
	$data = false;
	
	if ( $event_id !== false ){
			$data = $wpdb->get_results("SELECT * FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE event_id='" . mysql_escape_string($event_id) . "' LIMIT 1");
			$data = $data[0];
	}
  
  if($event_id == false ){
  	  $event_user = get_user();
  }else{
  		$event_user = $wpdb->get_var($wpdb->prepare("SELECT user FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE event_id='".mysql_escape_string($event_id)."'", APP_POST_TYPE));
  }
  $default_link =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE  user = '".$event_user."' AND config_item='default_link'", APP_POST_TYPE));
?>
  <div class="wrap" >
	<?php screen_icon('edit'); ?>
	<h2><?php if($mode == 'edit_save') echo _e('Edit Events','live-calendar'); else echo _e('Add New Event','live-calendar'); ?></h2>
  <div id="pop_up_cal" style="position:absolute;margin-left:150px;visibility:hidden;background-color:white;layer-background-color:white;z-index:1;"></div>
	<form name="quoteform" id="quoteform" class="wrap" method="post" action="<?php echo bloginfo('wpurl'); ?>/wp-admin/admin.php?page=live-calendar-events">
		<input type="hidden" name="action" value="<?php echo $mode; ?>">
		<input type="hidden" name="event_id" value="<?php echo stripslashes($event_id); ?>">
	
		<div id="linkadvanceddiv" class="postbox">
			<div style="float: left; width: 98%; clear: both;" class="inside">
        <table cellpadding="5" cellspacing="5">
        <tr>				
				<td><legend><?php _e('Event Title','live-calendar'); ?></legend></td>
				<td><input type="text" name="event_title" class="input" size="40" maxlength="30" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_title)); ?>" /></td>
        </tr>
        <tr>
				<td style="vertical-align:top;"><legend><?php _e('Event Description','live-calendar'); ?></legend></td>
				<td><textarea name="event_desc" class="input" rows="5" cols="50"><?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_desc)); ?></textarea></td>
        </tr>
        <tr>
				<td><legend><?php _e('Event Category','live-calendar'); ?></legend></td>
				<td><select name="event_category">
					  <?php
							$sql = "SELECT * FROM " . WP_LIVE_CALENDAR_CATEGORIES_TABLE." WHERE user = '".$event_user."'";
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
             </select>
        </td>
        </tr>
        <tr>
				<td><legend><?php _e('Event Status','live-calendar'); ?></legend></td>
				<td><select name="event_status">
					  <?php
							if($data->event_status == 'private'){
									echo '<option value="public" >'.__('public','live-calendar').'</option>';		
									echo '<option value="private" selected = "selected" >'.__('private','live-calendar').'</option>';		
							}else{
									echo '<option value="public" selected = "selected" >'.__('public','live-calendar').'</option>';		
									echo '<option value="private" >'.__('private','live-calendar').'</option>';						
															
							}
             ?>
             </select>
        </td>
        </tr>
        <tr>
				<td><legend><?php _e('Event Location','live-calendar'); ?></legend></td>
        <td>
<?php 
				if (empty($data)){
?>
        	<select id="definded_locations" name="definded_locations"  onchange="setLocation(this.options[this.options.selectedIndex].value)">
<?php
					$sql = "SELECT * FROM " . WP_LIVE_CALENDAR_LOCATIONS_TABLE." WHERE user = '".$event_user."' order by location_id desc";
	        $locations = $wpdb->get_results($sql);
	        echo '<option value="0" selected >'. __('New Location','live-calendar').'</option>'."\n";  
	        foreach($locations as $location){
              	echo '<option value="'.$location->location_id.'">'.$location->location_name.'</option>'."\n";              	
					}	
?>
					</select> 
<?php 
				}
?>  	  
        	<input type="text" id="event_location_name" name="event_location_name" class="input" size="40" maxlength="30" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_location_name)); ?>" />
        	Longitude : <input type="text" id="event_location_longitude" name="event_location_longitude" class="input" size="15" maxlength="15" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_location_longitude )); ?>" />
        	Latitude : <input type="text" id="event_location_latitude" name="event_location_latitude" class="input" size="15" maxlength="15" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_location_latitude)); ?>" />
					<script language="javascript">
<?php						
						$sql = "SELECT * FROM " . WP_LIVE_CALENDAR_LOCATIONS_TABLE." WHERE user = '".$event_user."'";
	          $locations = $wpdb->get_results($sql);
						
						echo "var location_name=new Array()\n";
						echo "var location_longitudes=new Array()\n";
						echo "var location_latitudes=new Array()\n";
	          if (!empty($locations)){
              	foreach($locations as $location){
              				if($location->location_name == ""){
              						$loc_name = __('No Information','live-calendar');
              				}else{
              						$loc_name = $location->location_name;
              				}
              				
              				if($location->location_longitude == ""){
              						$loc_longitude = __('No Information','live-calendar');
              				}else{
              						$loc_longitude = $location->location_longitude;
              				}
              				if($location->location_latitude == ""){
              						$loc_latitude =  __('No Information','live-calendar');
              				}else{
              						$loc_latitude = $location->location_latitude;
              				}
              				echo "location_name[".$location->location_id."] = \"".$loc_name."\" \n";
              				echo "location_longitudes[".$location->location_id."] = \"".$loc_longitude."\" \n";
              				echo "location_latitudes[".$location->location_id."] = \"".$loc_latitude."\" \n";
              	}
            }
?>
						function setLocation(location_id){
								document.getElementById("event_location_name").value=location_name[location_id]; 
    						document.getElementById("event_location_longitude").value=location_longitudes[location_id]; 
    						document.getElementById("event_location_latitude").value=location_latitudes[location_id];     				
    				
				 	} 
				</script> 
       	</td> 
 				</tr>
        <tr>
				<td><legend><?php _e('Event Link','live-calendar'); ?></legend></td>
        <td><input type="text" name="event_link" class="input" size="100" value="<?php if ( !empty($data) ){ echo htmlspecialchars(stripslashes($data->event_link));} else {echo $default_link;}  ?>" /></td>
        </tr>
        <tr>
				<td><legend><?php _e('Start Date','live-calendar'); ?></legend></td>
        <td>
        	<script type="text/javascript">
							var cal_begin = new CalendarPopup('pop_up_cal');
							cal_begin.setWeekStartDay(<?php echo get_option('start_of_week'); ?>);
							function unifydates() {
					  			document.forms['quoteform'].event_date_end.value = document.forms['quoteform'].event_date_begin.value;
							}
					</script>
					<input type="text" name="event_date_begin" class="input" size="12" value="<?php 
											if ( !empty($data) ) {
														echo htmlspecialchars(stripslashes($data->event_date_begin));
											}
											else{
														echo date("Y-m-d",ctwo());
											} 
									?>" /> 
									<a href="#" onClick="cal_begin.select(document.forms['quoteform'].event_date_begin,'event_date_begin_anchor','yyyy-MM-dd'); return false;" name="event_date_begin_anchor" id="event_date_begin_anchor"><?php _e('Select Date','live-calendar'); ?></a>
				</td>
        </tr>
        <tr>
				<td><legend><?php _e('End Date','live-calendar'); ?></legend></td>
        <td>
        	<script type="text/javascript">
								function check_and_print() {
										unifydates();
										var cal_end = new CalendarPopup('pop_up_cal');
                    cal_end.setWeekStartDay(<?php echo get_option('start_of_week'); ?>);
										var newDate = new Date();
										newDate.setFullYear(document.forms['quoteform'].event_date_begin.value.split('-')[0],document.forms['quoteform'].event_date_begin.value.split('-')[1]-1,document.forms['quoteform'].event_date_begin.value.split('-')[2]);
										newDate.setDate(newDate.getDate()-1);
                    cal_end.addDisabledDates(null, formatDate(newDate, "yyyy-MM-dd"));
                    cal_end.select(document.forms['quoteform'].event_date_end,'event_date_end_anchor','yyyy-MM-dd');
								}
           </script>
						<input type="text" name="event_date_end" class="input" size="12" value="<?php 
								if ( !empty($data) ) {
										echo htmlspecialchars(stripslashes($data->event_date_end));
								}
								else{
										echo date("Y-m-d",ctwo());
								}
						?>" />  
						<a href="#" onClick="check_and_print(); return false;" name="event_date_end_anchor" id="event_date_end_anchor"><?php _e('Select Date','live-calendar'); ?></a>
				</td>
        </tr>
        <tr>
				<td><legend><?php _e('Time (hh:mm)','live-calendar'); ?></legend></td>
				<td>
					<input type="text" name="event_time_begin" class="input" size=12 value="<?php 
							if ( !empty($data) ) {
									echo date("H:i",strtotime(htmlspecialchars(stripslashes($data->event_time_begin))));
							}else{
									echo date("H:i",ctwo());
							}
					?>" /> - 					
					<input type="text" name="event_time_end" class="input" size=12 value="<?php 
							if ( !empty($data) ) {
											echo date("H:i",strtotime(htmlspecialchars(stripslashes($data->event_time_end))));
							}else{
									echo date("H:i",ctwo());
							}
					?>" />
				<?php _e('Optional, set blank if not required.','live-calendar'); ?> <?php _e('Current time difference from GMT is ','live-calendar'); echo get_option('gmt_offset'); _e(' hour(s)','live-calendar'); ?>.
				</td>
        </tr>
        </table>
			</div>
			<div style="clear:both; height:1px;">&nbsp;</div>
		</div>
    <input type="submit" name="save" class="button bold" value="<?php _e('Save','live-calendar'); ?> &raquo;" />
	</form>
<?php
}

function live_calendar_event_edit(){
    global $wpdb;
		$edit = $create = $save = $delete = false;
		$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
		$event_id = !empty($_REQUEST['event_id']) ? $_REQUEST['event_id'] : '';
		$title = !empty($_REQUEST['event_title']) ? $_REQUEST['event_title'] : '';
		$location_name = !empty($_REQUEST['event_location_name']) ? $_REQUEST['event_location_name'] :"";
		$location_longitude = !empty($_REQUEST['event_location_longitude']) ? $_REQUEST['event_location_longitude'] : "";
		$location_latitude = !empty($_REQUEST['event_location_latitude']) ? $_REQUEST['event_location_latitude'] : "";
	  $desc = !empty($_REQUEST['event_desc']) ? $_REQUEST['event_desc'] : '';
	  $date_begin = !empty($_REQUEST['event_date_begin']) ? $_REQUEST['event_date_begin'] : '';
	  $date_end = !empty($_REQUEST['event_date_end']) ? $_REQUEST['event_date_end'] : '';
	  $time_begin = !empty($_REQUEST['event_time_begin']) ? $_REQUEST['event_time_begin'] : '';
	  $time_end = !empty($_REQUEST['event_time_end']) ? $_REQUEST['event_time_end'] : '';
	  $category = !empty($_REQUEST['event_category']) ? $_REQUEST['event_category'] : '';
	  $status = !empty($_REQUEST['event_status']) ? $_REQUEST['event_status'] : '';
    $linky = !empty($_REQUEST['event_link']) ? $_REQUEST['event_link'] : '';

		if ( $action == 'add_save' ){
						if ($time_begin == '' || $time_end == ''){
								$time_begin = '00:00';
								$time_end = '00:00';
	      		}
      			$sql = "INSERT INTO " . WP_LIVE_CALENDAR_TABLE . " SET user = '".get_user()."', event_title='" .$title. "',event_location_name='" .$location_name. "',event_location_longitude='" .$location_longitude. "',event_location_latitude='" .$location_latitude. "',event_desc='" .$desc. "', event_date_begin='" . $date_begin. "', event_date_end='" .$date_end. "', event_time_begin='" .$time_begin . "', event_time_end='" .$time_end . "', event_category='".$category."', event_status='".$status."', event_link='".$linky."'";	     
	    			$wpdb->get_results($sql);	
						
	    			$sql = "SELECT event_id FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE  user = '".get_user()."' AND event_title='" .$title . "' AND event_desc='" .$desc . "' AND event_date_begin='" .$date_begin . "' AND event_date_end='" . $date_end . "' LIMIT 1";
	    			$result = $wpdb->get_results($sql);
	
	    			if ( empty($result) || empty($result[0]->event_id) ){
?>
								<div class="error"><p><strong><?php _e('Error','live-calendar'); ?>:</strong> <?php _e('An event with the details you submitted could not be found in the database. This may indicate a problem with your database or the way in which it is configured.','live-calendar'); ?></p></div>
<?php
						}else{
?>
								<div class="updated"><p><?php _e('Event added. It will now show in your calendar.','live-calendar'); ?></p></div>
<?php
 									live_calendar_event_form('edit_save', $result[0]->event_id);
	      		}
		}elseif ( $action == 'edit_save' ){	
				if ( empty($event_id) ){
?>
						<div class="error"><p><strong><?php _e('Failure','live-calendar'); ?>:</strong> <?php _e("You can't update an event if you haven't submitted an event id",'live-calendar'); ?></p></div>
<?php		
				}else{
			        	$sql = "UPDATE " . WP_LIVE_CALENDAR_TABLE . " SET event_title='" . $title
		             . "',event_location_name='" .$location_name. "',event_location_longitude='" .$location_longitude. "',event_location_latitude='" .$location_latitude. "', event_desc='" .$desc . "', event_date_begin='" . $date_begin
                             . "', event_date_end='" . $date_end . "', event_time_begin='" . $time_begin . "', event_time_end='" . $time_end . "', event_category=".$category.", event_status='".$status."', event_link='".$linky."' WHERE event_id='" .$event_id . "'";
		             
			        	$wpdb->get_results($sql);
		          
			        	$sql = "SELECT event_id FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE  event_title='" . $title. "'"
		             . " AND event_desc='" . $desc . "' AND event_date_begin='" . $date_begin . "' AND event_date_end='" . $date_end . "' LIMIT 1";
			        	$result = $wpdb->get_results($sql);
		
								if ( empty($result)){
?>
									<div class="error"><p><strong><?php _e('Failure','live-calendar'); ?>:</strong> <?php _e('The database failed to return data to indicate the event has been updated sucessfully. This may indicate a problem with your database or the way in which it is configured.','live-calendar'); ?></p></div>
<?php
								}else{
?>
									<div class="updated"><p><?php _e('Event updated successfully','live-calendar'); ?></p></div>
<?php               live_calendar_event_form('edit_save', $event_id);
								}
					}
	  }elseif ( $action == 'delete' ){
				if ( empty($event_id) ){
?>
		<div class="error"><p><strong><?php _e('Error','live-calendar'); ?>:</strong> <?php _e("You can't delete an event if you haven't submitted an event id",'live-calendar'); ?></p></div>
<?php			
				}else{
		        $sql = "DELETE FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE  event_id='" . mysql_escape_string($event_id) . "'";
		        $wpdb->get_results($sql);
		        
		        $sql = "SELECT event_id FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE event_id='" . mysql_escape_string($event_id) . "'";
		        $result = $wpdb->get_results($sql);
		        
		        if (empty($result)){
 ?>
		        		<div class="updated"><p><?php _e('Event deleted successfully','live-calendar'); ?></p></div>
<?php
		        }else{
?>
								<div class="error"><p><strong><?php _e('Error','live-calendar'); ?>:</strong> <?php _e('Despite issuing a request to delete, the event still remains in the database. Please investigate.','live-calendar'); ?></p></div>
<?php

						}		
				}
		}			
}

function live_calendar_event_manage(){
	multi_user_update();
	if(!empty($_REQUEST['action'])){
			if($_REQUEST['action'] == 'add'){
					live_calendar_event_form();
			}else if($_REQUEST['action'] == 'add_save'){
					live_calendar_event_edit();	
					live_calendar_event_list_form();
			}else if($_REQUEST['action'] == 'edit'){
					if(isset($_REQUEST['event_id'])){
			 				live_calendar_event_form('edit_save', $_REQUEST['event_id']);
			 		}			
			}else if($_REQUEST['action'] == 'edit_save'){
			    live_calendar_event_edit();
			    live_calendar_event_list_form();
			}else if($_REQUEST['action'] == 'delete'){
			    live_calendar_event_edit();
			    live_calendar_event_list_form();
			}
	}else{
	 live_calendar_event_list_form();
	}
}
function live_calendar_event_list_form(){
	global $wpdb;
?>
<div class="wrap">
<?php screen_icon('edit'); ?>
<h2><?php echo _e('Events','live-calendar'); ?> <a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/admin.php?page=live-calendar-events&action=add" class="button add-new-h2"><?php echo _e('Add New','live-calendar'); ?></a> </h2>
<form name="filterform" id="filterform" class="wrap" method="get" action="<?php echo bloginfo('wpurl'); ?>/wp-admin/admin.php">
	<input type="hidden" name="page" value="live-calendar-events">
<p class="search-box">
	<input type="text" id="event_search_input" name="event_search_input" value="<?php if(isset($_REQUEST['event_search_input'])) echo $_REQUEST['event_search_input']; ?>" />
	<input type="submit" value="<?php echo _e('Search','live-calendar'); ?>" class="button" />
</p>
<p><?php
if(live_calendar_is_admin()){
	 $f_users = get_live_calendar_users();
	 if(count($f_users) > 1){
?><b>
<?php
_e('User : ','live-calendar'); 
?></b>
<select name="f_event_user">
<?php
	  if(isset($_REQUEST['f_event_user'])){
	  		echo '<option value="all" ';
	  		if($_REQUEST['f_event_user'] == 'all') echo ' select="selected" ';
	  		echo ' >'.__('All','live-calendar').'</option>';
	  }else{
	  		echo '<option value="all" select="selected" >'.__('All','live-calendar').'</option>';
	  }
    foreach($f_users as $f_user){
				echo '<option value="'.stripslashes($f_user->user).'"';
				if ($f_user->user == $_REQUEST['f_event_user']){
						echo 'selected="selected"';
				}
				echo '>'.stripslashes($f_user->user).'</option>';
		}
 ?>
</select> &nbsp;
<?php }
}
?>
	<b>
<?php
_e('Category : ','live-calendar'); 
?></b>
<select name="f_event_category">
<?php
		$sql = "SELECT * FROM " . WP_LIVE_CALENDAR_CATEGORIES_TABLE." WHERE user = '".get_user()."'";
	  $f_cats = $wpdb->get_results($sql);
	  if(isset($_REQUEST['f_event_category'])){
	  		echo '<option value="all" ';
	  		if($_REQUEST['f_event_category'] == 'all') echo ' select="selected" ';
	  		echo ' >'.__('All','live-calendar').'</option>';
	  }else{
	  		echo '<option value="all" >'.__('All','live-calendar').'</option>';
	  }
    foreach($f_cats as $f_cat){
				echo '<option value="'.stripslashes($f_cat->category_id).'"';
				if ($f_cat->category_id == $_REQUEST['f_event_category']){
						echo 'selected="selected"';
				}
				echo '>'.stripslashes($f_cat->category_name).'</option>';
		}
 ?>
</select> &nbsp;
	<b>
<?php
_e('Status : ','live-calendar'); 
?></b>
<select name="f_event_status">
<?php
	  if(isset($_REQUEST['f_event_status'])){
	  		echo '<option value="all" ';
	  		if($_REQUEST['f_event_status'] == 'all') echo ' selected="selected" ';
	  		echo ' >'.__('All','live-calendar').'</option>';
	  		
	  		echo '<option value="public" ';
	  		if($_REQUEST['f_event_status'] == 'public') echo ' selected="selected" ';
	  		echo ' >'.__('public','live-calendar').'</option>';
	  		
	  		echo '<option value="private" ';
	  		if($_REQUEST['f_event_status'] == 'private') echo ' selected="selected" ';
	  		echo ' >'.__('private','live-calendar').'</option>';
	  }else{
	  		echo '<option value="all" selected="selected">'.__('All','live-calendar').'</option>';
	  		echo '<option value="public" >'.__('public','live-calendar').'</option>';
	  		echo '<option value="private" >'.__('private','live-calendar').'</option>';
	  }

 ?>
</select> &nbsp;
<b><?php _e('Start Date : ','live-calendar'); ?></b>
<input type="text" name="f_event_date_begin" class="input" size="12" value="<?php 

	if(isset($_REQUEST['f_event_date_begin'])){
		echo $_REQUEST['f_event_date_begin'];		
	}else{
		echo date("Y-m-d",(ctwo()- 3600*24*365));
	}
?>" />  &nbsp;
<b><?php _e('End Date : ','live-calendar'); ?></b>
<input type="text" name="f_event_date_end" class="input" size="12" value="<?php 
	if(isset($_REQUEST['f_event_date_end'])){
		echo $_REQUEST['f_event_date_end'];
	}else{
		echo date("Y-m-d",ctwo());
	}
?>" /> &nbsp;
<input type="submit" id="post-query-submit" value="<?php _e('Filter','live-calendar'); ?>" class="button-secondary" />
</p>
</form>
<?php
	if(isset($_REQUEST['page_no']) && !empty($_REQUEST['page_no'])){
			$page_no = $_REQUEST['page_no'];
	}else{
			$page_no = 1;
	}
				
	if(isset($_REQUEST['event_search_input']) && !empty($_REQUEST['event_search_input'])){
			$key = "event_title like '%".mysql_escape_string($_REQUEST['event_search_input'])."%' OR event_desc like '%".mysql_escape_string($_REQUEST['event_search_input'])."%' ";
	}else{
			$key = " 1=1 ";
	}

	
	if(isset($_REQUEST['f_event_category']) && !empty($_REQUEST['f_event_category']) && $_REQUEST['f_event_category'] != 'all'){
			$f_cat = " event_category = ".$_REQUEST['f_event_category'];
	}else{
			$f_cat = " 1=1 ";
	}
	
	if(isset($_REQUEST['f_event_status']) && !empty($_REQUEST['f_event_status']) && $_REQUEST['f_event_status'] != 'all'){
			$f_status = " event_status = '".$_REQUEST['f_event_status']."' ";
	}else{
			$f_status = " 1=1 ";
	}
	
	if(isset($_REQUEST['f_event_user']) && !empty($_REQUEST['f_event_user']) && $_REQUEST['f_event_user'] != 'all'){
			$f_user = " user = '".$_REQUEST['f_event_user']."'";
	}else{
			$f_user =get_user_filter();
	}
	
	if(isset($_REQUEST['f_event_date_begin']) && isset($_REQUEST['f_event_date_end']) && !empty($_REQUEST['f_event_date_begin']) && !empty($_REQUEST['f_event_date_end'])){
			$f_date = " event_date_begin >= '".mysql_escape_string(date($_REQUEST['f_event_date_begin']))."' AND event_date_end <= '".mysql_escape_string(date($_REQUEST['f_event_date_end'])). "'";
	}else{
			$f_date = " 1=1 ";
	}
	
	$sql = "SELECT * FROM " . WP_LIVE_CALENDAR_TABLE . " where  ".$f_user." AND ".$f_cat. " AND " .$f_status." AND ".$f_date." AND ".$key." ORDER BY event_date_begin DESC, event_time_begin DESC, event_id DESC";

	$events = $wpdb->get_results($sql);	

	live_calendar_event_list($events, $page_no);
?>
</div>
<?php
}

function live_calendar_event_list($events = '', $page_no = 1){
	global $wpdb;	
	if($events == ''){
			$sql = "SELECT * FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE ".get_user_filter()." ORDER BY event_date_begin DESC, event_time_begin DESC, event_id DESC";	
			$events = $wpdb->get_results($sql);	
	}
	if (!empty($events)){
		$total	=	count($events);
		$per_page = 30;
		$show_flag = 0;
		
?>
<table class="widefat page fixed" width="100%" cellpadding="0" cellspacing="0">	
	<thead>
		<tr>
				<th class="manage-column" scope="col"><?php _e('ID','live-calendar') ?></th>
				<th class="manage-column" scope="col" colspan="2"><?php _e('Title','live-calendar') ?></th>
				<th class="manage-column" scope="col" colspan="2"><?php _e('Location','live-calendar') ?></th>
				<th class="manage-column" scope="col"><?php _e('Start Date','live-calendar') ?></th>
				<th class="manage-column" scope="col"><?php _e('End Date','live-calendar') ?></th>
		    <th class="manage-column" scope="col" colspan="2"><?php _e('Time','live-calendar') ?></th>
		    <th class="manage-column" scope="col"><?php _e('Category','live-calendar') ?></th>
		    <th class="manage-column" scope="col"><?php _e('Status','live-calendar') ?></th>
<?php
if(live_calendar_is_admin()){
	 $f_users = get_live_calendar_users();
	 if(count($f_users) > 1){
?>
		    <th class="manage-column" scope="col"><?php _e('User','live-calendar') ?></th>
<?php 
	}
} 
?>
				<th class="manage-column" scope="col"><?php _e('Edit','live-calendar') ?></th>
				<th class="manage-column" scope="col"><?php _e('Delete','live-calendar') ?></th>
		</tr>
	</thead>
<?php
	$class = '';
	foreach ( $events as $event ){
			$class = ($class == 'alternate') ? '' : 'alternate';
			$show_flag = $show_flag + 1;
			if($show_flag > (($page_no-1)*$per_page) and $show_flag <= ($page_no*$per_page)){
?>
					<tr class="<?php echo $class; ?>">
						<td scope="row"><?php echo stripslashes($event->event_id); ?></th>
						<td colspan="2"><?php echo stripslashes($event->event_title); ?></td>
						<td colspan="2"><?php echo stripslashes($event->event_location_name); ?></td>
						<td><?php echo stripslashes($event->event_date_begin); ?></td>
						<td><?php echo stripslashes($event->event_date_end); ?></td>
						<td colspan="2"><?php echo date("H:i",strtotime(htmlspecialchars(stripslashes($event->event_time_begin))));?> - <?php echo date("H:i",strtotime(htmlspecialchars(stripslashes($event->event_time_end))));?></td>
						<td><?php echo $wpdb->get_var($wpdb->prepare("SELECT category_name FROM " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " WHERE  category_id = ".$event->event_category, APP_POST_TYPE)); ?></td>
						<td><?php if($event->event_status =='public'){ echo __('public','live-calendar');}else{ echo __('private','live-calendar');} ?></td>
<?php
if(live_calendar_is_admin()){
	 $f_users = get_live_calendar_users();
	 if(count($f_users) > 1){
?>
		    <td><?php echo $event->user; ?></td>
<?php 
	} 
}
			  unset($this_cat); 
?>
						<td><a href="<?php bloginfo('wpurl') ?>/wp-admin/admin.php?page=live-calendar-events&amp;action=edit&amp;event_id=<?php echo stripslashes($event->event_id);?>" class='edit'><?php echo __('Edit','live-calendar'); ?></a>
						</td>
						<td><a href="<?php bloginfo('wpurl') ?>/wp-admin/admin.php?page=live-calendar-events&amp;action=delete&amp;event_id=<?php echo stripslashes($event->event_id);?>" class="delete" onclick="return confirm('<?php _e('Are you sure you want to delete this event?','live-calendar'); ?>')"><?php echo __('Delete','live-calendar'); ?></a>
						</td>
					</tr>
<?php
 			}
 			}
 			if(floor($total/$per_page)!=0){
 					if(isset($_REQUEST['f_event_date_begin']) && !empty($_REQUEST['f_event_date_begin']) && isset($_REQUEST['f_event_date_end']) && !empty($_REQUEST['f_event_date_end'])){
 						  $filterstring = '&f_event_date_begin='.$_REQUEST['f_event_date_begin'].'&f_event_date_end='.$_REQUEST['f_event_date_end'];
 					}
					if(isset($_REQUEST['f_event_category']) && !empty($_REQUEST['f_event_category'])){						
						 $filterstring .= '&f_event_category='.$_REQUEST['f_event_category'];
					}
					if(isset($_REQUEST['f_event_status']) && !empty($_REQUEST['f_event_status'])){						
						 $filterstring .= '&f_event_status='.$_REQUEST['f_event_status'];
					}
					if(isset($_REQUEST['event_search_input']) && !empty($_REQUEST['event_search_input'])){
						 $filterstring .= '&event_search_input='.$_REQUEST['event_search_input'];
					}		
?>
		<tfoot>
			<tr>
				<td colspan="2"></td>
				<td colspan="1"><a href="<?php bloginfo('wpurl') ?>/wp-admin/admin.php?page=live-calendar-events&amp;page_no=<?php echo (($page_no-1)==0) ? 1 : ($page_no-1); echo $filterstring; ?>" > <<
<?php	_e('PrePage','live-calendar');?> </a></td>
				<td colspan="2"></td>
				<td colspan="
<?php 	 
					$f_users = get_live_calendar_users();
	 	      if(live_calendar_is_admin()){ 
	 	      	if(count($f_users) > 1) {
	 	      		echo '4';
	 	      	}else{ 
	 	      		echo '3';
	 	      	}
	 	      } ?>">
	 	      <p align="center"><?php _e('Current:','live-calendar'); echo $page_no; ?>/<?php echo ceil($total/$per_page); ?></p></td>
				
			<td colspan="2"></td>
			<td colspan="2"><a href="<?php bloginfo('wpurl') ?>/wp-admin/admin.php?page=live-calendar-events&amp;page_no=
<?php 
				if($page_no == ceil($total/$per_page)){
	  				echo $page_no;
	  		}else{ 
	  				echo $page_no+1 ;
	  		}
	  		echo $filterstring;
?>"    >
<?php _e('NextPage','live-calendar');?> 
			>></a></td>
		<tr>
	</tfoot>
<?php
	}
?>
</table>
<?php
			}
	}
?>