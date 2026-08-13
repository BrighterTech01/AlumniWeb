// Alumni Modal Functions
function openAlumniModal(userId = null, mode = 'create') {
    let url = '../forms/modalAlumni.php?mode=' + mode;
    if (userId) {
        url += '&id=' + userId;
    }

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(data => {
            // Create a container for the modal if it doesn't exist
            let modalContainer = document.getElementById('alumniModalContainer');
            if (!modalContainer) {
                modalContainer = document.createElement('div');
                modalContainer.id = 'alumniModalContainer';
                document.body.appendChild(modalContainer);
            }
            modalContainer.innerHTML = data;
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error loading alumni modal:', error);
            alert('Error loading alumni form');
        });
}

function closeAlumniModal() {
    const modalContainer = document.getElementById('alumniModalContainer');
    if (modalContainer) {
        modalContainer.remove();
    }
    document.body.style.overflow = 'auto';
}

function openDeleteAlumniModal(userId, userName = '') {
    // Create delete modal if it doesn't exist
    let deleteModalContainer = document.getElementById('deleteModalContainer');
    if (!deleteModalContainer) {
        deleteModalContainer = document.createElement('div');
        deleteModalContainer.id = 'deleteModalContainer';
        document.body.appendChild(deleteModalContainer);
        
        // Load delete modal HTML
        fetch('../forms/modalAlumni.php?mode=delete')
            .then(response => response.text())
            .then(data => {
                deleteModalContainer.innerHTML = data;
                
                // Update the form action to point to alumniController.php
                const deleteForm = document.getElementById('deleteAlumniForm');
                if (deleteForm) {
                    deleteForm.action = '../controllers/alumniController.php';
                }
                
                document.getElementById('deleteUserId').value = userId;
                
                // Update confirmation message with user name if provided
                if (userName) {
                    const confirmationText = document.querySelector('#deleteAlumniModal p');
                    if (confirmationText) {
                        confirmationText.innerHTML = `Are you sure you want to delete <strong>${userName}</strong>? This action cannot be undone.`;
                    }
                }
                
                document.getElementById('deleteAlumniModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
    } else {
        // Update the form action to point to alumniController.php
        const deleteForm = document.getElementById('deleteAlumniForm');
        if (deleteForm) {
            deleteForm.action = '../controllers/alumniController.php';
        }
        
        document.getElementById('deleteUserId').value = userId;
        if (userName) {
            const confirmationText = document.querySelector('#deleteAlumniModal p');
            if (confirmationText) {
                confirmationText.innerHTML = `Are you sure you want to delete <strong>${userName}</strong>? This action cannot be undone.`;
            }
        }
        document.getElementById('deleteAlumniModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeDeleteAlumniModal() {
    const deleteModal = document.getElementById('deleteAlumniModal');
    if (deleteModal) {
        deleteModal.classList.add('hidden');
    }
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
        if (e.target.id === 'alumniModal') {
            closeAlumniModal();
        } else if (e.target.id === 'deleteAlumniModal') {
            closeDeleteAlumniModal();
        }
    }
});

// Also update your HTML button to pass the user name for better confirmation message
function updateDeleteButtons() {
    document.querySelectorAll('[onclick*="openDeleteAlumniModal"]').forEach(button => {
        const onclickAttr = button.getAttribute('onclick');
        const match = onclickAttr.match(/openDeleteAlumniModal\((\d+)\)/);
        if (match) {
            const userId = match[1];
            const row = button.closest('tr');
            const userName = row.querySelector('td:nth-child(2) .text-white').textContent.trim();
            button.setAttribute('onclick', `openDeleteAlumniModal(${userId}, '${userName.replace(/'/g, "\\'")}')`);
        }
    });
}

// Call this after page load to update delete buttons with user names
document.addEventListener('DOMContentLoaded', updateDeleteButtons);

// Initialize form validation
document.addEventListener('DOMContentLoaded', function() {
    // Password validation for alumni form
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'alumniForm') {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password && password.value.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
        }
    });
});



// ==============================================
// EVENT MODAL FUNCTIONS
// ==============================================

function openEventModal(eventId = null) {
    // Remove any existing modals first
    removeExistingModals();

    const url = eventId ? `../modal/modalEvent.php?id=${eventId}` : '../modal/modalEvent.php';
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#eventModal');
            if (modal) {
                document.body.appendChild(modal);
                modal.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error loading event modal:', error);
            showToast('Failed to load event form. Please try again.', 'error');
        });
}

function closeEventModal() {
    const modal = document.getElementById('eventModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function openEventViewModal(eventId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../modal/modalEvent.php?id=${eventId}&view=true`)
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#viewModal');
            if (modal) {
                document.body.appendChild(modal);
                modal.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error loading event view modal:', error);
            showToast('Failed to load event details. Please try again.', 'error');
        });
}

function closeEventViewModal() {
    const modal = document.getElementById('viewModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function openEventDeleteModal(eventId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../modal/modalEvent.php?id=${eventId}&delete=true`)
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#deleteModal');
            if (modal) {
                document.body.appendChild(modal);
                document.getElementById('deleteEventId').value = eventId;
                modal.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error loading delete modal:', error);
            showToast('Failed to load delete confirmation. Please try again.', 'error');
        });
}

function closeEventDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

// Helper function to remove existing modals
function removeExistingModals() {
    const modals = [
        'alumniModal', 'storyModal', 'eventModal',
        'viewModal', 'deleteModal'
    ];
    
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) modal.remove();
    });
}

// Update the click outside handler to include all modals
document.addEventListener('click', function(event) {
    // Event modals
    const eventModal = document.getElementById('eventModal');
    if (eventModal && !eventModal.contains(event.target) && !event.target.closest('[onclick*="openEventModal"]')) {
        closeEventModal();
    }
    
    const eventViewModal = document.getElementById('viewModal');
    if (eventViewModal && !eventViewModal.contains(event.target) && !event.target.closest('[onclick*="openEventViewModal"]')) {
        closeEventViewModal();
    }
    
    const eventDeleteModal = document.getElementById('deleteModal');
    if (eventDeleteModal && !eventDeleteModal.contains(event.target) && !event.target.closest('[onclick*="openEventDeleteModal"]')) {
        closeEventDeleteModal();
    }

    // Alumni modals (keep your existing code)
    const alumniModal = document.getElementById('alumniModal');
    if (alumniModal && !alumniModal.contains(event.target) && !event.target.closest('[onclick*="openAlumniModal"]')) {
        closeModal();
    }
    
    // Story modals (keep your existing code)
    const storyModal = document.getElementById('storyModal');
    if (storyModal && !storyModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
        closeStoryModal();
    }
});

// ==============================================
// TOAST NOTIFICATION FUNCTION (Keep existing)
// ==============================================
function showToast(message, type = 'success') {
    // ... (keep your existing toast implementation)
}

// ==============================================
// OTHER MODAL FUNCTIONS (Keep your existing alumni, story, and user modal functions)
// ==============================================
// ... (keep all your existing modal functions for alumni, stories, and users)