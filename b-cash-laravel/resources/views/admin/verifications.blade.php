@extends('layouts.app')

@section('title', 'Verification Management - Admin')

@push('styles')
<style>
.verification-list {
    margin-top: 20px;
}

.verification-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.verification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.verification-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9em;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-approved {
    background: #d1fae5;
    color: #065f46;
}

.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}

.verification-details {
    margin-bottom: 15px;
}

.verification-documents {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.document-preview {
    position: relative;
    aspect-ratio: 16/9;
    background: #f3f4f6;
    border-radius: 4px;
    overflow: hidden;
}

.document-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.document-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 4px 8px;
    font-size: 0.9em;
}

.verification-actions {
    display: flex;
    gap: 10px;
}

.verification-actions button {
    flex: 1;
    padding: 8px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
}

.approve-btn {
    background: var(--gcash-blue);
    color: white;
}

.approve-btn:hover {
    background: var(--gcash-blue-dark);
}

.reject-btn {
    background: #ef4444;
    color: white;
}

.reject-btn:hover {
    background: #dc2626;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal-content {
    position: relative;
    background: white;
    margin: 10vh auto;
    padding: 20px;
    width: 90%;
    max-width: 500px;
    border-radius: 8px;
}

.close-modal {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 24px;
    cursor: pointer;
}

.modal img {
    max-width: 100%;
    height: auto;
}

#rejectReasonModal textarea {
    width: 100%;
    min-height: 100px;
    margin: 10px 0;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.filters {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-group select {
    padding: 6px;
    border-radius: 4px;
    border: 1px solid #ddd;
}
</style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Verification Management</h1>
        </div>

        <div class="filters">
            <div class="filter-group">
                <label for="statusFilter">Status:</label>
                <select id="statusFilter">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="dateFilter">Date:</label>
                <select id="dateFilter">
                    <option value="">All</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
        </div>

        <div class="verification-list" id="verificationList">
            <!-- Verifications will be loaded here -->
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <img id="modalImage" src="" alt="Document Preview">
        </div>
    </div>

    <!-- Reject Reason Modal -->
    <div id="rejectReasonModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Rejection Reason</h3>
            <textarea id="rejectReason" placeholder="Enter reason for rejection"></textarea>
            <button id="confirmReject" class="reject-btn">Confirm Rejection</button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let pendingRejectVerificationId = null;

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function getStatusClass(status) {
    switch(status) {
        case 'pending': return 'status-pending';
        case 'approved': return 'status-approved';
        case 'rejected': return 'status-rejected';
        default: return '';
    }
}

function createVerificationCard(verification) {
    return `
        <div class="verification-card" data-id="${verification.id}">
            <div class="verification-header">
                <div>
                    <strong>${verification.user.name}</strong>
                    <span class="verification-status ${getStatusClass(verification.status)}">
                        ${verification.status.charAt(0).toUpperCase() + verification.status.slice(1)}
                    </span>
                </div>
                <div>
                    <small>Submitted: ${formatDate(verification.created_at)}</small>
                </div>
            </div>
            <div class="verification-details">
                <p><strong>Document Type:</strong> ${verification.document_type}</p>
                <p><strong>Document Number:</strong> ${verification.document_number}</p>
            </div>
            <div class="verification-documents">
                <div class="document-preview" onclick="previewImage('${verification.document_front}')">
                    <img src="${verification.document_front}" alt="Front of Document">
                    <div class="document-label">Front</div>
                </div>
                <div class="document-preview" onclick="previewImage('${verification.document_back}')">
                    <img src="${verification.document_back}" alt="Back of Document">
                    <div class="document-label">Back</div>
                </div>
                <div class="document-preview" onclick="previewImage('${verification.face_image}')">
                    <img src="${verification.face_image}" alt="Face Photo">
                    <div class="document-label">Face Photo</div>
                </div>
            </div>
            ${verification.status === 'pending' ? `
                <div class="verification-actions">
                    <button class="approve-btn" onclick="approveVerification(${verification.id})">Approve</button>
                    <button class="reject-btn" onclick="showRejectModal(${verification.id})">Reject</button>
                </div>
            ` : ''}
        </div>
    `;
}

async function loadVerifications() {
    try {
        const statusFilter = document.getElementById('statusFilter').value;
        const dateFilter = document.getElementById('dateFilter').value;
        
        const response = await fetch(`/api/admin/verifications?status=${statusFilter}&date=${dateFilter}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            }
        });
        
        if (!response.ok) throw new Error('Failed to load verifications');
        
        const verifications = await response.json();
        const container = document.getElementById('verificationList');
        
        container.innerHTML = verifications.length ? 
            verifications.map(v => createVerificationCard(v)).join('') :
            '<p class="text-center">No verifications found.</p>';
    } catch (error) {
        console.error('Error loading verifications:', error);
        alert('Failed to load verifications. Please try again.');
    }
}

async function approveVerification(id) {
    if (!confirm('Are you sure you want to approve this verification?')) return;
    
    try {
        const response = await fetch(`/api/admin/verifications/${id}/approve`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            }
        });
        
        if (!response.ok) throw new Error('Failed to approve verification');
        
        await loadVerifications();
    } catch (error) {
        console.error('Error approving verification:', error);
        alert('Failed to approve verification. Please try again.');
    }
}

function showRejectModal(id) {
    pendingRejectVerificationId = id;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectReasonModal').style.display = 'block';
}

async function rejectVerification() {
    const reason = document.getElementById('rejectReason').value.trim();
    if (!reason) {
        alert('Please provide a reason for rejection');
        return;
    }
    
    try {
        const response = await fetch(`/api/admin/verifications/${pendingRejectVerificationId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({ reason })
        });
        
        if (!response.ok) throw new Error('Failed to reject verification');
        
        document.getElementById('rejectReasonModal').style.display = 'none';
        await loadVerifications();
    } catch (error) {
        console.error('Error rejecting verification:', error);
        alert('Failed to reject verification. Please try again.');
    }
}

function previewImage(url) {
    document.getElementById('modalImage').src = url;
    document.getElementById('imageModal').style.display = 'block';
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    loadVerifications();
    
    // Filter change handlers
    document.getElementById('statusFilter').addEventListener('change', loadVerifications);
    document.getElementById('dateFilter').addEventListener('change', loadVerifications);
    
    // Modal close handlers
    document.querySelectorAll('.close-modal').forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            closeBtn.closest('.modal').style.display = 'none';
        });
    });
    
    // Modal outside click handlers
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', event => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Reject confirmation handler
    document.getElementById('confirmReject').addEventListener('click', rejectVerification);
});
</script>
@endpush