<?php
defined('ABSPATH') || exit;

abstract class IMQ_Abstract_Quote
{
    /**
     * Quote Data array. This is the core Quote data
     *
     * @since 1.0
     * @var array
     */
    private $data = array(
        'id'                           => 0,
        'status'                       => '',
        // Quote props.
        'customer_id'                  => 0,
        'price_level'                  => '',
        'tax_exempt'                   => '',
        'items'                        => array(),
        'total'                        => '',
        'shipping'       => array(
            'first_name' => '',
            'last_name'  => '',
            'company'    => '',
            'address_1'  => '',
            'address_2'  => '',
            'city'       => '',
            'state'      => '',
            'postcode'   => '',
            'country'    => '',
            'phone'      => '',
        ),
    );

    /**
     * Quote Items array.
     */
    private $data_items = array();

    /**
     * Constructor.
     *
     * If an ID is passed, sets the ID of the quote and attempts to read the quote from the database.
     * If no ID is passed, a new quote is created.
     *
     * @since 1.0
     *
     * @param int $order Quote ID to load. If 0, a new quote is created.
     */
    public function __construct($order = 0)
    {

        if (is_numeric($order) && $order > 0) {
            $this->load_data($order);
        }
    }


    /**
     * Get the ID of the quote.
     *
     * @since 1.0
     *
     * @return int The quote ID.
     */
    public function get_id()
    {
        return $this->data['id'];
    }

    /**
     * Set the ID of the quote.
     *
     * @since 1.0
     *
     * @param int $id The ID of the quote.
     */
    private function set_id($id)
    {
        $this->data['id'] = $id;
    }

    /**
     * Get the status of the quote.
     *
     * @since 1.0
     *
     * @return string The status of the quote.
     */
    private function get_status()
    {
        return $this->data['status'];
    }

    /**
     * Set the status of the quote to 'publish'.
     *
     * @since 1.0
     */
    private function set_status()
    {
        $this->data['status'] = 'publish';
    }

    /**
     * Get the customer ID.
     *
     * @since 1.0
     *
     * @return int The customer ID.
     */
    public function get_customer_id()
    {
        return $this->data['customer_id'];
    }

    /**
     * Set the customer ID for the quote based on the post author.
     *
     * This function retrieves the post author ID associated with the given
     * quote ID and assigns it to the customer ID in the quote data.
     *
     * @param int $quote_id The ID of the quote used to fetch the post author.
     */
    private function set_customer_id($quote_id)
    {
        $author_id = get_post_field('post_author', $quote_id);
        $this->data['customer_id'] = $author_id;
    }

    /**
     * Get the price level of the quote.
     *
     * @since 1.0
     *
     * @return string The price level.
     */
    protected function get_price_level()
    {
        return $this->data['price_level'];
    }

    /**
     * Retrieves the price level from the database and assigns it to the quote data.
     *
     * The price level is retrieved from the post meta table with the key
     * '_price_level' and the value is assigned to the price level key in the
     * quote data array.
     */
    private function set_price_level()
    {
        $price_level = get_post_meta($this->get_id(), '_price_level', true);
        $this->data['price_level'] = $price_level;
    }

    /**
     * Get the tax exempt status of the quote.
     *
     * @since 1.0
     *
     * @return bool The tax exempt status.
     */
    public function get_tax_exempt()
    {
        return $this->data['tax_exempt'];
    }

    /**
     * Set the tax exempt status of the quote.
     *
     * Retrieves the tax exempt status from the post meta table with the key
     * '_text_exempt' and assigns it to the tax exempt key in the quote data
     * array.
     *
     */
    private function set_tax_exempt()
    {
        $tax_exempt = get_post_meta($this->get_id(), '_tax_exempt', true);
        $this->data['tax_exempt'] = $tax_exempt;
    }

    /**
     * Retrieves the items associated with the quote.
     *
     * @since 1.0
     *
     * @return IMQ_Quote_Items The items associated with the quote.
     */
    public function get_items()
    {
        return $this->data['items'];
    }

    /**
     * Set the items associated with the quote.
     *
     * @since 1.0
     *
     */
    private function set_items()
    {
        $items = new IMQ_Quote_Items($this->get_id());
        $this->data['items'] = $items;
    }

    /**
     * Retrieves the shipping associated with the quote.
     *
     * @since 1.0
     *
     * @return IMQ_Shipping The shipping associated with the quote.
     */
    public function get_shipping()
    {
        return $this->data['shipping'];
    }

    /**
     * Set the shipping associated with the quote.
     *
     * @since 1.0
     */
    private function set_shipping()
    {
        $shipping = new IMQ_Shipping($this->get_id());
        $this->data['shipping'] = $shipping;
    }

    /**
     * Retrieves the total associated with the quote.
     *
     * @since 1.0
     *
     * @return float The total associated with the quote.
     */
    public function get_total()
    {
        return $this->data['total'];
    }

    /**
     * Retrieves the total associated with the quote from the database and sets it in the object.
     *
     * @since 1.0
     */
    public function set_total()
    {
        $total = (float) get_post_meta($this->get_id(), '_total', true);
        $this->data['total'] = $total;
    }

