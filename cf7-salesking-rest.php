<?php
class SkRest {

    private $options = null;

    private $apis = array();

    private $apiStates = array();

    private $systemMessage = null;

    /**
     * checks current system status
     * @return bool
     */
    private function systemCheck() {
        if (!$this->getApiStatus()) {
            $this->systemMessage = __('Invalid SalesKing credentials', 'wpcf7-salesking' );
            return false;
        }

        return true;
    }

    /**
     *  callback function to output a system message in admin area
     */
    public function setSystemMessage() {
        echo '<div class="error">'.$this->systemMessage.'</div>';
    }

   
   
    /**
     * generate settings header
     * @return null
     */
    public function adminSettingsDisplay() {
        if ($this->options['message'] && $_GET['settings-updated'] == "true") {
            ?>
            <div class="error"><?php echo $this->options['message']; ?></div>
            <?php
        }
    }

    
    /**
     * Inject the options
     * @return null
     */
    public function setOptions($input){
        $this->options = $input;
   }
   
   
    /**
     * @param $input array
     *
     * @return mixed
     */
    public function validateSettings($input) {

        if ($input['sk_url'] OR $input['sk_username'] OR $input['sk_password']) {
            if ($this->getApiStatus($input['sk_url'], 
                                    $input['sk_username'], 
                                    $input['sk_password']) == false)
                {
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
        $sk_url = ($sk_url == null) ? $this->options['sk_url'] : $sk_url;
        $sk_username = ($sk_username == null) ? $this->options['sk_username'] : $sk_username;
        $sk_password = ($sk_password == null) ? $this->options['sk_password'] : $sk_password;

        // create a unique instance for every credential combination
        $hash = md5($sk_url.$sk_username.$sk_password);

        if (!array_key_exists($hash, $this->apis)) {
            // make sure that curl is available
            
            // make sure that curl is available
            if (!in_array('curl', get_loaded_extensions())) {
                echo "curl missing";
                return false;
            }

            require_once dirname(__FILE__).'/lib/salesking/salesking.php';

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
        if (array_key_exists($hash, $this->apiStates) == false)
        {
            if ((strlen($sk_url)>0) && 
                 (strlen($sk_username)>0) &&
                 (strlen($sk_password)>0))
                {
                $url = "/api/users/current";
                try {
                    $api = $this->getApi($sk_url, $sk_username, $sk_password);
                    if ($api == false) {
                        echo "curl stuff setup wrong??? api initialization failed";
                        wp_die("curl setup wrong!! api initialization failed");
                    }
                    $response = $api->request($url);
                }
                catch (SaleskingException $e) {
                    // in general something went wrong
                    $this->apiStates[$hash] = false;
                }
                // we have a response
                if ($response['code'] == '200') {
                    if (property_exists($response['body'],'user')) {
                        $this->apiStates[$hash] = true;
                    }
                // page not found?    
                }elseif ($response['code'] == '404'){
                    $this->apiStates[$hash] = false;
                    //var_dump($api);
                    //var_dump($response);
                    $msg = "salesking rest api http_return_code 404: ".$api->sk_url."".$url;
                    wp_die($msg);
                // redirect??
                }elseif ($response['code'] == '302'){
                    $this->apiStates[$hash] = false;
                    //var_dump($api);
                    //var_dump($response);
                    $msg = "salesking rest api http_return_code 302: (invalid credntials)".$api->sk_url."".$url;
                    //wp_die($msg);
                }
                else
                {
                    $this->apiStates[$hash] = false;
                }
            }
            else
            {
                $this->apiStates[$hash] = true;
            }
        }
        return $this->apiStates[$hash];
    }

    /**
     * return products from caching layers
     * @return bool|null
     */
    private function getProducts() {
        // cached in memory?
        if ($this->products == null) {
            // cached in db?
            if ( false === ( $products = get_transient( 'wpcf7-salesking_products' ) ) ) {
                // fetch from api and cache in db
                $products = $this->fetchProducts();
                set_transient('wpcf7-salesking_products', $products, 60*60);
            }

            // cache in memory
            $this->products = $products;
        }

        return $this->products;
    }

    /**
     * fetch products from api
     * @return bool
     */
    protected function fetchProducts() {
        $products = $this->getApi()->getCollection(array(
                'type' => 'product',
                'autoload' => true
            ));

        // filter products for specific tags
        try {
            $this->products = $products->tags($this->options['products_tag'])->load()->getItems();
        }
        catch (SaleskingException $e) {
            return false;
        }

        return $this->products;
    }

    /**
     * fetch pdf templates
     * @return bool|SaleskingCollection
     */
    protected function fetchPdfTemplates() {
        if ($this->pdfTemplates == null) {
            $templates = $this->getApi()->getCollection(array(
                    'type' => 'pdf_template',
                    'autoload' => true
                ));

            try {
                $this->pdfTemplates = $templates->kind($this->options['document_type'])->load()->getItems();
            }
            catch (SaleskingException $e) {
                return false;
            }
        }

        return $this->pdfTemplates;
    }

    /**
     * fetch email templates
     * @return bool|SaleskingCollection
     *
     */
    protected function fetchEmailTemplates() {
        if ($this->emailTemplates == null) {

            $templates = $this->getApi()->getCollection(array(
                    'type' => 'email_template',
                    'autoload' => true
                ));

            try {
                $this->emailTemplates = $templates->kind($this->options['document_type'])->load()->getItems();
            }
            catch (SaleskingException $e) {
                return false;
            }
        }

        return $this->emailTemplates;
    }


    /**
     * submit entered information to salesking api
     * @return bool
     */
    private function send() {
        // set up objects
        $address = $this->getApi()->getObject('address');
        $contact = $this->getApi()->getObject('contact');
        $document = $this->getApi()->getObject($this->options['document_type']);
        $comment = $this->getApi()->getObject('comment');

        // bind address data
        try {
            $address->bind($_POST, array(
                "wpcf7-salesking_contact_address1" => "address1",
                "wpcf7-salesking_contact_city" => "city",
                "wpcf7-salesking_contact_zip" => "zip",
                "wpcf7-salesking_contact_country" => "country"
            ));
        }
        catch (SaleskingException $e) {
            return false;
        }

        // bind contact data
        try {
            $contact->bind($_POST, array(
                "wpcf7-salesking_contact_organisation_name" => "organisation",
                "wpcf7-salesking_contact_last_name" => "last_name",
                "wpcf7-salesking_contact_first_name" => "first_name",
                "wpcf7-salesking_contact_email" => "email",
                "wpcf7-salesking_contact_phone" => "phone_office"
            ));

            // set tags, type and address
            $contact->type = $this->options['contact_type'];
            $contact->tag_list = $this->options['contact_tags'];
            $contact->addresses = array($address->getData());
        }
        catch (SaleskingException $e) {
            return false;
        }

        // save contact
        try {
            $contact->save();
        }
        catch (SaleskingException $e) {
            return false;
        }

        // set all the other options for the document
        try {
            $document->contact_id = $contact->id;
            $document->tag_list = $this->options['document_tags'];
            $document->notes_before = $this->options['notes_before'];
            $document->notes_after = $this->options['notes_after'];
            $document->status = "open";
            $document->line_items = $items;
        }
        catch (SaleskingException $e) {
            die(print_r($e));
        }

        // save document
        try {
            $document->save();
        }
        catch (SaleskingException $e) {
            return false;
        }

        // set up comment
        if (isset($_POST['wpcf7-salesking_comment']) AND trim($_POST['wpcf7-salesking_comment']) != "") {
            try {
                $comment->text = $_POST['wpcf7-salesking_comment'];
                $comment->related_object_type = 'Document';
                $comment->related_object_id = $document->id;
                $comment->save();
            }
            catch (SaleskingException $e) {
                return false;
            }

        }
        
    }
}
?>