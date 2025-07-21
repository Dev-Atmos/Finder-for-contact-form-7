jQuery(document).ready(function ($) {
    // Initialize DataTable
    var table = $('table.cf7ff-table').DataTable({
        pageLength: 10,
        order: [[0, 'asc']],
        language: {
            search: "Filter:"
        }
    });

    // On filter change, submit form and reload the table
    $('#cf7ff-builder-filter-form').on('submit', function (e) {
        e.preventDefault();

        var builder = $('#cf7ff-builder-filter').val();
        $('#cf7ff-loading').show();
        var form_id = $('#cf7ff-form-id-filter').val();
        // inside data:

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'cf7ff_filter',
                builder: builder,
                nonce: cf7ff_admin_params.nonce,
                form_id: form_id,
            },
            success: function (response) {
                table.clear().rows.add(response.data).draw();
            },
            complete: function () {
                $('#cf7ff-loading').hide();
            }
        });
    });
    $('#cf7ff-view-report').on('click', function () {
        let selected = $('.cf7ff-select-row:checked').map(function () {
            return $(this).val();
        }).get();

        if (selected.length === 0) {
            alert('Please select at least one form.');
            return;
        }

        // Load detailed info via AJAX
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'cf7ff_get_details',
                form_ids: selected,
                nonce: cf7ff_admin_params.nonce
            },
            success: function (res) {
                if (res.success) {
                    $('#cf7ff-modal-content').html(res.data.html);
                    $('#cf7ff-modal, #cf7ff-modal-overlay').fadeIn();
                } else {
                    alert('Failed to load report.');
                }
            }
        });
    });
    $('#cf7ff-view-hardcoded-report').on('click', function () {
        $('#cf7ff-modal-content').html('');
        let selected = $('.cf7ff-select-hardcoded-row:checked').map(function () {
            return $(this).val();
        }).get();

        if (selected.length === 0) {
            alert('Please select at least one form.');
            return;
        }

        // Load detailed info via AJAX
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'cf7ff_get_details',
                form_ids: selected,
                nonce: cf7ff_admin_params.nonce
            },
            success: function (res) {
                if (res.success) {
                    $('#cf7ff-modal-content').html(res.data.html);
                    $('#cf7ff-modal, #cf7ff-modal-overlay').fadeIn();
                } else {
                    alert('Failed to load report.');
                }
            }
        });
    });

    $('#cf7ff-modal-close, #cf7ff-modal-overlay').on('click', function () {
        $('#cf7ff-modal, #cf7ff-modal-overlay').fadeOut();
    });
    $('#cf7ff-select-all').on('change', function () {
        $('.cf7ff-select-row').prop('checked', this.checked);
    });

    $('#cf7ff-download-report').on('click', function () {
        let selected = $('.cf7ff-select-row:checked').map(function () {
            return $(this).val();
        }).get();

        if (selected.length === 0) {
            alert('Select at least one form to download.');
            return;
        }

        // Create a hidden form and submit
        let form = $('<form method="post" action="' + cf7ff_admin_params.ajaxurl + '">')
            .append($('<input type="hidden" name="action" value="cf7_form_finder_download_csv">'))
            .append($('<input type="hidden" name="nonce" value="' + cf7ff_admin_params.nonce + '">'))
            .append($('<input type="hidden" name="form_ids" value="' + selected.join(',') + '">'));

        $('body').append(form);
        form.submit();
    });


});


