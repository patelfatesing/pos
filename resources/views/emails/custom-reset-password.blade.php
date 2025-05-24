<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Password Reset Request</title>
    <style>
        /* Reset some defaults */
        body, p, div {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2c3e50;
        }
        body {
            background-color: #f4f6f8;
            padding: 30px 0;
        }
        .email-wrapper {
            max-width: 600px;
            background: #ffffff;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid #e1e4e8;
        }
        .email-header {
            background-color: #34495e;
            color: #ecf0f1;
            padding: 20px 30px;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
        }
        .email-body {
            padding: 30px;
            font-size: 16px;
            line-height: 1.5;
        }
        .btn-reset {
            display: inline-block;
            background-color: #1abc9c;
            color: #ffffff !important;
            padding: 14px 28px;
            text-decoration: none;
            font-weight: 600;
            border-radius: 6px;
            margin: 25px 0;
        }
        .email-footer {
            font-size: 12px;
            color: #95a5a6;
            text-align: center;
            padding: 20px 30px;
            border-top: 1px solid #e1e4e8;
        }
        a {
            color: #1abc9c;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            Hello {{ $user->name }},
        </div>
        <div class="email-body">
            <p>You requested a password reset for your account. Click the button below to reset your password:</p>

            <p style="text-align:center;">
                <a href="{{ $url }}" class="btn-reset" target="_blank" rel="noopener">Reset Password</a>
            </p>

            <p>If you did not request a password reset, please ignore this email. No further action is required.</p>

            <p>Thanks,<br>liquorhub</p>
        </div>
        <div class="email-footer">
            If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
            <a href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}</a>
        </div>
    </div>
</body>
</html>
