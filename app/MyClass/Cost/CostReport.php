<?php
namespace App\MyClass;
use Illuminate\Support\Facades\DB;

class CostReport {
   function __constructor()
    {

    }
    public function getWhereOrAnd($whereCount){
        if($whereCount == 0){
            return ' WHERE ';
        }        
        else if($whereCount > 0){
            return ' AND ';
        }
        return '';
    }
    public function getCostPaymentsReport1($cost_cat_id = null, $is_recoverable = null, $payment_status = null, $from_date = null, $to_date = null, $optional_cost_id = null){
            $queryParameters = [];
            $onStatement = "";
            $whereCount = 0;
            $whereStatement = "";
            if(empty($optional_cost_id) == false) {
                $onStatement = " AND c.id = ? ";
                $queryParameters.add($optional_cost_id);
            }
            else
            {
                if(empty($cost_cat_id) == false){
                    $onStatement = " AND c.cost_cat_id = ? ";
                    $queryParameters.add($cost_cat_id);
                }
                if(empty($is_recoverable) == false){
                    $onStatement += " AND c.is_recoverable = ? ";
                    $queryParameters.add($is_recoverable);
                }
                if(empty($payment_status) == false){
                    if($payment_status == "due") {
                        $whereCondition = " c.amount > (t1.direct_pay_amounts + t2.iou_pay_amounts) ";                    
                        $whereStatement += getWhereOrAnd($whereCount) + $whereCondition;
                        $whereCount += 1;
                    }
                    else if($payment_status == "paid"){
                        $whereCondition = " c.amount = (t1.direct_pay_amounts + t2.iou_pay_amounts) ";
                        $whereStatement += getWhereOrAnd($whereCount) + $whereCondition;
                        $whereCount += 1;
                    }
                }
                if(empty($from_date) == false){                
                    $whereStatement += getWhereOrAnd($whereCount) + " c.created_at >= ? ";
                    $queryParameters.add($from_date);
                    $whereCount += 1;
                    if(empty($to_date) == false) {
                        $whereCondition = "c.created_at <= ?";                    
                        $whereStatement += getWhereOrAnd($whereCount) + $whereCondition;
                        $queryParameters.add($to_date);
                        $whereCount += 1;
                    }
                }
            }            
        $query = "
            SELECT c.id cost_id, cc.cost_name, c.amount cost_amount, t1.direct_pay_amounts,
                    t2.iou_pay_amounts, c.bill_copy, c.comments, c.created_by,
                    CONCAT(u.first_name, ' ', u.last_name) created_by_name,
                    c.created_at
            FROM Cost_categories cc JOIN Costs c
                    ON cc.id = c.cost_cat_id " + onStatement + 
                    " LEFT JOIN
                    ( SELECT cnt.cost_id, 
                            SUM(et.transaction_amount) direct_pay_amounts
                    FROM Costs_n_transactions cnt JOIN ERP_transactions et
                                ON cnt.pay_erp_transaction_id =  et.id
                        GROUP BY cnt.cost_id
                    ) t1
                        ON c.id = t1.cost_id
                    LEFT JOIN
                    ( SELECT ic.cost_id, SUM(ic.paid) iou_pay_amounts                              
                    FROM IOU_costs ic
                    GROUP BY ic.cost_id
                    ) t2
                        ON c.id = t2.cost_id
                    JOIN Users u
                        ON c.created_by = u.id "
            + $whereStatement ;
                
        $result = DB::select($query, $queryParameters);
        $result  = json_decode(json_encode($result), true);
        return $result;
    }
    public function getCostPaymentsReport2($cost_cat_id = null, $payment_type = null, $is_recoverable = null, $from_date = null, $to_date = null) {        
        $queryParameters = [];
        $whereCount = 0;
        $whereStatement = "";
        if(empty($cost_cat_id) == false){
            $whereStatement += getDynamicWhere($whereCount) + "t.cost_cat_id = ? ";
            $queryParameters.add($cost_cat_id);
            $whereCount += 1;
        }
        if(empty($is_recoverable) == false){
            $whereStatement += getDynamicWhere($whereCount) + "t.is_recoverable = ? ";
            $queryParameters.add($is_recoverable);
            $whereCount += 1;
        }
        if(empty($from_date) == false) {
            $queryParameters.add($from_date);   
            $whereStatement += getDynamicWhere($whereCount) + "t.created_at >= ?";
            $whereCount += 1;    
            if(empty($to_date) == false) {
                $queryParameters.add($to_date);
                $whereStatement += getDynamicWhere($whereCount) + "t.created_at <= ?";
                $whereCount += 1;
            }
        }
        $subQuery1 = "
            SELECT ic.id, c.cost_cat_id, c.is_recoverable, 'IOU payment' payment_type, cc.cost_name, ic.paid,
                    ic.spender_id, CONCAT(u1.first_name, ' ', u1.last_name) spender_name, u1.mobile spender_mobile,
                    ic.created_by, CONCAT(u2.first_name, ' ', u2.last_name) creator_name, u2.mobile creator_mobile,
                    ic.created_at, ic.cost_id
            FROM IOU_costs ic JOIN Costs c JOIN Cost_categories cc
                    ON (ic.cost_id, c.cost_cat_id) = (c.id, cc.id)
                JOIN Users u1 JOIN Users u2
                    ON ic.spender_id = u1.id AND ic.created_by = u2.id ";
        $subQuery2 = "
            SELECT et.id, c.cost_cat_id, c.is_recoverable, 'Direct payment' payment_type,
                    cc.cost_name, et.transaction_amount paid,
                    et.to_id spender_id, CONCAT(u1.first_name, ' ', u1.last_name) spender_name, u1.mobile spender_mobile, 
                    et.created_by, CONCAT(u2.first_name, ' ', u2.last_name) creator_name, u2.mobile creator_mobile, 
                    et.created_at, cnt.cost_id
            FROM Costs_n_transactions cnt JOIN ERP_transactions et
                   ON cnt.pay_erp_transaction_id =  et.id
                JOIN Costs c JOIN Cost_categories cc
                        ON (cnt.cost_id, c.cost_cat_id) = (c.id, cc.id)
                    JOIN Users u1 JOIN Users u2
                        ON et.to_id = u1.id AND et.created_by = u2.id ";
        if(empty($payment_type) == false) {
            if($payment_type == "Direct payment") {
                $subQuery = $subquery1;
            }
            else if($payment_type == "IOU payment") {
                $subQuery = $subquery2;
            }
        }
        else {
            $subQuery = $subQuery1 +
                    " UNION ALL "
                    + $subQuery2;
        }
        $query = "
            SELECT t.id, t.payment_type, t.cost_name, t.paid, t.spender_id, t.spender_name, t.spender_mobile,
                    t.created_by, t.creator_name, t.creator_mobile, t.created_at, t.cost_id
            FROM ( " + $subQuery + " ) t "        
            + $whereStatement +
            " ORDER BY t.created_at DESC";                
        $result = DB::select($query, $queryParameters);
        $result  = json_decode(json_encode($result),true);
        return $result;
    }
    public function getCostPaymentDetails($cost_id){
        $json_object;
        if( empty($session_data) == true) {
            $session_data = getCostPaymentsReport1($optional_cost_id);
        }
        $json_object->session_data = $session_data;   
        $json_object->iou_payment_list = getIOU_PaymentList($cost_id);
        $json_object->direct_payment_list = getDirectPaymentList($cost_id);
        return $json_object;
    }
    public function getIOU_PaymentList($cost_id) {                
        $query = "
            SELECT ( case
                        when 1 then (select b.id
                                    FROM Budgets b
                                    WHERE b.budget_on_date <= ic.created_at and
                                        ic.created_at <= DATE_ADD(b.budget_on_date, INTERVAL b.budget_period_in_months MONTH) 
                                )
                        else 0
                    end) budget_id,
                    ic.id iou_cost_id, ic.paid, ic.spender_id,
                    CONCAT(u1.first_name, ' ', u1.last_name) spender_name,
                    u1.mobile spender_mobile, ic.created_by,
                    CONCAT(u2.first_name, ' ', u2.last_name) creator_name,
                    u2.mobile creator_mobile, ic.created_at, ic.cost_id
            FROM IOU_costs ic JOIN Costs c
                    ON ic.cost_id = ? AND ic.cost_id = c.id
                JOIN Users u1 JOIN Users u2
                    ON ic.spender_id = u1.id AND ic.created_by = u2.id 
            ORDER BY ic.created_at DESC";      
        $result = DB::select($query, [$cost_id]);
        $result  = json_decode(json_encode($result), true);
        return $result;
    }
    public function getDirectPaymentList($cost_id) {
        $onCodition = " cnt.cost_id = ? AND ";
        $query = "
            SELECT ( case
                        when 1 then (select b.id
                                    FROM Budgets b
                                    WHERE b.budget_on_date <= et.created_at and
                                        et.created_at <= DATE_ADD(b.budget_on_date, INTERVAL b.budget_period_in_months MONTH) 
                                )
                        else 0
                    end) budget_id, et.id transaction_id, et.from_id, ea.name from_name,
                    et.to_id, CONCAT(u1.first_name, ' ', u1.last_name ) to_name, et.transaction_amount,
                    et.created_by,  CONCAT(u2.first_name, ' ', u2.last_name ) creator_name, u2.mobile,
                    et.created_at
            FROM Costs_n_transactions cnt JOIN ERP_transactions et JOIN ERP_accounts ea
                    ON " + $onCodition + " cnt.pay_erp_transaction_id = et.id AND et.from_id = ea.id
                JOIN  Users u1 JOIN Users u2
                    ON et.to_id = u1.id AND et.created_by = u2.id ";
        $result = DB::SELECT($query, [$cost_id]);
        $result  = json_decode(json_encode($result), true);
        return $result;
    }
}
?>