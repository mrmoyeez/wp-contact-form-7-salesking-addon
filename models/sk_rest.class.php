<?php
/**
 * Class SkRest submit data to SalesKing API
 */
class SkRest {
  private $options = null;
  private $apis = array();
  private $apiStates = array();

  /**
   * @param $args array with sk credentials from post params
   * @return SkRest
   */
  public static function init($args){
    $input =  array();
    $input['sk_url'] = $args['sk_url'];
    $input['sk_username'] = $args['sk_username'];
    $input['sk_password'] = $args['sk_password'];
    $rest = new SkRest();
    $rest->setOptions($input);
    return $rest;
  }

  /**
   * validate if all field for login are set: user, pass, url
   * @param $data  array with keys: sk_username, sk_password, sk_url
   * @return bool
   */
  public static function has_credentials($data){
    return !(empty($data['sk_username']) && empty($data['sk_password']) && empty($data['sk_url']));
  }

  /**
  * Inject the options
  * @param $input  array with keys: sk_username, sk_password, sk_url
  * @return null
  */
  public function setOptions($input){
      $this->options = $input;
  }

  /**
   * validate the options
   * @return $this->options plus ['message'] if something is wrong
   */
  public function validateOptions(){
      $input = $this->options;
      if (!self::has_credentials( $this->options)) {
        $input['message'] = __('Missing credentials', 'wpcf7-salesking' );
      }else{
        if ($this->getApiStatus($input['sk_url'], $input['sk_username'], $input['sk_password']) == false) {
          $input['sk_password'] = '';
          $input['message'] = __('Invalid credentials', 'wpcf7-salesking' );
        }
      }
      return $input;
  }

  /**
   * set up Salesking PHP library
   * @param $sk_url
   * @param $sk_username
   * @param $sk_password
   * @return bool|Salesking
   */
  private function getApi($sk_url = null, $sk_username = null, $sk_password = null) {
    // check if credentials are provided and switch to default values
    $sk_url      = ($sk_url == null)      ? $this->options['sk_url'] : $sk_url;
    $sk_username = ($sk_username == null) ? $this->options['sk_username'] : $sk_username;
    $sk_password = ($sk_password == null) ? $this->options['sk_password'] : $sk_password;

    // create a unique instance for every credential combination
    $hash = md5($sk_url.$sk_username.$sk_password);
    if (!array_key_exists($hash, $this->apis)) {
      // make sure that curl is available
      if (!in_array('curl', get_loaded_extensions())) {
          echo "curl missing";
          return false;
      }
      require_once dirname(__FILE__) . '/../vendor/salesking/salesking.php';
      // set up object
      $config = array(
          "sk_url" => $sk_url,
          "user" => $sk_username,
          "password" => $sk_password
      );
      $this->apis[$hash] = new Salesking($config);
    }
    return $this->apis[$hash];
  }

  /**
   *
   * fetch current api status
   * @param $sk_url string
   * @param $sk_username string
   * @param $sk_password string
   * @return bool
   */
  private function getApiStatus($sk_url = null, $sk_username = null, $sk_password = null) {
    // check if credentials are provided and switch to default values
    $sk_url = ($sk_url == null) ? $this->options['sk_url'] : $sk_url;
    $sk_username = ($sk_username == null) ? $this->options['sk_username'] : $sk_username;
    $sk_password = ($sk_password == null) ? $this->options['sk_password'] : $sk_password;
    // create a unique instance for every credential combination
    $hash = md5($sk_url.$sk_username.$sk_password);
    // get new status only
    if (array_key_exists($hash, $this->apiStates) != false) {
      return $this->apiStates[$hash];
    }
    if ( strlen($sk_url)>0 && strlen($sk_username)>0 && strlen($sk_password)>0) {
      $this->apiStates[$hash] = true;
      return $this->apiStates[$hash];
    }
    // make request
    $url = "/api/users/current";
    try {
      $api = $this->getApi($sk_url, $sk_username, $sk_password);
      if ($api == false) {
          echo "curl stuff setup wrong??? api initialization failed";
          wp_die("curl setup wrong!! api initialization failed");
      }
      $response = $api->request($url);
    } catch (SaleskingException $e) {
      // in general something went wrong
      $this->apiStates[$hash] = false;
    }
    // we have a response
    if ($response['code'] == '200' && property_exists($response['body'],'user')) {
      $this->apiStates[$hash] = true;
    }elseif ($response['code'] == '404'){
      // page not found?
      $this->apiStates[$hash] = false;
      $msg = "salesking rest api http_return_code 404: ".$api->sk_url."".$url;
      wp_die($msg);
    } elseif ($response['code'] == '302'){
      // redirect??
      $this->apiStates[$hash] = false;
      $msg = "salesking rest api http_return_code 302: (invalid credentials)".$api->sk_url."".$url;
      //              wp_die($msg);
    }else{
      $this->apiStates[$hash] = false;
    }
    return $this->apiStates[$hash];
}
    
    
  /**
   * does the transfer
   * @param $form_data array
   * @return bool*
  */
  public function send_data($form_data){
    $ok = false;
    $contact = $this->getApi()->getObject('contact');
    $contact->type = 'Lead';
    // some error prevention massages
    if (empty($form_data['organisation']) &&
        empty($form_data['last_name']) &&
        !(empty($form_data['email']))
    ){
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
      wp_die("salesking transmission failed - email not sent - error - contact admin");
      return false;
    }
    return true;
  }
}