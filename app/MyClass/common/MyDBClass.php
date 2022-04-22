<?php
namespace App\MyClass;
//use Illuminate\Support\Facades\DB;

class MyDBClass {
   function __constructor()
    {

    }
    public function getWhereOrAnd($whereCount){
        if($whereCount == 0){
            return ' WHERE ';
        }        
        else if($whereCount > 0){
            return ' AND ';
        }
        return '';
    }
    public function getOnOrAnd($onCount){
        if($onCount == 0){
            return ' ON ';
        }        
        else if($onCount > 0){
            return ' AND ';
        }
        return '';
    }
}
?>