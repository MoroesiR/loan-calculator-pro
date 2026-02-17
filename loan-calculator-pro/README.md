---

# **Loan Calculator Pro - WordPress Plugin**

A professional loan calculator plugin for WordPress with amortization schedules and admin dashboard functionality.

**Version:** 1.0.0  
**Tested with:** WordPress 5.0+  
**PHP:** 7.4+  

---

## **What It Does**

This plugin adds a complete loan calculator to your WordPress site where users can:
- Calculate monthly loan payments with real-time results
- View detailed amortization schedules with monthly breakdowns
- Download payment schedules as CSV files
- Responsive design that works on all devices

**For site admins:**
- Dashboard with calculation statistics and analytics
- Export functionality for all calculation data
- Customizable default settings
- Visual charts showing usage trends

---

## **Installation**

1. Download the ZIP file
2. Go to **Plugins → Add New → Upload Plugin** in WordPress
3. Upload the ZIP file
4. Click **Activate**

The plugin will automatically create the required database tables.

---

## **How to Use It**

### **Add to a Page or Post**

Use the shortcode:

```
[loan_calculator]
```

Customization options:
```
[loan_calculator theme="professional" show_amortization="yes"]
```

### **Add to a Theme File**

If you're editing theme files:

```php
<?php echo do_shortcode('[loan_calculator]'); ?>
```

---

## **For Developers**

### **REST API**

The plugin includes REST API endpoints for external integration:

**Calculate a loan:**
```
POST /wp-json/loan-calculator/v1/calculate
```

Request body:
```json
{
  "loan_amount": 50000,
  "interest_rate": 8.5,
  "loan_term": 24
}
```

### **Database Structure**

```sql
CREATE TABLE wp_loan_calculations (
  id INT AUTO_INCREMENT,
  loan_amount DECIMAL(15,2),
  interest_rate DECIMAL(5,2),
  loan_term INT,
  monthly_payment DECIMAL(15,2),
  total_payment DECIMAL(15,2),
  total_interest DECIMAL(15,2),
  calculation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  user_ip VARCHAR(45),
  PRIMARY KEY (id)
);
```

### **Code Architecture**
```
loan-calculator-pro/
├── loan-calculator-pro.php    # Main plugin file (all core logic)
├── assets/
│   ├── css/                   # Frontend & admin styles
│   └── js/                    # Frontend & admin scripts
└── templates/
    ├── calculator-form.php    # Calculator UI
    ├── admin-dashboard.php    # Admin stats page
    └── admin-settings.php     # Settings page
```

---

## **Technical Implementation**

The plugin implements:
- Object-oriented PHP following WordPress coding standards
- Secure AJAX requests with nonce verification
- Prepared SQL statements to prevent SQL injection
- Comprehensive input validation and sanitization
- Responsive CSS with modern design patterns
- Chart.js integration for admin analytics


---

## **Future Roadmap**

Planned enhancements:
- Multiple currency support
- Advanced reporting features
- User account integration for saved calculations
- Additional calculation methods (compound interest, etc.)
- Multilingual support

---

## **Troubleshooting**

**Calculator not displaying:**
- Verify the shortcode is correctly placed
- Check browser console for JavaScript errors
- Ensure no other plugins are conflicting

**Calculation errors:**
- Verify input formats (use decimal for percentages: 7.5)
- Check PHP version compatibility (requires 7.4+)

---

## **About Me**

In my day job I build and maintain production loan 
management systems with PHP, MySQL and payment gateway integrations 
- so building a loan calculator felt like familiar territory, 
just in a different environment.

This plugin was my first serious dive into WordPress development 
and I genuinely enjoyed figuring out how the hooks system works.

**GitHub:** [github.com/MoroesiR](https://github.com/MoroesiR)  
**Email:** mavundlamoroesi@gmail.com  
**Open to:** Remote software development opportunities
---

## **License**

GPL v2 - Same as WordPress. Feel free to use, modify, or improve it!

---

