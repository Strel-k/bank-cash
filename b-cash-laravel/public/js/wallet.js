// Wallet JavaScript for B-Cash - Laravel API version
class WalletService {
    constructor() {
        this.apiUrl = '/api/wallet';
    }

    getAuthHeaders() {
        const token = window.apiToken || sessionStorage.getItem('api_token') || localStorage.getItem('api_token') || null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken || '';
        
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        };
        
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        
        return headers;
    }

    handleApiError(response) {
        if (response.status === 401) {
            window.location.href = '/login';
            return true;
        } else if (response.status === 419) {
            window.location.reload();
            return true;
        }
        return false;
    }

    async getBalance() {
        try {
            const response = await fetch(`${this.apiUrl}/balance`, {
                method: 'GET',
                headers: this.getAuthHeaders(),
                credentials: 'same-origin'
            });

            if (this.handleApiError(response)) {
                return { success: false, message: 'Authentication failed' };
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Balance fetch error:', error);
            return { success: false, message: 'Failed to fetch balance' };
        }
    }

    async transferMoney(transferData) {
        try {
            const response = await fetch(`${this.apiUrl}/send-money`, {
                method: 'POST',
                headers: this.getAuthHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify(transferData)
            });

            if (this.handleApiError(response)) {
                return { success: false, message: 'Authentication failed' };
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Transfer error:', error);
            return { success: false, message: 'Transfer failed' };
        }
    }

    async searchAccount(phone) {
        try {
            const response = await fetch(`${this.apiUrl}/search-users?q=${encodeURIComponent(phone)}`, {
                method: 'GET',
                headers: this.getAuthHeaders(),
                credentials: 'same-origin'
            });

            if (this.handleApiError(response)) {
                return { success: false, message: 'Authentication failed' };
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Search error:', error);
            return { success: false, message: 'Search failed' };
        }
    }

    async addMoney(amount) {
        try {
            const response = await fetch(`${this.apiUrl}/add-money`, {
                method: 'POST',
                headers: this.getAuthHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({ amount })
            });

            if (this.handleApiError(response)) {
                return { success: false, message: 'Authentication failed' };
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Add money error:', error);
            return { success: false, message: 'Add money failed' };
        }
    }

    async payBills(billAccount, amount) {
        try {
            const response = await fetch(`${this.apiUrl}/pay-bills`, {
                method: 'POST',
                headers: this.getAuthHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({ bill_account: billAccount, amount })
            });

            if (this.handleApiError(response)) {
                return { success: false, message: 'Authentication failed' };
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Pay bills error:', error);
            return { success: false, message: 'Pay bills failed' };
        }
    }
}

// Initialize wallet service
const walletService = new WalletService();

// Global functions for dashboard compatibility
async function refreshBalance() {
    const balanceDisplay = document.getElementById('balanceDisplay');
    if (balanceDisplay) {
        const result = await walletService.getBalance();
        if (result.success) {
            const balance = result.balance || result.data?.balance || 0;
            balanceDisplay.innerText = `Balance: ₱${parseFloat(balance).toFixed(2)}`;
        } else {
            balanceDisplay.innerText = 'Balance: ₱0.00';
            console.error('Balance refresh error:', result.message);
        }
    }
}

async function loadTransactionHistory() {
    // This function would be implemented based on your transaction history display needs
    console.log('Loading transaction history...');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Form handlers
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    if (!(window.apiToken || sessionStorage.getItem('api_token') || localStorage.getItem('api_token'))) {
        window.location.href = '/login';
        return;
    }

    // Balance display
    const balanceDisplay = document.getElementById('balanceDisplay');
    if (balanceDisplay) {
        walletService.getBalance().then(result => {
            if (result.success) {
                const balance = result.balance || result.data?.balance || 0;
                balanceDisplay.innerText = `Balance: ₱${parseFloat(balance).toFixed(2)}`;
            } else {
                balanceDisplay.innerText = 'Balance: ₱0.00';
                console.error('Balance load error:', result.message);
            }
        });
    }

    // Transfer form
    const transferForm = document.getElementById('transferForm');
    if (transferForm) {
        transferForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const transferData = {
                receiver_phone: formData.get('receiver_phone'),
                amount: parseFloat(formData.get('amount')),
                description: formData.get('description')
            };

            const result = await walletService.transferMoney(transferData);

            if (result.success) {
                alert('Transfer successful! Reference: ' + (result.data?.reference_number || 'N/A'));
                this.reset();
                refreshBalance();
            } else {
                alert(result.message || 'Transfer failed');
            }
        });
    }

    // Search account
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const account = formData.get('account');

            const result = await walletService.searchAccount(account);

            if (result.success) {
                const user = result.users?.[0] || result.data;
                alert(`Account found: ${user?.full_name || 'Unknown User'}`);
            } else {
                alert(result.message || 'Account not found');
            }
        });
    }

    // Send Money form
    const sendMoneyForm = document.getElementById('sendMoneyForm');
    if (sendMoneyForm) {
        sendMoneyForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const transferData = {
                receiver_phone: formData.get('receiver_phone'),
                amount: parseFloat(formData.get('amount')),
                description: formData.get('description')
            };

            const result = await walletService.transferMoney(transferData);

            if (result.success) {
                closeModal('sendMoneyModal');
                refreshBalance();
                if (typeof loadTransactionHistory === 'function') {
                    loadTransactionHistory();
                }
            } else {
                alert(result.message || 'Transfer failed');
            }
        });
    }

    // Add Money form
    const addMoneyForm = document.getElementById('addMoneyForm');
    if (addMoneyForm) {
        addMoneyForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const amount = parseFloat(formData.get('amount'));

            const result = await walletService.addMoney(amount);

            if (result.success) {
                alert('Money added successfully!');
                closeModal('addMoneyModal');
                refreshBalance();
                if (typeof loadTransactionHistory === 'function') {
                    loadTransactionHistory();
                }
            } else {
                alert(result.message || 'Failed to add money');
            }
        });
    }

    // Pay Bills form
    const payBillsForm = document.getElementById('payBillsForm');
    if (payBillsForm) {
        payBillsForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const billAccount = formData.get('bill_account');
            const amount = parseFloat(formData.get('amount'));

            const result = await walletService.payBills(billAccount, amount);

            if (result.success) {
                alert('Bill payment successful!');
                closeModal('payBillsModal');
                refreshBalance();
                if (typeof loadTransactionHistory === 'function') {
                    loadTransactionHistory();
                }
            } else {
                alert(result.message || 'Bill payment failed');
            }
        });
    }
});