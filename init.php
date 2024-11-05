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
