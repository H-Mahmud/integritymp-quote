<?php
defined('ABSPATH') || exit;
/**
 * Customer Account
 *
 */
class Integrity_Mp_Quote_Customer_Account
{
    /**
     * The single instance of the class.
     * 
     * @var Integrity_Mp_Quote_Customer_Account
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
        add_action('woocommerce_register_form_start', array($this, 'woocommerce_custom_registration_fields'), 10, 0);
        add_filter('woocommerce_registration_errors', array($this, 'validate_woocommerce_custom_registration_fields'), 10, 3);
        add_action('woocommerce_created_customer', array($this, 'save_woocommerce_custom_registration_fields'), 10, 1);
        add_filter('woocommerce_new_customer_data', array($this, 'set_username_for_woocommerce_registration'), 10, 1);

        add_action('show_user_profile', array($this, 'show_custom_user_profile_fields'), 10, 1);
        add_action('edit_user_profile',  array($this, 'show_custom_user_profile_fields'), 10, 1);
        add_action('personal_options_update', array($this, 'save_custom_user_profile_fields'), 10, 1);
        add_action('edit_user_profile_update', array($this, 'save_custom_user_profile_fields'), 10, 1);
    }



    /**
     * Adds custom registration fields to the WooCommerce registration form.
     *
     * This method defines an array of custom fields to be added to the registration form,
     * each with specific attributes such as type, requirement status, label, and CSS class.
     * It includes fields for first name, last name, business name, business location, 
     * business address, phone, and username. These fields are rendered on the form using
     * the woocommerce_form_field function.
     */
    public function woocommerce_custom_registration_fields()
    {
        $fields = [
            'first_name' => [
                'type'        => 'text',
                'required'    => true,
                'label'       => __('First Name', 'integritymp-quote'),
                'class'       => ['form-row-first'],
            ],
            'last_name' => [
                'type'        => 'text',
                'required'    => true,
                'label'       => __('Last Name', 'integritymp-quote'),
                'class'       => ['form-row-last'],
            ],
            'business_name' => [
                'type'        => 'text',
                'required'    => true,
                'label'       => __('Business Name', 'integritymp-quote'),
                'class'       => ['form-row-wide'],
            ],
            'business_location' => [
                'type'        => 'text',
                'required'    => true,
                'label'       => __('Business Location', 'integritymp-quote'),
                'class'       => ['form-row-wide'],
            ],
            'business_address' => [
                'type'        => 'text',
                'required'    => true,
                'label'       => __('Business Address', 'integritymp-quote'),
                'class'       => ['form-row-wide'],
            ],
            'phone' => [
                'type'        => 'tel',
                'required'    => true,
                'label'       => __('Phone', 'integritymp-quote'),
                'class'       => ['form-row-wide'],
            ],
            'username' => [
                'type'        => 'text',
                'required'    => true,
                'label'       => __('Username', 'integritymp-quote'),
                'class'       => ['form-row-wide'],
            ],
        ];

        foreach ($fields as $key => $field_args) {
            woocommerce_form_field($key, $field_args, $_POST[$key] ?? '');
        }
    }



    /**
     * Validates custom WooCommerce registration fields.
     *
     * @param WP_Error $validation_errors The validation errors object to add errors to.
     * @param string   $username The submitted username.
     * @param string   $email The submitted email address.
     * @return WP_Error The updated validation errors object.
     *
     * This function checks if the required custom fields (first name, last name, business name, business location,
     * business address, phone, and username) are filled out during registration. It adds validation errors if any
     * of these fields are empty. Additionally, it checks if the username already exists and adds an error if so.
     */
    public function validate_woocommerce_custom_registration_fields($validation_errors, $username, $email)
    {
        if (empty($_POST['first_name']))
            $validation_errors->add('first_name_error', __('First Name is required.', 'integritymp-quote'));

        if (empty($_POST['last_name']))
            $validation_errors->add('last_name_error', __('Last Name is required.', 'integritymp-quote'));

        if (empty($_POST['business_name']))
            $validation_errors->add('business_name_error', __('Business Name is required.', 'integritymp-quote'));

        if (empty($_POST['business_location']))
            $validation_errors->add('business_location_error', __('Business Location is required.', 'integritymp-quote'));

        if (empty($_POST['business_address']))
            $validation_errors->add('business_address_error', __('Business Address is required.', 'integritymp-quote'));

        if (empty($_POST['phone']))
            $validation_errors->add('phone_error', __('Phone number is required.', 'integritymp-quote'));

        if (empty($_POST['username']))
            $validation_errors->add('username_error', __('Username is required.', 'integritymp-quote'));
        elseif (username_exists($_POST['username']))
            $validation_errors->add('username_exists_error', __('Username already exists!', 'woocommerce'));

        return $validation_errors;
    }



