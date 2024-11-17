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
