<?php
defined('ABSPATH') || exit;

/**
 * Assign terms to a product and save the product.
 *
 * @param int    $product_id Product ID.
 * @param string $taxonomy_slug   Taxonomy slug.
 * @param array  $term_ids   Term IDs to assign.
 */
function imq_assign_terms_to_product($product_id, $taxonomy_slug, $term_ids)
{
    $product = wc_get_product($product_id);
    $taxonomy = 'pa_' . $taxonomy_slug;
    $taxonomy_id = imq_get_attribute_id_by_slug($taxonomy_slug);

    $attributes = $product->get_attributes();
    $attributes[$taxonomy] = new WC_Product_Attribute();
    $attributes[$taxonomy]->set_id($taxonomy_id);
    $attributes[$taxonomy]->set_name($taxonomy);
    $attributes[$taxonomy]->set_options($term_ids);
    $attributes[$taxonomy]->set_position(0);
    $attributes[$taxonomy]->set_visible(true);
    $attributes[$taxonomy]->set_variation(false);

    $product->set_attributes($attributes);
    $product->save();

    wp_set_object_terms($product_id, $term_ids, $taxonomy, true);
}


/**
 * Get the ID of a product attribute by its slug.
 *
 * @param string $slug The slug of the attribute.
 * @return int|null The attribute ID if found, or null if not found.
 */
function imq_get_attribute_id_by_slug($slug)
{
    $attribute_taxonomies = wc_get_attribute_taxonomies();

    foreach ($attribute_taxonomies as $attribute) {
        if ($attribute->attribute_name === $slug) {
            return $attribute->attribute_id;
        }
    }

    return null;
}
