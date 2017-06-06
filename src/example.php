<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$mapas = new MapasSDK\MapasSDK(
    'http://mapas.local/',
    'chave publica',
    'chave privada',
    'HS256'
);

//
$newAgent = $mapas->createEntity('agent', [
    'type' => '2',
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

print_r($newAgent);
//var_dump($newAgent);
//var_dump($newAgent->response);


//$agents = $mapas->apiGet('api/agent/find', [
//    '@select' => 'id,name,documento,emailPrivado',
//    'user' => 'EQ(@User:1)'
//]);
//
//var_dump($agents);

//
//print_r($mapas->getEntityDescription('agent'));
//print_r($mapas->getEntityTypes('space'));

//print_r($mapas->findEntity('agent', 83, 'id,name,location'));

//print_r($mapas->createEntity('agent', []));
