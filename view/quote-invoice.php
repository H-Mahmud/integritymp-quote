<?php
defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wc_add_notice('Please create an account to access the store. If you already have an account, please login.', 'error');
    wp_redirect(wc_get_account_endpoint_url('my-account'));
    exit;
}

$quote_id = get_query_var('view-quote');
if (!$quote_id) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    exit;
}

$quote = get_post($quote_id);
if ($quote->post_type != 'shop_quote' || $quote->post_author != get_current_user_id()) {
    wc_add_notice('Quote not found or you do not have permission to access it.', 'error');
    wp_redirect(wc_get_account_endpoint_url('my-account'));
    exit;
}

$quote = new IMQ_Quote($quote_id);
$quote_items = $quote->get_items();
?>
<html>

<head>
    <title><?php echo get_the_title($quote_id) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-size: 14px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            margin: 20px;
            color: #000;

        }

        .invoice {
            max-width: 800px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table td.amount {
            text-align: right;
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

        .table.item-table thead tr th {
            text-align: center;
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

        .button-group {
            text-align: right;
            margin-top: 2rem;
        }

        .button-group .go-back {
            text-decoration: none;
            display: inline-block;
            margin-right: 8px;
        }

        .button-group .btn-print {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin-top: 1rem;
            text-align: right;
        }

        .button-group.btn-print:hover {
            background-color: #45a049;
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

            .button-group {
                display: none;
            }
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
                <h1 style="font-weight: 900; font-size: 32px; color: #2C5988;">PROPOSAL</h1>
                <table>
                    <tr>
                        <td>Quote Number:</td>
                        <td> <?php _e($quote_id); ?></td>
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
                <?php
                $shipping = $quote->get_shipping();
                $business_name = $shipping->get_company();
                $full_name = $shipping->get_full_name();
                $address_1 = $shipping->get_address_1();
                $address_2 = $shipping->get_address_2();
                $state_address = $shipping->get_state_address();

                echo <<<HTML
                        <td>
                            $business_name
                            <br>
                                $full_name
                            <br>
                            $address_1
                            <br>
                            $address_2
                            <br>
                            $state_address
                        </td>
                    HTML;
                ?>
            </tr>
        </table>

        <?php
        /*
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
        */
        ?>

        <table class="table item-table">
            <thead>
                <tr>
                    <th>Quantity</th>
                    <th style="min-width: 120px;">Line Item ID</th>
                    <th>Description</th>
                    <th style="min-width: 100px;">Unit Price</th>
                    <th style="min-width: 100px;">Ext. Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quote_items as $item): ?>
                    <tr>
                        <td class="amount"><?php echo $item->get_product_qty(); ?></td>
                        <td><?php echo $item->get_line_item(); ?></td>
                        <td><?php echo $item->get_description(); ?></td>
                        <td class="amount"><?php echo number_format($item->get_quote_price(), 2); ?></td>
                        <td class="amount"><?php echo number_format($item->get_quote_price_total(), 2); ?></td>
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
                <th><?php echo number_format($quote->get_total(), 2); ?></th>
            </tr>
        </table>

        <dl>
            <dt>
                <h4 style="margin-bottom: 4px;">This proposal is subject to the conditions noted below:</h4>
            </dt>
            <dd>This proposal does not include freight.</dd>
            <dd>This proposal does not include installation.</dd>
            <?php
            $vendor_name = get_post_meta($quote->get_id(), '_vendor_name', true);
            $vendor_number = get_post_meta($quote->get_id(), '_vendor_number', true);

            if (!empty($vendor_name) && !empty($vendor_number)) {
                echo "<dd>$vendor_name\'s vendor number for Integrity Medical Products is $vendor_number</dd>";
            }
            ?>
        </dl>



        <div class="button-group no-print">
            <a href="javascript:history.back()" class="go-back"><span>&#8592;</span>Go Back</a>
            <button class="btn-print" id="print-quote-btn">Print Quote</button>
        </div>
    </div>
    <script>
        document.getElementById('print-quote-btn').addEventListener('click', function() {
            window.print();
        });
    </script>
</body>

</html>
