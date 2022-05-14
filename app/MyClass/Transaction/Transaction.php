<?php
namespace App\MyClass\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\MyClass\ERP_Account\CashCounter;
use Auth;

class Transaction {
   function __constructor()
    {

    }
    private function getTransactionTypeID( $transactionType, $transactionWay)
    {
        $query = "
            SELECT transaction_type_id
            FROM Transaction_types
            WHERE transaction_type = ? and transaction_way = ?";
        $result = DB::select($query, [$transactionType, $transactionWay] );
        $result =  json_decode(json_encode($result), true);
        return $result["transaction_type_id"];
    }
    public function doTransaction($transactionData)
    {
        $createBy = Auth::user()->id;
        $createdAt = Carbon::now('Asia/Dhaka').format("YYYY-MM-DD HH:MI:SS");  // get current date with SQL date format
        $transactionTypeId = $this->getTransactionTypeID( $transactionData["transactionType"], $transactionData["transactionWay"] );
        $placeHolder = [
            $transactionTypeId, $transactionData["fromId"], $transactionData["toId"], $transactionData["transactionAmount"], $transactionData["transactionDate"],
            $transactionData["slipType"], $transactionData["slip"], $createBy, $createdAt
        ];
        $query = "
            INSERT INTO ERP_transactions (transaction_type_id, from_id, to_id, transaction_amount, transaction_date, slip_type, slip, created_by, created_at)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = DB::insert($query, $placeHolder);
        return $result;
    }
    public function addTransactionType($transactionType, $transactionWay)
    {
        $query = "
        INSERT INTO Transaction_types (transaction_type, transaction_way)
            VALUES(?, ?)";
        $result = DB::insert($query, [$transactionType, $transactionWay] );
        return $result;
    }
    public function getTransactionTypesList()
    {
        $query = "
            SELECT transaction_type_id, transaction_type, transaction_way
            FROM Transaction_types";
        $result = DB::select($query);
        return json_decode(json_encode($result), true);
    }
}
?>