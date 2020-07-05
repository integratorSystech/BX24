<?php
namespace BX24;

class FullBatch extends DecoratorBitrix24
{    
    public function batch($fields)
    {   
        $data['result'] = [];
        while (count($fields) > 0) {
            $res = parent::batch(array_splice($fields, 0, 50));
            
            if (!empty($res['result'])) {
                $data['result'] = array_merge(
                    $data['result'],
                    $res['result']
                );  
            } else {
                $data['result'] = array_merge(
                    $data['result'],
                    $res
                );  
            }
        }
        return $data;
    }
}
