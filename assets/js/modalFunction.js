// Alumni Modal Functions
function openAlumniModal(alumnusId = null) {
    // Remove any existing modals first
    const existingModal = document.getElementById('alumniModal');
    if (existingModal) existingModal.remove();
    
    const existingViewModal = document.getElementById('viewModal');
    if (existingViewModal) existingViewModal.remove();
    
    const existingDeleteModal = document.getElementById('deleteModal');
    if (existingDeleteModal) existingDeleteModal.remove();

    const url = alumnusId ? `../forms/modalAlumni.php?id=${alumnusId}` : '../forms/modalAlumni.php';
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#alumniModal');
            if (modal) {
                document.body.appendChild(modal);
                modal.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error loading modal:', error);
            showToast('Failed to load form. Please try again.', 'error');
        });
}

function closeModal() {
    // Check for various modals in order
    const eventModal = document.getElementById('eventModal');
    if (eventModal) {
        eventModal.classList.add('hidden');
        setTimeout(() => eventModal.remove(), 300);
        return;
    }
    
    const newsModal = document.getElementById('newsModal');
    if (newsModal) {
        newsModal.classList.add('hidden');
        setTimeout(() => newsModal.remove(), 300);
        return;
    }
    
    const announcementModal = document.getElementById('announcementModal');
    if (announcementModal) {
        announcementModal.classList.add('hidden');
        setTimeout(() => announcementModal.remove(), 300);
        return;
    }
    
    const jobModal = document.getElementById('jobModal');
    if (jobModal) {
        jobModal.classList.add('hidden');
        setTimeout(() => jobModal.remove(), 300);
        return;
    }
    
    // Then check for alumni modal
    const modal = document.getElementById('alumniModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function openViewModal(id) {
    // Check which page we're on
    const pathname = window.location.pathname;
    const isEventsPage = pathname.includes('manage_events') || pathname.includes('events.php');
    const isNewsPage = pathname.includes('manage_news') || pathname.includes('news.php');
    const isAnnouncementsPage = pathname.includes('announcements.php');
    const isJobsPage = pathname.includes('manage_jobs') || pathname.includes('jobs.php');
    
    // Remove any existing modals first
    removeExistingModals();

    let url;
    if (isEventsPage) {
        openEventViewModal(id);
        return;
    } else if (isNewsPage) {
        openNewsViewModal(id);
        return;
    } else if (isAnnouncementsPage) {
        url = `../forms/modalAnnouncement.php?id=${id}&view=true`;
    } else if (isJobsPage) {
        openJobViewModal(id);
        return;
    } else {
        url = `../forms/modalAlumni.php?id=${id}&view=true`;
    }

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#viewModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('View modal not found in response');
                showToast('Failed to load details. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading view modal:', error);
            showToast('Failed to load details. Please try again.', 'error');
        });
}

