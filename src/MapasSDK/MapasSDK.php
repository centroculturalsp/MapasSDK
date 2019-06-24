<?php

namespace MapasSDK;

use Curl\Curl;
use \Firebase\JWT\JWT;


function EQ($value) {
    return 'EQ(' . $value . ')';
}

function IN(array $values){
    return "IN(" . implode(',', $values) . ')';
}

// @TODO: implementar as funções dos outros operadores

class MapasSDK {

    protected $_apiUrl;
    protected $_pubKey;
    protected $_priKey;
    protected $_algo;

    public $debugRequest = false;
    public $debugResponse = false;

    /**
     * As requisições por GET que "casarem" com os padrões definidos nessa propriedade serão cacheadas
     *
     * @var array padrões
     */
    public $cachePatterns = [
        'api/[^/]+/describe',
        'api/[^/]+/getTypes'
    ];

    /**
     * Opções para a requisição curl
     *
     * @see http://php.net/manual/pt_BR/function.curl-setopt.php
     * @var array
     */
    public $curlOptions = [
        CURLOPT_SSL_VERIFYPEER => false
    ];

    /**
     * Instancia o SDK
     *
     * @param string $instanceUrl Url da instalação do Mapas Culturais
     * @param string $pubKey Chave pública da aplicação no Mapas Culturais
     * @param string $priKey Chave privada da aplicação no Mapas Culturais
     * @param string $algo Algorítimo usado para encriptar o JWT
     */
    public function __construct($instanceUrl, $pubKey, $priKey, $algo = 'HS512') {
        $this->_mapasInstanceUrl = $instanceUrl;
        $this->_pubKey = $pubKey;
        $this->_priKey = $priKey;
        $this->_algo = $algo;
    }

        /**
     * Executa um request para a instância do Mapas Culturais
     *
     * @param string $method Método do request (GET|POST|PATCH|PUT|DELETE)
     * @param string $targetPath Caminho do destino da requisição (exemplo: <b>api/agent/find</b> ou <b>space/single/99<b>)
     * @param array $data Dados a serem enviados na requisição
     * @param array $headers Headers adicionais a serem enviados na requisição
     * @param array $curlOptions Opções adicionais para o curl
     *
     * @return Curl
     *
     * @throws Exceptions\BadRequest
     * @throws Exceptions\Unauthorized
     * @throws Exceptions\Forbidden
     * @throws Exceptions\NotFound
     * @throws Exceptions\UnexpectedError
     */
    public function apiRequest($method, $targetPath, array $data = [], array $headers = [], array $curlOptions = []) {
        $curl = new Curl;

        $this->_debugRequest($method, $targetPath, $data, $headers, $curlOptions);

        foreach ($this->curlOptions as $option => $value) {
            $curl->setOpt($option, $value);
        }

        foreach ($curlOptions as $option => $value) {
            $curl->setOpt($option, $value);
        }

        $jwt = JWT::encode([
                    'tm' => (string) microtime(true),
                    'pk' => $this->_pubKey
                ], $this->_priKey, $this->_algo     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );
        
        $curl->setHeader('authorization', $jwt);
        $curl->setHeader('MapasSDK-REQUEST', 'true');

        foreach ($headers as $k => $v) {
            $curl->setHeader($k, $v);
        }

        if(strtolower($method) === 'patch'){
            $curl->$method($this->_mapasInstanceUrl . $targetPath, $data, true);
        } else {
            $curl->$method($this->_mapasInstanceUrl . $targetPath, $data);
        }
        $curl->close();

        $responseObject = json_decode($curl->response);

        if (json_last_error() === JSON_ERROR_NONE) {
            $curl->response = $responseObject;
        }

        if ($curl->error) {
            switch ($curl->http_status_code) {
                case 400:
                    throw new Exceptions\BadRequest($curl);

                case 401:
                    throw new Exceptions\Unauthorized($curl);

                case 403:
                    throw new Exceptions\Forbidden($curl);

                case 404:
                    throw new Exceptions\NotFound($curl);

                default:
                    throw new Exceptions\UnexpectedError($curl);
            }
        }
        $curl->data = $data;

        $this->_debugResponse($curl);

        return $curl;
    }