    /**
     * Saves custom WooCommerce registration fields to the user meta when the user is created.
     *
     * @param int $customer_id The ID of the user being saved.
     *
     * This function checks if the custom fields (first name, last name, business name, business location, business address, and phone)
     * are set during registration, and if so, saves them to the user meta. It uses the update_user_meta function to
     * store the values in the meta table.
     */
    public function save_woocommerce_custom_registration_fields($customer_id)
    {
        if (isset($_POST['first_name']))
            update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['first_name']));

        if (isset($_POST['last_name']))
            update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['last_name']));

        if (isset($_POST['business_name']))
            update_user_meta($customer_id, 'business_name', sanitize_text_field($_POST['business_name']));

        if (isset($_POST['business_location']))
            update_user_meta($customer_id, 'business_location', sanitize_text_field($_POST['business_location']));

        if (isset($_POST['business_address']))
            update_user_meta($customer_id, 'business_address', sanitize_text_field($_POST['business_address']));

        if (isset($_POST['phone']))
            update_user_meta($customer_id, 'phone', sanitize_text_field($_POST['phone']));
    }



    /**
     * Sets the username for the new customer in the woocommerce_new_customer_data filter.
     *
     * If the 'username' field is set in the $_POST global, it sets the 'user_login' key in the $data array to the
     * sanitized value of the 'username' field. Otherwise, the 'user_login' key is left unchanged.
     *
     * @param array $data The array of data being passed to the woocommerce_new_customer_data filter.
     * @return array The modified array of data.
     */
    function set_username_for_woocommerce_registration($data)
    {
        if (isset($_POST['username']) && !empty($_POST['username'])) {
            $data['user_login'] = sanitize_text_field($_POST['username']);
        }
        return $data;
    }



    /**
     * Displays custom user profile fields in the WordPress user profile page.
     *
     * This function outputs HTML form fields for editing user meta data, including
     * business name, business location, business address, and phone number. The fields
     * are pre-populated with the current values from the user's metadata.
     *
     * @param WP_User $user The user object for the profile being edited.
     */
    public function show_custom_user_profile_fields($user)
    {
?>
        <h3><?php _e('Business Information', 'integritymp-quote'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="business_name"><?php _e('Business Name', 'integritymp-quote'); ?></label></th>
                <td><input type="text" name="business_name" id="business_name" value="<?php echo esc_attr(get_the_author_meta('business_name', $user->ID)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="business_location"><?php _e('Business Location', 'integritymp-quote'); ?></label></th>
                <td><input type="text" name="business_location" id="business_location" value="<?php echo esc_attr(get_the_author_meta('business_location', $user->ID)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="business_address"><?php _e('Business Address', 'integritymp-quote'); ?></label></th>
                <td><input type="text" name="business_address" id="business_address" value="<?php echo esc_attr(get_the_author_meta('business_address', $user->ID)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="phone"><?php _e('Phone', 'integritymp-quote'); ?></label></th>
                <td><input type="text" name="phone" id="phone" value="<?php echo esc_attr(get_the_author_meta('phone', $user->ID)); ?>" class="regular-text" /></td>
            </tr>
        </table>
<?php
    }



    /**
     * Saves custom user profile fields to the user meta when the user is edited.
     *
     * Checks if the custom fields (business name, business location, business address, and phone)
     * are set during the user edit process, and if so, saves them to the user meta.
     *
     * @param int $user_id The ID of the user being edited.
     */
    public function save_custom_user_profile_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        if (!empty($_POST['business_name'])) {
            update_user_meta($user_id, 'business_name', sanitize_text_field($_POST['business_name']));
        }
        if (!empty($_POST['business_location'])) {
            update_user_meta($user_id, 'business_location', sanitize_text_field($_POST['business_location']));
        }
        if (!empty($_POST['business_address'])) {
            update_user_meta($user_id, 'business_address', sanitize_text_field($_POST['business_address']));
        }
        if (!empty($_POST['phone'])) {
            update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
        }
    }



    /**
     * Gets the singleton instance of the class.
     *
     * @return Integrity_Mp_Quote_Customer_Account The singleton instance.
     */
    public static function get_instance()
    {
        if (! self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

Integrity_Mp_Quote_Customer_Account::get_instance();
