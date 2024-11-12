<?php
defined('ABSPATH') || exit;

$args = array(
    'post_type' => 'shop_quote',
    'posts_per_page' => 10,
    'author' => get_current_user_id()
);
$quotes_query = new WP_Query($args);

if ($quotes_query->have_posts()) { ?>

    <table class="woocommerce-orders-table woocommerce-MyAccount-quote shop_table shop_table_responsive my_account_orders account-orders-table">
        <thead>
            <tr>
                <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr">Quote</span></th>
                <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr">Date</span></th>
                <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr">Total</span></th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($quotes_query->have_posts()) {
                $quotes_query->the_post(); ?>
                <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-pending order">
                    <th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number">
                        <?php the_title('<a href="' . esc_url(wc_get_account_endpoint_url('view-quote') . get_the_ID()) . '">', '</a>'); ?>
                    </th>
                    <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date">
                        <?php the_date(); ?>
                    </td>
                    <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total">
                        <?php echo get_post_meta(get_the_ID(), '_total', true); ?>
                    </td>
                </tr>
        <?php
            }
            wp_reset_postdata();
        } else {
            echo '<p>No quotes found.</p>';
        }
        ?>

        </tbody>
    </table>
