<?php
/**
 * Plugin Name: Loan Calculator Pro
 * Plugin URI: https://github.com/moroesi-ramodupi/loan-calculator-pro
 * Description: A professional loan calculator with amortization schedule, comparison tools, and admin dashboard. Perfect for financial websites.
 * Version: 1.0.0
 * Author: Moroesi Ramodupi
 * Author URI: https://github.com/moroesi-ramodupi
 * License: GPL v2 or later
 * Text Domain: loan-calculator-pro
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LCP_VERSION', '1.0.0');
define('LCP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LCP_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class LoanCalculatorPro {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_shortcode('loan_calculator', array($this, 'render_calculator'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_calculate_loan', array($this, 'ajax_calculate_loan'));
        add_action('wp_ajax_nopriv_calculate_loan', array($this, 'ajax_calculate_loan'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    public function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'loan_calculations';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            loan_amount decimal(15,2) NOT NULL,
            interest_rate decimal(5,2) NOT NULL,
            loan_term int(11) NOT NULL,
            monthly_payment decimal(15,2) NOT NULL,
            total_payment decimal(15,2) NOT NULL,
            total_interest decimal(15,2) NOT NULL,
            calculation_date datetime DEFAULT CURRENT_TIMESTAMP,
            user_ip varchar(45),
            PRIMARY KEY (id),
            KEY calculation_date (calculation_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        add_option('lcp_default_interest_rate', '7.5');
        add_option('lcp_default_loan_term', '12');
        add_option('lcp_currency_symbol', 'R');
    }
    
    public function deactivate() {
        // Cleanup tasks if needed
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style('lcp-frontend-style', LCP_PLUGIN_URL . 'assets/css/frontend.css', array(), LCP_VERSION);
        wp_enqueue_script('lcp-frontend-script', LCP_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), LCP_VERSION, true);
        
        wp_localize_script('lcp-frontend-script', 'lcpAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lcp_calculate_nonce'),
            'currency' => get_option('lcp_currency_symbol', 'R')
        ));
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'loan-calculator') === false) {
            return;
        }
        wp_enqueue_style('lcp-admin-style', LCP_PLUGIN_URL . 'assets/css/admin.css', array(), LCP_VERSION);
        wp_enqueue_script('lcp-admin-script', LCP_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'chart-js'), LCP_VERSION, true);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Loan Calculator Pro',
            'Loan Calculator',
            'manage_options',
            'loan-calculator-pro',
            array($this, 'render_admin_page'),
            'dashicons-calculator',
            30
        );
        
        add_submenu_page(
            'loan-calculator-pro',
            'All Calculations',
            'All Calculations',
            'manage_options',
            'loan-calculator-pro',
            array($this, 'render_admin_page')
        );
        
        add_submenu_page(
            'loan-calculator-pro',
            'Settings',
            'Settings',
            'manage_options',
            'loan-calculator-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function render_calculator($atts) {
        $atts = shortcode_atts(array(
            'theme' => 'default',
            'show_amortization' => 'yes'
        ), $atts);
        
        ob_start();
        include LCP_PLUGIN_DIR . 'templates/calculator-form.php';
        return ob_get_clean();
    }
    
    public function ajax_calculate_loan() {
        check_ajax_referer('lcp_calculate_nonce', 'nonce');
        
        $loan_amount = floatval($_POST['loan_amount'] ?? 0);
        $interest_rate = floatval($_POST['interest_rate'] ?? 0);
        $loan_term = intval($_POST['loan_term'] ?? 0);
        
        if ($loan_amount <= 0 || $interest_rate < 0 || $loan_term <= 0) {
            wp_send_json_error(array('message' => 'Invalid input values'));
            return;
        }
        
        $results = $this->calculate_loan($loan_amount, $interest_rate, $loan_term);
        $this->save_calculation($loan_amount, $interest_rate, $loan_term, $results);
        
        wp_send_json_success($results);
    }
    
    private function calculate_loan($principal, $annual_rate, $months) {
        $monthly_rate = ($annual_rate / 100) / 12;
        
        if ($monthly_rate > 0) {
            $monthly_payment = $principal * ($monthly_rate * pow(1 + $monthly_rate, $months)) / 
                              (pow(1 + $monthly_rate, $months) - 1);
        } else {
            $monthly_payment = $principal / $months;
        }
        
        $total_payment = $monthly_payment * $months;
        $total_interest = $total_payment - $principal;
        
        $amortization = array();
        $balance = $principal;
        
        for ($i = 1; $i <= $months; $i++) {
            $interest_payment = $balance * $monthly_rate;
            $principal_payment = $monthly_payment - $interest_payment;
            $balance -= $principal_payment;
            
            $amortization[] = array(
                'month' => $i,
                'payment' => round($monthly_payment, 2),
                'principal' => round($principal_payment, 2),
                'interest' => round($interest_payment, 2),
                'balance' => round(max(0, $balance), 2)
            );
        }
        
        return array(
            'monthly_payment' => round($monthly_payment, 2),
            'total_payment' => round($total_payment, 2),
            'total_interest' => round($total_interest, 2),
            'amortization_schedule' => $amortization
        );
    }
    
    private function save_calculation($amount, $rate, $term, $results) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'loan_calculations';
        
        $wpdb->insert(
            $table_name,
            array(
                'loan_amount' => $amount,
                'interest_rate' => $rate,
                'loan_term' => $term,
                'monthly_payment' => $results['monthly_payment'],
                'total_payment' => $results['total_payment'],
                'total_interest' => $results['total_interest'],
                'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ),
            array('%f', '%f', '%d', '%f', '%f', '%f', '%s')
        );
    }
    
    public function register_rest_routes() {
        register_rest_route('loan-calculator/v1', '/calculate', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_calculate_loan'),
            'permission_callback' => '__return_true',
            'args' => array(
                'loan_amount' => array('required' => true, 'type' => 'number'),
                'interest_rate' => array('required' => true, 'type' => 'number'),
                'loan_term' => array('required' => true, 'type' => 'integer')
            )
        ));
        
        register_rest_route('loan-calculator/v1', '/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_stats'),
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ));
    }
    
    public function rest_calculate_loan($request) {
        $loan_amount = floatval($request->get_param('loan_amount'));
        $interest_rate = floatval($request->get_param('interest_rate'));
        $loan_term = intval($request->get_param('loan_term'));
        
        if ($loan_amount <= 0 || $interest_rate < 0 || $loan_term <= 0) {
            return new WP_Error('invalid_data', 'Invalid input values', array('status' => 400));
        }
        
        $results = $this->calculate_loan($loan_amount, $interest_rate, $loan_term);
        return rest_ensure_response($results);
    }
    
    public function rest_get_stats($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'loan_calculations';
        
        $stats = array(
            'total_calculations' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name"),
            'avg_loan_amount' => $wpdb->get_var("SELECT AVG(loan_amount) FROM $table_name"),
            'total_loan_amount' => $wpdb->get_var("SELECT SUM(loan_amount) FROM $table_name"),
            'recent_calculations' => $wpdb->get_results(
                "SELECT * FROM $table_name ORDER BY calculation_date DESC LIMIT 10"
            )
        );
        
        return rest_ensure_response($stats);
    }
    
    public function render_admin_page() {
        include LCP_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }
    
    public function render_settings_page() {
        if (isset($_POST['lcp_save_settings'])) {
            check_admin_referer('lcp_settings_nonce');
            
            update_option('lcp_default_interest_rate', sanitize_text_field($_POST['default_interest_rate']));
            update_option('lcp_default_loan_term', sanitize_text_field($_POST['default_loan_term']));
            update_option('lcp_currency_symbol', sanitize_text_field($_POST['currency_symbol']));
            
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        include LCP_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    public function get_calculations($limit = 50) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'loan_calculations';
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name ORDER BY calculation_date DESC LIMIT %d", $limit)
        );
    }
}

// Initialize the plugin
function lcp_init() {
    return LoanCalculatorPro::get_instance();
}

add_action('plugins_loaded', 'lcp_init');