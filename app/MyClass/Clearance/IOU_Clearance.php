<?php
namespace App\MyClass\Cost;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IOU_Clearance Implements Clearance {
   function __constructor()
    {

    }
    public function getAccountClearableEmployeeList()
    {
        $query = "
            SELECT u.id employee_id, u.user_type, concat(u.first_name, ' ', u.last_name) employee_name,
                            u.mobile, temp2.total_cash_transfer, temp2.total_cost_paid 
            FROM    (SELECT temp1.*, SUM(ic.paid) total_cost_paid
                    FROM (SELECT et.to_id, SUM(et.transaction_amount) total_cash_transfer
                            FROM ERP_transactions et
                                        NATURAL JOIN (select tt.transaction_type_id
                                                        FROM Transaction_types tt
                                                        WHERE tt.transaction_type = 'Transfer') tt1
                            WHERE et.id NOT IN (SELECT transfer_erp_transaction_id
                                                FROM IOU_transfer_clearances)
                            GROUP BY et.to_id
                            ) temp1              
                            JOIN IOU_costs ic
                                ON temp1.to_id = ic.spender_id
                    where ic.id NOT IN (SELECT iou_cost_id FROM IOU_cost_clearances)
                    GROUP BY ic.spender_id
                    ) temp2
                    JOIN Users u
                        ON temp2.to_id = u.id";
        $result = DB::select($query);
        return json_decode(json_encode($result), true);
    }
    public function getAccountClearableDetails($userId)
    {
        $iou_Transaction = new iou_Transaction();
        $iou_Cost = new iou_Cost();
        $result->iou_Transaction =  $iou_Transaction.getClearableTransferTransactionList($userId)
        $result->iou_Cost = $iou_Cost.getClearableCostList($userId);
        return $result;
    }
    public function doClearance($employeeId, $accountantId, $cashCounterId)
    {
        $iou_transaction = new iou_Transaction();
        $transfer_data = $iou_transaction.getClearableTransferIdWithAmount($employeeId);
        $iou_cost = new iou_Cost();
        $cost_data = $iou_cost.getClearableIOUCostIdWithAmount($employeeId);
        $total_transfer = 0;
        $total_cost = 0;

        foreach ($transfer_data as $value){
            $total_transfer += $value["transaction_amount"];            // this line need to test whether summation is working or not
        }
        foreach ($cost_data as $value){
            $total_cost += $value["paid"];
        }
        $returnable_amount = $total_transfer - $total_cost;

        $return_erp_transaction_id = null;
        if($returnable_amount > 0)
        {
            $offlineTransaction = new OfflineTransaction();

            $transaction_data->from_id = $employeeId;
            $transaction_data->to_id = $cash_counter_id;
            $transaction_data->transaction_amount = $returnable_amount;

            $return_erp_transaction_id = $offlineTransaction.return($transaction_data);
        }
        $currentDateTime = Carbon::now();
        $query = "
            INSERT INTO IOU_Clearances (employee_id, accountant_id, cash_counter_id, return_erp_transaction_id, clearance_at)
                values(?, ?, ?, ?)";
        DB::insert($query, [$employeeId, $accountantId, $cashCounterId, $return_erp_transaction_id, $currentDateTime]);     // create cash_clearance_id  
        $cashClearanceId = DB::getPdo()->lastInsertId();    // return newly created cash_clearance_id
        
        foreach ($transfer_data as $value){
            $transfer_erp_transaction_id = $value["transaction_id"];
            $query = "
                INSERT INTO IOU_transfer_clearances (cost_clearance_id, transfer_erp_transaction_id)
                    values(?, ?)";
            DB::insert($query, [$cashClearanceId, $transfer_erp_transaction_id]);   // add cash_clearance_id and transfer_erp_transaction_id into IOU_transfer_clearances table
        }
        foreach ($cost_data as $value){
            $IOUCostId = $value["iou_cost_id"];
            $query = "
                INSERT INTO IOU_cost_clearances (cash_clearance_id, iou_cost_id)
                    values(?, ?)";
            DB::insert($query, [$cashClearanceId, $IOUCostId]);   // add cash_clearance_id and iou_cost_id into IOU_cost_clearances table
        }

    }
    public function getAccountClearanceHistoyList(){
      
    }   
    public function getAccountClearanceHistoyDetails($clearanceId)
    {
     
    }
}