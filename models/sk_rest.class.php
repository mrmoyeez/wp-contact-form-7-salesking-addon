<?php
/**
 * Class SkRest submit data to SalesKing API
 */
class SkRest {
  private $config = null;
  public $errors = array();

  /**
   * @param $config array with sk credentials from post params:
   *  $config['sk_url'];
   *  $config['sk_username'];
   *  $config['sk_password'];
   */
  public function __construct($config = array()){
    $this->config = $config;
  }

  /**
   * validate if all field for login are set: user, pass, url
   * @param $data  array with keys: sk_username, sk_password, sk_url
   * @return bool
   */
  public static function has_credentials($data){
    return !empty($data['sk_username']) && !empty($data['sk_password']) && !empty($data['sk_url']);
  }

  /**
   * validate the credentials in config
   * @return $this->config plus ['message'] if something is wrong
   */
  public function test_credentials(){
    if (!self::has_credentials( $this->config)) {
      $this->errors[]= __('Missing credentials', 'wpcf7-salesking' );
      return false;
    }
    $this->getApiStatus();
  }

  /**
   * set up Salesking PHP library
   * @return bool|Salesking
   */
  private function getApi() {
    // make sure that curl is available
    if (!in_array('curl', get_loaded_extensions())) {
      $this->errors[] = "PHP curl module is missing on your server. Please contact your admin!";
      return false;
    }
    require_once dirname(__FILE__) . '/../vendor/salesking/salesking.php';
    // set up object
    $config = array(
      "sk_url" => $this->config['sk_url'],
      "user" => $this->config['sk_username'],
      "password" => $this->config['sk_password']
    );
    try {
      $sk = new Salesking($config);
    } catch (SaleskingException $e) {
      // in general something went wrong
      $this->errors[] = $e->getMessage();
      return false;
    }
    return $sk;
  }

  /**
   *
   * fetch current api status
   * @return bool
   */
  private function getApiStatus() {
    // make request
    $url = "/api/users/current";

    try {
      $api = $this->getApi();
      $response = $api->request($url);
    } catch (SaleskingException $e) {
      // in general something went wrong
      $this->errors[] = $e->getMessage();
      return false;
    }

    if (!empty($this->errors ) )
      return false;

    // we have a response
    if ($response['code'] == '200'){
      if( property_exists($response['body'],'user')) {
        return true;
      }else{
        $this->errors[] = "SalesKing api user could not be found";
      }
    }elseif ($response['code'] == '404'){
      // page not found?
      $this->errors[] = "SalesKing api not found at: ".$api->sk_url;
    } elseif ($response['code'] == '302'){
      // redirect??
      $this->errors[] =  "Invalid credentials for ".$api->sk_url;
    }else{
      $this->errors[] =  "SalesKing API unexpected http_return_code ".$response['code'];
    }
    return false;
  }
    
  /**
   * does the transfer
   * @param $form_data array
   * @return bool*
  */
  public function send_data($form_data){
    $contact = $this->getApi()->getObject('contact');
    $contact->type = 'Lead';
    // some error prevention massages
    if (empty($form_data['organisation']) && empty($form_data['last_name']) && !(empty($form_data['email'])) ){
      $form_data['last_name'] = $form_data['email'];
    }

    try {
      $contact->bind($form_data);
    } catch (SaleskingException $e) {
      return false;
    }

    // save contact
    try {
      $contact->save();
    } catch (SaleskingException $e) {
      var_dump($e);
      wp_die("SalesKing transmission failed - email not sent - error - contact admin");
      return false;
    }
    return true;
  }
}