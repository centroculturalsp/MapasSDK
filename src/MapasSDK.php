<?php

namespace MapasSDK;

use Curl\Curl;
use JWT;

class MapasSDK {

    protected $_apiUrl;
    protected $_pubKey;
    protected $_priKey;
    protected $_algo;

    public function __construct($mapasInstanceUrl, $pubKey, $priKey, $algo = 'HS512') {
        $this->_mapasInstanceUrl = $mapasInstanceUrl;
        $this->_pubKey = $pubKey;
        $this->_priKey = $priKey;
        $this->_algo = $algo;
    }

    public function apiRequest($method, $url, array $data = [], $headers = []) {
        $curl = new Curl;

        $jwt = JWT::encode([
                'tm' => microtime(true),
                'pk' => $this->_pubKey
            ], $this->_priKey, $this->_algo     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );


        $curl->setHeader('authorization', $jwt);
        $curl->setHeader('MapasSDK-REQUEST', 'true');

        foreach ($headers as $k => $v) {
            $curl->setHeader($k, $v);
        }

        $curl->$method($this->_mapasInstanceUrl . $url, $data);
        
        $curl->close();
        
        $responseObject = json_decode($curl->response);
        
        if(json_last_error() === JSON_ERROR_NONE){
            $curl->response = $responseObject;
        }
        
        return $curl;
    }

    public function apiGet($url, array $data = [], $headers = []) {
        return $this->apiRequest('get', $url, $data, $headers);
    }

    public function apiPost($url, array $data = [], $headers = []) {
        return $this->apiRequest('post', $url, $data, $headers);
    }

    public function apiPut($url, array $data = [], $headers = []) {
        return $this->apiRequest('put', $url, $data, $headers);
    }

    public function apiPatch($url, array $data = [], $headers = []) {
        return $this->apiRequest('patch', $url, $data, $headers);
    }

    public function apiDelete($url, array $data = [], $headers = []) {
        return $this->apiRequest('delete', $url, $data, $headers);
    }

    public function getEntity($entityType, $id, $fields) {
        return $this->apiGet("api/{$entityType}/findOne", [
            'id' => "EQ({$id})",
            '@select' => $fields
        ]);
    }

    public function createEntity($entityType, array $data) {
        return $this->apiPost("{$entityType}/index", $data);
    }

    public function updateEntity($entityType, $id, array $data) {
        return $this->apiPut("$entityType/single/{$id}", $data);
    }

    public function patchEntity($entityType, $id, array $data) {
        return $this->apiPatch("$entityType/single/{$id}", $data);
    }

    public function deleteEntity($entityType, $id) {
        return $this->apiDelete("{$entityType}/single/{$id}");
    }

}
