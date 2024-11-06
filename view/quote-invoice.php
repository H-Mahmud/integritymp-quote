<?php
defined('ABSPATH') || exit;
// Get cart items
$cart_items = WC()->cart->get_cart();

// Start output buffering
ob_start();
?>
<html>

<head>
    <title>Invoice</title>
    <style>
        /* Add your custom styles for the invoice here */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .invoice {
            max-width: 800px;
            margin: auto;
        }

        .header,
        .footer {
            text-align: center;
            margin-bottom: 20px;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .item-table th,
        .item-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .btn-print {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }

        .btn-print:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="invoice">
        <div class="header">
            <h1>Invoice</h1>
        </div>
        <table class="item-table">
            <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Line Item ID</th>
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
