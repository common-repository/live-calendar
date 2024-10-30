<?php
function calendar_month_comparison($month){
  $current_month = strtolower(date("M", ctwo()));
  if (isset($_GET['yr']) && isset($_GET['month'])){
      if ($month == $_GET['month']){
	  			return ' selected="selected"';
			}
  }
  elseif ($month == $current_month){
      return ' selected="selected"';
  }
}

function calendar_year_comparison($year){
  $current_year = strtolower(date("Y", ctwo()));
  if (isset($_GET['yr']) && isset($_GET['month'])){
      if ($year == $_GET['yr']){
	  			return ' selected="selected"';
			}
  }
  else if ($year == $current_year){
      return ' selected="selected"';
  }
}

if(!function_exists('live_calendar_snippet_text')) {
	function live_calendar_snippet_text($text, $length = 0) {
		global $wpdb;
		if(empty($user) || $user == "all"){
    	$user_str = ' 1=1 ';
  	} else{
  		$user_str = " user ='".$user."'";
  	}   
		$length= $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE $user_str AND config_item='event_title_truncate'", APP_POST_TYPE));
		if (defined('MB_OVERLOAD_STRING')) {
		  $text = @html_entity_decode($text, ENT_QUOTES, get_option('blog_charset'));
		 	if (mb_strlen($text) > $length) {
				return htmlentities(mb_substr($text,0,$length), ENT_COMPAT, get_option('blog_charset')).'..';
		 	} else {
				return htmlentities($text, ENT_COMPAT, get_option('blog_charset'));
		 	}
		} else {
			$text = @html_entity_decode($text, ENT_QUOTES, get_option('blog_charset'));
		 	if (strlen($text) > $length) {
				return htmlentities(substr($text,0,$length), ENT_COMPAT, get_option('blog_charset')).'..';
		 	} else {
				return htmlentities($text, ENT_COMPAT, get_option('blog_charset'));
		 	}
		}
	}
}

### Function to indicate the number of the day passed, eg. 1st or 2nd Sunday
function np_of_day($date){
  $instance = 0;
  $dom = date('j',strtotime($date));
  if (($dom-7) <= 0) { $instance = 1; }
  else if (($dom-7) > 0 && ($dom-7) <= 7) { $instance = 2; }
  else if (($dom-7) > 7 && ($dom-7) <= 14) { $instance = 3; }
  else if (($dom-7) > 14 && ($dom-7) <= 21) { $instance = 4; }
  else if (($dom-7) > 21 && ($dom-7) < 28) { $instance = 5; }
  return $instance;
}

function permalink_prefix(){
  if (is_home()) { 
    $p_link = get_bloginfo('url'); 
    if ($p_link[strlen($p_link)-1] != '/') { $p_link = $p_link.'/'; }
  } else { 
    $p_link = get_permalink(); 
  }

  if (!(strstr($p_link,'?'))) { $link_part = $p_link.'?'; } else { $link_part = $p_link.'&'; }

  return $link_part;
}


function next_link($cur_year,$cur_month){
  $mod_rewrite_months = array(1=>'jan','feb','mar','apr','may','jun','jul','aug','sept','oct','nov','dec');
  $next_year = $cur_year + 1;

  if ($cur_month == 12){
      return '<a href="' . permalink_prefix() . 'month=jan&amp;yr=' . $next_year . '">'.__('Next','live-calendar').' &raquo;</a>';
  }else{
      $next_month = $cur_month + 1;
      $month = $mod_rewrite_months[$next_month];
      return '<a href="' . permalink_prefix() . 'month='.$month.'&amp;yr=' . $cur_year . '">'.__('Next','live-calendar').' &raquo;</a>';
  }
}

function prev_link($cur_year,$cur_month){
  $mod_rewrite_months = array(1=>'jan','feb','mar','apr','may','jun','jul','aug','sept','oct','nov','dec');
  $last_year = $cur_year - 1;

  if ($cur_month == 1){
      return '<a href="' . permalink_prefix() . 'month=dec&amp;yr='. $last_year .'">&laquo; '.__('Prev','live-calendar').'</a>';
  }else{
      $next_month = $cur_month - 1;
      $month = $mod_rewrite_months[$next_month];
      return '<a href="' . permalink_prefix() . 'month='.$month.'&amp;yr=' . $cur_year . '">&laquo; '.__('Prev','live-calendar').'</a>';
  }
}


function get_event_desc($events){
  foreach($events as $event){
      $output .= '* '.stripslashes($event->event_desc);
  }
  return $output;
}

function draw_events($events,$user){

  foreach($events as $event){
      $output .= draw_event($event,$user).'<br />';
  }
  return $output;
}

