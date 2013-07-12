<?php

function call_SkCfAdmin(){
  return new SkCfAdmin();
}
if ( is_admin() )
  add_action( 'admin_init', 'call_SkCfAdmin' );

/**
 * Adds SalesKing form fields to WPContactForm7 admin area.
 * Class SkCf7
 */
class SkCfAdmin{

  /**
   * register wordpress and contact_form hooks
   */
  function __construct(){
    add_action( 'wpcf7_admin_notices', array( &$this, 'add_meta_box' ) );
    add_action( 'wpcf7_admin_after_mail_2', array( &$this, 'show_meta_box' ));
    add_action( 'wpcf7_after_save', array( &$this, 'save_form' ) );
    add_action( 'admin_print_scripts', array( &$this, 'enqueue_scripts' ) );
    add_filter( 'plugin_action_links', array( &$this, 'settings_link' ), 10, 2 );
    add_action( 'wp_ajax_sk_login_test',  array( &$this, 'sk_login_test' ) );
  }

  function sk_login_test(){
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
    header("Content-type: text/plain");
    if($this->valid_credentials($_GET))
      echo 'ok';
    die();
  }

  /**
   * add our javascript to the admin page
   */
  function enqueue_scripts(){
    global $plugin_page;
    if ( ! isset( $plugin_page ) || 'wpcf7' != $plugin_page )
      return;

    $admin_js = plugins_url('assets/js/admin.js', WPCF7_SK_PLUGIN_BASENAME);
    wp_enqueue_script( 'wpcf7-sk-admin', $admin_js, array( 'jquery', 'wpcf7-admin' ), WPCF7_SK_VERSION, true );
  }

  /**
   * Settings link in the plugin list, goes to wp contact form
   */
  function settings_link($links, $file){
    if ( $file != WPCF7_SK_PLUGIN_BASENAME )
      return $links;

    $settings_link = '<a href="' . menu_page_url( 'wpcf7', false ) . '">'. esc_html( __( 'Settings', 'wpcf7' ) ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
  }

  /**
   * register the meta box on the contact form
   */
  function add_meta_box (){
    if ( !wpcf7_admin_has_edit_cap() )
      return;
    add_meta_box(
      'cf7skdiv',
      __( 'SalesKing', 'wpcf7' ),
      array( &$this, 'show_form' ),
      'cfseven',
      'cf7_sk',
      'core',
      array( 'id' => 'wpcf7-cf7',
        'name' => 'cf7_sk',
        'use' => __( 'Use SalesKing', 'wpcf7' ) ) );

  }

  /**
   * calls wp method to render the meta boxes for the form
   */
  function show_meta_box($cf){
    do_meta_boxes( 'cfseven', 'cf7_sk', $cf );
  }

  /**
   * Save ContactForm SalesKing settings in admin
   * @param $cf WPCF7_ContactForm
   */
  function save_form($cf) {
    $params = $_POST['wpcf7-sk'];
    update_option( 'cf7_sk_'.$cf->id, $params );
    // check the credentials on save
//    if ( isset($params['active']) && isset($params['test-credentials']) )
//      $this->validate_credentials($params);

  }

  /**
   * Show html on admin contact form
   * @param $cf WPCF7_ContactForm
   */
  function show_form($cf){

    $cf7_sk_defaults = array();
    // set some defaults
    $cf7_sk = get_option( 'cf7_sk_'.$cf->id, $cf7_sk_defaults );
    # if the form is new add the defaults
    $cf7_sk = $this->set_defaults($cf7_sk);
    ?>

    <div class="mail-field">
      <input type="checkbox" id="wpcf7-sk-active" name="wpcf7-sk[active]" value="1"<?php echo ( isset($cf7_sk['active']) && $cf7_sk['active']==1 ) ? ' checked="checked"' : ''; ?> />
      <label for="wpcf7-sk-active"><?php echo esc_html( __( 'Use SalesKing Integration', 'wpcf7' ) ); ?></label>
      <div class="pseudo-hr"></div>
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
          <textarea id="wpcf7-sk-notes" name="wpcf7-sk[notes]" class="wide" rows="3"><?php echo esc_attr( $cf7_sk['notes'] ); ?></textarea>
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
          <input type="text" id="wpcf7-sk-sk_username" name="wpcf7-sk[sk_username]" class="wide" size="70" value="<?php echo esc_attr( $cf7_sk['sk_username'] ); ?>" />
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
          <input type="submit" class="button-primary" data-url="<?php echo admin_url('admin-ajax.php').'?action=sk_login_test'  ?>" id="wpcf7-sk-test-credentials" name="wpcf7-sk[test-credentials]" value="<?php echo __( 'Test SalesKing connection', 'wpcf7' ); ?>"/>
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

  /**
   * @param $args Array(sk_username=>.., sk_password=>.., sk_subdomain=>..)
   * @return bool
   */
  function valid_credentials($args){
    // this function calls the api, and tries a customer get
    // if there is no 200, send an error message
    $rest = $this->setup_rest_api($args);
    $input = $rest->validateOptions();
    return !isset($input['message']);
  }

  /**
   * @param $args array with sk credentials from post params
   * @return SkRest
   */
  private function setup_rest_api($args){
    $input =  array();
    $input['sk_url'] = "https://".$args['sk_subdomain'].".salesking.eu";
    $input['sk_username'] = $args['sk_username'];
    $input['sk_password'] = $args['sk_password'];
    $rest = new SkRest();
    $rest->setOptions($input);
    return $rest;
  }

  /**
   * Set default values for a new form
   * @param $cf7_sk
   * @return mixed
   */
  private function set_defaults($cf7_sk){

    if ($this->has_credentials($cf7_sk))
      return $cf7_sk;

    // set the form values as we do have no values found yet
    if (empty($cf7_sk['sk_username'])){
      $cf7_sk['sk_username'] = 'your@SalesKing-login-email.de';
    };
    if (empty($cf7_sk['sk_subdomain'])){
      $cf7_sk['sk_subdomain'] = 'SalesKing-Subdomain';
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

  /**
   * determine if we initialize the form first time, all sk fields are empty
   * @param $cf7_sk
   * @return bool
   */
  private function has_credentials($cf7_sk){
    return !(empty($cf7_sk['sk_username']) && empty($cf7_sk['sk_password']) && empty($cf7_sk['sk_subdomain']));
  }


}