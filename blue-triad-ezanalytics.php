<?php
/**
 * @package blue-triad-ezanalytics
 * @version 1.0
 */

/**
 * Plugin Name:         Blue Triad EZAnalytics
 * Plugin URI: https://www.bluetriad.com/wp-plugin
 * Description: Plugin for inserting the Google Universal Analytics code and user Tracking Id key into the header.
 * Author: John Ahlquist
 * Author URI:           http://www.bluetriad.com/wp-plugin-author
 *
 * Version:             1.0
 * Requires at least:   3.8
 * Tested up to:        4.9
 *
 * License:             GPL v3
 * 
 *
 * Blue Triad EZAnalytics
 * Copyright (C) 2018, Blue Triad, john@bluetriad.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category            Plugin
 * @copyright           Copyright © 2018 John Ahlquist
 * @author              John Ahlquist
 */


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function blue_triad_add_analytics() {
	$options = get_option( 'blue_triad_options' );
	$set = isset( $options[ 'blue_triad_field_GA2' ] );
	if ($set) {
		$UA = $options[ 'blue_triad_field_GA2' ];
	} else {
		$UA = '';
	};
	
	if ($UA && strlen($UA) > 0) {

		$UA = blue_triad_key_trim($UA);
		if (!blue_triad_validate_key($UA)) {
			echo "<!--  WARNING *BlueTriad EZAnalytics* invalid Google Analytics key  --> \n";
			return;
		}
	}

	if ($UA && strlen($UA) > 0) {
		$script =  "<!-- BEGIN *BlueTriad EZAnalytics* Global site tag (gtag.js) - Google Analytics -->\n"
		. '<script type="text/javascript" async="" src="https://www.google-analytics.com/analytics.js">' . "\n"
		. '</script>' . "\n"
		. '<script async="" src="https://www.googletagmanager.com/gtag/js?id='
		. $UA
		. '">' . "\n"
		. '</script>' . "\n"
		. '<script>' . "\n";

		echo $script;

		echo "  window.dataLayer = window.dataLayer || [];\n";
		echo "  function gtag(){dataLayer.push(arguments);}\n";
		echo "  gtag('js', new Date());\n";
		echo "  gtag('config', '";
		echo $UA;
		echo "');\n";
		echo "</script>\n";
		echo "<!-- END *BlueTriad* Global site tag (gtag.js) - Google Analytics -->\n";

	} else {
		echo "<!-- WARNING *BlueTriad EZAnalytics* Analytics key not set - Google Analytics --> \n";
	}
}

// Now we set that function up to execute when the wp_head action is called
add_action('wp_head', 'blue_triad_add_analytics', 1);
add_action( 'admin_head', 'blue_triad_add_analytics' );


?>

<?php
/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */
 
/**
 * custom option and settings
 */
function blue_triad_settings_init() {
	// register a new setting for 'BlueTriadGA' page
	register_setting( 'BlueTriadGA', 'blue_triad_options' );
	
	// register a new section in the 'BlueTriadGA' page
	add_settings_section(
	'blue_triad_section_developers',
	__( 'Google Analytics Account Info', 'BlueTriadGA' ),
	'blue_triad_section_developers_cb',
	'BlueTriadGA'
	);
	

	add_settings_field(
		'blue_triad_field_GA2', // as of WP 4.6 this value is used only internally
		// use $args' label_for to populate the id inside the callback
		__( 'Google Analytics Tracking ID Key Text', 'BlueTriadGA' ),
		'blue_triad_field_GA2_cb',
		'BlueTriadGA',
		'blue_triad_section_developers',
		[
		'label_for' => 'blue_triad_field_GA2',
		'class' => 'blue_triad_row',
		'blue_triad_custom_data' => 'custom',
		]
		);
}
 
/**
 * register our blue_triad_settings_init to the admin_init action hook
 */
add_action( 'admin_init', 'blue_triad_settings_init' );

function blue_triad_key_trim($key) {
	// removes spaces and quotes.	
	$key = str_replace(' ', '', $key);
	$key = str_replace('"', '', $key);
	$key = str_replace("'", '', $key);
	return $key;
}

function blue_triad_validate_key($key) { 
	return preg_match('/^ua-\d{4,9}-\d{1,4}$/i', strval($key));
}
 
/**
 * custom option and settings:
 * callback functions
 */
 
// developers section cb
 
