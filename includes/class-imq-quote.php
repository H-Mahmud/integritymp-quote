<?php
defined('ABSPATH') || exit;

class Integrity_Mp_Quote
{
    /**
     * The single instance of the class.
     * 
     * @var Integrity_Mp_Quote
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
        add_action('init', array($this, 'register_quote_post_type'), 20);
        add_filter('post_row_actions', array($this, 'remove_quote_post_row_actions'), 10, 2);
        add_filter('manage_shop_quote_posts_columns', array($this, 'wc_quote_custom_columns'));
        add_action('manage_shop_quote_posts_custom_column', array($this,  'wc_quote_custom_column_data'), 20, 2);
        add_action('admin_menu', array($this, 'quote_submenu_page'));

        add_action('init', array(self::class, 'add_quotes_endpoint'));
        add_action('init', array(self::class, 'add_view_quote_endpoint'));
        add_filter('woocommerce_account_menu_items', array($this, 'add_quotes_link_my_account'));
        add_action('woocommerce_account_quotes_endpoint', array($this, 'my_account_quotes_content'));
        // add_action('woocommerce_account_view-quote_endpoint', array($this, 'my_account_view_quote_content'));
        // add_action('woocommerce_account_view-quote_endpoint', array($this, 'my_account_view_quote_content'));
        add_action('wp', array($this, 'get_quote_request'));

        // add_filter('page_template', array($this, 'complete_quote_page_template'));
        add_action('before_delete_post', array($this, 'delete_quote_product_lookup'));
        add_filter('template_include', array($this, 'single_quote_invoice_view'));
    }



    /**
     * Registers the 'shop_quote' post type. This is a custom post type that stores
     * quotes created by customers. It is not public and is only accessible via the
     * WooCommerce admin UI.
     *
     * @since 1.0
     */
    public function register_quote_post_type()
    {
        register_post_type('shop_quote', array(
            'labels' => array(
                'name'               => __('Quotes', 'integritymp-quote'),
                'singular_name'      => __('Quote', 'integritymp-quote'),
                'menu_name'          => __('Quotes', 'integritymp-quote'),
                'add_new'            => __('Add Quote', 'integritymp-quote'),
                'add_new_item'       => __('Add New Quote', 'integritymp-quote'),
                'edit_item'          => __('Edit Quote', 'integritymp-quote'),
                'new_item'           => __('New Quote', 'integritymp-quote'),
                'view_item'          => __('View Quote', 'integritymp-quote'),
                'search_items'       => __('Search Quotes', 'integritymp-quote'),
                'not_found'          => __('No quotes found', 'integritymp-quote'),
                'not_found_in_trash' => __('No quotes found in Trash', 'integritymp-quote')
            ),
            'public'              => false,
            'show_ui'             => true,
            'exclude_from_search' => true,
            'capability_type'     => 'shop_order',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'supports'            => array(''),
            'has_archive'         => false,
            'show_in_menu'        => 'woocommerce',
            'show_in_admin_bar'   => true,
            'rewrite'             => false,
        ));
    }



    /**
     * Adds a "Quotes" link to the My Account menu.
     *
     * @param array $items The menu items to be modified.
     * @return array The modified menu items.
     */
    public function add_quotes_link_my_account($items)
    {
        $new_items = array();
        foreach ($items as $key => $value) {
            $new_items[$key] = $value;
            if ('orders' === $key) {
                $new_items['quotes'] = __('Quotes', 'integritymp-quote');
            }
        }
        return $new_items;
    }



    /**
     * Outputs the content for the "Quotes" page in the My Account section.
     *
     * @action woocommerce_account_quotes_endpoint
     */
    public function my_account_quotes_content()
    {
        include_once IMQ_PLUGIN_DIR_PATH . 'view/my-account-quotes.php';
    }



    /**
     * Outputs the content for the "View Quote" page in the My Account section.
     *
     * This function is hooked to the 'woocommerce_account_view-quote_endpoint' action hook.
     *
     * @since 1.0
     */

    public function my_account_view_quote_content()
    {
        include_once IMQ_PLUGIN_DIR_PATH . 'view/my-account-view-quote.php';
    }




    /**
     * Registers the 'quotes' endpoint, which is used for displaying all quotes in the My Account section.
     *
     * This function is hooked to the 'init' action hook.
     *
     * @since 1.0
     */
    public static function add_quotes_endpoint()
    {
        add_rewrite_endpoint('quotes', EP_ROOT | EP_PAGES);
    }



