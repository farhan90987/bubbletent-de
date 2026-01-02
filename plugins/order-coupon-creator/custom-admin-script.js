jQuery(document).ready(function($) {
    // Flag to prevent multiple AJAX requests
    let isProcessing = false;

    function handleBulkAction(actionId) {
        if (isProcessing) {
            console.log('Processing already in progress');
            return;
        }

        isProcessing = true;
        console.log('Bulk action triggered:', actionId);
        var post_ids = [];
        $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
            post_ids.push($(this).val());
        });

        if (post_ids.length === 0) {
            alert('Please select at least one order.');
            isProcessing = false;
            return;
        }

        console.log('Selected post IDs:', post_ids);

        // Inject the modal HTML and styles dynamically with a spinner
        var modalHtml = `
            <div id="couponModal" class="coupon-modal">
                <div class="coupon-modal-content">
                    <span class="coupon-close">&times;</span>
                    <h2>Coupon Creation Log</h2>
                    <div id="coupon-log-content" style="max-height: 400px; overflow-y: auto;">
                        <div class="spinner"></div>
                        <p>Processing...</p>
                    </div>
                </div>
            </div>
            <style>
                .coupon-modal {
                    display: flex;
                    position: fixed;
                    z-index: 1000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0,0,0,0.4);
                    justify-content: center;
                    align-items: center;
                }
                .coupon-modal-content {
                    background-color: #fefefe;
                    padding: 20px;
                    border: 1px solid #888;
                    width: 80%;
                    max-width: 600px;
                    box-shadow: 0 5px 15px rgba(0,0,0,.5);
                    border-radius: 10px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                }
                .coupon-close {
                    color: #aaa;
                    align-self: flex-end;
                    font-size: 28px;
                    font-weight: bold;
                }
                .coupon-close:hover,
                .coupon-close:focus {
                    color: black;
                    text-decoration: none;
                    cursor: pointer;
                }
                .spinner {
                    border: 4px solid rgba(0, 0, 0, 0.1);
                    border-left-color: #000;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    animation: spin 1s linear infinite;
                    margin-bottom: 10px;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `;
        $('body').append(modalHtml);
        $('#couponModal').show();

        // Get the modal
        var modal = document.getElementById("couponModal");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("coupon-close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
            $('#couponModal').remove();
            isProcessing = false;
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
                $('#couponModal').remove();
                isProcessing = false;
            }
        }

        // AJAX request to handle the coupon creation
        $.ajax({
            url: occ_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'occ_handle_custom_bulk_action',
                post_ids: post_ids,
                action_id: actionId // Include the action ID for debugging
            },
            success: function(response) {
                console.log('AJAX success:', response);

                if (response.success) {
                    var log = response.data.join('<br>');
                    if (log) {
                        $('#coupon-log-content').html(log);
                    } else {
                        $('#coupon-log-content').html('<p>No log data available.</p>');
                    }
                } else {
                    $('#coupon-log-content').html('<p>Failed to create coupons.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', status, error);
                $('#coupon-log-content').html('<p>AJAX error: ' + status + ' - ' + error + '</p>');
                console.log(xhr.responseText);
            },
            complete: function() {
                isProcessing = false;
            }
        });
    }

    // Bind click events for both buttons with separate handlers
    $('#doaction').off('click').on('click', function(e) {
        console.log('Button clicked: doaction');
        var selectedAction = $('#bulk-action-selector-top').val();
        if (selectedAction === 'create_coupons') {
            e.preventDefault(); // Prevent the default form submission
            handleBulkAction('doaction');
        } else {
            console.log('Selected action is not create_coupons. No further action taken.');
        }
    });

    $('#doaction2').off('click').on('click', function(e) {
        console.log('Button clicked: doaction2');
        var selectedAction = $('#bulk-action-selector-bottom').val();
        if (selectedAction === 'create_coupons') {
            e.preventDefault(); // Prevent the default form submission
            handleBulkAction('doaction2');
        } else {
            console.log('Selected action is not create_coupons. No further action taken.');
        }
    });
});
