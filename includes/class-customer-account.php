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

        add_filter('manage_users_columns', array($this, 'add_users_verification_column'), 10, 1);
        add_filter('manage_users_sortable_columns', array($this, 'user_verification_column_sortable'), 10, 1);
        // add_action('restrict_manage_users', array($this, 'user_filter_by_verification'), 10, 1);
        add_filter('pre_get_users', array($this, 'filter_users_by_verification'), 10, 1);
        add_action('manage_users_custom_column', array($this,  'show_verification_status'), 10, 3);
        add_action('admin_head', array($this, 'custom_user_status_badge_styles'));
        add_action('admin_menu', array($this, 'add_unverified_count_badge'));

        add_action('user_register', array($this, 'add_default_verification_status'), 10, 1);
        add_action('show_user_profile', array($this, 'add_verification_status_field'), 10, 1);
        add_action('edit_user_profile', array($this, 'add_verification_status_field'), 10, 1);
        add_action('personal_options_update', array($this, 'save_verification_status'), 10, 1);
        add_action('edit_user_profile_update', array($this, 'save_verification_status'), 10, 1);

        add_action('template_redirect',  array($this, 'apply_woocommerce_restrictions'));
        add_action('woocommerce_product_query', array($this, 'hide_woocommerce_categories'), 10, 1);
        add_action('wp_head', array($this, 'show_unverified_customer_message'));
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
        if (isset($_POST['first_name'])) {
            update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['first_name']));
            update_user_meta($customer_id, 'shipping_first_name', sanitize_text_field($_POST['first_name']));
        }

        if (isset($_POST['last_name'])) {
            update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['last_name']));
            update_user_meta($customer_id, 'shipping_last_name', sanitize_text_field($_POST['last_name']));
        }

        if (isset($_POST['business_name'])) {
            update_user_meta($customer_id, 'business_name', sanitize_text_field($_POST['business_name']));
            update_user_meta($customer_id, 'shipping_company', sanitize_text_field($_POST['business_name']));
        }

        if (isset($_POST['business_location']))
            update_user_meta($customer_id, 'business_location', sanitize_text_field($_POST['business_location']));

        if (isset($_POST['business_address'])) {
            update_user_meta($customer_id, 'business_address', sanitize_text_field($_POST['business_address']));
            update_user_meta($customer_id, 'shipping_address_1', sanitize_text_field($_POST['business_address']));
        }

        if (isset($_POST['phone'])) {
            update_user_meta($customer_id, 'phone', sanitize_text_field($_POST['phone']));
            update_user_meta($customer_id, 'shipping_phone', sanitize_text_field($_POST['phone']));
        }
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
     * Adds a new column to the Users table to display verification status.
     *
     * @param array $columns The existing columns in the Users table.
     *
     * @return array The modified columns array with the new column added.
     */
    public function add_users_verification_column($columns)
    {
        $columns['verification_status'] = 'Verification Status';
        return $columns;
    }



    /**
     * Make verification status column sortable.
     *
     * @param array $columns
     *
     * @return array
     */
    public function user_verification_column_sortable($columns)
    {
        $columns['verification_status'] = 'verification_status';
        return $columns;
    }



    /**
     * Adds a dropdown filter for verification status on the user management page.
     *
     * This function outputs a select dropdown with options for filtering users by their
     * verification status ('unverified', 'verified', 'rejected'). It checks if a filter
     * is applied via a GET request and sets the selected option accordingly.
     *
     * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
     */
    public function user_filter_by_verification($which)
    {
        $status = array('unverified', 'verified', 'rejected');
        if (isset($_GET['verification_status']) && in_array($_GET['verification_status'], $status)) {
            $selected = $_GET['verification_status'];
        } else {
            $selected = 'all';
        }

    ?>
        <select name="verification_status">
            <option value="" <?php selected('all', $selected); ?>><?php _e('All Verification Statuses', 'integritymp-quote'); ?></option>
            <option value="unverified" <?php selected('unverified', $selected); ?>>Unverified</option>
            <option value="verified" <?php selected('verified', $selected); ?>>Verified</option>
            <option value="rejected" <?php selected('rejected', $selected); ?>>Rejected</option>
        </select>
    <?php
    }



    /**
     * Filters users by their verification status in the admin user management page.
     *
     * This function modifies the user query to filter users based on the 'verification_status'
     * passed via a GET request. It checks if the current page is 'users.php' and a valid
     * 'verification_status' parameter is set in the URL, then adds the corresponding
     * meta query to the user query.
     *
     * @param WP_User_Query $query The WP_User_Query object to modify.
     */
    public function filter_users_by_verification($query)
    {
        global $pagenow;

        if (is_admin() && 'users.php' == $pagenow && isset($_GET['verification_status']) && $_GET['verification_status'] != '') {
            $query->query_vars['meta_key'] = 'verification_status';
            $query->query_vars['meta_value'] = $_GET['verification_status'];
        }
    }



    /**
     * Displays a badge with the user's verification status in the admin user table.
     *
     * This function is hooked to the 'manage_users_custom_column' action to render
     * a status badge for the 'verification_status' column in the users list table.
     * It retrieves the user's verification status from user meta and returns an
     * HTML span element with a class and text representing the status.
     *
     * @param string $value       The column's current value.
     * @param string $column_name The name of the column being processed.
     * @param int    $user_id     The ID of the user being displayed.
     *
     * @return string The HTML for the status badge or the original column value.
     */
    function show_verification_status($value, $column_name, $user_id)
    {
        if ('verification_status' == $column_name) {
            $status = get_user_meta($user_id, 'verification_status', true);

            // Define badge classes for each status
            $badge_class = 'status-badge ';
            switch ($status) {
                case 'verified':
                    $badge_class .= 'status-verified';
                    break;
                case 'unverified':
                    $badge_class .= 'status-unverified';
                    break;
                case 'rejected':
                    $badge_class .= 'status-rejected';
                    break;
                default:
                    $badge_class .= 'status-unverified';
                    break;
            }

            return '<span class="' . esc_attr($badge_class) . '">' . ucfirst($status) . '</span>';
        }
        return $value;
    }



    /**
     * Outputs custom CSS styles for user status badges.
     *
     * This function echoes a style block containing CSS definitions for
     * the 'status-badge' class and specific styles for badges representing
     * user verification statuses ('verified', 'unverified', 'rejected').
     * The styles include display, padding, font size, weight, and background
     * colors for each status type, ensuring the badges are visually distinct
     * in the admin interface.
     */
    function custom_user_status_badge_styles()
    {
        echo '<style>
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            border-radius: 4px;
            text-transform: capitalize;
        }
        .status-verified {
            background-color: #28a745; /* Green */
        }
        .status-unverified {
            background-color: #6c757d; /* Gray */
        }
        .status-rejected {
            background-color: #dc3545; /* Red */
        }
    </style>';
    }



    /**
     * Adds a badge with the count of unverified users to the WordPress admin menu.
     *
     * This function iterates over the global WordPress admin menu and appends
     * a notification badge to the "Users" menu item, displaying the number of
     * users with an 'unverified' status. This provides a quick visual indicator
     * of pending user verifications.
     */
    public function add_unverified_count_badge()
    {
        global $menu;

        $count = count(get_users([
            'meta_key' => 'verification_status',
            'meta_value' => 'unverified'
        ]));
        if (!$count) return;

        foreach ($menu as $key => $value) {
            if ($menu[$key][2] == 'users.php') {
                $menu[$key][0] .= sprintf(' <span class="awaiting-mod">%d</span>', $count);
                break;
            }
        }
    }




    /**
     * Sets the default verification status for a newly registered user.
     *
     * This function is attached to the 'user_register' action hook and sets the
     * default verification status for a user to 'unverified' upon registration.
     *
     * @param int $user_id The ID of the newly registered user.
     */
    function add_default_verification_status($user_id)
    {
        add_user_meta($user_id, 'verification_status', 'unverified', true);
    }



    /**
     * Adds a verification status field to the user profile page.
     *
     * This function outputs a dropdown field on the WordPress user profile
     * page allowing administrators to select a user's verification status.
     * The options include 'Unverified', 'Verified', and 'Rejected'. It checks
     * if the current user has the capability to manage options before proceeding.
     *
     * @param WP_User $user The user object of the profile being edited.
     */
    public function add_verification_status_field($user)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $status = get_user_meta($user->ID, 'verification_status', true);
        $price_level = get_user_meta($user->ID, 'price_level', true);
        $is_tax_exempt = get_user_meta($user->ID, 'tax_exempt', true);
    ?>
        <h3><?php _e('Verification Status', 'integritymp-quote'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="verification_status"><?php _e('Status', 'integritymp-quote'); ?></label></th>
                <td>
                    <select name="verification_status" id="verification_status">
                        <option value="unverified" <?php selected($status, 'unverified'); ?>>Unverified</option>
                        <option value="verified" <?php selected($status, 'verified'); ?>>Verified</option>
                        <option value="rejected" <?php selected($status, 'rejected'); ?>>Rejected</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="price_level"><?php _e('Price Level', 'integritymp-quote'); ?></label></th>
                <td>
                    <select name="price_level" id="price_level">
                        <option value="" <?php selected($price_level, ''); ?>>Undefined</option>
                        <option value="_price_level_1" <?php selected($price_level, '_price_level_1'); ?>>Price Level 1</option>
                        <option value="_price_level_2" <?php selected($price_level, '_price_level_2'); ?>>Price Level 2</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="tax_exempt"><?php esc_html_e('Tax Exempt', 'integritymp-quote'); ?></label></th>
                <td>
                    <input type="checkbox" name="tax_exempt" id="tax_exempt" value="1" <?php checked($is_tax_exempt, '1'); ?>>
                    <span class="description"><?php esc_html_e('Check if this customer is tax-exempt.', 'integritymp-quote'); ?></span>
                </td>
            </tr>
        </table>
<?php
    }



    /**
     * Saves the verification status for a user.
     *
     * This function updates the user's verification status in the user meta data,
     * allowing administrators to set the status to 'verified', 'unverified', or 'rejected'.
     *
     * @param int $user_id The ID of the user whose verification status is being updated.
     */
    public function save_verification_status($user_id)
    {
        if (!current_user_can('manage_options')) return;

        if (isset($_POST['verification_status'])) {
            update_user_meta($user_id, 'verification_status', $_POST['verification_status']);
        }

        if (isset($_POST['price_level'])) {
            update_user_meta($user_id, 'price_level', $_POST['price_level']);
        }

        if (isset($_POST['tax_exempt'])) {
            update_user_meta($user_id, 'tax_exempt', 1);
        } else {
            delete_user_meta($user_id, 'tax_exempt');
        }
    }



    /**
     * Restricts access to the WooCommerce store to users whose verification status is set to 'verified'.
     *
     * If the user is not logged in, redirects to the login page. If the user is logged in but not verified, redirects to a custom 'not-verified' page.
     *
     * This function is an action hook for the 'init' action.
     */
    public function restrict_woocommerce_to_verified_users()
    {
        if (!is_user_logged_in()) {
            wc_add_notice('Please create an account to access the store. If you already have an account, please login.', 'error');
            wp_redirect(get_permalink(wc_get_page_id('myaccount')));
            exit;
        }

        $user_id = get_current_user_id();
        $verification_status = get_user_meta($user_id, 'verification_status', true);

        if ($verification_status !== 'verified') {
            wc_add_notice('Your account is not verified yet or you do not have permission to access it.', 'error');

            wp_redirect(get_permalink(wc_get_page_id('myaccount')));
            exit;
        }
    }

    /**
     * Applies restrictions on WooCommerce pages to ensure only verified users have access.
     *
     * This function checks if the current page is a WooCommerce-related page such as shop, product category, cart, checkout,
     * or a custom 'complete-quote' page. If so, it calls the restriction function to verify user access.
     *
     * The function is hooked to the 'template_redirect' action to execute during the template redirect phase.
     */
    public function apply_woocommerce_restrictions()
    {
        if (is_shop() || is_product_category() || is_product() || is_cart() || is_checkout() || is_page('product-categories')) {
            $this->restrict_woocommerce_to_verified_users();
        }
    }



    /**
     * Hides WooCommerce categories from non-verified users.
     *
     * This function hooks into the `woocommerce_product_query` action to modify the query for WooCommerce categories.
     * It checks if the current user is not an administrator and if the current page is a WooCommerce-related page such as
     * shop, product category, or product. If so, it verifies the user's verification status and if not 'verified', sets the
     * query to return no results.
     *
     * @param WP_Query $query The query object.
     */
    public function hide_woocommerce_categories($query)
    {
        if (!is_admin() && (is_shop() || is_product_category() || is_product()) && !current_user_can('manage_options')) {
            $user_id = get_current_user_id();
            $verification_status = get_user_meta($user_id, 'verification_status', true);

            if ($verification_status !== 'verified') {
                $query->set('post__in', array(0));
            }
        }
    }

    /**
     * Shows a message to unverified customers on the front-end.
     *
     * If the user is logged in and their verification status is not 'verified', this function
     * displays a message at the top of the page with a link to the contact page for assistance.
     *
     * The message is styled with a custom CSS block that is added to the page.
     */
    public function show_unverified_customer_message()
    {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $verified_status = get_user_meta($user_id, 'verification_status', true);
            if ($verified_status !== 'verified') {
                echo '
            <div class="unverified-customer-message">
                <p>Your account is not verified yet. Please <a href="/contact">contact support</a> for assistance.</p>
            </div>
            ';

                // Add custom CSS for the message styling
                echo '
            <style>
                .unverified-customer-message {
                    background-color: #f8d7da;
                    color: #721c24;
                    text-align: center;
                    font-size: 16px;
                    border: 1px solid #f5c6cb;
                    border-radius: 5px;
                    position: fixed;
                    top: 0;
                    width: 100%;
                    height: 40px;
                    z-index: 9999;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .unverified-customer-message p {
                    margin: 0;
                }
                .unverified-customer-message a {
                    color: #721c24;
                    font-weight: bold;
                    text-decoration: underline;
                }
                #page {
                  margin-top: 40px;
                }
            </style>
            ';
            }
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
