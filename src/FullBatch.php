<?php
namespace BX24;

class FullBatch extends DecoratorBitrix24
{    
    public function batch($fields)
    {          
        while (count($fields) > 0) {
            $res = parent::batch(array_splice($fields, 0, 50));
            
            if (!empty($res['result'])) {
                $this->data['result'] = array_merge(
                    $this->data['result'],
                    $res['result']
                );  
            } else {
                $this->data['result'] = array_merge(
                    $this->data['result'],
                    $res
                );  
            }
        }
        return $this->data;
    }
}