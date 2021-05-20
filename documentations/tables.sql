drop database agri_erp_solution;
create database agri_erp_solution;

create table Users (
    id INT unsigned AUTO_INCREMENT,
    user_type varchar(30) NOT NULL,
    password varchar(30) NOT NULL,
    first_name varchar(30) NOT NULL,
    last_name varchar(30),
    email varchar(50),
    region varchar(30) NOT NULL,
    city varchar(30) NOT NULL,
    area varchar(30) NOT NULL,
    address varchar(100) NOT NULL,
    mobile varchar(11) NOT NULL,
    photo_url varchar(100),
    register_on DATETIME NOT NULL,
    PRIMARY KEY (id)   
);

create table Drivers (
    driver_id INT unsigned NOT NULL,
    vehicle_no varchar(15) NOT NULL,
    entry_by int unsigned NOT NULL,
    entry_on DATETIME NOT NULL,
    foreign key(driver_id) references Users(id),
    foreign key(entry_by) references Users(id)  
);
/* ERP accounts all data tables  */
   
    create table ERP_bank(
        bank_id tinyint unsigned,
        account_no INT unsigned NOT NULL,
        bank_name varchar(20) NOT NULL,
        branch_name varchar(20),
        account_holder varchar(30) NOT NULL,
        balance_amount Decimal(10,2) NOT NULL,
        is_active bit(1) not null,
        created_by INT unsigned NOT NULL,
        create_on DATETIME NOT NULL,
        PRIMARY KEY (bank_id),
        UNIQUE (account_no,bank_name),
        foreign key(created_by) references Users(id)
    );

    create table Cash_counters(
        cash_counter_id tinyint unsigned AUTO_INCREMENT,
        cash_counter_name varchar(30) NOT NULL,
        balance Decimal(10,2) NOT NULL,
        is_active bit not null,
        created_by INT unsigned NOT NULL,
        create_on DATETIME NOT NULL,
        PRIMARY KEY (cash_counter_id),
        foreign key(created_by) references Users(id)
    );

    create table Assign_cash_counter(
        cash_counter_id tinyint unsigned NOT NULL,
        user_id INT unsigned NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        UNIQUE (cash_counter_id),
        foreign key(cash_counter_id) references Cash_counters(cash_counter_id),
        foreign key(user_id) references Users(id),
        foreign key(entry_by) references Users(id)
    );

     create table Transaction_types(
        transaction_type_id tinyint unsigned AUTO_INCREMENT,
        transaction_type varchar(8) NOT NULL,
        transaction_way varchar(30) NOT NULL,
        PRIMARY KEY (transaction_type_id),
        CHECK(transaction_type IN ('Withdraw',
                                'Deposit',
                                'Pay',
                                'Pay refund',
                                'Receive',
                                'Transfer',
                                'Return')),
        CHECK(transaction_way IN ('Bank to Bank',
                                'Bank to User',
                                'User to Bank',
                                'Bank to cash counter',
                                'Cash counter to Bank'
                                'Cash counter to Cash counter',
                                'Cash counter to user',
                                'User to cash counter'))
    );

    create table ERP_transactions(
        erp_transaction_id BIGINT unsigned AUTO_INCREMENT,
        transaction_type_id tinyint unsigned NOT NULL,
        transaction_from INT unsigned NOT NULL,
        transaction_to INT unsigned NOT NULL,
        transaction_amount Decimal(10,2) NOT NULL,
        transaction_date DATE NOT NULL,
        slip_type varchar(20),
        slip varchar(100),
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        PRIMARY KEY (erp_transaction_id),
        foreign key (transaction_type_id) references Transaction_types(transaction_type_id),
        foreign key (entry_by) references Users(id)
    );

    create table Online_transaction_details(
        online_transaction_id varchar(30) NOT NULL,
        erp_transaction_id BIGINT unsigned NOT NULL,
        user_bank_name varchar(20) DEFAULT NULL,
        user_account_no INT unsigned DEFAULT NULL,
        transaction_charge decimal(10,2) NOT NULL,
        UNIQUE (online_transaction_id,erp_transaction_id),
        foreign key (erp_transaction_id) references ERP_transactions(erp_transaction_id)
    );

    create table Expense_category_list(
        expense_cat_id smallint unsigned AUTO_INCREMENT,
        expense_type varchar(10) NOT NULL,
        expense_time varchar(20) NOT NULL,
        expense_name varchar(30) NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        PRIMARY KEY (expense_cat_id),
        foreign key(entry_by) references Users(id),
        CHECK (expense_type IN ("Fixed", "Variable")),
        CHECK (expense_time IN ("Daily", "Monthly","Yearly"))
    );

    create table Expenses(
        expense_id INT unsigned AUTO_INCREMENT,
        expense_cat_id SMALLINT UNSIGNED not null,
        amount Decimal(10,2) NOT NULL,
        bill_copy varchar(100),
        comments varchar(100),
        is_direct_expense bit(1) NOT NULL,
        PRIMARY KEY (expense_id),
        foreign key(expense_cat_id) references Expense_category_list(expense_cat_id)
    );
    
    create table Indirect_expense_ref (
        expense_id INT unsigned NOT NULL,
        spender_id INT unsigned NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        UNIQUE (expense_id),
        foreign key(expense_id) references Expenses(expense_id),
        foreign key(spender_id) references Users(id),
        foreign key(entry_by) references Users(id)
    );

    create table Direct_expenses_n_transactions(
        pay_erp_transaction_id BIGINT unsigned NOT NULL,
        expense_id INT unsigned NOT NULL,
        UNIQUE (pay_erp_transaction_id,expense_id),
        foreign key(pay_erp_transaction_id) references ERP_transactions(erp_transaction_id),
        foreign key(expense_id) references Expenses(expense_id)
    );

    create table IOU_clearance(
        cash_clearance_id BIGINT unsigned AUTO_INCREMENT,
        employee_id INT unsigned NOT NULL,
        accountant_id INT unsigned NOT NULL,
        cash_counter_id tinyint unsigned NOT NULL,
        cash_return_erp_transaction_id BIGINT unsigned,
        clearance_on DATETIME NOT NULL,
        PRIMARY KEY (cash_clearance_id),
        foreign key(employee_id) references Users(id),
        foreign key(accountant_id) references Users(id),
        foreign key(cash_counter_id) references Cash_counters(cash_counter_id)
    );

    create table IOU_Indirect_expense_clearances(
        cash_clearance_id BIGINT unsigned NOT NULL,
        expense_id INT unsigned NOT NULL,
        UNIQUE (cash_clearance_id,expense_id),   
        foreign key (cash_clearance_id) references IOU_clearance(cash_clearance_id),     
        foreign key(expense_id) references Expenses(expense_id)
    );

    create table IOU_transfer_clearances(    
        cash_clearance_id BIGINT unsigned NOT NULL,
        transfer_erp_transaction_id BIGINT unsigned NOT NULL,
        UNIQUE (cash_clearance_id,transfer_erp_transaction_id),
        foreign key(cash_clearance_id) references IOU_clearance(cash_clearance_id),        
        foreign key(transfer_erp_transaction_id) references ERP_transactions(erp_transaction_id)
    );

    create table Budget_expenses(
        budget_id INT unsigned AUTO_INCREMENT,
        budget_on_date date not null,
        date_from date not null,
        date_to date not null,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        PRIMARY KEY (budget_id),
        foreign key(entry_by) references Users(id)
    );

    create table Budget_expense_details(
        budget_id INT unsigned NOT NULL,
        expense_cat_id smallint unsigned not null,
        expense_budget Decimal(10,2) NOT NULL,
        UNIQUE (budget_id,expense_cat_id),
        foreign key(budget_id) references Budget_expenses(budget_id),
        foreign key(expense_cat_id) references Expense_category_list(expense_cat_id)
    );
