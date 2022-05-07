<?php
namespace App\MyClass;
use Illuminate\Support\Facades\DB;


class Budget {
   function __constructor()
    {

    }
    public function addBudget($data)
    {   //convert createdAt date-format into sql format(i.e. YYYY-MM-DD )
        $createdAt = Carbon::parse($data["createdAt"])->format("YYYY-MM-DD");
        $query = "
            INSERT INTO Budgets (budget_on_date, budget_period_in_months, created_by, created_at)
                values(?, ?, ?, ?)";
        DB::insert($query, [$data["budgetOnDate"], $data["budgetPeriodInMonths"], $data["createdBy"], $createdAt ]);
        $newBudgetId = DB::getPdo()->lastInsertId();    // return newly created budgetId
        foreach($data["budgets"] as $value){
            $query2 = "
                INSERT INTO Budget_refs (budget_id, cost_cat_id, budget)
                    values(?, ?, ?)";
            DB::insert($query, [ $newBudgetId, $value["costCatId"], $value["costBudget"] ] );
        }
    }
    public function updateCurrentBudget()
    {   //convert createdAt date-format into sql format(i.e. YYYY-MM-DD )
        $createdAt = Carbon::parse($data["createdAt"])->format("YYYY-MM-DD");
        $query = "
            UPDATE Budgets 
                set budget_on_date = ?,
                    budget_period_in_months = ?
            WHERE id = ?";
        DB::update($query, [ $data["budgetOnDate"], $data["budgetPeriodInMonths"], $data["budgetId"] ]);

        // foreach($data["budgets"] as $value){
        //     $query2 = "
        //         UPDATE Budget_refs 
        //             set cost_cat_id = ?,
        //                 budget = ?
        //             WHERE id = ?";
        //     DB::insert($query, [ $newBudgetId, $value["costCatId"], $value["costBudget"] ] );
        // }

    }
    public function getCurrentBudgetDateInfo()
    {
        $query = "
            SELECT budget_on_date, budget_period_in_months
            FROM Budgets
            LIMIT 1
            ORDER BY id DESC";
        $results = DB::SELECT($query);
        return json_decode(json_encode($result), true);
    }
    public function getBudgetDateInfo($budgetId){
        $query = "
        SELECT budget_on_date, budget_period_in_months
        FROM Budgets
        WHERE id = ?";
    $results = DB::SELECT($query, [$budgetId]);
    return json_decode(json_encode($result), true);
    }
    public function cancelBudget($budgetId)
    {

    }

}
?>