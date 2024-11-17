<?php
defined('ABSPATH') || exit;
class IMQ_Product_Price_Filter
{

    /**
     * The single instance of the class.
     * 
     * @var IMQ_Product_Price_Filter
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


        add_filter('woocommerce_get_price_html', array($this, 'adjust_quote_price_display'), 10, 2);
        add_filter('woocommerce_cart_item_price', array($this, 'adjust_cart_price_display'), 10, 3);
        add_filter('woocommerce_cart_item_subtotal', array($this, 'adjust_cart_subtotal_display'), 10, 3);

        add_filter('woocommerce_cart_total', 'adjust_cart_subtotal');
        add_filter('woocommerce_cart_subtotal', 'adjust_cart_total', 10, 3);
    }

    /**
     * Adjust the price display to use the quote price if available.
     *
     * @param string $price_html The HTML for the price.
     * @param WC_Product $product The product object.
     * @return string The adjusted price HTML.
     */
    public function adjust_quote_price_display($price_html, $product)
    {

        if (get_quote_price($product->get_id())) {
            return wc_price(get_quote_price($product->get_id()));
        }
        return $price_html;
    }


    /**
     * Adjusts the cart item price display to use the quote price if available.
     *
     * @param string $price_html The HTML for the cart item price.
     * @param array $cart_item The cart item data.
     * @param string $cart_item_key The cart item key.
     * @return string The adjusted price HTML.
     */
    public function adjust_cart_price_display($price_html, $cart_item, $cart_item_key)
    {

        if (get_quote_price($cart_item['data']->get_id())) {
            return wc_price(get_quote_price($cart_item['data']->get_id()));
        }
        return wc_price($price_html);
    }


    /**
     * Adjusts the cart item subtotal display to use the quote price if available.
     *
     * @param string $subtotal The HTML for the cart item subtotal.
     * @param array $cart_item The cart item data.
     * @param string $cart_item_key The cart item key.
     * @return string The adjusted subtotal HTML.
     */
    public function adjust_cart_subtotal_display($subtotal, $cart_item, $cart_item_key)
    {
        $quantity = $cart_item['quantity'];

        if (get_quote_price($cart_item['data']->get_id())) {
            $price = get_quote_price($cart_item['data']->get_id());
            return wc_price($price * $quantity);
        }
        return $subtotal;
    }



    /**
     * Adjusts the cart subtotal display to use the quote price if available.
     *
     * @param string $cart_subtotal The HTML for the cart subtotal.
     * @param bool $compound Whether or not to include compound taxes.
     * @param WC_Cart $cart The cart object.
     * @return string The adjusted subtotal HTML.
     */
    public function adjust_cart_subtotal($cart_subtotal, $compound, $cart)
    {
        $total = 0;

        foreach ($cart->get_cart() as $cart_item) {
            $quantity = $cart_item['quantity'];
            if (get_quote_price($cart_item['data']->get_id())) {
                $price = get_quote_price($cart_item['data']->get_id());
                $total += $price * $quantity;
            } else {
                $total += $cart_item['line_subtotal'];
            }
        }

        return wc_price($total);
    }


    /**
     * Adjusts the cart total display to use the quote price if available.
     *
     * @param string $cart_total The HTML for the cart total.
     * @return string The adjusted total HTML.
     */
    public function adjust_cart_total($cart_total)
    {
        $total = 0;
        $cart = WC()->cart;

        foreach ($cart->get_cart() as $cart_item) {
            $quantity = $cart_item['quantity'];
            if (get_quote_price($cart_item['data']->get_id())) {
                $price = get_quote_price($cart_item['data']->get_id());
                $total += $price * $quantity;
            } else {
                $total += $cart_item['line_subtotal'];
            }
        }

        return wc_price($total);
    }



    /**
     * Gets the singleton instance of the class.
     *
     * @return IMQ_Product_Price_Filter The singleton instance.
     */
    public static function get_instance()
    {
        if (! self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

IMQ_Product_Price_Filter::get_instance();
