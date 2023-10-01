<?php
namespace phpsgex\framework\models;

class ModelLoader{
    protected function __construct(Array $values){
        if( $values == null ) throw new \Exception("values is null");

        foreach( get_object_vars($this) as $key => $val ) {
            if( !array_key_exists($key,$values) )
                error_log("Invalid key $key for class ".get_class($this));
            else if( $values[$key] != null )
                $this->{$key} = $values[$key];
        }
    }
}