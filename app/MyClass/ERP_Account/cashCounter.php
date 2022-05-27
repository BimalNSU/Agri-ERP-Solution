<?php
namespace App\MyClass\ERP_Account;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;

class CashCounter {
   function __constructor()
    {

    }
    public createAccount($cashCounterName, $balance, $isActive)
    {
        $createdBy = Auth::user()->id;
        $createdAt = Carbon::now('Asia/Dhaka').format("YYYY-MM-DD HH:MI:SS");  // get current date with SQL date format
        $query = " 
            INSERT INTO ERP_accounts (name, balance, is_active, created_by, created_at)
                VALUES(?, ?, ?)";
        $result = DB::insert($query, [$cashCounterName, $balance, $isActive, $createdBy, $createdAt]);
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