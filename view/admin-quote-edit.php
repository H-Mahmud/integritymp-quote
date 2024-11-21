<?php
defined('ABSPATH') || exit;

if (!current_user_can('manage_options'))
    wp_die('You do not have sufficient permissions to access this page.');


if (!isset($_GET['id']) || empty($_GET['id']) || get_post($_GET['id'])->post_type !== 'shop_quote')
    wp_die('You attempted to edit a quote that does not exist. Perhaps it was deleted?');

$quote_id = $_GET['id'];
$quote = new IMQ_Quote($quote_id);
$quote_items = $quote->get_items();
?>

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
        /* max-width: 800px; */
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

    .table.quote-to {
        max-width: 300px;
    }

    .table.quote-to td {
        border-left: 0;
        vertical-align: text-top;
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

        .wp-toolbar,
        #wpcontent {
            margin: 0 !important;
            padding: 0 !important;
        }

        #adminmenuback,
        #adminmenuwrap,
        #wpadminbar,
        #wpfooter,
        .button-group {
            display: none !important;
        }
    }
</style>


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

    <?php
    $shipping = $quote->get_shipping();
    $business_name = $shipping->get_company();
    $full_name = $shipping->get_full_name();
    $address_1 = $shipping->get_address_1();
    $address_2 = $shipping->get_address_2();
    $state_address = $shipping->get_state_address();


    $user_profile_url = admin_url('user-edit.php?user_id=' . $quote->get_customer_id());
    $profile_url = esc_url($user_profile_url);
    $price_level =  $quote->get_price_level_label();
    $tax_exempt = $quote->get_tax_exempt();

    echo <<<HTML
    <table class="table quote-to">
        <thead>
            <tr>
                <th colspan="2">Quote To:</th>
            </tr>
        </thead>
        <tr>
            <td>Customer:</td>
            <td><a href="$profile_url" target="_blank">$full_name</a></td>
        </tr>
        <tr>
            <td>Business Name:</td>
            <td>$business_name</td>
        </tr>
        <tr>
            <td>Price Level:</td>
            <td>$price_level</td>
        </tr>
        <tr>
            <td>Tax Exempt:</td>
            <td>$tax_exempt</td>
        </tr>
        <tr>
            <td>Address:</td>
            <td>
                $address_1
                <br>
                $address_2
                <br>
                $state_address
            </td>
        </tr>

    </table>
HTML;
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



    <!-- <div class="button-group no-print">
        <button class="button button-primary" id="print-quote-btn">Print Quote</button>
    </div> -->
</div>
<script>
    document.getElementById('print-quote-btn').addEventListener('click', function() {
        window.print();
    });
</script>
