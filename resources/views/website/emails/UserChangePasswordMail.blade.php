<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

<h2>  مرحبا </h2>
<br>
<p>  يمكنك تغير كلمة المرور الخاصة بك من خلال الرابط التالي  :- </p>
<br>
<p><a href="{{url('user/change-forget-password/'.$code)}}"> {{url('user/change-forget-password/'.$code)}} </a></p>
<br>
<p> شكرا لك علي التسجيل </p>

</body>
</html>
