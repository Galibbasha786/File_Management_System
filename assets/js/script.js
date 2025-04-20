// Open share modal
function openShareModal(fileId) {
    document.getElementById('shareFileId').value = fileId;
    document.getElementById('shareModal').style.display = 'block';
}

// Close share modal
function closeShareModal() {
    document.getElementById('shareModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('shareModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}