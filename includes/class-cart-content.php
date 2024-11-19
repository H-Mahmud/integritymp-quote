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

        add_action('woocommerce_after_cart_totals', array($this, 'cart_totals_need_help'), 20);
        add_filter('gettext', array($this, 'change_cart_totals_heading'), 20, 3);



        add_action('init', array($this, 'clear_cart_session'), 20);
        add_action('init', array($this, 'save_cart_session_favorites'), 20);
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

        $save_cart_queries = array(
            'save_cart' => 'true',
            'save_cart_nonce' => wp_create_nonce('save_cart')
        );

        $save_cart_url = add_query_arg($save_cart_queries, $cart_url);
    ?>
        <div class="imq-cart-header">
            <div class="links">
                <a class="continue-shopping" href="<?php echo get_permalink(wc_get_page_id('shop')); ?>"><span class="imq-arrow-left"></span> Continue shipping</a>
                <div class="link-right">
                    <a href="<?php echo $url; ?>" class="empty-cart">
                        <svg class="" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                        </svg>
                        Clear Cart
                    </a>
                    <?php if (defined('YITH_WCWL_VERSION')): ?>
                        <a href="<?php echo $save_cart_url; ?>" class="save-cart">
                            <svg fill="#4981C6" width="92" height="92" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 4v16c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2V8a.997.997 0 0 0-.293-.707l-5-5A.996.996 0 0 0 14 2H6c-1.103 0-2 .897-2 2zm14 4.414L18.001 20H6V4h7.586L18 8.414z" />
                                <path d="M8 6h2v4H8zm4 0h2v4h-2z" />
                            </svg>
                            Save Cart
                        </a>
                    <?php endif; ?>
                </div>
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
     * Saves the cart session contents to the user's wishlist.
     *
     * This function is hooked to the 'init' action and will save the cart session
     * to the user's wishlist if the correct parameters are passed in the GET
     * request. Specifically, it requires the 'save_cart' and 'save_cart_nonce'
     * parameters to be present and for the nonce to be valid.
     *
     * @since 1.0
     */
    public function save_cart_session_favorites()
    {
        if (is_admin() || !isset($_GET['save_cart']) || !isset($_GET['save_cart_nonce']) || !wp_verify_nonce($_GET['save_cart_nonce'], 'save_cart') || !function_exists('yith_wcwl_count_add_to_wishlist')) return;

        $cart_items = WC()->cart->get_cart();

        // Iterate through each cart item
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $product_variation_id = $cart_item['variation_id'];
            yith_wcwl_count_add_to_wishlist($product_id);
        }

        wc_add_notice('Your cart has been saved to your favorites!', 'success');

        wp_redirect(get_permalink(wc_get_page_id('cart')));
        exit;
    }

    /**
     * Prints a message to the user with a phone number to call for help.
     *
     * This function is hooked to the 'woocommerce_cart_totals_after_order_total' action.
     *
     * @since 1.0
     */
    public function cart_totals_need_help()
    {
        echo <<<HTML
            <h4 class="need-help">Need Help? <br>
                Call jeff at 727-244-2013
            </h4>
            HTML;
    }



    /**
     * Modifies the cart totals heading text to replace "Cart" with "Quote" and other
     * related phrases.
     *
     * @param string $translated_text The translated text to modify.
     * @param string $text The original untranslated text.
     * @param string $domain The text domain.
     *
     * @return string The modified translated text.
     *
     * @since 1.0
     */
    public function change_cart_totals_heading($translated_text, $text, $domain)
    {
        $replacements = [
            'Cart'          => 'Quote',
            'Add to cart'   => 'Add to Quote',
            'View cart'     => 'View Quote',
            'Proceed to checkout' => 'Submit Quote',
            'Quote totals'   => 'Quote Summary',
        ];

        if ($domain === 'woocommerce') {
            foreach ($replacements as $original => $replacement) {
                $translated_text = str_ireplace($original, $replacement, $translated_text);
            }
        }

        return $translated_text;
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