    /**
     * Registers the 'view-quote' endpoint, which is used for displaying a single quote in the My Account section.
     *
     * This function is hooked to the 'init' action hook.
     *
     * @since 1.0
     */
    public static function add_view_quote_endpoint()
    {
        add_rewrite_endpoint('view-quote', EP_ROOT | EP_PAGES);
    }



    /**
     * Replaces the template for the "Complete Quote" page with our custom template.
     *
     * @param string $page_template The path to the template file.
     *
     * @return string The path to the template file.
     */
    public function complete_quote_page_template($page_template)
    {
        if (is_page('complete-quote')) {
            $page_template = IMQ_PLUGIN_DIR_PATH . 'view/quote-invoice.php';
        }
        return $page_template;
    }

    /**
     * Adds custom columns to the Quotes admin page
     *
     * @param array $columns The current columns array
     * @return array The updated columns array
     */
    public function wc_quote_custom_columns($columns)
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'quote' => __('Quote', 'integritymp-quote'),
            'price_level' => __('Price Level', 'integritymp-quote'),
            'quote_total' => __('Quote Total', 'integritymp-quote'),
            'date' => __('Date', 'integritymp-quote')
        );
        return $columns;
    }



    /**
     * Handles custom column data for Quote post type
     *
     * @param string $column  The name of the column to display
     * @param int    $post_id The current post ID
     */

    public function wc_quote_custom_column_data($column, $post_id)
    {
        $quote = new IMQ_Quote($post_id);
        switch ($column) {
            case 'quote':
                echo $this->get_quote_edit_link(get_the_ID());
                break;
            case 'price_level':
                echo $quote->get_price_level_label();
                break;
            case 'quote_total':
                echo $quote->get_total();
                break;

            case 'date':
                echo get_the_date();
                break;
        }
    }



    /**
     * Generates a link to edit a quote in the WordPress admin interface.
     *
     * @param int $quote_id The ID of the quote to generate a link for.
     *
     * @return string The edit link, with the quote ID and business name displayed.
     */
    public function get_quote_edit_link($quote_id)
    {
        $quote = new IMQ_Quote($quote_id);
        $business_name = $quote->get_shipping()->get_company();
        $quote_tile = '#' . $quote_id . " " . $business_name;

        $edit_link = admin_url('admin.php?page=shop-quote&action=edit&id=' . $quote_id);
        return "<strong><a class='row-title' href='$edit_link'>$quote_tile</a></strong>";
    }



    /**
     * Remove unwanted row actions for 'shop_quote' post type.
     *
     * @param array    $actions The list of row actions.
     * @param \WP_Post $post    The post object.
     *
     * @return array
     */
    public function remove_quote_post_row_actions($actions, $post)
    {
        if ($post->post_type === 'shop_quote') {
            unset($actions['edit']);
            unset($actions['view']);
            unset($actions['trash']);
            unset($actions['inline hide-if-no-js']);
        }
        return $actions;
    }



    /**
     * Adds a submenu page for quotes in the WordPress admin dashboard.
     *
     * This function hooks into the 'admin_menu' action to add a submenu page
     * under the main admin page 'admin.php'. The submenu is titled 'Quote' and
     * is accessible to users with 'manage_options' capability. The content
     * of the submenu page is generated by the 'my_custom_submenu_page_content' function.
     *
     * @since 1.0
     */
    public function quote_submenu_page()
    {
        add_submenu_page(
            'admin.php',
            __('Quote', 'integritymp-quote'),
            __('Quote', 'integritymp-quote'),
            'manage_options',
            'shop-quote',
            array($this, 'quote_submenu_page_content')
        );
    }



    /**
     * Generates the content for the submenu page 'shop_quote'.
     *
     * The content is a simple HTML page with a heading and a paragraph.
     *
     * @since 1.0
     */
    public function quote_submenu_page_content()
    {
        include_once IMQ_PLUGIN_DIR_PATH . 'view/admin-quote-edit.php';
    }



    /**
     * Redirects the user to the complete quote page if they are logged in and on the
     * cart page with the 'quote_request' query string parameter set to 'true', and
     * if the cart is not empty.
     *
     * Called by the 'wp' action hook.
     *
     * @since 1.0
     */
    public function get_quote_request()
    {
        if (!is_user_logged_in() || is_admin() || !isset($_GET['quote_request']) || $_GET['quote_request'] != 'true') return;
        if (WC()->cart->get_cart_contents_count() <= 0) {
            wc_add_notice('The cart is empty. Please add some products.', 'error');

            wp_redirect(wc_get_cart_url());
            exit;
        }

        $customer_id = get_current_user_id();

        $first_name = get_user_meta($customer_id, 'shipping_first_name', true);
        $last_name = get_user_meta($customer_id, 'shipping_last_name', true);
        $shipping_address_1 = get_user_meta($customer_id, 'shipping_address_1', true);
        $shipping_address_2 = get_user_meta($customer_id, 'shipping_address_2', true);
        $business_name = get_user_meta($customer_id, 'shipping_company', true);
        $shipping_city = get_user_meta($customer_id, 'shipping_city', true);
        $shipping_postcode = get_user_meta($customer_id, 'shipping_postcode', true);
        $shipping_state = get_user_meta($customer_id, 'shipping_state', true);
        $shipping_country = get_user_meta($customer_id, 'shipping_country', true);
        $shipping_phone = get_user_meta($customer_id, 'shipping_phone', true);

        $cart_items = WC()->cart->get_cart();
        $quote = new IMQ_Quote();

        $shipping_address = [
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'address_1'     => $shipping_address_1,
            'address_2'     => $shipping_address_2,
            'company'       => $business_name,
            'city'          => $shipping_city,
            'postcode'      => $shipping_postcode,
            'state'         => $shipping_state,
            'country'       => $shipping_country,
            'phone'         => $shipping_phone
        ];
        $quote->add_shipping($shipping_address);

        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];

            $quote->add_product($product_id, $quantity);
        }

        $quote_id = $quote->save();
        $vendor_name = get_user_meta($quote->get_customer_id(), 'vendor_name', true);
        $vendor_number = get_user_meta($quote->get_customer_id(), 'vendor_number', true);

        if (!empty($vendor_name) && !empty($vendor_number)) {
            update_post_meta($quote_id, '_vendor_number', $vendor_number);
            update_post_meta($quote_id, '_vendor_name', $vendor_name);
        }
        WC()->cart->empty_cart();

        $current_user = wp_get_current_user();
        $current_user_email = $current_user->user_email;

        $this->send_quote_mail($current_user_email, 'IntegrityMP Quote #' . $quote_id, $quote_id);

        $quote_invoice_view = esc_url(wc_get_account_endpoint_url('view-quote') . $quote_id);
        wp_redirect($quote_invoice_view);
        exit;
    }


    /**
     * Deletes a quote product lookup entry from the database for a given post ID.
     *
     * This function is triggered before a post is deleted. It checks if the post type
     * is 'shop_quote' and removes the corresponding entry from the 'quote_product_lookup'
     * table in the database.
     *
     * @param int $post_id The ID of the post being deleted.
     */
    public function delete_quote_product_lookup($post_id)
    {
        global $wpdb;
        if (get_post_type($post_id) === 'shop_quote') {
            $table_name = $wpdb->prefix . 'quote_product_lookup';
            $wpdb->delete($table_name, array('quote_id' => $post_id), array('%d'));
        }
    }

    /**
     * Overrides the template used for displaying a single quote in the frontend.
     *
     * This function is hooked to the 'template_include' action hook. It checks if the
     * query var 'view-quote' is set and loads a custom template if it is. The custom
     * template is located in the plugin directory and is named 'quote-invoice.php'.
     *
     * @param string $template The template file path.
     * @return string The custom template file path.
     * @since 1.0
     */
    public function single_quote_invoice_view($template)
    {
        global $wp_query;
        if (isset($wp_query->query_vars['view-quote'])) {
            $custom_template = IMQ_PLUGIN_DIR_PATH . 'view/quote-invoice.php';
            if ($custom_template) {
                return $custom_template;
            }
        }
        return $template;
    }

    /**
     * Sends an email containing the quote to the customer.
     *
     * This function requires three parameters: the customer email address to send the quote to,
     * the subject of the email, and the ID of the quote to be sent. It uses the
     * 'wp_mail' function to send the email.
     *
     * @param string $to The customer email address to send the quote to.
     * @param string $subject The subject of the email.
     * @param int $quote_id The ID of the quote to be sent.
     * @since 1.0
     */
    public function send_quote_mail($to, $subject, $quote_id)
    {
        require_once IMQ_PLUGIN_DIR_PATH . 'view/quote-email.php';
        $html_content = imq_quote_email($quote_id);

        $admin_email = get_option('admin_email');
        $site_title = get_bloginfo('name');
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_title . ' <' . $admin_email . '>'
        );
        wp_mail($to, $subject, $html_content, $headers);
    }

    /**
     * Gets the singleton instance of the class.
     *
     * @return Integrity_Mp_Quote The singleton instance.
     */
    public static function get_instance()
    {
        if (! self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}
Integrity_Mp_Quote::get_instance();
