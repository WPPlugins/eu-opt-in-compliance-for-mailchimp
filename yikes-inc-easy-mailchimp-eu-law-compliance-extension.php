<?php
/**
 * 		Plugin Name:       	EU Opt-In Compliance for MailChimp
 * 		Plugin URI:       	http://www.yikesinc.com
 * 		Description:       	This add-on extends Easy Forms for MailChimp to allow MailChimp forms to comply with the EU Opt-In Directive.
 * 		Version:          	1.1.2
 * 		Author:            	YIKES, Inc.
 * 		Author URI:        	http://www.yikesinc.com
 * 		License:          	GPL-2.0+
 * 		License URI:       	http://www.gnu.org/licenses/gpl-2.0.txt
 * 		Text Domain:       	eu-opt-in-compliance-for-mailchimp
 * 		Domain Path:       	/languages
 *		
 * 		EU Opt-In Compliance for MailChimp is free software: you can redistribute it and/or modify
 * 		it under the terms of the GNU General Public License as published by
 * 		the Free Software Foundation, either version 2 of the License, or
 * 		any later version.
 *
 * 		EU Opt-In Compliance for MailChimp is distributed in the hope that it will be useful,
 * 		but WITHOUT ANY WARRANTY; without even the implied warranty of
 * 		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * 		GNU General Public License for more details.
 *
 * 		You should have received a copy of the GNU General Public License
 *		along with Easy Forms for MailChimp. If not, see <http://www.gnu.org/licenses/>.
 *
 *		We at Yikes Inc. embrace the open source philosophy on a daily basis. We donate company time back to the WordPress project,
 *		and constantly strive to improve the WordPress project and community as a whole. We eat, sleep and breath WordPress.
 *
 *		"'Free software' is a matter of liberty, not price. To understand the concept, you should think of 'free' as in 'free speech,' not as in 'free beer'."
 *		- Richard Stallman
 *
**/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// must include plugin.php to use is_plugin_active()
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! is_plugin_active( 'yikes-inc-easy-mailchimp-extender/yikes-inc-easy-mailchimp-extender.php' ) ) {
	deactivate_plugins( plugin_basename( __FILE__ ) );
	add_action( 'admin_notices' , 'yikes_inc_mailchimp_eu_law_compliance_display_activation_error' );
	return;
}

function yikes_inc_mailchimp_eu_law_compliance_display_activation_error() {
	?>	
		<!-- hide the 'Plugin Activated' default message -->
		<style>
		#message.updated {
			display: none;
		}
		</style>
		<!-- display our error message -->
		<div class="error">
			<p><?php _e( 'EU Opt-In Compliance for MailChimp could not be activated because the base plugin is not installed and active.', 'eu-opt-in-compliance-for-mailchimp' ); ?></p>
			<p><?php printf( __( 'Please install and activate %s before activating this extension.', 'eu-opt-in-compliance-for-mailchimp' ) , '<a href="' . esc_url_raw( admin_url( 'plugin-install.php?tab=search&type=term&s=Yikes+Inc.+Easy+MailChimp+Forms' ) ) . '" title="Easy Forms for MailChimp">Easy Forms for MailChimp</a>' ); ?></p>
		</div>
	<?php
}	
/* end check */

/********* Begin Main Plugin ***********/
class Yikes_Inc_Easy_Mailchimp_EU_Law_Compliance_Extension {

	/* Construction of our main class */
	public function __construct() {
		// add our incentives link to the edit form page
		add_action( 'yikes-mailchimp-edit-form-section-links' , array( $this , 'add_eu_law_compliance_section_links' ) );
		// add the incentives section to the edit form page
		add_action( 'yikes-mailchimp-edit-form-sections' , array( $this , 'render_eu_compliance_section' ) );
		// include our scripts & styles on the admin
		add_action( 'admin_enqueue_scripts' , array( $this, 'enqueue_yikes_mailchimp_eu_compliance_admin_styles' ) );
		// include our scripts & styles on the frontend
		add_action( 'yikes-mailchimp-shortcode-enqueue-scripts-styles' , array( $this, 'enqueue_yikes_mailchimp_eu_compliance_frontend_styles' ) );
		// render our checkbox after the form
		add_action( 'yikes-mailchimp-additional-form-fields' , array( $this, 'render_frontend_compliance_checkbox' ) );
		// set the locale
		$this->set_locale();
	}		
	
	/* 
	*	Enqueue our styles on the dashboard
	*	@since 0.1
	*/
	public function enqueue_yikes_mailchimp_eu_compliance_admin_styles( $hook ) {
		if( 'admin_page_yikes-mailchimp-edit-form' == $hook ) {
			wp_enqueue_style( 'yikes-mailchimp-eu-compliance-styles' , plugin_dir_url(__FILE__) . 'includes/css/yikes-mailchimp-eu-law-icons.css' );
		}
	}
	
	/* 
	*	Enqueue our styles on the frontend
	*	@since 0.1
	*/
	public function enqueue_yikes_mailchimp_eu_compliance_frontend_styles( $hook ) {
		wp_enqueue_style( 'yikes-mailchimp-eu-frontend-compliance-styles' , plugin_dir_url(__FILE__) . 'includes/css/yikes-mailchimp-eu-law-extension-frontend.css' );
	}
	
