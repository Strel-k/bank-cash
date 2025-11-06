// Transaction JavaScript for B-Cash - Laravel API version
class TransactionService {
    constructor() {
        this.apiUrl = '/api/transactions';
    }

    async getTransactionHistory(limit = 10, offset = 0) {
        try {
            const response = await fetch(`${this.apiUrl}/history?limit=${limit}&offset=${offset}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            });
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Failed to fetch transaction history' };
        }
    }

    async getTransactionStats() {
        try {
            const response = await fetch(`${this.apiUrl}/stats`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            });
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Failed to fetch transaction stats' };
        }
    }

    async searchTransactions(searchTerm, limit = 20) {
        try {
            const response = await fetch(`${this.apiUrl}/search?q=${encodeURIComponent(searchTerm)}&limit=${limit}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            });
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Search failed' };
        }
    }
}

// Initialize transaction service
const transactionService = new TransactionService();

// Form handlers
document.addEventListener('DOMContentLoaded', function() {
    // Transaction history display
    const historyContainer = document.getElementById('transactionHistory');
    if (historyContainer) {
        transactionService.getTransactionHistory().then(result => {
            if (result.success) {
                displayTransactions(result.data.transactions);
            } else {
                alert(result.message);
            }
        });
    }

    // Stats display
    const statsContainer = document.getElementById('transactionStats');
    if (statsContainer) {
        transactionService.getTransactionStats().then(result => {
            if (result.success) {
                displayStats(result.data.stats);
            } else {
                alert(result.message);
            }
        });
    }

    // Search form
    const searchForm = document.getElementById('searchTransactionForm');
    if (searchForm) {
        searchForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const searchTerm = document.getElementById('searchTerm').value;

            const result = await transactionService.searchTransactions(searchTerm);

            if (result.success) {
                displayTransactions(result.data.transactions);
            } else {
                alert(result.message);
            }
        });
    }
});

// Display functions
function displayTransactions(transactions) {
    const container = document.getElementById('transactionHistory');
    if (!container) return;

    container.innerHTML = '';

    if (transactions.length === 0) {
        container.innerHTML = '<p>No transactions found.</p>';
        return;
    }

    transactions.forEach(transaction => {
        const div = document.createElement('div');
        div.className = 'transaction-item';
        div.innerHTML = `
            <div class="transaction-details">
                <h4>${transaction.transaction_type === 'send' ? 'Sent' : 'Received'}</h4>
                <p>Amount: ₱${transaction.amount}</p>
                <p>Reference: ${transaction.reference_number}</p>
                <p>Date: ${transaction.created_at}</p>
            </div>
        `;
        container.appendChild(div);
    });
}

function displayStats(stats) {
    const container = document.getElementById('transactionStats');
    if (!container) return;

    container.innerHTML = `
        <div class="stats-grid">
            <div class="stat-item">
                <h3>Total Sent</h3>
                <p>₱${stats.total_sent}</p>
            </div>
            <div class="stat-item">
                <h3>Total Received</h3>
                <p>₱${stats.total_received}</p>
            </div>
            <div class="stat-item">
                <h3>Transaction Count</h3>
                <p>${stats.transaction_count}</p>
            </div>
        </div>
    `;
}
