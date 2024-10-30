<?php
/*
Plugin Name: live-calendar
Plugin URI: http://xieziming.com/dev/live-calendar
Description: A event calendar like Microsoft Live Calendar. <a href= "http://xieziming.com/dev/live-calendar" target="_blank"> [Usage]</a>
Version: 1.9.7
Author: Suny Tse
Author URI: http://xieziming.com
*/

/*  Copyright 2010  Suny Tse  (email : inbox@xieziming.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $table_prefix;
$table_prefix = ( isset( $table_prefix ) ) ? $table_prefix : $wpdb->prefix;

define('WP_LIVE_CALENDAR_TABLE', $table_prefix . 'live_calendar');
define('WP_LIVE_CALENDAR_CONFIG_TABLE',  $table_prefix . 'live_calendar_config');
define('WP_LIVE_CALENDAR_CATEGORIES_TABLE',  $table_prefix . 'live_calendar_categories');
define('WP_LIVE_CALENDAR_LOCATIONS_TABLE',  $table_prefix . 'live_calendar_locations');

register_activation_hook(__FILE__,'live_calendar_install');

add_action('admin_menu', 'live_calendar_menu');
add_action('wp_head', 'live_calendar_head');
add_action('widgets_init', 'widget_live_calendar_init');
add_action('init', 'live_calendar_textdomain');
add_action( "admin_head", 'calendar_add_javascript' );

add_filter('the_content','calendar_event_page');
add_filter('the_content','upcoming_event_page');
add_filter('the_content','todays_event_page');

require dirname(__FILE__) . "/calendar_function.php";

function get_user(){
	$current_user_data = wp_get_current_user();
	return $current_user_data->user_login;
}

function get_user_filter(){

  if (current_user_can('level_10')){
  		$re_str = ' 1=1 ';
  }else{
			$re_str = " user = '".get_user()."'";
	}
	
	return $re_str;
}

function get_live_calendar_users(){
	  global $wpdb;
		$sql = "SELECT distinct user FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE;
	  $users = $wpdb->get_results($sql);
	  return $users;
}
function live_calendar_is_admin(){
		if(current_user_can('level_10')){
		   return true;
		}else{
			return false;	
		}
}

function live_calendar_install(){
  global $wpdb;
  $wp_calendar_exists = false;
  $wp_calendar_config_exists = false;
  $wp_calendar_category_exists = false;
  $wp_calendar_location_exists = false;

/************************ Check which table not exist *********************/

  $tables = $wpdb->get_results("show tables");
  foreach ( $tables as $table ){
      foreach ( $table as $value ){
	  			if ( $value == WP_LIVE_CALENDAR_TABLE ){
	      			$wp_calendar_exists = true;
	    		}
	  			if ( $value == WP_LIVE_CALENDAR_CONFIG_TABLE ){
              $wp_calendar_config_exists = true;
      		}
      		if ( $value == WP_LIVE_CALENDAR_CATEGORIES_TABLE ){
              $wp_calendar_category_exists = true;
      		}
      		if ( $value == WP_LIVE_CALENDAR_LOCATIONS_TABLE ){
              $wp_calendar_location_exists = true;
      		}     		
  		}
  }


