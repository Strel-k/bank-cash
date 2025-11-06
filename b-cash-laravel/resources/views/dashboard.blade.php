@extends('layouts.app')

@section('title', 'Dashboard - B-Cash')

@section('content')
    <!-- Welcome Section -->
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Welcome to B-Cash</h1>
            <p class="text-center">Your secure digital wallet solution</p>
        </div>
    </div>

    <!-- Balance Display -->
    <div class="balance-card">
        <div class="balance-label">Available Balance</div>
        <div class="balance-amount" id="balanceDisplay">₱0.00</div>
        <button class="form-button mt-2" onclick="refreshBalance()" style="max-width: 200px; margin: 0 auto;">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Quick Actions</h2>
        </div>
        <div class="quick-actions">
            <button class="action-button" data-action="open-modal" data-target="sendMoneyModal">
                <div class="action-icon" style="color: var(--gcash-blue);">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="action-label">Send Money</div>
            </button>
            <button class="action-button" data-action="open-modal" data-target="addMoneyModal">
                <div class="action-icon" style="color: var(--gcash-orange);">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="action-label">Add Money</div>
            </button>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Transactions</h2>
        </div>
        <div id="transactionHistory">
            <div class="transaction-list">
                <div class="text-center" style="padding: var(--spacing-lg); color: #666;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 48px; margin-bottom: var(--spacing-md);"></i>
                    <p>Loading transactions...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <!-- Send Money Modal -->
    <div id="sendMoneyModal" class="modal">
        <div class="modal-content">
            <span class="close" data-action="close-modal" data-target="sendMoneyModal">&times;</span>
            <h2>Send Money</h2>
            <form id="sendMoneyForm">
                <label for="receiver_phone">Receiver Phone Number:</label>
                <div style="position: relative;">
                    <input type="text" id="receiver_phone" name="receiver_phone" autocomplete="off" required>
                    <div id="phoneSuggestions" class="suggestions-list" style="display:none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ccc; max-height: 150px; overflow-y: auto; z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                </div>

                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" min="0.01" step="0.01" required>

                <label for="description">Description (optional):</label>
                <textarea id="description" name="description"></textarea>

                <button type="submit">Send</button>
            </form>
        </div>
    </div>

    <!-- Add Money Modal -->
    <div id="addMoneyModal" class="modal">
        <div class="modal-content">
            <span class="close" data-action="close-modal" data-target="addMoneyModal">&times;</span>
            <h2>Add Money</h2>
            <form id="addMoneyForm">
                <label for="add_amount">Amount:</label>
                <input type="number" id="add_amount" name="amount" min="0.01" step="0.01" required>
                
                <button type="submit">Add</button>
            </form>
        </div>
    </div>
@endpush

@push('styles')
<style>
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    padding: 10px;
}

.pagination-btn {
    padding: 8px 16px;
    background: var(--gcash-blue);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.pagination-btn.active {
    background: #1a1a2e;
}

.transaction-amount.positive {
    color: var(--success-color);
}

.transaction-amount.negative {
    color: var(--error-color);
}
</style>
@endpush

@push('scripts')
<script src="/js/dashboard.js"></script>
<script>
let currentPage = 1;
const pageSize = 10;

async function refreshBalance() {
    const balanceDisplay = document.getElementById('balanceDisplay');
    balanceDisplay.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    
    try {
        const response = await fetch('/api/wallet/balance', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            credentials: 'include'
        });
        const result = await response.json();
        
        if (response.ok) {
            balanceDisplay.textContent = `₱${parseFloat(result.balance).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        } else {
            balanceDisplay.textContent = '₱0.00';
            alert(result.message || 'Failed to load balance');
        }
    } catch (error) {
        balanceDisplay.textContent = '₱0.00';
        alert('Failed to load balance');
    }
}

async function loadTransactionHistory(page = 1) {
    const transactionHistory = document.getElementById('transactionHistory');
    transactionHistory.innerHTML = '<div class="text-center" style="padding: var(--spacing-lg); color: #666;"><i class="fas fa-spinner fa-spin" style="font-size: 48px; margin-bottom: var(--spacing-md);"></i><p>Loading transactions...</p></div>';

    try {
        const response = await fetch(`/api/transactions/history?page=${page}&per_page=${pageSize}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            credentials: 'include'
        });
        const result = await response.json();

        if (response.ok) {
            currentPage = page;
            if (result.data.length > 0) {
                displayTransactions(result.data);
                displayPagination(result.total, page, pageSize);
            } else {
                transactionHistory.innerHTML = '<div class="text-center" style="padding: var(--spacing-lg); color: #666;"><i class="fas fa-history" style="font-size: 48px; margin-bottom: var(--spacing-md);"></i><p>No transactions yet</p><small>Start using B-Cash to see your transaction history</small></div>';
            }
        } else {
            transactionHistory.innerHTML = '<div class="text-center" style="padding: var(--spacing-lg); color: #666;"><i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: var(--spacing-md);"></i><p>Failed to load transactions</p></div>';
        }
    } catch (error) {
        transactionHistory.innerHTML = '<div class="text-center" style="padding: var(--spacing-lg); color: #666;"><i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: var(--spacing-md);"></i><p>Failed to load transactions</p></div>';
    }
}

