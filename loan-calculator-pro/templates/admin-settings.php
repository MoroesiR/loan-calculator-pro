<?php
/**
 * Admin Settings Template
 */

if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$default_rate = get_option('lcp_default_interest_rate', '7.5');
$default_term = get_option('lcp_default_loan_term', '12');
$currency = get_option('lcp_currency_symbol', 'R');
?>

<div class="wrap lcp-admin-wrap">
    <h1 class="lcp-admin-title">
        <span class="dashicons dashicons-admin-settings"></span>
        Loan Calculator Settings
    </h1>
    
    <form method="post" action="" class="lcp-settings-form">
        <?php wp_nonce_field('lcp_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="default_interest_rate">Default Interest Rate (%)</label>
                </th>
                <td>
                    <input 
                        type="number" 
                        id="default_interest_rate" 
                        name="default_interest_rate" 
                        value="<?php echo esc_attr($default_rate); ?>" 
                        step="0.1"
                        min="0"
                        max="50"
                        class="regular-text"
                    >
                    <p class="description">Default interest rate to pre-fill in the calculator form.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="default_loan_term">Default Loan Term (Months)</label>
                </th>
                <td>
                    <input 
                        type="number" 
                        id="default_loan_term" 
                        name="default_loan_term" 
                        value="<?php echo esc_attr($default_term); ?>" 
                        min="1"
                        max="360"
                        class="regular-text"
                    >
                    <p class="description">Default loan term in months to pre-fill in the calculator form.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="currency_symbol">Currency Symbol</label>
                </th>
                <td>
                    <input 
                        type="text" 
                        id="currency_symbol" 
                        name="currency_symbol" 
                        value="<?php echo esc_attr($currency); ?>" 
                        class="regular-text"
                        maxlength="5"
                    >
                    <p class="description">Currency symbol to display (e.g., R, $, €, £).</p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input 
                type="submit" 
                name="lcp_save_settings" 
                id="submit" 
                class="button button-primary" 
                value="Save Settings"
            >
        </p>
    </form>
    
    <hr>
    
    <div class="lcp-info-section">
        <h2>Usage Instructions</h2>
        
        <div class="lcp-info-card">
            <h3>1. Display Calculator on Your Site</h3>
            <p>Add the following shortcode to any page or post:</p>
            <code class="lcp-code-block">[loan_calculator]</code>
            
            <h4>Shortcode Parameters:</h4>
            <ul>
                <li><code>theme="default"</code> - Visual theme (default)</li>
                <li><code>show_amortization="yes"</code> - Show/hide amortization schedule (yes/no)</li>
            </ul>
            
            <p><strong>Example:</strong></p>
            <code class="lcp-code-block">[loan_calculator theme="default" show_amortization="yes"]</code>
        </div>
        
        <div class="lcp-info-card">
            <h3>2. REST API Usage</h3>
            <p>You can integrate the calculator via REST API:</p>
            
            <h4>Calculate Loan:</h4>
            <code class="lcp-code-block">
                POST <?php echo rest_url('loan-calculator/v1/calculate'); ?><br>
                Content-Type: application/json<br><br>
                {<br>
                &nbsp;&nbsp;"loan_amount": 100000,<br>
                &nbsp;&nbsp;"interest_rate": 7.5,<br>
                &nbsp;&nbsp;"loan_term": 12<br>
                }
            </code>
            
            <h4>Get Statistics (Admin Only):</h4>
            <code class="lcp-code-block">
                GET <?php echo rest_url('loan-calculator/v1/stats'); ?>
            </code>
        </div>
        
        <div class="lcp-info-card">
            <h3>3. PHP Function Usage</h3>
            <p>Use the calculator programmatically in your theme:</p>
            <code class="lcp-code-block">
                &lt;?php echo do_shortcode('[loan_calculator]'); ?&gt;
            </code>
        </div>
    </div>
</div>