/************************ start the (re)install ***************************/

  if (!$wp_calendar_exists){
  	
      $sql = "CREATE TABLE ". WP_LIVE_CALENDAR_TABLE ." (
                                event_id INT(11) NOT NULL AUTO_INCREMENT ,
                                event_date_begin DATE NOT NULL ,
                                event_date_end DATE NOT NULL ,
                                event_title VARCHAR(30) NOT NULL ,
                                event_location_name VARCHAR(100) NOT NULL ,
                                event_location_longitude VARCHAR(30) NOT NULL ,
                                event_location_latitude VARCHAR(30) NOT NULL ,
                                event_desc TEXT NOT NULL ,
                                event_time_begin TIME ,
                                event_time_end TIME ,
                                event_category BIGINT(20) UNSIGNED NOT NULL DEFAULT 1 ,
                                event_status VARCHAR(20) NOT NULL ,
                                event_link TEXT DEFAULT '' ,
                                user VARCHAR( 20 ) CHARACTER SET utf8  NOT NULL,
                                PRIMARY KEY (event_id)
                        ) DEFAULT CHARSET=utf8;";
       	$wpdb->get_results($sql);
    }
    if(!$wp_calendar_config_exists){
    		$sql = "CREATE TABLE " . WP_LIVE_CALENDAR_CONFIG_TABLE . " (
                                config_item VARCHAR(30) NOT NULL ,
                                config_value TEXT NOT NULL ,
                                user VARCHAR( 20 ) CHARACTER SET utf8  NOT NULL,
                                PRIMARY KEY (config_item, user)
                        ) DEFAULT CHARSET=utf8;";
      	$wpdb->get_results($sql);
      
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='default_link', config_value='".get_bloginfo('home')."/calendar' ";
      	$wpdb->get_results($sql);       
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='display_upcoming_days', config_value=7";
     	 	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='enable_categories', config_value='true'";
      	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='sidebar_list_num', config_value=10";
      	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='event_title_width', config_value=30";
      	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='event_title_truncate', config_value=16";
      	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='sidebar_calendar_color', config_value='#666666'";
    		$wpdb->get_results($sql);    
    		$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='sidebar_calendar_fontcolor', config_value='#000000'";
    		$wpdb->get_results($sql);
		}
		
		if(!$wp_calendar_category_exists){
      	$sql = "CREATE TABLE " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " ( 
                                category_id INT(11) NOT NULL AUTO_INCREMENT, 
                                category_name VARCHAR(30) NOT NULL , 
                                category_colour VARCHAR(30) NOT NULL , 
                                font_colour VARCHAR(30) NOT NULL DEFAULT '#000000', 
                                user VARCHAR( 20 ) CHARACTER SET utf8  NOT NULL,
                                PRIMARY KEY (category_id) 
                             ) DEFAULT CHARSET=utf8;";
      	$wpdb->get_results($sql);
      
      	$sql = "INSERT INTO " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " SET category_id=1, category_name='General', category_colour='#F6F79B', font_colour='#000000' ";
      	$wpdb->get_results($sql);
    }
    
    if(!$wp_calendar_location_exists){
      	$sql = "CREATE TABLE " . WP_LIVE_CALENDAR_LOCATIONS_TABLE . " ( 
                                location_id INT(11) NOT NULL AUTO_INCREMENT, 
                                location_name VARCHAR(30) NOT NULL , 
                                location_longitude VARCHAR(30) NOT NULL , 
                                location_latitude VARCHAR(30) NOT NULL, 
                                user VARCHAR( 20 ) CHARACTER SET utf8  NOT NULL,
                                PRIMARY KEY (location_id) 
                             ) DEFAULT CHARSET=utf8;";
      	$wpdb->get_results($sql);     	
    }
}

function check_calendar_update(){
		global $wpdb;  		        
 	 	$sql = "ALTER TABLE ". WP_LIVE_CALENDAR_TABLE ."  ADD event_location_name VARCHAR( 100 ) NOT NULL AFTER event_date_end" ;
 	 	$wpdb->get_results($sql);
 	 	
 	 	$sql = "ALTER TABLE ". WP_LIVE_CALENDAR_TABLE ."  ADD event_location_longitude VARCHAR( 30 ) NOT NULL AFTER event_location_name" ;
 	 	$wpdb->get_results($sql);
 	 	
 	 	$sql = "ALTER TABLE ". WP_LIVE_CALENDAR_TABLE ."  ADD event_location_latitude VARCHAR( 30 ) NOT NULL AFTER event_location_longitude" ;
 	 	$wpdb->get_results($sql);
 	 	
 	 	$sql = "ALTER TABLE ". WP_LIVE_CALENDAR_TABLE ."  ADD event_status VARCHAR(20) NOT NULL AFTER event_category" ;
 	 	$wpdb->get_results($sql);
 	 	
 	 	$sql = "update ". WP_LIVE_CALENDAR_TABLE ."  set event_status = 'public' where event_status='' " ;
 	 	$wpdb->get_results($sql);
}

