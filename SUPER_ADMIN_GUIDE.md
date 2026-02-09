# CShop Retail Management System - Super Admin Guide

## Welcome Super Admin!

This comprehensive guide shows you everything you need to know about managing the CShop system. As Super Admin, you have full access to all features and are responsible for system setup, user management, and maintaining the system's integrity.

---

## Table of Contents

1. [Getting Started as Super Admin](#getting-started-as-super-admin)
2. [System Setup & Configuration](#system-setup--configuration)
3. [Branch Management](#branch-management)
4. [User Management](#user-management)
5. [Role & Permission Management](#role--permission-management)
6. [System Monitoring](#system-monitoring)
7. [Audit Trails & Security](#audit-trails--security)
8. [Troubleshooting](#troubleshooting)
9. [Complete Workflow](#complete-workflow)

---

## Getting Started as Super Admin

### What is a Super Admin?

As Super Admin, you are the ultimate authority in the system. You can:
- Access all modules and features
- Create and manage branches
- Create and manage users
- Create and manage roles and permissions
- View all audit trails and activity logs
- Override any permission check
- Configure system settings

### Your Responsibilities

1. **Initial Setup**: Configure branches and create initial users
2. **User Management**: Create accounts for branch admins and staff
3. **Access Control**: Define roles and assign appropriate permissions
4. **System Health**: Monitor activity logs and audit trails
5. **Security**: Ensure proper access controls are in place

### Logging In

1. Open your web browser and go to your CShop website
2. Enter your Super Admin credentials
3. Click "Log In"
4. You will see the full dashboard with all features accessible

---

## System Setup & Configuration

### Step 1: Create Branches

Before users can use the system, you need to create branches (store locations).

**How to Create a Branch:**

1. Go to **Setup** → **Branches**
2. Click **Add Branch**
3. Fill in branch details:
   - **Name**: Branch/store name (e.g., "Downtown Store")
   - **Address**: Physical location
   - **Phone**: Contact number
   - **Email**: Branch email
4. Click **Save**

**Repeat** for each branch you operate.

### Step 2: Create Categories

Organize your products into categories for better management.

**How to Create Categories:**

1. Go to **Setup** → **Categories**
2. Click **Add Category**
3. Enter category name (e.g., "Beverages", "Snacks", "Dairy")
4. Click **Save**

### Step 3: Set Up Bulk Units & Types

If you sell products in bulk (packaged quantities), configure these first.

**How to Set Up Bulk Units:**

1. Go to **Setup** → **Bulk Units & Types**
2. Click **Add Bulk Unit**
3. Enter:
   - **Name**: Unit name (e.g., "Case", "Carton", "Box")
   - **Quantity**: How many individual items per bulk unit
4. Click **Save**

**How to Set Up Bulk Types:**

1. Go to **Setup** → **Bulk Units & Types**
2. Click **Add Bulk Type**
3. Enter:
   - **Name**: Type name (e.g., "Cigarettes", "Beverages")
   - **Description**: What this type is for
4. Click **Save**

### Step 4: Create Initial Products

Add your product catalog.

**How to Add Products:**

1. Go to **Setup** → **Products** → **Add Product**
2. Fill in product details:
   - **Name**: Product name
   - **Category**: Select from dropdown
   - **Cost Price**: Purchase price
   - **Selling Price**: Retail price
   - **Expiry Date**: For perishable items
   - **Opening Stock**: Initial quantity
   - **Bulk Enabled**: Check if sold in bulk
   - **Bulk Type**: Select if bulk enabled
3. Click **Save**

---

## Branch Management

### Viewing All Branches

1. Go to **Setup** → **Branches**
2. See all branches with:
   - Branch name
   - Address
   - Contact information
   - Number of users assigned

### Editing a Branch

1. Go to **Setup** → **Branches**
2. Find the branch you want to edit
3. Click the edit button
4. Update branch details
5. Click **Save**

### Deleting a Branch

**Warning**: Deleting a branch will remove all associated data including users, products, and transactions.

1. Go to **Setup** → **Branches**
2. Find the branch you want to delete
3. Click the delete button
4. Confirm deletion

**Best Practice**: Archive branches instead of deleting when possible.

---

## User Management

### Creating Users

**Step 1: Go to User Management**

1. Go to **Setup** → **Users**
2. Click **Add User**

**Step 2: Fill in User Details**

- **Name**: Full name of the user
- **Email**: Email address (used for login)
- **Branch**: Select which branch they work at
- **Password**: Create a secure password
- **Confirm Password**: Re-enter password

**Step 3: Save the User**

Click **Save** to create the user account.

**Step 4: Assign Roles**

After creating the user, assign them to roles:
1. Go to **Settings** → **User Roles**
2. Select the branch
3. Select the user
4. Check the roles to assign
5. Click **Save**

### Editing Users

1. Go to **Setup** → **Users**
2. Find the user
3. Click edit
4. Update details
5. Click **Save**

### Deleting Users

1. Go to **Setup** → **Users**
2. Find the user
3. Click delete
4. Confirm deletion

**Note**: Deleting a user removes their access but preserves their transaction history.

### User Roles Explained

**Super Admin** (You):
- Full system access
- Can manage everything
- Bypasses all permission checks

**Branch Admin**:
- Manages a specific branch
- Can create/edit products for their branch
- Can manage stock and sales
- Can view reports for their branch

**Cashier**:
- Can only process sales
- Cannot edit products or stock
- Limited to sales operations

**Storekeeper**:
- Can manage products and stock
- Can receive stock in
- Cannot process sales

**Accountant**:
- Can view reports
- Can record expenses
- Cannot manage products or stock

---

## Role & Permission Management

### Understanding Permissions

Permissions control what users can do. They are organized by module and action:

**Branches Module:**
- `branches.view` - View branch list
- `branches.create` - Create new branches
- `branches.edit` - Edit branch details
- `branches.delete` - Delete branches

**Users Module:**
- `users.view` - View user list
- `users.create` - Create new users
- `users.edit` - Edit user details
- `users.delete` - Delete users

**RBAC Module:**
- `rbac.roles.view` - View roles
- `rbac.roles.create` - Create roles
- `rbac.roles.edit` - Edit roles
- `rbac.roles.delete` - Delete roles
- `rbac.permissions.view` - View permissions
- `rbac.user_roles.assign` - Assign roles to users

**Setup Modules:**
- `setup.categories.view/create/edit/delete` - Manage categories
- `setup.bulk.view/create/edit/delete` - Manage bulk units/types

**Products Module:**
- `products.view` - View products
- `products.create` - Add products
- `products.edit` - Edit products
- `products.delete` - Delete products

**Stock In Module:**
- `stock_in.view` - View stock receipts
- `stock_in.post` - Create stock receipts
- `stock_in.edit` - Edit stock receipts
- `stock_in.delete` - Delete stock receipts

**Sales Module:**
- `sales.view` - View sales
- `sales.post` - Create sales
- `sales.edit` - Edit sales
- `sales.delete` - Delete sales

**Expenses Module:**
- `expenses.view` - View expenses
- `expenses.create` - Add expenses
- `expenses.edit` - Edit expenses
- `expenses.delete` - Delete expenses

**Reports Module:**
- `reports.sales` - View sales reports
- `reports.profit` - View profit reports
- `reports.stock` - View stock reports
- `reports.expenses` - View expense reports
- `reports.expiry` - View expiry reports

**Audit Module:**
- `audit.stock_movements.view` - View stock movement history
- `audit.activity_logs.view` - View user activity logs

**Alerts Module:**
- `alerts.stock_adjustment` - View stock adjustment alerts
- `alerts.expired_stock` - View expired stock alerts
- `alerts.expiry_warning` - View expiry warning alerts
- `alerts.low_stock` - View low stock alerts

### Creating Custom Roles

**Step 1: Go to Roles Management**

1. Go to **Settings** → **Roles**
2. Click **Add Role**

**Step 2: Define the Role**

- **Role Name**: What to call this role (e.g., "Inventory Manager")
- **Branch**: Which branch this role applies to

**Step 3: Select Permissions**

Expand permission groups and check the permissions you want:

**Example: Inventory Manager Role**
- Products: view, create, edit
- Stock In: view, post, edit
- Reports: stock, expiry
- Alerts: low_stock, expiry_warning

**Step 4: Save the Role**

Click **Save** to create the role.

### Editing Roles

1. Go to **Settings** → **Roles**
2. Find the role
3. Click edit
4. Update role name or permissions
5. Click **Save**

### Deleting Roles

1. Go to **Settings** → **Roles**
2. Find the role
3. Click delete
4. Confirm deletion

**Warning**: Deleting a role removes it from all users who had it assigned.

### Assigning Roles to Users

**Step 1: Go to User Roles**

1. Go to **Settings** → **User Roles**

**Step 2: Select Branch and User**

- Select the branch
- Select the user

**Step 3: Assign Roles**

Check the roles you want to assign to this user for this branch.

**Step 4: Save**

Click **Save** to assign the roles.

**Note**: A user can have different roles in different branches. This is called "branch-scoped roles."

---

## System Monitoring

### Dashboard Overview

As Super Admin, your dashboard shows:

**KPI Cards:**
- Total sales across all branches
- Total inventory value
- Low stock items needing attention

**Recent Alerts:**
- Stock adjustments
- Expired stock
- Expiry warnings
- Low stock alerts

**Quick Access:**
- All modules are accessible from the sidebar

### Monitoring Branch Performance

**Step 1: Check Sales Reports**

1. Go to **Analytics** → **Reports** → **Sales**
2. See sales data across all branches
3. Identify top-performing branches

**Step 2: Check Profit Reports**

1. Go to **Analytics** → **Reports** → **Profit**
2. See profitability by branch
3. Identify areas for improvement

**Step 3: Check Stock Reports**

1. Go to **Analytics** → **Reports** → **Stock**
2. See inventory levels by branch
3. Identify low stock issues

### Monitoring User Activity

**Step 1: Check Activity Logs**

1. Go to **Audit** → **Audit Trails** → **Activity Logs**
2. See all user actions:
   - Who did what
   - When they did it
   - What module they used

**Step 2: Check Stock Movements**

1. Go to **Audit** → **Audit Trails** → **Stock Movements**
2. See all inventory changes:
   - Stock receipts (IN)
   - Sales (OUT)
   - Adjustments

### Managing Alerts

**Step 1: Check Notification Bell**

1. Look at the bell icon in the top right
2. Number shows unread alerts
3. Click to see recent alerts

**Step 2: Review Dashboard Alerts**

1. Check the **Alerts** section on dashboard
2. See most recent alerts with:
   - Type of alert
   - What it's about
   - When it occurred
   - Mark as read option

**Step 3: Take Action**

- **Expired Stock**: Remove from inventory
- **Expiry Warning**: Plan for replacement or discount
- **Low Stock**: Reorder items
- **Stock Adjustment**: Investigate discrepancy

---

## Audit Trails & Security

### Viewing Audit Trails

**Activity Logs:**

1. Go to **Audit** → **Audit Trails** → **Activity Logs**
2. See complete history of:
   - User logins
   - Data changes
   - Module access
   - Permission checks

**Stock Movements:**

1. Go to **Audit** → **Audit Trails** → **Stock Movements**
2. See complete history of:
   - Stock receipts
   - Sales transactions
   - Manual adjustments

### Security Best Practices

**1. User Management**
- Create strong passwords for all users
- Change passwords regularly
- Remove access for former employees immediately
- Review user access periodically

**2. Role Management**
- Give users only the permissions they need
- Use custom roles instead of giving everyone full access
- Review role assignments regularly
- Remove unused roles

**3. System Monitoring**
- Check activity logs weekly
- Review stock movements daily
- Monitor alerts regularly
- Investigate suspicious activity

**4. Data Integrity**
- Regularly check for expired stock
- Verify stock levels match physical inventory
- Review sales for accuracy
- Audit expense records

### Troubleshooting Common Issues

**Issue: User cannot access a module**

**Solution:**
1. Check if user has the required permission
2. Verify role assignment
3. Ensure user is assigned to the correct branch

**Issue: Stock levels don't match physical inventory**

**Solution:**
1. Check stock movement logs
2. Look for manual adjustments
3. Review sales receipts
4. Check for voided transactions

**Issue: Reports showing incorrect data**

**Solution:**
1. Verify data in source modules
2. Check for unprocessed transactions
3. Review audit trails for errors
4. Contact technical support if needed

---

## Complete Workflow

### Phase 1: Initial Setup (First Time Setup)

**Step 1: Configure System**

1. Log in as Super Admin
2. Create branches for each store location
3. Create product categories
4. Set up bulk units and types (if applicable)

**Step 2: Create Initial Users**

1. Create Branch Admin accounts for each branch
2. Create cashier accounts for each branch
3. Create storekeeper accounts for each branch
4. Create accountant accounts for each branch

**Step 3: Assign Roles**

1. Go to **Settings** → **User Roles**
2. For each user, assign appropriate roles:
   - Branch Admin: All permissions for their branch
   - Cashier: Sales permissions only
   - Storekeeper: Product and stock permissions
   - Accountant: Reports and expenses permissions

**Step 4: Add Products**

1. Go to **Setup** → **Products** → **Add Product**
2. Add all products to the catalog
3. Set opening stock for each product
4. Configure bulk settings if needed

### Phase 2: Daily Operations

**Morning Routine:**

1. Check dashboard for alerts
2. Review low stock items
3. Check for expired products
4. Review yesterday's sales

**Throughout the Day:**

1. Monitor activity logs
2. Check stock movements
3. Review new user registrations
4. Respond to alerts

**Evening Routine:**

1. Review daily sales
2. Check stock levels
3. Verify expense records
4. Review audit trails

### Phase 3: Weekly Tasks

**Monday:**
- Review weekly sales reports
- Check profit margins
- Identify low stock items

**Wednesday:**
- Review activity logs
- Check for security issues
- Verify user access

**Friday:**
- Review weekly expenses
- Check stock movement logs
- Prepare for next week

### Phase 4: Monthly Tasks

**First Week:**
- Review monthly reports
- Audit inventory levels
- Review user roles

**Second Week:**
- Check system performance
- Review alert patterns
- Update documentation

**Third Week:**
- Review profit trends
- Check for expired products
- Audit expense records

**Fourth Week:**
- Monthly system review
- Plan for next month
- Update user access

---

## Advanced Procedures

### Managing Multi-Branch Operations

**Scenario: You have multiple branches**

**Step 1: Create Branches**

1. Go to **Setup** → **Branches**
2. Create each branch with location details

**Step 2: Create Branch Admins**

1. Create a user for each branch
2. Assign them as Branch Admin for their branch only

**Step 3: Assign Staff**

1. Create cashier, storekeeper, accountant accounts
2. Assign them to their respective branches
3. Give them appropriate roles

**Step 4: Monitor All Branches**

1. Check reports for each branch
2. Compare performance
3. Identify issues early

### Handling User Turnover

**When an employee leaves:**

**Step 1: Revoke Access**

1. Go to **Setup** → **Users**
2. Find the user
3. Delete their account

**Step 2: Review Their Actions**

1. Go to **Audit** → **Activity Logs**
2. Filter by the user
3. Review what they did

**Step 3: Assign Replacement**

1. Create new user account
2. Assign same roles to replacement
3. Train replacement on system use

### Investigating Discrepancies

**When something doesn't add up:**

**Step 1: Check Audit Trails**

1. Go to **Audit** → **Activity Logs**
2. Filter by date/time range
3. Look for suspicious activity

**Step 2: Check Stock Movements**

1. Go to **Audit** → **Stock Movements**
2. Review all transactions
3. Look for manual adjustments

**Step 3: Review Alerts**

1. Check for stock adjustment alerts
2. Look for unusual patterns
3. Identify the source

**Step 4: Take Action**

1. Correct the discrepancy
2. Document what happened
3. Prevent future occurrences

---

## System Maintenance

### Regular Backups

Ensure regular backups are scheduled:
- Database backups daily
- File backups weekly
- Test backup restoration monthly

### Performance Monitoring

Check system performance:
- Page load times
- Report generation speed
- User feedback on slowness

### Security Updates

Keep the system secure:
- Update passwords regularly
- Review user access quarterly
- Monitor for suspicious activity
- Keep software updated

---

## Emergency Procedures

### System Down

**If the system becomes unavailable:**

1. Contact technical support immediately
2. Document what happened
3. Note the time of outage
4. Inform affected users

### Data Loss

**If data is lost or corrupted:**

1. Stop using the system immediately
2. Contact technical support
3. Restore from most recent backup
4. Verify data integrity

### Security Breach

**If unauthorized access is suspected:**

1. Change all passwords immediately
2. Review activity logs
3. Identify the breach source
4. Close security holes
5. Document the incident

---

## Getting Help

### Technical Support

For technical issues:
- Contact your IT support team
- Provide error messages
- Document steps to reproduce
- Include screenshots if possible

### System Questions

For questions about system features:
- Refer to this guide
- Check the user guide for non-technical users
- Contact your system administrator

### Training Resources

- User Guide: For all users
- Super Admin Guide: This document
- In-person training: Contact your administrator

---

## Summary

As Super Admin, you are responsible for:

1. **System Setup**: Creating branches, users, and initial data
2. **User Management**: Creating accounts and assigning roles
3. **Access Control**: Defining permissions and managing security
4. **System Monitoring**: Checking alerts, reports, and audit trails
5. **Maintenance**: Regular backups, updates, and security checks

Your dashboard provides:
- Complete system overview
- Real-time alerts
- Quick access to all modules
- Detailed audit trails

Remember:
- You have full access to everything
- You can bypass all permission checks
- You are responsible for system integrity
- Monitor regularly to catch issues early

---

## Quick Reference for Super Admin

| Task | Where to Go | What to Do |
|------|-------------|------------|
| Create Branch | Setup → Branches | Click Add Branch, fill details, save |
| Create User | Setup → Users | Click Add User, fill details, save |
| Assign Roles | Settings → User Roles | Select branch, user, check roles, save |
| Create Role | Settings → Roles | Click Add Role, select permissions, save |
| View Reports | Analytics → Reports | Select report type, view data |
| Check Activity | Audit → Activity Logs | View all user actions |
| Check Stock Movements | Audit → Stock Movements | View inventory changes |
| View Alerts | Dashboard or Notification Bell | See recent alerts, mark as read |
| Manage Products | Setup → Products | Add, edit, or delete products |
| Receive Stock | Operations → Stock In | Add stock receipts |
| Process Sales | Operations → Sales | Record sales transactions |
| Record Expenses | Operations → Expenses | Track business expenses |

---

## Best Practices Checklist

- [ ] Create all branches before adding users
- [ ] Assign appropriate roles to each user
- [ ] Check activity logs weekly
- [ ] Review stock movements daily
- [ ] Monitor alerts regularly
- [ ] Run regular backups
- [ ] Update passwords regularly
- [ ] Review user access quarterly
- [ ] Keep documentation updated
- [ ] Train new users properly

---

**End of Super Admin Guide**

For questions or issues, contact your technical support team or system administrator.

---

## Appendix: Permission Matrix

### Branch Admin Permissions (Recommended)
```
branches.view
users.view
products.view, create, edit
stock_in.view, post, edit
sales.view, post, edit
expenses.view, create, edit
reports.sales, profit, stock, expenses, expiry
audit.stock_movements.view, audit.activity_logs.view
alerts.stock_adjustment, alerts.expired_stock, alerts.expiry_warning, alerts.low_stock
```

### Cashier Permissions (Recommended)
```
sales.view, sales.post
alerts.low_stock
```

### Storekeeper Permissions (Recommended)
```
products.view, create, edit
stock_in.view, post, edit
reports.stock, reports.expiry
alerts.stock_adjustment, alerts.expired_stock, alerts.expiry_warning, alerts.low_stock
```

### Accountant Permissions (Recommended)
```
expenses.view, create, edit
reports.sales, reports.profit, reports.expenses
```

---

**Remember**: As Super Admin, you can always override any permission check if needed. Use this power responsibly!
