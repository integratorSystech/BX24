<?php
namespace BX24;

interface ComponentBitrix24
{
    const BATCH_METHOD = '/batch.json';
    
    public function call($method, $params);
    public function batch($fields);
    public function connect($method, $params);
}


class Bitrix24 implements ComponentBitrix24
{
    public $method;
    public $params;
    public $data;
    public $start;
    public $total;
    public $auth = [];
    protected $portal;
    protected $app;
    
    public static function dump($var)
    {
        echo '<br>';
        var_dump($var);
        echo '<br>';
    }
    
    public function setAuth($auth)
    {
        $this->auth = ["auth" => $auth];
        return;
    }


    public function __construct($portal)
    {
        $this->portal = $portal;
        if (!empty(filter_input(INPUT_POST, 'AUTH_ID'))) {
            $this->auth = ["auth" => filter_input(INPUT_POST, 'AUTH_ID')];
        }
    }

    public function call($method, $params)
    {
        $this->method = $method;
        $this->params = $params;
        return $this->connect($this->method, $this->params);
    }

     /**
    * @param array $fields
    * @author a.panfilov
    * @return array unchanged
    */
    
    public function batch($fields)
    {   
        $batch_params = [];
        foreach ($fields as $params) {
            foreach ($params as $method => $param) {
                $batch_params['cmd'][] = $method."?".http_build_query($param);
            }
        }
        return $this->connect(ComponentBitrix24::BATCH_METHOD, $batch_params)['result'];
    }

    public function connect($method, $params)
    {
        usleep(450000);
        $queryUrl = $this->portal.$method;
        $queryData = http_build_query(array_merge($params, $this->auth));

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $queryUrl,
            CURLOPT_POSTFIELDS => $queryData
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        if(empty(json_decode($result, true)['result'])){
            $res['result'] = json_decode($result, true);
            return $res; 
        }
        return json_decode($result, true);
  }
}


class DecoratorBitrix24 implements ComponentBitrix24
{
    public $method;
    public $params;
    public $auth = [];

    protected $component;
    protected $data = ['result' => []];

    public function __construct(ComponentBitrix24 $component)
    {
        $this->component = $component;
    }

    public function call($method, $params)
    {
        $this->component->method = $method;
        $this->component->params = $params;
        return $this->component->call($method, $params);
    }

    public function batch($fields)
    {
        return $this->component->batch($fields);
    }

    public function connect($method, $params)
    {   
        if (!empty(filter_input(INPUT_POST, 'AUTH_ID'))) {
            $this->component->auth = ["auth" => filter_input(INPUT_POST, 'AUTH_ID')];
        }
        return $this->component->connect($method, $params);
    }
}
