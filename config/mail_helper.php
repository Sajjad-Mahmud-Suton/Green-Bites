<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║   ██████╗ ██████╗ ███████╗███████╗███╗   ██╗    ██████╗ ██╗████████╗███████╗║
 * ║  ██╔════╝ ██╔══██╗██╔════╝██╔════╝████╗  ██║    ██╔══██╗██║╚══██╔══╝██╔════╝║
 * ║  ██║  ███╗██████╔╝█████╗  █████╗  ██╔██╗ ██║    ██████╔╝██║   ██║   █████╗  ║
 * ║  ██║   ██║██╔══██╗██╔══╝  ██╔══╝  ██║╚██╗██║    ██╔══██╗██║   ██║   ██╔══╝  ║
 * ║  ╚██████╔╝██║  ██║███████╗███████╗██║ ╚████║    ██████╔╝██║   ██║   ███████╗║
 * ║   ╚═════╝ ╚═╝  ╚═╝╚══════╝╚══════╝╚═╝  ╚═══╝    ╚═════╝ ╚═╝   ╚═╝   ╚══════╝║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: mail_helper.php                                                    ║
 * ║  PATH: /config/mail_helper.php                                            ║
 * ║  DESCRIPTION: Email sending helper functions using PHPMailer              ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  SECTIONS:                                                                ║
 * ║    1. Dependencies (PHPMailer, email config)                              ║
 * ║    2. sendEmail() - Generic email sending                                 ║
 * ║    3. sendPasswordResetEmail() - Password reset emails                    ║
 * ║    4. sendWelcomeEmail() - Welcome emails for new users                   ║
 * ║    5. sendOrderConfirmation() - Order confirmation emails                 ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  DEPENDENCIES:                                                            ║
 * ║    - PHPMailer (vendor/phpmailer/)                                        ║
 * ║    - email.php (SMTP configuration)                                       ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 1: DEPENDENCIES
   ═══════════════════════════════════════════════════════════════════════════ */

// Include PHPMailer classes
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';

// Include email configuration
require_once __DIR__ . '/email.php';


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 2: GENERIC EMAIL SENDING
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Send an email using PHPMailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string $textBody Plain text email body (optional)
 * @return array ['success' => bool, 'message' => string]
 */
function sendEmail($to, $subject, $htmlBody, $textBody = '') {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = MAIL_DEBUG;
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(MAIL_REPLY_TO, MAIL_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully.'];
    } catch (\Throwable $e) {
        return ['success' => false, 'message' => "Email could not be sent. Error: " . $e->getMessage()];
    }
}

/**
 * Send Password Reset Email
 * 
 * @param string $email User's email address
 * @param string $token Reset token
 * @param string $userName User's name (optional)
 * @return array ['success' => bool, 'message' => string]
 */
