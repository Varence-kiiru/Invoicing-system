$(document).ready(function() {
    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        
        $.ajax({
            url: 'ajax/save_settings.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    alert('Settings saved successfully');
                    location.reload();
                } else {
                    alert(response.message || 'Error saving settings');
                }
            }
        });
    });
});