    /**
     * Sets a product item in the quote, for use when constructing a quote manually.
     *
     * @param int $product_id The product ID
     * @param int $product_qty The quantity of the product to add
     */
    protected function push_item($product_id, $product_qty = 1)
    {
        $this->data_items[] = [
            'product_id'    => $product_id,
            'product_qty'   => $product_qty,
        ];
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
    protected function put_shipping($shipping)
    {
        $shipping = wp_parse_args($shipping, $this->data['shipping']);
        $this->data['shipping'] = $shipping;
    }

    /**
     * Add a new quote and return the ID of the newly created quote.
     *
     * @since 1.0
     *
     * @return int The ID of the newly created quote.
     */
    private function add_quote()
    {
        $quote_id = wp_insert_post(array(
            'post_type'    => 'shop_quote',
            'post_status'  => 'publish',
        ));

        if (is_wp_error($quote_id)) {
            return $quote_id;
        }

        wp_update_post(
            array(
                'ID' => $quote_id,
                'post_title' => 'Quote #' . $quote_id,
            )
        );

        $this->set_id($quote_id);
        $this->set_customer_id($quote_id);
        return $quote_id;
    }

    /**
     * Saves the customer's current price level to the quote.
     *
     * The price level is retrieved from the customer's user meta, and saved to the quote
     * as a post meta value with the key '_price_level'.
     *
     * @param int $customer_id The ID of the customer.
     */
    private function add_price_level()
    {
        $price_level = get_user_meta($this->get_customer_id(), 'price_level', true);
        update_post_meta($this->get_id(), '_price_level', $price_level);
    }

    /**
     * Saves the customer's tax exempt status to the quote.
     *
     * The tax exempt status is retrieved from the customer's user meta, and saved to the quote
     * as a post meta value with the key '_text_exempt'.
     *
     * @return void
     */
    private function add_tax_exempt()
    {
        $text_exempt = get_user_meta($this->get_customer_id(), 'tax_exempt', true);
        update_post_meta($this->get_id(), '_tax_exempt', $text_exempt);
    }

    /**
     * Saves the shipping details to the quote.
     *
     * The shipping details are retrieved from the quote data and saved to the quote
     * as a post meta value with the key '_shipping'.
     *
     * @return void
     */
    private function add_shipping()
    {
        update_post_meta($this->get_id(), '_shipping', maybe_serialize($this->data['shipping']));
    }

    /**
     * Save items to the database
     *
     * @return void
     */
    private function add_items()
    {
        $items = $this->data_items;
        foreach ($items as $item) {
            $this->insert_item($item['product_id'], $item['product_qty']);
        }
    }

    /**
     * Insert a product item into the quote_product_lookup table
     *
     * @param int $product_id The product ID
     * @param int $product_qty The quantity of the product to add
     *
     * @return int|false The ID of the inserted row, or false if the insert failed
     */
    private function insert_item($product_id, $product_qty = 1)
    {
        $product = wc_get_product($product_id);
        if (!$product) return false;

        global $wpdb;
        $table_name = $wpdb->prefix . 'quote_product_lookup';
        $data = [
            'quote_id'      => $this->get_id(),
            'product_id'    => $product_id,
            'variation_id'  => 0,
            'customer_id'   => $this->get_customer_id(),
            'date_created'  => current_time('mysql'),
            'product_qty'   => $product_qty,
            'price'         => $product->get_price(),
            'quote_price'   => $this->get_quote_price($product_id),
            'price_level'   => $this->get_price_level(),
        ];

        $inserted = $wpdb->insert($table_name, $data, [
            '%d', // quote_id
            '%d', // product_id
            '%d', // variation_id
            '%d', // customer_id
            '%s', // date_created
            '%d',  // product_qty
            '%d',  // price
            '%d',  // quote_price
            '%s'  // price_level
        ]);

        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Retrieves the price for the given product ID based on the customer's price level.
     *
     * If the customer has a price level set, this function will first look for a meta value
     * with the key set to the price level. If that value exists, it will be returned.
     *
     * If no price level is set, or if the price level meta value does not exist, this function
     * will fall back to retrieving the product's regular price.
     *
     * @param int $product_id The ID of the product.
     *
     * @return float The price of the product based on the customer's price level.
     */
    public function get_quote_price($product_id)
    {
        $price_level = $this->get_price_level();
        if ($price_level && $price = get_post_meta($product_id, $price_level, true)) {
            return $price;
        }
        $product = wc_get_product($product_id);
        return $product->get_price();
    }

    /**
     * Calculates the total price of all items in the quote and updates the quote's total.
     *
     * This function iterates over each item in the quote, accumulates the total
     * price of these items, and then saves the total as a post meta value with
     * the key '_total'.
     *
     * @return void
     */
    private function add_total()
    {
        $total = 0;
        $items = new IMQ_Quote_Items($this->get_id());;
        foreach ($items as $item) {
            $total += $item->get_quote_price_total();
        }
        update_post_meta($this->get_id(), '_total', $total);
    }

    public function load_data($quote_id)
    {
        $this->set_id($quote_id);
        $this->set_status();
        $this->set_customer_id($quote_id);
        $this->set_price_level();
        $this->set_tax_exempt();
        $this->set_items();
        $this->set_shipping();
        $this->set_total();
    }

    /**
     * Saves the quote to the database.
     *
     * This function creates a new quote if it doesn't already exist. It performs 
     * various tasks such as adding the price level, items, tax exemption status, 
     * and calculating the total amount for the quote.
     *
     * @return int|false The ID of the newly created quote or false if the quote already exists.
     */
    public function save()
    {
        if ($this->get_id()) return $this->get_id();

        $quote_id = $this->add_quote();
        if ($quote_id) {
            $this->add_price_level();
            $this->add_items();
            $this->add_shipping();
            $this->add_tax_exempt();
            $this->add_total();
        }

        $this->load_data($quote_id);

        return $quote_id;
    }
}
