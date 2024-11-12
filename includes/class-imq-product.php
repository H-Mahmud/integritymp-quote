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

        add_action('woocommerce_product_options_pricing', array($this, 'add_custom_price_fields'), 10);
        add_action('woocommerce_process_product_meta', array($this, 'save_custom_price_fields'), 10, 1);

        add_action('woocommerce_product_import_inserted_product_object', array($this, 'save_imported_custom_fields'), 10, 2);
        add_filter('woocommerce_csv_product_import_mapping_options', array($this, 'add_custom_fields_to_csv_mapping'));
        add_filter('woocommerce_csv_product_import_mapping_default_columns', array($this, 'set_default_csv_mapping_for_custom_fields'));

        add_filter('woocommerce_product_importer_parsed_data',  array($this, 'custom_woocommerce_product_import_images'), 10, 2);
        add_action('woocommerce_cart_totals_before_order_total', array($this, 'add_cart_totals_shipping_address'));
        add_action('woocommerce_calculated_shipping', array($this, 'save_custom_shipping_field_to_session'));
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
        $quote_request = wc_get_checkout_url();
        $quote_request = add_query_arg('quote_request', 'true', $quote_request);
?>
        <a href="<?php echo esc_url($quote_request); ?>" id="quote_request_btn" class="checkout-button button alt wc-forward<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>">
            <?php esc_html_e('Complete Quote', 'integritymp-quote'); ?>
        </a>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const requestQuoteBtn = document.getElementById("quote_request_btn");
                const shippingCalculatorForm = document.querySelector(".woocommerce-shipping-calculator");

                let isFormSubmitted = false;
                requestQuoteBtn.addEventListener("click", function(event) {
                    if (!shippingCalculatorForm.checkValidity()) {
                        event.preventDefault();
                        shippingCalculatorForm.reportValidity();
                        return;
                    }

                    if (!isFormSubmitted) {
                        shippingCalculatorForm.querySelector("button[type='submit']").click();
                        document.body.addEventListener("updated_shipping_method", function() {
                            isFormSubmitting = true;
                        });
                        requestQuoteBtn.click();
                    }
                });
            });
        </script>
    <?php
    }



    /**
     * Adds custom price fields to the WooCommerce product options
     *
     * Adds three text inputs for prices to the pricing options area of the product
     * options page. The IDs of the inputs are '_price_level_1', '_price_level_2', and
     * '_cost', and the labels are 'Price Level 1', 'Price Level 2', and 'Cost',
     * respectively.
     */
    public function add_custom_price_fields()
    {
        woocommerce_wp_text_input([
            'id' => '_price_level_1',
            'label' => __('Price Level 1', 'integritymp-quote'),
            'data_type' => 'price',
        ]);

        woocommerce_wp_text_input([
            'id' => '_price_level_2',
            'label' => __('Price Level 2', 'integritymp-quote'),
            'data_type' => 'price',
        ]);

        woocommerce_wp_text_input([
            'id' => '_cost',
            'label' => __('Cost', 'integritymp-quote'),
            'data_type' => 'price',
        ]);
    }



    /**
     * Saves the custom price fields from the product options page to the database.
     *
     * When the user saves the product, the custom price fields are saved to the
     * database as post meta fields. The IDs of the fields are '_price_level_1',
     * '_price_level_2', and '_cost', and the values are the input values provided
     * by the user.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_custom_price_fields($post_id)
    {
        if (isset($_POST['_price_level_1'])) {
            update_post_meta($post_id, '_price_level_1', sanitize_text_field($_POST['_price_level_1']));
        }
        if (isset($_POST['_price_level_2'])) {
            update_post_meta($post_id, '_price_level_2', sanitize_text_field($_POST['_price_level_2']));
        }
        if (isset($_POST['_cost'])) {
            update_post_meta($post_id, '_cost', sanitize_text_field($_POST['_cost']));
        }
    }



    /**
     * Saves custom price fields imported from a CSV file to the database.
     *
     * When a product is imported from a CSV file, this function is called to
     * save the custom price fields to the database as post meta fields. The
     * IDs of the fields are '_price_level_1', '_price_level_2', and '_cost',
     * and the values are the input values provided by the user in the CSV file.
     *
     * @param WC_Product $object The product object being imported.
     * @param array $data The data from the CSV file being imported.
     */
    public function save_imported_custom_fields($object, $data)
    {
        if (!empty($data['_price_level_1']))
            update_post_meta($object->get_id(), '_price_level_1', $data['_price_level_1']);

        if (!empty($data['_price_level_2']))
            update_post_meta($object->get_id(), '_price_level_2', $data['_price_level_2']);

        if (!empty($data['_cost']))
            update_post_meta($object->get_id(), '_cost', $data['_cost']);


        if (!empty($data['manufacturer'])) {
            $manufacturer_value = sanitize_text_field($data['manufacturer']);
            $taxonomy_slug = 'manufacturer';
            $taxonomy = 'pa_' . $taxonomy_slug;
            $term_ids = array();
            $term = get_term_by('name', $manufacturer_value, $taxonomy);
            if ($term) {
                $term_ids[] = (int) $term->term_id;
            } else {
                $result =  wp_insert_term($manufacturer_value, $taxonomy);
                $term_ids[] =  $result['term_id'];
            }

            imq_assign_terms_to_product($object->get_id(), $taxonomy_slug, $term_ids);
        }
    }



    /**
     * Adds custom fields to the CSV mapping options.
     *
     * This function takes an array of options and adds custom fields to it.
     * The custom fields are 'Price Level 1', 'Price Level 2', 'Cost', and 'Manufacturer',
     * which are added to the existing options with the IDs '_price_level_1', '_price_level_2',
     * '_cost', and 'manufacturer', respectively.
     *
     * @param array $options The existing options.
     * @return array The modified options with custom fields added.
     */
    public function add_custom_fields_to_csv_mapping($options)
    {
        $options['price']['options']['_price_level_1'] = 'Price Level 1';
        $options['price']['options']['_price_level_2'] = 'Price Level 2';
        $options['price']['options']['_cost'] = 'Cost';

        $options['manufacturer'] = 'Manufacturer';
        return $options;
    }



    /**
     * Sets default CSV mapping for custom fields.
     *
     * This function modifies the provided columns array by adding default
     * mappings for custom fields. The custom fields include 'Price Level 1',
     * 'Price Level 2', 'Cost', and 'Manufacturer', which are mapped to their
     * respective IDs '_price_level_1', '_price_level_2', '_cost', and 'manufacturer'.
     *
     * @param array $columns The existing columns array to be modified.
     * @return array The modified columns array with default mappings for custom fields.
     */
    function set_default_csv_mapping_for_custom_fields($columns)
    {
        $columns['Price Level 1'] = '_price_level_1';
        $columns['Price Level 2'] = '_price_level_2';
        $columns['Cost'] = '_cost';

        $columns['Manufacturer'] = 'manufacturer';
        return $columns;
    }


    /**
     * Modifies the parsed data for a product during import, specifically the 'raw_image_id' and
     * 'raw_gallery_image_ids' fields. If the image is a URL, it is added to the array of image URLs
     * as-is. If the image is a filename, it is assumed to reside in the
     * /wp-content/images/ directory and the URL is constructed accordingly.
     *
     * @param array $parsed_data The parsed data for the product being imported.
     * @param WC_Product_Importer $importer The WC_Product_Importer instance doing the importing.
     *
     * @return array The modified parsed data.
     */
    public function custom_woocommerce_product_import_images($parsed_data, $importer)
    {
        if (isset($parsed_data['raw_image_id'])) {
            $parsed_data['raw_image_id'] = $this->custom_process_product_import_images($parsed_data['raw_image_id']);
        }

        if (isset($parsed_data['raw_gallery_image_ids'])) {
            $parsed_data['raw_gallery_image_ids'] = $this->custom_process_product_import_images($parsed_data['raw_gallery_image_ids']);
        }

        return $parsed_data;
    }

    /**
     * Processes the images field from the CSV file being imported.
     *
     * If the image is a URL, it is added to the array of image URLs as-is.
     * If the image is a filename, it is assumed to reside in the
     * /wp-content/product-images/ directory and the URL is constructed accordingly.
     *
     * @param string|array $images The images field from the CSV file.
     * @return array|string The array of image URLs or a single image URL.
     */
    private function custom_process_product_import_images($images)
    {
        $images_base_url = site_url('/wp-content/product-images/');

        if (is_string($images)) {
            return filter_var($images, FILTER_VALIDATE_URL) ? $images : $images_base_url . $images;
        }

        $image_urls = [];
        foreach ($images as $filename) {
            $image_urls[] = filter_var($filename, FILTER_VALIDATE_URL) ? $filename : $images_base_url . $filename;
        }

        return $image_urls;
    }


    /**
     * Adds a shipping address row to the cart totals table.
     *
     * @since 1.0.0
     */
    public function add_cart_totals_shipping_address()
    {
    ?>
        <tr class="shipping-address-row">
            <th><?php _e('Shipping', 'integritymp-quote'); ?></th>
            <td>

                <form class="woocommerce-shipping-calculator" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
                    <section class="shipping-address-form">

                        <?php if (apply_filters('woocommerce_shipping_calculator_enable_country', true)) : ?>
                            <p class="form-row form-row-wide" id="calc_shipping_country_field">
                                <label for="calc_shipping_country" class="screen-reader-text"><?php esc_html_e('Country / region:', 'woocommerce'); ?></label>
                                <select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state country_select" rel="calc_shipping_state">
                                    <option value="default"><?php esc_html_e('Select a country / region&hellip;', 'woocommerce'); ?></option>
                                    <?php
                                    foreach (WC()->countries->get_shipping_countries() as $key => $value) {
                                        echo '<option value="' . esc_attr($key) . '"' . selected(WC()->customer->get_shipping_country(), esc_attr($key), false) . '>' . esc_html($value) . '</option>';
                                    }
                                    ?>
                                </select>
                            </p>
                        <?php endif; ?>

                        <?php if (apply_filters('woocommerce_shipping_calculator_enable_state', true)) : ?>
                            <p class="form-row form-row-wide" id="calc_shipping_state_field">
                                <?php
                                $current_cc = WC()->customer->get_shipping_country();
                                $current_r  = WC()->customer->get_shipping_state();
                                $states     = WC()->countries->get_states($current_cc);

                                if (is_array($states) && empty($states)) {
                                ?>
                                    <input type="hidden" name="calc_shipping_state" id="calc_shipping_state" placeholder="<?php esc_attr_e('State / County', 'woocommerce'); ?>" required />
                                <?php
                                } elseif (is_array($states)) {
                                ?>
                                    <span>
                                        <label for="calc_shipping_state" class="screen-reader-text"><?php esc_html_e('State / County:', 'woocommerce'); ?></label>
                                        <select name="calc_shipping_state" class="state_select" id="calc_shipping_state" data-placeholder="<?php esc_attr_e('State / County', 'woocommerce'); ?>">
                                            <option value=""><?php esc_html_e('Select an option&hellip;', 'woocommerce'); ?></option>
                                            <?php
                                            foreach ($states as $ckey => $cvalue) {
                                                echo '<option value="' . esc_attr($ckey) . '" ' . selected($current_r, $ckey, false) . '>' . esc_html($cvalue) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </span>
                                <?php
                                } else {
                                ?>
                                    <label for="calc_shipping_state" class="screen-reader-text"><?php esc_html_e('State / County:', 'woocommerce'); ?></label>
                                    <input type="text" class="input-text" value="<?php echo esc_attr($current_r); ?>" placeholder="<?php esc_attr_e('State / County', 'woocommerce'); ?>" name="calc_shipping_state" id="calc_shipping_state" required />
                                <?php
                                }
                                ?>
                            </p>
                        <?php endif; ?>

                        <?php if (apply_filters('woocommerce_shipping_calculator_enable_city', true)) : ?>
                            <p class="form-row form-row-wide" id="calc_shipping_city_field">
                                <label for="calc_shipping_city" class="screen-reader-text"><?php esc_html_e('City:', 'woocommerce'); ?></label>
                                <input type="text" class="input-text" value="<?php echo esc_attr(WC()->customer->get_shipping_city()); ?>" placeholder="<?php esc_attr_e('City', 'woocommerce'); ?>" name="calc_shipping_city" id="calc_shipping_city" required />
                            </p>
                        <?php endif; ?>

                        <?php if (apply_filters('woocommerce_shipping_calculator_enable_postcode', true)) : ?>
                            <p class="form-row form-row-wide" id="calc_shipping_postcode_field">
                                <label for="calc_shipping_postcode" class="screen-reader-text"><?php esc_html_e('Postcode / ZIP:', 'woocommerce'); ?></label>
                                <input type="text" class="input-text" value="<?php echo esc_attr(WC()->customer->get_shipping_postcode()); ?>" placeholder="<?php esc_attr_e('Postcode / ZIP', 'woocommerce'); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" required />
                            </p>
                        <?php endif; ?>

                        <p class="form-row form-row-wide" id="shipping_address_field">
                            <label for="shipping_address_1" class="screen-reader-text"><?php esc_html_e('Address', 'woocommerce'); ?></label>
                            <input type="text" class="input-text" value="<?php echo esc_attr(WC()->customer->get_shipping_address_1()); ?>" placeholder="<?php esc_attr_e('Address', 'woocommerce'); ?>" name="shipping_address_1" id="shipping_address_1" required />
                        </p>

                        <p><button type="submit" name="calc_shipping" value="1" class="button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"><?php esc_html_e('Update', 'woocommerce'); ?></button></p>
                        <?php wp_nonce_field('woocommerce-shipping-calculator', 'woocommerce-shipping-calculator-nonce'); ?>
                    </section>
                </form>

            </td>
        </tr>

        <style>
            .shipping-address-row select {
                min-height: 50px !important;
            }
        </style>
<?php
    }

    /**
     * Save the custom shipping field value to the customer session.
     *
     * @since 1.0.0
     */
    public function save_custom_shipping_field_to_session()
    {
        if (isset($_POST['shipping_address_1'])) {
            $shipping_address_1 = sanitize_text_field($_POST['shipping_address_1']);
            WC()->customer->set_shipping_address_1($shipping_address_1);
            WC()->customer->save();

            // Set shipping address in the WooCommerce session
            WC()->session->set('customer', array_merge(
                WC()->session->get('customer', []),
                ['shipping_address_1' => sanitize_text_field($shipping_address_1)]
            ));
        }
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
