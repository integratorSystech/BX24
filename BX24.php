<?
class BX24{

  public $method;
  public $batch_method;
  public $params;
  protected $portal;
  protected $data;
  protected $start;
  protected $total;

  public function __construct($portal){
    $this->portal = $portal;
    $this->batch_method = '/batch.json';
  }

  public function call($method, $params){
    $this->method = $method;
    $this->params = $params;
    $this->data['result'] = [];
    $this->start = 0;


    $Data = $this->connect($this->method, $this->params);
    $this->total = $Data['total'];
    if (!empty($this->total)){
      if ($this->total > 50){
        $this->batch_param();
        return $this->data;
      }
      else {
        return $Data;
      }
    }
    else {
      return $Data;
    }
  }

  public function batch($fields){
    foreach ($fields as $params){
      foreach ($params as $method => $param){
        $batch_params['cmd'][] =  $method."?".http_build_query($param);
      }
    }
    return $this->connect($this->batch_method, $batch_params);
  }

  private function connect($method, $params){
    usleep(500000);
    $queryUrl = $this->portal.$method;

    $queryData = http_build_query($params);

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
    $result = json_decode($result, true);
      return $result;
  }

  private function batch_param(){
    $i=0;
    while ($this->start < $this->total){
      $Params[] = ["$this->method" => array_merge($this->params, ['start' => $this->start])];
      $this->start += 50;
      $i++;
      if ($i == 50){
        $this->batch_param();
        continue;
      }
    }
    $Data = $this->batch($Params);
    foreach ($Data['result']['result'] as $result){
      $this->data['result'] = array_merge($this->data['result'], $result);
    }
    return;
  }
}
?>
