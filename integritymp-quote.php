<?php
/*
 * Plugin Name:       IntegrityMP Quoting and Sales Add-On
 * Plugin URI:        https://integritymp.com/
 * Description:       A quoting and sales add-on for IntegrityMP, allowing registered customers to request quotes, view past orders, and easily convert quotes into sales. Integrates with WooCommerce for quote management and product catalog access.
 * Version:           1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Mahmudul Hasan
 * Author URI:        https://imahmud.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/H-Mahmud/integritymp-quote
 * Text Domain:       integritymp-quote
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 */

defined('ABSPATH') || exit;

// Plugin constants
defined('IMQ_PLUGIN_FILE') || define('IMQ_PLUGIN_FILE', __FILE__);
defined('IMQ_PLUGIN_DIR_PATH') || define('IMQ_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
defined('IMQ_PLUGIN_DIR_URL') || define('IMQ_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));

// Load plugin classes & dependencies
require_once IMQ_PLUGIN_DIR_PATH . 'init.php';
require_once IMQ_PLUGIN_DIR_PATH . 'imq-functions.php';
require_once IMQ_PLUGIN_DIR_PATH . 'includes/class-customer-account.php';
require_once IMQ_PLUGIN_DIR_PATH . 'includes/class-otp-verification.php';
require_once IMQ_PLUGIN_DIR_PATH . 'includes/class-imq-product.php';
require_once IMQ_PLUGIN_DIR_PATH . 'includes/class-imq-quote.php';
require_once IMQ_PLUGIN_DIR_PATH . 'includes/class-quote-items.php';
require_once IMQ_PLUGIN_DIR_PATH . 'includes/class-quote-shipping.php';
require_once IMQ_PLUGIN_DIR_PATH . 'includes/class-quote-abstract.php';
require_once IMQ_PLUGIN_DIR_PATH . 'includes/class-quote.php';
require_once IMQ_PLUGIN_DIR_PATH . 'includes/class-product-category-filter.php';
require_once IMQ_PLUGIN_DIR_PATH . 'includes/class-cart-content.php';
