# CShop Retail Management System - Detailed Module Documentation

This document provides detailed step-by-step guidance for every module in the CShop system, explaining how to use each feature, what the system does behind the scenes, and the rules it follows.

---

## Table of Contents

1. [Dashboard](#dashboard)
2. [Products Management](#products-management)
3. [Stock In Management](#stock-in-management)
4. [Sales Management](#sales-management)
5. [Expenses Management](#expenses-management)
6. [Reports](#reports)
7. [Branch Management](#branch-management)
8. [User Management](#user-management)
9. [Role Management](#role-management)
10. [User Role Assignment](#user-role-assignment)
11. [Categories Management](#categories-management)
12. [Bulk Units & Types Management](#bulk-units--types-management)
13. [Audit Trails](#audit-trails)
14. [Alerts System](#alerts-system)

---

## Dashboard

### A) What You See on the Dashboard

The dashboard shows a quick overview of your business:

**KPI Cards (Key Performance Indicators):**
1. Total Sales (This Month)
   - Shows total revenue from all sales this month
   - Updates in real-time as sales are made
   - Shows amount in your local currency

2. Inventory Value
   - Total value of all products currently in stock
   - Calculated as: sum of (stock quantity × cost price) for all products
   - Helps you understand your investment in inventory

3. Low Stock Value
   - Total value of products running low
   - Helps prioritize what to reorder

**Quick Access Cards:**
Click any card to jump directly to that module:
- Products
- Categories
- Bulk Units & Types
- Stock In
- Sales

**Alerts Section:**
Shows recent alerts with:
- Alert type (stock adjustment, expired stock, expiry warning, low stock)
- Alert message
- When it was created
- Color-coded by urgency (red = urgent, yellow = warning, blue = info)
- "Mark as read" button

### B) How Dashboard Data Is Calculated

**Total Sales:**
- System sums all sales records for current month
- Includes both cash and card payments
- Updates automatically as sales are posted

**Inventory Value:**
- System calculates for each product: current_stock × cost_price
- Sums all product values
- Updates when stock changes (stock in, sales, adjustments)

**Low Stock Value:**
- System identifies products where current_stock < reorder_level
- Calculates value for each low stock product
- Sums all low stock product values

**Alerts:**
- System checks for alert conditions regularly
- Shows most recent 5 alerts
- Alert count on notification bell shows total unread

---

## Products Management

### A) Add New Product (Add Mode)

**Step 1: Go to Product Creation**
1. Navigate to Setup → Products → Add Product

**Step 2: Fill in Product Details**

**Basic Information:**
- **Name**: What the product is called (e.g., "Coca-Cola 500ml")
- **Category**: Select from dropdown categories
- **Cost Price**: How much you paid per unit (e.g., 0.50)
- **Selling Price**: How much you sell it for (e.g., 1.00)
- **Expiry Date**: When the product expires (if applicable)
- **Opening Stock**: How many you have to start with

**Bulk Settings (Optional):**
- **Bulk Enabled**: Check if you sell this product in bulk packages
- **Bulk Type**: Select which bulk type (e.g., "Beverages") if bulk enabled

**Step 3: Click Save**

**What the System Does When You Save:**

1. Creates product record with all details
2. Creates product_stock record with opening_stock
3. If opening_stock > 0:
   - Creates stock_in receipt (auto-generated)
   - Creates stock_in_items for the opening stock
   - Creates stock_movement record (type: IN)
   - Sets expiry date from product expiry

**Validation Rules:**
- Product name is required
- Cost price and selling price must be positive numbers
- Selling price must be >= cost price (optional, but recommended)
- Expiry date must be in the future (if provided)
- Opening stock must be a positive number

### B) Manage Products (Manage Mode)

**Step 1: Go to Product Management**
1. Navigate to Setup → Products → Manage Products

**Step 2: View Product List**
- Table shows all products with:
  - Product name
  - Category
  - Cost price
  - Selling price
  - Current stock
  - Expiry date (if set)

**Step 3: Filter and Search**
- Search by product name
- Filter by category
- Filter by stock level (all, low stock, out of stock)

**Step 4: Edit a Product**
1. Click edit button on product row
2. Update any field
3. Click Save

**What the System Does When You Edit:**
- Updates product record
- If cost price changed: affects future profit calculations
- If selling price changed: affects future sales
- If expiry date changed: updates expiry tracking

**Step 5: Delete a Product**
1. Click delete button on product row
2. Confirm deletion

**What the System Does When You Delete:**
- Marks product as deleted (soft delete)
- Keeps historical data (sales, stock movements)
- Prevents new sales of this product
- Existing sales records remain intact

### C) View Expired Products

**Step 1: Go to Expired Products**
1. Navigate to Setup → Products → Expired Products

**Step 2: View List**
- Shows all products where expiry_date < today
- Shows:
  - Product name
  - Category
  - Expiry date
  - How long expired
  - Current stock (if any remaining)

**Step 3: Take Action**
- Remove expired products from inventory
- Adjust stock to zero
- Create stock adjustment alert

---

## Stock In Management

### A) Record New Stock (Add Mode)

**Step 1: Go to Stock In Creation**
1. Navigate to Operations → Stock In → Add Stock In

**Step 2: Select Branch (Super Admin Only)**
- If you're Super Admin, select which branch
- If you're Branch Admin, your branch is auto-selected

**Step 3: Select Product**
- Use product search box
- Select from dropdown
- Product details appear (name, current stock, cost price)

**Step 4: Choose Entry Type**

**Units Mode:**
- Enter Quantity (units)
- Enter Cost Price per unit
- Enter Expiry Date (if applicable)

**Bulk Mode:**
- Enter Bulk Quantity (number of packs)
- Enter Cost Price per bulk (per pack)
- Enter Expiry Date (if applicable)
- System shows:
  - Units per bulk (from bulk unit definition)
  - Total units (bulk_qty × units_per_bulk)

**Step 5: Click Add Item**
- Adds item to cart
- Can add multiple items (group mode)

**Step 6: Fill Receipt Details**
- Supplier name (optional)
- Receipt number (optional)
- Notes (optional)

**Step 7: Click Post Stock In**

**What the System Does When You Post:**

1. Creates stock_in receipt record
2. For each item in cart:
   - Creates stock_in_item record
   - Stores quantity (total units)
   - Stores bulk_quantity (if bulk mode)
   - Stores cost_price (per unit)
   - Stores expiry_date
   - Sets remaining_quantity = quantity
3. Updates product_stocks.current_stock:
   - Adds quantity to current stock
4. Creates stock_movement record:
   - Type: IN
   - Links to stock_in_item
   - Records quantity added
5. Creates alert if stock adjustment needed

**Validation Rules:**
- Product must be selected
- Quantity must be positive
- Cost price must be positive
- Expiry date must be in future (if provided)
- Supplier name optional

### B) Manage Stock In Receipts (Manage Mode)

**Step 1: Go to Stock In Management**
1. Navigate to Operations → Stock In → Manage Stock In

**Step 2: View Receipt List**
- Shows all receipts with:
  - Receipt number
  - Date
  - Supplier
  - Branch
  - Total quantity
  - Total cost
  - User who created it

**Step 3: Filter and Search**
- Filter by date range
- Search by receipt number
- Filter by branch
- Filter by supplier

**Step 4: View Receipt Details**
1. Click view button on receipt row
2. See all items in receipt:
   - Product name
   - Quantity
   - Cost price
   - Expiry date
   - Remaining quantity

**Step 5: Edit a Receipt**
1. Click edit button on receipt row
2. Update receipt details or items
3. Click Save

**What the System Does When You Edit:**
- Updates receipt record
- Updates stock_in_item records
- Adjusts product_stocks.current_stock
- Creates stock_movement records for adjustments

**Step 6: Void a Receipt**
1. Click void button on receipt row
2. Enter void reason
3. Confirm void

**What the System Does When You Void:**
- Marks receipt as voided
- Deducts quantity from product_stocks.current_stock
- Increases remaining_quantity in stock_in_items
- Creates stock_movement records (type: OUT)
- Records void reason and date

---

## Sales Management

### A) Record a NEW Sale (Add Mode)

**Step 1: Choose Sale Entry Type: Single vs Group**

At the top of "Record Sale" you have:

**Single Mode:**
- You can only keep one item in the cart
- If you add another item, the system keeps only the last added item
- Use for quick POS (1 product)

**Group Mode:**
- You can add many items to the cart
- When you post, you get one Sales Receipt with multiple line items
- Use for typical customer basket (many products)

**Step 2: Select Branch (Super Admin Only)**
- If you're Super Admin, you must select a branch first
- If you're not Super Admin, branch is automatically your branch
- Branch matters because stock is branch-based

**Step 3: Select Product**
- Use the product search box
- Select from dropdown
- Once you select a product:
  - If the product has bulk_enabled = true, you can use Bulk
  - If not, Bulk is disabled and you sell only units

**Step 4: Choose Entry Type: Units or Bulk**

You now have Unit/Bulk tab buttons:

**If you choose Units:**
- You enter Quantity (Units) (e.g., 3 pieces)

**If you choose Bulk:**
- You enter Bulk Quantity (e.g., 2 packs)
- The UI shows:
  - Units per bulk
  - Total units (bulk_qty × units_per_bulk)

**Step 5: Enter Selling Price (all cases)**

This is important:

**If Units mode:**
- You enter Price per Unit

**If Bulk mode:**
- You enter Price per Bulk (price per pack)
- But internally the system stores unit_price always per unit
- So when you type bulk price, Livewire automatically converts:
  - unit_price = bulk_price / units_per_bulk

That's why in the cart table you'll see per-bulk display, but the database remains consistent.

**Step 6: Click Add Item**
- Adds the selected product line into the cart
- If you add the same product again:
  - It increases quantity (and bulk qty if in bulk mode)
- Single mode will keep only the last item

**Step 7: Payment section + Post Sale**

Fill:
- Customer Name (optional)
- Method: cash or card
- Amount Paid
  - For cash, system requires amount_paid >= grand_total
  - For card, it won't block you the same way (based on current validation)

Then click: **Post Sale**

### B) What happens when you click "Post Sale" (system behavior)

When you post a sale, the system does all of these:

**1) Stock availability validation (non-expired only)**

For each item, it checks you have enough stock from valid batches:

Only stock-in batches where:
- receipt not voided
- remaining_quantity > 0
- expiry is NULL or >= today

If not enough: you get "Insufficient non-expired stock …".

**2) FEFO allocation (batch picking)**

For each sale item, it allocates stock using FEFO:

- Earliest expiry first
- Non-expiry batches come last
- Then by stock_in_item id

This is the core of "sell oldest expiring stock first".

**3) Correct COGS + Profit**

COGS/profit are computed from the actual allocated batches:

```
allocatedCost = sum(batch.cost_price * qty_taken_from_batch)
unit_cost = allocatedCost / allocatedQty (weighted average)
line_cost = allocatedCost
line_profit = line_total - line_cost
```

Receipt totals:
- cogs_total = sum of all line_cost
- profit_total = sum of all line_profit

**4) Stock updates + movements**

- Deducts from product_stocks.current_stock
- Reduces stock_in_items.remaining_quantity from the allocated batches
- Creates stock_movements OUT records

### C) Manage existing Sales (Manage mode)

In Manage mode you can:
- Filter by date range
- Search (receipt / branch / user / customer name)
- Select many receipts
- Print Selected

And per receipt you can typically:
- View receipt details
- Edit receipt (full edit modal)
- Void receipt

### D) Edit a Sale (Full Edit)

When you edit a sale and save:

**What the system does:**
1. Reverses old allocations
2. Adds allocated quantities back to the original stock_in_items.remaining_quantity
3. Restores product stock
4. Adds previous sold quantity back into product_stocks.current_stock
5. Deletes old sales_items
6. Re-posts the edited cart as a fresh allocation again (FEFO), and recalculates COGS/profit

This ensures edited receipts remain batch-correct.

### E) Void a Sale

Voiding is like canceling the sale after the fact:
- Returns stock back (including to the original batches via allocations)
- Updates stock levels
- Marks receipt voided_at, void_reason, etc.

**Key "possible ways" summary:**
- Single + Units
- Single + Bulk
- Group + Units
- Group + Bulk
- Mixed Group (some lines units, some lines bulk) in the same receipt
- Edit posted sale (reallocates batches FEFO again)
- Void posted sale (reverses everything)

---

## Expenses Management

### A) Record New Expense (Add Mode)

**Step 1: Go to Expense Creation**
1. Navigate to Operations → Expenses → Add Expense

**Step 2: Fill in Expense Details**
- **Description**: What the expense is for (e.g., "Rent for February")
- **Amount**: How much it cost (e.g., 500.00)
- **Date**: When the expense occurred (defaults to today)
- **Category**: Optional category (e.g., "Rent", "Utilities", "Supplies")

**Step 3: Click Save**

**What the System Does When You Save:**
- Creates expense record
- Records user who created it
- Records date and time
- Stores amount and description

**Validation Rules:**
- Description is required
- Amount must be positive number
- Date must be valid date

### B) Manage Expenses (Manage Mode)

**Step 1: Go to Expense Management**
1. Navigate to Operations → Expenses → Manage Expenses

**Step 2: View Expense List**
- Shows all expenses with:
  - Description
  - Amount
  - Date
  - Category
  - User who created it

**Step 3: Filter and Search**
- Filter by date range
- Filter by category
- Search by description

**Step 4: Edit an Expense**
1. Click edit button on expense row
2. Update details
3. Click Save

**What the System Does When You Edit:**
- Updates expense record
- Records who made the change

**Step 5: Delete an Expense**
1. Click delete button on expense row
2. Confirm deletion

**What the System Does When You Delete:**
- Removes expense record
- Expense no longer appears in reports

---

## Reports

### A) Sales Report

**Step 1: Go to Sales Report**
1. Navigate to Analytics → Reports → Sales

**Step 2: Select Date Range**
- Choose start date
- Choose end date

**Step 3: View Report**
Shows:
- Total sales amount
- Number of transactions
- Average sale amount
- Sales by day (graph)
- Top-selling products
- Sales by branch (if multi-branch)

**What the System Does:**
- Queries all sales records in date range
- Sums total amounts
- Calculates averages
- Groups by day for graph
- Groups by product for top sellers
- Groups by branch for multi-branch

### B) Profit Report

**Step 1: Go to Profit Report**
1. Navigate to Analytics → Reports → Profit

**Step 2: Select Date Range**
- Choose start date
- Choose end date

**Step 3: View Report**
Shows:
- Total revenue (sales)
- Total cost (COGS)
- Total profit
- Profit margin percentage
- Profit by product
- Profit trends

**What the System Does:**
- Sums sales totals
- Sums COGS from sales
- Calculates profit = revenue - COGS
- Calculates profit margin = (profit / revenue) × 100
- Groups by product
- Shows trends over time

### C) Stock Report

**Step 1: Go to Stock Report**
1. Navigate to Analytics → Reports → Stock

**Step 2: View Report**
Shows:
- Current inventory levels
- Inventory value
- Stock by category
- Low stock items
- Out of stock items

**What the System Does:**
- Queries all product_stocks
- Calculates inventory value
- Groups by category
- Identifies low stock (current_stock < reorder_level)
- Identifies out of stock (current_stock = 0)

### D) Expenses Report

**Step 1: Go to Expenses Report**
1. Navigate to Analytics → Reports → Expenses

**Step 2: Select Date Range**
- Choose start date
- Choose end date

**Step 3: View Report**
Shows:
- Total expenses
- Expenses by category
- Expense trends
- Largest expenses

**What the System Does:**
- Sums all expenses in date range
- Groups by category
- Shows trends over time
- Sorts by amount

### E) Expiry Report

**Step 1: Go to Expiry Report**
1. Navigate to Analytics → Reports → Expiry

**Step 2: View Report**
Shows:
- Products expiring soon (within 7 days)
- Products expiring within 30 days
- Already expired products
- Expiry timeline

**What the System Does:**
- Queries products with expiry dates
- Calculates days until expiry
- Groups by expiry period
- Shows timeline view

**Highlighted Rows:**
- Red border: Already expired
- Yellow border: Expiring soon
- Blue border: Normal

---

## Branch Management

### A) Create New Branch

**Step 1: Go to Branch Creation**
1. Navigate to Setup → Branches
2. Click Add Branch

**Step 2: Fill in Branch Details**
- **Name**: Branch/store name (e.g., "Downtown Store")
- **Address**: Physical location
- **Phone**: Contact number
- **Email**: Branch email

**Step 3: Click Save**

**What the System Does:**
- Creates branch record
- Branch becomes available for user assignment
- Stock and sales can be tracked by branch

**Validation Rules:**
- Branch name is required
- Name must be unique

### B) Edit Branch

**Step 1: Go to Branch Management**
1. Navigate to Setup → Branches
2. Click edit button on branch row

**Step 2: Update Details**
- Update any field
- Click Save

**What the System Does:**
- Updates branch record
- Changes are reflected throughout system

### C) Delete Branch

**Step 1: Go to Branch Management**
1. Navigate to Setup → Branches
2. Click delete button on branch row

**Step 2: Confirm Deletion**

**Warning:** Deleting a branch removes all associated data including users, products, and transactions.

**What the System Does:**
- Marks branch as deleted (soft delete)
- Historical data preserved
- Branch no longer available for selection

---

## User Management

### A) Create New User

**Step 1: Go to User Creation**
1. Navigate to Setup → Users
2. Click Add User

**Step 2: Fill in User Details**
- **Name**: Full name of the user
- **Email**: Email address (used for login)
- **Branch**: Select which branch they work at
- **Password**: Create a secure password
- **Confirm Password**: Re-enter password

**Step 3: Click Save**

**What the System Does:**
- Creates user account
- Links user to branch
- Hashes password for security
- User can log in with email and password

**Validation Rules:**
- Name is required
- Email is required and must be valid format
- Email must be unique
- Password must be at least 8 characters
- Password and confirm password must match

### B) Edit User

**Step 1: Go to User Management**
1. Navigate to Setup → Users
2. Click edit button on user row

**Step 2: Update Details**
- Update any field except email
- Click Save

**What the System Does:**
- Updates user record
- Changes take effect immediately

### C) Delete User

**Step 1: Go to User Management**
1. Navigate to Setup → Users
2. Click delete button on user row

**Step 2: Confirm Deletion**

**What the System Does:**
- Removes user account
- User can no longer log in
- Historical data preserved

---

## Role Management

### A) Create New Role

**Step 1: Go to Role Creation**
1. Navigate to Settings → Roles
2. Click Add Role

**Step 2: Define Role**
- **Role Name**: What to call this role (e.g., "Inventory Manager")
- **Branch**: Which branch this role applies to

**Step 3: Select Permissions**

Expand permission groups and check the permissions you want:

**Branches Module:**
- branches.view
- branches.create
- branches.edit
- branches.delete

**Users Module:**
- users.view
- users.create
- users.edit
- users.delete

**RBAC Module:**
- rbac.roles.view
- rbac.roles.create
- rbac.roles.edit
- rbac.roles.delete
- rbac.permissions.view
- rbac.user_roles.assign

**Setup Modules:**
- setup.categories.view/create/edit/delete
- setup.bulk.view/create/edit/delete

**Products Module:**
- products.view
- products.create
- products.edit
- products.delete

**Stock In Module:**
- stock_in.view
- stock_in.post
- stock_in.edit
- stock_in.delete

**Sales Module:**
- sales.view
- sales.post
- sales.edit
- sales.delete

**Expenses Module:**
- expenses.view
- expenses.create
- expenses.edit
- expenses.delete

**Reports Module:**
- reports.sales
- reports.profit
- reports.stock
- reports.expenses
- reports.expiry

**Audit Module:**
- audit.stock_movements.view
- audit.activity_logs.view

**Alerts Module:**
- alerts.stock_adjustment
- alerts.expired_stock
- alerts.expiry_warning
- alerts.low_stock

**Step 4: Click Save**

**What the System Does:**
- Creates role record
- Links permissions to role
- Role can be assigned to users

### B) Edit Role

**Step 1: Go to Role Management**
1. Navigate to Settings → Roles
2. Click edit button on role row

**Step 2: Update Role**
- Update role name
- Add or remove permissions
- Click Save

**What the System Does:**
- Updates role record
- Updates permission assignments
- Changes affect all users with this role

### C) Delete Role

**Step 1: Go to Role Management**
1. Navigate to Settings → Roles
2. Click delete button on role row

**Step 2: Confirm Deletion**

**Warning:** Deleting a role removes it from all users who had it assigned.

**What the System Does:**
- Removes role record
- Removes role from all users
- Users lose those permissions

---

## User Role Assignment

### A) Assign Roles to User

**Step 1: Go to User Roles**
1. Navigate to Settings → User Roles

**Step 2: Select Branch and User**
- Select the branch
- Select the user

**Step 3: Assign Roles**
- Check the roles to assign to this user for this branch
- Click Save

**What the System Does:**
- Links user to roles for specific branch
- User gets all permissions from assigned roles
- User can have different roles in different branches

### B) View User Roles

**Step 1: Go to User Roles**
1. Navigate to Settings → User Roles

**Step 2: View Assignments**
- Shows all user-role assignments
- Grouped by branch and user

---

## Categories Management

### A) Create New Category

**Step 1: Go to Category Creation**
1. Navigate to Setup → Categories
2. Click Add Category

**Step 2: Enter Category Name**
- Enter category name (e.g., "Beverages")

**Step 3: Click Save**

**What the System Does:**
- Creates category record
- Category becomes available for product assignment

**Validation Rules:**
- Category name is required
- Name must be unique

### B) Edit Category

**Step 1: Go to Category Management**
1. Navigate to Setup → Categories
2. Click edit button on category row

**Step 2: Update Name**
- Update category name
- Click Save

**What the System Does:**
- Updates category record
- Changes reflected in product categories

### C) Delete Category

**Step 1: Go to Category Management**
1. Navigate to Setup → Categories
2. Click delete button on category row

**Step 2: Confirm Deletion**

**Warning:** Deleting a category removes it from all products.

**What the System Does:**
- Marks category as deleted
- Products in category lose category assignment

---

## Bulk Units & Types Management

### A) Create New Bulk Unit

**Step 1: Go to Bulk Units**
1. Navigate to Setup → Bulk Units & Types
2. Click Add Bulk Unit

**Step 2: Enter Bulk Unit Details**
- **Name**: Unit name (e.g., "Case", "Carton", "Box")
- **Quantity**: How many individual items per bulk unit (e.g., 24)

**Step 3: Click Save**

**What the System Does:**
- Creates bulk unit record
- Unit becomes available for products

**Validation Rules:**
- Name is required
- Quantity must be positive number

### B) Create New Bulk Type

**Step 1: Go to Bulk Types**
1. Navigate to Setup → Bulk Units & Types
2. Click Add Bulk Type

**Step 2: Enter Bulk Type Details**
- **Name**: Type name (e.g., "Cigarettes", "Beverages")
- **Description**: What this type is for

**Step 3: Click Save**

**What the System Does:**
- Creates bulk type record
- Type becomes available for products

**Validation Rules:**
- Name is required

### C) Edit/Delete Bulk Units & Types

**Step 1: Go to Bulk Units & Types**
1. Navigate to Setup → Bulk Units & Types
2. Click edit or delete button

**Step 2: Update or Confirm**

**What the System Does:**
- Updates or removes record
- Changes reflected in products

---

## Audit Trails

### A) View Stock Movements

**Step 1: Go to Stock Movements**
1. Navigate to Audit → Audit Trails → Stock Movements

**Step 2: View Movement List**
Shows all inventory changes:
- Type: IN (stock in) or OUT (sale/void)
- Product name
- Quantity
- Date and time
- User who performed action
- Related receipt or sale

**Step 3: Filter and Search**
- Filter by date range
- Filter by type (IN/OUT)
- Filter by product
- Search by user

**What the System Shows:**
- Complete history of all stock changes
- Helps track inventory flow
- Useful for troubleshooting

### B) View Activity Logs

**Step 1: Go to Activity Logs**
1. Navigate to Audit → Audit Trails → Activity Logs

**Step 2: View Activity List**
Shows all user actions:
- User who performed action
- Action type (create, edit, delete, view)
- Module affected
- Date and time
- Details of what changed

**Step 3: Filter and Search**
- Filter by date range
- Filter by user
- Filter by module
- Search by action

**What the System Shows:**
- Complete history of all user actions
- Helps with accountability
- Useful for security

---

## Alerts System

### A) View Alerts

**Step 1: Check Notification Bell**
- Look at bell icon in top right
- Number shows unread alerts
- Click to see recent alerts

**Step 2: Check Dashboard**
- Alerts section shows recent alerts
- Color-coded by urgency
- Click "Mark as read" to dismiss

**Step 3: View All Alerts**
- Each alert shows:
  - Type (stock_adjustment, expired_stock, expiry_warning, low_stock)
  - Title
  - Message
  - When it was created
  - Related product or item

### B) Alert Types

**Stock Adjustment Alert:**
- Triggered when inventory is manually adjusted
- Indicates possible discrepancy
- Needs investigation

**Expired Stock Alert:**
- Triggered when product expires
- Urgent attention needed
- Remove from inventory

**Expiry Warning Alert:**
- Triggered when product is expiring soon
- Plan for replacement or discount
- Not urgent but needs attention

**Low Stock Alert:**
- Triggered when stock runs low
- Time to reorder
- Prevents stockouts

### C) Mark Alerts as Read

**Step 1: Click "Mark as read"**
- Alert disappears from dashboard
- Notification bell count decreases
- Alert record kept in system

**What the System Does:**
- Marks alert as read
- Records when it was read
- Alert still visible in audit trail

---

## Summary

This document provides detailed step-by-step guidance for every module in the CShop system. Each section explains:

1. **How to use the feature** - Step-by-step instructions
2. **What the system does** - Behind-the-scenes logic
3. **Validation rules** - What's required and what's not
4. **System behavior** - How data flows and is processed

Use this document as a reference for understanding how each part of the system works and what to expect when performing actions.

---

**End of Detailed Module Documentation**

For questions about specific features or procedures, refer to the User Guide or Super Admin Guide.
