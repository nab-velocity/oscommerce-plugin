<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('MODULE_PAYMENT_VELOCITY_TEXT_TITLE', 'Credit Card - Velocity');
define('MODULE_PAYMENT_VELOCITY_TEXT_DESCRIPTION', 'Payment Done Via velocity gateway.');
define('MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_OWNER', 'Credit Card Owner:');
define('MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
define('MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
define('MODULE_PAYMENT_VELOCITY_TEXT_CVV', 'CVV Number:');
define('MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_OWNER', '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
define('MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
define('MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_CVV', '* The 3 or 4 digit CVV number must be entered from the back of the credit card.\n');
define('MODULE_PAYMENT_VELOCITY_TEXT_ERROR_MESSAGE', 'There has been an error processing your credit card. Please try again.');
define('MODULE_PAYMENT_VELOCITY_TEXT_DECLINED_MESSAGE', 'Your credit card was declined. Please try another card or contact your bank for more info.');
define('MODULE_PAYMENT_VELOCITY_TEXT_ERROR', 'Credit Card Error!');
define('TABLE_PAYMENT_VELOCITY_TRANSACTIONS', 'velocity_transactions');
define('FILENAME_VELOCITY_REFUND', 'velocityRefund.php');
define('FILENAME_CHECKOUT_FAILURE', 'checkout_failure.php');
?>