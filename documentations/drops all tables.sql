/* Online orders all data tables */
	drop table Cash_on_delivery_clearance_list;
	drop table Cash_on_delivery_clearance;	
	drop table Reviews;
    
    drop table Customer_refund_pay;
    drop table Customer_payment_by_wallet;
    drop table Customer_refund_ref;
    drop table Customer_wallet_accounts;
    
	drop table Customer_bank_slip_payments;
	drop table Customer_online_payments;
	drop table Customer_cash_payments;
	drop table Pay_of_B2B_orders;
	drop table Customer_payments;
	drop table Delivery_list;
	drop table Packaging_ref;
	drop table Packaging;
    drop table Order_item_cancels;
    drop table Order_item_returns;
	drop table Order_Fails;
	drop table Order_Cancels;
	drop table Order_OTP;
	drop table Order_adjustments;
	drop table Order_items;
	drop table Orders;
	drop table Shopping_carts;
	drop table Wishlist;
    drop table Order_rules;
/* .. */

drop table Direct_sales_details;
drop table Direct_sales;

/* Stocks data tables */
	drop table Quality_downgrade_records_details;
	drop table Quality_downgrade_records;
	drop table Waste_products_details;
	drop table Waste_products;
	drop table Stock_Transfer_details;
	drop table Stock_Transfers;
	drop table Stocks;
	drop table Houses;
/* .. */

drop table Collect_n_buy_ref;
drop table Collect_land_ref;
drop table Collect_n_buy;
drop table Farmers_payments ;
drop table Price_analyze_details;
drop table Price_analyze_buying_list;
drop table Price_analyze;
drop table Buying_agreement_details  ;
drop table Buying_agreements;

/*..Production monitoring data tables..*/
	drop table Pre_grading;
	drop table Pre_Harvests;
	drop table Disease_and_treatment;
	drop table Soil_test;
	drop table Irrigation;
	drop table Fertilization;
	drop table Assign_lands;
/* .. */

drop table Productions;

/* .. products type and details data tables ..*/
	drop table Price_update;
	drop table Products_ref;
	drop table Sub_id_details;
	drop table Products;
	drop table Category_relation;
	drop table Categories;
/* .. */

drop table Land_tracer ;
drop table Land_areas;
drop table Trip_drivers_payments;
drop table Trip_drivers;
drop table Trips;
drop table Assign_agents;
drop table Collection_points;

/* ERP accounts all data tables  */
	drop table Budget_expense_details;
	drop table Budget_expenses;
	drop table IOU_transfer_clearances;
	drop table IOU_Indirect_expense_clearances;
	drop table IOU_clearance;
	drop table Direct_expenses_n_transactions;
    drop table Indirect_expense_ref;
	drop table Expenses;
	drop table Expense_category_list;
	drop table Assign_cash_counter;
	drop table Cash_counters;
    drop table Online_transaction_details;
	drop table ERP_transactions;
	drop table Transaction_types;
/* .. */
drop table ERP_bank;
drop table Drivers;
drop table Users;