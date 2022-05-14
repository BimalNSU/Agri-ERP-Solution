<?php
namespace App\MyClass\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\MyClass\Transaction\Transaction;
use App\MyClass\ERP_Account\CashCounter;

class OnlineTransaction extends Transaction {
   function __constructor()
    {

    }
    public function receivePayment($data)
    {      
        $transactionData = array(
            "transactionType" => "Receive",
            "transactionWay" => "User to Cash counter",
            "fromId" => $data["fromId"],    // here fromId is userId
            "toId" => $data["toId"],        // here toId is cash_counter_id
            "transactionAmount" => $data["transactionAmount"],
            "transaction_Date" => $data["transactionDate"]
        );
        $receiveErpTransactionId = $this->doTransaction($transactionData);
        $cashCounter = new CashCounter();
        $cashCounter->addBalance( $data["toId"], $data["transactionAmount"] );    // here toId is cash_counter_id
        return $receiveErpTransactionId;                            
    }
    public function pay($data)
    {
        $transactionData = array(
            "transactionType" => "Pay",
            "transactionWay" => "Cash Counter to User",
            "fromId" => $data["fromId"],    // here fromId is cash_counter_id
            "toId" => $data["toId"],        // here toId is user_id this an employeeId
            "transactionAmount" => $data["transactionAmount"],
            "transactionDate" => Carbon::now('Asia/Dhaka')->format('YYYY-MM-DD HH:MI:SS') // current date-time in MySql format
        );
        $payErpTransactionId = $this->doTransaction($transactionData);
        $cashCounter = new CashCounter();
        $cashCounter->useBalance( $data["fromId"], $data["transactionAmount"] );    // here fromId is cash_counter_id
        return $payErpTransactionId;        
    }         
    public function return($data)
    {
        $transactionData = array(
            "transactionType" => "Return",
            "transactionWay" => "User to Cash Counter",
            "fromId" => $data["fromId"],    // here fromId is employee_id
            "toId" => $data["toId"],        // here toId is cash_counter_id
            "transactionAmount" => $data["transactionAmount"],
            "transactionDate" => Carbon::now('Asia/Dhaka')->format('YYYY-MM-DD HH:MI:SS') // current date-time in MySql format
        );
        $returnErpTransactionId = $this->doTransaction($transactionData);
        $cashCounter = new CashCounter();
        $cashCounter->addBalance( $data["toId"], $data["transactionAmount"] ); // here toId is cash_counter_id
        return $returnErpTransactionId; 
    }
    public function transfer($data)
    {
        $transactionData = array(
            "transactionType" => "Transfer",
            "transactionWay" => $data["transferMethod"],
            "fromId" => $data["fromId"],    // here fromId is cash_counter_id
            "toId" => $data["toId"],        // here toId is either an user or another cash_counter_id
            "transactionAmount" => $data["transactionAmount"],
            "transactionDate" => Carbon::now('Asia/Dhaka')->format('YYYY-MM-DD HH:MI:SS') // current date-time in MySql format
        );
        $traferErpTransactionId = $this->doTransaction($transactionData);
        $cashCounter = new CashCounter();
        $cashCounter->useBalance( $data["fromId"], $data["transactionAmount"] );    // here fromId is cash_counter_id
        if ($data["transferMethod"] == "Cash Counter to Cash Counter" ){
            $cashCounter->addBalance( $data["toId"] , $data["transactionAmount"] );      // here toId is another cash_counter_id
        }
        return $traferErpTransactionId;
    }

}
?>