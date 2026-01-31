<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f4f4; line-height:1.6; color:#333333;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f4f4f4;">
        <tr>
            <td align="center" style="padding:30px 10px;">
                <table role="presentation" width="600" border="0" cellspacing="0" cellpadding="0" style="max-width:600px; background-color:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #dddddd;">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding:30px 40px 20px; background-color:#2c3e50; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px; font-weight:bold;">{{ config('app.name') }}</h1>
                            <div style="height:4px; width:60px; background-color:#4a90e2; margin:15px auto 0;"></div>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding:40px 40px 30px;">
                            <p style="font-size:18px; margin:0 0 20px;">Hello {{ $user->username }},</p>

                            <p style="margin:0 0 20px;">Thank you for registering with {{ config('app.name') }}. To complete your registration and unlock full access, please verify your email address by clicking the button below.</p>

                            <table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center" style="margin:30px auto;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $verificationUrl }}"
                                           style="background-color:#4a90e2; color:#ffffff; padding:16px 36px; border-radius:6px; font-weight:600; font-size:16px; text-decoration:none; display:inline-block;">
                                            Verify My Email Address
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 20px; font-size:15px; color:#555555;">This verification link expires in <strong>24 hours</strong> for your security. If the button doesn't work, copy and paste this link into your browser:</p>
                            <p style="margin:0 0 30px; font-size:14px; word-break:break-all; color:#0066cc;">{{ $verificationUrl }}</p>

                            <p style="margin:0 0 20px; font-size:15px; color:#555555;">If you didn't create an account with us, please ignore this email — no action is needed.</p>

                            <p style="margin:30px 0 0; font-size:16px;">Best regards,<br>The {{ config('app.name') }} Team</p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 40px; background-color:#f8f8f8; font-size:13px; color:#777777; text-align:center; border-top:1px solid #dddddd;">
                            <p style="margin:0 0 10px;">{{ config('app.name') }} • Education Platform</p>
                            <p style="margin:0;">Your Real Address Here, Dhaka, Bangladesh (update to your actual physical address)</p>
                            <p style="margin:10px 0 0;">This is an automated security message. Do not reply directly. For help, contact support@yourdomain.com</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