function draw_event($event,$user){
  global $wpdb;
  if(empty($user) || $user == "all"){
    	$user_str = ' 1=1 ';
  } else{
  		$user_str = " user ='".$user."'";
  }                                  
  $show_cat = $wpdb->get_var("SELECT config_value FROM ".WP_LIVE_CALENDAR_CONFIG_TABLE." WHERE $user_str AND config_item='enable_categories'",0,0);

  if ($show_cat == 'true'){
      $sql = "SELECT * FROM " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " WHERE $user_str AND category_id=".mysql_escape_string($event->event_category);
      $cat_details = $wpdb->get_row($sql);
      $style = "background-color:".stripslashes($cat_details->category_colour)." ; color:".stripslashes($cat_details->font_colour)." ;";
  }
  else{
      $sql = "SELECT * FROM " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " WHERE $user_str AND category_id=1";
      $cat_details = $wpdb->get_row($sql);
      $style = "background-color:".stripslashes($cat_details->category_colour).";color:".stripslashes($cat_details->font_colour)." ;";
  }
  $header_details .=  '<span class="event-title" style="color:'.stripslashes($cat_details->font_colour).'; ">'.stripslashes($event->event_title);
  $header_details .=  '</span><br/>';
  if (($event->event_time_begin == '00:00:00') && ($event->event_time_end == '00:00:00')) {
	 		$time_string =__('All day','live-calendar');
	}else {
			$time_string =__('Time :','live-calendar').' '.date(get_option('time_format'), strtotime(stripslashes($event->event_time_begin))) . ' - '. date(get_option('time_format'), strtotime(stripslashes($event->event_time_end)));
	}

 	$header_details .= '<span class="time" style="color:'.stripslashes($cat_details->font_colour).'; " >'.$time_string.'</span><br />';
 	$event_location_name = $event->event_location_name;
 	$event_location_longitude = $event->event_location_longitude;
 	$event_location_latitude =$event->event_location_latitude;
 	if(!empty($event_location_longitude) && !empty($event_location_longitude) && is_numeric($event_location_longitude) && is_numeric($event_location_longitude)){
 		$temp1 = explode(".",$event_location_longitude);
 		$temp2 = explode(".",$event_location_latitude);
	
		$location = '<br /><br />[ '.$event_location_name.' , '.$temp1[0].'°'.substr($temp1[1],0,4).'\' , '.$temp2[0].'°'.substr($temp2[1],0,4).'\' ]';
	
	}else{
		$location = "";
	}
	
	if ($event->event_link != '') { 
  			$linky = stripslashes($event->event_link); 
  }else { 
  			$linky = '#'; 
  }
  
  $details = '<span class="calnk"><a href="'.$linky.'" style="'.$style.'" target="_blank">' . live_calendar_snippet_text(stripslashes($event->event_title)) . '<span style="'.$style.'">' . $header_details . '<br/>' . stripslashes($event->event_desc) .''.$location.'</span></a></span>';

  return $details;
}

function draw_sidebar_events($events, $i,$default_link,$sidebar_calendar_color,$sidebar_calendar_fontcolor){
  if (!count($events)){
				$details = $i;
	}else{
			foreach($events as $event){
					$header_details .=  '<span class="event-title" style="color:'.$sidebar_calendar_fontcolor.'">'.stripslashes($event->event_title);
					$header_details .=  '</span><br/>';
					
					if (($event->event_time_begin == '00:00:00') && ($event->event_time_end == '00:00:00')) {
	 							$time_string = ' '.__('All day','live-calendar');
	   			}else {
	   						$time_string = ' '.__('Time','live-calendar').' : ' . date(get_option('time_format'), strtotime(stripslashes($event->event_time_begin))) . ' - '. date(get_option('time_format'), strtotime(stripslashes($event->event_time_end))) ;
	   			}
      		$header_details .= '<span class="time" style="color:'.$sidebar_calendar_fontcolor.'; " >'.$time_string.'</span><br />';
  	
  				$header_details .= '<p style="font-size:85%; margin:10px; text-align:left; color:'.$sidebar_calendar_fontcolor.'">' . stripslashes($event->event_desc).'</p>';
			}
		
		 	if(count($events) == 1){
		     	if ($events[0]->event_link != '') { 
  						$linky = stripslashes($events[0]->event_link); 
  				}else { 
  						$linky = '#'; 
  				}
		  }else{
  						$linky = $default_link;
			}
			$details = '<span class="calnk_side"><a href="'.$linky.'"  style="background-color:'.$sidebar_calendar_color.';" target="_blank">' . $i . '<span style="background-color:'.$sidebar_calendar_color.'; color:'.$sidebar_calendar_fontcolor.';">' . $header_details .  '</span></a></span>';

	}

  return  $details;
}


function grab_events($y,$m,$d,$user){
    global $wpdb,$tod_no,$cal_no;
    $arr_events = array();
    $date = $y . '-' . $m . '-' . $d;   
    if(empty($user) || $user == "all"){
    	$user_str = ' 1=1 ';
  	} else{
  		$user_str = " user ='".$user."'";
  	} 
    $events = $wpdb->get_results("SELECT * FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE $user_str AND event_date_begin <= '$date' AND event_date_end >= '$date' AND event_status='public' ORDER BY event_date_begin ASC, event_time_begin ASC, event_id ASC");
    if (!empty($events)){
         foreach($events as $event){
	   					array_push($arr_events, $event);
         }
    }
 
  	return $arr_events;
}

