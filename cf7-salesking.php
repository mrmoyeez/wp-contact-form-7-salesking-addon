<?php
/*
Plugin Name: Contact Form 7 - SalesKing Addon
Plugin URI: http://www.salesking.eu/wordpress-contributions/salesking-addon-for-contact-form-7/
Description: Add the power of SalesKing to Contact Form 7
Author URI: http://www.saleskin.eu
Version: 0.01
*/

/*  
MIT Licens
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
        wp_die( wp_sprintf( __( 'Sorry, This plugin has taken a bold step in requiring PHP 5.3.0+. Your server is currently running PHP %2s, Please bug your host to upgrade to a recent version of PHP which is less bug-prone.', 'wpcf7-salesking' ), PHP_VERSION ) );
    }
    if (!in_array('curl', get_loaded_extensions())) {
        deactivate_plugins( __FILE__ );
        wp_die( __( 'Sorry, This plugin requires the curl extension for PHP which isn\'t available on you server. Please contact your host.', 'wpcf7-salesking' ));
    }
}

// contact form is present and active
// lets do our stuff

define( 'WPCF7_SK_VERSION', '0.01' );

if ( ! defined( 'WPCF7_SK_PLUGIN_BASENAME' ) )
	define( 'WPCF7_SK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

add_action( 'wpcf7_after_save', 'wpcf7_sk_save_salesking' );

function wpcf7_sk_save_salesking($args)
{
    update_option( 'cf7_sk_'.$args->id, $_POST['wpcf7-salesking'] );
    // check the credentials on save 
    $sk_activated = false;
    $sk_test_credentials = false;
    $wp_sk = $_POST['wpcf7-salesking']; 
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
function validate_submitted_credentials($args){
    // this function calls the api, and tries a customer get
    // if there is no 200, send an error message
    require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'cf7-salesking-rest.php');
    
    // setup the api
    $input =  array();
    $input['sk_url'] = "https://".$args['subdomain'].".salesking.eu";
    $input['sk_username'] = $args['api-username'];
    $input['sk_password'] = $args['api-userpw']; 
    $restAPI = new SkRest;
    $restAPI->setOptions($input);
    $input = $restAPI->validateSettings($input);
    if (isset($input['message']) == true){
        //add_action('wpcf7_admin_notices', 'wpcf7_sk_credentials_validation_failed', 9);
        echo "<div class=\"message error \"><p>Salesking Credentials failed</p></div>";
        var_dump($input);
        // @todo: improve the error message here
        wp_die("credential validation failed - hit back button and change credentials");
    }
    //else{
        // add the message to the top screen
    //    add_action('wpcf7_admin_notices', 'wpcf7_sk_credentials_validation_success', 9);
    //}
}
/*
*function wpcf7_sk_credentials_validation_failed(){
*    $msg = '<div class="alert">'
*        . 'FAILED'
*        . '</div>';
*    echo apply_filters( 'wpcf7_sk_credentials_validation_failed', $msg );
*}
*/

/*
*function wpcf7_sk_credentials_validation_success(){
*    $msg="<div class='alert alert-success'>Nimble API settings have been sucessfully saved. Please map Nimble fields with Contact Form 7 fields to complete the integration process.</div>";
*    echo apply_filters( 'wpcf7_sk_credentials_validation_success', $msg );
*    wp_die("succ");
*}
*/



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
			
