<?php

/*
Plugin Name: Domain Reminder
URI:
Version: 1.0
Author: Luigi Perrone
Author URI:
Description: Simple plugin to remember the expiration date of your domain.
*/

if(!defined('ABSPATH')) exit;
if(defined('WP_INSTALLING') && WP_INSTALLING) {return;}

// ADD CSS FUNCTION
function style() {
   wp_register_style('style', plugins_url('style.css',__FILE__ ));
   wp_enqueue_style('style');
}
add_action( 'admin_init','style');

add_action('wp_dashboard_setup', 'domain_reminder_dashboard_widgets');
  
function domain_reminder_dashboard_widgets() {
global $wp_meta_boxes;
 
wp_add_dashboard_widget('domain_reminder_widget', 'Domain Reminder', 'domain_reminder_dashboard_help');
}

// We'll key on the slug for the settings page so set it here so it can be used in various places
define('MY_PLUGIN_SLUG', 'domain-reminder-option');

// Register a callback for our specific plugin's actions
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'domain_reminder_action_links');
function domain_reminder_action_links( $links ) {
   $links[] = '<a href="'. menu_page_url(MY_PLUGIN_SLUG, false) .'">Settings</a>';
   return $links;
}

// Create a normal admin menu
if ( is_admin() ){ // admin actions
  add_action('admin_menu', 'register_settings');
  add_action( 'admin_init', 'domain_reminder_register_settings' );
}

function register_settings() {
   add_options_page('Domain Reminder Settings', 'Domain Reminder Settings', 'manage_options', MY_PLUGIN_SLUG, 'domain_reminder_settings_page');

    //We just want to URL to be valid so now we're going to remove the item from the menu
    //The code below walks the global menu and removes our specific item by its slug
    global $submenu;
    if( array_key_exists('domain-reminder-option' , $submenu))
    {
        foreach($submenu['domain-reminder-option'] as $k => $v)
        {
            if( MY_PLUGIN_SLUG === $v[2] )
            {
                unset($submenu['domain-reminder-option'][$k]);
            }
        }
    }
}

/**
* Sanitize each setting field as needed
*
* @param array $input Contains all settings fields as array keys
*/
function sanitize( $input )
{
    $new_input = array();
    if( isset( $input['domain_reminder_option_dominio'] ) )
        $new_input['domain_reminder_option_dominio'] = sanitize_text_field( $input['domain_reminder_option_dominio'] );
	
    if( isset( $input['domain_reminder_option_scadenza'] ) )
        $new_input['domain_reminder_option_scadenza'] = sanitize_text_field( $input['domain_reminder_option_scadenza'] );

    if( isset( $input['domain_reminder_option_avviso'] ) )
        $new_input['domain_reminder_option_avviso'] = sanitize_text_field( $input['domain_reminder_option_avviso'] );	
			
        return $new_input;
}

// FUNCTION REGISTER SETTINGS
function domain_reminder_register_settings() {
  register_setting( 'domain_reminder_options_group', 'domain_reminder_option_dominio' );
  register_setting( 'domain_reminder_options_group', 'domain_reminder_option_scadenza' );
  register_setting( 'domain_reminder_options_group', 'domain_reminder_option_avviso' ); 
}
 
// This is our plugins settings page
function domain_reminder_settings_page() {
?>
  <div>
  <?php screen_icon(); ?>
  <h1>Domain Remind - General Settings</h1>
  <br />
  <form method="post" action="options.php">
  <?php settings_fields('domain_reminder_options_group'); ?>
  <div class="form-check">
  <label class="form-check-label">Dominio:</label>
    <input class="form-check-input" type="text" name="domain_reminder_option_dominio" id="dominio" value="<?php echo esc_attr( get_option('domain_reminder_option_dominio') ); ?>">
  </div>
  <div class="form-check">
  <label class="form-check-label">Scadenza:</label>
    <input class="form-check-input" type="date" name="domain_reminder_option_scadenza" id="scadenza" value="<?php echo esc_attr( get_option('domain_reminder_option_scadenza') ); ?>">
  </div>
  <div class="form-check">
  <label class="form-check-label">Notice:</label>
    <input class="form-check-input" type="text" name="domain_reminder_option_avviso" id="avviso" placeholder="indica il numero dei giorni di preavviso es. 10 - default 10gg" value="<?php echo esc_attr( get_option('domain_reminder_option_avviso') ); ?>">
  </div>			  
  <?php submit_button(); ?>
  </form>
  </div>
<?php
}

function domain_reminder_dashboard_help() {
$today = date ("Y-m-d");
$domain = esc_attr(get_option('domain_reminder_option_dominio') );
$expire = esc_attr(get_option('domain_reminder_option_scadenza') );
$notice = esc_attr(get_option('domain_reminder_option_avviso') );
$expire_it = date('d/m/Y', strtotime($expire));
$day = floor((strtotime($expire) - strtotime($today)) / 86400); 
echo "<p>Dominio: $domain</p>";
echo "<p>Scadenza: $expire_it</p>";

if($day > $notice){ 
   echo "<p>Giorni rimanenti: <span style='color: green;'><b>$day</b></span></p>"; 
   }else{
   echo "<p>Giorni rimanenti: <span style='color: red;'><b>$day</b></span></p>";
}

if(empty($notice)){
   $notice = 10; 
}

echo "<p>Preavviso: $notice giorni</p>";

if($day == $notice){
  echo "<p style='color: red;'><strong>E' tempo di rinnovare il tuo dominio!</strong></p>";
  }
}

?>
