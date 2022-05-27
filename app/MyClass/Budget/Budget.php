<?php
namespace App\MyClass;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;

class Budget {
   function __constructor()
    {

    }
    public function addBudget($data)
    {   $createdBy = Auth::user()->id;
        //get current datetime in sql format(i.e. YYYY-MM-DD HH:MI:SS )
        $createdAt = Carbon::now('Asia/Dhaka').format("YYYY-MM-DD HH:MI:SS");  // get current date with SQL date format
        $query = "
            INSERT INTO Budgets (budget_on_date, budget_period_in_months, created_by, created_at)
                values(?, ?, ?, ?)";
        DB::insert($query, [$data["budgetOnDate"], $data["budgetPeriodInMonths"], $createdBy, $createdAt ]);
        $newBudgetId = DB::getPdo()->lastInsertId();    // return newly created budgetId
        foreach($data["budgets"] as $value){
            $query2 = "
                INSERT INTO Budget_refs (budget_id, cost_cat_id, budget)
                    values(?, ?, ?)";
            DB::insert($query, [ $newBudgetId, $value["costCatId"], $value["costBudget"] ] );
        }
    }
    public function updateCurrentBudget()
    {   
        $updatedBy = Auth::user()->id;
        //get current datetime in sql format(i.e. YYYY-MM-DD HH:MI:SS )
        $updatedAt = Carbon::now('Asia/Dhaka').format("YYYY-MM-DD HH:MI:SS");  // get current date with SQL date format
        $query = "
            UPDATE Budgets 
                set budget_on_date = ?,
                    budget_period_in_months = ?,
                    updated_by = ?,
                    update_at = ?
            WHERE id = ?";
        DB::update($query, [ $data["budgetOnDate"], $data["budgetPeriodInMonths"], $updatedBy, $updatedAt, $data["budgetId"] ]);

        $query2 = "
            DELETE
            FROM Budget_refs
            WHERE budget_id = ?";
        $numberOfDeleted = DB::delete($query2, [$budgetId]);    // delete existing data
        foreach($data["budgets"] as $value){
            $query3 = "
                INSERT INTO Budget_refs (budget_id, cost_cat_id, budget)
                    values(?, ?, ?)";
            DB::insert($query, [ $budgetId, $value["costCatId"], $value["costBudget"] ] );
        }

    }
    public function getCurrentBudgetDateInfo()
    {
        $query = "
            SELECT budget_on_date, budget_period_in_months
            FROM Budgets
            WHERE deleted = 0
            LIMIT 1
            ORDER BY id DESC";
        $results = DB::SELECT($query);
        return json_decode(json_encode($result), true);
    }
    public function getBudgetDateInfo($budgetId){
        $query = "
        SELECT budget_on_date, budget_period_in_months
        FROM Budgets
        WHERE deleted = 0
        WHERE id = ?";
    $results = DB::SELECT($query, [$budgetId]);
    return json_decode(json_encode($result), true);
    }
    public function cancelBudget($budgetId)
    {
        $query ="
            UPDATE Budgets
                set deleted = 1
            WHERE id = ?";
        DB::update($query, [ $budgetId ]);    
    }
}
?>