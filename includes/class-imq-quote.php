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

        add_action('init', array(self::class, 'add_quotes_endpoint'));
        add_action('init', array(self::class, 'add_view_quote_endpoint'));
        add_filter('woocommerce_account_menu_items', array($this, 'add_quotes_link_my_account'));
        add_action('woocommerce_account_quotes_endpoint', array($this, 'my_account_quotes_content'));
        add_action('woocommerce_account_view-quote_endpoint', array($this, 'my_account_view_quote_content'));
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
