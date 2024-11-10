<?php
defined('ABSPATH') || exit;

class IMQ_Quote extends IMQ_Abstract_Quote
{

    public function __construct($order = 0)
    {
        parent::__construct($order);
    }

    public function get_price_level()
    {
        return parent::get_price_level();
    }

    public function add_product($product_id, $quantity)
    {
        parent::set_item($product_id, $quantity);
    }

    public function add_shipping($address)
    {
        parent::add_shipping($address);
    }

    public function get_items()
    {
        return parent::get_items();
    }
}
