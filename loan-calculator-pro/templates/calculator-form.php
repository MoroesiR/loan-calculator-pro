<?php
/**
 * Loan Calculator Form Template
 */

$currency = get_option('lcp_currency_symbol', 'R');
$default_rate = get_option('lcp_default_interest_rate', '7.5');
$default_term = get_option('lcp_default_loan_term', '12');
?>

<div class="lcp-calculator-wrapper" data-theme="<?php echo esc_attr($atts['theme']); ?>">
    <div class="lcp-calculator-container">
        <h2 class="lcp-calculator-title">Loan Calculator</h2>
        
        <form id="lcp-calculator-form" class="lcp-calculator-form">
            <div class="lcp-form-group">
                <label for="lcp-loan-amount">Loan Amount (<?php echo esc_html($currency); ?>)</label>
                <input 
                    type="number" 
                    id="lcp-loan-amount" 
                    name="loan_amount" 
                    class="lcp-input" 
                    placeholder="e.g., 100000" 
                    min="1000" 
                    step="1000"
                    required
                >
            </div>
            
            <div class="lcp-form-group">
                <label for="lcp-interest-rate">Annual Interest Rate (%)</label>
                <input 
                    type="number" 
                    id="lcp-interest-rate" 
                    name="interest_rate" 
                    class="lcp-input" 
                    placeholder="e.g., 7.5" 
                    value="<?php echo esc_attr($default_rate); ?>"
                    min="0" 
                    max="50"
                    step="0.1"
                    required
                >
            </div>
            
            <div class="lcp-form-group">
                <label for="lcp-loan-term">Loan Term (Months)</label>
                <input 
                    type="number" 
                    id="lcp-loan-term" 
                    name="loan_term" 
                    class="lcp-input" 
                    placeholder="e.g., 12" 
                    value="<?php echo esc_attr($default_term); ?>"
                    min="1" 
                    max="360"
                    required
                >
                <small class="lcp-help-text">Or select: 
                    <button type="button" class="lcp-term-btn" data-term="12">12 months</button>
                    <button type="button" class="lcp-term-btn" data-term="24">24 months</button>
                    <button type="button" class="lcp-term-btn" data-term="36">36 months</button>
                    <button type="button" class="lcp-term-btn" data-term="60">60 months</button>
                </small>
            </div>
            
            <button type="submit" class="lcp-calculate-btn">
                <span class="lcp-btn-text">Calculate</span>
                <span class="lcp-loader" style="display: none;">Calculating...</span>
            </button>
        </form>
        
        <div id="lcp-results" class="lcp-results" style="display: none;">
            <h3>Calculation Results</h3>
            
            <div class="lcp-results-grid">
                <div class="lcp-result-card">
                    <div class="lcp-result-label">Monthly Payment</div>
                    <div class="lcp-result-value" id="lcp-monthly-payment">-</div>
                </div>
                
                <div class="lcp-result-card">
                    <div class="lcp-result-label">Total Payment</div>
                    <div class="lcp-result-value" id="lcp-total-payment">-</div>
                </div>
                
                <div class="lcp-result-card">
                    <div class="lcp-result-label">Total Interest</div>
                    <div class="lcp-result-value" id="lcp-total-interest">-</div>
                </div>
            </div>
            
            <?php if ($atts['show_amortization'] === 'yes'): ?>
            <div class="lcp-amortization-section">
                <h4>Amortization Schedule</h4>
                <div class="lcp-table-container">
                    <table class="lcp-amortization-table" id="lcp-amortization-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Payment</th>
                                <th>Principal</th>
                                <th>Interest</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated via JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <button type="button" class="lcp-export-btn" id="lcp-export-schedule">
                    Export Schedule (CSV)
                </button>
            </div>
            <?php endif; ?>
        </div>
        
        <div id="lcp-error" class="lcp-error-message" style="display: none;"></div>
    </div>
</div>