<?php
defined('ABSPATH') || exit;
/**
 * Generates an HTML email with OTP details.
 *
 * This function takes an OTP and an admin email address as arguments and returns
 * an HTML string containing the OTP and the admin email address. The HTML
 * string is formatted to be a verification email that is easy to read and
 * understand.
 *
 * @param string $otp The One-Time Password (OTP) to be sent in the email.
 * @param string $admin_email The email address of the admin user.
 *
 * @return string The HTML string containing the OTP and the admin email address.
 */
function imq_get_opt_email($otp, $admin_email)
{
    ob_start();
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>OTP Verification</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }

            .email-container {
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .email-header {
                text-align: center;
                padding-bottom: 20px;
                border-bottom: 1px solid #eee;
            }

            .brand-logo {
                max-width: 120px;
            }

            .email-body {
                padding: 20px;
            }

            .otp-container {
                text-align: center;
                background-color: #f2f2f2;
                padding: 20px;
                margin-top: 20px;
                border-radius: 8px;
            }

            .otp {
                font-size: 24px;
                font-weight: bold;
                color: #333333;
                background-color: #ffffff;
                padding: 10px 20px;
                border-radius: 6px;
            }

            .footer {
                text-align: center;
                margin-top: 30px;
                font-size: 12px;
                color: #777777;
            }

            a {
                color: #1a73e8;
                text-decoration: none;
            }
        </style>
    </head>

    <body>

        <div class="email-container">
            <div class="email-header">
                <img src="<?php echo IMQ_PLUGIN_DIR_URL . '/assets/integritymp-logo.png' ?>" alt="IntegrityMP" class="brand-logo">
                <h2>OTP Verification</h2>
            </div>

            <div class="email-body">
                <p>Hi there,</p>
                <p>We received a request to verify your email address. Use the One-Time Password (OTP) below to complete your verification.</p>

                <div class="otp-container">
                    <p>Your OTP:</p>
                    <div class="otp"><?php echo $otp ?></div>
                </div>

                <p>This OTP will expire in 5 minutes. If you didn’t request this verification, please ignore this email.</p>
                <p>If you need further assistance, feel free to contact us at <a href="mailto:<?php echo $admin_email ?>"><?php echo $admin_email ?></a>.</p>
            </div>

            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. All Rights Reserved.</p>
                <p><a href="<?php echo site_url() ?>">Visit our website</a></p>
            </div>
        </div>

    </body>

    </html>

<?php
    return ob_get_clean();
}
