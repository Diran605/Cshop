# CShop Retail Management System - System Workflow & Logic

## Understanding How the System Works

This document explains how the CShop system works in simple terms. It shows the flow of information, the rules the system follows, and how everything connects together.

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Data Flow](#data-flow)
3. [Core Business Logic](#core-business-logic)
4. [How Different Parts Connect](#how-different-parts-connect)
5. [Complete Workflow](#complete-workflow)

---

## System Overview

### What is CShop?

CShop is a retail management system that helps stores manage their business. Think of it as a digital assistant that keeps track of:
- What products you have in stock
- What you sell and when
- How much money you make
- Where your money goes (expenses)
- When things expire
- Who does what in the system

### Main Parts of the System

The system has several main parts that work together:

1. **Products & Inventory** - What you sell and how much you have
2. **Sales** - What you sell to customers
3. **Stock In** - What you receive from suppliers
4. **Expenses** - Money you spend to run the business
5. **Reports** - Information about how your business is doing
6. **Users & Access** - Who can use the system and what they can do
7. **Alerts** - Notifications about important events
8. **Audit Trail** - Record of everything that happens

---

## Data Flow

### How Information Moves Through the System

```
Supplier → Stock In → Inventory → Sales → Reports
                ↓
            Alerts
                ↓
            Audit Trail
```

### Step-by-Step Flow

**Step 1: Products Enter the System**

1. You create a product (name, price, category)
2. You receive stock from suppliers (Stock In)
3. The system adds the items to your inventory

**Step 2: Products Are Sold**

1. A customer buys something (Sales)
2. The system removes the item from inventory
3. The system records the sale
4. The system calculates profit

**Step 3: Information is Tracked**

1. Every action is recorded (Audit Trail)
2. Important events trigger alerts (Alerts)
3. All data is available in reports (Reports)

**Step 4: You Make Decisions**

1. You check reports to see how business is going
2. You check alerts to know what needs attention
3. You check audit trails to see what happened

---

## Core Business Logic

### Inventory Management

**How Inventory Works:**

1. **Initial Stock** - When you add a product, you tell the system how many you have
2. **Stock Increases** - When you receive more from suppliers, the system adds to your inventory
3. **Stock Decreases** - When you sell items, the system removes them from inventory
4. **Stock Adjustments** - If something is wrong, you can manually adjust the quantity

**Rules the System Follows:**

- You can't sell more than you have in stock
- Every time stock changes, the system records why
- The system tracks both individual items and bulk packages
- Expired items are marked so you know not to sell them

### Sales Management

**How Sales Work:**

1. **Customer Makes a Purchase** - You record what they bought and how much
2. **System Checks Inventory** - Makes sure you have enough to sell
3. **System Updates Inventory** - Removes sold items from stock
4. **System Records Sale** - Saves the sale with date, time, and amount
5. **System Calculates Profit** - Compares selling price to cost price

**Rules the System Follows:**

- Sales are recorded with date and time
- Each sale is linked to the specific product sold
- The system tracks who made the sale (which user)
- Sales can be edited or voided if there's a mistake

### Stock In Management

**How Stock In Works:**

1. **Supplier Delivers Products** - You receive items from suppliers
2. **You Record the Receipt** - Enter what you received and how much
3. **System Updates Inventory** - Adds the items to your stock
4. **System Records the Receipt** - Saves when and how much you received
5. **System Tracks Cost** - Records how much you paid

**Rules the System Follows:**

- Each stock receipt is recorded with date and time
- The system tracks who received the stock
- The system records the cost price for profit calculation
- Stock receipts can be edited or voided if there's a mistake

### Expense Management

**How Expenses Work:**

1. **You Spend Money** - You pay for something (rent, utilities, supplies)
2. **You Record the Expense** - Enter what you spent and why
3. **System Records the Expense** - Saves the amount and description
4. **System Shows in Reports** - Expenses appear in profit and expense reports

**Rules the System Follows:**

- Each expense is recorded with date and description
- The system tracks who recorded the expense
- Expenses are used to calculate profit
- Expenses can be edited or deleted if there's a mistake

### Expiry Management

**How Expiry Tracking Works:**

1. **You Enter Expiry Date** - When adding products, you enter when they expire
2. **System Tracks Expiry** - The system monitors when products will expire
3. **System Sends Alerts** - You get warnings before products expire
4. **System Marks Expired** - Expired products are marked so you know

**Rules the System Follows:**

- The system checks expiry dates regularly
- You get warnings before products expire (expiry warning)
- You get alerts when products have expired (expired stock)
- Expired products are shown in reports so you can remove them

### Alert Management

**How Alerts Work:**

1. **Something Happens** - An event occurs in the system
2. **System Checks Rules** - The system checks if this event is important
3. **System Creates Alert** - If important, an alert is created
4. **You Get Notified** - The alert appears on your dashboard
5. **You Mark as Read** - When you've seen it, you can dismiss it

**Types of Alerts:**

- **Stock Adjustment** - When inventory is manually changed (might indicate a problem)
- **Expired Stock** - Products that have expired and should be removed
- **Expiry Warning** - Products expiring soon (need to plan)
- **Low Stock** - Products running low (need to reorder)

**Rules the System Follows:**

- Alerts are created automatically based on system events
- Each alert is linked to a specific user
- Alerts can be marked as read when you've seen them
- Unread alerts show a count on the notification bell

### User Access & Permissions

**How Access Control Works:**

1. **Users Are Created** - Each person who uses the system has an account
2. **Roles Are Assigned** - Each user has roles that define what they can do
3. **Permissions Are Checked** - Before any action, the system checks if the user is allowed
4. **Action Is Allowed or Blocked** - Based on permissions, the action proceeds or is blocked

**User Types:**

- **Super Admin** - Can do everything, manages the entire system
- **Branch Admin** - Manages one store location
- **Cashier** - Can only process sales
- **Storekeeper** - Manages products and inventory
- **Accountant** - Handles expenses and reports

**Rules the System Follows:**

- Each user can only do what their permissions allow
- Permissions are organized by what they want to do (view, create, edit, delete)
- Users can have different roles in different branches
- Super Admin can override any permission check

### Audit Trail

**How the System Records Everything:**

1. **Action Happens** - Someone does something in the system
2. **System Records It** - The system saves what happened, when, and who did it
3. **Information is Stored** - The record is kept permanently
4. **You Can Review It** - You can look at the history anytime

**What Gets Recorded:**

- Who performed the action (user name)
- When they did it (date and time)
- What they did (the action taken)
- What module they used (products, sales, etc.)
- What changed (before and after values for important data)

**Rules the System Follows:**

- Every important action is recorded automatically
- Records are kept permanently (not deleted)
- Records can be filtered by user, date, or action type
- Records help troubleshoot problems and provide accountability

---

## How Different Parts Connect

### The Connection Diagram

```
Products
   ↓ (stock in)
Inventory
   ↓ (sold)
Sales
   ↓ (recorded)
Reports
   ↑ (data from)
Expenses
```

### How Parts Work Together

**Products and Inventory:**

- Products define what you sell
- Inventory tracks how many you have
- Stock in adds to inventory
- Sales remove from inventory

**Sales and Reports:**

- Sales are recorded with product and price
- Reports show sales totals and trends
- Profit reports compare sales to costs
- Stock reports show what's left

**Expenses and Reports:**

- Expenses are recorded when you spend money
- Expense reports show where money goes
- Profit reports compare sales to expenses
- This shows your actual profit

**Alerts and Everything:**

- Stock changes trigger stock adjustment alerts
- Expiry dates trigger expiry alerts
- Low inventory triggers low stock alerts
- Alerts appear on dashboard and in reports

**Audit Trail and Everything:**

- Every action is recorded
- Shows who did what and when
- Helps find problems
- Provides accountability

---

## Complete Workflow

### Initial Setup Workflow

**What You Do First:**

1. **Create Branches** - Add each store location
2. **Create Categories** - Organize product types
3. **Set Up Bulk Units** - Define packaging (if you sell in bulk)
4. **Create Products** - Add all products to the catalog
5. **Create Users** - Add accounts for your staff
6. **Assign Roles** - Give users appropriate access
7. **Set Opening Stock** - Enter initial inventory levels

### Daily Operations Workflow

**Morning Routine:**

1. **Check Dashboard** - See what's happening in your business
2. **Review Alerts** - Look for urgent items (expired stock, low stock)
3. **Check Low Stock** - See what needs to be reordered
4. **Plan the Day** - Decide what needs attention

**Throughout the Day:**

1. **Receive Stock** - When suppliers deliver, record it in Stock In
2. **Make Sales** - When customers buy, record the sale
3. **Record Expenses** - When you spend money, record it
4. **Check Alerts** - New alerts appear throughout the day

**Evening Routine:**

1. **Review Sales** - See how much you sold today
2. **Check Inventory** - Verify stock levels
3. **Review Alerts** - Make sure you've seen all alerts
4. **Check Reports** - Look at today's performance

### Weekly Workflow

**Monday:**

1. Review last week's sales reports
2. Check profit margins
3. Identify low stock items to reorder
4. Plan for the week

**Wednesday:**

1. Review activity logs
2. Check for any security issues
3. Verify user access
4. Address any problems

**Friday:**

1. Review weekly expenses
2. Check stock movement logs
3. Prepare for next week
4. Make any needed adjustments

### Monthly Workflow

**First Week:**

1. Review monthly reports
2. Audit inventory levels
3. Review user roles and access
4. Check for expired products

**Second Week:**

1. Check system performance
2. Review alert patterns
3. Update documentation if needed
4. Address any issues

**Third Week:**

1. Review profit trends
2. Check for expired products
3. Audit expense records
4. Plan improvements

**Fourth Week:**

1. Monthly system review
2. Plan for next month
3. Update user access as needed
4. Prepare for month-end

### Problem-Solving Workflow

**When Something Goes Wrong:**

1. **Identify the Problem** - What isn't working right?
2. **Check Audit Trails** - Look at what happened
3. **Find the Cause** - What caused the problem?
4. **Fix the Problem** - Make corrections
5. **Document It** - Record what happened and why
6. **Prevent Recurrence** - Make sure it doesn't happen again

**Example: Inventory Doesn't Match**

1. Check physical inventory
2. Check stock movement logs
3. Look for manual adjustments
4. Find the discrepancy
5. Adjust inventory to match
6. Document why it happened

---

## Business Rules Explained

### Inventory Rules

**Rule 1: You Can't Sell What You Don't Have**

- Before a sale is processed, the system checks if you have enough stock
- If you don't have enough, the sale is blocked
- This prevents selling items you don't actually have

**Rule 2: Every Stock Change Is Tracked**

- When stock increases (from suppliers), the system records it
- When stock decreases (from sales), the system records it
- When stock is manually adjusted, the system records why
- This helps you track where inventory goes

**Rule 3: Expired Items Are Marked**

- When products expire, they're marked as expired
- Expired products appear in reports so you can remove them
- You can't sell expired products (the system prevents it)

### Sales Rules

**Rule 1: Sales Are Permanent**

- Once a sale is recorded, it stays in the system
- You can void a sale if there's a mistake
- Voided sales are recorded so you know what happened

**Rule 2: Sales Are Linked to Products**

- Each sale is linked to the specific product sold
- This lets you see what sells best
- This helps with inventory planning

**Rule 3: Sales Are Linked to Users**

- Each sale records who made it
- This helps with accountability
- This helps with performance tracking

### Expense Rules

**Rule 1: Expenses Reduce Profit**

- Expenses are subtracted from sales to calculate profit
- This shows your actual profit, not just revenue
- This helps you understand where money goes

**Rule 2: Expenses Are Categorized**

- Each expense has a description
- This helps you see where money is spent
- This helps with budgeting

### Alert Rules

**Rule 1: Alerts Are Automatic**

- The system creates alerts automatically
- You don't have to create alerts manually
- The system checks for alert conditions regularly

**Rule 2: Alerts Are User-Specific**

- Each alert is linked to a specific user
- Only that user sees the alert
- This keeps alerts relevant to each person

**Rule 3: Alerts Can Be Dismissed**

- When you've seen an alert, you can mark it as read
- This removes it from your dashboard
- The alert record is kept in the audit trail

---

## How Reports Work

### What Reports Show

**Sales Report:**
- How much you sold over time
- Which products sell best
- Sales trends (increasing or decreasing)
- Sales by branch (if you have multiple locations)

**Profit Report:**
- Revenue minus expenses
- Profit margins
- Most profitable products
- Profit trends

**Stock Report:**
- Current inventory levels
- Stock value by category
- Low stock items
- Inventory trends

**Expenses Report:**
- Where money is spent
- Expense categories
- Spending trends
- Expenses by branch

**Expiry Report:**
- Products expiring soon
- Already expired products
- Expiry timeline
- Expiry patterns

### How Reports Are Generated

1. **Data Is Collected** - The system gathers all relevant data
2. **Data Is Calculated** - Totals, averages, and trends are calculated
3. **Data Is Displayed** - The report shows the information
4. **Data Is Updated** - Reports are always current

### How Reports Help You

- See how your business is performing
- Identify problems early
- Make informed decisions
- Plan for the future
- Track progress over time

---

## Security and Safety

### How the System Keeps Data Safe

**User Authentication:**
- Each user has a unique login
- Passwords are required
- Only authorized users can access the system

**Access Control:**
- Users can only do what their permissions allow
- Sensitive actions require specific permissions
- Super Admin can override permissions if needed

**Audit Trail:**
- Every action is recorded
- Records show who did what and when
- Records help find problems and ensure accountability

**Data Protection:**
- Regular backups protect against data loss
- Records are kept permanently
- Data can be restored if needed

### How to Stay Safe

1. **Use Strong Passwords** - Mix letters, numbers, and symbols
2. **Change Passwords Regularly** - Every 3-6 months
3. **Review User Access** - Remove access for former employees
4. **Check Activity Logs** - Look for suspicious activity
5. **Keep Software Updated** - Install updates when available

---

## Summary

### How the System Works Together

1. **Products** define what you sell
2. **Inventory** tracks how much you have
3. **Stock In** adds to inventory
4. **Sales** remove from inventory
5. **Reports** show how you're doing
6. **Alerts** tell you what needs attention
7. **Audit Trail** records everything
8. **Users** do the work with appropriate access

### The Big Picture

CShop is like a digital assistant that:
- Remembers everything that happens
- Tells you when something needs attention
- Shows you how your business is performing
- Helps you make better decisions
- Keeps your data safe and organized

### Key Takeaways

- Every action is recorded and tracked
- The system follows clear rules and logic
- Different parts work together seamlessly
- Reports help you understand your business
- Alerts help you stay on top of things
- Access control keeps everything secure

---

## Quick Reference

| What Happens | How the System Handles It |
|--------------|--------------------------|
| You receive stock | System adds to inventory, records receipt |
| You make a sale | System removes from inventory, records sale |
| You spend money | System records expense, shows in reports |
| Product expires | System marks it, sends alert |
| Stock runs low | System sends alert, shows in reports |
| User makes change | System records in audit trail |
| Something goes wrong | Check audit trails to find cause |

---

**End of System Workflow & Logic**

This document explains how the CShop system works in simple terms. No technical knowledge is needed to understand it. The system is designed to be intuitive and easy to use, while providing powerful tools to manage your retail business effectively.

For questions about how to use specific features, refer to the User Guide or Super Admin Guide.
