<?php
namespace BX24;

class FullBatch extends DecoratorBitrix24
{    
    public function batch($fields)
    {          
        while (count($fields) > 0) {
            print_r(count($fields));
            echo '<br>';
            $this->data['result'] = array_merge($this->data['result'], parent::batch(array_splice($fields, 0, 50))['result']);  
        }
        return $this->data;
    }
}