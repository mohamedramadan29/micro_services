<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> نسيت كلمة المرور </title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            text-align: right;
            direction: rtl
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .email-header {
            background-color: #4CAF50;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 24px;
        }

        .email-body {
            padding: 20px;
            line-height: 1.6;
            color: #333333;
        }

        .email-body h2 {
            color: #4CAF50;
            font-size: 22px;
            margin-bottom: 10px;
        }

        .email-body p {
            margin-bottom: 20px;
            font-size: 16px;
        }

        .email-body a {
            display: inline-block;
            background-color: #4CAF50;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 10px;
        }

        .email-body a:hover {
            background-color: #45a049;
        }

        .email-footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #666666;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            نسيت كلمة المرور
        </div>
        <div class="email-body">
            <h2>مرحبًا !</h2>
            <p> يمكنك تغير كلمة المرور الخاصة بك من خلال الرابط التالي :- </p>
            <p>
                <a href="{{ url('user/change-forget-password/' . $code) }}" target="_blank"> تغير كلمة المرور </a>
            </p>
            <p>إذا كنت تواجه أي مشكلة، يُرجى نسخ الرابط أدناه ولصقه في متصفحك:</p>
            <p>{{ url('user/change-forget-password/' . $code) }}</p>
            <p>شكرًا لانضمامك إلينا!</p>
        </div>
        <div class="email-footer">
            &copy; 2024 نفذها - جميع الحقوق محفوظة
        </div>
    </div>
</body>

</html>