	/*
	*	Render the custom checkbox on the front end of the site
	*	hook into yikes-mailchimp-additional-form-fields
	*	@since 0.1
	*/
	public function render_frontend_compliance_checkbox( $form_data ) {
		$custom_field_data = array();
		if ( is_string( $form_data['custom_fields'] ) ) {
			$custom_field_data = json_decode( $form_data['custom_fields'], true );
		} else if ( is_array( $form_data['custom_fields'] ) ) {
			$custom_field_data = $form_data['custom_fields'];
		}
		$prechecked = ( isset( $custom_field_data['eu-compliance-law-checkbox-precheck'] ) ) ? $custom_field_data['eu-compliance-law-checkbox-precheck'] : 0;
		$checkbox_text = ( isset( $custom_field_data['eu-compliance-law-checkbox-text'] ) ) ? $custom_field_data['eu-compliance-law-checkbox-text'] : sprintf( __( 'Please check the checkbox to ensure that you comply with the <a title="Europen Optin Laws" href="%s" target="_blank">EU Laws</a>.', 'eu-opt-in-compliance-for-mailchimp' ), esc_url( 'http://www.lsoft.com/resources/optinlaws.asp' ) );
		$checked = ( $prechecked == 1 ) ? 'checked="checked"' : '';
		echo '<label class="yikes-mailchimp-eu-compliance-label"><input type="checkbox" required="required" name="eu-laws" value="1" ' . $checked . '> ' . $checkbox_text . '</label>';
	}
	
	/*
	*	Adding additional sections & fields to the edit form screen
	*	@since 0.1
	*/
		
		/* Add custom link to the links */
		public function add_eu_law_compliance_section_links() {
			// creating a new link on the edit form page
			Yikes_Inc_Easy_Mailchimp_Extender_Helper::add_edit_form_section_link( array(
				'id' => 'eu-law-compliance-section', // section id
				'text' => __( 'EU Law Compliance', 'eu-opt-in-compliance-for-mailchimp' ), // the text that will display in the link
				'icon_family' => 'custom',
				'icon' => 'yikes-mailchimp-eu-law' // dashicon icon class
			) );
		}
		
		/* Add custom section associated with link above */
		public static function render_eu_compliance_section() {	
		
			// defining a new section, associated with the link above
			Yikes_Inc_Easy_Mailchimp_Extender_Helper::add_edit_form_section( array(
				'id' => 'eu-law-compliance-section',  // section id (must match link id above)
				'main_title' => __( 'EU Law Compliance', 'eu-opt-in-compliance-for-mailchimp' ), // title of the main block of this custom section
				'main_description' => __( 'A check box will display below your form asking new users to confirm addition to your mailing list.' , 'eu-opt-in-compliance-for-mailchimp' ),
				'main_fields' => array(
					array(
						'label' => __( 'Pre-check Compliance Checkbox' , 'eu-opt-in-compliance-for-mailchimp' ), // label text for this field
						'type' => 'select', // type of field (text,select,checkbox,radio)
						'options' => array(
							'1' => __( 'Yes', 'eu-opt-in-compliance-for-mailchimp' ),
							'2' => __( 'No',  'eu-opt-in-compliance-for-mailchimp' )
						),
						'id' => 'eu-compliance-law-checkbox-precheck', // field id - determines how data is saved in database
						'description' => __( 'Should this check box be pre-checked on initial page load?.' , 'eu-opt-in-compliance-for-mailchimp' ), // field description  
					),
					array(
						'label' => __( 'Compliance Checkbox Text' , 'eu-opt-in-compliance-for-mailchimp' ), // label text for this field
						'type' => 'wysiwyg', // type of field (text,select,checkbox,radio)
						'id' => 'eu-compliance-law-checkbox-text', // field id - determines how data is saved in database
						'default' => sprintf( __( 'Please check the checkbox to ensure that you comply with the %s.' ), '<a href="' . esc_url( 'http://www.lsoft.com/resources/optinlaws.asp' ). '" title="' . __( 'Europen Optin Laws' , 'eu-opt-in-compliance-for-mailchimp' ) . '" target="_blank">' . __( 'EU Laws' , 'eu-opt-in-compliance-for-mailchimp' ) . '</a>' ),
						'description' => __( 'Add custom label text for this check box.' , 'eu-opt-in-compliance-for-mailchimp' ), // field description  
					),
				),
			) );
		}
		
	/*
	*	End additional sections & fields
	*/
	
	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Yikes_Inc_Easy_Mailchimp_Extender_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once dirname( __FILE__ ) . '/includes/class-yikes-inc-easy-mailchimp-eu-compliance-i18n.php';
		$eu_compliance_i18n = new Yikes_Inc_Easy_Mailchimp_EU_Compliance_i18n();
		$eu_compliance_i18n->set_domain( 'eu-opt-in-compliance-for-mailchimp' ); 
		add_action( 'plugins_loaded', array( $eu_compliance_i18n, 'load_eu_compliance_text_domain') );
	}
	
}

/*
*	Ensure base class is loaded first
*/
add_action( 'plugins_loaded' , 'load_eu_compliance_law_extension' );
function load_eu_compliance_law_extension() {	
	if( class_exists( 'Yikes_Inc_Easy_Mailchimp_Forms_Admin' ) ) {
		new Yikes_Inc_Easy_Mailchimp_EU_Law_Compliance_Extension;
	}
}
						
/**
 * The code that runs during plugin uninstallation.
 */
register_uninstall_hook( __FILE__, 'uninstall_yikes_inc_easy_mailchimp_eu_compliance_extension' );
function uninstall_yikes_inc_easy_mailchimp_eu_compliance_extension() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-yikes-inc-easy-mailchimp-eu-compliance-extension-uninstall.php';
	Yikes_Inc_Easy_Mailchimp_EU_Compliance_Extension_Uninstaller::uninstall();
}	