/*..*/

create table Collection_points(
    collection_point_id INT unsigned AUTO_INCREMENT,
    area varchar(30) NOT NULL,
    address varchar(100) NOT NULL,
    entry_by INT unsigned NOT NULL,
    entry_on DATETIME NOT NULL,
    PRIMARY KEY (collection_point_id),
    foreign key(entry_by) references Users(id)
);    

create table Assign_agents(
    collection_point_id INT unsigned NOT NULL,
    agent_id INT unsigned NOT NULL,
    entry_by INT unsigned NOT NULL,
    entry_on DATETIME NOT NULL,    
    UNIQUE (collection_point_id,agent_id),
    foreign key(collection_point_id) references Collection_points(collection_point_id),
    foreign key(agent_id) references Users(id),
    foreign key(entry_by) references Users(id)
);

create table Trips(
    trip_id BIGINT unsigned AUTO_INCREMENT,
    collection_point_id INT unsigned NOT NULL,
    transportation_cost Decimal(10,2) NOT NULL,
    other_cost Decimal(10,2) NOT NULL,
    trip_date DATE NOT NULL,
    entry_by INT unsigned NOT NULL,
    entry_on DATETIME NOT NULL,
    status tinyint(2) unsigned NOT NULL,
    PRIMARY KEY (trip_id),
    foreign key(collection_point_id) references Collection_points(collection_point_id),
    foreign key(entry_by) references Users(id)
);                            

