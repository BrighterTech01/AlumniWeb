// Function to open the alumni modal
function openAlumniModal(alumnusId = null) {
    // Remove any existing modals first
    const existingModal = document.getElementById('alumniModal');
    if (existingModal) {
        existingModal.remove();
    }

    const url = alumnusId ? `../forms/modalAlumni.php?id=${alumnusId}` : '../forms/modalAlumni.php';
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#alumniModal');
            if (modal) {
                document.body.appendChild(modal);
                // Add a small delay to ensure the modal is in the DOM before showing it
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            }
        })
        .catch(error => {
            console.error('Error loading modal:', error);
            showToast('Failed to load form. Please try again.', 'error');
        });
}

// Function to close the alumni modal
function closeModal() {
    const modal = document.getElementById('alumniModal');
    if (modal) {
        modal.classList.add('hidden');
        // Remove the modal from DOM after animation completes
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
}

// Function to open delete confirmation modal
function openDeleteModal(alumnusId) {
    // Remove any existing delete modal first
    const existingDeleteModal = document.getElementById('deleteModal');
    if (existingDeleteModal) {
        existingDeleteModal.remove();
    }

    fetch('../forms/modalAlumni.php')
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const deleteModal = tempContainer.querySelector('#deleteModal');
            if (deleteModal) {
                document.body.appendChild(deleteModal);
                document.getElementById('deleteAlumnusId').value = alumnusId;
                // Add a small delay to ensure the modal is in the DOM before showing it
                requestAnimationFrame(() => {
                    deleteModal.classList.remove('hidden');
                });
            }
        })
        .catch(error => {
            console.error('Error loading delete modal:', error);
            showToast('Failed to load delete confirmation. Please try again.', 'error');
        });
}

// Function to close delete confirmation modal
function closeDeleteModal() {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.classList.add('hidden');
        // Remove the modal from DOM after animation completes
        setTimeout(() => {
            if (deleteModal.parentNode) {
                deleteModal.parentNode.removeChild(deleteModal);
            }
        }, 300);
    }
}

// Function to open view modal
function openViewModal(alumnusId) {
    // Remove any existing view modals first
    const existingModal = document.getElementById('viewModal');
    if (existingModal) {
        existingModal.remove();
    }

    fetch(`../forms/modalAlumni.php?id=${alumnusId}&view=true`)
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#viewModal');
            if (modal) {
                document.body.appendChild(modal);
                // Add a small delay to ensure the modal is in the DOM before showing it
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            }
        })
        .catch(error => {
            console.error('Error loading view modal:', error);
            showToast('Failed to load alumni details. Please try again.', 'error');
        });
}

// Function to close view modal
function closeViewModal() {
    const modal = document.getElementById('viewModal');
    if (modal) {
        modal.classList.add('hidden');
        // Remove the modal from DOM after animation completes
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
}

// Update click outside handler to include view modal
document.addEventListener('click', function(event) {
    const alumniModal = document.getElementById('alumniModal');
    if (alumniModal && !alumniModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
        closeModal();
    }
    
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal && !deleteModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
        closeDeleteModal();
    }

    const viewModal = document.getElementById('viewModal');
    if (viewModal && !viewModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
        closeViewModal();
    }
});

// Handle form submission success/error messages
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        const successValue = urlParams.get('success');
        let message = '';
        
        switch(successValue) {
            case '1':
                message = 'Alumni added successfully!';
                break;
            case '2':
                message = 'Alumni updated successfully!';
                break;
            case '3':
                message = 'Alumni deleted successfully!';
                break;
        }
        
        if (message) {
            showToast(message, 'success');
        }
    }
    
    if (urlParams.has('error')) {
        const errorValue = urlParams.get('error');
        let message = 'An error occurred. Please try again.';
        
        switch(errorValue) {
            case 'alumni_creation_failed':
                message = 'Failed to create alumni record.';
                break;
            case 'user_creation_failed':
                message = 'Alumni created but user account creation failed.';
                break;
            case 'alumni_update_failed':
                message = 'Failed to update alumni record.';
                break;
            case 'user_update_failed':
                message = 'Alumni updated but user account update failed.';
                break;
            case 'alumni_delete_failed':
                message = 'Failed to delete alumni record.';
                break;
            case 'user_delete_failed':
                message = 'Failed to delete user account.';
                break;
        }
        
        showToast(message, 'error');
    }
});

