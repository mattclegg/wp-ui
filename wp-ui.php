<?php
/*
Plugin Name: WP UI - Tabs, accordions and more. 
Plugin URI: http://kav.in/wp-ui-for-wordpress
Description: Easily add Tabs, Accordion, Collapsibles to your posts. With 14 fresh Unique CSS3 styles and multiple jQuery UI custom themes.
Author:	Kavin
Version: 0.5.6
Author URI: http://kav.in

Copyright 2011 Kavin (http://kav.in/contact)

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
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


if ( function_exists( 'shortcode_unautop' ) ) {
	add_filter( 'the_editor_content', 'shortcode_unautop' );
	add_filter( 'the_content', 'shortcode_unautop' );
}

add_filter( 'widget_text', 'do_shortcode');


// Textdomain constant 
define( 'WPPTD' , 'wp-ui');

// $opts = get_option( 'wpUI_options');
// echo '<pre>';
// echo '</pre>';
// 
// echo $opts['jqui_custom_themes'];

$wpuiver = '0.5.3';

$wp_ui = new wpUI;

class wpUI {

	private $plugin_details, $options;

	
	public function __construct() {
		$this->wpUI();
	} // END fn __construct.


	public function wpUI() {
		
		// Register the default options on activation.
		register_activation_hook( __FILE__ , array(&$this, 'set_defaults'));

		// Output the plugin scripts and styles.
		add_action('wp_print_scripts', array(&$this, 'plugin_viewer_scripts'));
		
		add_action('wp_print_styles', array(&$this, 'plugin_viewer_styles'));


		// Load the admin scripts and styles.
		if ( is_admin() )
			add_action('admin_print_styles', array(&$this, 'admin_scripts_styles'));
			add_action('admin_print_styles', array(&$this, 'admin_styles'));
	
		// Translation.
		add_action('init', array(&$this, 'load_plugin_textdomain'));
		add_action('init', array(&$this, 'wpui_tackle_conflicts'));

		// Custom CSS query.
		add_filter( 'query_vars', array( &$this, 'wpui_add_query') );
		add_action( 'template_redirect', array( &$this, 'wpui_custom_css') );		
	
		// Shortcodes.
		add_shortcode('wptabs', array(&$this, 'sc_wptabs'));
		add_shortcode( 'wptabtitle', array(&$this, 'sc_wptabtitle'));
		add_shortcode( 'wptabcontent', array(&$this, 'sc_wptabcontent'));
		add_shortcode( 'wpspoiler', array(&$this, 'sc_wpspoiler'));
		add_shortcode( 'wpdialog', array(&$this, 'sc_wpdialog'));

		// alternative shortcodes.
		add_shortcode( 'tabs', array(&$this, 'sc_wptabs'));
		add_shortcode( 'tabname', array(&$this, 'sc_wptabtitle'));
		add_shortcode( 'tabcont', array(&$this, 'sc_wptabcontent'));
		add_shortcode( 'wslider', array(&$this, 'sc_wpspoiler'));
		add_shortcode( 'wslider', array(&$this, 'sc_wpspoiler'));

	
		/**
		 *  Insert the editor buttons and help panels.
		 */
		include_once( 'js/wpuimce/wptabs_mce.php' );
		
		
		/**
		 * 	WP UI options module and the page.
		 */
		require_once('admin/wpUI-options.php');

		// Get the options.
		$this->options = get_option('wpUI_options');
		
	} //END method wpUI
	
	/**
	 * 	Load the wpUI text domain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( WPPTD, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );		
	}

	public function plugin_viewer_scripts() {
		$plugin_url = get_option("url") . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__));

		if ( ! is_admin() && ! isset($this->options['jquery_disabled'] ) ) {
			wp_deregister_script( 'jquery' );
			
			wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js');
			
			wp_enqueue_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js');
					
			wp_enqueue_script('jquery-easing', $plugin_url . '/js/jquery.easing.1.3.js');
		}
		
		wp_enqueue_script( 'wp-ui-min', $plugin_url . '/js/wp-ui.js');
		wp_localize_script( 'wp-ui-min', 'wpUIOpts', array(
			'wpUrl'           =>	get_bloginfo('url'),
			'pluginUrl'       =>	plugins_url('/wp-ui/'),
			'enableTabs'      =>	isset($this->options['enable_tabs']) ? $this->options['enable_tabs'] : '',
			'enableAccordion' =>	isset($this->options['enable_accordion']) ? $this->options['enable_accordion'] : '',
			'enableSpoilers'  =>	isset($this->options['enable_spoilers']) ?	$this->options['enable_spoilers'] : '' ,	
			'enableDialogs'	  =>	isset($this->options['enable_dialogs']) ?	$this->options['enable_dialogs'] : '' ,	
			'tabsEffect'      =>	isset($this->options['tabsfx']) ? $this->options['tabsfx'] : '',
			'effectSpeed'     =>	isset($this->options['fx_speed']) ? $this->options['fx_speed'] : '',
			'accordEffect'    =>	isset($this->options['tabsfx']) ? $this->options['tabsfx'] : '',
			'alwaysRotate'    =>	isset($this->options['tabs_rotate']) ? $this->options['tabs_rotate'] : '',
			'tabsEvent'  	  =>	isset($this->options['tabs_event']) ? $this->options['tabs_event'] : '',
			'accordEvent'  	  =>	isset($this->options['accord_event']) ? $this->options['accord_event'] : '',
			'topNav'          =>	isset($this->options['topnav']) ? $this->options['topnav'] : '',
			'accordAutoHeight'=>	isset($this->options['accord_autoheight']) ? $this->options['accord_autoheight'] : '',
			'accordCollapsible'=>	isset($this->options['accord_collapsible']) ? $this->options['accord_collapsible'] : '',
			'accordEasing'		=>	isset( $this->options['accord_easing'] ) ? $this->options['accord_easing'] : '',
			'bottomNav'       =>	isset($this->options['bottomnav']) ? $this->options['bottomnav'] : '',
			'tabPrevText'     =>	isset($this->options['tab_nav_prev_text']) ? $this->options['tab_nav_prev_text'] : '',
			'tabNextText'     =>	isset($this->options['tab_nav_next_text']) ? $this->options['tab_nav_next_text'] : '',
			'spoilerShowText' =>	isset($this->options['spoiler_show_text']) ? $this->options['spoiler_show_text'] : '',
			'spoilerHideText' =>	isset($this->options['spoiler_hide_text']) ? $this->options['spoiler_hide_text'] : '',
			"cookies"			=>	isset( $this->options['use_cookies'] ) ? $this->options['use_cookies'] : '',
			"hashChange"		=> isset( $this->options['linking_history'] ) ? $this->options['linking_history'] : ''
		));


		if ( ! is_admin() ) {
			wp_enqueue_script('wpui-init', $plugin_url . '/js/init.js');
			wp_localize_script('wpui-init' , 'initOpts', array(
				'wpUrl'				=>	get_bloginfo('url'),
				'pluginUrl' 		=>	plugins_url('/wp-ui/'),
				'queryVars1'	=>	add_query_arg( array(
					 	'action' => 'WPUIstyles',
					 	'height' => '200',
					 	'width' => '300'
					 ), 'admin-ajax.php' )	
			));
		} // END if ! is _admin() for init.js.
		
	}
	
	/**
	 * 	Output the plugin styles.
	 */
	public function plugin_viewer_styles() {

		global $is_IE;
		$plugin_url = plugins_url('/wp-ui/');
		
		/**
		 * 	Check for the style file under the CSS directory and load it. 
		 * 	If absent, check for the jQuery UI theme style and load.
		 */
		if ( file_exists( plugin_dir_path( __FILE__ )
		 			. 'css/' . $this->options['tab_scheme']  . '.css' )) {
			
			// Main tab, accordion, spoiler layout.
			wp_enqueue_style('wp-tabs-css', $plugin_url . '/wp-ui.css');
			
			if ( 
				$is_IE &&
				 $this->options['enable_ie_grad'] && 
				 @file_exists( plugin_dir_path( __FILE__ )
				 	. 'css/' . $this->options['tab_scheme']  . '-ie.css' )
				)
				wp_enqueue_style(
					'wp-tabs-IE-css-bundled-' . $this->options['tab_scheme'] ,
				 	$plugin_url . 'css/' . $this->options['tab_scheme'] . '-ie.css'
				);
				
		} else {
			// Sets the standard font size for jQuery UI themes, to ensure compat with diff themes. 
			wp_enqueue_style( 'jquery-ui-wp-fix', $plugin_url . 'css/jquery-ui-wp-fix.css' );
			
			// Load the jQuery UI theme from the Google CDN.
			wp_enqueue_style( 'jquery-ui-css-' . $this->options['tab_scheme'] , 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/themes/' . $this->options['tab_scheme'] . '/jquery.ui.all.css');
			
		} // END if ( file_exists ... ) ( Check for css stylesheet)


		/**
		 * 	Load jQuery UI custom themes.
		 */
		if ( isset( $this->options['jqui_custom_themes'] ) && $this->options['jqui_custom_themes'] != '' ) {
			$jquithms = json_decode( $this->options[ 'jqui_custom_themes'] , true );
			foreach( $jquithms as $key=>$val ) {
				wp_enqueue_style( $key, $val );		
			}
		}

	
		/**
		 *	Load the additional CSS, if any has been input on the options page.		
		 */
		if ( $this->options['custom_css'] != '' )
			wp_enqueue_style( 'wpui-custom-css', get_bloginfo( 'url' ) . '/?wpui-query=css');
		
		/**
		 * 	Load all the styles 
		 * 
		 * 	This combines all the custom styles into a single file. For using multiple skins on 
		 * 	the same page, and people who donot want to load gazillion separate stylesheets.
		 * 
		 */
		if ( $this->options['load_all_styles'] ) {
			wp_enqueue_style( 'wp-tabs-css-bundled-all' , $plugin_url . 'css/wpui-all.css');
			if ( $is_IE && $this->options['enable_ie_grad'] )
			wp_enqueue_style( 'wp-tabs-css-bundled-all-IE' , $plugin_url . 'css/wpui-all-ie.css');
		}
		
		// Try a jQuery UI theme.
		// wp_enqueue_style( 'jquery-ui-css-flick' , 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/themes/ui-darkness/jquery.ui.all.css');
		
	} // END method plugin_viewer_styles()
	
	
	
	/**
	 * 	Scripts and styles for the options page.
	 */
	public function admin_scripts_styles() {
		global $wp_version;
		$plugin_url = plugins_url('/wp-ui/');
		
		
		// Use the bundled jQuery.
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-dialog' );

		// wp_enqueue_script( 'jquery-color' );
		// wp_enqueue_script( 'jquery-ui-effects' , $plugin_url . 'js/ui-effects.js');


		if ( ( isset($_GET['page']) && $_GET['page'] == 'wpUI-options' )) {
				
			// Load newer jQuery for older versions. Will be removed in WP UI 1.0. 
			// if ( version_compare( $wp_version, '3.0', '<' ) ) {	
				wp_deregister_script( 'jquery' );
				wp_deregister_script( 'jquery-ui-tabs' );
				wp_deregister_script( 'jquery-ui-dialog' );
				// wp_deregister_script( 'jquery-color' );
			 		wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js');
					wp_enqueue_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js');
			// }

			wp_enqueue_script( 'admin_wp_ui' , $plugin_url . 'js/admin.js');
			wp_localize_script('admin_wp_ui' , 'initOpts', array(
				'wpUrl'				=>	get_bloginfo('url'),
				'pluginUrl' 		=>	plugins_url('/wp-ui/'),
				'queryVars1'	=>	add_query_arg( array(
					 	'action' => 'WPUIstyles',
					 	'height' => '200',
					 	'width' => '300'
					 ), 'admin-ajax.php' ),
					
				'queryVars2'	=>	add_query_arg( array(
					 	'action' => 'jqui_custom_css',
					 ), 'admin-ajax.php' )
				));
			
		wp_enqueue_script( 'admin_jq_ui' , $plugin_url . 'js/jqui-admin.js');

		} // end the $_GET page conditional.

		// Load the thickbox scripts, styles and media-upload.
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
		wp_print_scripts('media-upload');

		// Editor buttons and JS vars.
		wp_enqueue_script('editor');
		wp_localize_script( 'editor', 'pluginVars', array(
			'wpUrl'		=>	get_bloginfo('url'),
			'pluginUrl'	=>	$plugin_url,
			'tmceURL'	=>	get_bloginfo( 'url' ) . '/wp-includes/js/tinymce/',
			'queryVars1'	=>	add_query_arg( array( 'action' => 'tabtitlehelp', 'height' => '200', 'width' => '300' ), 'admin-ajax.php' )
		));


	} // END method admin_scripts_styles


	function admin_styles() {
		$plugin_url = plugins_url('/wp-ui/');
		
		// Load the css on options page.
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wpUI-options' ) {
			wp_enqueue_style('wp-tabs-admin-js', $plugin_url . '/css/admin.css');
			// wp_enqueue_style('wp-admin-jqui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/themes/smoothness/jquery.ui.all.css');
		}		
	}


	/**
	 * 	Add buttons to wp-ui options page's editors.
	 */
	function add_mce_buttons($buttons) {
		if ( isset($GET['page']) && $_GET['page'] == 'wpUI-options')
			array_push( $buttons, 'seperator', 'image', 'forecolorpicker', 'backcolorpicker');
		return $buttons;		
	} // END function add_mce_buttons
	


	/**
	 * 	Set the defaults on plugin activation.
	 */
	function set_defaults() {
		// First install.
		if ( ! $this->options ) {
			$defaults = get_wpui_default_options();
			update_option( 'wpUI_options', $defaults );
		} else {
			// Append the new options.
			$oldopts = get_option( 'wpUI_options' );
			$newdefs = get_wpui_default_options();
			$updateopts = array_merge( $newdefs , $oldopts );
			update_option( 'wpUI_options', $updateopts );
		} // End if ( !this->options )
	} 
	
	

	// =======================
	// = Add the shortcodes. =
	// =======================

	/**
	 * 	[wptabs] shortcode.	
	 */
	function sc_wptabs( $atts, $content = null) {
		extract(shortcode_atts(array(
			"type"		=>	'tabs',
			'style'		=>	$this->options['tab_scheme'],
			'effect'	=>	$this->options['tabsfx'],
			'speed'		=>	'600',
			// Tabs only options below
			'rotate'	=>	'', 
			'position'	=>	'top'
		), $atts));
		
		$output  = '';

		$jqui_cust = isset( $this->options[ 'jqui_custom_themes' ] ) ? json_decode( $this->options[ 'jqui_custom_themes' ] , true ) : array();
		
		if ( stristr( $style, 'wpui-' )	&& ! array_key_exists( $style, $jqui_cust ) ) {
			$style .= ' wpui-styles';
		} else {
			$style .= ' jqui-styles';
		}
		
		//  
		// if( array_key_exists( $style , $jqui_cust ) ) {
		// 	$style .= ' jqui-styles';
		// }
		
		// Default : tabs. Change type for accordion.
		$class  = ($type == 'accordion') ? 'wp-accordion' : 'wp-tabs';
		$class .= ' ' . $style;
		$class .= ( $rotate == '' ) ? '' : ' tab-rotate-' . $rotate;
		$class .= ( $position == 'bottom' ) ? ' tabs-bottom' : '';

	
		$output .= '<div class="' . $class . '">' . do_shortcode($content) . '</div><!-- end div.wp-tabs -->';
		return $output;
	} // END function sc_wptabs.
	
	
	/**
	 * 	[wptabtitle]
	 */
	function sc_wptabtitle( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'header'	=>	'h3',
			'tablabel'	=>	'',
			'load'	=>	''
		), $atts));
		
		// Check if the tab's content is to be loaded thro AJAX.
		if ( $load != '' ) {
			$output  = '<' . $header . ' class="wp-tab-title">';
			$output .= '<a class="wp-tab-load" href="' . $load . '">';
			$output .= do_shortcode($content);
			$output .= '</a>';
			$output .= '</' . $header . '>';
		} else {
			
			
			
			$output = '<' . $header . ' class="wp-tab-title">' . do_shortcode( __( $content ) ) . '</' . $header . '>';
		}
		
		return $output;
	} // END function sc_wptabtitle
	
	/**
	 * 	[wptabcontent]
	 */
	function sc_wptabcontent( $atts, $content = null ) {
		extract( shortcode_atts( array( 
				'class'	=>	''
			), $atts));
			
			return '<div class="wp-tab-content">' . do_shortcode($content) . '</div><!-- end div.wp-tab-content -->';
			
	} // END function sc_wptabcontent


	/**
	 * 	Spoilers/Collapsibles/Sliders. 
	 * 
	 * 	[wpspoiler name="NAME"]
	 */
	function sc_wpspoiler( $atts, $content = null ) {
		extract( shortcode_atts( array( 
				'name'		=>	'Show Content',
				'style'		=>	$this->options['tab_scheme'],
				'fade'		=>	'true',
				'slide'		=>	'true',
				'speed'		=>	false,
				'showText'	=>	'Click to show',
				'hideText'	=>	'Click to hide',
				'open'		=>	'false'
				
			), $atts));
			
			$h3class  = '';
			$h3class .= ( $fade == 'true' ) ? ' fade-true' : ' fade-false'; 
			$h3class .= ( $slide == 'true' ) ? ' slide-true' : ' slide-false';
			$h3class .= ( $open == 'true' ) ? ' open-true' : ' open-false';
			
			$h3class .= ( $speed ) ? ' speed-' . $speed : '';
			
			return '<div class="wp-spoiler ' . $style . '"><h3 class="ui-collapsible-header' . $h3class . '"><span class="ui-icon"></span>' .$name . '</h3><div class="ui-collapsible-content">'  . do_shortcode($content) . '</div></div><!-- end div.wp-spoiler -->';
	} // END function sc_wptabcontent


	/**
	 * 	Dialogs
	 * 	
	 * 	[wpdialog]Stuff you wanna say[/wpdialog]
	 */
	function sc_wpdialog( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'style'			=>	'',
			'autoOpen'		=>	'true',
			'openlabel'		=>	'Show Information',
			'title'			=>	'Information',
			'height'		=>	'auto',
			'width'			=>	'300',
			'show'			=>	'slide',
			'hide'			=>	'fade',
			'modal'			=>	'false',
			'closeOnEscape'	=>	'1',
			'position'		=>	'center',
			'zIndex'		=>	'1000',
			'button'		=>	false
		), $atts ) );
		
		$args = '';

		if ( $style ) $args .= ' wpui-dialogClass:' . $style . '-arg';
		
		if ( $width ) $args .= ' wpui-width:' . $width . '-arg';
		if ( $height ) $args .= ' wpui-height:' . $height . '-arg';
		$args .= ' wpui-autoOpen:' . $autoOpen . '-arg';
		if ( $show ) $args .= ' wpui-show:' . $show . '-arg';
		if ( $hide ) $args .= ' wpui-hide:' . $hide . '-arg';
		if ( $modal ) $args .= ' wpui-modal:' . $modal . '-arg';
		$args .= ' wpui-closeOnEscape:' . $closeOnEscape . '-arg';
		if ( $position ) $args .= ' wpui-position:' . $position . '-arg';
		if ( $zIndex ) $args .= ' wpui-zIndex:' . $zIndex . '-arg';
		if ( $button ) {
			$button = str_ireplace( ' ', '*_*', $button );
			$args .= ' wpui-button:' . $button . '-arg';
		}

		$output = '';

		// if ( ! $autoOpen ) {
		// 	$output .= '<a href="#" class="ui-button dialog-opener">' . $openlabel . '</a>';
		// 	$output .= '<div class="wp-dialog" style="display: none;">';
		// } else {		
			$output .= '<div class="wp-dialog ' . $style . '" title="' . $title . '">';
		// }
		$output .= '<h4 class="wp-dialog-title ' . $args . '"></h4>';
		$output .= $content . '</div><!-- end .wp-dialog -->';
		
		
		return $output;
				
		
		
	} // END method sc_wpdialog	

	
	/**
	 * 	Try to solve the conflicts.
	 */
	function wpui_tackle_conflicts() {
		// if ( wp_script_is( 'thickbox', 'queue') ||  wp_script_is( 'thickbox', 'done')) 
		wp_enqueue_script('thickbox_fix', plugins_url( 'wp-ui/js/fix_tb.js' ) , array('thickbox'), '0.2', true );
	} // END method wpui_tackle_Conflicts


	/**
	 * 	Add the wpui-query GET var 
	 */
	function wpui_add_query( $query_vars )
	{
		$query_vars[] = 'wpui-query';
		return $query_vars;
	} // END function wpui_add_query


	/**
	 * 	Output the custom css if any.
	 */
	function wpui_custom_css()
	{
		$query = get_query_var( 'wpui-query' );
		if ( 'css' == $query ) {
			// include_once( 'css/css.php');
			header( 'Content-type: text/css' );
			header( 'Cache-Control: must-revalidate' );
			$offset = 72000;
			header( "Expires: " . gmdate( "D, d M Y H:i:s", time() + 72000) . " GMT");
			$opts = get_option( 'wpUi_options' );
			echo $opts['custom_css'];
			exit; // Dont remove.
		}
		
	} // END function wpui_custom_css

	
} // end class WP_UI



