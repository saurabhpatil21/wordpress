<?php
    /*
      Plugin Name: Contact Form 7 Email Validation
      Plugin URI:
      Description: Add a customized functionality to add a email field validation check point to the popular Contact Form 7 plugin. A DNS verification validation has been integrated to verify email address from a valid domain.
      Author: Aashish Sonawane
      Author URI:
      Version: 1.0
      Text Domain: contact-form-7-email-validation
     * 
     */

    /**
     * 
     * Check if CF7 is installed and activated.
     * 		Deliver a message to install CF7 if not.
     * 
     */

    add_action('admin_init', 'wpcf7_email_validation_has_parent_plugin');

    function wpcf7_email_validation_has_parent_plugin() {
        if (is_admin() && current_user_can('activate_plugins') && !is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
            add_action('admin_notices', 'wpcf7_email_validation_nocf7_notice');

            deactivate_plugins(plugin_basename(__FILE__));

            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }
    }

    function wpcf7_email_validation_nocf7_notice() {
        ?>
        <div class="error">
            <p>
                <?php
                printf(
                        __('%s must be installed and activated for the CF7 Email Validation Check plugin to work', 'contact-form-7-email-validation'), '<a href="' . admin_url('plugin-install.php?tab=search&s=contact+form+7') . '">Contact Form 7</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    function wpcf7_validate_email_check($email) {
       
        $exp = "^[a-z\'0-9]+([._-][a-z\'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$";

       if (eregi($exp, $email)) {
            if (checkdnsrr(array_pop(explode("@", $email)), "MX")) {
                return true;
            } else {
                return false;
            }
       } else {
            return false;
        }
    }

    function wpcf7_custom_email_validation_filter($result, $tag) {
                
        $tag = new WPCF7_Shortcode( $tag );

        $type = $tag->type;
        $name = $tag->name;

        if ('email' == $type || 'email*' == $type) { // Only apply to fields with the form field name of "company-email"

            $email_value = $_POST[$name];

            if(!wpcf7_validate_email_check($email_value)){
                 $result->invalidate( $tag, "Email address entered is invalid, DNS resolution failed.");
            }
        }
        return $result;
    }

    add_filter('wpcf7_validate_email', 'wpcf7_custom_email_validation_filter', 20, 2); // Email field
    add_filter('wpcf7_validate_email*', 'wpcf7_custom_email_validation_filter', 20, 2); // Req. Email field