create table Trip_drivers (
    trip_id BIGINT unsigned NOT NULL,
    driver_id INT unsigned NOT NULL,
    payment_amount Decimal(10,2) NOT NULL,
    is_present bit(1) NOT NULL,
    entry_by INT unsigned NOT NULL,
    entry_on DATETIME NOT NULL,
    UNIQUE (trip_id,driver_id),
    foreign key(trip_id) references Trips(trip_id),
    foreign key(driver_id) references Users(id),
    foreign key(entry_by) references Users(id)
);                            

create table Trip_drivers_payments (
    trip_id BIGINT unsigned NOT NULL,
    driver_id INT unsigned NOT NULL,
    pay_erp_transaction_id BIGINT unsigned NOT NULL,
    UNIQUE (trip_id,driver_id,pay_erp_transaction_id),
    foreign key(trip_id) references Trips(trip_id),
    foreign key(driver_id) references Users(id),
    foreign key(pay_erp_transaction_id) references ERP_transactions(erp_transaction_id)
);

create table Land_areas(
    land_id INT unsigned AUTO_INCREMENT,
    area varchar(30) NOT NULL,
    land_owner varchar(30) NOT NULL,
    location varchar(100) NOT NULL,
    cultivate_land_measurement Decimal(10,2) NOT NULL,
    farmer_id INT unsigned NOT NULL,
    entry_by INT unsigned NOT NULL,
    entry_on datetime not null,
    PRIMARY KEY (land_id),
    foreign key(farmer_id) references Users(id),
    foreign key(entry_by) references Users(id)
);

create table Land_tracer (
    land_tracer_id INT unsigned AUTO_INCREMENT,
    land_id INT unsigned NOT NULL,
    updated_by INT unsigned NOT NULL,
    updated_on DATETIME NOT NULL,
    primary key (land_tracer_id),
    foreign key(land_id) references Land_areas(land_id),
    foreign key(updated_by) references Users(id)
);

/* .. products type and details data tables ..*/
    create table Categories(
        cat_id tinyint unsigned AUTO_INCREMENT,
        name varchar(50) NOT NULL,
        image_url varchar(100) NOT NULL,
        PRIMARY KEY (cat_id)
    );

    create table Category_relation(
        parent_cat_id tinyint unsigned NOT NULL,
        child_cat_id tinyint unsigned NOT NULL,
        UNIQUE (parent_cat_id, child_cat_id),
        foreign key(parent_cat_id) references Categories(cat_id),
        foreign key(child_cat_id) references Categories(cat_id)
    );

    create table Products(
        product_id SMALLINT unsigned AUTO_INCREMENT,
        cat_id tinyint unsigned NOT NULL,
        name varchar(50) NOT NULL,
        description varchar(100),
        product_image varchar(100) NOT NULL,
        short_video varchar(100),
        default_unit varchar(10) NOT NULL,
        PRIMARY KEY (product_id),
        foreign key(cat_id) references Categories(cat_id)
    );

    create table Sub_id_details(
        sub_id tinyint unsigned AUTO_INCREMENT,
        size varchar(6) NOT NULL,
        grade tinyint(3) unsigned NOT NULL,
        unit varchar(10) NOT NULL,
        sales_place varchar(10) NOT NULL,
        PRIMARY KEY (sub_id),
        CHECK (size IN ("Large", "Medium", "Small","None")),
        CHECK (sales_place IN ("B2B Online",
                                "Outlet",
                                "B2C Online"))
    );

    create table Products_ref(
        product_code mediumint unsigned AUTO_INCREMENT,
        product_id smallint unsigned NOT NULL,
        sub_id tinyint unsigned NOT NULL,
        sales_price DECIMAL(10,2) NOT NULL,
        weight_range varchar(20),
        is_available bit(1) NOT NULL,
        PRIMARY KEY (product_code),
        UNIQUE (product_id, sub_id),
        foreign key(product_id) references Products(product_id),
        foreign key(sub_id) references Sub_id_details(sub_id)
    );    

    create table Price_update(
        update_id BIGINT unsigned AUTO_INCREMENT,
        product_code mediumint unsigned Not NULL,
        last_sales_price DECIMAL(10,2) NOT NULL,
        updated_by INT unsigned NOT NULL,
        updated_on DATETIME NOT NULL,
        PRIMARY KEY (update_id),
        foreign key(product_code) references Products_ref(product_code),        
        foreign key(updated_by) references Users(id)
    );

/*..*/