// Toast notification function
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
        type === 'success' ? 'bg-neon-green' : 'bg-red-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Add fade-in animation
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
    });
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease-out';
        setTimeout(() => {
            if (toast.parentNode) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Form validation for alumni form
document.addEventListener('DOMContentLoaded', function() {
    const alumniForm = document.getElementById('alumniForm');
    if (alumniForm) {
        alumniForm.addEventListener('submit', function(event) {
            // Only validate user account fields if they exist (new alumni form)
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const username = document.getElementById('username');
            
            if (password && confirmPassword) {
                // Check if passwords match
                if (password.value !== confirmPassword.value) {
                    event.preventDefault();
                    showToast('Passwords do not match!', 'error');
                    return;
                }
                
                // Check password length
                if (password.value.length < 8) {
                    event.preventDefault();
                    showToast('Password must be at least 8 characters long!', 'error');
                    return;
                }
            }
            
            if (username) {
                // Check username format
                const usernameRegex = /^[a-zA-Z0-9_]+$/;
                if (!usernameRegex.test(username.value)) {
                    event.preventDefault();
                    showToast('Username can only contain letters, numbers, and underscores!', 'error');
                    return;
                }
            }
        });
    }
});

// Function to open the story modal
function openStoryModal(storyId = null) {
    // Remove any existing modals first
    const existingModal = document.getElementById('storyModal');
    if (existingModal) {
        existingModal.remove();
    }

    const url = storyId ? `../forms/modalStory.php?id=${storyId}` : '../forms/modalStory.php';
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#storyModal');
            if (modal) {
                document.body.appendChild(modal);
                // Add a small delay to ensure the modal is in the DOM before showing it
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            }
        })
        .catch(error => {
            console.error('Error loading modal:', error);
            showToast('Failed to load form. Please try again.', 'error');
        });
}

// Function to open story view modal
function openStoryViewModal(storyId) {
    // Remove any existing view modals first
    const existingModal = document.getElementById('viewModal');
    if (existingModal) {
        existingModal.remove();
    }

    fetch(`../forms/modalStory.php?id=${storyId}&view=true`)
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#viewModal');
            if (modal) {
                document.body.appendChild(modal);
                // Add a small delay to ensure the modal is in the DOM before showing it
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            }
        })
        .catch(error => {
            console.error('Error loading view modal:', error);
            showToast('Failed to load story details. Please try again.', 'error');
        });
}

// Function to open story delete modal
function openStoryDeleteModal(storyId) {
    // Remove any existing delete modal first
    const existingDeleteModal = document.getElementById('deleteModal');
    if (existingDeleteModal) {
        existingDeleteModal.remove();
    }

    fetch('../forms/modalStory.php')
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const deleteModal = tempContainer.querySelector('#deleteModal');
            if (deleteModal) {
                document.body.appendChild(deleteModal);
                document.getElementById('deleteStoryId').value = storyId;
                // Add a small delay to ensure the modal is in the DOM before showing it
                requestAnimationFrame(() => {
                    deleteModal.classList.remove('hidden');
                });
            }
        })
        .catch(error => {
            console.error('Error loading delete modal:', error);
            showToast('Failed to load delete confirmation. Please try again.', 'error');
        });
}

// Function to close story modal
function closeStoryModal() {
    const modal = document.getElementById('storyModal');
    if (modal) {
        modal.classList.add('hidden');
        // Remove the modal from DOM after animation completes
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
}

// Function to close story view modal
function closeStoryViewModal() {
    const modal = document.getElementById('viewModal');
    if (modal) {
        modal.classList.add('hidden');
        // Remove the modal from DOM after animation completes
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
}

// Function to close story delete modal
function closeStoryDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.add('hidden');
        // Remove the modal from DOM after animation completes
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
}

// Update click outside handler to include story modals
document.addEventListener('click', function(event) {
    const storyModal = document.getElementById('storyModal');
    if (storyModal && !storyModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
        closeStoryModal();
    }
    
    const storyViewModal = document.getElementById('viewModal');
    if (storyViewModal && !storyViewModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
        closeStoryViewModal();
    }
    
    const storyDeleteModal = document.getElementById('deleteModal');
    if (storyDeleteModal && !storyDeleteModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
        closeStoryDeleteModal();
    }
});

// User Details View Modal Functions
function openViewModal(userId) {
    // Remove any existing modals
    const existingModal = document.getElementById('viewModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Fetch and display the modal content
    fetch(`../forms/modalUser.php?id=${userId}`)
        .then(response => response.text())
        .then(html => {
            document.body.insertAdjacentHTML('beforeend', html);
            const modal = document.getElementById('viewModal');
            modal.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading user details', 'error');
        });
}

function closeViewModal() {
    const modal = document.getElementById('viewModal');
    if (modal) {
        modal.classList.add('hidden');
        // Remove the modal from DOM after animation
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('viewModal');
    if (modal && event.target === modal) {
        closeViewModal();
    }
});