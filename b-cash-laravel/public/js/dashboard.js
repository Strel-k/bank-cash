// dashboard.js - FRESH VERSION - CACHE BUSTED
console.log('=== DASHBOARD JS LOADED - FRESH VERSION ===');

class DashboardService {
    constructor() {
        this.apiBase = 'http://localhost:8000'; // WEB ROUTES
        this.isInitialized = false;
        console.log('Service base URL:', this.apiBase);
    }

    async initialize() {
        if (this.isInitialized) return;
        
        try {
            console.log('Initializing CSRF...');
            await fetch(`${this.apiBase}/sanctum/csrf-cookie`, {
                method: 'GET',
                credentials: 'include'
            });
            this.isInitialized = true;
            console.log('CSRF initialized');
        } catch (error) {
            console.error('CSRF init error:', error);
        }
    }

    getAuthHeaders() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        
        if (token) headers['X-CSRF-TOKEN'] = token;
        return headers;
    }

    async handleResponse(response) {
        console.log('Response:', response.status, response.url);
        
        if (response.status === 401) {
            return { success: false, message: 'Please login again' };
        }

        try {
            return await response.json();
        } catch (error) {
            return { success: false, message: 'Invalid response' };
        }
    }

    async getBalance() {
        await this.initialize();
        console.log('🔄 Getting balance from WEB route...');
        
        try {
            const response = await fetch(`${this.apiBase}/wallet/balance`, {
                method: 'GET',
                headers: this.getAuthHeaders(),
                credentials: 'include'
            });
            return await this.handleResponse(response);
        } catch (error) {
            return { success: false, message: 'Network error' };
        }
    }

    async getTransactionHistory() {
        await this.initialize();
        console.log('📊 Getting transactions from WEB route...');
        
        try {
            const response = await fetch(`${this.apiBase}/transactions/history?page=1&per_page=10`, {
                method: 'GET',
                headers: this.getAuthHeaders(),
                credentials: 'include'
            });
            return await this.handleResponse(response);
        } catch (error) {
            return { success: false, message: 'Network error' };
        }
    }

    async addMoney(amount) {
        await this.initialize();
        try {
            const response = await fetch(`${this.apiBase}/wallet/add-money`, {
                method: 'POST',
                headers: this.getAuthHeaders(),
                credentials: 'include',
                body: JSON.stringify({ amount: parseFloat(amount) })
            });
            return await this.handleResponse(response);
        } catch (error) {
            return { success: false, message: 'Network error' };
        }
    }

    async sendMoney(receiverPhone, amount, description = '') {
        await this.initialize();
        try {
            const response = await fetch(`${this.apiBase}/wallet/send-money`, {
                method: 'POST',
                headers: this.getAuthHeaders(),
                credentials: 'include',
                body: JSON.stringify({
                    receiver_phone: receiverPhone,
                    amount: parseFloat(amount),
                    description: description
                })
            });
            return await this.handleResponse(response);
        } catch (error) {
            return { success: false, message: 'Network error' };
        }
    }

    async payBills(billAccount, amount) {
        await this.initialize();
        try {
            const response = await fetch(`${this.apiBase}/wallet/pay-bills`, {
                method: 'POST',
                headers: this.getAuthHeaders(),
                credentials: 'include',
                body: JSON.stringify({
                    bill_account: billAccount,
                    amount: parseFloat(amount)
                })
            });
            return await this.handleResponse(response);
        } catch (error) {
            return { success: false, message: 'Network error' };
        }
    }

    async searchUsers(query) {
        await this.initialize();
        try {
            const response = await fetch(`${this.apiBase}/wallet/search-users?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: this.getAuthHeaders(),
                credentials: 'include'
            });
            return await this.handleResponse(response);
        } catch (error) {
            return { success: false, message: 'Network error' };
        }
    }
}

const dashboardService = new DashboardService();

async function refreshBalance() {
    console.log('🔄 refreshBalance() called');
    const balanceDisplay = document.getElementById('balanceDisplay');
    if (!balanceDisplay) return;

    balanceDisplay.textContent = 'Loading...';
    const result = await dashboardService.getBalance();
    console.log('Balance result:', result);
    
    if (result && result.success) {
        const balance = result.data?.balance || result.balance || 0;
        balanceDisplay.textContent = `₱${parseFloat(balance).toFixed(2)}`;
    } else {
        balanceDisplay.textContent = '₱0.00';
        console.error('Balance error:', result?.message);
    }
}

async function loadTransactionHistory() {
    console.log('📊 loadTransactionHistory() called');
    const container = document.getElementById('transactionHistory');
    if (!container) return;

    container.textContent = 'Loading...';
    const result = await dashboardService.getTransactionHistory();
    console.log('Transactions result:', result);
    
    if (result && result.success) {
        const transactions = result.data?.transactions || result.transactions || [];
        if (transactions.length === 0) {
            container.innerHTML = '<p>No transactions</p>';
        } else {
            container.innerHTML = transactions.map(t => `
                <div class="transaction">
                    <strong>${t.type || 'Unknown'}</strong>: ₱${parseFloat(t.amount || 0).toFixed(2)}
                </div>
            `).join('');
        }
    } else {
        container.innerHTML = `<p>Error: ${result?.message || 'Failed to load'}</p>`;
    }
}

// Add your other functions (addMoney, sendMoney, payBills, searchUsers) here...

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Dashboard starting...');
    setTimeout(() => {
        refreshBalance();
        loadTransactionHistory();
    }, 500);
});