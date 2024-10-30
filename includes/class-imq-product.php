<?php
defined('ABSPATH') || exit;
/**
 * Integrity_Mp_Quote_Product class.
 * 
 */
class Integrity_Mp_Quote_Product
{
    /**
     * The single instance of the class.
     * 
     * @var Integrity_Mp_Quote_Product
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
        add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'single_product_add_to_quote_text'), 10);
        add_filter('woocommerce_product_add_to_cart_text', array($this, 'archive_product_add_to_quote_text'), 10, 1);
        add_action('init', function () {
            remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
        });
        add_action('woocommerce_proceed_to_checkout', array($this, 'cart_page_complete_quote_button'), 20);
    }


    /**
     * Filters the 'Add to cart' button text for single product view to 'Add to Quote'.
     *
     * @return string The 'Add to cart' button text for single product view.
     * @since 1.0
     */
    public function single_product_add_to_quote_text()
    {
        return __('Add to Quote', 'integritymp-quote');
    }



    /**
     * Filters the 'Add to cart' button text for product archives to 'Add to Quote'.
     *
     * @param string $text The 'Add to cart' button text for product archives.
     * @return string The 'Add to cart' button text for product archives.
     * @since 1.0
     */
    public function archive_product_add_to_quote_text($text)
    {
        return __('Add to Quote', 'integritymp-quote');
    }


    /**
     * Outputs a 'Complete Quote' button on the cart page that links to the Quote Request page.
     *
     */
    public function cart_page_complete_quote_button()
    {
        $quote_request = home_url('/complete-quote');
?>
        <a href="<?php echo esc_url($quote_request); ?>" class="checkout-button button alt wc-forward<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>">
            <?php esc_html_e('Complete Quote', 'integritymp-quote'); ?>
        </a>
<?php
    }


    /**
     * Gets the singleton instance of the class.
     *
     * @return Integrity_Mp_Quote_Product The singleton instance.
     */
    public static function get_instance()
    {
        if (! self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

Integrity_Mp_Quote_Product::get_instance();
