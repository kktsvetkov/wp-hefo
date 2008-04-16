<?php
/**
* "HeFo" WordPress Plugin
* 
* This plugin is designed to inject HTML snippets into the header and the
* footer of WordPress pages, in order to make them persistent across themes,
* and theme-independent.
*
* @version SVN: $Id: hefo.php 40918 2008-04-16 10:45:06Z Mrasnika $
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @link http://kaloyan.info/blog/wp-hefo/
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @internal prevent from direct calls
*/
if (!defined('ABSPATH')) {
	return ;
	}

/**
* @internal prevent from second inclusion
*/
if (!class_exists('hefo')) {

/////////////////////////////////////////////////////////////////////////////

/**
* "HeFo" WordPress Plugin
*/
Class hefo {

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Initiate
	*
	* All the calls to this class are static so this method acts up as
	* some sort of constructor. It is attaching the plugin to all the
	* hooks it requires, and if the plugin has been updated, runs the
	* install to upgrade it.
	*/
	function init() {

		// attach the handler
		//
		add_action('wp_head',
			array('hefo', 'wp_head'));
		add_action('wp_footer',
			array('hefo', 'wp_footer'));


		// attach to admin menu
		//
		if (is_admin()) {
			add_action('admin_menu',
				array('hefo', '_menu')
				);
			}
		
		// attach to plugin installation
		//
		register_activation_hook(
			__FILE__,
			array('hefo', 'install')
			);

		// plugin updated, upgrade it
		//
		if (version_compare(hefo::version(), get_option('hefo_version')) > 0) {
			hefo::install();
			}
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Inject the wp_head related snippets
	* @return string
	*/
	function wp_head() {
		return hefo::heforize(__FUNCTION__);
		}

	/**
	* Inject the wp_footer related snippets
	* @return string
	*/
	function wp_footer() {
		return hefo::heforize(__FUNCTION__);
		}

	/**
	* Inject the wp_footer related snippets
	* @return string
	*/
	function heforize($tag) {
		$hefo_settings = (array) get_option('hefo_settings');
		if (isset($hefo_settings['snippets'][$tag])) {
			echo $hefo_settings['snippets'][$tag];
			}
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Performs the routines required at plugin installation
	*/	
	function install() {

		// settings
		//
		$hefo_settings = array(
			'snippets' => array(
				'wp_head' => '',
				'wp_footer' => '',
				)
			);
		
		if ($old_hefo_settings = get_option('hefo_settings')) {
			update_option(
				'hefo_settings', array_merge(
					$hefo_settings,
					$old_hefo_settings
					)
				);
			} else {
			add_option(
				'hefo_settings', $hefo_settings
				);
			}

		// version
		//
		if (get_option('hefo_version')) {
			update_option(
				'hefo_version', hefo::version()
				);
			} else {
			add_option(
				'hefo_version', hefo::version(), ' ', 'no'
				);
			}
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
	
	/**
	* Attach the menu page to the `Options` tab
	*/
	function _menu() {
		add_submenu_page('themes.php',
			 'HeFo: Header & Footer',
			 'Header & Footer', 8,
			 __FILE__,
			 array('hefo', 'menu')
			);
		}
		
	/**
	* Handles and renders the menu page
	*/
	function menu() {

		// sanitize referrer
		//
		$_SERVER['HTTP_REFERER'] = preg_replace(
			'~&saved=.*$~Uis','', $_SERVER['HTTP_REFERER']
			);
		
		// information updated ?
		//
		if ($_POST['submit']) {

			$_ = $_POST['hefo_settings'];
			$_['snippets'] = array_map('stripCSlashes', $_['snippets']);
			
			// save
			//
			update_option(
				'hefo_settings',
				$_
				);

			die("<script>document.location.href = '{$_SERVER['HTTP_REFERER']}&saved=settings:" . time() . "';</script>");
			}

		// operation report detected
		//
		if (@$_GET['saved']) {
			
			list($saved, $ts) = explode(':', $_GET['saved']);
			if (time() - $ts < 10) {
				echo '<div class="updated"><p>';
	
				switch ($saved) {
					case 'settings' :
						echo 'Settings saved.';
						break;
					}
	
				echo '</p></div>';
				}
			}

		// read the settings
		//
		$hefo_settings = (array) get_option('hefo_settings');

?>
<div class="wrap">
	<h2>HeFo: Header &amp; Footer</h2>
	<p>
	This plugin is designed to help you inject portions of HTML code (or as 
	we call them "HTML snippets") into your blog without having to modify 
	the theme you are using.
	</p>

	<form method="post">

		<label for="wp_head_html">Header:</label><br/>
		<textarea name="hefo_settings[snippets][wp_head]" style="width:90%; height:120px;"
			id="wp_head_html"><?php echo $hefo_settings['snippets']['wp_head']; ?></textarea><br/><br/>

		<label for="wp_footer_html">Footer:</label><br/>
		<textarea name="hefo_settings[snippets][wp_footer]" style="width:90%; height:120px;"
			id="wp_footer_html"><?php echo $hefo_settings['snippets']['wp_footer']; ?></textarea>

		<p class="submit" style="text-align:left;"><input type="submit" name="submit" value="Update &raquo;" /></p>
	</form>
</div>
<?php
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* Get the version of the plugin
	* @access public
	*/
	Function version() {
		if (preg_match('~Version\:\s*(.*)\s*~i', file_get_contents(__FILE__), $R)) {
			return trim($R[1]);
			}
		return '$Rev: 40918 $';
		}
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	//--end-of-class
	}

}

/////////////////////////////////////////////////////////////////////////////

/**
* Initiating the plugin...
* @see hefo
*/
hefo::init();

/////////////////////////////////////////////////////////////////////////////

/*
Plugin Name: HeFo
Plugin URI: http://kaloyan.info/blog/wp-hefo/
Description: This plugin is designed to inject HTML snippets into the header and the footer of WordPress pages, in order to make them persistent across themes, and theme-independent.
Author: Kaloyan K. Tsvetkov
Version: 0.2
Author URI: http://kaloyan.info/
*/

?>