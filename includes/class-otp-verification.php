<?php
defined('ABSPATH') || exit;

class IMQ_OTP_Verification
{
    /**
     * The single instance of the class.
     * 
     * @var IMQ_OTP_Verification
     * @access private
     */
    private static $_instance = null;



    /**
     * Private constructor to prevent instantiation from outside of the class.
     * 
     * @access private
     * @final
     */
    private final function __construct()
    {
        add_action('init', array($this, 'start_session_on_init'));
        add_action('woocommerce_register_form', array($this,  'opt_verification_registration_fields_after_email'), 20);
        add_filter('woocommerce_registration_errors',  array($this, 'otp_verification_registration_errors'), 100, 3);
        add_action('wp_head', array($this, 'otp_input_show_hide'), 20);
    }

    /**
     * Starts a new session if one is not already active.
     *
     * This function hooks into the 'init' action to ensure that a session is
     * started at the beginning of a page load, enabling session management
     * throughout the request lifecycle.
     */
    public function start_session_on_init()
    {
        if (!session_id()) {
            session_start();
        }
    }

    /**
     * Adds an OTP input field to the registration form after the email field.
     *
     * This function checks if the current page is an account page and if the user is not logged in.
     * If both conditions are met, it adds a required OTP field to the form using WooCommerce's form field function.
     * The field is of text type and includes a label and placeholder for user input.
     */
    public function opt_verification_registration_fields_after_email()
    {
        if (is_account_page() && !is_user_logged_in()) {
            $otp_field = [
                'type'        => 'text',
                'required'    => true,
                'label'       => __('OTP', 'integritymp-quote'),
                'class'       => ['form-row-wide'],
                'placeholder' => __('Enter OTP', 'integritymp-quote'),
                'clear'       => true,
            ];
            woocommerce_form_field('otp', $otp_field, $_POST['otp'] ?? '');
        }
    }


    public function otp_input_show_hide()
    {
        if (empty($_POST)) {
            unset($_SESSION['imq_user_otp']);
            unset($_SESSION['imq_user_email']);
        }
        if (!isset($_SESSION['imq_user_otp'])) {
            echo <<<HTML
            <style>
                #otp_field {
                    display: none;
                }
            </style>
            HTML;
        }
    }


    /**
     * Adds an error to the WooCommerce registration errors object if the OTP sent to the user does not match the one entered.
     *
     * This function checks if the current page is an account page and if the user is not logged in.
     * If both conditions are met, it checks if the OTP session variables are set. If they are, it
     * compares the user-submitted OTP with the stored OTP. If they match, it unsets the session
     * variables and returns the validation errors object. If they don't match, or if the OTP session
     * variables are not set, it adds an error to the validation errors object.
     *
     * @param WP_Error $validation_errors - The validation errors object.
     * @param string   $username         - The username entered by the user.
     * @param string   $email            - The email entered by the user.
     *
     * @return WP_Error - The validation errors object with the added error.
     */
    public function otp_verification_registration_errors($validation_errors, $username, $email)
    {
        if (isset($_SESSION['imq_user_otp']) && isset($_SESSION['imq_user_email'])) {
            if (isset($_POST['otp']) && ! empty($_POST['otp'])) {
                $user_otp = sanitize_text_field($_POST['otp']);
                $stored_otp = $_SESSION['imq_user_otp'];

                $user_email = $_POST['email'];
                $stored_email = $_SESSION['imq_user_email'];

                if ($user_otp == $stored_otp && $user_email == $stored_email) {
                    unset($_SESSION['imq_user_otp']);
                    unset($_SESSION['imq_user_email']);
                    return $validation_errors;
                } else {
                    $validation_errors->add('otp_error', __('Invalid OTP. Please try again.', 'integritymp-quote'));
                }
            } else {
                $validation_errors->add('otp_error', __('Please enter the OTP sent to your email.', 'integritymp-quote'));
            }
        } else {
            if (isset($_POST['email']) && ! empty($_POST['email'])) {
                $email = sanitize_email($_POST['email']);
                $this->send_otp_to_email($email);

                wc_add_notice(__('An OTP has been sent to your email. Please enter it below.', 'woocommerce'), 'notice');
                $validation_errors->add('otp_error', __('', 'integritymp-quote'));
            }
        }
        return $validation_errors;
    }


    /**
     * Sends an OTP to the given email address and stores it in session variables.
     *
     * @param string $email The email address to send the OTP to.
     *
     * @return int The generated OTP.
     */
    public function send_otp_to_email($email)
    {
        $otp = rand(100000, 999999);

        $_SESSION['imq_user_otp'] = $otp;
        $_SESSION['imq_user_email'] = $email;

        // Send OTP email to user
        $subject = 'Your OTP for Registration';
        require_once IMQ_PLUGIN_DIR_PATH . 'view/otp-email.php';

        $admin_email = get_option('admin_email');
        $site_title = get_bloginfo('name');
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_title . ' <' . $admin_email . '>'
        );

        $html_content = imq_get_opt_email($otp, $admin_email);
        wp_mail($email, $subject, $html_content, $headers);

        return $otp;
    }


    /**
     * Gets the singleton instance of the class.
     *
     * @return IMQ_OTP_Verification The singleton instance.
     */
    public static function get_instance()
    {
        if (! self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}
IMQ_OTP_Verification::get_instance();
