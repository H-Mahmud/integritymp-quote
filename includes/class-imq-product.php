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
        $quote_request = add_query_arg('quote_request', 'true', $quote_request);
?>
        <a href="<?php echo esc_url($quote_request); ?>" class="checkout-button button alt wc-forward<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>">
            <?php esc_html_e('Complete Quote', 'integritymp-quote'); ?>
        </a>
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


        if (!empty($data['vendor'])) {
            $vendor_value = sanitize_text_field($data['vendor']);
            $taxonomy_slug = 'vendor';
            $taxonomy = 'pa_' . $taxonomy_slug;
            $term_ids = array();
            $term = get_term_by('name', $vendor_value, $taxonomy);
            if ($term) {
                $term_ids[] = (int) $term->term_id;
            } else {
                $result =  wp_insert_term($vendor_value, $taxonomy);
                $term_ids[] =  $result['term_id'];
            }

            imq_assign_terms_to_product($object->get_id(), $taxonomy_slug, $term_ids);
        }
    }



    /**
     * Adds custom fields to the CSV mapping options for product import.
     *
     * This function extends the available mapping options for product import by
     * adding custom fields. Specifically, it adds three price level fields and a
     * vendor field to the CSV mapping options.
     *
     * @param array $options The existing CSV mapping options.
     * @return array The modified CSV mapping options including custom fields.
     */
    public function add_custom_fields_to_csv_mapping($options)
    {
        $options['price']['options']['_price_level_1'] = 'Price Level 1';
        $options['price']['options']['_price_level_2'] = 'Price Level 2';
        $options['price']['options']['_cost'] = 'Cost';

        $options['vendor'] = 'Vendor';
        return $options;
    }



    /**
     * Sets the default mapping for custom fields when importing products from a CSV file.
     *
     * This function takes an array of default column mappings and adds custom fields to it.
     * The custom fields are 'Price Level 1', 'Price Level 2', 'Cost', and 'Vendor', which
     * are mapped to the corresponding custom fields in the database.
     *
     * @param array $columns The existing default column mappings.
     * @return array The modified default column mappings with custom fields added.
     */
    function set_default_csv_mapping_for_custom_fields($columns)
    {
        $columns['Price Level 1'] = '_price_level_1';
        $columns['Price Level 2'] = '_price_level_2';
        $columns['Cost'] = '_cost';

        $columns['Vendor'] = 'vendor';
        return $columns;
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