    protected function _debugRequest($method, $targetPath, $data, $headers, $curlOptions){
        if($this->debugRequest){
            echo "\n------------=======================MapasSDK=========================------------\n" .
                    strtoupper($method) . " {$targetPath } \n\n";

            $debug = [];

            if($headers){ $debug['headers'] = $headers; }
            if($curlOptions){ $debug['curl options'] = $curlOptions; }
            if($data){ $debug['data'] = $data; }

            if($debug) {
                print_r($debug);
            }
        }
    }

    protected function _debugResponse($curl){
        if($this->debugResponse){
            if($this->debug){
                echo "\n\nCURL OBJECT:\n\n";
                print_r($curl);
                echo "\n\n================================================================================\n";
            }
        }
    }

    /**
     * Faz uma requisição por GET
     *
     * @param string $targetPath
     * @param array $data
     * @param array $headers
     * @param type $curlOptions
     *
     * @return Curl
     */
    public function apiGet($targetPath, array $data = [], array $headers = [], array $curlOptions = []) {
        return $this->apiRequest('get', $targetPath, $data, $headers, $curlOptions);
    }

    /**
     * Faz uma requisição por POST
     *
     * @param string $targetPath
     * @param array $data
     * @param array $headers
     * @param type $curlOptions
     *
     * @return Curl
     */
    public function apiPost($targetPath, array $data = [], array $headers = [], array $curlOptions = []) {
        return $this->apiRequest('post', $targetPath, $data, $headers, $curlOptions);
    }

    /**
     * Faz uma requisição por PUT
     *
     * @param string $targetPath
     * @param array $data
     * @param array $headers
     * @param type $curlOptions
     *
     * @return Curl
     */
    public function apiPut($targetPath, array $data = [], array $headers = [], array $curlOptions = []) {
        return $this->apiRequest('put', $targetPath, $data, $headers, $curlOptions);
    }

    /**
     * Faz uma requisição por PATCH
     *
     * @param string $targetPath
     * @param array $data
     * @param array $headers
     * @param type $curlOptions
     *
     * @return Curl
     */
    public function apiPatch($targetPath, array $data = [], array $headers = [], array $curlOptions = []) {
        return $this->apiRequest('patch', $targetPath, $data, $headers, $curlOptions);
    }

    /**
     * Faz uma requisição por DELETE
     *
     * @param string $targetPath
     * @param array $data
     * @param array $headers
     * @param type $curlOptions
     *
     * @return Curl
     */
    public function apiDelete($targetPath, array $data = [], array $headers = [], array $curlOptions = []) {
        return $this->apiRequest('delete', $targetPath, $data, $headers, $curlOptions);
    }

    /**
     * Cria uma nova entidade do tipo informado com os dados fornecidos e retorna a entidade criada.
     *
     * @param string $type Tipo da entidade (agent|space|project|event|etc)
     * @param array $data
     *
     * @return object
     *
     * @throws Exceptions\ValidationError
     */
    public function createEntity($type, array $data) {
        $curl = $this->apiPost("{$type}/index", $data);

        if(isset($curl->response->error) && $curl->response->error){
            throw new Exceptions\ValidationError($curl);
        }

        return $curl->response;
    }

    /**
     * Sobrescreve os dados da entidade com o id informado pelos dados fornecidos e retorna a entidade modificada.
     *
     * <b>ATENÇÃO: TODOS OS DADOS DEVEM SER ENVIADOS.</b>
     *
     * Para atualizar somente os dados enviados, sem modificar os não enviados, utilizar a função patchEntity.
     *
     *
     * @param string $type Tipo da entidade (agent|space|project|event|etc)
     * @param int $id Id da entidade a ser atualizada
     * @param array $data
     *
     * @return object
     */
    public function updateEntity($type, $id, array $data) {
        $curl = $this->apiPut("$type/single/{$id}", $data);

        if(isset($curl->response->error) && $curl->response->error){
            throw new Exceptions\ValidationError($curl);
        }

        return $curl->response;
    }

