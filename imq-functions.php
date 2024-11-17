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

/*
add_action('wp_head', function () {
    if (!is_shop()) return;
    <style>
        body .site-content {
            visibility: hidden;
        }

        body.show-shop .site-content {
            visibility: visible !important;
        }

        body.woocommerce-shop.product-category-exists #secondary {
            display: none !important;
        }

        body.woocommerce-shop.product-category-exists #primary {
            width: 100%;
            border-left: 0 !important;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            if ($("body.woocommerce-shop .ast-woocommerce-container li.product-category.product").length) {
                $("body").addClass("product-category-exists");

            }
            $("body").addClass("show-shop");
        });
    </script>

});
*/

/**
 * Retrieves the price for the given product ID based on the customer's price level.
 *
 * If the customer has a price level set, this function will first look for a meta value
 * with the key set to the price level. If that value exists, it will be returned.
 *
 * If no price level is set, or if the price level meta value does not exist, this function
 * will fall back to retrieving the product's regular price.
 *
 * @param int $product_id The ID of the product.
 *
 * @return float The price of the product based on the customer's price level.
 */
function get_quote_price($product_id)
{

    if (!is_user_logged_in() || get_user_meta(get_current_user_id(), 'verification_status', true) !== 'verified') return false;

    $price_level = get_user_meta(get_current_user_id(), 'price_level', true);
    if (!$price_level) return false;

    $price = get_post_meta($product_id, $price_level, true);
    if (!$price) false;
    return $price;
}
