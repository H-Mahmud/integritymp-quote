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
