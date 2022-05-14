<?php
namespace App\MyClass\ERP_Account;
use Illuminate\Support\Facades\DB;

class Bank extends ERP_Account {
    function __constructor()
    {

    }
    function createAccount($bank_name, $branch_name, $account_no, $account_holder, $balance, $is_acitve)
    {
        $is_payment_receivable_account = 0;
        $erpAccountPlaceHolder = [$bank_name, $balance, $is_acitve, $created_by, $created_at];
        $query = "
            INSERT into ERP_accounts (name, balance, is_active, created_by, created_at)
                values( ?, ?, ?, ?, ?)";
        DB::insert($query, $erpAccountPlaceHolder);
        $erpAccountId = DB::getPdo()->lastInsertId();

        $erpAccountDetailsPlaceHolder = [$erpAccountId, $account_no, $branch_name, $account_holder, $this->is_payment_receivable_account];
        $query = "
            INSERT into ERP_bank_details (erp_account_id, account_no, branch_name, account_holder, is_payment_receivable_account)
                values( ?, ?, ?, ?, ?)";
        DB::insert($query, $erpAccountDetailsPlaceHolder);
        return $erpAccountId;
    }
    function getBankListIdWithName()
    {
        $query = "
            SELECT id bank_id, name bank_name
            FROM ERP_accounts
            WHERE id IN (SELECT erp_account_id 
                        FROM ERP_bank_details)";
        $result = DB::select($query);
        $result = json_decode(json_encode($result), true);
        return $result;
    }
    public function getPaymentBankId($bankName)
    {
        $query = "
            SELECT id
            FROM ERP_accounts
            WHERE name = ? and
                id IN (SELECT erp_account_id
                        FROM ERP_bank_details
                        WHERE is_payment_receivable_account = 1)";
        $result = DB::select($query);
        $result = json_decode(json_encode($result), true);
        return $result["id"];
    }
}
?>