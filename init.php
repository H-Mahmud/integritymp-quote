<?php
defined('ABSPATH') || exit;

/**
 * Called when the plugin is activated.
 *
 * Creates the "vendor" attribute (if it doesn't already exist) and
 * adds the "quotes" and "view-quote" endpoints to the Integrity Quote
 * plugin.
 *
 * @since 1.0
 */
function imq_plugin_activation()
{
    imq_create_db_quote_tables();
    imq_create_vendor_attribute();
    Integrity_Mp_Quote::add_quotes_endpoint();
    Integrity_Mp_Quote::add_view_quote_endpoint();
    flush_rewrite_rules();
}
register_activation_hook(IMQ_PLUGIN_FILE, 'imq_plugin_activation');

/**
 * Creates the 'vendor' attribute, which is a select-type attribute
 * ordered by menu_order and with archives enabled.
 *
 * Does nothing if the attribute already exists.
 *
 * @since 1.0
 */
function imq_create_vendor_attribute()
{
    $slug = 'vendor';
    $name = 'Vendor';
    if (taxonomy_exists('pa_' . $slug)) return;

    $attribute = array(
        'name'         => $name,
        'slug'         => $slug,
        'type'         => 'select',
        'order_by'     => 'menu_order',
        'has_archives' => true,
    );

    wc_create_attribute($attribute);
}


/**
 * Creates the database table for storing quote items. This table
 * stores the relationship between quotes, products, and customers.
 *
 * The table has the following columns:
 *
 * - `quote_item_id`: The ID of the quote item.
 * - `quote_id`: The ID of the quote the item belongs to.
 * - `product_id`: The ID of the product the item is for.
 * - `variation_id`: The ID of the product variation the item is for.
 * - `customer_id`: The ID of the customer who submitted the quote. NULL if it was submitted by a guest.
 * - `date_created`: The date the quote item was created.
 * - `product_qty`: The quantity of the product the customer wants to purchase.
 *
 * The table is created with the following indexes:
 *
 * - `quote_id`: For quickly finding all quote items for a given quote.
 * - `product_id`: For quickly finding all quote items for a given product.
 * - `customer_id`: For quickly finding all quote items for a given customer.
 * - `date_created`: For quickly sorting quote items by creation date.
 *
 * @since 1.0
 */
function imq_create_db_quote_tables()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'quote_product_lookup';
    $charset_collate = $wpdb->get_charset_collate();
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            quote_item_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            quote_id BIGINT(20) UNSIGNED NOT NULL,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            variation_id BIGINT(20) UNSIGNED NOT NULL,
            customer_id BIGINT(20) UNSIGNED DEFAULT NULL,
            date_created DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
            product_qty INT(11) NOT NULL,
            PRIMARY KEY (quote_item_id),
            INDEX quote_id (quote_id),
            INDEX product_id (product_id),
            INDEX customer_id (customer_id),
            INDEX date_created (date_created)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
