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
<tr>
    <td>عزيزي  {{ $name  }} !</td>
</tr>
<tr>
    <td> &nbsp;</td>
</tr>
<tr>
    <td>  من فضلك فعل الحساب الخاص بك من خلال الرابط :- </td>
</tr>
<tr>
    <td><a href="{{url('user/confirm/'.$code)}}"> {{url('user/confirm/'.$code)}} </a></td>
</tr>
<tr>
    <td> &nbsp;</td>
</tr>
<tr>
    <td>  شكرا لك علي التسجيل </td>
</tr>
<tr>
    <td>  Khamsat  </td>
</tr>

</body>
</html>