###Function to provide time with WordPress offset, localy replaces time()
function ctwo(){
  return (time()+(3600*(get_option('gmt_offset'))));
}


function page_calendar($user){
    global $wpdb,$week_no;
    if(empty($user) || $user =="all"){
    	  $user_str =  " 1=1 ";
    }else{
    		$user_str	= " user ='".$user."'";
    }
    unset($week_no);
    if (get_option('start_of_week') == 0){
				$name_days = array(1=>__('Sun','live-calendar'),__('Mon','live-calendar'),__('Tue','live-calendar'),__('Wed','live-calendar'),__('Thu','live-calendar'),__('Fri','live-calendar'),__('Sat','live-calendar'));
    }
    else{
				$name_days = array(1=>__('Mon','live-calendar'),__('Tue','live-calendar'),__('Wed','live-calendar'),__('Thu','live-calendar'),__('Fri','live-calendar'),__('Sat','live-calendar'),__('Sun','live-calendar'));
    }
    $name_months = array(1=>__('January','live-calendar'),__('February','live-calendar'),__('March','live-calendar'),__('April','live-calendar'),__('May','live-calendar'),__('June','live-calendar'),__('July','live-calendar'),__('August','live-calendar'),__('September','live-calendar'),__('October','live-calendar'),__('November','live-calendar'),__('December','live-calendar'));

    if (empty($_GET['month']) || empty($_GET['yr'])){
        $c_year = date("Y",ctwo());
        $c_month = date("m",ctwo());
        $c_day = date("d",ctwo());
    }

    if ($_GET['yr'] <= 3000 && $_GET['yr'] >= 0 && (int)$_GET['yr'] != 0){
        if ($_GET['month'] == 'jan' || $_GET['month'] == 'feb' || $_GET['month'] == 'mar' || $_GET['month'] == 'apr' || $_GET['month'] == 'may' || $_GET['month'] == 'jun' || $_GET['month'] == 'jul' || $_GET['month'] == 'aug' || $_GET['month'] == 'sept' || $_GET['month'] == 'oct' || $_GET['month'] == 'nov' || $_GET['month'] == 'dec'){

               $c_year = mysql_escape_string($_GET['yr']);
               if ($_GET['month'] == 'jan') { $t_month = 1; }
               else if ($_GET['month'] == 'feb') { $t_month = 2; }
               else if ($_GET['month'] == 'mar') { $t_month = 3; }
               else if ($_GET['month'] == 'apr') { $t_month = 4; }
               else if ($_GET['month'] == 'may') { $t_month = 5; }
               else if ($_GET['month'] == 'jun') { $t_month = 6; }
               else if ($_GET['month'] == 'jul') { $t_month = 7; }
               else if ($_GET['month'] == 'aug') { $t_month = 8; }
               else if ($_GET['month'] == 'sept') { $t_month = 9; }
               else if ($_GET['month'] == 'oct') { $t_month = 10; }
               else if ($_GET['month'] == 'nov') { $t_month = 11; }
               else if ($_GET['month'] == 'dec') { $t_month = 12; }
               $c_month = $t_month;
               $c_day = date("d",ctwo());
        }
        else{
               $c_year = date("Y",ctwo());
               $c_month = date("m",ctwo());
               $c_day = date("d",ctwo());
        }
    }
    else{
        $c_year = date("Y",ctwo());
        $c_month = date("m",ctwo());
        $c_day = date("d",ctwo());
    }

    if (get_option('start_of_week') == 0){
				$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
        $first_weekday = ($first_weekday==0?1:$first_weekday+1);
    }
    else{
				$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
				$first_weekday = ($first_weekday==0?7:$first_weekday);
    }

    $days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));

    $calendar_body .= '<table class="calendar-table" id="calendar-table" >';
    $date_switcher = $wpdb->get_var("SELECT config_value FROM ".WP_LIVE_CALENDAR_CONFIG_TABLE." WHERE $user_str AND config_item='display_jump'",0,0);

    if ($date_switcher == 'true'){
				$calendar_body .= '<tr><td colspan="7" class="calendar-date-switcher"><form method="get" action="'.htmlspecialchars($_SERVER['REQUEST_URI']).'">';
				$qsa = array();
				parse_str($_SERVER['QUERY_STRING'],$qsa);
				foreach ($qsa as $name => $argument){
	    			if ($name != 'month' && $name != 'yr'){
								$calendar_body .= '<input type="hidden" name="'.strip_tags($name).'" value="'.strip_tags($argument).'" />';
	      		}
	  		}
				$calendar_body .= ''.__('Month','live-calendar').': <select name="month" style="width:100px;">
            <option value="jan"'.calendar_month_comparison('jan').'>'.__('January','live-calendar').'</option>
            <option value="feb"'.calendar_month_comparison('feb').'>'.__('February','live-calendar').'</option>
            <option value="mar"'.calendar_month_comparison('mar').'>'.__('March','live-calendar').'</option>
            <option value="apr"'.calendar_month_comparison('apr').'>'.__('April','live-calendar').'</option>
            <option value="may"'.calendar_month_comparison('may').'>'.__('May','live-calendar').'</option>
            <option value="jun"'.calendar_month_comparison('jun').'>'.__('June','live-calendar').'</option>
            <option value="jul"'.calendar_month_comparison('jul').'>'.__('July','live-calendar').'</option> 
            <option value="aug"'.calendar_month_comparison('aug').'>'.__('August','live-calendar').'</option> 
            <option value="sept"'.calendar_month_comparison('sept').'>'.__('September','live-calendar').'</option> 
            <option value="oct"'.calendar_month_comparison('oct').'>'.__('October','live-calendar').'</option> 
            <option value="nov"'.calendar_month_comparison('nov').'>'.__('November','live-calendar').'</option> 
            <option value="dec"'.calendar_month_comparison('dec').'>'.__('December','live-calendar').'</option> 
            </select>
            '.__('Year','live-calendar').': <select name="yr" style="width:60px;">';

				$past = 30;
				$future = 30;
				$fut = 1;
				while ($past > 0){
	    			$p .= '<option value="';
	    			$p .= date("Y",ctwo())-$past;
	    			$p .= '"'.calendar_year_comparison(date("Y",ctwo())-$past).'>';
	    			$p .= date("Y",ctwo())-$past.'</option>';
	    			$past = $past - 1;
	  		}
				while ($fut < $future) {
	    			$f .= '<option value="';
	    			$f .= date("Y",ctwo())+$fut;
	    			$f .= '"'.calendar_year_comparison(date("Y",ctwo())+$fut).'>';
	    			$f .= date("Y",ctwo())+$fut.'</option>';
	    			$fut = $fut + 1;
	  		} 
				$calendar_body .= $p;
				$calendar_body .= '<option value="'.date("Y",ctwo()).'"'.calendar_year_comparison(date("Y",ctwo())).'>'.date("Y",ctwo()).'</option>';
				$calendar_body .= $f;
    		$calendar_body .= '</select><input type="submit" value="'.__('Go','live-calendar').'" /></form></td></tr>';
  	}
  	
  	$calendar_body .= '
                    <tr>
                    <td colspan="2" class="calendar-prev">' . prev_link($c_year,$c_month) . '</td>
                    <td colspan="3" class="calendar-month">'.$name_months[(int)$c_month].' '.$c_year.'</td>
                    <td colspan="2" class="calendar-next">' . next_link($c_year,$c_month) . '</td>
                    </tr>';

    $calendar_body .= '<tr>';
    for ($i=1; $i<=7; $i++) {
				if (get_option('start_of_week') == 0){
	    			$calendar_body .= '<td class="'.($i<7&&$i>1?'normal-day-heading':'weekend-heading').'">'.$name_days[$i].'</td>';
	  		}
				else{
	    			$calendar_body .= '<td class="'.($i<6?'normal-day-heading':'weekend-heading').'">'.$name_days[$i].'</td>';
	  		}
    }
    $calendar_body .= '</tr>';

    for ($i=1; $i<=$days_in_month;){
        $calendar_body .= '<tr>';
        for ($ii=1; $ii<=7; $ii++){
            if ($ii==$first_weekday && $i==1){
								$go = TRUE;
	      		}
            elseif ($i > $days_in_month ) {
								$go = FALSE;
	      		}
            if ($go) {
								if (get_option('start_of_week') == 0){
		    						$grabbed_events = grab_events($c_year,$c_month,$i,$user);
		    						$no_events_class = '';
		    						if (!count($grabbed_events)){
												$no_events_class = ' no-events';
		      					}
		      					else{
												$no_events_class = ' events';
		      					}
		      					
		    						$calendar_body .= '<td class="'.(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date("Ymd",ctwo())?'current-day':'day-with-date').$no_events_class.'"><span '.($ii<7&&$ii>1?'':'class="weekend"').'>'.$i++.'</span><span class="event"><br />' . draw_events($grabbed_events,$user) . '</span></td>';
		  					}
								else{
		    						$grabbed_events = grab_events($c_year,$c_month,$i,$user);
		    						$no_events_class = '';
	            			if (!count($grabbed_events)){
												$no_events_class = ' no-events';
		      					}
		      					else{
												$no_events_class = ' events';
		      					}
		      					$datastring = $name_months[(int)$c_month].' '.$i;
		    						$calendar_body .= '<td class="'.(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date("Ymd",ctwo())?'current-day':'day-with-date').$no_events_class.'"><span '.($ii<6?'':'class="weekend"').'>'.$i++.'</span><br/><span class="event">' . draw_events($grabbed_events,$user) . '</span></td>';
		  					}
	     			}
            else {
								$calendar_body .= ' <td class="day-without-date">&nbsp;</td>';
	      		}
        }
        $calendar_body .= '</tr>';
    }
    $show_cat = $wpdb->get_var("SELECT config_value FROM ".WP_LIVE_CALENDAR_CONFIG_TABLE." WHERE $user_str AND config_item='enable_categories'",0,0);

    if ($show_cat == 'true'){
				$sql = "SELECT * FROM " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " WHERE $user_str ORDER BY category_name ASC";
				$cat_details = $wpdb->get_results($sql);
				$tr_flag	=	0;
        foreach($cat_details as $cat_detail){
        		$cat_each_count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM " . WP_LIVE_CALENDAR_TABLE . " where $user_str AND event_category = '".$cat_detail->category_id."'", APP_POST_TYPE));

        		if($tr_flag%7 == 0) $calendar_body .= '<tr>';
        		$calendar_body .= '<td colspan="1" style="background-color:'.$cat_detail->category_colour.';font-size:0.9em; color:'.$cat_detail->font_colour.'; ">'.$cat_detail->category_name.' ('.$cat_each_count.')</td>';
        		
        		if($tr_flag%7 == 6) $calendar_body .= '</tr>';
        		
        		$tr_flag	=	$tr_flag+1;
	  		}
	  		if(count($cat_details)%7 !=0){
        		for($i=0;$i<(7 - count($cat_details)%7);$i++){
        				  $calendar_body .= '<td colspan="1"></td>';
        		}
        }
        if($tr_flag == count($cat_details)) $calendar_body .= '</tr>';	  		 
    }
    $calendar_body .= '</table>';
    
    echo $calendar_body;
}


