# CShop Retail Management System - User Guide

## Welcome to CShop!

This guide will help you understand how to use the CShop retail management system. Whether you're a store manager, cashier, or administrator, this system helps you manage inventory, sales, expenses, and more.

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Managing Products](#managing-products)
4. [Stock Management](#stock-management)
5. [Processing Sales](#processing-sales)
6. [Tracking Expenses](#tracking-expenses)
7. [Viewing Reports](#viewing-reports)
8. [Managing Users & Roles](#managing-users--roles)
9. [Audit Trails](#audit-trails)
10. [Alerts & Notifications](#alerts--notifications)

---

## Getting Started

### Logging In

1. Open your web browser and go to your CShop website
2. Enter your username and password
3. Click "Log In"

### What You See First

After logging in, you'll see the **Dashboard** - your main control center showing:
- Total sales for this month
- Current inventory value
- Low stock items that need attention
- Recent alerts and notifications

---

## Dashboard Overview

The dashboard gives you a quick snapshot of your business:

### Key Performance Indicators (KPIs)
- **Total Sales**: How much money you've made this month
- **Inventory Value**: Total worth of all products in stock
- **Low Stock Value**: Value of products running low

### Quick Access Cards
Click on any card to quickly access:
- **Products**: Manage your product catalog
- **Categories**: Organize product groups
- **Bulk Units & Types**: Set up packaging options
- **Stock In**: Receive new inventory
- **Sales**: Record sales transactions

### Recent Alerts
If there are important alerts (like expired products or low stock), they appear here with:
- Color-coded indicators (red for urgent, yellow for warnings)
- What the alert is about
- When it was created
- Option to mark as read

---

## Managing Products

### Adding New Products

1. Go to **Setup** → **Products** → **Add Product**
2. Fill in the product details:
   - **Name**: What the product is called
   - **Category**: Which group it belongs to
   - **Cost Price**: How much you paid for it
   - **Selling Price**: How much you sell it for
   - **Expiry Date**: When it expires (if applicable)
   - **Opening Stock**: How many you have to start
3. Click **Save**

### Managing Existing Products

1. Go to **Setup** → **Products** → **Manage Products**
2. Find the product you want to edit
3. Click the edit button to change details
4. Or click delete to remove it

### Viewing Expired Products

1. Go to **Setup** → **Products** → **Expired Products**
2. See all products that have expired
3. Take action to remove or restock

---

## Stock Management

### Receiving Stock In

When you get new inventory from suppliers:

1. Go to **Operations** → **Stock In** → **Add Stock In**
2. Select the product you received
3. Choose **Units** (individual items) or **Bulk** (packaged quantities)
4. Enter:
   - **Quantity**: How many you received
   - **Cost Price**: Price per unit
   - **Expiry Date**: When the stock expires
5. Click **Save**

### Managing Stock Records

1. Go to **Operations** → **Stock In** → **Manage Stock In**
2. View all stock receipts
3. Edit or void receipts if needed

---

## Processing Sales

### Making a Sale

1. Go to **Operations** → **Sales** → **Add Sales**
2. Select the product you're selling
3. Choose **Units** (sell individually) or **Bulk** (sell in packages)
4. Enter the quantity
5. The system automatically:
   - Calculates the total price
   - Reduces inventory
   - Records the sale
6. Click **Save**

### Viewing Sales History

1. Go to **Operations** → **Sales** → **Manage Sales**
2. See all sales records with:
   - Date and time
   - Products sold
   - Total amount
3. Edit or void sales if needed

---

## Tracking Expenses

### Recording Expenses

1. Go to **Operations** → **Expenses** → **Add Expense**
2. Enter expense details:
   - **Description**: What the expense is for
   - **Amount**: How much it cost
   - **Date**: When it occurred
3. Click **Save**

### Managing Expenses

1. Go to **Operations** → **Expenses** → **Manage Expenses**
2. View all expense records
3. Edit or delete as needed

---

## Viewing Reports

The system provides detailed reports to help you make decisions:

### Sales Report
- Total sales over time
- Best-selling products
- Revenue trends

### Profit Report
- Revenue vs. cost
- Profit margins
- Profitability analysis

### Stock Report
- Current inventory levels
- Stock value by category
- Low stock items

### Expenses Report
- Expense breakdown
- Spending trends
- Cost analysis

### Expiry Report
- Products expiring soon
- Already expired items
- Expiry timeline

### How to Access Reports
1. Go to **Analytics** → **Reports**
2. Select the report you want to view
3. Reports show data with highlighted rows for items with alerts

---

## Managing Users & Roles

### Creating Users

1. Go to **Setup** → **Users**
2. Click **Add User**
3. Enter:
   - **Name**: Person's name
   - **Email**: Their email address
   - **Branch**: Which branch they work at (if applicable)
   - **Password**: Their login password
4. Click **Save**

### Assigning Roles

1. Go to **Settings** → **User Roles**
2. Select a branch
3. Select a user
4. Check the roles you want to assign:
   - **Super Admin**: Full access to everything
   - **Branch Admin**: Manages a specific branch
   - **Cashier**: Can process sales only
   - **Storekeeper**: Manages products and stock
   - **Accountant**: Handles expenses and reports
5. Click **Save**

### Creating Custom Roles

1. Go to **Settings** → **Roles**
2. Click **Add Role**
3. Enter role name
4. Select which permissions the role should have:
   - **Branches**: View, create, edit, delete branches
   - **Users**: View, create, edit, delete users
   - **Products**: View, create, edit, delete products
   - **Stock In**: View, post, edit, delete stock receipts
   - **Sales**: View, post, edit, delete sales
   - **Expenses**: View, create, edit, delete expenses
   - **Reports**: View sales, profit, stock, expenses, expiry reports
   - **Audit**: View stock movements and activity logs
   - **Alerts**: View stock adjustment, expired stock, expiry warning, low stock alerts
5. Click **Save**

---

## Audit Trails

The system keeps track of all actions for accountability:

### Stock Movements
- Shows every time stock is added or removed
- Tracks who made the change
- Records the reason (sale, receipt, adjustment)

### Activity Logs
- Shows all user actions in the system
- Tracks who did what and when
- Helps with troubleshooting and security

### How to View Audit Trails
1. Go to **Audit** → **Audit Trails**
2. Select **Stock Movements** or **Activity Logs**
3. View detailed history of all actions

---

## Alerts & Notifications

The system automatically alerts you to important events:

### Types of Alerts

1. **Stock Adjustment Alert**
   - When inventory is manually adjusted
   - Helps track corrections and discrepancies

2. **Expired Stock Alert**
   - Products that have already expired
   - Urgent attention needed

3. **Expiry Warning Alert**
   - Products expiring soon
   - Plan for replacement or discount

4. **Low Stock Alert**
   - Products running low
   - Time to reorder

### Viewing Alerts

1. Look at the **notification bell** in the top right corner
   - Shows number of unread alerts
   - Click to see recent alerts

2. Check the **Dashboard** for alert cards
   - Shows most recent alerts
   - Color-coded by urgency
   - Click "Mark as read" to dismiss

### Alerts in Reports

When viewing reports, rows with alerts are highlighted:
- **Red border**: Expired stock (urgent)
- **Yellow border**: Expiry warning or stock adjustment (needs attention)
- **Blue border**: Low stock (monitor)

---

## Tips for Using the System

### Best Practices

1. **Keep Products Updated**
   - Always add new products before selling them
   - Update prices when they change
   - Track expiry dates for perishable items

2. **Monitor Stock Levels**
   - Check low stock alerts regularly
   - Reorder before running out
   - Use expiry warnings to prevent waste

3. **Record Everything**
   - Every sale should be recorded
   - Track all expenses
   - This gives accurate reports

4. **Use Reports**
   - Check sales reports weekly
   - Review profit reports monthly
   - Use expiry reports to manage perishables

5. **Manage Users Properly**
   - Give users only the access they need
   - Change passwords regularly
   - Review user roles periodically

### Common Questions

**Q: What if I make a mistake?**
A: Most entries can be edited or voided. Check the "Manage" section for each module.

**Q: How do I know when products are expiring?**
A: The system sends expiry warning alerts. Check your notification bell and dashboard.

**Q: Can I see what happened in the past?**
A: Yes! Use the Audit Trails section to see all historical actions.

**Q: What if I need help?**
A: Contact your system administrator or Super Admin for assistance.

---

## Getting Help

If you need assistance:
1. Check this guide first
2. Contact your system administrator
3. For technical issues, reach out to your IT support team

---

## Summary

CShop makes it easy to:
- Manage your inventory
- Process sales quickly
- Track expenses
- View detailed reports
- Stay informed with alerts
- Control user access

Start with the dashboard to get an overview, then explore each section as needed. The system is designed to be intuitive, so don't hesitate to click around and discover features!

---

## Quick Reference

| What You Want to Do | Where to Go |
|---------------------|-------------|
| See business overview | Dashboard |
| Add products | Setup → Products → Add Product |
| Receive stock | Operations → Stock In → Add Stock In |
| Make a sale | Operations → Sales → Add Sales |
| Record expenses | Operations → Expenses → Add Expense |
| View reports | Analytics → Reports |
| Manage users | Setup → Users |
| Assign roles | Settings → User Roles |
| Create roles | Settings → Roles |
| View history | Audit → Audit Trails |
| Check alerts | Notification bell (top right) |

---

**End of User Guide**

For more detailed information or specific questions, contact your system administrator.
