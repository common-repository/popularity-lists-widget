<?php
/*
Plugin Name: Popularity Lists Widget
Description: Popularity Lists Widget is a wordPress widget, Operates by using "Popularity Contest" Plugin. And, a popular article is output as a list. 
Author: Tomoya Otake
Version: 1.1
Author URI: http://www.jaco-bass.com/
Plugin URI: http://www.jaco-bass.com/blog/2007/10/popularity-lists-widget/
*/

//----------------------------------------------------------------------------
//MAIN WIDGET BODY
//----------------------------------------------------------------------------

// We're putting the plugin's functions in one big function we then
// call at 'plugins_loaded' (add_action() at bottom) to ensure the
// required Sidebar Widget functions are available.
function widget_popularity_lists_init() {

	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return; // ...and if not, exit gracefully from the script.

	// This function prints the sidebar widget--the cool stuff!
	function widget_popularity_lists($args, $number = 1) {

		// $args is an array of strings which help your widget
		// conform to the active theme: before_widget, before_title,
		// after_widget, and after_title are the array keys.
		extract($args);

		// Collect our widget's options, or define their defaults.
		$options = get_option('widget_popularity_lists');

		$title = empty($options[$number]['title']) ? __('Popular Posts') : $options[$number]['title'];
		$b = empty($options[$number]['b']) ? '<li>' : $options[$number]['b'];
		$a = empty($options[$number]['a']) ? '</li>' : $options[$number]['a'];
		if ( !$l = (int) $options[$number]['l'] )
			$l = 10;
		else if ( $l < 1 )
			$l = 1;
		else if ( $l > 15 )
			$l = 15;

?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<ul>
			<?php echo akpc_most_popular($limit = $l, $before = $b, $after= $a ); ?>
			</ul>
		<?php echo $after_widget; ?>
<?php
	}

	echo $after_widget;

	function widget_popularity_lists_control($number) {

		// Collect our widget's options.
		$options = $newoptions = get_option('widget_popularity_lists');

		// This is for handing the control form submission.
		if ( $_POST["popularity-lists-submit-$number"] ) {
			$newoptions[$number]['title'] = strip_tags(stripslashes($_POST["popularity-lists-title-$number"]));
			$newoptions[$number]['b'] = stripslashes($_POST["popularity-lists-b-$number"]);
			$newoptions[$number]['a'] = stripslashes($_POST["popularity-lists-a-$number"]);
			$newoptions[$number]['l'] = (int) ($_POST["popularity-lists-l-$number"]);
		}

		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_popularity_lists', $options);
		}

		$title = attribute_escape($options[$number]['title']);
		$b = $options[$number]['b'];
		$a = $options[$number]['a'];
		if ( !$l = (int) $options[$number]['l'] )
			$l = 5;

// The HTML below is the control form for editing options.

?>

		<p><label for="popularity-lists-title-<?php echo "$number"; ?>">
			<?php _e( 'Title:' ); ?> <input style="width:300px" id="popularity-lists-title-<?php echo "$number"; ?>" name="popularity-lists-title-<?php echo "$number"; ?>" type="text" value="<?php echo $title; ?>" />
		</label></p>

		<p><label for="popularity-lists-l-<?php echo "$number"; ?>"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="popularity-lists-l-<?php echo "$number"; ?>" name="popularity-lists-l-<?php echo "$number"; ?>" type="text" value="<?php echo $l; ?>" /></label> <?php _e('(at most 15)'); ?></p>

		<p><label for="popularity-lists-b-<?php echo "$number"; ?>"><?php _e('Before list is placed:'); ?> <input style="width: 60px; text-align: center;" id="popularity-lists-b-<?php echo "$number"; ?>" name="popularity-lists-b-<?php echo "$number"; ?>" type="text" value="<?php echo $b; ?>" /></label> <?php _e('(Default is &lt;li&gt;)'); ?></p>

		<p><label for="popularity-lists-a-<?php echo "$number"; ?>"><?php _e('After list is placed:'); ?> <input style="width: 60px; text-align: center;" id="popularity-lists-a-<?php echo "$number"; ?>" name="popularity-lists-a-<?php echo "$number"; ?>" type="text" value="<?php echo $a; ?>" /></label> <?php _e('(Default is &lt;/li&gt;)'); ?></p>

		<input type="hidden" id="popularity-lists-submit-<?php echo "$number"; ?>" name="popularity-lists-submit-<?php echo "$number"; ?>" value="1" />

<?php
	}
	// Tell Dynamic Sidebar about our new widget and its control
	widget_popularity_lists_register();
}
//----------------------------------------------------------------------------
//MULTIPLE WIDGET HANDLING
//----------------------------------------------------------------------------

function widget_popularity_lists_setup() {
	$options = $newoptions = get_option('widget_popularity_lists');
	if ( isset($_POST['popularity-lists-number-submit']) ) {
		$number = (int) $_POST['popularity-lists-number'];
		if ( $number > 9 ) $number = 9;
		if ( $number < 1 ) $number = 1;
		$newoptions['number'] = $number;
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_popularity_lists', $options);
		widget_popularity_lists_register($options['number']);
	}
}

function widget_popularity_lists_page() {
	$options = $newoptions = get_option('widget_popularity_lists');
?>
	<div class="wrap">
		<form method="POST">
		<h2>Popularity Lists Widgets</h2>
		<p style="line-height: 30px;"><?php _e('How many Popularity Lists widgets would you like?'); ?>
		<select id="popularity-lists-number" name="popularity-lists-number" value="<?php echo $options['number']; ?>">
<?php for ( $i = 1; $i < 10; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
		</select>
		<span class="submit"><input type="submit" name="popularity-lists-number-submit" id="popularity-lists-number-submit" value="<?php _e('Save'); ?>" /></span></p>
		</form>
	</div>
<?php
}

function widget_popularity_lists_register() {
	$options = get_option('widget_popularity_lists');
	$number = $options['number'];
	if ( $number < 1 ) $number = 1;
	if ( $number > 9 ) $number = 9;
	for ($i = 1; $i <= 9; $i++) {
		$name = array('Popularity Lists %s', null, $i);
		register_sidebar_widget($name, $i <= $number ? 'widget_popularity_lists' : /* unregister */ '', $i);
		register_widget_control($name, $i <= $number ? 'widget_popularity_lists_control' : /* unregister */ '', 400, 150, $i);
	}
	add_action('sidebar_admin_setup', 'widget_popularity_lists_setup');
	add_action('sidebar_admin_page', 'widget_popularity_lists_page');
}

//----------------------------------------------------------------------------
//HOOK IN
//----------------------------------------------------------------------------

// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('plugins_loaded', 'widget_popularity_lists_init');

?>
