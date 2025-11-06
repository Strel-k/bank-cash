// Wallet JavaScript for B-Cash - Fixed version with token handling
class WalletService {
    constructor() {
        this.apiUrl = '/api/wallet.php';
    }

    async getBalance() {
        try {
            const response = await fetch(`${this.apiUrl}?action=balance`, {
                credentials: 'include'
            });
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Failed to fetch balance' };
        }
    }

    async transferMoney(transferData) {
        try {
            const response = await fetch(`${this.apiUrl}?action=transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify(transferData)
            });
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Transfer failed' };
        }
    }

    async searchAccount(account) {
        try {
            const response = await fetch(`${this.apiUrl}?action=search&account=${account}`, {
                credentials: 'include'
            });
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Search failed' };
        }
    }

    async addMoney(amount) {
        try {
            const response = await fetch(`${this.apiUrl}?action=addMoney`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({ amount })
            });
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Add money failed' };
        }
    }

    async payBills(billAccount, amount) {
        try {
            const response = await fetch(`${this.apiUrl}?action=payBills`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({ bill_account: billAccount, amount })
            });
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Pay bills failed' };
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
                alert(result.message);
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
                receiver_account: formData.get('receiver_account'),
                amount: parseFloat(formData.get('amount')),
                description: formData.get('description')
            };

            const result = await walletService.transferMoney(transferData);

            if (result.success) {
                alert('Transfer successful! Reference: ' + result.data.reference_number);
                window.location.reload();
            } else {
                alert(result.message);
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
                alert(`Account found: ${result.data.full_name}`);
            } else {
                alert(result.message);
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
                receiver_account: formData.get('receiver_account'),
                amount: parseFloat(formData.get('amount')),
                description: formData.get('description')
            };

            const result = await walletService.transferMoney(transferData);

            if (result.success) {
                alert('Transfer successful! Reference: ' + result.data.reference_number);
                closeModal('sendMoneyModal');
                refreshBalance();
                loadTransactionHistory();
            } else {
                alert(result.message);
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
                loadTransactionHistory();
            } else {
                alert(result.message);
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
                loadTransactionHistory();
            } else {
                alert(result.message);
            }
        });
    }
});
