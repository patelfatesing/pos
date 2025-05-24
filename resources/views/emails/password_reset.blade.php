<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .header {
            font-size: 24px;
            color: #4B7BEC;
            margin-bottom: 20px;
        }
        .info {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">Account Details</div>

        <p class="info">Hello,</p>

         <p class="info">Your account password has been changed. Use the new credentials below to log in:</p>

        <p class="info"><span class="label">Email:</span> {{ $email }}</p>
        <p class="info"><span class="label">Password:</span> {{ $newPassword }}</p>

        <p class="info">For your security, please log in and update your password as soon as possible.</p>

        <div class="footer">
            Thanks,<br>
            liquorhub
        </div>
    </div>
</body>
</html>
