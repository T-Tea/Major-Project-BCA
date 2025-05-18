document.addEventListener('DOMContentLoaded', function() {
    const feeTypeFilter = document.getElementById('fee-type-filter');
    const statusFilter = document.getElementById('status-filter');
    
    function filterTransactions() {
        const selectedFeeType = feeTypeFilter.value;
        const selectedStatus = statusFilter.value;
        
        const transactions = document.querySelectorAll('.transactions-table tbody tr');
        
        transactions.forEach(transaction => {
            const feeType = transaction.getAttribute('data-fee-type');
            const status = transaction.getAttribute('data-status');
            
            const feeTypeMatch = selectedFeeType === 'all' || feeType === selectedFeeType;
            const statusMatch = selectedStatus === 'all' || status === selectedStatus;
            
            if (feeTypeMatch && statusMatch) {
                transaction.style.display = '';
            } else {
                transaction.style.display = 'none';
            }
        });
    }
    
    feeTypeFilter.addEventListener('change', filterTransactions);
    statusFilter.addEventListener('change', filterTransactions);
});