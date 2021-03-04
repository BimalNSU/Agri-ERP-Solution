<?php
namespace App\MyClass;
use Illuminate\Support\Facades\DB;


class testClass {
   function __constructor()
    {

    }

    public function testFunction()
    {
        $data = DB::SELECT("SELECT * FROM users");
        $data = json_encode($data);
        $data  = json_decode(json_encode($data),true);
        return $data;
    }
}