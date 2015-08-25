<?php

require 'MapasSDK.php';
require '../vendor/autoload.php';

$mapas = new MapasSDK\MapasSDK(
    'http://mapas.local/', 
    '7lPnTbPby84S10gA4TDwqVuBACNBEkdF', 
    'kFDSzQdThMpzG3x4WoVY2GDOaH1fze0H0oBBZmtquvC1sI6R32xJaSYLvZO2sPPL'
);


$newAgent = $mapas->createEntity('agent', [
    'type' => '21',
    'name' => 'Fulano ' . date('Y/m/d H:i:s'),
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
var_dump($newAgent->response);


$agents = $mapas->apiGet('api/agent/find/', [
    '@select' => 'id,name,documento,emailPrivado',
    'user' => 'EQ(@User:1)'
]);

var_dump($agents);