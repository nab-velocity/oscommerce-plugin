<?php
/**
 * Velcity payment method class
 *
 * @package paymentMethod
 * @copyright Copyright velocity.
 * @copyright Portions Copyright 2003 osCommerce
 * @license 
 * @version GIT: $Id: Author: Ashish  Tue Aug 1 2015
 */
 
/**
 * Velocity payment method class
 * 
 */

class velocity {
      
    var $code, $title, $description, $enabled;

    function velocity() {
        global $order;

        $this->code = 'velocity';
        $this->title = MODULE_PAYMENT_VELOCITY_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_VELOCITY_TEXT_DESCRIPTION;
        $this->enabled = defined('MODULE_PAYMENT_VELOCITY_STATUS') && (MODULE_PAYMENT_VELOCITY_STATUS == 'True') ? true : false;


        if ( $this->enabled === true ) {
            if ( isset($order) && is_object($order) ) {
                $this->update_status();
            }
        }
    }

    function update_status() {
        global $order;

        if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_COD_ZONE > 0) ) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_COD_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }

        // disable the module if the order only contains virtual products
        if ($this->enabled == true) {
          if ($order->content_type == 'virtual') {
            $this->enabled = false;
          }
        }
    }
    
    function javascript_validation() {
        $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
                  '    var cc_owner = document.checkout_payment.velocity_cc_owner.value;' . "\n" .
                  '    var cc_number = document.checkout_payment.velocity_cc_number.value;' . "\n";
        if ('True') {
                $js .= '    var cc_cvv = document.checkout_payment.velocity_cc_cvv.value;' . "\n";
        }
        $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
                  '      error_message = error_message + "' . MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_OWNER . '";' . "\n" .
                  '      error = 1;' . "\n" .
                  '    }' . "\n" .
                  '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
                  '      error_message = error_message + "' . MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_NUMBER . '";' . "\n" .
                  '      error = 1;' . "\n" .
                  '    }' . "\n";
        if ('True') {
                $js .= '    if (cc_cvv == "" || !(/^\+?(0|[1-9]\d*)$/.test(cc_cvv)) || cc_cvv.length < "3" || cc_cvv.length > "4") {' . "\n".
                '      error_message = error_message + "' . MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_CVV . '";' . "\n" .
                '      error = 1;' . "\n" .
                '    }' . "\n" ;
        }
        $js .= '  }' . "\n";

        return $js;
    }

    function selection() {
        global $order;
        
        for ($i=1; $i<13; $i++) {
          $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B - (%m)',mktime(0,0,0,$i,1,2000)));
        }

        $today = getdate();
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
          $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }
        
        $onFocus = 'onfocus="methodSelect(\'pmt-' . $this->code . '\')"';
        
        $selection = array( 'id'     => $this->code,
                            'module' => $this->title,
                            'fields' => array(
                                            array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_OWNER,
                                                  'field' => tep_draw_input_field('velocity_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'id="'.$this->code.'-cc-owner"' . $onFocus . ' autocomplete="off"'),
                                                  'tag'   => $this->code.'-cc-owner'),
                                            array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_NUMBER,
                                                  'field' => tep_draw_input_field('velocity_cc_number', '', 'id="'.$this->code.'-cc-number"' . $onFocus . ' autocomplete="off"'),
                                                  'tag'   => $this->code.'-cc-number'),
                                            array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_EXPIRES,
                                                  'field' => tep_draw_pull_down_menu('velocity_cc_expires_month', $expires_month, strftime('%m'), 'id="'.$this->code.'-cc-expires-month"' . $onFocus) . '&nbsp;' . tep_draw_pull_down_menu('velocity_cc_expires_year', $expires_year, '', 'id="'.$this->code.'-cc-expires-year"' . $onFocus),
                                                  'tag'   => $this->code.'-cc-expires-month'),
                                            array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CVV,
                                                  'field' => tep_draw_password_field('velocity_cc_cvv', '', 'size="4" maxlength="4"' . ' id="'.$this->code.'-cc-cvv"' . $onFocus . ' autocomplete="off"'),
                                                  'tag'   => $this->code.'-cc-cvv')	   
                            )
        );

        return $selection;
        
    }

    function pre_confirmation_check() {
        
        global $messageStack;

        if (isset($_POST['velocity_cc_number'])) {
                include(DIR_WS_CLASSES . 'cc_validation.php');

                $cc_validation = new cc_validation();
                $result = $cc_validation->validate($_POST['velocity_cc_number'], $_POST['velocity_cc_expires_month'], $_POST['velocity_cc_expires_year']);
                $error = '';
                switch ($result) {
                        case -1:
                        $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
                        break;
                        case -2:
                        case -3:
                        case -4:
                        $error = TEXT_CCVAL_ERROR_INVALID_DATE;
                        break;
                        case false:
                        $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
                        break;
                }

                if ( ($result == false) || ($result < 1) ) {
                        tep_session_register('payment_error');
                        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=' . $error));
                }

                $this->cc_card_type    = $cc_validation->cc_type;
                $this->cc_card_number  = $cc_validation->cc_number;
                $this->cc_expiry_month = $cc_validation->cc_expiry_month;
                $this->cc_expiry_year  = $cc_validation->cc_expiry_year;

        }
    }

    /**
     * Display Credit Card Information on the Checkout Confirmation Page
     *
     * @return array
     */
    function confirmation() {
      if (isset($_POST['velocity_cc_number'])) {
            $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                                  'fields' => array(array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_OWNER,
                                  'field' => $_POST['velocity_cc_owner']),
                            array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_NUMBER,
                                  'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                            array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_EXPIRES,
                                  'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['velocity_cc_expires_month'], 1, '20' . $_POST['velocity_cc_expires_year'])))));
        } else {
            $confirmation = array();
        }
        return $confirmation;
    }

    /**
     * Build the data and actions to process when the "Submit" button is pressed on the order-confirmation screen.
     * This prepare the card detail and address detail for the verify transaction.
     *
     * @return string
     */
    function process_button() {
        global $order;

        $avsData = array (
            'Street'        => $order->billing['street_address'],
            'City'          => $order->billing['city'],
            'StateProvince' => '',
            'PostalCode'    => $order->billing['postcode'],
            'Country'       => 'USA'
        );

        $cardData = array (
            'cardtype'    => str_replace(' ', '', $this->cc_card_type), 
            'pan'         => $this->cc_card_number, 
            'expire'      => $this->cc_expiry_month.substr($this->cc_expiry_year, -2), 
            'cvv'         => $_POST['velocity_cc_cvv'],
            'track1data'  => '', 
            'track2data'  => ''
        );

        $_SESSION['avsdata'] = base64_encode(serialize($avsData));
        $_SESSION['carddata'] = base64_encode(serialize($cardData));

        return false;
    }

     /**
     * @return booolen
     *
     */
    function before_process() {

//        $file = 'admin/orders.php';
//
//        $person = '<script type="text/javascript"> (function(funcName, baseObj) {
//            // The public function name defaults to window.docReady
//            // but you can pass in your own object and own function name and those will be used
//            // if you want to put them in a different namespace
//            funcName = funcName || "docReady";
//            baseObj = baseObj || window;
//            var readyList = [];
//            var readyFired = false;
//            var readyEventHandlersInstalled = false;
//
//            // call this when the document is ready
//            // this function protects itself against being called more than once
//            function ready() {
//                if (!readyFired) {
//                    // this must be set to true before we start calling callbacks
//                    readyFired = true;
//                    for (var i = 0; i < readyList.length; i++) {
//                        // if a callback here happens to add new ready handlers,
//                        // the docReady() function will see that it already fired
//                        // and will schedule the callback to run right after
//                        // this event loop finishes so all handlers will still execute
//                        // in order and no new ones will be added to the readyList
//                        // while we are processing the list
//                        readyList[i].fn.call(window, readyList[i].ctx);
//                    }
//                    // allow any closures held by these functions to free
//                    readyList = [];
//                }
//            }
//
//            function readyStateChange() {
//                if ( document.readyState === "complete" ) {
//                    ready();
//                }
//            }
//
//                // This is the one public interface
//                // docReady(fn, context);
//                // the context argument is optional - if present, it will be passed
//                // as an argument to the callback
//                baseObj[funcName] = function(callback, context) {
//                    // if ready has already fired, then just schedule the callback
//                    // to fire asynchronously, but right away
//                    if (readyFired) {
//                        setTimeout(function() {callback(context);}, 1);
//                        return;
//                    } else {
//                        // add the function and context to the list
//                        readyList.push({fn: callback, ctx: context});
//                    }
//                    // if document already ready to go, schedule the ready function to run
//                    if (document.readyState === "complete") {
//                        setTimeout(ready, 1);
//                    } else if (!readyEventHandlersInstalled) {
//                        // otherwise if we don"t have event handlers installed, install them
//                        if (document.addEventListener) {
//                            // first choice is DOMContentLoaded event
//                            document.addEventListener("DOMContentLoaded", ready, false);
//                            // backup is window load event
//                            window.addEventListener("load", ready, false);
//                        } else {
//                            // must be IE
//                            document.attachEvent("onreadystatechange", readyStateChange);
//                            window.attachEvent("onload", ready);
//                        }
//                        readyEventHandlersInstalled = true;
//                    }
//                }
//            })("docReady", window);
//            docReady(function() {
//                var e = document.getElementById("contentText");
//                button1 = document.createElement("input");
//                button1.type = "submit";
//                button1.name = "refund";
//                button1.value = "Do Velocity Refund";
//                form = document.createElement("form");
//                form.setAttribute("id", "velocity_refund");
//                form.setAttribute("action", "http://localhost/oscommerce/admin/orders.php?page=1&oID=29&action=edit");
//                form.appendChild(button1);
//                e.appendChild(form);
//            });</script>';
//
//        file_put_contents($file, $person, FILE_APPEND | LOCK_EX);
//
//        die;
      return false;
    }

    /**
     * Post-processing activities for send detail to velocity gateway for the verify the detail and process the payment 
     * Trought velocity gateway and return response.
     * 
     * @return boolean
     */
    function after_process() {
        
        include_once('includes/sdk/Velocity.php');
        global $order, $insert_id, $db, $messageStack;
        
        $identitytoken        = MODULE_PAYMENT_VELOCITY_IDENTITYTOKEN;
        $workflowid           = MODULE_PAYMENT_VELOCITY_WORKFLOWID;
        $applicationprofileid = MODULE_PAYMENT_VELOCITY_APPLICATIONPROFILEID;
        $merchantprofileid    = MODULE_PAYMENT_VELOCITY_MERCHANTPROFILEID;

        if (MODULE_PAYMENT_VELOCITY_TESTMODE)
            $isTestAccount = TRUE;
        else
            $isTestAccount = FALSE;

        try {            
            $velocityProcessor = new VelocityProcessor( $applicationprofileid, $merchantprofileid, $workflowid, $isTestAccount, $identitytoken );    
        } catch (Exception $e) {
            tep_session_register('payment_error');
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_FAILURE, 'payment_error=' . $this->code . '&error=' . $e->getMessage()));
        }

        $avsData = unserialize(base64_decode($_SESSION['avsdata']));
        $cardData = unserialize(base64_decode($_SESSION['carddata']));

        /* Request for the verify avsdata and card data*/
        try {
            
            $response = $velocityProcessor->verify(array(  
                    'amount'       => $order->info['total'],
                    'avsdata'      => $avsData, 
                    'carddata'     => $cardData,
                    'entry_mode'   => 'Keyed',
                    'IndustryType' => 'Ecommerce',
                    'Reference'    => 'xyz',
                    'EmployeeId'   => '11'
            ));

        } catch (Exception $e) {
            tep_session_register('payment_error');
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_FAILURE, 'payment_error=' . $this->code . '&error=' . $e->getMessage()));
        }

        $errors = '';
        if (is_array($response) && isset($response['Status']) && $response['Status'] == 'Successful') {

                /* Request for the authrizeandcapture transaction */
                try {
                        $cap_response = $velocityProcessor->authorizeAndCapture( array(
                                'amount'       => $order->info['total'], 
                                'avsdata'      => $avsData,
                                'token'        => $response['PaymentAccountDataToken'], 
                                'order_id'     => $insert_id,
                                'entry_mode'   => 'Keyed',
                                'IndustryType' => 'Ecommerce',
                                'Reference'    => 'xyz',
                                'EmployeeId'   => '11'
                        ));
                        
                        $xml = VelocityXmlCreator::authorizeandcaptureXML( array(
                                'amount'       => $order->info['total'], 
                                'avsdata'      => $avsData,
                                'token'        => $response['PaymentAccountDataToken'], 
                                'order_id'     => $insert_id,
                                'entry_mode'   => 'Keyed',
                                'IndustryType' => 'Ecommerce',
                                'Reference'    => 'xyz',
                                'EmployeeId'   => '11'
                        ));  // got authorizeandcapture xml object. 

                        $req = $xml->saveXML();

                        if ( is_array($cap_response) && !empty($cap_response) && isset($cap_response['Status']) && $cap_response['Status'] == 'Successful') {

                                /* save the transaction detail with that order.*/ 
                                $comments = 'Credit Card - Velocity payment.  ApprovalCode: ' . $cap_response['ApprovalCode'] . '. TransID: ' . $cap_response['TransactionId'] . '.';
                                $history = tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . (int)$insert_id . "', '2', now(),'1','" . tep_db_input($comments) . "')");
                               
                                /* save the authandcap response into 'zen_velocity_transactions' custom table.*/ 
                                $sql = tep_db_query("insert into " . TABLE_PAYMENT_VELOCITY_TRANSACTIONS . " (transaction_id, transaction_status, order_id, request_obj, response_obj) values ('" . $cap_response['TransactionId'] ."', '" . $cap_response['Status'] . "', '" . $insert_id . "', '" . serialize($req) ."', '" . serialize($cap_response) . "')");

                                /* for update the order status */
                                $orderstatus = tep_db_query("update " . TABLE_ORDERS . " set orders_status = 2 where orders_id='" . $insert_id . "'"); 

                                
                        } else if ( is_array($cap_response) && !empty($cap_response) ) {
                                $errors .= $cap_response['StatusMessage'];
                        } else if (is_string($cap_response)) {
                                $errors .= $cap_response;
                        } else {
                                $errors .= 'Unknown Error in authandcap process please contact the site admin';
                        }
                } catch(Exception $e) {
                    $errors .= $e->getMessage();
                }

        } else if (is_array($response) &&(isset($response['Status']) && $response['Status'] != 'Successful')) {
            $errors .= $response['StatusMessage'];
        } else if (is_string($response)) {
            $errors .= $response;
        } else {
            $errors .= 'Unknown Error in verification process please contact the site admin';
        }

        if ($errors != '') {
            tep_session_register('payment_error');
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_FAILURE, 'payment_error=' . $this->code . '&error=' . $e->getMessage()));
        }

        return true;
    }

     /**
     * @return errors array
     *
     */
    function get_error() {
       global $HTTP_GET_VARS;
       return $HTTP_GET_VARS;
    }

    function check() {  
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_VELOCITY_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {

        /* create custom velocity table if install this module first time */
        $sql = "CREATE TABLE IF NOT EXISTS velocity_transactions(
                id int not null auto_increment, 
                transaction_id varchar(220), 
                transaction_status varchar(100) not null, 
                order_id varchar(10) not null, 
                request_obj text not null,
                response_obj text not null, 
                primary key(id)
              )";
        
        tep_db_query($sql);
        
        /* add velocity configuration credential after installation */
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Velocity Credit Card Module', 'MODULE_PAYMENT_VELOCITY_STATUS', 'False', 'Do you want to accept Velocity Credit Card payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Identity Token', 'MODULE_PAYMENT_VELOCITY_IDENTITYTOKEN', '0', 'Identity Token is the long lived security token provided by velocity', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('WorkflowId/ServiceId', 'MODULE_PAYMENT_VELOCITY_WORKFLOWID', '0', 'Workflowid or Serviceid is the service identification number', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ApplicationProfileId', 'MODULE_PAYMENT_VELOCITY_APPLICATIONPROFILEID', '0', 'ApplicationProfileId application id for perticular merchant.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MerchantProfileId', 'MODULE_PAYMENT_VELOCITY_MERCHANTPROFILEID', '0', 'MerchantProfileId merchant identification code.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Mode', 'MODULE_PAYMENT_VELOCITY_TEST_LIVE', 'True', 'Select Velocity gateway test or production mode', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    
        /* For add custom refund order status */
        $v_status = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Velocity Refund'");
        if (isset($v_status->num_rows) && $v_status->num_rows == 0) {
                $max_order_id = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS );
                $v_order_id = (int)$max_order_id->num_rows + 1;
                tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name, public_flag, downloads_flag) values (" . $v_order_id . ",1,'Velocity Refund', 1, 0)");
        }
        
    }

    function remove() {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
        return array('MODULE_PAYMENT_VELOCITY_STATUS', 'MODULE_PAYMENT_VELOCITY_IDENTITYTOKEN', 'MODULE_PAYMENT_VELOCITY_WORKFLOWID', 'MODULE_PAYMENT_VELOCITY_APPLICATIONPROFILEID', 'MODULE_PAYMENT_VELOCITY_MERCHANTPROFILEID', 'MODULE_PAYMENT_VELOCITY_TEST_LIVE');
    }
    
  }
?>
