<?php

require 'autoload.php';
$beneficiario = new Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'ACME',
    'endereco'  => 'Rua um, 123',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '99.999.999/9999-99',
]);

$pagador = new Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'Cliente',
    'endereco'  => 'Rua um, 123',
    'bairro'    => 'Bairro',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '999.999.999-99',
]);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Itau([
    'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '341.png',
    'dataVencimento'         => new Carbon\Carbon(),
    'valor'                  => 100,
    'multa'                  => false,
    'juros'                  => false,
    'numero'                 => 1,
    'numeroDocumento'        => 1,
    'pagador'                => $pagador,
    'beneficiario'           => $beneficiario,
    'carteira'               => 112,
    'agencia'                => 1111,
    'conta'                  => 99999,
    'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
    'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    'aceite'                 => 'S',
    'especieDoc'             => 'DM',
    'pix_qrcode'             => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAR0AAAEdAQMAAAALpCE4AAAABlBMVEX///8AAABVwtN+AAADYElEQVRoge2Zy7GrQAxERbFgSQiTiUmMKqhyYiYTQmDJgkKvu8XHdgR6tzwLrvGcu5BHn5bG7Ld+68+v1t13q+e1c+djs+aFr1drn9jxMS+EP492M3+BtIf1+NRuxTp/zrGbFXr6yq3VCOF1Xh98VIB8Tw8Nc+O+lWbHvk9L7Wu11P8BFL6jw4BT0cSGp5Qboj9ZX5pXOxaGhDfTYgUW119Olw1iBF9e9PH4CvNkUCzE7Y4wpk3YoXXVYt8rFyRLGAiTj0WJE6eEVcGwi8wImRWrlmFmDMCVEA2KYOuWAY/rWBJCNSOYWyhQMKe3xgkxEFSvskIsUNPSM13iWCo6/wRjtQ88L8QC1eGzciY+wVi+UiEUv36ChBBs4tnMsA7+dKR4x4N59PoJUkKmXMPUU5yuJGmzMoybtyqVEbJK5wCyOVypp47s+Wp5IYjdnU71OOw0FS2dzW1dQoiph3G7Kkkq9cg6Jk5qhbRQVKme+Yc9BurVIROwd0dwRkh5XpWVyd6ZQlFjw58yQyYHcqr0YdYBqVdCoWXLsWSGEKj0J2pG6QJkSkGj3U6XEAqJ6xQ0G2sTrLMQZv4mkDNC0Rf5ame3wZAopkx0OV1CCJbgRDi+ONIlPYtt0hnGWSFEcFjHFrqhddTr1GQjM1FiCIfh/nTpghgdCVLj96Z68kFs+0HCsIHWGSO41ilBQt5VKh2ELWN/p5ypPC8V7CIrTw2pJ22YZkIcMAYUwZcSywixTXKJF751riHSyBr79I/+LiM0LSG9ZrVJaKYRy2fPlxayISqrRqE0h5MBNR8aHSWGJHE3k+yN/+HZOJXYNadLCfXR8TNu9Vp4LN5IFeyZIc3kQqrH+MLVbewSZreETAdxAMACJeHIZtqVQjVEstOfUkLUBTBR37o8SxZzw85knxGKIxiLHdKL07n2GBjdbVJGaKBKr1lUSeJV2pejxvJWyvJBrYbPgxSvKWylgs9rgTEtFORxRXFo36U+a+znFUAqKG5x2RfF6CjkuzI++6cxLzTGDdeqay5aRzkW6vEWyBkhSS9kHc5w1VEfXd2H02WFNKyj7OWr9Feos7tNSgoxjLuFZ8N92URhdimxlJD8qY5pi6b/xxiaF/4vs7xQuE18q8lFXFtoYPQmHtJBv/Vbf3r9A2A129WHoHA0AAAAAElFTkSuQmCC',
]);

$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
$pdf->addBoleto($boleto);
echo $pdf->gerarBoleto();
