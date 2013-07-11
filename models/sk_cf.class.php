<?php
// init the class on the post edit screen
function call_SkCf(){  return new SkCf(); }
add_action( 'init', 'call_SkCf' );
/**
 * Process form submits from frontend
 * Class SkCf
 */
class SkCf{

  /**
   * register all needed hooks
   */
  function __construct(){
    add_action( 'wpcf7_before_send_mail', array( $this, 'submit' ) );
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
        $payload[$field] = $this->clean_tags ( $cf7_sk[$field], $tmp);
      }
      // setup and post to api
      $sk_API = $this->setup_rest_api($cf7_sk);
      $ok = $sk_API->send_data($payload);
    }
  }


  private function clean_tags($field_name, $output_str){
    $begin_tag_indicator= "[";
    $end_tag_indicator = "]";
    $begin_pos = strpos($field_name, $begin_tag_indicator);
    $end_pos = strpos($output_str, $end_tag_indicator);
    // values are unprocessed, and there seems to be a tag left
    if (($field_name == $output_str) && ( $begin_pos <> $end_pos))
      $output_str = "";

    return $output_str;
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


}