<?php

require 'MapasSDK.php';
require '../vendor/autoload.php';

$mapas = new MapasSDK\MapasSDK('http://localhost/', 'f738a2db0b3f5d94853b9e61cd73b4162b80fd5c565ac088bea54c6995e2a358', '045c2ae5fb5488ed6687789a2f7693dc9a6e4ac1a05aed3631c0e38f329d200598ba0fcab9c14f73e5ff1d2a06d3fbeea4caf4d61c7331e74a5595d4b5c6d999');


$newAgent = $mapas->post('agent/index', [
    'type' => '2',
    'name' => 'Fulano',
    'shortDescription' => 'Oi',
    'terms' => [
        'area' => [
            'Arqueologia'
        ]
    ],
    'location' => [
        '-46.685684400000014',
        '-23.5404024'
    ],
    'endereco' => 'Rua Capital Federal'
]);

var_dump($newAgent);

$agents = $mapas->get('api/agent/find/', [
    '@select' => 'id,name,documento,emailPrivado',
    'user' => 'EQ(@User:1)'
]);

var_dump($agents);