function sidebar_calendar($user){
    global $wpdb,$week_no;
  	if(empty($user) || $user =="all"){
    	  $user_str =  " 1=1 ";
    }else{
    		$user_str	= " user ='".$user."'";
    }
  	$default_link =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE $user_str AND config_item='default_link'", APP_POST_TYPE));
    unset($week_no);
    if (get_option('start_of_week') == 0){
    	if(WPLANG == ''){
					$name_days = array(1=>'S','M','T','W','T','F','S');
			}else{
					$name_days = array(1=>__('Su','live-calendar'),__('Mo','live-calendar'),__('Tu','live-calendar'),__('We','live-calendar'),__('Th','live-calendar'),__('Fr','live-calendar'),__('Sa','live-calendar'));
			}
    }
    else{
    	if(WPLANG == ''){
				$name_days = array(1=>'M','T','W','T','F','S','S');
			}else{
				$name_days = array(1=>__('Mo','live-calendar'),__('Tu','live-calendar'),__('We','live-calendar'),__('Th','live-calendar'),__('Fr','live-calendar'),__('Sa','live-calendar'),__('Su','live-calendar'));
			}
    }
    $name_months = array(1=>__('January','live-calendar'),__('February','live-calendar'),__('March','live-calendar'),__('April','live-calendar'),__('May','live-calendar'),__('June','live-calendar'),__('July','live-calendar'),__('August','live-calendar'),__('September','live-calendar'),__('October','live-calendar'),__('November','live-calendar'),__('December','live-calendar'));

    if (empty($_GET['month']) || empty($_GET['yr'])){
        $c_year = date("Y",ctwo());
        $c_month = date("m",ctwo());
        $c_day = date("d",ctwo());
    }

    if ($_GET['yr'] <= 3000 && $_GET['yr'] >= 0 && (int)$_GET['yr'] != 0){
        if ($_GET['month'] == 'jan' || $_GET['month'] == 'feb' || $_GET['month'] == 'mar' || $_GET['month'] == 'apr' || $_GET['month'] == 'may' || $_GET['month'] == 'jun' || $_GET['month'] == 'jul' || $_GET['month'] == 'aug' || $_GET['month'] == 'sept' || $_GET['month'] == 'oct' || $_GET['month'] == 'nov' || $_GET['month'] == 'dec'){

               $c_year = mysql_escape_string($_GET['yr']);
               if ($_GET['month'] == 'jan') { $t_month = 1; }
               else if ($_GET['month'] == 'feb') { $t_month = 2; }
               else if ($_GET['month'] == 'mar') { $t_month = 3; }
               else if ($_GET['month'] == 'apr') { $t_month = 4; }
               else if ($_GET['month'] == 'may') { $t_month = 5; }
               else if ($_GET['month'] == 'jun') { $t_month = 6; }
               else if ($_GET['month'] == 'jul') { $t_month = 7; }
               else if ($_GET['month'] == 'aug') { $t_month = 8; }
               else if ($_GET['month'] == 'sept') { $t_month = 9; }
               else if ($_GET['month'] == 'oct') { $t_month = 10; }
               else if ($_GET['month'] == 'nov') { $t_month = 11; }
               else if ($_GET['month'] == 'dec') { $t_month = 12; }
               $c_month = $t_month;
               $c_day = date("d",ctwo());
        }
        else{
               $c_year = date("Y",ctwo());
               $c_month = date("m",ctwo());
               $c_day = date("d",ctwo());
        }
    }
    else{
        $c_year = date("Y",ctwo());
        $c_month = date("m",ctwo());
        $c_day = date("d",ctwo());
    }

    if (get_option('start_of_week') == 0){
				$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
        $first_weekday = ($first_weekday==0?1:$first_weekday+1);
    }
    else{
				$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
				$first_weekday = ($first_weekday==0?7:$first_weekday);
    }

    $days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));

    $calendar_body .= '<table id="wp-calendar">';  	
  	$calendar_body .= '
  									<caption>'.$name_months[(int)$c_month].' '.$c_year. '</caption>
  									<thead>
                    <tr>';

    for ($i=1; $i<=7; $i++) {
				if (get_option('start_of_week') == 0){
	    			$calendar_body .= '<th scope="col" title="'.$name_days[$i].'">'.$name_days[$i].'</th>';
	  		}
				else{
	    			$calendar_body .= '<th scope="col" title="'.$name_days[$i].'">'.$name_days[$i].'</th>';
	  		}
    }
    $calendar_body .= '</tr></thead>';

    $calendar_body .= '<tbody><tr>';
  	
  	$sidebar_calendar_color =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE $user_str AND config_item='sidebar_calendar_color'", APP_POST_TYPE));
  	$sidebar_calendar_fontcolor =  $wpdb->get_var($wpdb->prepare("SELECT config_value FROM " . WP_LIVE_CALENDAR_CONFIG_TABLE . " WHERE $user_str AND config_item='sidebar_calendar_fontcolor'", APP_POST_TYPE));

    for ($i=1; $i<=$days_in_month;){
        $calendar_body .= '<tr>';
        for ($ii=1; $ii<=7; $ii++){
            if ($ii==$first_weekday && $i==1){
								$go = TRUE;
	      		}
            elseif ($i > $days_in_month ) {
								$go = FALSE;
	      		}
            if ($go) {
		    				$grabbed_events = grab_events($c_year,$c_month,$i,$user);
		    				$no_events_class = '';		    				
		      			$calendar_body .= '<td>'.draw_sidebar_events($grabbed_events, $i++,$default_link,$sidebar_calendar_color,$sidebar_calendar_fontcolor).'</td>';
						}else {
								$calendar_body .= '<td>&nbsp;</td>';
	      		}
        }
        $calendar_body .= '</tr>';
    }
    $calendar_body .= '<tr>';
    $calendar_body .= '<td colspan="3" id="prev">'. prev_link($c_year,$c_month) . '</td><td colspan="4" id="next">'. next_link($c_year,$c_month) . '</td>';
    $calendar_body .= '</tr>';
    $calendar_body .= '</tbody></table>';
    
    return $calendar_body;
}
/******************************* Display the Calendar in a page **************************/
function calendar_event_page($content){

	$cal_match = preg_match_all('|<calendar>(.*)</calendar>|U',$content,$cal_match_data);
	if($cal_match){
		foreach($cal_match_data[1] as $cal_user){
			$cal_output = page_calendar($cal_user);
			$content = preg_replace('|<calendar>(.*)</calendar>|U',$cal_output, $content,1);
		}
	}
	
	if (preg_match('{CALENDAR}',$content)){
      $cal_output = page_calendar('all');
      $content = str_replace('{CALENDAR}',$cal_output,$content);
  }
  
  return $content;
}

