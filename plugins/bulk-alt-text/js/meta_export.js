jQuery(document).ready(function ($) {
    $('#export-button').on('click', function (e) {
        e.preventDefault();
        console.log('Export button clicked');

        const exportType = $('#export-type').val();
        const exportLanguage = $('#export-language').val();

        if (!exportType) {
            alert('Please select a type to export.');
            return;
        }

        if (!exportLanguage || exportLanguage.trim() === '') {
            alert('Please select a valid language to export.');
            return;
        }

        console.log('Export type:', exportType);
        console.log('Export language:', exportLanguage);

        $.ajax({
            url: BulkAltUpdater.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'export_meta_data',
                export_type: exportType,
                export_language: exportLanguage,
                security: BulkAltUpdater.nonce,
            },
            success: function (response) {
                console.log('Export response:', response);
                if (response.success) {
                    window.location.href = response.data.file_url;
                } else {
                    alert('Export failed: ' + response.data.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('An error occurred while processing the export.');
            },
        });
    });
});
