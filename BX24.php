<?
class BX24{

  public $method;
  public $batch_method;
  public $params;
  protected $portal;
  protected $data;
  protected $start;
  protected $total;
  protected $auth;
  protected $app;


  public function __construct($portal, $app=0){
    $this->portal = $portal;
    $this->batch_method = '/batch.json';
    $this->app = $app;
    if($this->app){
      $this->auth = $_POST['AUTH_ID'];
    }
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


  private function private_batch($fields){
    foreach ($fields as $params){
      foreach ($params as $method => $param){
        $batch_params['cmd'][] =  $method."?".http_build_query($param);
      }
    }
    return $this->connect($this->batch_method, $batch_params)['result'];
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
    $Data = $this->private_batch($Params);
    foreach ($Data['result'] as $result){
      $this->data['result'] = array_merge($this->data['result'], $result);
    }
    return;
  }


  public function batch($fields){
	    foreach ($fields as $params){
      foreach ($params as $method => $param){
        $batch_params['cmd'][] =  $method."?".http_build_query($param);
      }
    }
	$count=count($batch_params['cmd']);
	$kol=ceil($count/50);
	$c=0;
	$yst=0;
	$all=array();
	while ($c<$kol)
	{
		$obs=0;
		while ($obs<50)
		{
		$tek['cmd'][$obs]=array_shift($batch_params['cmd']);
		$obs++;
		}
		$data=$this->connect($this->batch_method, $tek)['result'];
		foreach($data['result'] as $y)
		{
		$all[$yst]=$y;
		$yst++;
		}
		$c++;
		unset($tek,$data);
	}
	return $all;
  }


  private function connect($method, $params){
    usleep(450000);
    $queryUrl = $this->portal.$method;

    if ($this->app){
      $queryData = http_build_query(array_merge($params, ["auth" => $this->auth]));
    }
    else{
      $queryData = http_build_query($params);
    }

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
}
?>
