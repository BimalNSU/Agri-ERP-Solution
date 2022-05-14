<?php
namespace App\MyClass\ERP_Account;
use Illuminate\Support\Facades\DB;

class ERP_Account {
   function __constructor()
    {

    }
    function updateAccountStatus($erpAccountId, $isActive)
    {
        $query = " 
            UPDATE ERP_accounts
                SET is_active = ?
            WHERE id = ?";
        $result = DB::update($query, [$isActive, $erpAccountId]);
        $result;
    }
    function addBalance($erpAccountId, $amount)
    {
        $query = " 
            UPDATE ERP_accounts
                SET balance = balance + ?
            WHERE id = ?";
        $result = DB::update($query, [$amount, $erpAccountId]);
    }
    function useBalance($erpAccountId, $useAmount)
    {
        $query = " 
            UPDATE ERP_accounts
                SET balance = balance - ?
            WHERE id = ?";
        $result = DB::update($query, [$useAmount, $erpAccountId]);
        return $result;
    }
    function getAssignedAccountantList($erpAccountId)
    {
        $query = " 
            SELECT y.accountant_id, concat(u1.first_name, ' ', u1.last_name) name, u1.mobile
            FROM ERP_accounts x JOIN ERP_accountants y
                    ON x.id = y.erp_account_id AND
                        x.id = ?
                    JOIN Users u1
                    ON y.accountant_id = u1.id";
        $result = DB::select($query, [$erpAccountId]);
        return json_decode(json_encode($result), true);
    }
    
    function getAccountType($erpAccountId)
    {
        $query = " 
            SELECT case
                        WHEN ea.id IS NULL then 'None'
                        WHEN ea.id IS NOT NULL AND
                                ebd.erp_account_id IS NULL then 'Offline'
                        ELSE 'Online'
                    END 'type'
            FROM ERP_accounts ea LEFT JOIN ERP_bank_details ebd
                    ON ea.id = ebd.erp_account_id
            WHERE ea.id = ?";
        $result = DB::select($query, [$erpAccountId]);
        $result = json_decode($result, true);
        return $result['type'];
    }
}
?>