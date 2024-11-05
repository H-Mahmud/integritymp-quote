<?php
defined('ABSPATH') || exit;

global $wp;
if (isset($wp->query_vars['view-quote'])) {
    $quote_id = absint($wp->query_vars['view-quote']);
    $quote = get_post($quote_id);

    if ($quote && $quote->post_type === 'shop_quote' && $quote->post_author == get_current_user_id()) {
        echo '<h2>Quote Details</h2>';
        echo '<p><strong>Quote ID:</strong> ' . $quote_id . '</p>';
        echo '<p><strong>Quote Status:</strong> ' . get_post_status($quote_id) . '</p>';
        // Add additional quote details as needed
    } else {
        echo '<p>Quote not found or access denied.</p>';
    }
}
