function confirmDelete(action, name) {
    document.getElementById('deleteModalName').textContent = name;
    document.getElementById('deleteForm').action = action;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
function submitDelete() {
    document.getElementById('deleteForm').submit();
}
