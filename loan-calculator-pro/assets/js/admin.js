/**
 * Loan Calculator Pro - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Export all data to CSV
        $('#lcp-export-all').on('click', function(e) {
            e.preventDefault();
            
            const table = $('.wp-list-table');
            if (!table.length) {
                alert('No data to export');
                return;
            }
            
            let csv = '';
            
            // Add headers
            table.find('thead tr th').each(function() {
                csv += '"' + $(this).text().trim() + '",';
            });
            csv = csv.slice(0, -1) + '\n';
            
            // Add data rows
            table.find('tbody tr').each(function() {
                $(this).find('td').each(function() {
                    csv += '"' + $(this).text().trim() + '",';
                });
                csv = csv.slice(0, -1) + '\n';
            });
            
            // Create download
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'loan-calculator-data-' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        });
        
        // Clear all data with confirmation
        $('#lcp-clear-data').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete all calculation data? This action cannot be undone.')) {
                return;
            }
            
            if (!confirm('This will permanently delete all records. Are you absolutely sure?')) {
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'lcp_clear_all_data',
                    nonce: $('#lcp_admin_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('All data has been cleared successfully.');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.data.message || 'Failed to clear data'));
                    }
                },
                error: function() {
                    alert('An error occurred while clearing data.');
                }
            });
        });
        
        // Settings form validation
        $('.lcp-settings-form').on('submit', function() {
            const interestRate = parseFloat($('#default_interest_rate').val());
            const loanTerm = parseInt($('#default_loan_term').val());
            
            if (isNaN(interestRate) || interestRate < 0 || interestRate > 50) {
                alert('Interest rate must be between 0 and 50');
                return false;
            }
            
            if (isNaN(loanTerm) || loanTerm < 1 || loanTerm > 360) {
                alert('Loan term must be between 1 and 360 months');
                return false;
            }
            
            return true;
        });
        
        // Add animation to stat cards
        $('.lcp-stat-card').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)'
            }).delay(index * 100).animate({
                'opacity': 1
            }, 500, function() {
                $(this).css('transform', 'translateY(0)');
            });
        });
        
    });
    
})(jQuery);