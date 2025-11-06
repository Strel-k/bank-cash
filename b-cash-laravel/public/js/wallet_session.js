// Wallet JavaScript for B-Cash - Session-based authentication
class WalletService {
    constructor() {
        // Use the current origin for consistent URL resolution
        const baseUrl = window.location.origin;
        this.apiUrl = `${baseUrl}/B-Cash AJAX/public/api/wallet.php`;
    }

    async getBalance() {
        try {
            const response = await fetch(`${this.apiUrl}?action=balance`, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Balance fetch error:', error);
            return { success: false, message: 'Failed to fetch balance' };
        }
    }

    async transferMoney(transferData) {
        try {
            const response = await fetch(`${this.apiUrl}?action=transfer`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(transferData)
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Transfer error:', error);
            return { success: false, message: 'Transfer failed' };
        }
    }

    async addMoney(amount) {
        try {
            const response = await fetch(`${this.apiUrl}?action=addMoney`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ amount })
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Add money error:', error);
            return { success: false, message: 'Add money failed' };
        }
    }

    async payBills(billAccount, amount) {
        try {
            const response = await fetch(`${this.apiUrl}?action=payBills`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ bill_account: billAccount, amount })
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Pay bills error:', error);
            return { success: false, message: 'Pay bills failed' };
        }
    }

    async searchAccount(phone) {
        try {
            const response = await fetch(`${this.apiUrl}?action=search&phone=${phone}`, {
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Search phone error:', error);
            return { success: false, message: 'Search failed' };
        }
    }
}

// Initialize wallet service
const walletService = new WalletService();

// Form handlers
document.addEventListener('DOMContentLoaded', function() {
    // Balance display
    const balanceDisplay = document.getElementById('balanceDisplay');
    if (balanceDisplay) {
        walletService.getBalance().then(result => {
            if (result.success) {
                balanceDisplay.innerText = `Balance: ₱${result.data.balance}`;
            } else {
                console.error('Balance error:', result.message);
                // Removed alert for better UX
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
                // Removed alert for better UX
                closeModal('sendMoneyModal');
                refreshBalance();
                loadTransactionHistory();
            } else {
                console.error('Transfer error:', result.message);
                // Removed alert for better UX
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
                // Removed alert for better UX
                closeModal('addMoneyModal');
                refreshBalance();
                loadTransactionHistory();
            } else {
                console.error('Add money error:', result.message);
                // Removed alert for better UX
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
                // Removed alert for better UX
                closeModal('payBillsModal');
                refreshBalance();
                loadTransactionHistory();
            } else {
                console.error('Pay bills error:', result.message);
                // Removed alert for better UX
            }
        });
    }

    // Search account
        const receiverPhoneInput = document.getElementById('receiver_phone');
        if (receiverPhoneInput) {
            receiverPhoneInput.addEventListener('blur', async function() {
                const phone = this.value.trim();
                if (phone.length >= 6) { // Minimum phone number length
                    const result = await walletService.searchAccount(phone);
                    if (result.success) {
                        // Could show account holder name here
                        console.log('Phone found:', result.data.full_name);
                    } else {
                        console.log('Phone search result:', result.message);
                    }
                }
            });
        }
});
