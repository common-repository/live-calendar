<?php
function live_calendar_categories(){
  global $wpdb;
  multi_user_update();
  if (isset($_POST['mode']) && $_POST['mode'] == 'add'){
  	  if(!empty($_POST['category_name']) && !empty($_POST['category_colour']) && !empty($_POST['font_colour'])){
  	  		if(preg_match('/^#.{6}$/',$_POST['category_colour']) && preg_match('/^#.{6}$/',$_POST['font_colour'])){
      			$sql = "INSERT INTO " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " SET user = '".get_user()."', category_name='".mysql_escape_string($_POST['category_name'])."', category_colour='".mysql_escape_string($_POST['category_colour'])."', font_colour='".mysql_escape_string($_POST['font_colour'])."'";
      			$wpdb->get_results($sql);
      			echo "<div class=\"updated\"><p><strong>".__('Category added successfully','live-calendar')."</strong></p></div>";
      		}else{
      			echo "<div class=\"error\"><p><strong>".__('Error Color Format !','live-calendar')."</strong></p></div>";
      		}
      }
  }else if (isset($_GET['mode']) && isset($_GET['category_id']) && $_GET['mode'] == 'delete'){
      $sql = "DELETE FROM " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " WHERE user = '".get_user()."' AND category_id=".mysql_escape_string($_GET['category_id']);
      $wpdb->get_results($sql);
      $sql = "UPDATE " . WP_LIVE_CALENDAR_TABLE . " SET event_category=1 WHERE user = '".get_user()."' AND event_category=".mysql_escape_string($_GET['category_id']);
      $wpdb->get_results($sql);
      echo "<div class=\"updated\"><p><strong>".__('Category deleted successfully','live-calendar')."</strong></p></div>";
  }else if (isset($_GET['mode']) && isset($_GET['category_id']) && $_GET['mode'] == 'edit' && !isset($_POST['mode'])){
      $sql = "SELECT * FROM " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " WHERE user = '".get_user()."' AND category_id=".mysql_escape_string($_GET['category_id']);
      $cur_cat = $wpdb->get_row($sql);
?>
	<div class="wrap">
   <h2><?php _e('Edit Category','live-calendar'); ?></h2>
    <form name="catform" id="catform" class="wrap" method="post" action="<?php echo bloginfo('wpurl'); ?>/wp-admin/admin.php?page=live-calendar-categories">
          <input type="hidden" name="mode" value="edit" />
          <input type="hidden" name="category_id" value="<?php echo stripslashes($cur_cat->category_id) ?>" />
          <div id="linkadvanceddiv" class="postbox">
               <div style="float: left; width: 98%; clear: both;" class="inside">
									<table cellpadding="5" cellspacing="5">
                  	<tr>
											<td><legend><?php _e('Category Name','live-calendar'); ?>:</legend></td>
                      <td><input type="text" name="category_name" class="input" size="30" maxlength="30" value="<?php echo stripslashes($cur_cat->category_name) ?>" /></td>
										</tr>
                    <tr>
											<td><legend><?php _e('Category Colour (include #)','live-calendar'); ?>:</legend></td>
                      <td><input type="text" name="category_colour" class="input" size="10" maxlength="7" value="<?php echo stripslashes($cur_cat->category_colour) ?>" /></td>
                    </tr>
                     <tr>
											<td><legend><?php _e('Font Colour (include #)','live-calendar'); ?>:</legend></td>
                      <td><input type="text" name="font_colour" class="input" size="10" maxlength="7" value="<?php echo stripslashes($cur_cat->font_colour) ?>" /></td>
                    </tr>
                  </table>
              	</div>
                <div style="clear:both; height:1px;">&nbsp;</div>
                </div>
                <input type="submit" name="save" class="button bold" value="<?php _e('Save','live-calendar'); ?> &raquo;" />
    </form>
</div>
<?php
	}else if (isset($_POST['mode']) && isset($_POST['category_id']) && isset($_POST['category_name']) && isset($_POST['category_colour']) && isset($_POST['font_colour'])&& $_POST['mode'] == 'edit'){
      $sql = "UPDATE " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " SET user = '".get_user()."', category_name='".mysql_escape_string($_POST['category_name'])."', category_colour='".mysql_escape_string($_POST['category_colour'])."' , font_colour='".mysql_escape_string($_POST['font_colour'])."'  WHERE category_id=".mysql_escape_string($_POST['category_id']);
      $wpdb->get_results($sql);
     	echo "<div class=\"updated\"><p><strong>".__('Category edited successfully','live-calendar')."</strong></p></div>";
  }
  
  if ($_GET['mode'] != 'edit' || $_POST['mode'] == 'edit'){
?>

  <div class="wrap">
    <h2><?php _e('Add Category','live-calendar'); ?></h2>
    <form name="catform" id="catform" class="wrap" method="post" action="<?php echo bloginfo('wpurl'); ?>/wp-admin/admin.php?page=live-calendar-categories">
          <input type="hidden" name="mode" value="add" />
          <input type="hidden" name="category_id" value="">
          <div id="linkadvanceddiv" class="postbox">
          		<div style="float: left; width: 98%; clear: both;" class="inside">
       					<table cellspacing="5" cellpadding="5">
                  <tr>
                  	<td><legend><?php _e('Category Name','live-calendar'); ?>:</legend></td>
                  	<td><input type="text" name="category_name" class="input" size="30" maxlength="30" value="" /></td>
                  </tr>
                  <tr>
                  	<td><legend><?php _e('Category Colour (include #)','live-calendar'); ?>:</legend></td>
                  	<td><input type="text" name="category_colour" class="input" size="10" maxlength="7" value="" /></td>
                  </tr>
                  <tr>
                  	<td><legend><?php _e('Font Colour (include #)','live-calendar'); ?>:</legend></td>
                  	<td><input type="text" name="font_colour" class="input" size="10" maxlength="7" value="" /></td>
                  </tr>
                </table>
              </div>
		        	<div style="clear:both; height:1px;">&nbsp;</div>
          </div>
                <input type="submit" name="save" class="button bold" value="<?php _e('Save','live-calendar'); ?> &raquo;" />
    	</form>
    	<div class="wrap">
    	<h2><?php _e('Manage Categories','live-calendar'); ?></h2>
<?php
    $categories = $wpdb->get_results("SELECT * FROM " . WP_LIVE_CALENDAR_CATEGORIES_TABLE . " WHERE user = '".get_user()."' ORDER BY category_id ASC");
 		if ( !empty($categories) ){
?>
     <table class="widefat page fixed" width="100%" cellpadding="1" cellspacing="1">
       <thead> 
       	<tr>
         <th class="manage-column" scope="col"><?php _e('ID','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Category Name','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Category Colour','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Font Colour','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Count','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Edit','live-calendar') ?></th>
	       <th class="manage-column" scope="col"><?php _e('Delete','live-calendar') ?></th>
       	</tr>
      </thead>
<?php
     $class = '';
     foreach ( $categories as $category ){
	   		$class = ($class == 'alternate') ? '' : 'alternate';
?>
       <tr class="<?php echo $class; ?>">
	     	<th scope="row"><?php echo stripslashes($category->category_id); ?></th>
	     		<td><?php echo stripslashes($category->category_name); ?></td>
	     		<td style="background-color:<?php echo stripslashes($category->category_colour); ?>;">&nbsp;</td>
	     		<td style="background-color:<?php echo stripslashes($category->font_colour); ?>;">&nbsp;</td>
<?php 
  $cat_count =  $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . WP_LIVE_CALENDAR_TABLE . " WHERE user = '".get_user()."' AND event_category='".stripslashes($category->category_id)."'", APP_POST_TYPE));
?>
	     	  <td><?php echo number_format($cat_count); ?></td>
	     		<td><a href="<?php echo bloginfo('wpurl')  ?>/wp-admin/admin.php?page=live-calendar-categories&amp;mode=edit&amp;category_id=<?php echo stripslashes($category->category_id);?>" class='edit'><?php echo __('Edit','live-calendar'); ?></a></td>
<?php
	     		if ($category->category_id == 1){
		 					echo '<td>'.__('N/A','live-calendar').'</td>';
	     		}
       		else{
?>
          		<td><a href="<?php echo bloginfo('wpurl') ?>/wp-admin/admin.php?page=live-calendar-categories&amp;mode=delete&amp;category_id=<?php echo stripslashes($category->category_id);?>" class="delete" onclick="return confirm('<?php echo __('Are you sure you want to delete this category?','live-calendar'); ?>')"><?php echo __('Delete','live-calendar'); ?></a></td>
<?php
	     		}
?>
       </tr>
<?php
}
?>
     </table>
<?php
 }
 else{
     echo '<p>'.__('There are no categories in the database - something has gone wrong!','live-calendar').'</p>';
 }
 
?>
	</div>
  </div>

<?php
  } 
}
?>