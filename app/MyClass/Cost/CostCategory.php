<?php
namespace App\MyClass\Cost;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;

class CostCategory {
   function __constructor()
    {

    }
    public function getCostCatId_n_NameList()
    {
        $query = "
            SELECT id costCatId, cost_name
            FROM Cost_categories
            WHERE isDeleted = 0";
        $result = DB::select($query)
        $result  = json_decode(json_encode($result), true);
        return $result;
    }
    public function updateCostCatName($costTypeId,  $newCostName)
    {
        $query = "
            UPDATE Cost_categories
                SET cost_name = ?
            WHERE id = ?"
        DB::update($query, [ $newCostName, $costTypeId ]);
    }
    public function addCostCategory($costName)
    {
        $createdBy = Auth::user()->id;
        $createdAt = Carbon::now('Asia/Dhaka').format("YYYY-MM-DD HH:MI:SS");  // get current date with SQL date format
        $query = "
            INSERT INTO Cost_categories (cost_name, created_by, created_at)
                values(?, ?, ?)";
        DB::update($query, [ $costName, $createdBy, $createdAt ]);
    }
    public function cancelCostCategory($costCatId)
    {
        $query = "
            UPDATE Cost_categories
                SET isDeleted = true
            WHERE id = ?"
        DB::update($query, [ $costCatId ]);
    }

?>