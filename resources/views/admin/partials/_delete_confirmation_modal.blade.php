{{-- This is a basic example. You'd need JavaScript to show/hide it and handle the form submission. --}}
{{-- For simplicity, the examples above use `confirm()`. This is a more advanced alternative. --}}

<div id="deleteConfirmationModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
  <div class="modal-content" style="background-color:#fefefe; margin:15% auto; padding:20px; border:1px solid #888; width:80%; max-width:500px; border-radius:5px; text-align:center;">
    <span class="close-modal-button" onclick="document.getElementById('deleteConfirmationModal').style.display='none'" style="color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer;">&times;</span>
    <h4>Confirm Deletion</h4>
    <p>Are you sure you want to delete this item? This action cannot be undone.</p>
    <p id="deleteModalItemName" style="font-weight:bold;"></p>
    <form id="deleteModalForm" action="" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-info" onclick="document.getElementById('deleteConfirmationModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
    </form>
  </div>
</div>

<script>
// Basic script to make the modal work - you might integrate this into your main admin JS file
// function showDeleteModal(formAction, itemName) {
//     document.getElementById('deleteModalForm').action = formAction;
//     const itemNameElement = document.getElementById('deleteModalItemName');
//     if (itemNameElement && itemName) {
//         itemNameElement.textContent = 'Item: ' + itemName;
//     } else if (itemNameElement) {
//         itemNameElement.textContent = '';
//     }
//     document.getElementById('deleteConfirmationModal').style.display = 'block';
// }

// Example usage in your index.blade.php action buttons:
// <button type="button" class="btn btn-danger" onclick="showDeleteModal('{{ route('admin.users.destroy', $user) }}', '{{ $user->name }}')">Delete</button>
// Then, the form inside the modal would submit.
// For now, the examples use a simpler `onsubmit="return confirm(...)"`.
</script>
