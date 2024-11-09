<?php
defined('ABSPATH') || exit;

$quote_id = get_query_var('view-quote');

if (!$quote_id || get_post_type($quote_id) != 'shop_quote') {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    exit;
}


$post = get_post($quote_id);
// Get cart items
$cart_items = WC()->cart->get_cart();

// Start output buffering
ob_start();
?>
<html>

<head>
    <title>Invoice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        @media print {
            .table thead tr {
                background-color: #bdc3c9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table-total .total {
                background-color: #bdc3c9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #000;
        }

        .invoice {
            max-width: 800px;
            margin: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table thead tr {
            background-color: #bdc3c9;
        }

        .table tbody {
            border: 1px solid #acbac9;
        }

        .table tbody th,
        .table tbody td {
            border: none;
            border-left: 1px solid #acbac9;
            padding: 8px;
            text-align: left;
        }

        .table th {
            border: 1px solid #acbac9;
            padding: 8px;
            text-align: left;
        }

        .table-total {
            width: 300px;
            margin-left: auto;
            margin-right: 0;
        }

        .table-total tbody,
        .table-total tbody td,
        .table-total tbody th {
            border: none;
        }

        .table-total tbody th:last-child {
            text-align: right;
        }

        .table-total .total {
            background-color: #bdc3c9;
        }

        .btn-print {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin-top: 1rem;
            text-align: right;
        }

        .btn-print:hover {
            background-color: #45a049;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
    </style>
</head>

<body>
    <div class="invoice">
        <div class="header">
            <div>
                <img src="<?php echo IMQ_PLUGIN_DIR_URL . '/assets/integritymp-logo.png'; ?>" alt="integritymp">
                <p style="text-align:left; margin-top: 6px;">
                    1608 Hampton Lane <br>
                    Safety Harbor, FL 34695 <br>
                    Tel: 727-244-2013 <br>
                    Fax: 727-259-1463
                </p>
            </div>
            <div>
                <h1 style="font-weight: 900; color: #2C5988;">PROPOSAL</h1>
                <table>
                    <tr>
                        <td>Quote Number:</td>
                        <td> <?php echo $post->ID; ?></td>
                    </tr>
                    <tr>
                        <td>Quote Date:</td>
                        <td> <?php echo get_the_date('M d, Y', $quote_id) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="table" style="max-width: 300px;">
            <thead>
                <tr>
                    <th>Quote To:</th>
                </tr>
            </thead>
            <tr>
                <td>
                    St. Jseph's Hospital<br>
                    Stacey MoClinton <br>
                    Clinical Engineering Services <br>
                    30001 W MLK Bivd <br>
                    Tampa, FL 33607
                </td>
            </tr>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Payment Terms</th>
                <th>Quote Good Thru</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td> DisplayPort to DVI-D Adapters</td>
                <td> Net 30 Days</td>
                <td> 11/23/24</td>
            </tr>
        </tbody>
    </table>

    <div class="reference">

    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Quantity</th>
                <th style="min-width: 120px;">Line Item ID</th>
                <th>Description</th>
                <th>Unit Price</th>
                <th>Ext. Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $cart_item_key => $cart_item):
                $_product = $cart_item['data'];
                $quantity = $cart_item['quantity'];
                $product = wc_get_product($_product->id);
                $unit_price = $product->get_price();
                $ext_price = $product->get_price() * $quantity
            ?>
                <tr>
                    <td><?php echo esc_html($quantity); ?></td>
                    <td><?php echo esc_html($product->get_sku()); ?></td>
                    <td><?php echo esc_html($product->get_description()); ?></td>
                    <td><?php echo wc_price($unit_price); ?></td>
                    <td><?php echo wc_price($ext_price); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="table table-total">
        <tr>
            <td>Sales Tax</td>
            <td></td>
        </tr>
        <tr class="total">
            <th>TOTAL</th>
            <th><?php echo wc_price(WC()->cart->get_total()); ?></th>
        </tr>

    </table>

    <div style="text-align: right;">
        <small> Sales Tax</small>
        <br>
        <span>
            <span>TOTAL</span>
            <span> 180.90</span>
        </span>

    </div>

    <dl>
        <dt>
            <h4>This proposal is subject to the conditions noted below:</h4>
        </dt>
        <dd>This proposal does not include freight.</dd>
        <dd>This proposal does not include installation.</dd>
        <dd>BayCare's vendor number for Integrity Medical Products is 774121</dd>
    </dl>





    <button class="btn-print" id="print-invoice-btn">Print Invoice</button>

    </div>

    <script>
        // Function to generate the PDF
        document.getElementById('print-invoice-btn').addEventListener('click', function() {
            window.print();
        });
    </script>
</body>

</html>
<?php

// Output the content
echo ob_get_clean();
