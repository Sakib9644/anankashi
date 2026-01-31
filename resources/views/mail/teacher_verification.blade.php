Hello {{ $user->username }},

Thank you for registering with {{ config('app.name') }}.

To complete your registration, please verify your email address by visiting this link:
{{ $verificationUrl }}

This link expires in 24 hours for security reasons.

If you didn't sign up for an account, you can safely ignore this email.

Best regards,
The {{ config('app.name') }} Team

{{ config('app.name') }}

Automated message — do not reply.
