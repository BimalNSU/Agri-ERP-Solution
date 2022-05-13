<?php
namespace App\MyClass\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\MyClass\Transaction\Transaction;
use App\MyClass\ERP_Account\Bank;
use App\MyClass\ERP_Account\CashCounter;

class OnlineTransaction extends Transaction {
   function __constructor()
    {

    }
    private function addUserOnlineTransactionInfo($data)
   {
        $placeHolder = array($data["onlineTransactionId"], $data["erpTransactionId"], $data["userBankName"], $data["userAccountNo"], $data["transactionCharge"] );
        $query = "
            INSERT into Online_transaction_details (online_transaction_id, erp_transaction_id, user_bank_name, user_account_no, transaction_charge)
                values( ?, ?, ?, ?, ?)";
        $result = DB::insert($query, $placeHolder);
        return $result;
   }  
    public function receivePayment($data)
    {
        $bank = new Bank()
        $bankName = $data["paymentMethod"];
        $bankId = $bank->getPaymentBankId($bankName);
        $transactionData = array(
            "transactionType" => "Receive",
            "transactionWay" => "User to Bank",
            "fromId" => $data["fromId"],    // here fromId is userId
            "toId" => $bankId,
            "transactionAmount" => $data["transactionAmount"],
            "transactionDate" => Carbon::now('Asia/Dhaka')->format('YYYY-MM-DD HH:MI:SS') // current date-time in MySql format
        );
        $receiveErpTransactionId = $this->doTransaction($transactionData);
        $data3 = array(
            "onlineTransactionId" => $data["onlineTransactionId"],
            "erpTransactionId" => $receiveErpTransactionId,
            "userBankName" => $data["userBankName"],
            "userAccountNo" => $data["userAccountNo"],
            "transactionCharge" => $data["transactionCharge"]
        );
        $bank->addBalance( $bankId, $data["transactionAmount"] );
        $this->addUserOnlineTransactionInfo($data3);                                      
        return $receiveErpTransactionId;
    }
    public function pay($data)
    {
        $transactionData = array (
            "transactionType" => "Pay",
            "transactionWay" => "Bank to User",
            "fromId" => $data["fromId"],    // here fromId is bankId
            "toId" => $data["toId"],        // here toId is userId
            "transactionAmount" => $data["transactionAmount"],
            "transactionDate" => $data["transactionDate"]
        );
        $payErpTransactionId = $this->doTransaction($transactionData);
        $bank = new Bank();
        $useBalance = $data["transactionAmount"] + $data["transactionCharge"];
        $bank->useBalance( $data["fromId"], $useBalance);   // here fromId is bankId
        $data3 = array(
            "onlineTransactionId" => $data["onlineTransactionId"],
            "erpTransactionId" => $payErpTransactionId,
            "userBankName" => $data["userBankName"],
            "userAccountNo" => $data["userAccountNo"],
            "transactionCharge" => $data["transactionCharge"]
        );
        $this->addUserOnlineTransactionInfo($data3);
        return $payErpTransactionId;
    }           
    public function payRefund($data)
    {
        $transactionData = array(
            "transactionType" => "Pay Refund",
            "transactionWay" => "Bank to User",
            "fromId" => $data["fromId"],    // here fromId is bankId
            "toId" => $data["toId"],        // here toId is userId
            "transactionAmount" => $data["transactionAmount"],
            "transactionDate" => $data["transactionDate"]
        );
        $payRefundErpTransactionId = $this->doTransaction($transactionData);
        $bank = new Bank();
        $useBalance = $data["transactionAmount"] - $data["transactionCharge"]; 
        $bank->useBalance($data["fromId"], $useBalance);    // here fromId is bankId
        $data3 = array(
            "onlineTransactionId" => $data["onlineTransactionId"],
            "erpTransactionId" => $payRefundErpTransactionId,
            "userBankName" => $data["userBankName"],
            "userAccountNo" => $data["userAccountNo"],
            "transactionCharge" => $data["transactionCharge"]
        );
        $this->addUserOnlineTransactionInfo($data3);                   
        return $payRefundErpTransactionId;
    }
    public function transfer($data)
    { 
        $transactionData = array(
            "transactionType" => "Transfer",
            "transactionWay" => $data["transferMethod"],
            "fromId" => $data["fromId"],    // here fromId is bankId
            "toId" => $data["toId"],        // here toId is either userId or another bankId 
            "transactionAmount" => $data["transactionAmount"],
            "transactionDate" => $data["transactionDate"]
        );
        $transferErpTransactionId = $this->doTransaction($transactionData);
        $bank = new Bank();
        $useBalance = $data["transactionAmount"] + $data["transactionCharge"]
        $bank->useBalance( $data["fromId"], $useBalance);   // here fromId is bankId
        if( $data["transferMethod"] == "Bank to Bank")
        {
            $addAmount = $data["transactionAmount"];
            $bank->addBalance( $data["toId"], $addAmount);  // here toId is another bankId
        }
        $data3 = array(
            "onlineTransactionId" => $data["onlineTransactionId"],
            "erpTransactionId" => $transferErpTransactionId,
            "userBankName" => $data["userBankName"],
            "userAccountNo" => $data["userAccountNo"],
            "transactionCharge" => $data["transactionCharge"]
        );
        $this->addUserOnlineTransactionInfo($data3);
        return $transferErpTransactionId;
    }
    public function withdraw($data)
    {
        $transactionData = array(
            "transactionType" => "Withdraw",
            "transactionWay" => "Bank to Cash Counter",
            "fromId" => $data["fromId"],    //here "fromId" is "bankId"
            "toId" => $data["toId"],        // here "toId" is "cashCounterId"
            "transactionAmount" => $data["transactionAmount"],
            "transactionDate" => $data["transactionDate"]
        );
        $withdrawErpTransactionId = $this->doTransaction($transactionData);

        $bank = new Bank();
        $useBalance = $data["transactionAmount"] + $data["transactionCharge"];
        $bank->useBalance( $data["fromId"], $useBalance);   //here fromId is bankId
        $cashCounter = new CashCounter();
        $cashCounterId = $data["toId"];
        $cashCounter->addBalance( $cashCounterId, $data["transactionAmount"] )
        $data3 = array(
            "onlineTransactionId" => $data["onlineTransactionId"],
            "erpTransactionId" => $withdrawErpTransactionId,
            "userBankName" => $data["userBankName"],
            "userAccountNo" => $data["userAccountNo"],
            "transactionCharge" => $data["transactionCharge"]
        );
        $this->addUserOnlineTransactionInfo($data3);
        return $withdrawErpTransactionId;
    }
    public function deposit($data)
    {
        $transactionData = array(
            "transactionType" => "Deposit",
            "transactionWay" => "Cash Counter to Bank",
            "fromId" => $data["fromId"],     // here "toId" is "cashCounterId"
            "toId" => $data["toId"],        //here "fromId" is "bankId"
            "transactionAmount" => $data["transactionAmount"],
            "transactionDate" => Carbon::now('Asia/Dhaka')->format('YYYY-MM-DD HH:MI:SS') // current date-time in MySql format
        );
        $erpTransactionId = $this->doTransaction($transactionData);

        $cashCounter = new CashCounter();
        $useBalance = $data["transactionAmount"] + $data["transactionCharge"];
        $cashCounter->useBalance( $data["fromId"], $useBalance);    // here "fromId" is "cashCounterId"
        $bank = new Bank();
        $bank->addBalance( $data["toId"], $data["transactionAmount"] ); // here "toId" is bankId"

        $data3 = array(
            "onlineTransactionId" => $data["onlineTransactionId"],
            "erpTransactionId" => $erpTransactionId,
            "userBankName" => null,
            "userAccountNo" => null,
            "transactionCharge" => $data["transactionCharge"]
        );
        $this->addUserOnlineTransactionInfo($data3);
        return $erpTransactionId;
    }    
}
?>