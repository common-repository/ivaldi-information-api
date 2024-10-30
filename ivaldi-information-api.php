<?php
/*
Plugin Name: Ivaldi Information API
Description: A plugin to retrieve WordPress-site information
Version: 0.1
Author: Dimitri Snijder, Ivaldi
Author URI: http://ivaldi.nl/
*/

add_action('init', 'iia_start');
add_action('activity_box_end', 'iia_set_login_session');
add_action('admin_menu', 'iia_plugin_menu');


function iia_create_table() {

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	global $wpdb;
	$stats_table_name = $wpdb->base_prefix . "user_stats";

	$sql = "CREATE TABLE ". $stats_table_name ." (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
		PRIMARY KEY  (id),
		date DATE NOT NULL,
		count INT(11) NOT NULL
		)
	DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

	dbDelta( $sql );

	if(get_option('iia_hash') === FALSE) {
		/* no hash exists, create one */
		update_option('hash', iia_rand_sha1(20));
	}
}


register_activation_hook(__FILE__, 'iia_create_table');


function iia_start() {

	$site_title = get_bloginfo('name');
	$site_version = get_bloginfo('version');
	$title = get_bloginfo('url');
	$code = get_option('hash');

	if(isset($_GET['hash'])) {
		if($_GET['hash'] == $code){
			$array['site'] = array('title' => $site_title, 'wp_version' => $site_version);
			$array['plugins'] = iia_get_plugin_version();
			$array['themes'] = iia_themes();
			$array['logins'] = iia_get_logins();
			$array['comments'] = iia_get_comments();

			echo json_encode($array);
			die;
		}
	}
}


function iia_themes() {

	$site_version = get_bloginfo('version');

	if($site_version >= 3.4) {
		foreach(wp_get_themes() as $theme) {
			$array[] = array('theme_name' => $theme->get('Name'), 'local_ver' => $theme->get('Version'));
		}
	} 
	else {
		foreach(get_themes() as $theme) {
			$array[] = array('theme_name' => $theme['Name'], 'local_ver' => $theme['Version']);
		}
	}

	return $array;
}


function iia_get_plugin_version() {

	require_once(ABSPATH . 'wp-admin/includes/plugin.php');

	$i = 0;
	$allData = array();

	foreach (array_keys(get_plugins()) as $plugin) {

		$plugin_data = get_plugin_data($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/' . $plugin);
		$plugin_version = $plugin_data['Version'];
		$plugin_name = $plugin_data['Name'];

		if($plugin_name != 'Ivaldi Information API') {
			$allData[] = array('plugin_name' => $plugin_name, 'local_ver' => $plugin_version);
		}

	}

	return $allData;
}

function iia_set_login_session() {
	session_start();

	if(!isset($_SESSION['loggedin'])) {
		$_SESSION['loggedin'] = true;
		iia_update_logins();
	}
}


function iia_update_logins() {

	global $wpdb;

	$stats_table_name = $wpdb->base_prefix . "user_stats";
	$now = date('Y-m-d', time());
	$row = $wpdb->get_row("SELECT * FROM {$stats_table_name} WHERE date = '$now'");

	if($row) {

		$result = $wpdb->update(
			$stats_table_name,
			array( 'count' => $row->count+1 ),
			array( 'date' => $now ),
			array( '%s','%d'), 
			array( '%s' ) 
		);
	}
	else {

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$stats_table_name}
				(date, count)
				VALUES (%s, %d)",
				$now, 1
			)
		);
	}

}


function iia_get_logins() {

	global $wpdb;

	$stats_table_name = $wpdb->base_prefix . "user_stats";
	$day = $wpdb->get_var("SELECT sum(count) FROM {$stats_table_name} WHERE date = CURRENT_DATE");
	$week = $wpdb->get_var("SELECT sum(count) FROM {$stats_table_name} WHERE date >= CURRENT_DATE - INTERVAL 7 DAY");
	$month = $wpdb->get_var("SELECT sum(count) FROM {$stats_table_name} WHERE date >= CURRENT_DATE - INTERVAL 1 MONTH");
	$year = $wpdb->get_results("SELECT date, count FROM {$stats_table_name} WHERE date >= CURRENT_DATE - INTERVAL 1 YEAR");

	foreach ($year as $key => $value) {
		$logins[] = array('date' => $value->date, 'count' => $value->count);
	}

	return $logins;

}


function iia_get_comments() {

	$comments = get_comments('status=hold');

	foreach($comments as $comment) {
		$array[] = array('date' => $comment->comment_date, 'author' => $comment->comment_author, 'email' => $comment->comment_author_email);
	}

	return $array;
}


function iia_plugin_menu() {

	add_options_page('Ivaldi Information API settings', 'Ivaldi Information API', 'administrator', __FILE__, 'iia_settings_page', plugins_url( 'images/small-logo.png' , __FILE__ ));
}


function iia_settings_page() {
	$siteurl = get_option('siteurl');
    $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/style.css';
    echo "<link rel='stylesheet' type='text/css' href='$url' />";
	?>

	<div class="wrap">
		<a href="http://ivaldi.nl/" target="_blank"><div class="ivaldi-logo"></div></a>
		<h2 class="title">Ivaldi Information API</h2>

		<div class="hash">
			<h2>Code</h2>	
			<p>You can retrieve the information by appending the following code to your website URL.</p>
			<span class="code">?hash=<?php echo get_option('hash'); ?></span>
		</div>
	</div>

	<?php
}


function iia_rand_sha1($length) {

	$max = ceil($length / 40);
	$random = '';
	for ($i = 0; $i < $max; $i ++) {
		$random .= sha1(microtime(true).mt_rand(10000,90000));
	}
	return substr($random, 0, $length);
}


?>
