<?php
/*
Plugin Name: Contact Form 7 - SalesKing Addon
Plugin URI: http://www.salesking.eu/wordpress-contributions/salesking-addon-for-contact-form-7/
Description: Add the power of SalesKing to Contact Form 7
Author URI: http://www.salesking.eu
Version: 0.02
*/

/*  
MIT Licence
*/
// first check if it is good to go?
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
$plugin_name = 'contact-form-7/wp-contact-form-7.php';
$is_active = is_plugin_active($plugin_name);
if ($is_active == '1') {
} else {
  echo '<br /><br /><div class="alert alert-error">Contact Form 7 plugin is required to configure this SalesKing Addon plugin. Please install and activate Contact Form 7 plugin and then return to this page.</div>';
  exit;
}
function activationHook() {
    if (!version_compare( PHP_VERSION, '5.3.0', '>=' )) {
        deactivate_plugins( __FILE__ );
        wp_die( wp_sprintf( __( 'Sorry, This plugin has taken a bold step in requiring PHP 5.3.0+. Your server is currently running PHP %2s, Please bug your host to upgrade to a recent version of PHP which is less bug-prone.', 'wpcf7-sk' ), PHP_VERSION ) );
    }
    if (!in_array('curl', get_loaded_extensions())) {
        deactivate_plugins( __FILE__ );
        wp_die( __( 'Sorry, This plugin requires the curl extension for PHP which isn\'t available on you server. Please contact your host.', 'wpcf7-sk' ));
    }
}

// contact form is present and active
// lets do our stuff

define( 'WPCF7_SK_VERSION', '0.03' );

if ( ! defined( 'WPCF7_SK_PLUGIN_BASENAME' ) )
    define( 'WPCF7_SK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

add_action( 'wpcf7_after_save', 'wpcf7_sk_save_salesking' );

function wpcf7_sk_save_salesking($args)
{
    update_option( 'cf7_sk_'.$args->id, $_POST['wpcf7-sk'] );
    // check the credentials on save 
    $sk_activated = false;
    $sk_test_credentials = false;
    $wp_sk = $_POST['wpcf7-sk']; 
    // sk activated ?
    if (isset($wp_sk['active']) && 
       (strlen($wp_sk['active']) == 1) && 
       ($wp_sk['active'] == 1) )
       {
        $sk_activated = true;
    };
    if (isset($wp_sk['test-credentials']) && 
       ($wp_sk['test-credentials'] == true )){
        $sk_test_credentials = true ; 
    }
    if (($sk_activated == true) && ($sk_test_credentials == true )){
        validate_submitted_credentials($wp_sk);
    }
}
function setup_rest_api($args){
    // just instantiate the api
    require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'cf7-salesking-rest.php');
    $input =  array();
    $input['sk_url'] = "https://".$args['sk_subdomain'].".salesking.eu";
    $input['sk_username'] = $args['sk_username'];
    $input['sk_password'] = $args['sk_password'];
    $rest = new SkRest();
    $rest->setOptions($input);
    return $rest;
}

function validate_submitted_credentials($args){
    // this function calls the api, and tries a customer get
    // if there is no 200, send an error message
    $rest = setup_rest_api($args);
    $input = $rest->validateOptions();
    if (isset($input['message']) == true){
        //add_action('wpcf7_admin_notices', 'wpcf7_sk_credentials_validation_failed', 9);
        echo "<div class=\"message error \"><p>Salesking Credentials failed</p></div>";
        var_dump($input);
        // @todo: improve the error message here
        // found no way to do propper messaging
        // we rely on the die here - BAD
        wp_die("credential validation failed - hit back button and change credentials");
    }
}

add_action( 'wpcf7_admin_notices', 'add_sk_meta' );

function add_sk_meta (){
    if ( wpcf7_admin_has_edit_cap() ) {
        add_meta_box( 'cf7skdiv', __( 'SalesKing', 'wpcf7' ),
            'wpcf7_sk_add_salesking', 'cfseven', 'cf7_sk', 'core',
            array(
                'id' => 'wpcf7-cf7',
                'name' => 'cf7_sk',
                'use' => __( 'Use SalesKing', 'wpcf7' ) ) );
    }
}

add_action( 'wpcf7_admin_after_mail_2', 'show_sk_metabox' );

function show_sk_metabox($cf){
    do_meta_boxes( 'cfseven', 'cf7_sk', $cf );
}

function is_no_user_or_pw_or_subdomain($cf7_sk){
    // some hacky marker to determine if we initialize the form first time
    $ok = false;
    if (empty($cf7_sk['sk_username']) or (
        empty($cf7_sk['sk_password']) or (
        empty($cf7_sk['sk_subdomain']))))
        {
        $ok = true;
    }
    return $ok;

}