/******************************* Function for sidebar **************************/

function upcoming_event_page($content){
	global $wpdb; 
	$cal_match = preg_match_all('|<upcoming>(.*)</upcoming>|U',$content,$cal_match_data);
	if($cal_match){
		foreach($cal_match_data[1] as $cal_user){
			$cal_output = '<span class="page-upcoming-events"><B>'.__('Upcoming Events','live-calendar').' :</B><br />'.upcoming_events($cal_user).'</span>';
			$content = preg_replace('|<upcoming>(.*)</upcoming>|U',$cal_output, $content,1);
		}
	}

  if (preg_match('{UPCOMING_EVENTS}',$content)){
      $cal_output = '<span class="page-upcoming-events"><B>'.__('Upcoming Events','live-calendar').' :</B><br />'.upcoming_events('all').'</span>';
      $content = str_replace('{UPCOMING_EVENTS}',$cal_output,$content); 
  }
  return $content;
}
/*********************   Print today events   *************************/
function todays_event_page($content){
	global $wpdb;
	$cal_match = preg_match_all('|<today>(.*)</today>|U',$content,$cal_match_data);
	if($cal_match){
		foreach($cal_match_data[1] as $cal_user){
			$cal_output = '<span class="page-todays-events"><B>'.__('Current Events','live-calendar').' :</B>'.todays_events($cal_user).'</span>';
			$content = preg_replace('|<today>(.*)</today>|U',$cal_output, $content,1);
		}
	}
	
  if (preg_match('{TODAYS_EVENTS}',$content)){
      $cal_output = '<span class="page-todays-events"><B>'.__('Current Events','live-calendar').' :</B>'.todays_events('all').'</span>';
      $content = str_replace('{TODAYS_EVENTS}',$cal_output,$content);
	}
  return $content;
}


