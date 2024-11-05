<?php
defined('ABSPATH') || exit;

$args = array(
    'post_type' => 'shop_quote',
    'posts_per_page' => 10,
    'author' => get_current_user_id()
);
$quotes_query = new WP_Query($args);

if ($quotes_query->have_posts()) {
    echo '<h2>Your Quotes</h2><ul>';
    while ($quotes_query->have_posts()) {
        $quotes_query->the_post();
        $quote_id = get_the_ID();
        echo '<li>';
        echo '<a href="' . esc_url(wc_get_account_endpoint_url('view-quote') . $quote_id) . '">';
        echo 'Quote #' . $quote_id;
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>';
    wp_reset_postdata();
} else {
    echo '<p>No quotes found.</p>';
}