function wpcf7_sk_add_salesking($args)
{
    $cf7_sk_defaults = array();
    // set some defaults
    
    
    $cf7_sk = get_option( 'cf7_sk_'.$args->id, $cf7_sk_defaults );
    // api defaults
    if (empty($cf7_sk['api-username'])){
        $cf7_sk['api-username'] = 'YOUR-API-USERNAME';
    };
    if (empty($cf7_sk['api-userpw'])){
        $cf7_sk['api-userpw'] = 'YOUR-API-USER-PASSWORD';
    };
    if (empty($cf7_sk['subdomain'])){
        $cf7_sk['subdomain'] = 'MY-SUBDOMAIN';
    };
    // form specific settings
    if (empty($cf7_sk['email'])){
        $cf7_sk['email'] = '[your-email]';
    };
    if (empty($cf7_sk['name'])){
        $cf7_sk['name'] = '[your-name]';
    };
    if (empty($cf7_sk['organization-name'])){
        $cf7_sk['organization-name'] = '[your-organization]';
    };
    if (empty($cf7_sk['work-phone'])){
        $cf7_sk['work-phone'] = '[your-phone]';
    };
    if (empty($cf7_sk['data-tags'])){
        $cf7_sk['data-tags'] = 'website-contact-form';
    };
    if (empty($cf7_sk['lead-notice'])){
        $cf7_sk['lead-notice'] = '[your-subject] !!! [your-message]';
    };
    // hacky
    // check ir the post contained the test and submitt button
    // if so say it was successfull, as in fail case the wp_die got hit
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
	<input type="checkbox" id="wpcf7-salesking-active" name="wpcf7-salesking[active]" value="1"<?php echo ( $cf7_sk['active']==1 ) ? ' checked="checked"' : ''; ?> />
	<label for="wpcf7-salesking-active"><?php echo esc_html( __( 'Use SalesKing Integration', 'wpcf7' ) ); ?></label>
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
			<label for="wpcf7-salesking-email"><?php echo esc_html( __( 'Lead Email:', 'wpcf7' ) ); ?></label><br />
			<input type="text" id="wpcf7-salesking-email" name="wpcf7-salesking[email]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['email'] ); ?>" />
		</div>
		<div class="mail-field">
		<label for="wpcf7-salesking-name"><?php echo esc_html( __( 'Full Name:', 'wpcf7' ) ); ?></label><br />
		<input type="text" id="wpcf7-salesking-name" name="wpcf7-salesking[name]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['name'] ); ?>" />
		</div>
        <div class="mail-field">
		<label for="wpcf7-salesking-organization-name"><?php echo esc_html( __( 'Organization Name:', 'wpcf7' ) ); ?></label><br />
		<input type="text" id="wpcf7-salesking-organization-name" name="wpcf7-salesking[organization-name]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['organization-name'] ); ?>" />
		</div>
        <div class="mail-field">
		<label for="wpcf7-salesking-work-phone"><?php echo esc_html( __( 'Work Phone:', 'wpcf7' ) ); ?></label><br />
		<input type="text" id="wpcf7-salesking-work-phone" name="wpcf7-salesking[work-phone]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['work-phone'] ); ?>" />
		</div>
        <div class="mail-field">
		<label for="wpcf7-salesking-lead-notice"><?php echo esc_html( __( 'Lead Notice:', 'wpcf7' ) ); ?></label><br />
		<input type="text" id="wpcf7-salesking-lead-notice" name="wpcf7-salesking[lead-notice]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['lead-notice'] ); ?>" />
		</div>
        <div class="mail-field">
		<label for="wpcf7-salesking-data-tags"><?php echo esc_html( __( 'Data Tags:', 'wpcf7' ) ); ?></label><br />
		<input type="text" id="wpcf7-salesking-data-tags" name="wpcf7-salesking[data-tags]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['data-tags'] ); ?>" />
		</div>
	</div>
	
	<div class="half-right">
		<div class="mail-field">
		<label for="wpcf7-salesking-api-username"><?php echo esc_html( __( 'API Username:', 'wpcf7' ) ); ?></label><br />
		<input type="text" id="wpcf7-salesking-api" name="wpcf7-salesking[api-username]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['api-username'] ); ?>" />
		</div>
		<div class="mail-field">
		<label for="wpcf7-salesking-api-userpw"><?php echo esc_html( __( 'API User Password:', 'wpcf7' ) ); ?></label><br />
		<input type="password" id="wpcf7-salesking-api-userpw" name="wpcf7-salesking[api-userpw]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['api-userpw'] ); ?>" />
		</div>
        <div class="mail-field">
		<label for="wpcf7-salesking-subdomain"><?php echo esc_html( __( 'Your Salesking Subdomain:', 'wpcf7' ) ); ?></label><br />
		<input type="text" id="wpcf7-salesking-subdomain" name="wpcf7-salesking[subdomain]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['subdomain'] ); ?>" />
		</div>
        
        <div class="mail-field">
		<label for="wpcf7-salesking-test-credentials"><?php echo esc_html( __( 'Test your Credentials here:', 'wpcf7' ) ); ?></label><br />
		<input type="submit" class="button-primary" id="wpcf7-salesking-test-credentials" name="wpcf7-salesking[test-credentials]" value="<?php echo esc_html( __( 'save and test credentials', 'wpcf7' ) ); ?>"   />
		</div>
        
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
		array( 'jquery', 'wpcf7-admin' ), WPCF7_CM_VERSION, true );
}


add_action( 'wpcf7_before_send_mail', 'wpcf7_sk_add_data' );

function wpcf7_sk_add_data($obj)
{
    // function gets the posted data and massages and submitts it
	$cf7_sk = get_option( 'cf7_sk_'.$obj->id );
	if( $cf7_sk )
	{
		$subscribe = false;
		$regex = '/\[\s*([a-zA-Z_][0-9a-zA-Z:._-]*)\s*\]/';
		$callback = array( &$obj, 'cf7_sk_callback' );
        // minimal set of data present?
		$email = cf7_sk_tag_replace( $regex, $cf7_sk['email'], $obj->posted_data );
		$name = cf7_sk_tag_replace( $regex, $cf7_sk['name'], $obj->posted_data );
        $subscribe = true;
		if($subscribe && $email != $cf7_sk['email'])
		{
			// hook the api to do the communications
			require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'csrest_subscribers.php');
			
			$wrap = new CF7CM_CS_REST_Subscribers( trim($listarr[0]), $cf7_sk['api'] );
			foreach($listarr as $listid)
			{
				$wrap->set_list_id(trim($listid));
				$wrap->add(array(
					'EmailAddress' => $email,
					'Name' => $name,
					'CustomFields' => $CustomFields,
					'Resubscribe' => $ResubscribeOption
				));
			}
		}
	}
}

function cf7_sk_tag_replace( $pattern, $subject, $posted_data, $html = false ) {
	if( preg_match($pattern,$subject,$matches) > 0)
	{
		if ( isset( $posted_data[$matches[1]] ) ) {
			$submitted = $posted_data[$matches[1]];
			if ( is_array( $submitted ) )
				$replaced = join( ', ', $submitted );
			else
				$replaced = $submitted;
			if ( $html ) {
				$replaced = strip_tags( $replaced );
				$replaced = wptexturize( $replaced );
			}
			$replaced = apply_filters( 'wpcf7_mail_tag_replaced', $replaced, $submitted );
			return stripslashes( $replaced );
		}
		if ( $special = apply_filters( 'wpcf7_special_mail_tags', '', $matches[1] ) )
			return $special;
		return $matches[0];
	}
	return $subject;
}

function wpcf7_sk_plugin_url( $path = '' ) {
	return plugins_url( $path, WPCF7_CM_PLUGIN_BASENAME );
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
