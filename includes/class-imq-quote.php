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
        add_action('woocommerce_account_view-quote_endpoint', array($this, 'my_account_view_quote_content'));

        add_filter('page_template', array($this, 'complete_quote_page_template'));
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
            'status' => __('Status', 'integritymp-quote'),
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
        switch ($column) {
            case 'quote':
                echo $this->get_quote_edit_link(get_the_ID());
                break;
            case 'status':
                echo 'Status';
                break;
            case 'quote_total':
                echo 'Quote total';
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

        $author_id = get_post_field('post_author', $quote_id);
        $business_name = get_the_author_meta('business_name', $author_id);
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
