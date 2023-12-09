<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
</head>
<body>
<img src="{{ $logo }}"/>
<h1>{{ $empresa }}</h1></body>
<p>Olá cliente {{ $boleto['pagador']['nome'] }}</p>
</html>