function multi_user_update(){
	check_calendar_update();
	global $wpdb;
	$current_user_exist =  $wpdb->get_var($wpdb->prepare("SELECT config_item FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE  user = '".get_user()."'", APP_POST_TYPE));
	if(empty($current_user_exist)){
			$user_exist =  $wpdb->get_var($wpdb->prepare("SELECT user FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE, APP_POST_TYPE));
			if(empty($user_exist)){
				$sql = "UPDATE ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET user= '".get_user()."'";
				$wpdb->get_results($sql);
				$sql = "UPDATE ".WP_LIVE_CALENDAR_CATEGORIES_TABLE." SET user= '".get_user()."'";
				$wpdb->get_results($sql);
				$sql = "UPDATE ".WP_LIVE_CALENDAR_TABLE." SET user= '".get_user()."'";
				$wpdb->get_results($sql);
				$sql = "UPDATE ".WP_LIVE_CALENDAR_LOCATIONS_TABLE." SET user= '".get_user()."'";
				$wpdb->get_results($sql);
			}else{

      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='default_link', config_value='".get_bloginfo('home')."/calendar', user= '".get_user()."' ";
      	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='display_upcoming_days', config_value=7, user= '".get_user()."'";
     	 	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='enable_categories', config_value='true', user= '".get_user()."'";
      	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='sidebar_list_num', config_value=10, user= '".get_user()."'";
      	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='event_title_width', config_value=30, user= '".get_user()."'";
      	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='event_title_truncate', config_value=16, user= '".get_user()."'";
      	$wpdb->get_results($sql);
      	$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='sidebar_calendar_color', config_value='#666666', user= '".get_user()."'";
    		$wpdb->get_results($sql);
    		$sql = "INSERT INTO ".WP_LIVE_CALENDAR_CONFIG_TABLE." SET config_item='sidebar_calendar_fontcolor', config_value='#000000', user= '".get_user()."'";
    		$wpdb->get_results($sql);
    		
    		$sql = "INSERT INTO " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " SET category_name='General', category_colour='#F6F79B', font_colour='#000000', user= '".get_user()."' ";
      	$wpdb->get_results($sql);
     }
        
	}
}

function live_calendar_textdomain() {
	load_plugin_textdomain('live-calendar', false, 'live-calendar/lang');
}

function live_calendar_menu() {

  if (function_exists('add_menu_page')) {
       add_menu_page(__('Calendar','live-calendar'), __('Calendar','live-calendar'), 2, 'live-calendar-events',live_calendar_event_manage);
  }
  if (function_exists('add_submenu_page')) {
       add_submenu_page('live-calendar-events', __('Events','live-calendar'), __('Events','live-calendar'), 2, 'live-calendar-events', 'live_calendar_event_manage');
       add_submenu_page('live-calendar-events', __('Categories','live-calendar'), __('Categories','live-calendar'), 2, 'live-calendar-categories', 'live_calendar_categories');
       add_submenu_page('live-calendar-events', __('Locations','live-calendar'), __('Locations','live-calendar'), 2, 'live-calendar-locations', 'live_calendar_locations');
       add_submenu_page('live-calendar-events', __('Options','live-calendar'), __('Options','live-calendar'), 2, 'live-calendar-options', 'live_calendar_options');
       add_submenu_page('live-calendar-events', __('Tools','live-calendar'), __('Tools','live-calendar'), 2, 'live-calendar-tools', 'live_calendar_tools');
  }
}

if($_GET['page'] == 'live-calendar-options'){
	include 'calendar_option.php';
}else if($_GET['page'] == 'live-calendar-categories'){
	include 'calendar_category.php';
}else if($_GET['page'] == 'live-calendar-locations'){
	include 'calendar_location.php';
}else if($_GET['page'] == 'live-calendar-tools'){
	include 'calendar_tools.php';
}else{
	include 'calendar_event.php';
}

function live_calendar_head(){
	$url = get_bloginfo('wpurl');
?>
 	<link rel="stylesheet" href="<?php echo $url; ?>/wp-content/plugins/live-calendar/live-calendar.css" type="text/css" media="screen" /> 
<?php
}

###Live-Calendar Dashboard Date widget

function calendar_add_javascript(){ 
  echo '<script type="text/javascript" src="';
  bloginfo('wpurl');
  echo '/wp-content/plugins/live-calendar/calendar.js"></script><script type="text/javascript">document.write(getCalendarStyles());</script>';
  
}

### Live-Calendar Sidebar Widget
function widget_live_calendar_init() {
	register_widget('WP_Widget_live_calendar');
}


class WP_Widget_live_calendar extends WP_Widget {

	function WP_Widget_live_calendar() {
			$widget_ops = array('description' => __('Live Calendar', 'live-calendar'));
			$this->WP_Widget('live-calendar', __('Live Calendar', 'live-calendar'), $widget_ops);
	}

	function widget($args, $instance) {
			extract($args);
			$title = apply_filters('widget_title', esc_attr($instance['title']));
			$type = esc_attr($instance['type']);
			$user = esc_attr($instance['user']);
			echo $before_widget.$before_title.$title.$after_title;
			echo "\n";
			switch($type) {
				case 'event_calendar':
					echo sidebar_calendar($user);
					break;
				case 'upcoming_events':
      		echo sidebar_upcoming_events($user);
					break;
				case 'recent_events':
      		echo sidebar_recent_events($user);
					break;
				case 'recent_status':
      		echo sidebar_recent_status($user);
					break;
			}
			echo "\n";
			echo $after_widget;
	}

	function update($new_instance, $old_instance) {
			if (!isset($new_instance['submit'])) {
				return false;
			}
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['type'] = strip_tags($new_instance['type']);
			$instance['user'] = strip_tags($new_instance['user']);
			return $instance;
	}

	function form($instance) {
			global $wpdb;
			$instance = wp_parse_args((array) $instance, array('title' => __('Event Calendar', 'live-calendar'), 'type' => 'event_calendar','user' => 'all'));
			$title = esc_attr($instance['title']);
			$type = esc_attr($instance['type']);
			$user = esc_attr($instance['user']);
			$sql = "select distinct user from ".WP_LIVE_CALENDAR_CONFIG_TABLE;
			$users_list = $wpdb->get_results($sql);
?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'live-calendar'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Display Type:', 'live-calendar'); ?>
				<select name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>" class="widefat">
					<option value="event_calendar"<?php selected('event_calendar', $type); ?>><?php _e('Event Calendar', 'live-calendar'); ?></option>
					<option value="upcoming_events"<?php selected('upcoming_events', $type); ?>><?php _e('Upcoming Events', 'live-calendar'); ?></option>
					<option value="recent_events"<?php selected('recent_events', $type); ?>><?php _e('Recent Events', 'live-calendar'); ?></option>
					<option value="recent_status"<?php selected('recent_status', $type); ?>><?php _e('Recent Status', 'live-calendar'); ?></option>
				</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('user'); ?>"><?php _e('Calendar:', 'live-calendar'); ?>
				<select name="<?php echo $this->get_field_name('user'); ?>" id="<?php echo $this->get_field_id('user'); ?>" class="widefat">
					<option value="all"<?php selected('all', $user); ?>><?php _e('All Users', 'live-calendar'); ?></option>
					<?php foreach($users_list as $user_list){?>
					<option value="<?php echo $user_list->user;?>"<?php selected($user_list->user, $user); ?>><?php echo $user_list->user; ?></option>
					<?php }?>
				</select>
				</label>
			</p>
			<p>
				<input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
			</p>
<?php
	}
}
?>