function displayTransactions(transactions) {
    const container = document.getElementById('transactionHistory');
    container.innerHTML = '';

    const transactionList = document.createElement('div');
    transactionList.className = 'transaction-list';
    transactions.forEach(transaction => {
        const transactionItem = document.createElement('div');
        transactionItem.className = 'transaction-item';

        // Determine display values based on type
        let amountClass = 'transaction-amount';
        let icon = 'fa-exchange-alt';
        let typeText = (transaction.type || '').toString();

        switch ((transaction.type || '').toString()) {
            case 'send':
                amountClass = 'transaction-amount negative';
                icon = 'fa-arrow-up';
                typeText = 'Sent';
                break;
            case 'receive':
                amountClass = 'transaction-amount positive';
                icon = 'fa-arrow-down';
                typeText = 'Received';
                break;
            case 'topup':
                amountClass = 'transaction-amount positive';
                icon = 'fa-plus-circle';
                typeText = 'Added Money';
                break;
            case 'withdraw':
                amountClass = 'transaction-amount negative';
                icon = 'fa-minus-circle';
                typeText = 'Withdrawal';
                break;
            default:
                typeText = typeText.charAt(0).toUpperCase() + typeText.slice(1);
        }

        // Determine counterparty info
        let counterpartyInfo = '';
        if (transaction.type === 'topup' || transaction.type === 'withdraw') {
            counterpartyInfo = 'Wallet Transaction';
        } else if (transaction.type === 'send') {
            counterpartyInfo = transaction.receiver_name ?
                `To: ${transaction.receiver_name} (${transaction.receiver_phone})` :
                `To: ${transaction.receiver_phone}`;
        } else {
            counterpartyInfo = transaction.sender_name ?
                `From: ${transaction.sender_name} (${transaction.sender_phone})` :
                `From: ${transaction.sender_phone}`;
        }

        const reference = transaction.reference || transaction.reference_number || '';

        transactionItem.innerHTML = `
            <div class="transaction-details">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <h4 style="margin: 0; color: var(--text-primary);">${typeText}</h4>
                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">${counterpartyInfo}</p>
                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">${transaction.description || 'No description'}</p>
                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.75rem;">Ref: ${reference}</p>
                    </div>
                    <div class="${amountClass}" style="font-weight: 600; font-size: 1.1rem;">
                        <i class="fas ${icon}"></i> ₱${parseFloat(transaction.amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </div>
                </div>
                <div style="margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.75rem;">
                        ${new Date(transaction.created_at).toLocaleDateString()} ${new Date(transaction.created_at).toLocaleTimeString()}
                    </p>
                    <span style="font-size: 0.75rem; color: var(--text-secondary); background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">
                        ${((transaction.type || '').toString()).toUpperCase()}
                    </span>
                </div>
            </div>
        `;

        transactionList.appendChild(transactionItem);
    });

    container.appendChild(transactionList);
}

function displayPagination(total, currentPage, pageSize) {
    const totalPages = Math.ceil(total / pageSize);
    if (totalPages <= 1) return;

    const paginationContainer = document.createElement('div');
    paginationContainer.className = 'pagination-container';

    // Previous button
    if (currentPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.className = 'pagination-btn';
        prevBtn.onclick = () => loadTransactionHistory(currentPage - 1);
        paginationContainer.appendChild(prevBtn);
    }

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        pageBtn.className = `pagination-btn${i === currentPage ? ' active' : ''}`;
        pageBtn.onclick = () => loadTransactionHistory(i);
        paginationContainer.appendChild(pageBtn);
    }

    // Next button
    if (currentPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.className = 'pagination-btn';
        nextBtn.onclick = () => loadTransactionHistory(currentPage + 1);
        paginationContainer.appendChild(nextBtn);
    }

    const transactionHistory = document.getElementById('transactionHistory');
    transactionHistory.appendChild(paginationContainer);
}

