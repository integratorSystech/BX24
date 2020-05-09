<?php
namespace BX24;

class FullList extends DecoratorBitrix24
{
    protected $total;
   
    public function call($method, $params)
    {
        $this->data = ['result' => []];
        $this->method = $method;
        $this->params = $params;
        $this->total = $this->connect($method, $params)['total'];
        if ($this->total > 50) {
            return $this->paramsForBatch();
        } else {
            return parent::call($method, $params);
        }
        
    }

    public function batch($fields)
    {
        return parent::batch($fields);
    }

    public function connect($method, $params)
    {
        return parent::connect($method, $params);
    }

    private function paramsForBatch($start=0)
    {
      $i=0;
      $Params = [];
      while ($start < $this->total){
        $Params[] = [$this->method => array_merge($this->params, ['start' => $start])];
        $start += 50;
        $i++;
        if ($i == 50) {
            $this->paramsForBatch($start);
            continue;
        }
      }
      $Data = $this->batch($Params);
      if ($this->method == 'tasks.task.list') {
        foreach ($Data['result'] as $result) {
          $this->data['result'] = array_merge($this->data['result'], $result['tasks']);
        }
      } elseif ($this->method == 'crm.documentgenerator.document.list') {
          foreach ($Data['result'] as $result) {
          $this->data['result'] = array_merge($this->data['result'], $result['documents']);
        }
      } else{
          foreach ($Data['result'] as $result) {
              $this->data['result'] = array_merge($this->data['result'], $result);
        }
      }
      return $this->data;
    }
}

