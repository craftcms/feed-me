$(function() {

    // Find entry types by chosen section
    $(document).on('change', '#section', function() {
        $('#entrytype').html('');
        Craft.postActionRequest('feedMe/getEntryTypes', { 'section': $(this).val() }, function(entrytypes) {
            $.each(entrytypes, function(index, value) {
                $('#entrytype').append('<option value="' + value.id + '">' + value.name + '</option>');
            });
        });
    });

    // Update the Primary XML Element - if not already set
    $(document).on('change', '#feedType', function() {
        // Don't do this if edit the field...
        //if ($('input[name="feedId"]').length == 0) {
            $('#primaryElement').html('');

            if ($(this).val() == 'rss') {
                $('#primaryElement').val('item');
            } else if ($(this).val() == 'atom') {
                $('#primaryElement').val('entry');
            } else {
                $('#primaryElement').val('');
            }
        //}
    });

    // Add an attribute to the Primary XML Element when typed in manually. This helps to prevent the above
    // triggering disrupt what the user has manually entered
    $( "#primaryElement" ).keypress(function() {
        $(this).attr('data-manual');
    });


    // Allow multiple back-end action hooks depending on button clicked. I'm sure there is a better way though!
    $(document).on('click', 'input[type="submit"]', function(e) {
        e.preventDefault();
        var form = $(this).parents('form');

        if ($(this).attr('data-action')) {
            $(form).find('input[name="action"]').val($(this).attr('data-action'));
        }

        $(form).submit();
    });

    
});