<?php
require 'bootstrap/app.php';
$app = app();

echo "Stock items with expiry date: ";
var_dump(StockInItem::whereNotNull('expiry_date')->count());

echo "Today's date: ";
var_dump(Carbon\Carbon::today()->toDateString());

echo "Clearance items: ";
var_dump(ClearanceItem::count());
?>