create table Productions(
    production_id BIGINT unsigned AUTO_INCREMENT,
    farmer_id INT unsigned NOT NULL,
    product_id smallint unsigned NOT NULL,
    cultivation_qt Decimal(10,2) NOT NULL,
    unit varchar(10) NOT NULL,
    cultivation_date DATE NOT NULL,
    probable_harvest_qt Decimal(10,2) NOT NULL,
    probable_harvest_date DATE NOT NULL,
    entry_by INT unsigned NOT NULL,
    entry_on DATETIME NOT NULL,    
    is_fully_harvested bit(1) NOT NULL,
    PRIMARY KEY (production_id),
    foreign key(farmer_id) references Users(id),    
    foreign key(product_id) references Products(product_id),
    foreign key(entry_by) references Users(id)
);

/*..Production monitoring data tables..*/
    create table Assign_lands(
        production_id BIGINT unsigned NOT NULL,
        land_id INT unsigned NOT NULL,
        UNIQUE (production_id,land_id),
        foreign key(production_id) references Productions(production_id),   
        foreign key(land_id) references Land_areas(land_id) 
    );

    create table Fertilization(
        f_id INT unsigned AUTO_INCREMENT,
        assign_land_id INT unsigned NOT NULL,
        fertilizer_qt Decimal(10,2) NOT NULL,
        fertilization_day DATE NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        PRIMARY KEY (f_id),
        foreign key(assign_land_id) references Assign_lands(land_id), 
        foreign key(entry_by) references Users(id)
    );

    create table Irrigation(
        r_id INT unsigned AUTO_INCREMENT,
        assign_land_id INT unsigned NOT NULL,
        irrigation_day DATE NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        PRIMARY KEY (r_id),
        foreign key(assign_land_id) references Assign_lands(land_id), 
        foreign key(entry_by) references Users(id)
    );

    create table Soil_test(
        soil_test_id INT unsigned AUTO_INCREMENT,
        assign_land_id INT unsigned NOT NULL,
        test_day DATE NOT NULL,
        test_result varchar(100) ,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        PRIMARY KEY (soil_test_id),
        foreign key(assign_land_id) references Assign_lands(land_id), 
        foreign key(entry_by) references Users(id)
    );

    create table Disease_and_treatment(
        DT_id INT unsigned AUTO_INCREMENT,
        assign_land_id INT unsigned NOT NULL,
        treatment_type varchar(30) NOT NULL,
        disease_name varchar(30) NOT NULL,
        disease_day DATE NOT NULL,
        treatment_name varchar(30) NOT NULL,
        treatment_qt Decimal(10,2) NOT NULL,
        treatment_day DATE NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        PRIMARY KEY (DT_id),
        foreign key(assign_land_id) references Assign_lands(land_id), 
        foreign key(entry_by) references Users(id)
    );

    create table Pre_Harvests(
        pre_harvest_id BIGINT unsigned AUTO_INCREMENT,
        production_id BIGINT unsigned NOT NULL,
        expected_harvest_qt Decimal(10,2) NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        PRIMARY KEY (pre_harvest_id),
        foreign key(production_id) references Productions(production_id),
        foreign key(entry_by) references Users(id)
    );

    create table Pre_grading(
        pre_grading_id BIGINT unsigned AUTO_INCREMENT,
        pre_harvest_id BIGINT unsigned NOT NULL,
        grader_id INT unsigned NOT NULL,
        size varchar(10) NOT NULL,
        grading_before_collection INT unsigned NOT NULL,
        expected_harvest_qt Decimal(10,2) NOT NULL,
        PRIMARY KEY(pre_grading_id),
        foreign key(pre_harvest_id) references Pre_Harvests(pre_harvest_id),
        foreign key(grader_id) references Users(id)
    );    
/* .. */

create table Buying_agreements(
    buy_id BIGINT unsigned AUTO_INCREMENT,
    production_id BIGINT unsigned NOT NULL,
    farmer_id INT unsigned NOT NULL,
    collection_point_id INT unsigned NOT NULL,
    due Decimal(10,2) NOT NULL,
    status tinyint(2) unsigned NOT NULL,
    agreement_date DATE NOT NULL,
    created_by INT unsigned NOT NULL,
    created_on DATETIME NOT NULL,
    PRIMARY KEY (buy_id),
    foreign key(production_id) references Productions(production_id),
    foreign key(farmer_id) references Users(id),
    foreign key(collection_point_id) references Collection_points(collection_point_id),
    foreign key(created_by) references Users(id)
);

create table Buying_agreement_details(
    buy_id BIGINT unsigned NOT NULL,
    sub_id tinyint unsigned Not NULL,
    qt Decimal(10,2) NOT NULL,
    buy_price DECIMAL(10,2) NOT NULL,
    carrying_unit_cost Decimal(10,2) NOT NULL,
    UNIQUE (buy_id,sub_id),
    foreign key(buy_id) references Buying_agreements(buy_id),
    foreign key(sub_id) references Sub_id_details(sub_id)
);    

