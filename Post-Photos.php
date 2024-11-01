<?php
/*
Plugin Name: Post Photos
Description: Add photos to your posts!
Version: 1.0
Author: Liam Parker
Author URI: http://liamparker.com/
*/

add_action("admin_init", "ppSetupAdmin");
add_action('save_post', 'ppSavePluginAdmin');

//Define Initial Variables
define("pp_plugin_id", 'pp', true);
define("pp_plugin_name", 'Post Photos', true);
define("pp_plugin_name_id", 'postPhotos', true);
define("pp_form_verification_id", pp_plugin_id."-verification", true);
define("pp_form_row_id", pp_plugin_id."-id", true);

//Setup Admin
function ppSetupAdmin(){
	if(function_exists('get_post_types')) {
		$postTypes = get_post_types( array(), 'objects' );
		foreach ($postTypes as $postType) {
			if ($postType->show_ui) {
				add_meta_box(pp_plugin_name_id, pp_plugin_name, pp_plugin_name_id, $postType->name, "normal", "high");
			}
		}
	} 
}

//Get Post Photos
function getPostPhotos(){
	global $post;
	$postPhotos = get_post_meta( $post->ID, pp_plugin_name_id, true);
	if (is_array($postPhotos)){ 
		$counter = 1;
		foreach($postPhotos['postPhoto'] as $id => $postPhoto ) {
			$result .= "<div class='post-photo' id='post-photo-$counter'><img src='$postPhoto'/></div>";
			$counter++;
		}
	}
	return $result;
}

//Admin Save
function ppSavePluginAdmin(){
	if (wp_verify_nonce( $_POST[pp_form_verification_id], plugin_basename(__FILE__)) && !defined('DOING_AUTOSAVE')) {
		global $post;
		$postPhotosArray = array();
		$counter = 0;
		if($_POST[pp_form_row_id]){
			foreach($_POST[pp_form_row_id] as $id) {
				$postPhoto = $_POST['postPhoto'][$id]; 
				if($postPhoto){
					$postPhotosArray['postPhoto'][$counter] = $postPhoto; 
					$counter++;
				}
			} 
		}
	}
	if (count($postPhotosArray)>0){
		update_post_meta($post->ID, pp_plugin_name_id, $postPhotosArray);
	}else{
		delete_post_meta($post->ID, pp_plugin_name_id);
	}
}

//Make Input Row
function ppMakeRow($id, $link=''){
	$pp_form_row_id = pp_form_row_id."[$id]";
	$row = "<div class='box'><input type='hidden' name='$pp_form_row_id' value='$id'/><label>Photo URL: </label><input type='text' name='postPhoto[$id]' value='$link'/> <input class='button' id='remove' type='button' name='remove' value='Remove Row'/></div>";
	return $row;
}

//Setup Meta Box
function postPhotos(){
?>

<style>
	#wpwrap #<?php echo pp_plugin_name_id; ?> .box {margin: 5px 0;overflow: hidden;}
	#wpwrap #<?php echo pp_plugin_name_id; ?> p {margin: 10px 0;}
</style>

<script>
	var $j = jQuery.noConflict();
	$j(document).ready(function() {
		
		$j('#postPhotos #add').click(function() {
			newPostPhotosRow();
		});
		
		$j('#postPhotos #remove').live('click', function() {
			$j(this).parent().remove();
		});
		
		function newPostPhotosRow(){
			$j('#postPhotos #newRow').before("<?php echo ppMakeRow(rand(1000, 2000)); ?>");
		}
		
		$j('#postPhotos #clear').click(function() {
			$j("#postPhotos .box").remove();
			newPostPhotosRow();
		})
		
	})
</script>

<?php
	global $post;
	$custom = get_post_custom($post->ID);
	$postPhotos = $custom[pp_plugin_name_id][0];
	$postPhotos = unserialize($postPhotos);		
	if (is_array($postPhotos)){ 
		foreach($postPhotos['postPhoto'] as $id => $postPhoto ) {
			echo ppMakeRow($id, $postPhoto); 
		}
	}
	echo ppMakeRow("1000"); 
?>
	<div id="newRow"></div> 
	<?php wp_nonce_field( plugin_basename(__FILE__), pp_form_verification_id); 
?>
	<p>
		<input id="add" class="button" type="button" name="add" value="Add New Row"/>
		<input id="clear" class="button" type="button" name="clear" value="Clear Rows"/>
	</p>
<?php } ?>