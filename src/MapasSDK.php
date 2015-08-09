<?php

namespace MapasSDK;

use Curl\Curl,
    JWT;

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

    public function request($method, $url, array $data = [], $headers = []) {
        $curl = new Curl;

        $jwt = JWT::encode(
            [
                'tm' => microtime(true),
                'pk' => $this->_pubKey
            ], $this->_priKey, $this->_algo     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );


        $curl->setHeader('authorization', $jwt);
        $curl->setHeader('X_REQUESTED_WITH', 'XMLHttpRequest');

        foreach ($headers as $k => $v) {
            $curl->setHeader($k, $v);
        }

        $curl->$method($this->_mapasInstanceUrl . $url, $data);
        $curl->close();
        return $curl->response;
    }

    public function get($url, array $data = [], $headers = []) {
        return $this->request('get', $url, $data, $headers);
    }

    public function post($url, array $data = [], $headers = []) {
        return $this->request('post', $url, $data, $headers);
    }

    public function put($url, array $data = [], $headers = []) {
        return $this->request('put', $url, $data, $headers);
    }

    public function patch($url, array $data = [], $headers = []) {
        return $this->request('patch', $url, $data, $headers);
    }

    public function delete($url, array $data = [], $headers = []) {
        return $this->request('delete', $url, $data, $headers);
    }

}
