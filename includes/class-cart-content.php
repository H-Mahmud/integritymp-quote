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

    ?>
        <div class="imq-cart-header">
            <div class="links">
                <a class="continue-shopping" href="<?php echo get_permalink(wc_get_page_id('shop')); ?>"><span class="imq-arrow-left"></span> Continue shipping</a>
                <a href="" class="empty-cart"></a>
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