create table Price_analyze(
    price_analyze_id BIGINT unsigned AUTO_INCREMENT,
    daily_operating_budget_cost Decimal(10,2) NOT NULL,
    daily_delivery_cost DECIMAL(10,2) NOT NULL,
    safe_predemand_qt_percentage INT unsigned NOT NULL,
    profit_level_percentage INT unsigned NOT NULL,
    entry_by INT unsigned NOT NULL,
    entry_on DATETIME NOT NULL,
    PRIMARY KEY (price_analyze_id),
    foreign key(entry_by) references Users(id)
);

create table Price_analyze_buying_list(
    price_analyze_id BIGINT unsigned NOT NULL,
    buy_id BIGINT unsigned NOT NULL,
    UNIQUE (price_analyze_id,buy_id),
    foreign key(price_analyze_id) references Price_analyze(price_analyze_id),
    foreign key(buy_id) references Buying_agreements(buy_id)
);

create table Price_analyze_details(
    price_analyze_id BIGINT unsigned NOT NULL,
    product_code mediumint unsigned Not NULL,
    estimated_qt Decimal(10,2) NOT NULL,
    average_buying_price DECIMAL(10,2) NOT NULL,
    profitless_price DECIMAL(10,2) NOT NULL,
    profitable_price Decimal(10,2) NOT NULL,
    generated_sales_price Decimal(10,2) NOT NULL,
    custom_sales_price Decimal(10,2) NOT NULL,
    UNIQUE (price_analyze_id,product_code),
    foreign key(price_analyze_id) references Price_analyze(price_analyze_id),
    foreign key(product_code) references Products_ref(product_code)
);

create table Farmers_payments(
    pay_erp_transaction_id BIGINT unsigned NOT NULL,
    buy_id BIGINT unsigned NOT NULL,
    pay_amount Decimal(10,2) NOT NULL,
    UNIQUE (pay_erp_transaction_id, buy_id),
    foreign key(pay_erp_transaction_id) references ERP_transactions(erp_transaction_id),
    foreign key(buy_id) references Buying_agreements(buy_id)
);

create table Collect_n_buy(
    collection_code BIGINT unsigned AUTO_INCREMENT,
    product_id SMALLINT unsigned NOT NULL,
    buy_id BIGINT unsigned NOT NULL,    
    entry_by INT unsigned NOT NULL,
    entry_on DATETIME NOT NULL,
    status tinyint(2) unsigned NOT NULL,
    PRIMARY KEY (collection_code),
    UNIQUE (collection_code,buy_id),
    foreign key(product_id) references Products(product_id),
    foreign key(buy_id) references Buying_agreements(buy_id),
    foreign key(entry_by) references Users(id)
);

create table Collect_land_ref (
    collection_code BIGINT unsigned NOT NULL,
    assign_land_id INT unsigned NOT NULL,
    collected_harvest_qt Decimal(10,2) NOT NULL,
    on_harvest_waste_qt Decimal(10,2) NOT NULL,
    unique(collection_code, assign_land_id),
    foreign key(collection_code) references Collect_n_buy(collection_code),
    foreign key(assign_land_id) references Land_areas(land_id)
);

create table Collect_n_buy_ref(
    collection_code BIGINT unsigned NOT NULL,
    sub_id tinyint unsigned Not NULL,
    qt Decimal(10,2) NOT NULL,
    extra_qt Decimal(10,2) NOT NULL,
    crates TINYINT UNSIGNED NOT NULL,
    carrying_unit_cost Decimal(10,2) NOT NULL,
    UNIQUE (collection_code,sub_id),
    foreign key(collection_code) references Collect_n_buy(collection_code),    
    foreign key(sub_id) references Sub_id_details(sub_id)
);