$upload_dir = wp_upload_dir();
$jqdir = preg_replace( '/(\d){4}\/(\d){2}/i' , '' , $upload_dir['path'] ) . 'wp-ui/';



function wpui_jqui_dirs( $dir, $format='array' ) {
	$valid = array();
	if ( ! is_dir( $dir ) )
		return "NO_DIR ::::: $dir";
		
	$it = new DirectoryIterator( $dir );
	
	$abspath = ABSPATH;
	
	$i = 0;
	foreach( $it as $fi ) {
		if ( $fi->isDir() &&
		 	! $fi->isDot() )
		  {
		
		$itt = new DirectoryIterator( $fi->getPathname() );

			foreach( $itt as $fii ) {
				if ( $fii->isFile() ) {
					if( 'css' == substr( $fii->getFilename() , -3 ) ) {
						$valid[ $fi->getBasename() ] = $fii->getPathName();
						$i++;
					}
				}
			}
			$i++;
		}
	}
	ksort( $valid );
	foreach( $valid as $key=>$value ) {
		$valid[ $key ] = get_bloginfo('wpurl') . '/' . str_ireplace( ABSPATH, '', $value );
	}
	
	if ( empty( $valid ) ) {
		return "EMPTY_DIR ::::: " . $dir;
	} else {
		return json_encode( $valid );
	}
	
	// if ( $format == 'array' ) {
	// 	return $valid;
	// } else {
	// }		
} // END update CSS dirs.




?>