function set_salesking_defaults_if_empty($cf7_sk){
    // api defaults
    if (is_no_user_or_pw_or_subdomain($cf7_sk) == false){
        // if there is something set, do nothing
        return $cf7_sk;
    }
    // set the form values as we do have no values found yet
    if (empty($cf7_sk['sk_username'])){
        $cf7_sk['sk_username'] = 'YOUR-API-USERNAME';
    };
    if (empty($cf7_sk['sk_password'])){
        $cf7_sk['sk_password'] = 'YOUR-API-USER-PASSWORD';
    };
    if (empty($cf7_sk['sk_subdomain'])){
        $cf7_sk['sk_subdomain'] = 'MY-SUBDOMAIN';
    };
    // form specific settings
    if (empty($cf7_sk['email'])){
        $cf7_sk['email'] = '[your-email]';
    };
    if (empty($cf7_sk['last_name'])){
        $cf7_sk['last_name'] = '[your-name]';
    };
    if (empty($cf7_sk['organisation'])){
        $cf7_sk['organisation'] = '[organisation]';
    };
    if (empty($cf7_sk['phone_office'])){
        $cf7_sk['phone_office'] = '[phone_office]';
    };
    if (empty($cf7_sk['tag_list'])){
        $cf7_sk['tag_list'] = 'example-tag';
    };
    if (empty($cf7_sk['sk_notes'])){
        $cf7_sk['notes'] = "[your-subject] \n[your-message]";
    };
    if (empty($cf7_sk['lead_ref'])){
        $cf7_sk['lead_ref'] = '[_post_url]';
    };
return $cf7_sk;
}



function wpcf7_sk_add_salesking($args)
{
    $cf7_sk_defaults = array();
    // set some defaults
    $cf7_sk = get_option( 'cf7_sk_'.$args->id, $cf7_sk_defaults );
    # if the form is new add the defaults
    $cf7_sk = set_salesking_defaults_if_empty($cf7_sk);
    // hacky
    // check if the post contained the test and submitt button
    // if so say it was successfull, as in fail case the wp_die got hit
    // use this bad way, as there seems to be no easy way for sending msgs to the user
    $sk_activated = false;
    $sk_test_credentials = false;
    if (isset($cf7_sk['active']) && 
       (strlen($cf7_sk['active']) == 1) && 
       ($cf7_sk['active'] == 1) )
       {
        $sk_activated = true;
    };
    if (isset($cf7_sk['test-credentials']) && 
       ($cf7_sk['test-credentials'] == true )){
        $sk_test_credentials = true ; 
    }
    ?>

<div class="mail-field">
    <input type="checkbox" id="wpcf7-sk-active" name="wpcf7-sk[active]" value="1"<?php echo ( isset($cf7_sk['active']) && $cf7_sk['active']==1 ) ? ' checked="checked"' : ''; ?> />
    <label for="wpcf7-sk-active"><?php echo esc_html( __( 'Use SalesKing Integration', 'wpcf7' ) ); ?></label>
<div class="pseudo-hr"></div>
<?php
// success message if the post tested the credentials
if ($sk_activated && $sk_test_credentials){
    echo "<div class=\"message error updated \"><p>Salesking Credentials successfully tested</p></div>";
}
?>
</div>

<br class="clear" />

<div class="mail-fields">
    <div class="half-left">
        <div class="mail-field">
            <label for="wpcf7-sk-email"><?php echo esc_html( __( 'Lead Email:', 'wpcf7' ) ); ?></label><br />
            <input type="text" id="wpcf7-sk-email" name="wpcf7-sk[email]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['email'] ); ?>" />
        </div>
        <div class="mail-field">
        <label for="wpcf7-sk-last_name"><?php echo esc_html( __( 'Full Name:', 'wpcf7' ) ); ?></label><br />
        <input type="text" id="wpcf7-sk-last_name" name="wpcf7-sk[last_name]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['last_name'] ); ?>" />
        </div>
        <div class="mail-field">
        <label for="wpcf7-sk-organisation"><?php echo esc_html( __( 'Organization Name:', 'wpcf7' ) ); ?></label><br />
        <input type="text" id="wpcf7-sk-organisation" name="wpcf7-sk[organisation]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['organisation'] ); ?>" />
        </div>
        <div class="mail-field">
        <label for="wpcf7-sk-work-phone"><?php echo esc_html( __( 'Phone Office:', 'wpcf7' ) ); ?></label><br />
        <input type="text" id="wpcf7-sk-phone_office" name="wpcf7-sk[phone_office]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['phone_office'] ); ?>" />
        </div>
        <div class="mail-field">
        <label for="wpcf7-sk-notes"><?php echo esc_html( __( 'Lead Notice:', 'wpcf7' ) ); ?></label><br />
        <textarea id="wpcf7-sk-notes" name="wpcf7-sk[notes]" class="wide"  rows="3"> <?php echo esc_attr( $cf7_sk['notes'] ); ?> </textarea>


        </div>
        <div class="mail-field">
        <label for="wpcf7-sk-lead_ref"><?php echo esc_html( __( 'Lead Source:', 'wpcf7' ) ); ?></label><br />
        <input type="text" id="wpcf7-sk-lead_ref" name="wpcf7-sk[lead_ref]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['lead_ref'] ); ?>" />
        </div>
        <div class="mail-field">
        <label for="wpcf7-sk-tag_list"><?php echo esc_html( __( 'Data Tags:', 'wpcf7' ) ); ?></label><br />
        <input type="text" id="wpcf7-sk-tag_list" name="wpcf7-sk[tag_list]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['tag_list'] ); ?>" />
        </div>
    </div>
    
    <div class="half-right">
        <div class="mail-field">
        <label for="wpcf7-sk-sk_username"><?php echo esc_html( __( 'API Username:', 'wpcf7' ) ); ?></label><br />
        <input type="text" id="wpcf7-sk-api" name="wpcf7-sk[sk_username]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['sk_username'] ); ?>" />
        </div>
        <div class="mail-field">
        <label for="wpcf7-sk-sk_password"><?php echo esc_html( __( 'API User Password:', 'wpcf7' ) ); ?></label><br />
        <input type="password" id="wpcf7-sk-sk_password" name="wpcf7-sk[sk_password]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['sk_password'] ); ?>" />
        </div>
        <div class="mail-field">
        <label for="wpcf7-sk-subdomain"><?php echo esc_html( __( 'Your Salesking Subdomain:', 'wpcf7' ) ); ?></label><br />
        <input type="text" id="wpcf7-sk-subdomain" name="wpcf7-sk[sk_subdomain]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['sk_subdomain'] ); ?>" />
        </div>
        
        <div class="mail-field">
        <label for="wpcf7-sk-test-credentials"><?php echo esc_html( __( 'Test your Credentials here:', 'wpcf7' ) ); ?></label><br />
        <input type="submit" class="button-primary" id="wpcf7-sk-test-credentials" name="wpcf7-sk[test-credentials]" value="<?php echo esc_html( __( 'save and test credentials', 'wpcf7' ) ); ?>"   />
        </div>
      <!--
        <div class="mail-field">
        <hr></hr>
        <p><strong> Note: </strong></br>
         <?php echo __( 'Please enter only one placeholder per field. [your-email] [foobar] does not work yet.'); ?>
        </p>
       </div>-->
    </div>
    
    <br class="clear" />

    
</div>

<?php
}

