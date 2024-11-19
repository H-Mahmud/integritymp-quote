<?php

use WP_Forge\Helpers\Arr;

defined('ABSPATH') || exit;

class IMQ_Cart_Content
{
    /**
     * The single instance of the class.
     * 
     * @var IMQ_Cart_Content
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
        add_action('woocommerce_before_cart', array($this, 'cart_page_steps'));
        add_action('woocommerce_before_cart_table', array($this, 'imq_cart_page_steps'));
        add_action('wp_enqueue_scripts', array($this, 'imq_enqueue_scripts'));

        add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'add_to_cart_button_text'));
        add_filter('woocommerce_product_add_to_cart_text', array($this, 'archive_add_to_quote_button_text'));

        add_action('init', array($this, 'clear_cart_session'), 20);
    }

    public function cart_page_steps()
    {
?>

        <div class="imq-steps-wrapper">
            <div class="line"></div>
            <div class="steps">
                <div class="steps__step step-1">
                    <div class="steps__step-number"></div>
                    <div class="steps__step-name step-2">Build Your Quote</div>
                </div>
                <div class="steps__step step-2">
                    <div class="steps__step-number"></div>
                    <div class="steps__step-name">Print to PDF <br> or ee can Email it to You</div>
                </div>
                <div class="steps__step step-3">
                    <div class="steps__step-number"></div>
                    <div class="steps__step-name">Send us a PO <br> order@integritymp.com</div>
                </div>
            </div>
        </div>

    <?php
    }


    /**
     * Adds the top header to the cart page, containing a button to continue shopping
     * and a button to empty the cart.
     *
     * @since 1.0
     */
    public function imq_cart_page_steps()
    {

        $cart_url = wc_get_page_permalink('cart');
        $queries = array(
            'clear_cart' => 'true',
            'clear_cart_nonce' => wp_create_nonce('clear_cart')
        );
        $url = add_query_arg($queries, $cart_url);
    ?>
        <div class="imq-cart-header">
            <div class="links">
                <a class="continue-shopping" href="<?php echo get_permalink(wc_get_page_id('shop')); ?>"><span class="imq-arrow-left"></span> Continue shipping</a>
                <a href="<?php echo $url; ?>" class="empty-cart">
                    <svg class="" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                    </svg>
                    Clear Cart
                </a>
            </div>
        </div>

<?php
    }

    /**
     * Enqueues the styles required for the IMQ cart page.
     *
     * This function is hooked to the 'wp_enqueue_scripts' action
     * to include the main stylesheet for the IMQ plugin.
     *
     * @since 1.0
     */
    public function imq_enqueue_scripts()
    {
        wp_enqueue_style('imq-style', IMQ_PLUGIN_DIR_URL . '/assets/imq-style.css');
    }

    /**
     * Filters the 'Add to cart' button text for single product view to 'Add to Quote'.
     *
     * @return string The 'Add to cart' button text for single product view.
     * @since 1.0
     */
    public function add_to_cart_button_text()
    {
        return __('Add to Quote', 'woocommerce');
    }

    // Change Button Text in Archive Pages


    /**
     * Filters the 'Add to cart' button text in archive pages to 'Add to Quote'.
     *
     * @return string The 'Add to cart' button text in archive pages.
     * @since 1.0
     */
    public function archive_add_to_quote_button_text()
    {
        return __('Add to Quote', 'woocommerce');
    }

    /**
     * Clears the cart session and redirects to the cart page.
     *
     * This function is hooked to the 'init' action and will clear the cart session
     * and redirect to the cart page if the correct parameters are passed in the
     * GET request. Specifically, it requires the 'clear_cart' and 'clear_cart_nonce'
     * parameters to be present and for the nonce to be valid.
     *
     * @since 1.0
     */
    public function clear_cart_session()
    {

        if (is_admin() || !isset($_GET['clear_cart']) || !isset($_GET['clear_cart_nonce']) || !wp_verify_nonce($_GET['clear_cart_nonce'], 'clear_cart')) return;

        WC()->cart->empty_cart();
        wp_redirect(get_permalink(wc_get_page_id('cart')));
        exit;
    }

    /**
     * Gets the singleton instance of the class.
     *
     * @return IMQ_Cart_Content The singleton instance.
     */
    public static function get_instance()
    {
        if (! self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

IMQ_Cart_Content::get_instance();