    /**
     * Atualza os dados fornecidos da entidade com o id informado e retorna a entidade modificada.
     *
     * @param string $type Tipo da entidade (agent|space|project|event|etc)
     * @param int $id Id da entidade a ser atualizada
     * @param array $data
     * @return object
     *
     * @throws Exceptions\ValidationError
     */
    public function patchEntity($type, $id, array $data) {
        $curl = $this->apiPatch("$type/single/{$id}", $data);

        if(isset($curl->response->error) && $curl->response->error){
            throw new Exceptions\ValidationError($curl);
        }

        return $curl->response;
    }

    /**
     * Deleta a entidade com o id informado.
     *
     * @param string $type Tipo da entidade (agent|space|project|event|etc)
     * @param int $id Id da entidade a ser deletada
     *
     * @return boolean true
     */
    public function deleteEntity($type, $id) {
        $curl = $this->apiDelete("{$type}/single/{$id}");

        return true;
    }

    /**
     * Retorna a descrição da entidade
     *
     * @param string $type Tipo da entidade (agent|space|project|event|etc)
     *
     * @return object
     */
    public function getEntityDescription($type) {
        $curl = $this->apiGet("api/{$type}/describe");

        return $curl->response;
    }

    /**
     * Retorna os tipos disponíveis para a entidade
     *
     * @param string $type Tipo da entidade (agent|space|project|event|etc)
     *
     * @return object
     */
    public function getEntityTypes($type) {
        $curl = $this->apiGet("api/{$type}/getTypes");

        return $curl->response;
    }

    /**
     * Retorna os ids das entidades filhas
     *
     * @param int $type Tipo da entidade (agent|space|project)
     * @param int $id Id da entidade
     * @param boolean $$include_parent_project_id incluir o id do projeto pai? (default false)
     *
     * @return int[]
     */

    public function getChildrenIds($type, $id, $include_parent_project_id = false) {
        $curl = $this->apiGet("api/{$type}/getChildrenIds/{$id}");

        $response = $curl->response;

        if($include_parent_project_id){
            $response[] = $id;
        }

        return $response;
    }

    /**
     * Retorna os campos selecionados da entidade com o id fornecido
     *
     * @param string $type Tipo da entidade (agent|space|project|event|etc)
     * @param int $id id da entidade
     * @param string $fields campos que devem ser retornados
     *
     * @return object
     */
    public function findEntity($type, $id, $fields) {
        $curl = $this->apiGet("api/{$type}/findOne", [
                    'id' => EQ($id),
                    '@select' => $fields
        ]);

        return $curl->response;
    }

    public function findEntities($type, $fields, $params = []) {
        if(is_array($fields)){
            $fields = implode(',', $fields);
        }

        $params['@select'] = $fields;

        $curl = $this->apiGet("api/{$type}/find", $params);

        return $curl->response;
    }

    /**
     * Retorna os espaços onde ocorrem eventos no período informado
     *
     * @param type $from data inicial dos eventos
     * @param type $to data final dos eventos
     * @param type $fields campos que devem ser retornados
     * @param type $params demais parâmetros para a consulta (ver documentação do método findEntitie para mais detalhes)
     */
    public function findSpacesByEvents($from, $to, $fields, array $params = []) {

        $params['@select'] = $fields;
        $params['@from'] = $from;
        $params['@to'] = $to;

        $curl = $this->apiGet('api/space/findByEvents', $params);

        return $curl->response;
    }

    /**
     * Retorna os termos da taxonomia informada
     *
     * @param string $taxonomy_slug
     * @return array
     */
    public function getTaxonomyTerms($taxonomy_slug){
        $curl = $this->apiGet('/api/term/list/' . $taxonomy_slug);

        return $curl->response;
    }

}