// section callbacks can accept an $args parameter, which is an array.
// $args have the following keys defined: title, id, callback.
// the values are defined at the add_settings_section() function.
function blue_triad_section_developers_cb( $args ) {

	$host = $_SERVER['HTTP_HOST'];

	$uri = $_SERVER['REQUEST_URI'];
	// echo $uri . '<br/>'; // Outputs: URI
	
	$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	
	$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	// echo $url . '<br/>'; // Outputs: Full URL
	
	$query = $_SERVER['QUERY_STRING'];
	// echo $query.'<br/>'; // Outputs: Query String


	$local_test = false;
	if ($local_test) {
		$link = "https://isvr2.ahlquistsoftware.com/bt-wordpress-setup/";
		echo '<h1> !!!! USING DEBUG LOCAL ' . $link;
	} else {
		$link = "https://www.bluetriad.com/bt-wordpress-setup/";
	}

	$link = $link ."?bt_redirect=".urlencode($url);

	if (isset($_GET['bt_webid'])) {
		$webid = $_GET['bt_webid'];
		echo '<h2>Received Google Analytics Tracking ID from Blue Triad: ' . $webid . '</h2> <p>Click Save Settings to update.</p>';
		?>
		<p>After you Save Settings, Refresh the page several times to generate real time statistics, then Click below to return to the Blue Triad site. </p>
		<?php
		echo("\n". '<h2><a href="' . $link . '"> Return to Blue Triad site.</a><br/> </h2>' . "\n");

	} else {
		?>
		<p>If you are a member of the Blue Triad service, you may click below to go to the Blue Triad site to select your Google Analytics Tracking ID text.  Otherwise you may paste the Tracking ID key into the field below. </p>
		<?php
		echo("\n". '<h2><a href="' . $link . '"> Click to go to the Blue Triad site.</a><br/> </h2>' . "\n");
	}
}
 
 
// field callbacks can accept an $args parameter, which is an array.
// $args is defined at the add_settings_field() function.
// wordpress has magic interaction with the following keys: label_for, class.
// the "label_for" key value is used for the "for" attribute of the <label>.
// the "class" key value is used for the "class" attribute of the <tr> containing the field.
// you can add custom key value pairs to be used inside your callbacks.
	
   function blue_triad_field_GA2_cb( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'blue_triad_options' );
	// output the field
	$defaultValue = isset( $options[ $args['label_for'] ] ) ?  $options[ $args['label_for'] ] : '';
	
	if ( isset( $_GET['bt_webid'] ) ) {
		$defaultValue = $_GET['bt_webid'];
	}

	$defaultValue = blue_triad_key_trim($defaultValue);
	if (!blue_triad_validate_key($defaultValue) && $defaultValue) {
		echo '<h4>Invalid key format:' . $defaultValue . '</h4>';
	}
	
	?>

  	<input type="text" 
	id="<?php echo esc_attr( $args['label_for'] ); ?>"
	data-custom="<?php echo esc_attr( $args['blue_triad_custom_data'] ); ?>"
	name="blue_triad_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
	  value="<?php echo $defaultValue ?>"
	>
   
	<p class="description">
	<?php esc_html_e( 'UA-#########-#', 'BlueTriadGA' ); ?>
	</p>
	<?php
   }
	
   /**
 * top level menu
 */
function blue_triad_options_page() {
 // add top level menu page
 add_menu_page(
 'BlueTriad',
 'BlueTriad Options',
 'manage_options',
 'BlueTriadGA',
 'blue_triad_options_page_html'
 );
}
 
/**
 * register our blue_triad_options_page to the admin_menu action hook
 */
add_action( 'admin_menu', 'blue_triad_options_page' );
 
/**
 * top level menu:
 * callback functions
 */
function blue_triad_options_page_html() {
 // check user capabilities
 if ( ! current_user_can( 'manage_options' ) ) {
 return;
 }
 
 // add error/update messages
 
 // check if the user have submitted the settings
 // wordpress will add the "settings-updated" $_GET parameter to the url
 if ( isset( $_GET['settings-updated'] ) ) {
 // add settings saved message with the class of "updated"
 add_settings_error( 'blue_triad_messages', 'blue_triad_message', __( 'Settings Saved', 'BlueTriadGA' ), 'updated' );
 }
 
 // show error/update messages
 settings_errors( 'blue_triad_messages' );
 ?>
 <div class="wrap">
 <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
 <form action="options.php" method="post">
 <?php
 // output security fields for the registered setting 'BlueTriadGA'
 settings_fields( 'BlueTriadGA' );
 // output setting sections and their fields
 // (sections are registered for 'BlueTriadGA', each field is registered to a specific section)
 do_settings_sections( 'BlueTriadGA' );
 // output save settings button
 submit_button( 'Save Settings' );
 ?>
 </form>
 </div>
 <?php
}
