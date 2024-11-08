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
    protected $data = array(
        'id'                           => 0,
        'status'                       => '',
        'currency'                     => '',
        'prices_include_tax'           => false,
        'date_created'                 => null,
        'date_modified'                => null,
        'discount_total'               => 0,
        'discount_tax'                 => 0,
        'shipping_total'               => 0,
        'shipping_tax'                 => 0,
        'cart_tax'                     => 0,
        'total'                        => 0,
        'total_tax'                    => 0,

        // Order props.
        'customer_id'                  => 0,
        'order_key'                    => '',
        'shipping'                     => array(
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
        'customer_ip_address'          => '',
        'customer_user_agent'          => '',
        // Operational data.
        'new_quote_email_sent'         => false,
    );


    /**
     * Quote Items
     * 
     * @since 1.0
     * @var array
     * */
    protected $data_items = array();

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
            $this->set_id($order);
        } else {
            $this->add_quote();
        }
    }


    public function get_id()
    {
        return $this->data['id'];
    }

    private function set_id($id)
    {
        $this->data['id'] = $id;
    }

    public function ge_status()
    {
        return 'publish';
    }

    public function get_user_id()
    {
        $user_id = get_current_user_id();
        if ($user_id) {
            return $user_id;
        }

        return false;
    }

    private function add_quote()
    {
        $quote_id = wp_insert_post(array(
            'post_author'           => $this->get_user_id(),
            'post_type'    => 'shop_quote',
            'post_status'  => $this->ge_status(),
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
    }

    // Function to retrieve quote data
    public function get_shop_quote_data($quote_id)
    {
        $data = array(
            'id'                    => $quote_id,
            'status'                => get_post_meta($quote_id, '_status', true),
            'currency'              => get_post_meta($quote_id, '_currency', true),
            'prices_include_tax'    => get_post_meta($quote_id, '_prices_include_tax', true),
            'date_created'          => get_post_meta($quote_id, '_date_created', true),
            'date_modified'         => get_post_meta($quote_id, '_date_modified', true),
            'discount_total'        => get_post_meta($quote_id, '_discount_total', true),
            'discount_tax'          => get_post_meta($quote_id, '_discount_tax', true),
            'shipping_total'        => get_post_meta($quote_id, '_shipping_total', true),
            'shipping_tax'          => get_post_meta($quote_id, '_shipping_tax', true),
            'cart_tax'              => get_post_meta($quote_id, '_cart_tax', true),
            'total'                 => get_post_meta($quote_id, '_total', true),
            'total_tax'             => get_post_meta($quote_id, '_total_tax', true),
            'customer_id'           => get_post_meta($quote_id, '_customer_id', true),
            'order_key'             => get_post_meta($quote_id, '_order_key', true),
            'shipping'              => get_post_meta($quote_id, '_shipping', true),
            'customer_ip_address'   => get_post_meta($quote_id, '_customer_ip_address', true),
            'customer_user_agent'   => get_post_meta($quote_id, '_customer_user_agent', true),
            'created_via'           => get_post_meta($quote_id, '_created_via', true),
            'date_completed'        => get_post_meta($quote_id, '_date_completed', true),
            'date_paid'             => get_post_meta($quote_id, '_date_paid', true),
            'cart_hash'             => get_post_meta($quote_id, '_cart_hash', true),
            'new_quote_email_sent'  => get_post_meta($quote_id, '_new_quote_email_sent', true),
        );

        return $data;
    }



    private function insert_quote_item($product_id, $product_qty = 1)
    {
        $product = wc_get_product($product_id);
        if (!$product) return false;

        global $wpdb;
        $table_name = $wpdb->prefix . 'quote_product_lookup';

        $quote_id = $this->get_id();
        $variation_id = 0;
        $customer_id = $this->get_user_id();
        $date_created = current_time('mysql');
        $price = $product->get_price();
        $quote_price = $this->get_quote_price($product_id);


        $data = [
            'quote_id'      => $quote_id,
            'product_id'    => $product_id,
            'variation_id'  => $variation_id,
            'customer_id'   => $customer_id,
            'date_created'  => $date_created,
            'product_qty'   => $product_qty,
        ];

        $inserted = $wpdb->insert($table_name, $data, [
            '%d', // quote_id
            '%d', // product_id
            '%d', // variation_id
            '%d', // customer_id
            '%s', // date_created
            '%d'  // product_qty
        ]);

        return $inserted ? $wpdb->insert_id : false;
    }

    public function get_quote_price($product_id)
    {
        return 0;
    }



    protected function set_item($product_id, $product_qty = 1)
    {
        $this->data_items[] = [
            'product_id'    => $product_id,
            'product_qty'   => $product_qty,
        ];
    }

    private function add_items()
    {
        $items = $this->data_items;
        foreach ($items as $item) {
            $this->insert_quote_item($item['product_id'], $item['product_qty']);
        }
    }


    protected function add_shipping($shipping)
    {
        update_post_meta($this->get_id(), '_shipping', maybe_serialize($shipping));
    }

    public function save()
    {
        $this->add_items();
    }
}
