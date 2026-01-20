<?php
/**
 * Admin Dashboard Template
 */

if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

global $wpdb;
$table_name = $wpdb->prefix . 'loan_calculations';

// Get statistics
$total_calculations = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$avg_loan_amount = $wpdb->get_var("SELECT AVG(loan_amount) FROM $table_name");
$total_loan_value = $wpdb->get_var("SELECT SUM(loan_amount) FROM $table_name");
$recent_calculations = $wpdb->get_results("SELECT * FROM $table_name ORDER BY calculation_date DESC LIMIT 20");

$currency = get_option('lcp_currency_symbol', 'R');
?>

<div class="wrap lcp-admin-wrap">
    <h1 class="lcp-admin-title">
        <span class="dashicons dashicons-calculator"></span>
        Loan Calculator Pro Dashboard
    </h1>
    
    <div class="lcp-admin-notice">
        <p><strong>Shortcode:</strong> Use <code>[loan_calculator]</code> to display the calculator on any page or post.</p>
        <p><strong>API Endpoint:</strong> <code><?php echo rest_url('loan-calculator/v1/calculate'); ?></code></p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="lcp-stats-grid">
        <div class="lcp-stat-card">
            <div class="lcp-stat-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="lcp-stat-content">
                <h3><?php echo number_format($total_calculations); ?></h3>
                <p>Total Calculations</p>
            </div>
        </div>
        
        <div class="lcp-stat-card">
            <div class="lcp-stat-icon">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="lcp-stat-content">
                <h3><?php echo $currency . ' ' . number_format($avg_loan_amount, 2); ?></h3>
                <p>Average Loan Amount</p>
            </div>
        </div>
        
        <div class="lcp-stat-card">
            <div class="lcp-stat-icon">
                <span class="dashicons dashicons-portfolio"></span>
            </div>
            <div class="lcp-stat-content">
                <h3><?php echo $currency . ' ' . number_format($total_loan_value, 2); ?></h3>
                <p>Total Loan Value</p>
            </div>
        </div>
        
        <div class="lcp-stat-card">
            <div class="lcp-stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="lcp-stat-content">
                <h3><?php echo $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE DATE(calculation_date) = CURDATE()"); ?></h3>
                <p>Calculations Today</p>
            </div>
        </div>
    </div>
    
    <!-- Chart Section -->
    <div class="lcp-chart-section">
        <h2>Last 7 Days Activity</h2>
        <canvas id="lcp-activity-chart" width="400" height="100"></canvas>
    </div>
    
    <!-- Recent Calculations Table -->
    <div class="lcp-table-section">
        <h2>Recent Calculations</h2>
        
        <?php if (empty($recent_calculations)): ?>
            <div class="lcp-empty-state">
                <span class="dashicons dashicons-info"></span>
                <p>No calculations yet. Add the <code>[loan_calculator]</code> shortcode to a page to get started!</p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Loan Amount</th>
                        <th>Interest Rate</th>
                        <th>Term (Months)</th>
                        <th>Monthly Payment</th>
                        <th>Total Payment</th>
                        <th>Total Interest</th>
                        <th>Date</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_calculations as $calc): ?>
                        <tr>
                            <td><?php echo esc_html($calc->id); ?></td>
                            <td><?php echo $currency . ' ' . number_format($calc->loan_amount, 2); ?></td>
                            <td><?php echo number_format($calc->interest_rate, 2); ?>%</td>
                            <td><?php echo esc_html($calc->loan_term); ?></td>
                            <td><?php echo $currency . ' ' . number_format($calc->monthly_payment, 2); ?></td>
                            <td><?php echo $currency . ' ' . number_format($calc->total_payment, 2); ?></td>
                            <td><?php echo $currency . ' ' . number_format($calc->total_interest, 2); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($calc->calculation_date)); ?></td>
                            <td><?php echo esc_html($calc->user_ip); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="lcp-table-actions">
                <a href="#" class="button" id="lcp-export-all">Export All Data (CSV)</a>
                <a href="#" class="button button-secondary" id="lcp-clear-data">Clear All Data</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Chart.js initialization
document.addEventListener('DOMContentLoaded', function() {
    <?php
    // Get last 7 days data
    $dates = array();
    $counts = array();
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE DATE(calculation_date) = %s",
            $date
        ));
        $dates[] = date('M d', strtotime($date));
        $counts[] = $count ? $count : 0;
    }
    ?>
    
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById('lcp-activity-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($dates); ?>,
                    datasets: [{
                        label: 'Calculations',
                        data: <?php echo json_encode($counts); ?>,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    }
});
</script>