/*********************   Print upcoming events   *************************/

function upcoming_events($user){
  global $wpdb;
  if(empty($user) || $user =="all"){
    	  $user_str =  " 1=1 ";
  }else{
    		$user_str	= " user ='".$user."'";
  }
  
  $future_days = $wpdb->get_var($wpdb->prepare("SELECT config_value FROM ".WP_LIVE_CALENDAR_CONFIG_TABLE." WHERE $user_str AND config_item='display_upcoming_days'", APP_POST_TYPE));
  $day_count = 1;
  
  while ($day_count < $future_days+1){
	 		list($y,$m,$d) = split("-",date("Y-m-d",mktime($day_count*24,0,0,date("m",ctwo()),date("d",ctwo()),date("Y",ctwo()))));
	 		$events = grab_events($y,$m,$d,$user);
	 		if (count($events) != 0) {
	 				$output .= '<li>'.date_i18n(get_option('date_format'),mktime($day_count*24,0,0,date("m",ctwo()),date("d",ctwo()),date("Y",ctwo())));
	 				$output .= '<ul>';
	 				foreach($events as $event){
	   				if (($event->event_time_begin == '00:00:00') && ($event->event_time_end == '00:00:00')) {
	 							$time_string = ' '.__('All day','live-calendar');
	   				}else {
	 						$time_string = date(get_option('time_format'), strtotime(stripslashes($event->event_time_begin))).' - '.date(get_option('time_format'), strtotime(stripslashes($event->event_time_end))).''.__(', In ','live-calendar').''.stripslashes($event->event_city);
	   				}
          	$output .= '<li><span class="sidebar_event_title">'.stripslashes($event->event_title).'</span><br />';
          	$output .='<span class="sidebar_event_time">' .$time_string.'</span><br />';
          	$output .= '<span class="sidebar_event_content">'.stripslashes($event->event_desc).'</span>';
          	$output .= '</li>';
	 				}
	 				$output .= '</ul></li>';
	 		}
	 		$day_count = $day_count+1;
	 }
   if ($output == ''){
  		$output .=__('No event till now!','live-calendar');
   }
	 $visual = '<ul>';
	 $visual .= $output;
	 $visual .= '</ul>';
	 return $visual;
}

