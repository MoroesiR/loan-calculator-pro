/**
 * Loan Calculator Pro - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Quick term selection buttons
        $('.lcp-term-btn').on('click', function(e) {
            e.preventDefault();
            const term = $(this).data('term');
            $('#lcp-loan-term').val(term);
        });
        
        // Form submission
        $('#lcp-calculator-form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('.lcp-calculate-btn');
            const btnText = submitBtn.find('.lcp-btn-text');
            const loader = submitBtn.find('.lcp-loader');
            
            // Get form values
            const loanAmount = parseFloat($('#lcp-loan-amount').val());
            const interestRate = parseFloat($('#lcp-interest-rate').val());
            const loanTerm = parseInt($('#lcp-loan-term').val());
            
            // Basic validation
            if (isNaN(loanAmount) || loanAmount <= 0) {
                showError('Please enter a valid loan amount');
                return;
            }
            
            if (isNaN(interestRate) || interestRate < 0) {
                showError('Please enter a valid interest rate');
                return;
            }
            
            if (isNaN(loanTerm) || loanTerm <= 0) {
                showError('Please enter a valid loan term');
                return;
            }
            
            // Show loading state
            submitBtn.prop('disabled', true);
            btnText.hide();
            loader.show();
            hideError();
            
            // Make AJAX request
            $.ajax({
                url: lcpAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'calculate_loan',
                    nonce: lcpAjax.nonce,
                    loan_amount: loanAmount,
                    interest_rate: interestRate,
                    loan_term: loanTerm
                },
                success: function(response) {
                    if (response.success) {
                        displayResults(response.data);
                    } else {
                        showError(response.data.message || 'Calculation failed');
                    }
                },
                error: function(xhr, status, error) {
                    showError('An error occurred. Please try again.');
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    btnText.show();
                    loader.hide();
                }
            });
        });
        
        // Display calculation results
        function displayResults(data) {
            const currency = lcpAjax.currency || 'R';
            
            // Update result cards
            $('#lcp-monthly-payment').text(currency + ' ' + formatNumber(data.monthly_payment));
            $('#lcp-total-payment').text(currency + ' ' + formatNumber(data.total_payment));
            $('#lcp-total-interest').text(currency + ' ' + formatNumber(data.total_interest));
            
            // Populate amortization table
            if (data.amortization_schedule && data.amortization_schedule.length > 0) {
                populateAmortizationTable(data.amortization_schedule, currency);
            }
            
            // Show results section
            $('#lcp-results').slideDown(400);
            
            // Scroll to results
            $('html, body').animate({
                scrollTop: $('#lcp-results').offset().top - 100
            }, 500);
        }
        
        // Populate amortization table
        function populateAmortizationTable(schedule, currency) {
            const tbody = $('#lcp-amortization-table tbody');
            tbody.empty();
            
            // Show first 12 months by default
            const displayLimit = schedule.length > 12 ? 12 : schedule.length;
            
            for (let i = 0; i < displayLimit; i++) {
                const row = schedule[i];
                const tr = $('<tr>');
                
                tr.append($('<td>').text(row.month));
                tr.append($('<td>').text(currency + ' ' + formatNumber(row.payment)));
                tr.append($('<td>').text(currency + ' ' + formatNumber(row.principal)));
                tr.append($('<td>').text(currency + ' ' + formatNumber(row.interest)));
                tr.append($('<td>').text(currency + ' ' + formatNumber(row.balance)));
                
                tbody.append(tr);
            }
            
            // If there are more rows, add a "Show All" row
            if (schedule.length > displayLimit) {
                const showAllRow = $('<tr class="lcp-show-all-row">');
                showAllRow.append($('<td colspan="5" style="text-align: center;">'));
                
                const showAllBtn = $('<button type="button" class="lcp-term-btn">')
                    .text('Show All ' + schedule.length + ' Months')
                    .on('click', function() {
                        tbody.empty();
                        schedule.forEach(function(row) {
                            const tr = $('<tr>');
                            tr.append($('<td>').text(row.month));
                            tr.append($('<td>').text(currency + ' ' + formatNumber(row.payment)));
                            tr.append($('<td>').text(currency + ' ' + formatNumber(row.principal)));
                            tr.append($('<td>').text(currency + ' ' + formatNumber(row.interest)));
                            tr.append($('<td>').text(currency + ' ' + formatNumber(row.balance)));
                            tbody.append(tr);
                        });
                    });
                
                showAllRow.find('td').append(showAllBtn);
                tbody.append(showAllRow);
            }
            
            // Store schedule for export
            window.lcpAmortizationData = schedule;
        }
        
        // Export amortization schedule to CSV
        $('#lcp-export-schedule').on('click', function() {
            if (!window.lcpAmortizationData) {
                alert('No data to export');
                return;
            }
            
            const currency = lcpAjax.currency || 'R';
            let csv = 'Month,Payment,Principal,Interest,Balance\n';
            
            window.lcpAmortizationData.forEach(function(row) {
                csv += `${row.month},${currency} ${row.payment},${currency} ${row.principal},${currency} ${row.interest},${currency} ${row.balance}\n`;
            });
            
            // Create download link
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'amortization-schedule.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        });
        
        // Helper: Format number with commas
        function formatNumber(num) {
            return parseFloat(num).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        // Helper: Show error message
        function showError(message) {
            $('#lcp-error').text(message).slideDown(300);
        }
        
        // Helper: Hide error message
        function hideError() {
            $('#lcp-error').slideUp(300);
        }
        
    });
    
})(jQuery);