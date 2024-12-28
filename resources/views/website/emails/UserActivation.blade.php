<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>تفعيل الحساب</title>
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
            تفعيل الحساب الخاص بك
        </div>
        <div class="email-body">
            <h2>مرحبًا {{ $name }}!</h2>
            <p>شكرًا لتسجيلك في نفذها. من فضلك قم بتفعيل الحساب الخاص بك عن طريق الضغط على الرابط أدناه:</p>
            <p>
                <a href="{{ url('user/confirm/' . $code) }}" target="_blank">تفعيل الحساب</a>
            </p>
            <p>إذا كنت تواجه أي مشكلة، يُرجى نسخ الرابط أدناه ولصقه في متصفحك:</p>
            <p>{{ url('user/confirm/' . $code) }}</p>
            <p>شكرًا لانضمامك إلينا!</p>
        </div>
        <div class="email-footer">
            &copy; 2024 نفذها - جميع الحقوق محفوظة
        </div>
    </div>
</body>

</html>