/* Stocks data tables */
    create table Houses(
        house_id smallint unsigned AUTO_INCREMENT,
        name varchar(30) NOT NULL,
        house_type varchar(30) NOT NULL,
        area varchar(30) NOT NULL,
        address varchar(50) NOT NULL,
        mobile varchar(11) NOT NULL,
        created_by INT unsigned NOT NULL,
        create_on DATETIME NOT NULL,
        PRIMARY KEY (house_id)
    );

    create table Stocks(
        house_id smallint unsigned NOT NULL,
        product_id smallint unsigned NOT NULL,
        collection_code BIGINT unsigned NOT NULL,
        sub_id tinyint unsigned Not NULL,
        qt Decimal(10,2) NOT NULL,
        UNIQUE (house_id,product_id,collection_code,sub_id),
        foreign key(house_id) references Houses(house_id),
        foreign key(product_id) references Products(product_id),
        foreign key(collection_code) references Collect_n_buy(collection_code),
        foreign key(sub_id) references Sub_id_details(sub_id)
    );

    create table Stock_Transfers(
        transfer_id INT unsigned AUTO_INCREMENT,
        from_house_id smallint unsigned NOT NULL,
        to_house_id smallint unsigned NOT NULL,
        transfer_by INT unsigned NOT NULL,
        transfer_on DATETIME NOT NULL,
        PRIMARY KEY (transfer_id),
        foreign key(from_house_id) references Houses(house_id),
        foreign key(to_house_id) references Houses(house_id),
        foreign key(transfer_by) references Users(id)
    );

    create table Stock_Transfer_details(
        transfer_id INT unsigned NOT NULL,
        collection_code BIGINT unsigned NOT NULL,
        sub_id tinyint unsigned Not NULL,
        qt Decimal(10,2) NOT NULL,
        UNIQUE (transfer_id,collection_code,sub_id),
        foreign key(transfer_id) references Stock_Transfers(transfer_id),
        foreign key(collection_code) references Collect_n_buy(collection_code),
        foreign key(sub_id) references Sub_id_details(sub_id)
    );

    create table Waste_products(
        waste_product_id INT unsigned AUTO_INCREMENT,
        house_id smallint unsigned NOT NULL,
        total_wastage Decimal(10,2) NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        PRIMARY KEY(waste_product_id),
        foreign key(house_id) references Houses(house_id),
        foreign key(entry_by) references Users(id)
    );

    create table Waste_products_details(
        waste_product_id INT unsigned NOT NULL,
        collection_code BIGINT unsigned NOT NULL,
        sub_id tinyint unsigned Not NULL,
        qt Decimal(10,2) NOT NULL,
        UNIQUE(waste_product_id,collection_code,sub_id),
        foreign key(waste_product_id) references Waste_products(waste_product_id),
        foreign key(collection_code) references Collect_n_buy(collection_code),
        foreign key(sub_id) references Sub_id_details(sub_id)
    );

    create table Quality_downgrade_records(
        quality_downgrade_record_id INT unsigned AUTO_INCREMENT,
        house_id smallint unsigned NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        PRIMARY KEY(quality_downgrade_record_id),
        foreign key(house_id) references Houses(house_id),
        foreign key(entry_by) references Users(id)
    );

    create table Quality_downgrade_records_details(
        quality_downgrade_record_id INT unsigned NOT NULL,
        collection_code BIGINT unsigned NOT NULL,
        last_sub_id tinyint unsigned NOT NULL,
        current_sub_id tinyint unsigned NOT NULL,
        downgraded_qt Decimal(10,2) NOT NULL,
        UNIQUE(quality_downgrade_record_id,collection_code,last_sub_id,current_sub_id),
        foreign key(collection_code) references Collect_n_buy(collection_code),
        foreign key(last_sub_id) references Sub_id_details(sub_id),
        foreign key(current_sub_id) references Sub_id_details(sub_id)
    );

/* .. */

create table Direct_sales(
    sales_id BIGINT unsigned AUTO_INCREMENT,
    shop_id smallint unsigned NOT NULL,
    total_payment DECIMAL(10,2) NOT NULL,       
    entry_by INT unsigned NOT NULL,
    entry_on DATETIME NOT NULL,
    PRIMARY KEY (sales_id),
    foreign key(shop_id) references Houses(house_id),
    foreign key(entry_by) references Users(id)
);

create table Direct_sales_details(
    sales_id BIGINT unsigned NOT NULL,
    product_code MEDIUMINT unsigned NOT NULL,
    qt DECIMAL(10,2) NOT NULL,
    unit_sales_price DECIMAL(10,2) NOT NULL,
    UNIQUE (sales_id,product_code),
    foreign key(sales_id) references Direct_sales(sales_id),
    foreign key(product_code) references Products_ref(product_code)
);

