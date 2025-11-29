// Transaction JavaScript for B-Cash - Laravel API version
class TransactionService {
    constructor() {
        this.apiUrl = '/api/transactions';
    }

    getAuthHeaders() {
        const token = window.apiToken || sessionStorage.getItem('api_token') || localStorage.getItem('api_token') || null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken || '';
        
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        };
        
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        
        return headers;
    }

    async getTransactionHistory(limit = 10, offset = 0) {
        try {
            const response = await fetch(`${this.apiUrl}/history?page=1&per_page=${limit}`, {
                method: 'GET',
                headers: this.getAuthHeaders(),
                credentials: 'same-origin'
            });
            
            if (response.status === 401) {
                window.location.href = '/login';
                return { success: false, message: 'Session expired' };
            }
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Transaction history error:', error);
            return { success: false, message: 'Failed to fetch transaction history' };
        }
    }

    async getTransactionStats() {
        try {
            const response = await fetch(`${this.apiUrl}/stats`, {
                method: 'GET',
                headers: this.getAuthHeaders(),
                credentials: 'same-origin'
            });
            
            if (response.status === 401) {
                window.location.href = '/login';
                return { success: false, message: 'Session expired' };
            }
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Transaction stats error:', error);
            return { success: false, message: 'Failed to fetch transaction stats' };
        }
    }

    async searchTransactions(searchTerm, limit = 20) {
        try {
            const response = await fetch(`${this.apiUrl}/search?q=${encodeURIComponent(searchTerm)}&limit=${limit}`, {
                method: 'GET',
                headers: this.getAuthHeaders(),
                credentials: 'same-origin'
            });
            
            if (response.status === 401) {
                window.location.href = '/login';
                return { success: false, message: 'Session expired' };
            }
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Transaction search error:', error);
            return { success: false, message: 'Search failed' };
        }
    }
}

// Initialize transaction service
const transactionService = new TransactionService();

// Form handlers
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    if (!(window.apiToken || sessionStorage.getItem('api_token') || localStorage.getItem('api_token'))) {
        window.location.href = '/login';
        return;
    }

    // Transaction history display
    const historyContainer = document.getElementById('transactionHistory');
    if (historyContainer) {
        transactionService.getTransactionHistory().then(result => {
            if (result.success) {
                displayTransactions(result.data?.transactions || result.data || []);
            } else {
                console.error('Transaction history error:', result.message);
            }
        });
    }

    // Stats display
    const statsContainer = document.getElementById('transactionStats');
    if (statsContainer) {
        transactionService.getTransactionStats().then(result => {
            if (result.success) {
                displayStats(result.data?.stats || result.data || {});
            } else {
                console.error('Transaction stats error:', result.message);
            }
        });
    }

    // Search form
    const searchForm = document.getElementById('searchTransactionForm');
    if (searchForm) {
        searchForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const searchTerm = document.getElementById('searchTerm')?.value;
            if (!searchTerm) return;

            const result = await transactionService.searchTransactions(searchTerm);

            if (result.success) {
                displayTransactions(result.data?.transactions || result.data || []);
            } else {
                console.error('Transaction search error:', result.message);
            }
        });
    }
});

// Display functions
function displayTransactions(transactions) {
    const container = document.getElementById('transactionHistory');
    if (!container) return;

    container.innerHTML = '';

    if (!transactions || transactions.length === 0) {
        container.innerHTML = '<p>No transactions found.</p>';
        return;
    }

    transactions.forEach(transaction => {
        const div = document.createElement('div');
        div.className = 'transaction-item';
        
        const transactionType = transaction.transaction_type || transaction.type || 'unknown';
        const amount = parseFloat(transaction.amount || 0).toFixed(2);
        const reference = transaction.reference_number || transaction.reference || 'N/A';
        const date = transaction.created_at ? new Date(transaction.created_at).toLocaleString() : 'N/A';
        
        div.innerHTML = `
            <div class="transaction-details">
                <h4>${transactionType === 'send' ? 'Sent' : 'Received'}</h4>
                <p>Amount: ₱${amount}</p>
                <p>Reference: ${reference}</p>
                <p>Date: ${date}</p>
            </div>
        `;
        container.appendChild(div);
    });
}

function displayStats(stats) {
    const container = document.getElementById('transactionStats');
    if (!container) return;

    const totalSent = parseFloat(stats.total_sent || 0).toFixed(2);
    const totalReceived = parseFloat(stats.total_received || 0).toFixed(2);
    const transactionCount = stats.transaction_count || 0;

    container.innerHTML = `
        <div class="stats-grid">
            <div class="stat-item">
                <h3>Total Sent</h3>
                <p>₱${totalSent}</p>
            </div>
            <div class="stat-item">
                <h3>Total Received</h3>
                <p>₱${totalReceived}</p>
            </div>
            <div class="stat-item">
                <h3>Transaction Count</h3>
                <p>${transactionCount}</p>
            </div>
        </div>
    `;
}