function closeViewModal() {
    const modal = document.getElementById('viewModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}



function openDeleteModal(id) {
    // Check which page we're on
    const pathname = window.location.pathname;
    const isEventsPage = pathname.includes('manage_events') || pathname.includes('events.php');
    const isNewsPage = pathname.includes('manage_news') || pathname.includes('news.php');
    const isAnnouncementsPage = pathname.includes('announcements.php');
    const isJobsPage = pathname.includes('manage_jobs') || pathname.includes('jobs.php');
    
    // Remove any existing modals first
    removeExistingModals();

    let url;
    if (isEventsPage) {
        openEventDeleteModal(id);
        return;
    } else if (isNewsPage) {
        openNewsDeleteModal(id);
        return;
    } else if (isAnnouncementsPage) {
        url = `../forms/modalAnnouncement.php?id=${id}&delete=true`;
    } else if (isJobsPage) {
        openJobDeleteModal(id);
        return;
    } else {
        url = `../forms/modalAlumni.php?id=${id}&delete=true`;
    }

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#deleteModal');
            if (modal) {
                document.body.appendChild(modal);
                // Set the ID in the delete form based on type
                if (isNewsPage) {
                    const deleteInput = modal.querySelector('#deleteNewsId');
                    if (deleteInput) deleteInput.value = id;
                } else if (isAnnouncementsPage) {
                    const deleteInput = modal.querySelector('#deleteAnnounceId');
                    if (deleteInput) deleteInput.value = id;
                } else if (isJobsPage) {
                    const deleteInput = modal.querySelector('#deleteJobId');
                    if (deleteInput) deleteInput.value = id;
                }
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('Delete modal not found in response');
                showToast('Failed to load delete confirmation. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading delete modal:', error);
            showToast('Failed to load delete confirmation. Please try again.', 'error');
        });
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

// Helper function to remove existing modals
function removeExistingModals() {
    const modals = [
        'alumniModal', 'storyModal', 'eventModal', 'newsModal', 'jobModal', 'announcementModal',
        'viewModal', 'deleteModal', 'alumniModalContainer', 'deleteModalContainer'
    ];

    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.remove();
        }
    });
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const alumniModal = document.getElementById('alumniModal');
    if (alumniModal && !alumniModal.contains(event.target) && !event.target.closest('[onclick*="openAlumniModal"]')) {
        closeModal();
    }
    
    const eventModal = document.getElementById('eventModal');
    if (eventModal && !eventModal.contains(event.target) && !event.target.closest('[onclick*="openEventModal"]')) {
        closeEventModal();
    }
    
    const eventViewModal = document.getElementById('viewModal');
    if (eventViewModal && !eventViewModal.contains(event.target) && !event.target.closest('[onclick*="openViewModal"]') && !event.target.closest('[onclick*="openEventViewModal"]')) {
        closeEventViewModal();
    }
    
    const eventDeleteModal = document.getElementById('deleteModal');
    if (eventDeleteModal && !eventDeleteModal.contains(event.target) && !event.target.closest('[onclick*="openDeleteModal"]') && !event.target.closest('[onclick*="openEventDeleteModal"]')) {
        closeEventDeleteModal();
    }
    
    const newsModal = document.getElementById('newsModal');
    if (newsModal && !newsModal.contains(event.target) && !event.target.closest('[onclick*="openNewsModal"]')) {
        closeNewsModal();
    }
    
    const newsViewModal = document.getElementById('viewModal');
    if (newsViewModal && !newsViewModal.contains(event.target) && !event.target.closest('[onclick*="openViewModal"]') && !event.target.closest('[onclick*="openNewsViewModal"]')) {
        closeNewsViewModal();
    }
    
    const newsDeleteModal = document.getElementById('deleteModal');
    if (newsDeleteModal && !newsDeleteModal.contains(event.target) && !event.target.closest('[onclick*="openDeleteModal"]') && !event.target.closest('[onclick*="openNewsDeleteModal"]')) {
        closeNewsDeleteModal();
    }
    
    const announcementModal = document.getElementById('announcementModal');
    if (announcementModal && !announcementModal.contains(event.target) && !event.target.closest('[onclick*="openAnnouncementModal"]')) {
        closeAnnouncementModal();
    }
    
    const announcementViewModal = document.getElementById('viewModal');
    if (announcementViewModal && !announcementViewModal.contains(event.target) && !event.target.closest('[onclick*="openViewModal"]') && !event.target.closest('[onclick*="openAnnouncementViewModal"]')) {
        closeAnnouncementViewModal();
    }
    
    const announcementDeleteModal = document.getElementById('deleteModal');
    if (announcementDeleteModal && !announcementDeleteModal.contains(event.target) && !event.target.closest('[onclick*="openDeleteModal"]') && !event.target.closest('[onclick*="openAnnouncementDeleteModal"]')) {
        closeAnnouncementDeleteModal();
    }
    
    const jobModal = document.getElementById('jobModal');
    if (jobModal && !jobModal.contains(event.target) && !event.target.closest('[onclick*="openJobModal"]')) {
        closeJobModal();
    }
    
    const jobViewModal = document.getElementById('viewModal');
    if (jobViewModal && !jobViewModal.contains(event.target) && !event.target.closest('[onclick*="openViewModal"]') && !event.target.closest('[onclick*="openJobViewModal"]')) {
        closeJobViewModal();
    }
    
    const jobDeleteModal = document.getElementById('deleteModal');
    if (jobDeleteModal && !jobDeleteModal.contains(event.target) && !event.target.closest('[onclick*="openDeleteModal"]') && !event.target.closest('[onclick*="openJobDeleteModal"]')) {
        closeJobDeleteModal();
    }
    
    const viewModal = document.getElementById('viewModal');
    if (viewModal && !viewModal.contains(event.target) && !event.target.closest('[onclick*="openViewModal"]')) {
        closeViewModal();
    }
    
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal && !deleteModal.contains(event.target) && !event.target.closest('[onclick*="openDeleteModal"]')) {
        closeDeleteModal();
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
    removeExistingModals();

    const url = storyId ? `../forms/modalStory.php?id=${storyId}` : '../forms/modalStory.php';
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#storyModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('Story modal not found in response');
                showToast('Failed to load form. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading modal:', error);
            showToast('Failed to load form. Please try again.', 'error');
        });
}