/***********************   Print todays events  ******************************/
function todays_events($user){
	
  $events = grab_events(date("Y",ctwo()),date("m",ctwo()),date("d",ctwo()),$user);
	if (count($events) != 0) {
	 		foreach($events as $event){
	   			if (($event->event_time_begin == '00:00:00') && ($event->event_time_end == '00:00:00')) {
	 							$time_string = ' '.__('All day','live-calendar');
	   			}else {
	 							$time_string = ' '.__('Between','live-calendar').' '.date(get_option('time_format'), strtotime(stripslashes($event->event_time_begin))).' - '.date(get_option('time_format'), strtotime(stripslashes($event->event_time_end)));
	   			}
          $output .= '<li><span class="sidebar_event_title">'.stripslashes($event->event_title).'</span><br />';
          $output .='<span class="sidebar_event_time">' .$time_string.'</span><br />';
          $output .= '<span class="sidebar_event_title">'.stripslashes($event->event_desc).'</span>';
          $output .= '</li>';
	 		}
	 }
   if ($output == ''){
  		$output .=''.__('No event till now!','live-calendar');
   }
	 $visual = '<ul>';
	 $visual .= $output;
	 $visual .= '</ul>';
	 return $visual;
	 
}


/************************   Print latest events  **********************************/

function sidebar_recent_events($user){
  	global $wpdb;
  	if(empty($user) || $user =="all"){
    	  $user_str =  " 1=1 ";
  	}else{
    		$user_str	= " user ='".$user."'";
  	}
    $sidebar_list_num = $wpdb->get_var("SELECT config_value FROM ".WP_LIVE_CALENDAR_CONFIG_TABLE." WHERE $user_str AND config_item='sidebar_list_num'",0,0);

  	$sql="SELECT * FROM " . WP_LIVE_CALENDAR_TABLE . "  where $user_str AND event_date_begin <= '".date('Y-m-d',ctwo())."' ORDER BY event_date_begin DESC, event_time_begin DESC, event_id DESC  limit ".$sidebar_list_num;
	  $events = $wpdb->get_results($sql);
	  $visual .='<ul>';
	  if(count($events) != 0){
	  		foreach($events as $event){
              	$output .= '<li><a href="'.$event->event_link.'" class="with_bottom_a" title="'.stripslashes($event->event_desc).'">'.stripslashes($event->event_title).' ('.date_i18n(get_option('date_format'),strtotime($event->event_date_begin)).')</a></li>';
			  }
			  $visual .= $output;
		}else{
				$visual .= ''.__('No event till now!','live-calendar').'</ul>';
		}		
	
		$visual .='</ul>';
		return $visual;
}

