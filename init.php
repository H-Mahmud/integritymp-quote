<?php
defined('ABSPATH') || exit;

/**
 * Called when the plugin is activated.
 *
 * Creates a 'vendor' attribute, which is a select-type attribute
 * ordered by menu_order and with archives enabled.
 *
 * @since 1.0
 */
function imq_plugin_activation()
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
register_activation_hook(IMQ_PLUGIN_FILE, 'imq_plugin_activation');
