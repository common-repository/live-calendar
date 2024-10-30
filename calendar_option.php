<?php
function live_calendar_options(){
  global $wpdb;
  multi_user_update();
  if (isset($_POST['enable_categories'])){
  		$display_upcoming_days = mysql_escape_string($_POST['display_upcoming_days']);
      
      if (mysql_escape_string($_POST['enable_categories']) == 'on'){
          $enable_categories = 'true';
      } else{
	    		$enable_categories = 'false';
	    }

	    if (mysql_escape_string($_POST['default_link']) == ''){
          $default_link = '#';
      } else{
	    		$default_link = mysql_escape_string($_POST['default_link']);
	    } 
	    if (mysql_escape_string($_POST['sidebar_list_num']) == ''){
          $sidebar_list_num = 10;
      } else{
	    		$sidebar_list_num = mysql_escape_string($_POST['sidebar_list_num']);
	    }
	    if (mysql_escape_string($_POST['event_title_width']) == ''){
          $event_title_width = 30;
      } else{
	    		$event_title_width = mysql_escape_string($_POST['event_title_width']);
	    }
	    if (mysql_escape_string($_POST['event_title_truncate']) == ''){
          $event_title_truncate = 30;
      } else{
	    		$event_title_truncate = mysql_escape_string($_POST['event_title_truncate']);
	    }
	    if (mysql_escape_string($_POST['sidebar_calendar_color']) == ''){
          $sidebar_calendar_color = '#666666';
      } else{
	    		$sidebar_calendar_color = mysql_escape_string($_POST['sidebar_calendar_color']);
	    }
	    if (mysql_escape_string($_POST['sidebar_calendar_fontcolor']) == ''){
          $sidebar_calendar_fontcolor = '#000000';
      } else{
	    		$sidebar_calendar_fontcolor = mysql_escape_string($_POST['sidebar_calendar_fontcolor']);
	    }
      
	    $wpdb->get_results("UPDATE " . WP_LIVE_CALENDAR_CONFIG_TABLE . " SET config_value = '".$default_link."' WHERE user = '".get_user()."' AND config_item='default_link'");
      $wpdb->get_results("UPDATE " . WP_LIVE_CALENDAR_CONFIG_TABLE . " SET config_value = '".$display_upcoming_days."' WHERE user = '".get_user()."' AND config_item='display_upcoming_days'");
  		$wpdb->get_results("UPDATE " . WP_LIVE_CALENDAR_CONFIG_TABLE . " SET config_value = '".$enable_categories."' WHERE user = '".get_user()."' AND config_item='enable_categories'");
  		$wpdb->get_results("UPDATE " . WP_LIVE_CALENDAR_CONFIG_TABLE . " SET config_value = '".$sidebar_list_num."' WHERE user = '".get_user()."' AND config_item='sidebar_list_num'");
  		$wpdb->get_results("UPDATE " . WP_LIVE_CALENDAR_CONFIG_TABLE . " SET config_value = '".$event_title_width."' WHERE user = '".get_user()."' AND config_item='event_title_width'");
  		$wpdb->get_results("UPDATE " . WP_LIVE_CALENDAR_CONFIG_TABLE . " SET config_value = '".$event_title_truncate."' WHERE user = '".get_user()."' AND config_item='event_title_truncate'");
  		$wpdb->get_results("UPDATE " . WP_LIVE_CALENDAR_CONFIG_TABLE . " SET config_value = '".$sidebar_calendar_color."' WHERE user = '".get_user()."' AND config_item='sidebar_calendar_color'");
  		$wpdb->get_results("UPDATE " . WP_LIVE_CALENDAR_CONFIG_TABLE . " SET config_value = '".$sidebar_calendar_fontcolor."' WHERE user = '".get_user()."' AND config_item='sidebar_calendar_fontcolor'");

  		echo "<div class=\"updated\"><p><strong>".__('Settings saved','live-calendar').".</strong></p></div>";
	}

		 $default_link =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE user = '".get_user()."' AND config_item='default_link'", APP_POST_TYPE));
  	 $display_upcoming_days =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE user = '".get_user()."' AND config_item='display_upcoming_days'", APP_POST_TYPE));
  	 $enable_categories =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE user = '".get_user()."' AND config_item='enable_categories'", APP_POST_TYPE));
  	 $sidebar_list_num =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE user = '".get_user()."' AND config_item='sidebar_list_num'", APP_POST_TYPE));
  	 $event_title_width =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE user = '".get_user()."' AND config_item='event_title_width'", APP_POST_TYPE));
		 $event_title_truncate =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE user = '".get_user()."' AND config_item='event_title_truncate'", APP_POST_TYPE));
		 $sidebar_calendar_color =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE user = '".get_user()."' AND config_item='sidebar_calendar_color'", APP_POST_TYPE));
   	 $sidebar_calendar_fontcolor =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE user = '".get_user()."' AND config_item='sidebar_calendar_fontcolor'", APP_POST_TYPE));
  
     if ($enable_categories == 'true'){
         $yes_enable_categories = 'selected="selected"';
     }else{
         $no_enable_categories = 'selected="selected"';
     }
?>
<div class="wrap">
  <h2><?php _e('Calendar Options','live-calendar'); ?></h2>
  <form name="quoteform" id="quoteform" class="wrap" method="post" action="<?php echo bloginfo('wpurl'); ?>/wp-admin/admin.php?page=live-calendar-options">
       <div id="linkadvanceddiv" class="postbox">
            <div style="float: left; width: 98%; clear: both;" class="inside">
                <table cellpadding="5" cellspacing="5">
               
									<tr>
											<td><legend><?php _e('Default Link','live-calendar'); ?>:</legend></td>
                      <td><input type="text" name="default_link" class="input" size="60" maxlength="60" value="<?php echo $default_link; ?>" /></td>
									</tr>
                  
                  <tr>
											<td><legend><?php _e('Display upcoming events for','live-calendar'); ?></legend></td>
                     	<td><input type="text" name="display_upcoming_days" value="<?php echo $display_upcoming_days ?>" size="1" maxlength="3" /> <?php _e('days','live-calendar'); ?>
                       </td>
                  </tr>
                  <tr>
											<td><legend><?php _e('Show event categories?','live-calendar'); ?></legend></td>
                      <td><select name="enable_categories">
				                			<option value="on" <?php echo $yes_enable_categories ?>><?php _e('Yes','live-calendar') ?></option>
															<option value="off" <?php echo $no_enable_categories ?>><?php _e('No','live-calendar') ?></option>
                          </select>
                      </td>
                  </tr>
                  <tr>
											<td><legend><?php _e('Number of Recent events list?','live-calendar'); ?></legend></td>
                      <td><input type="text" name="sidebar_list_num" class="input" size="10" maxlength="10" value="<?php echo $sidebar_list_num; ?>" /></td>
                  </tr>
                  <tr>
											<td><legend><?php _e('Max characters of event title?','live-calendar'); ?></legend></td>
                      <td><input type="text" name="event_title_width" class="input" size="10" maxlength="10" value="<?php echo $event_title_width; ?>" /></td>
                  </tr>
                  <tr>
											<td><legend><?php _e('Max characters to truncate?','live-calendar'); ?></legend></td>
                      <td><input type="text" name="event_title_truncate" class="input" size="10" maxlength="10" value="<?php echo $event_title_truncate; ?>" /></td>
                  </tr>
                  <tr>
											<td><legend><?php _e('Background color of sidebar calendar?','live-calendar'); ?></legend></td>
                      <td><input type="text" name="sidebar_calendar_color" class="input" size="10" maxlength="10" value="<?php echo $sidebar_calendar_color; ?>" /></td>
                  </tr>
                  <tr>
											<td><legend><?php _e('Font color of sidebar calendar?','live-calendar'); ?></legend></td>
                      <td><input type="text" name="sidebar_calendar_fontcolor" class="input" size="10" maxlength="10" value="<?php echo $sidebar_calendar_fontcolor; ?>" /></td>
                  </tr>
                </table>
						</div>
            <div style="clear:both; height:1px;">&nbsp;</div>
	        </div>
          <input type="submit" name="save" class="button bold" value="<?php _e('Save','live-calendar'); ?> &raquo;" />
  </form>
  </div>
<?php
}
?>