<?php

/**
 * Adds SalesKing fields to WPContactForm7. Display admin form fields, process form submits
 * Class SkCf7
 */
class SkCf7{


  /**
   * Save ContactForm SalesKing settings in admin
   * @param $cf WPCF7_ContactForm
   */
  function save_form($args) {
    update_option( 'cf7_sk_'.$args->id, $_POST['wpcf7-sk'] );
    // check the credentials on save
    $sk_activated = false;
    $sk_test_credentials = false;
    $wp_sk = $_POST['wpcf7-sk'];
    // sk activated ?
    if (isset($wp_sk['active']) && (strlen($wp_sk['active']) == 1) && ($wp_sk['active'] == 1) )
      $sk_activated = true;

    if (isset($wp_sk['test-credentials']) && $wp_sk['test-credentials'] == true )
      $sk_test_credentials = true;

    if (($sk_activated == true) && ($sk_test_credentials == true ))
      $this->validate_redentials($wp_sk);

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
    // hacky
    // check if the post contained the test and submit button
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
    if (isset($cf7_sk['test-credentials']) && ($cf7_sk['test-credentials'] == true ))
      $sk_test_credentials = true ;

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


  function validate_credentials($args){
    // this function calls the api, and tries a customer get
    // if there is no 200, send an error message
    $rest = $this->setup_rest_api($args);
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

  /**
   * @param $args array with sk credentials from post params
   * @return SkRest
   */
  function setup_rest_api($args){
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
  function set_defaults($cf7_sk){

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
  function has_credentials($cf7_sk){
    return !(empty($cf7_sk['sk_username']) && empty($cf7_sk['sk_password']) && empty($cf7_sk['sk_subdomain']));
  }

  /**
   * Sends form data to salesking, first substitutes placeholders in SK contact form setting fields
   * @param $obj WPCF7_ContactForm
   */
  function submit($obj){
    $cf7_sk = get_option( 'cf7_sk_'.$obj->id );
    if( $cf7_sk ) {
      $payload = array();
      $sk_fields = array('email', 'last_name', 'tag_list', 'lead_ref', 'phone_office', 'organisation', 'notes');
      foreach($sk_fields as $field){
        $tmp = $obj->replace_mail_tags($cf7_sk[$field] );
        $payload[$field] = strip_unprocessed_tags_or_null ( $cf7_sk[$field], $tmp);
      }
      // setup and post to api
      $sk_API = $this->setup_rest_api($cf7_sk);
      $ok = $sk_API->send_data($payload);
    }
  }

}