// Phone number suggestions
const receiverPhoneInput = document.getElementById('receiver_phone');
const suggestionsBox = document.getElementById('phoneSuggestions');

if (receiverPhoneInput) {
    receiverPhoneInput.addEventListener('input', async function() {
    const query = this.value.trim();
    if (query.length < 2) {
        suggestionsBox.style.display = 'none';
        suggestionsBox.innerHTML = '';
        return;
    }

    try {
        const response = await fetch(`/api/wallet/search-users?q=${encodeURIComponent(query)}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            credentials: 'include'
        });
        const result = await response.json();

        if (response.ok && result.users.length > 0) {
            suggestionsBox.innerHTML = '';
            result.users.forEach(user => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.textContent = `${user.full_name} (${user.phone_number})`;
                div.style.padding = '8px 12px';
                div.style.cursor = 'pointer';
                div.style.borderBottom = '1px solid #eee';
                div.addEventListener('click', () => {
                    receiverPhoneInput.value = user.phone_number;
                    suggestionsBox.style.display = 'none';
                    suggestionsBox.innerHTML = '';
                });
                div.addEventListener('mouseenter', () => {
                    div.style.backgroundColor = '#f5f5f5';
                });
                div.addEventListener('mouseleave', () => {
                    div.style.backgroundColor = 'transparent';
                });
                suggestionsBox.appendChild(div);
            });
            suggestionsBox.style.display = 'block';
        } else {
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
        }
    } catch (error) {
        suggestionsBox.style.display = 'none';
        suggestionsBox.innerHTML = '';
    }
});
}

// Send Money Form
const sendMoneyForm = document.getElementById('sendMoneyForm');
if (sendMoneyForm) {
    sendMoneyForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = {
        receiver_phone: this.receiver_phone.value,
        amount: parseFloat(this.amount.value),
        description: this.description.value
    };

    try {
        const response = await fetch('/api/wallet/send-money', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify(formData),
            credentials: 'include'
        });

        const result = await response.json();
        
        if (response.ok) {
            alert('Money sent successfully!');
            this.reset();
            closeModal('sendMoneyModal');
            refreshBalance();
            loadTransactionHistory();
        } else {
            alert(result.message || 'Failed to send money');
        }
    } catch (error) {
        alert('Failed to send money');
    }
});
}

// Add Money Form
const addMoneyForm = document.getElementById('addMoneyForm');
if (addMoneyForm) {
    addMoneyForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = {
        amount: parseFloat(this.amount.value)
    };

    try {
        const response = await fetch('/api/wallet/add-money', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify(formData),
            credentials: 'include'
        });

        const result = await response.json();
        
        if (response.ok) {
            alert('Money added successfully!');
            this.reset();
            closeModal('addMoneyModal');
            refreshBalance();
            loadTransactionHistory();
        } else {
            alert(result.message || 'Failed to add money');
        }
    } catch (error) {
        alert('Failed to add money');
    }
});
}

// Modal handling through data attributes
document.addEventListener('click', function(event) {
    const element = event.target.closest('[data-action]');
    if (!element) return;

    const action = element.dataset.action;
    const target = element.dataset.target;

    switch (action) {
        case 'open-modal':
            if (target) {
                const modal = document.getElementById(target);
                if (modal) {
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            }
            break;
        case 'close-modal':
            if (target) {
                const modal = document.getElementById(target);
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }
            break;
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Hide suggestions when clicking outside
document.addEventListener('click', function(event) {
    // If the suggestion elements are present, hide when clicking outside
    if (typeof receiverPhoneInput !== 'undefined' && receiverPhoneInput && typeof suggestionsBox !== 'undefined' && suggestionsBox) {
        if (!receiverPhoneInput.contains(event.target) && !suggestionsBox.contains(event.target)) {
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
        }
    }
});

// Programmatic modal helpers used by forms
function openModalById(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModalById(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Backwards-compatible helpers (existing code uses these names)
function openModal(modalId) { openModalById(modalId); }
function closeModal(modalId) { closeModalById(modalId); }

// Load initial data
document.addEventListener('DOMContentLoaded', async function() {
    // Ensure Sanctum CSRF cookie is present before making stateful API requests
    try {
        await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
    } catch (e) {
        console.warn('Failed to fetch CSRF cookie before dashboard API calls', e);
    }

    refreshBalance();
    loadTransactionHistory();
});
</script>
@endpush