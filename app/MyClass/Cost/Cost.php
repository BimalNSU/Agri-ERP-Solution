<?php
namespace App\MyClass\Cost;
use Illuminate\Support\Facades\DB;

class Cost {
   function __constructor()
    {

    }
    public function createCost($costCatId, $amount, $billCopy = null, $comments = null, $isRecoverable = 0){
        $createdBy = 1;             //set default userId for test purpose
        $createdAt = "2022-10-13";  //set current dateTime for test purpose
        $placeHolder = [$costCatId, $amount, $billCopy, $comments, $isRecoverable, $createdBy, $createdAt];
        $query = "
            INSERT into Costs (cost_cat_id, amount, bill_copy, comments, is_recoverable, created_by, created_at)
                values( ?, ?, ?, ?, ?, ?, ?)";
        DB::insert($query, $placeHolder);
        return DB::getPdo()->lastInsertId();    // return newly created costId
    }
    public function addCost($cost_data){
        $cost_id = createCost($cost_data["cost_cat_id"], $cost_data["amount"], $cost_data["bill_copy"], $cost_data["comments"]);
        $erpAccount = new ERP_Account();
        $type = $erpAccount.getAccountType($transaction_from);
        $pay_erp_transaction_id;
        $transaction_data;
        if($type == "Online") {            
            //here transaction_from means bank_id
            $transaction_data->from_id = $cost_data["transaction_from"];
            // here transaction_to = 0 or any user_id; 0 means "System_id"
            $transaction_data->to_id = $cost_data["transaction_to"];
            $transaction_data->online_transaction_id = $cost_data["online_transaction_id"];
            $transaction_data->transaction_amount = $cost_data["transaction_amount"];
            $transaction_data->transaction_charge = $cost_data["transaction_charge"];
            $transaction_data->transaction_date = $cost_data["transaction_date"];
            $transaction_data->slip = $cost_data["slip"];
            $onlineTransaction = new OnlineTransaction();
            $pay_erp_transaction_id = $onlineTransaction.pay($transaction_data);
        }
        else if($type == "Offline") {            
            //here transaction_from means cash_counter_id
            $transaction_data->from_id = $cost_data["transaction_from"];
            //here transaction_to means employee_id
            $transaction_data->to_id = $cost_data["transaction_to"];
            $transaction_data->transaction_amount = $cost_data["transaction_amount"];
            $offlineTransaction = new OfflineTransaction();
            $pay_erp_transaction_id = $offlineTransaction.pay($transaction_data);
        }
        else 
            return "Invalid request";
        
        /* add pay_erp_transaction_id with cost_id in Costs_n_transactions table */
        $query = " INSERT INTO Costs_n_transactions (pay_erp_transaction_id, cost_id)
                        values(?, ?)";
        DB::insert($query, [$pay_erp_transaction_id, $cost_id]);     
    }
    public function updateCost($costId, $newAmount, $billCopy, $newComments, $isRecoverable){
        $query ="
            update Costs
                SET amount = ?,                    
                    bill_copy = ?,
                    comments = ?,
                    is_recoverable = ?
            WHERE cost_id = ?";
        DB::update($query, [$newAmount, $billCopy, $newComments, $isRecoverable, $costId]);
    }
    public function getClearableCostList(){

    }
}
?>