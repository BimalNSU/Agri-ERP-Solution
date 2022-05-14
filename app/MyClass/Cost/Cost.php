<?php
namespace App\MyClass\Cost;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Cost {
   function __constructor()
    {

    }
    public function createCost($costCatId, $amount, $billCopy = null, $comments = null, $isRecoverable = 0){
        $createdBy = Auth::user()->id;
        $createdAt = Carbon::now('Asia/Dhaka').format("YYYY-MM-DD HH:MI:SS");  // get current date with SQL date format
        $placeHolder = [$costCatId, $amount, $billCopy, $comments, $isRecoverable, $createdBy, $createdAt];
        $query = "
            INSERT into Costs (cost_cat_id, amount, bill_copy, comments, is_recoverable, created_by, created_at)
                values( ?, ?, ?, ?, ?, ?, ?)";
        DB::insert($query, $placeHolder);
        return DB::getPdo()->lastInsertId();    // return newly created costId
    }
    public function addCost($costData){
        $costId = $this->createCost($costData["costCatId"], $costData["amount"], $costData["billCopy"], $costData["comments"]);
        $erpAccount = new ERP_Account();
        $type = $erpAccount.getAccountType($costData["transactionFrom"]);
        $payErpTransactionId;
        $transactionData;
        if($type == "Online") {            
            //here transaction_from means bank_id
            $transactionData->fromId = $costData["transactionFrom"];
            // here transaction_to = 0 or any user_id; 0 means "System_id"
            $transactionData->toId = $costData["transactionTo"];
            $transactionData->onlineTransactionId = $costData["onlineTransactionId"];
            $transactionData->transactionAmount = $costData["transactionAmount"];
            $transactionData->transactionCharge = $costData["transactionCharge"];
            $transactionData->transactionDate = $costData["transactionDate"];
            $transactionData->slip = $costData["slip"];
            $onlineTransaction = new OnlineTransaction();
            $payErpTransactionId = $onlineTransaction.pay($transactionData);
        }
        else if($type == "Offline") {            
            //here transaction_from means cash_counter_id
            $transactionData->fromId = $costData["transactionFrom"];
            //here transaction_to means employee_id
            $transactionData->toId = $costData["transactionTo"];
            $transactionData->transaction_amount = $costData["transactionAmount"];
            $offlineTransaction = new OfflineTransaction();
            $payErpTransactionId = $offlineTransaction.pay($transactionData);
        }
        else 
            return "Invalid request";
        
        /* add payErpTransactionId with costId in Costs_n_transactions table */
        $query = " INSERT INTO Costs_n_transactions (pay_erp_transaction_id, cost_id)
                        values(?, ?)";
        DB::insert($query, [$payErpTransactionId, $costId]);     
    }
    public function updateCost($costId, $newAmount, $billCopy, $newComments, $isRecoverable){
        $query ="
            update Costs
                SET amount = ?,                    
                    bill_copy = ?,
                    comments = ?,
                    is_recoverable = ?
            WHERE cost_id = ?";
        try{ 
            $affected = DB::update($query, [$newAmount, $billCopy, $newComments, $isRecoverable, $costId]);
            // DB::commit();
            if($affected > 0){
                return "updated successfully";
            }
        }
        catch(Exception $e)
        {
            // DB::rollback();
            $errorCode = $e->errorInfo[1];                     
            return $e;
        }
    }
    public function getClearableCostList(){
        $onlyClearable = true;
        return getCostlistSummary($onlyClearable);
    }
    public function getCurrentCostlist(){
        $onlyClearable = false;
        $budget = new Budget();
        $result = $budget.getCurrentBudgetDateInfo();
        $fromDate = $result['from_date'];
        $months = $result['budget_period_in_months'];
        $toDate = Carbon::parse($fromDate)->addMonths($months);     // toDate = fromDate + month
        return getCostlistSummary($onlyClearable, $fromDate, $toDate);
    }
    public function getCostlistSummary($onlyClearable, $fromDate="", $toDate=""){
        $optionalCondition1 = "WHERE";
        $optionalCondition2 = "WHERE";
        $count = 0;
        if( !empty($fromDate) and !empty($toDate) ){
            $optionalCondition1 += " et.created_by >= ? AND et.created_by <= ?";
            $optionalCondition2 += "ic.created_by >= ? AND ic.created_by <= ?";
            $count = 1;
        }
        if( !$onlyClearable ){
            if($count == 1){
                $optionalCondition1 += "AND";
                $optionalCondition2 += "AND";
                $optionalCondition1 += " et.id NOT IN (SELECT transfer_erp_transaction_id
                                                        FROM IOU_transfer_clearances)";
                $optionalCondition2 += " ic.id NOT IN (SELECT iou_cost_id
                                                        FROM IOU_cost_clearances)";
            }
        }
        $query = "
            SELECT cc.id cost_cat_id , cc.cost_name,
                        IFNULL(temp1.total_transaction_amount, 0) total_transaction_amount,
                        IFNULL(temp2.total_iou_paid, 0) total_iou_paid
            FROM (SELECT c.cost_cat_id, SUM(et.transaction_amount) total_transaction_amount
                    FROM ERP_transactions et JOIN Costs_n_transactions cnt
                            ON et.id = cnt.pay_erp_transaction_id
                        JOIN Costs c
                            ON cnt.cost_id = c.id"
                    + $optionalCondition1 +
                    "GROUP BY c.cost_cat_id
                 ) temp1
                    RIGHT JOIN 
                    Cost_categories cc
                        ON temp1.cost_cat_id = cc.id
                    LEFT JOIN 
                    (SELECT c.cost_cat_id, SUM(ic.paid) total_iou_paid
                    FROM IOU_Costs ic JOIN Costs c
                        ON ic.cost_id = c.id"
                    + $optionalCondition2 +
                    "GROUP BY c.cost_cat_id
                    ) temp2
                        ON temp2.cost_cat_id = cc.id";
        $result;
        if( !empty($fromDate) AND !empty($toDate) ){
            $result = DB::select($query, [$fromDate, $toDate, $fromDate, $toDate] );
        }else{
            $result = DB::select($query);
        }
        return json_decode(json_encode($result), true);
    }
    public function getCostListInCategory($costCatId, $budgetId=null){
        $budget = new Budget();
        $result;
        if($budgetId){
            $result = $budget.getBudgetDateInfo($budgetId); // return specific budget info
        }else {
            $result = $budget.getCurrentBudgetDateInfo();  // return current budget info
        }
        $fromDate = $result['from_date'];
        $months = $result['budget_period_in_months'];
        $toDate = Carbon::parse($fromDate)->addMonths($months);     // toDate = fromDate + month
        $query = "
        SELECT t2.*, CONCAT(u.first_name, ' ', u.last_name) created_by_name
        FROM ( SELECT t1.*, SUM(ic.paid) iou_amount
                FROM (SELECT id cost_id,  c.amount cost_amount, SUM(et.transaction_amount) transfer_amount, comments, created_by, created_at
                    FROM  ERP_transactions et JOIN Costs_n_transactions cnt RIGHT JOIN Costs c 
                            ON et.id = cnt.pay_erp_transaction_id AND ent.cost_id = c.id
                    WHERE c.cost_cat_id = ?
                    GROUP BY cnt.cost_id
                    ) as t1 
                    LEFT JOIN IOU_costs ic
                        ON t1.cost_id = ic.cost_id
                GROUP BY ic.cost_id
            ) as t2  JOIN Users u
                ON t2.created_by = u.id";
        $result = DB::select($query, [$costCatId]);
        return json_decode(json_encode($result), true);
    }
   
    public function getCostPayDetailsList($costId){
        
        // return:
        // [
        //     {
        //       from_id: int,
        //       from_name: string,
        //       paid: double,
        //       created_by: int,
        //       created_by_name: string,
        //       created_at: string
        //     },
        //   ]
    }
    
    public function cancelCost($costId){

    }
}
?>