// Function to open story view modal
function openStoryViewModal(storyId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../forms/modalStory.php?id=${storyId}&view=true`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#viewModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('Story view modal not found in response');
                showToast('Failed to load story details. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading view modal:', error);
            showToast('Failed to load story details. Please try again.', 'error');
        });
}

// Function to open story delete modal
function openStoryDeleteModal(storyId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../forms/modalStory.php?id=${storyId}&delete=true`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const deleteModal = tempContainer.querySelector('#deleteModal');
            if (deleteModal) {
                document.body.appendChild(deleteModal);
                const deleteInput = deleteModal.querySelector('#deleteStoryId');
                if (deleteInput) {
                    deleteInput.value = storyId;
                } else {
                    console.error('deleteStoryId input not found in delete modal');
                }
                requestAnimationFrame(() => {
                    deleteModal.classList.remove('hidden');
                });
            } else {
                console.error('Story delete modal not found in response');
                showToast('Failed to load delete confirmation. Please try again.', 'error');
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
function openUserViewModal(userId) {
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

function closeUserViewModal() {
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
        closeUserViewModal();
    }
});

// Job Modal Functions
function openJobModal(jobId = null) {
    // Remove any existing modals first
    removeExistingModals();

    const url = jobId ? `../forms/modalJob.php?id=${jobId}` : '../forms/modalJob.php';
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#jobModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('Job modal not found in response');
                showToast('Failed to load job form. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading job modal:', error);
            showToast('Failed to load job form. Please try again.', 'error');
        });
}

function openJobViewModal(jobId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../forms/modalJob.php?id=${jobId}&view=true`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#viewModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('Job view modal not found in response');
                showToast('Failed to load job details. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading job view modal:', error);
            showToast('Failed to load job details. Please try again.', 'error');
        });
}

function openJobDeleteModal(jobId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../forms/modalJob.php?id=${jobId}&delete=true`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#deleteModal');
            if (modal) {
                document.body.appendChild(modal);
                const deleteInput = modal.querySelector('#deleteJobId');
                if (deleteInput) {
                    deleteInput.value = jobId;
                } else {
                    console.error('deleteJobId input not found in delete modal');
                }
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('Job delete modal not found in response');
                showToast('Failed to load delete confirmation. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading job delete modal:', error);
            showToast('Failed to load delete confirmation. Please try again.', 'error');
        });
}

