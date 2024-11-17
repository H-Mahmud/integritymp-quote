<?php
defined('ABSPATH') || exit;

class IMQ_Shipping
{
    /**
     * Shipping details
     * 
     */
    private $shipping = array(
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
    );

    /**
     * Constructor
     * 
     * Retrieve shipping details from the database and
     * set it as the default shipping details.
     * 
     * @param int $quote_id Quote ID to retrieve shipping details from.
     */
    public function __construct($quote_id = 0)
    {
        if (empty($quote_id))  return;

        $shipping = maybe_unserialize(get_post_meta($quote_id, '_shipping', true));
        $this->shipping = wp_parse_args($shipping, $this->shipping);
    }

    /**
     * Retrieve the first name from the shipping details.
     *
     * @return string The first name from the shipping details.
     */
    public function get_first_name()
    {
        return $this->shipping['first_name'];
    }

    /**
     * Retrieve the last name from the shipping details.
     *
     * @return string The last name from the shipping details.
     */
    public function get_last_name()
    {
        return $this->shipping['last_name'];
    }

    /**
     * Retrieve the full name from the shipping details.
     *
     * @return string The full name from the shipping details.
     */
    public function get_full_name()
    {
        return $this->shipping['first_name'] . ' ' . $this->shipping['last_name'];
    }

    /**
     * Retrieve the company name from the shipping details.
     *
     * @return string The company name from the shipping details.
     */
    public function get_company()
    {
        return $this->shipping['company'];
    }

    /**
     * Retrieve the first line of the shipping address.
     *
     * @return string The first line of the shipping address.
     */
    public function get_address_1()
    {
        return $this->shipping['address_1'];
    }

    /**
     * Retrieve the second line of the shipping address.
     *
     * @return string The second line of the shipping address.
     */
    public function get_address_2()
    {
        return $this->shipping['address_2'];
    }

    /**
     * Retrieve the shipping city, state, and postal code.
     *
     * @return string The shipping city, state, and postal code.
     */
    public function get_state_address()
    {
        // return $this->shipping['city'] . ', ' . $this->shipping['state'] . ' ' . $this->shipping['postcode'];
        return $this->get_address_2();
    }

    /**
     * Retrieve the shipping city.
     *
     * @return string The shipping city.
     */
    public function get_city()
    {
        return $this->shipping['city'];
    }

    /**
     * Retrieve the shipping state.
     *
     * @return string The shipping state.
     */
    public function get_state()
    {
        return $this->shipping['state'];
    }

    /**
     * Retrieve the shipping postal code.
     *
     * @return string The shipping postal code.
     */
    public function get_postcode()
    {
        return $this->shipping['postcode'];
    }

    /**
     * Retrieve the shipping country.
     *
     * @return string The shipping country.
     */
    public function get_country()
    {
        return $this->shipping['country'];
    }

    /**
     * Retrieve the shipping phone number.
     *
     * @return string The shipping phone number.
     */
    public function get_phone()
    {
        return $this->shipping['phone'];
    }
}
