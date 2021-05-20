<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

<<<<<<< HEAD
class AdminController extends Controller
{
    //
=======
use App\MyClass\testClass;


class AdminController extends Controller
{
    public function index()
    {
        // return "hello admin";
        $testObj = new testClass();
        
        return $testObj->testFunction();
    }
>>>>>>> 5ea729515e4e82473d52a24596803fcc4be2e0a5
}
