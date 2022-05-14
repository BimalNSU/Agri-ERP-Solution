<?php
namespace App\MyClass\ERP_Account;
use Illuminate\Support\Facades\DB;

class CashCounter {
   function __constructor()
    {

    }
    public createAccount($cashCounterName, $balance, $isActive)
    {
        $query = " 
            INSERT INTO ERP_accounts (name, balance, is_active)
                VALUES(?, ?, ?)";
        $result = DB::insert($query, [$cashCounterName, $balance, $isActive]);
    }                
    public function getCounterList()
    {
        $query = "
            SELECT id cash_counter_id, name couter_name
            FROM ERP_accounts
            WHERE id NOT IN (SELECT erp_account_id
                            FROM ERP_bank_details)";
        $result = DB::select($query);
        return json_decode(json_encode($result), true);
    }
    public function getCounterBalanceList()
    {
        $query = "
            SELECT id cash_counter_id, name couter_name, balance
            FROM ERP_accounts
            WHERE id NOT IN (SELECT erp_account_id FROM ERP_bank_details)";
        $result = DB::select($query);
        return json_decode(json_encode($result), true);
    }
}
?>