add_action( 'admin_print_scripts', 'wpcf7_sk_admin_enqueue_scripts' );

function wpcf7_sk_admin_enqueue_scripts ()
{
    global $plugin_page;

    if ( ! isset( $plugin_page ) || 'wpcf7' != $plugin_page )
        return;

    wp_enqueue_script( 'wpcf7-sk-admin', wpcf7_sk_plugin_url( 'scripts.js' ),
        array( 'jquery', 'wpcf7-admin' ), WPCF7_SK_VERSION, true );
}


add_action( 'wpcf7_before_send_mail', 'wpcf7_sk_add_data' );
//add_action( 'wpcf7_mail_sent', 'wpcf7_sk_add_data' );

function strip_unprocessed_tags_or_null($val1, $ret_val2, $begin_tag_indicator= "[", $end_tag_indicator = "]"){
    // 
    $begin_pos = strpos($val1, $begin_tag_indicator);
    $end_pos = strpos($ret_val2, $end_tag_indicator);
    // values are unprocessed, and there seems to be a tag left
    if (($val1 == $ret_val2) && ( $begin_pos <> $end_pos)){
        $ret_val2 = "";
    }
    return $ret_val2;
}

/**
 * Sends form data to salesking, first substitutes placeholders in SK contact form setting fields
 * @param $obj WPCF7_ContactForm
 */
function wpcf7_sk_add_data($obj){
    $cf7_sk = get_option( 'cf7_sk_'.$obj->id );
    if( $cf7_sk ) {
      $payload = array();
      $sk_fields = array('email', 'last_name', 'tag_list', 'lead_ref', 'phone_office', 'organisation', 'notes');
      foreach($sk_fields as $field){
        $tmp = $obj->replace_mail_tags($cf7_sk[$field] );
        $payload[$field] = strip_unprocessed_tags_or_null ( $cf7_sk[$field], $tmp);
      }
      // setup and post to api
      $sk_API = setup_rest_api($cf7_sk);
      $ok = $sk_API->send_data($payload);
    }
}


function wpcf7_sk_plugin_url( $path = '' ) {
    return plugins_url( $path, WPCF7_SK_PLUGIN_BASENAME );
}

add_filter( 'plugin_action_links', 'wpcf7_sk_plugin_action_links', 10, 2 );

function wpcf7_sk_plugin_action_links( $links, $file ) {

    if ( $file != plugin_basename( __FILE__ ) )
        return $links;

    $settings_link = '<a href="' . menu_page_url( 'wpcf7', false ) . '">'
        . esc_html( __( 'Settings', 'wpcf7' ) ) . '</a>';

    array_unshift( $links, $settings_link );
    return $links;
}
