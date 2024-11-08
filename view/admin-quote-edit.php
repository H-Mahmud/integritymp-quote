<?php
defined('ABSPATH') || exit;

if (!current_user_can('manage_options'))
    wp_die('You do not have sufficient permissions to access this page.');

if (!isset($_GET['id']) || empty($_GET['id']) || get_post($_GET['id'])->post_type !== 'shop_quote')
    wp_die('You attempted to edit a quote that does not exist. Perhaps it was deleted?');
?>
<div class="wrap">
    <h1 class="wp-headline">Edit Quote</h1>
    <br class="wp-header-end">
    <div class="quote-invoice postbox ">
        <div class="inside">
            Quote Details
        </div>
    </div>
</div>