function closeJobModal() {
    const modal = document.getElementById('jobModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function closeJobViewModal() {
    const modal = document.getElementById('viewModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function closeJobDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

// Event Modal Functions
function openEventModal(eventId = null) {
    // Remove any existing modals first
    removeExistingModals();

    const url = eventId ? `../forms/modalEvent.php?id=${eventId}` : '../forms/modalEvent.php';
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#eventModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('Event modal not found in response');
                showToast('Failed to load event form. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading event modal:', error);
            showToast('Failed to load event form. Please try again.', 'error');
        });
}

function openEventViewModal(eventId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../forms/modalEvent.php?id=${eventId}&view=true`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#viewModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('Event view modal not found in response');
                showToast('Failed to load event details. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading event view modal:', error);
            showToast('Failed to load event details. Please try again.', 'error');
        });
}

function openEventDeleteModal(eventId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../forms/modalEvent.php?id=${eventId}&delete=true`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#deleteModal');
            if (modal) {
                document.body.appendChild(modal);
                const deleteInput = modal.querySelector('#deleteEventId');
                if (deleteInput) {
                    deleteInput.value = eventId;
                } else {
                    console.error('deleteEventId input not found in delete modal');
                }
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('Event delete modal not found in response');
                showToast('Failed to load delete confirmation. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading event delete modal:', error);
            showToast('Failed to load delete confirmation. Please try again.', 'error');
        });
}

function closeEventModal() {
    const modal = document.getElementById('eventModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function closeEventViewModal() {
    const modal = document.getElementById('viewModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function closeEventDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

// News Modal Functions
function openNewsModal(newsId = null) {
    // Remove any existing modals first
    removeExistingModals();

    const url = newsId ? `../forms/modalNews.php?id=${newsId}` : '../forms/modalNews.php';
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#newsModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('News modal not found in response');
                showToast('Failed to load news form. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading news modal:', error);
            showToast('Failed to load news form. Please try again.', 'error');
        });
}

function openNewsViewModal(newsId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../forms/modalNews.php?id=${newsId}&view=true`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#viewModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('News view modal not found in response');
                showToast('Failed to load news details. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading news view modal:', error);
            showToast('Failed to load news details. Please try again.', 'error');
        });
}

function openNewsDeleteModal(newsId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../forms/modalNews.php?id=${newsId}&delete=true`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#deleteModal');
            if (modal) {
                document.body.appendChild(modal);
                const deleteInput = modal.querySelector('#deleteNewsId');
                if (deleteInput) {
                    deleteInput.value = newsId;
                } else {
                    console.error('deleteNewsId input not found in delete modal');
                }
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.error('News delete modal not found in response');
                showToast('Failed to load delete confirmation. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading news delete modal:', error);
            showToast('Failed to load delete confirmation. Please try again.', 'error');
        });
}