/*.. Online orders all data tables */

    create table Order_rules(
        product_id smallint unsigned NOT NULL,
        minimum_qt decimal(10,2) NOT NULL,
        maximum_qt decimal(10,2) NOT NULL,
        start DATETIME NOT NULL,
        end DATETIME NOT NULL,
        entry_by int unsigned NOT NULL,
        entry_on datetime NOT NULL,
        UNIQUE (product_id),
        foreign key(product_id) references Products(product_id),
        foreign key(entry_by) references Users(id)
    );
    
    create table Wishlist(
        customer_id INT unsigned NOT NULL,
        product_code mediumint unsigned Not NULL,
        qt DECIMAL(10,2) NOT NULL,
        entry_on datetime not null,
        UNIQUE (customer_id,product_code),
        foreign key(customer_id) references Users(id),
        foreign key(product_code) references Products_ref(product_code)
    );

    create table Shopping_carts(
        customer_id INT unsigned NOT NULL,
        product_code mediumint unsigned Not NULL,
        qt decimal(10,2) NOT NULL,
        create_on DATETIME NOT NULL,
        UNIQUE (customer_id,product_code),
        foreign key(customer_id) references Users(id),
        foreign key(product_code) references Products_ref(product_code)
    );

    create table Orders(
        order_id BIGINT unsigned AUTO_INCREMENT,
        customer_id INT unsigned NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        due Decimal(10,2) NOT NULL,
        delivery_charge Decimal(10,2) NOT NULL,
        created_on DATETIME NOT NULL,
        status tinyint(6) unsigned NOT NULL,
        comments varchar(50),
        time_slot varchar(30) NOT NULL,
        customer_mobile varchar(11) NOT NULL,
        delivery_on DATETIME,
        PRIMARY KEY (order_id),
        foreign key(customer_id) references Users(id)     
    );                         

    create table Order_items(
        order_track_id BIGINT unsigned AUTO_INCREMENT,
        order_id BIGINT unsigned NOT NULL,
        product_code mediumint unsigned Not NULL,
        qt Decimal(10,2) NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        status tinyint(2) unsigned NOT NULL,    
        primary key(order_track_id),
        foreign key(order_id) references Orders(order_id),
        foreign key(product_code) references Products_ref(product_code),
        UNIQUE (order_id, product_code)
    );    

    create table Order_adjustments(
        order_track_id BIGINT unsigned NOT NULL,
        adjust_qt_in_default_unit Decimal(10,2) NOT NULL,
        UNIQUE(order_track_id),
        foreign key(order_track_id) references Order_items(order_track_id)
    );   

    create table Order_OTP(
        order_id BIGINT unsigned NOT NULL,
        otp_number smallint unsigned NOT NULL,
        generate_on DATETIME NOT NULL,
        is_otp_used bit(1) NOT NULL,
        UNIQUE (order_id, otp_number),
        foreign key(order_id) references Orders(order_id)
    );

    create table Order_Cancels (
        order_id BIGINT unsigned NOT NULL,
        cancellation_type varchar(15) NOT NULL,
        reason varchar(50),
        entry_on DATETIME NOT NULL,
        UNIQUE (order_id),
        foreign key(order_id) references Orders(order_id)
    );

    create table Order_Fails (
        order_id BIGINT unsigned NOT NULL,
        fail_type varchar(15) NOT NULL,
        reason varchar(50),
        entry_by int unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        UNIQUE (order_id),
        foreign key(order_id) references Orders(order_id),
        foreign key(entry_by) references Users(id)
    );
    create table Order_item_returns(
        order_track_id BIGINT unsigned NOT NULL,
        return_type varchar(15) NOT NULL,
        return_qt Decimal(10,2) NOT NULL,
        reason varchar(50),
        status bit(1) NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        foreign key(order_track_id) references Order_items(order_track_id),        
        foreign key(entry_by) references Users(id)
    );

    create table Order_item_cancels(
        order_track_id BIGINT unsigned NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        UNIQUE (order_track_id),
        foreign key(order_track_id) references Order_items(order_track_id),        
        foreign key(entry_by) references Users(id)
    );

    create table Packaging(
        order_id BIGINT unsigned NOT NULL,
        packaged_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        UNIQUE (order_id),
        foreign key(order_id) references Orders(order_id),
        foreign key(packaged_by) references Users(id)
    );    

    create table Packaging_ref(
        order_track_id BIGINT unsigned NOT NULL,
        house_id smallint unsigned NOT NULL,
        collection_code BIGINT unsigned NOT NULL,
        qt_in_default_unit decimal(10,2) NOT NULL,
        UNIQUE (order_track_id,house_id,collection_code),
        foreign key(order_track_id) references Order_items(order_track_id),
        foreign key(house_id) references Houses(house_id),
        foreign key(collection_code) references Collect_n_buy(collection_code)
    ); 

    create table Delivery_list(
        delivery_man_id INT unsigned NOT NULL,
        order_id BIGINT unsigned NOT NULL,
        assign_by INT unsigned NOT NULL,
        assign_on DATETIME NOT NULL,
        UNIQUE (delivery_man_id,order_id),
        foreign key(delivery_man_id) references Users(id),
        foreign key(order_id) references Orders(order_id),
        foreign key(assign_by) references Users(id)
    );    

    create table Customer_payments(
        invoice_id BIGINT unsigned AUTO_INCREMENT,
        customer_id INT unsigned NOT NULL,
        payment_method varchar(9) NOT NULL,
        customer_pay_amount Decimal(10,2) NOT NULL,
        invoice_status varchar(10) NOT NULL,
        PRIMARY KEY (invoice_id),
        foreign key(customer_id) references Users(id),
        CHECK(payment_method in ('Cash','Online','Bank slip','None')),
        CHECK(invoice_status in ('Processing','Paid','Failed'))
    );

    create table Pay_of_B2B_orders(
        invoice_id BIGINT unsigned NOT NULL,
        order_id BIGINT unsigned NOT NULL,
        pay Decimal(10,2) NOT NULL,
        UNIQUE (invoice_id, order_id),
        foreign key(invoice_id) references Customer_payments(invoice_id),
        foreign key(order_id) references Orders(order_id)
    );

    create table Customer_cash_payments(
        invoice_id BIGINT unsigned NOT NULL,
        delivery_man_id INT unsigned NOT NULL,
        entry_by INT unsigned NOT NULL,
        entry_on DATETIME NOT NULL,
        verify_by INT unsigned DEFAULT NULL,
        verify_on DATETIME,
        UNIQUE (invoice_id),
        foreign key(invoice_id) references Customer_payments(invoice_id),
        foreign key(delivery_man_id) references Users(id),
        foreign key(entry_by) references Users(id)
    );

    create table Customer_online_payments(
        invoice_id BIGINT unsigned NOT NULL,
        receive_erp_transaction_id BIGINT unsigned NOT NULL,
        UNIQUE (invoice_id,receive_erp_transaction_id),
        foreign key(invoice_id) references Customer_payments(invoice_id),
        foreign key(receive_erp_transaction_id) references ERP_transactions(erp_transaction_id)
    );

    create table Customer_bank_slip_payments(
        invoice_id BIGINT unsigned NOT NULL,
        slip varchar(100) NOT NULL,
        verify_by INT unsigned NOT NULL,
        verify_on DATETIME NOT NULL,
        UNIQUE (invoice_id),
        foreign key(invoice_id) references Customer_payments(invoice_id),
        foreign key(verify_by) references Users(id)
    );

    create table Customer_wallet_accounts(
        wallet_account_id INT unsigned AUTO_INCREMENT,
        customer_id INT unsigned NOT NULL,
        wallet_balance decimal(10,2) NOT NULL,
        create_on datetime not null,
        PRIMARY KEY (wallet_account_id),
        foreign key(customer_id) references Users(id)
    );
    
    create table Customer_refund_ref(
        wallet_account_id INT unsigned NOT NULL,
        order_id BIGINT unsigned NOT NULL,
        refund_amount decimal(10,2) NOT NULL,
        UNIQUE (wallet_account_id,order_id),
        foreign key(wallet_account_id) references Customer_wallet_accounts(wallet_account_id),
        foreign key(order_id) references Orders(order_id)
    );

    create table Customer_payment_by_wallet(
        wallet_account_id INT unsigned NOT NULL,
        invoice_id BIGINT unsigned NOT NULL,
        wallet_balance_use decimal(10,2) NOT NULL,
        UNIQUE (wallet_account_id,invoice_id),        
        foreign key(wallet_account_id) references Customer_wallet_accounts(wallet_account_id),
        foreign key(invoice_id) references Customer_payments(invoice_id)
    );

    create  table Customer_refund_pay(
        wallet_account_id INT unsigned NOT NULL,
        wallet_erp_transaction_id BIGINT unsigned NOT NULL,
        unique (wallet_account_id,wallet_erp_transaction_id),
        foreign key (wallet_account_id) references Customer_wallet_accounts(wallet_account_id),
        foreign key(wallet_erp_transaction_id) references ERP_transactions(erp_transaction_id)
    );

    create table Reviews(
        order_id BIGINT unsigned NOT NULL,
        product_code mediumint unsigned Not NULL,
        review varchar(50) NOT NULL,
        rating tinyint(5) unsigned NOT NULL,
        created_on DATETIME NOT NULL,
        unique (order_id,product_code),
        foreign key(order_id) references Orders(order_id),
        foreign key(product_code) references Products_ref(product_code)
    );

    create table Cash_on_delivery_clearance(
        invoice_clearance_id BIGINT unsigned AUTO_INCREMENT,
        cash_receive_erp_transaction_id BIGINT unsigned NOT NULL,
        PRIMARY KEY (invoice_clearance_id),
        foreign key(cash_receive_erp_transaction_id) references ERP_transactions(erp_transaction_id)
    );

    create table Cash_on_delivery_clearance_list(
        invoice_clearance_id BIGINT unsigned NOT NULL,
        invoice_id BIGINT unsigned NOT NULL,
        UNIQUE (invoice_clearance_id,invoice_id),
        foreign key(invoice_clearance_id) references Cash_on_delivery_clearance(invoice_clearance_id),
        foreign key(invoice_id) references Customer_payments(invoice_id)
    );    

/* .. */

