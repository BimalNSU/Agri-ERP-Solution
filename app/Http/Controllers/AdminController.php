<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\MyClass\testClass;

class AdminController extends Controller
{
    public function index()
    {
        // return "hello admin";
        $testObj = new testClass();
        
        return $testObj->testFunction();
    }
}
