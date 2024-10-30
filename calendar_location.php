<?php
function live_calendar_locations(){
  global $wpdb;
  multi_user_update();
  if (isset($_POST['mode']) && $_POST['mode'] == 'add'){
  	  if(!empty($_POST['location_name'])){
  	 
      			$sql = "INSERT INTO " . WP_LIVE_CALENDAR_LOCATIONS_TABLE . " SET user = '".get_user()."', location_name='".mysql_escape_string($_POST['location_name'])."', location_longitude='".mysql_escape_string($_POST['location_longitude'])."', location_latitude='".mysql_escape_string($_POST['location_latitude'])."'";
      			$wpdb->get_results($sql);
      			
      			echo "<div class=\"updated\"><p><strong>".__('Location added successfully','live-calendar')."</strong></p></div>";
      		
      }
  }else if (isset($_GET['mode']) && isset($_GET['location_id']) && $_GET['mode'] == 'delete'){
      		$sql = "DELETE FROM " . WP_LIVE_CALENDAR_LOCATIONS_TABLE . " WHERE user = '".get_user()."' AND location_id=".mysql_escape_string($_GET['location_id']);
      		$wpdb->get_results($sql);      		
      		echo "<div class=\"updated\"><p><strong>".__('Location deleted successfully','live-calendar')."</strong></p></div>";
     
  }else if (isset($_GET['mode']) && isset($_GET['location_id']) && $_GET['mode'] == 'edit' && !isset($_POST['mode'])){
      $sql = "SELECT * FROM " . WP_LIVE_CALENDAR_LOCATIONS_TABLE . " WHERE user = '".get_user()."' AND location_id=".mysql_escape_string($_GET['location_id']);
      $cur_loc = $wpdb->get_row($sql);
?>
	<div class="wrap">
   <h2><?php _e('Edit Location','live-calendar'); ?></h2>
    <form name="locform" id="locform" class="wrap" method="post" action="<?php echo bloginfo('wpurl'); ?>/wp-admin/admin.php?page=live-calendar-locations">
          <input type="hidden" name="mode" value="edit" />
          <input type="hidden" name="location_id" value="<?php echo stripslashes($cur_loc->location_id) ?>" />
          <div id="linkadvanceddiv" class="postbox">
               <div style="float: left; width: 98%; clear: both;" class="inside">
									<table cellpadding="5" cellspacing="5">
                  	<tr>
											<td><legend><?php _e('Location Name','live-calendar'); ?> :</legend></td>
                      <td><input type="text" name="location_name" class="input" size="30" maxlength="30" value="<?php echo stripslashes($cur_loc->location_name) ?>" /></td>
										</tr>
                    <tr>
											<td><legend><?php _e('Longitude','live-calendar'); ?> :</legend></td>
                      <td><input type="text" name="location_longitude" class="input" size="20" maxlength="20" value="<?php echo stripslashes($cur_loc->location_longitude) ?>" /></td>
                    </tr>
                    <tr>
											<td><legend><?php _e('Latitude','live-calendar'); ?>:</legend></td>
                      <td><input type="text" name="location_latitude" class="input" size="20" maxlength="20" value="<?php echo stripslashes($cur_loc->location_latitude) ?>" /></td>
                    </tr>
                  </table>
              	</div>
                <div style="clear:both; height:1px;">&nbsp;</div>
                </div>
                <input type="submit" name="save" class="button bold" value="<?php _e('Save','live-calendar'); ?> &raquo;" />
    </form>
</div>
<?php
	}else if (isset($_POST['mode']) && isset($_POST['location_id']) && isset($_POST['location_name']) && isset($_POST['location_longitude']) && isset($_POST['location_latitude'])&& $_POST['mode'] == 'edit'){
      $sql = "UPDATE " . WP_LIVE_CALENDAR_LOCATIONS_TABLE . " SET location_name='".$_POST['location_name']."', location_longitude='".$_POST['location_longitude']."' , location_latitude='".$_POST['location_latitude']."' WHERE user = '".get_user()."' and location_id=".$_POST['location_id'];
      $wpdb->get_results($sql);
     	echo "<div class=\"updated\"><p><strong>".__('Location edited successfully','live-calendar')."</strong></p></div>";
  }
  
  if ($_GET['mode'] != 'edit' || $_POST['mode'] == 'edit'){
?>
  <div class="wrap">
    <h2><?php _e('Add Location','live-calendar'); ?></h2>
    <form name="locform" id="locform" class="wrap" method="post" action="<?php echo bloginfo('wpurl'); ?>/wp-admin/admin.php?page=live-calendar-locations">
          <input type="hidden" name="mode" value="add" />
          <input type="hidden" name="location_id" value="">
          <div id="linkadvanceddiv" class="postbox">
          		<div style="float: left; width: 98%; clear: both;" class="inside">
       					<table cellspacing="5" cellpadding="5">
                  <tr>
                  	<td><legend><?php _e('Location Name','live-calendar'); ?> :</legend></td>
                  	<td><input type="text" name="location_name" class="input" size="30" maxlength="30" value="" /></td>
                  </tr>
                  <tr>
                  	<td><legend><?php _e('Longitude','live-calendar'); ?> :</legend></td>
                  	<td><input type="text" name="location_longitude" class="input" size="20" maxlength="20" value="" /></td>
                  </tr>
                  <tr>
                  	<td><legend><?php _e('Latitude','live-calendar'); ?> :</legend></td>
                  	<td><input type="text" name="location_latitude" class="input" size="20" maxlength="20" value="" /></td>
                  </tr>
                 
                </table>
              </div>
		        	<div style="clear:both; height:1px;">&nbsp;</div>
          </div>
                <input type="submit" name="save" class="button bold" value="<?php _e('Save','live-calendar'); ?> &raquo;" />
    	</form>
    	<div class="wrap">
    	<h2><?php _e('Manage Locations','live-calendar'); ?></h2>
    	 <table class="widefat page fixed" width="100%" cellpadding="1" cellspacing="1">
       <thead> 
       	<tr>
         <th class="manage-column" scope="col"><?php _e('ID','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Location Name','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Location Longitude','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Location Latitude','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Edit','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Delete','live-calendar') ?></th>
       	</tr>
      </thead>
<?php	
    $locations = $wpdb->get_results("SELECT * FROM " . WP_LIVE_CALENDAR_LOCATIONS_TABLE . " WHERE user = '".get_user()."'  ORDER BY location_id ASC");
 		if ( !empty($locations) ){
     		$class = '';
     		foreach ( $locations as $location ){
	   		$class = ($class == 'alternate') ? '' : 'alternate';
?>
					<tr class="<?php echo $class; ?>">
	     		<th scope="row"><?php echo $location->location_id; ?></th>
	     		<td><?php echo $location->location_name; ?></td>
	     		<td><?php echo $location->location_longitude; ?></td>
	     		<td><?php echo $location->location_latitude; ?></td>
	     		<td><a href="<?php echo bloginfo('wpurl')  ?>/wp-admin/admin.php?page=live-calendar-locations&amp;mode=edit&amp;location_id=<?php echo stripslashes($location->location_id);?>" class='edit'><?php echo __('Edit','live-calendar'); ?></a></td>
	        <td><a href="<?php echo bloginfo('wpurl') ?>/wp-admin/admin.php?page=live-calendar-locations&amp;mode=delete&amp;location_id=<?php echo stripslashes($location->location_id);?>" class="delete" onclick="return confirm('<?php echo __('Are you sure you want to delete this location?','live-calendar'); ?>')"><?php echo __('Delete','live-calendar'); ?></a></td>
       		</tr>
<?php
				}
 		}
?>
 </table>
	</div>
  </div>
<?php
  } 
}
?>