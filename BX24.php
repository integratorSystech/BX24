<?
//TO DO batch_params more 2500 results
class BX24{

  public $method;
  public $params;
  public $batch_params;
  protected $portal;
  protected $data;
  protected $start;

  public function __construct($portal){
    $this->portal = $portal;
  }

  public function call($method, $params){
    $this->method = $method;
    $this->params = $params;
    $this->data['result'] = [];
    $this->start = 0;


    $Data = $this->connect($this->params);
    $total = $Data['total'];
    if (!empty($total)){
      if ($total > 50){
        $this->batch_param($total);
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
    $this->method = '/batch.json';
    foreach ($fields as $params){
      foreach ($params as $method => $param){
        $this->batch_params['cmd'][] =  $method."?".http_build_query($param);
      }
    }
    return $this->connect($this->batch_params);
  }

  private function connect($params){
    usleep(500000);
    $queryUrl = $this->portal.$this->method;

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

  private function batch_param($total){
    $i=0;
    while ($this->start < $total){
      $Params[] = ["$this->method" => array_merge($this->params, ['start' => $this->start])];
      $this->start += 50;
      $i++;
      if ($i == 50){

        $Data = $this->batch($Params);
        foreach ($Data['result']['result'] as $result){
          $this->data['result'] = array_merge($this->data['result'], $result);
        }
        //$this->batch_param($total);
      }
    }
    return;
  }
}
?>
