<?php
defined('ABSPATH') || exit;

/**
 * Create and Get Quote
 */
class IMQ_Quote extends IMQ_Abstract_Quote
{

    /**
     * Constructor for the IMQ_Quote class.
     *
     * Initializes the quote with a given order ID. If no order ID is provided,
     * a new quote is created.
     *
     * @param int $order Quote ID to load. If 0, a new quote is created.
     */
    public function __construct($order = 0)
    {
        parent::__construct($order);
    }

    /**
     * Get the label of the customer's current price level.
     *
     * Maps the price level meta key to a human-readable label.
     *
     * @return string The label of the customer's current price level.
     */
    public function get_price_level_label()
    {
        $price_level = parent::get_price_level();
        switch ($price_level) {
            case '_price_level_2':
                $label = 'Level 1';
                break;
            case '_price_level_2':
                $label = 'Level 2';
                break;
            default:
                $label = 'Undefined';
        }
        return $label;
    }

    /**
     * Add a product to the quote.
     *
     * @param int $product_id The id of the product to add.
     * @param int $quantity The quantity of the product to add.
     *
     * @return void
     */
    public function add_product($product_id, $quantity)
    {
        parent::push_item($product_id, $quantity);
    }

    /**
     * Sets a shipping address in the quote, for use when constructing a quote manually.
     *
     * @param array $shipping The shipping address to add. The array should include the following:
     *                        - first_name
     *                        - last_name
     *                        - company
     *                        - address_1
     *                        - address_2
     *                        - city
     *                        - state
     *                        - postcode
     *                        - country
     */
    public function add_shipping($shipping)
    {
        parent::put_shipping($shipping);
    }

    /**
     * Save the quote.
     *
     * This method will save the quote into the database.
     *
     * @return void
     */
    public function save()
    {
        return parent::save();
    }
}