function closeNewsModal() {
    const modal = document.getElementById('newsModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function closeNewsViewModal() {
    const modal = document.getElementById('viewModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function closeNewsDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

// Announcement Modal Functions
function openAnnouncementModal(announcementId = null) {
    // Remove any existing modals first
    removeExistingModals();

    const url = announcementId ? `../forms/modalAnnouncement.php?id=${announcementId}` : '../forms/modalAnnouncement.php';
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#announcementModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.warn('Announcement modal element not found in fetched HTML.');
                showToast('Failed to load announcement form: Modal element missing.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading announcement modal:', error);
            showToast('Failed to load announcement form. Please try again.', 'error');
        });
}

function openAnnouncementViewModal(announcementId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../forms/modalAnnouncement.php?id=${announcementId}&view=true`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const modal = tempContainer.querySelector('#viewModal');
            if (modal) {
                document.body.appendChild(modal);
                requestAnimationFrame(() => {
                    modal.classList.remove('hidden');
                });
            } else {
                console.warn('Announcement view modal element not found in fetched HTML.');
                showToast('Failed to load announcement details: Modal element missing.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading announcement view modal:', error);
            showToast('Failed to load announcement details. Please try again.', 'error');
        });
}

function openAnnouncementDeleteModal(announcementId) {
    // Remove any existing modals first
    removeExistingModals();

    fetch(`../forms/modalAnnouncement.php?id=${announcementId}&delete=true`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            const deleteModal = tempContainer.querySelector('#deleteModal');
            if (deleteModal) {
                document.body.appendChild(deleteModal);
                const deleteInput = deleteModal.querySelector('#deleteAnnounceId');
                if (deleteInput) {
                    deleteInput.value = announcementId;
                } else {
                    console.error('deleteAnnounceId input not found in delete modal');
                }
                requestAnimationFrame(() => {
                    deleteModal.classList.remove('hidden');
                });
            } else {
                console.warn('Announcement delete modal element not found in fetched HTML.');
                showToast('Failed to load delete confirmation: Modal element missing.', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading announcement delete modal:', error);
            showToast('Failed to load delete confirmation. Please try again.', 'error');
        });
}

function closeAnnouncementModal() {
    const modal = document.getElementById('announcementModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function closeAnnouncementViewModal() {
    const modal = document.getElementById('viewModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}

function closeAnnouncementDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => modal.remove(), 300);
    }
}



// Expose event modal functions globally to ensure inline onclick handlers can access them
window.openEventModal = window.openEventModal || openEventModal;
window.openEventViewModal = window.openEventViewModal || openEventViewModal;
window.openEventDeleteModal = window.openEventDeleteModal || openEventDeleteModal;
window.closeEventModal = window.closeEventModal || closeEventModal;
window.closeEventViewModal = window.closeEventViewModal || closeEventViewModal;
window.closeEventDeleteModal = window.closeEventDeleteModal || closeEventDeleteModal;

// Expose job modal functions globally to ensure inline onclick handlers can access them
window.openJobModal = window.openJobModal || openJobModal;
window.openJobViewModal = window.openJobViewModal || openJobViewModal;
window.openJobDeleteModal = window.openJobDeleteModal || openJobDeleteModal;
window.closeJobModal = window.closeJobModal || closeJobModal;
window.closeJobViewModal = window.closeJobViewModal || closeJobViewModal;
window.closeJobDeleteModal = window.closeJobDeleteModal || closeJobDeleteModal;

// Expose news modal functions globally to ensure inline onclick handlers can access them
window.openNewsModal = window.openNewsModal || openNewsModal;
window.openNewsViewModal = window.openNewsViewModal || openNewsViewModal;
window.openNewsDeleteModal = window.openNewsDeleteModal || openNewsDeleteModal;
window.closeNewsModal = window.closeNewsModal || closeNewsModal;
window.closeNewsViewModal = window.closeNewsViewModal || closeNewsViewModal;
window.closeNewsDeleteModal = window.closeNewsDeleteModal || closeNewsDeleteModal;

// Expose story modal functions globally to ensure inline onclick handlers can access them
window.openStoryModal = window.openStoryModal || openStoryModal;
window.openStoryViewModal = window.openStoryViewModal || openStoryViewModal;
window.openStoryDeleteModal = window.openStoryDeleteModal || openStoryDeleteModal;
window.closeStoryModal = window.closeStoryModal || closeStoryModal;
window.closeStoryViewModal = window.closeStoryViewModal || closeStoryViewModal;
window.closeStoryDeleteModal = window.closeStoryDeleteModal || closeStoryDeleteModal;

// Expose announcement modal functions globally to ensure inline onclick handlers can access them
window.openAnnouncementModal = window.openAnnouncementModal || openAnnouncementModal;
window.openAnnouncementViewModal = window.openAnnouncementViewModal || openAnnouncementViewModal;
window.openAnnouncementDeleteModal = window.openAnnouncementDeleteModal || openAnnouncementDeleteModal;
window.closeAnnouncementModal = window.closeAnnouncementModal || closeAnnouncementModal;
window.closeAnnouncementViewModal = window.closeAnnouncementViewModal || closeAnnouncementViewModal;
window.closeAnnouncementDeleteModal = window.closeAnnouncementDeleteModal || closeAnnouncementDeleteModal;