function sendPasswordResetEmail($email, $token, $userName = 'User') {
    $resetLink = SITE_URL . "/reset_password.php?token=" . urlencode($token);
    
    $subject = "Password Reset Request - Green Bites";
    
    $htmlBody = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f1f5f9;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 40px 20px;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); padding: 30px 40px; text-align: center;">
                                <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">
                                    🍃 Green Bites
                                </h1>
                                <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 14px;">
                                    Campus Canteen Management System
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Body -->
                        <tr>
                            <td style="padding: 40px;">
                                <h2 style="color: #1e293b; margin: 0 0 20px; font-size: 22px;">
                                    Password Reset Request
                                </h2>
                                
                                <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 20px;">
                                    Hello <strong>' . htmlspecialchars($userName) . '</strong>,
                                </p>
                                
                                <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 25px;">
                                    We received a request to reset your password for your Green Bites account. 
                                    Click the button below to create a new password:
                                </p>
                                
                                <!-- CTA Button -->
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center" style="padding: 10px 0 30px;">
                                            <a href="' . $resetLink . '" 
                                               style="display: inline-block; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); 
                                                      color: #ffffff; text-decoration: none; padding: 14px 40px; 
                                                      border-radius: 50px; font-weight: 600; font-size: 16px;
                                                      box-shadow: 0 4px 15px rgba(34, 197, 94, 0.4);">
                                                🔐 Reset My Password
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 0 0 15px;">
                                    <strong>⏰ This link will expire in 1 hour</strong> for security reasons.
                                </p>
                                
                                <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 0 0 20px;">
                                    If the button doesn\'t work, copy and paste this link into your browser:
                                </p>
                                
                                <p style="background-color: #f1f5f9; padding: 12px 16px; border-radius: 8px; 
                                          word-break: break-all; font-size: 13px; color: #475569; margin: 0 0 25px;">
                                    ' . $resetLink . '
                                </p>
                                
                                <div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 10px;">
                                    <p style="color: #94a3b8; font-size: 13px; line-height: 1.5; margin: 0;">
                                        ⚠️ If you didn\'t request this password reset, please ignore this email 
                                        or contact us if you have concerns about your account security.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #1e293b; padding: 25px 40px; text-align: center;">
                                <p style="color: #94a3b8; font-size: 13px; margin: 0 0 8px;">
                                    © ' . date('Y') . ' Green Bites - Campus Canteen
                                </p>
                                <p style="color: #64748b; font-size: 12px; margin: 0;">
                                    This is an automated message. Please do not reply to this email.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    
    $textBody = "
    Green Bites - Password Reset Request
    =====================================
    
    Hello $userName,
    
    We received a request to reset your password for your Green Bites account.
    
    Click the link below to reset your password:
    $resetLink
    
    This link will expire in 1 hour for security reasons.
    
    If you didn't request this password reset, please ignore this email.
    
    ---
    Green Bites - Campus Canteen
    ";
    
    return sendEmail($email, $subject, $htmlBody, $textBody);
}


/**
 * Send OTP Verification Email
 * 
 * @param string $email User's email address
 * @param string $otp 6-digit OTP code
 * @param string $userName User's name
 * @return array ['success' => bool, 'message' => string]
 */
function sendOTPEmail($email, $otp, $userName = 'User') {
    $subject = "Your Verification Code - Green Bites";
    
    $htmlBody = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f1f5f9;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 40px 20px;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); padding: 30px 40px; text-align: center;">
                                <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">
                                    🍃 Green Bites
                                </h1>
                                <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 14px;">
                                    Campus Canteen Management System
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Body -->
                        <tr>
                            <td style="padding: 40px;">
                                <h2 style="color: #1e293b; margin: 0 0 20px; font-size: 22px; text-align: center;">
                                    ✉️ Email Verification
                                </h2>
                                
                                <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 20px; text-align: center;">
                                    Hello <strong>' . htmlspecialchars($userName) . '</strong>,
                                </p>
                                
                                <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 30px; text-align: center;">
                                    Use the verification code below to complete your signup:
                                </p>
                                
                                <!-- OTP Code Box -->
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center" style="padding: 20px 0 30px;">
                                            <div style="display: inline-block; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); 
                                                        padding: 20px 50px; border-radius: 16px; box-shadow: 0 8px 25px rgba(34, 197, 94, 0.35);">
                                                <span style="font-size: 36px; font-weight: 700; color: #ffffff; letter-spacing: 12px; font-family: monospace;">
                                                    ' . $otp . '
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 0 0 15px; text-align: center;">
                                    <strong>⏰ This code will expire in 2 minutes</strong>
                                </p>
                                
                                <div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px;">
                                    <p style="color: #94a3b8; font-size: 13px; line-height: 1.5; margin: 0; text-align: center;">
                                        ⚠️ If you didn\'t request this code, please ignore this email.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #1e293b; padding: 25px 40px; text-align: center;">
                                <p style="color: #94a3b8; font-size: 13px; margin: 0 0 8px;">
                                    © ' . date('Y') . ' Green Bites - Campus Canteen
                                </p>
                                <p style="color: #64748b; font-size: 12px; margin: 0;">
                                    This is an automated message. Please do not reply to this email.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    
    $textBody = "
    Green Bites - Email Verification
    =================================
    
    Hello $userName,
    
    Your verification code is: $otp
    
    This code will expire in 2 minutes.
    
    If you didn't request this code, please ignore this email.
    
    ---
    Green Bites - Campus Canteen
    ";
    
    return sendEmail($email, $subject, $htmlBody, $textBody);
}
