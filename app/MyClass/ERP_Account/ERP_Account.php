<?php
namespace App\MyClass;
use Illuminate\Support\Facades\DB;

class ERP_Account {
   function __constructor()
    {

    }
    function updateAccountStatus($erp_account_id, $is_active)
    {

    }
    function addBalance($erp_account_id, $amount)
    {

    }
    function useBalance($erp_account_id, $use_amount)
    {

    }
    function getAssignedAccountantList($erp_account_id)
    {
        // [
        //     {
        //       accountant_id: int,
        //       name: string,
        //       mobile: string
        //     },
        //   ]
    }
    
    function getAccountType($erp_account_id)
    {
        return '';
    }
}
?>