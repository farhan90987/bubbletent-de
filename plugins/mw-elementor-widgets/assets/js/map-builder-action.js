function delete_map_data(){
    document.querySelectorAll('.mw-delete-map').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            if (!confirm('Are you sure you want to delete this map?')) return;

            var mapId = this.getAttribute('data-map-id');

            var formData = new FormData();
            formData.append('action', 'mw_delete_map');
            formData.append('map_id', mapId);

            this.textContent = 'Deleting...';
            var currentButton = this;

            fetch(ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Map deleted successfully.');
                    location.reload();
                } else {
                    alert(data.data || 'Delete failed.');
                    currentButton.textContent = 'Delete';
                }
            })
            .catch(error => {
                alert('AJAX error: ' + error);
                currentButton.textContent = 'Delete';
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    delete_map_data()
})