function sidebar_upcoming_events($user){
		global $wpdb;
		if(empty($user) || $user =="all"){
    	  $user_str =  " 1=1 ";
  	}else{
    		$user_str	= " user ='".$user."'";
  	}
    $events = grab_events(date("Y",ctwo()),date("m",ctwo()),date("d",ctwo()),$user);
    $visual .='<ul>';
	  if(count($events) != 0){
	  		foreach($events as $event){
              	$output .= '<li><a href="'.$event->event_link.'" class="with_bottom_a" title="'.stripslashes($event->event_desc).'">'.stripslashes($event->event_title).' ('.date_i18n(get_option('date_format'),strtotime($event->event_date_begin)).')</a></li>';
			  }
		}else{
				$output = '';
		}
    /************** display upcoming events  ***********************/
    $future_days = $wpdb->get_var("SELECT config_value FROM ".WP_LIVE_CALENDAR_CONFIG_TABLE." WHERE $user_str AND config_item='display_upcoming_days'",0,0);
    $day_count = 1;
		$event_arr = Array();
		$event_arr_count = 0;
    while ($day_count < $future_days+1){
	  		list($y,$m,$d) = split("-",date("Y-m-d",mktime($day_count*24,0,0,date("m",ctwo()),date("d",ctwo()),date("Y",ctwo()))));
	  		$events = grab_events($y,$m,$d,$user);
	  		if (count($events) != 0) {
	  				foreach($events as $event){
	  						if(count($event_arr) != 0){
	  								foreach($event_arr as $event_id){
	  										if($event->event_id == $event_id){
	  												$is_reduplicate = true;
	  										}
	  								}
	  								if(!$is_reduplicate){
	  											$event_arr[$event_arr_count]=$event->event_id;
	  											$output .= '<li><a href="'.$event->event_link.'" class="with_bottom_a" title="'.stripslashes($event->event_desc).'">'.stripslashes($event->event_title).' ('.date_i18n(get_option('date_format'),strtotime($event->event_date_begin)).')</a></li>';
	  											$event_arr_count = $event_arr_count + 1;
	  								}
	  						}else{
	  								$event_arr[$event_arr_count]=$event->event_id;
	  								$output .= '<li><a href="'.$event->event_link.'" class="with_bottom_a" title="'.stripslashes($event->event_desc).'">'.stripslashes($event->event_title).' ('.date_i18n(get_option('date_format'),strtotime($event->event_date_begin)).')</a></li>';
	  								$event_arr_count = $event_arr_count + 1;
	  						}
	  				}
	  		}else{
	  				$output .='';
	  		}
				$day_count = $day_count+1;
	  }
	  		$visual .= $output;	 	  
	 	$visual .='</ul>';		
		return $visual;
}

function get_location_fullname($location_id){
		global $wpdb;	
		$parent_id = $location_id;
		$location_name= get_location_name($parent_id);
		$end = "N";
		while($end == "N"){
				$parent_id = get_location_parentID($parent_id);
				if($parent_id == 0){
						$end = "Y";
				}else{
						$location_name = $location_name .' , '.get_location_name($parent_id);
				}
					
		}	
		
		return 	$location_name;
}
function get_location_parentID($location_id){
		global $wpdb;	
		$parent_id = $wpdb->get_var($wpdb->prepare("SELECT location_parent FROM " . WP_LIVE_CALENDAR_LOCATIONS_TABLE . " WHERE location_id=".$location_id, APP_POST_TYPE));
		
		return $parent_id;
}

function get_location_name($location_id){
		global $wpdb;	
		$location_name = $wpdb->get_var($wpdb->prepare("SELECT location_name FROM " . WP_LIVE_CALENDAR_LOCATIONS_TABLE . " WHERE location_id=".$location_id, APP_POST_TYPE));
		
		return $location_name;
}

function get_event_count(){
		global $wpdb;	
		$event_count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE event_status='public'", APP_POST_TYPE));
		
		return $event_count;
}
function get_recent_events($count){
		global $wpdb;	
		$events = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE event_status='public' order by event_date_begin desc, event_time_end desc limit " . $count, APP_POST_TYPE));
		
		return $events;
}
function sidebar_recent_status($user){
		$events=get_recent_events(1);
		$event=$events[0];
		$event_location_longitude = $event->event_location_longitude;
 		$event_location_latitude =$event->event_location_latitude;
 		$temp1 = explode(".",$event_location_longitude);
 		$temp2 = explode(".",$event_location_latitude);
 		
    $calendar_body .= '<table id="wp-calendar">';  	
  	$calendar_body .= '<tbody><tr><td colspan="4" align="left">'.$event->event_desc.'</td></tr>';

    $calendar_body .= '<tr>';
    $calendar_body .= '<td colspan="1" ></td><td colspan="3" align="right">'. date( 'M j',strtotime($event->event_date_begin))." , @" . $event->event_location_name .'</td></tr>';
    $calendar_body .= '</tbody>';
    $calendar_body .= '</table>';
    
    return $calendar_body;
}
?>