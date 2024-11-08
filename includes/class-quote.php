<?php
defined('ABSPATH') || exit;

class IMQ_Quote extends IMQ_Abstract_Quote
{

    public function __construct()
    {
        parent::__construct();
    }

    public function add_product($product_id, $quantity)
    {
        parent::set_item($product_id, $quantity);
    }

    public function add_shipping($address)
    {
        parent::add_shipping($address);
    }
}
