// ==========================================================================

// Feed Me Plugin for Craft CMS

// ==========================================================================

if (typeof Craft.FeedMe === typeof undefined) {
    Craft.FeedMe = {};
}

$(function() {

    // Settings pane toggle for feeds index
    $(document).on('click', '#feeds .settings', function(e) {
        e.preventDefault();

        var row = $(this).parents('tr').data('id') + '-settings';
        var $settingsRow = $('tr[data-settings-id="' + row + '"] .settings-pane');

        $settingsRow.toggle();
    });

    // Change the import strategy for Users
    var $disableLabel = $('input[name="duplicateHandle[]"][value="disable"]').next('label');
    var originalDisableLabel = $disableLabel.text();
    var $disableInstructions = $disableLabel.siblings('.instructions');
    var originalDisableInstructions = $disableInstructions.text();

    // Toggle various field when changing element type
    $(document).on('change', '#elementType', function() {
        $('.element-select').hide();

        var value = $(this).val().replace(/\\/g, '-');
        $('.element-select[data-type="' + value + '"]').show();

        if (value === 'craft-elements-User') {
            $disableLabel.text(Craft.t('feed-me', 'Suspend missing users'));
            $disableInstructions.text(Craft.t('feed-me', 'Suspends any users that are missing from the feed.'));
        } else {
            $disableLabel.text(originalDisableLabel);
            $disableInstructions.text(originalDisableInstructions);
        }
    });

    $('#elementType').trigger('change');

    // Toggle the Entry Type field when changing the section select
    $(document).on('change', '.element-parent-group select', function() {
        var sections = $(this).parents('.element-sub-group').data('items') || {};
        var groupId = $(this).val();
        var entryType = 'item_' + groupId;
        var entryTypes = sections[entryType] || [];

        var currentValue = $('.element-child-group select').val();

        var newOptions = '<option value="">' + Craft.t('feed-me', 'None') + '</option>';
        $.each(entryTypes, function(index, value) {
            if (index) {
                newOptions += '<option value="' + index + '">' + value + '</option>';
            }
        });

        $('.element-child-group select').html(newOptions);

        // Select the first non-empty, or pre-selected
        if (currentValue) {
            $('.element-child-group select').val(currentValue);
        } else {
            $($('.element-child-group select').children()[1]).attr('selected', true);
        }

        // Show/hide the import settings depending on whether this group is a singleton
        var elementType = $('#elementType').val();
        if (
            Craft.FeedMe.elementTypes[elementType] &&
            Craft.FeedMe.elementTypes[elementType].groups[groupId] &&
            Craft.FeedMe.elementTypes[elementType].groups[groupId].isSingleton
        ) {
            if (!$('#singleton').val()) {
                $('#singleton').val('1');
                $('#is-create').attr({checked: false, disabled: true});
                $('#is-update').attr({checked: true, disabled: true});
                $('#is-disable-globally').attr({checked: false, disabled: true});
                $('#is-disable-site').attr({checked: false, disabled: true});
                $('#is-delete').attr({checked: false, disabled: true});
            }
        } else if ($('#singleton').val()) {
            $('#singleton').val('');
            $('#is-create').attr({checked: true, disabled: false});
            $('#is-update').attr({checked: false, disabled: false});
            $('#is-disable-globally').attr({checked: false, disabled: false});
            $('#is-disable-site').attr({checked: false, disabled: false});
            $('#is-delete').attr({checked: false, disabled: false});
        }
    });

    $('.element-parent-group select:visible').trigger('change');


    //
    // Field Mapping
    //

    // For field-mapping, auto-select Title if no unique checkboxes are set
    if ($('.feedme-uniques').length) {
        var checked = $('.feedme-uniques input[type="checkbox"]:checked').length;

        if (!checked) {
            $('.feedme-uniques input[type="checkbox"]:first').prop('checked', true);
        }
    }

    // For Assets, only show the upload options if we decide to upload
    $('.assets-uploads input').on('change', function(e) {
        var $options = $(this).parents('.field-extra-settings').find('.select');
        var $label = $(this).parents('.field-extra-settings').find('.asset-label-hide');

        if ($(this).prop('checked')) {
            $label.css({ opacity: 1, visibility: 'visible' });
            $options.css({ opacity: 1, visibility: 'visible' });
        } else {
            $label.css({ opacity: 0, visibility: 'hidden' });
            $options.css({ opacity: 0, visibility: 'hidden' });
        }
    });

    // On-load, hide/show upload options
    $('.assets-uploads input').trigger('change');

    // For elements, show the grouping select(s)
    $('.field-extra-settings .element-create input').on('change', function(e) {
        var $container = $(this).parents('.field-extra-settings').find('.element-groups');

        if ($(this).prop('checked')) {
            $container.show();
        } else {
            $container.hide();
        }
    });

    $('.field-extra-settings .element-create input').trigger('change');

    // Toggle various field when changing element type
    $('.field-extra-settings .element-group-section select').on('change', function(e) {
        var $container = $(this).parents('.field-extra-settings').find('.element-group-entrytype');
        var sections = $container.data('items');

        // var sections = $(this).parents('.element-sub-group').data('items');
        var entryType = 'item_' + $(this).val();
        var entryTypes = sections[entryType];

        var newOptions = '';
        $.each(entryTypes, function(index, value) {
            if (index) {
                newOptions += '<option value="' + index + '">' + value + '</option>';
            }
        });

        $container.find('select').html(newOptions);
    });

    $('.field-extra-settings .element-group-section select').trigger('change');

    // Selectize inputs
    $('.feedme-mapping .selectize select').selectize({
        allowEmptyOption: true,
    });

    // Help with sub-element field toggle
    $('.subelement-toggle label').on('click', function(e) {
        var $lightswitch = $(this).parents('.subelement-toggle').find('.lightswitch').data('lightswitch');

        $lightswitch.toggle();
    });

    // Show initially hidden element sub-fields. A little tricky because they're in a table, and all equal siblings
    $('.subelement-toggle .lightswitch').on('change', function(e) {
        var $lightswitch = $(this).data('lightswitch');
        var $tr = $(this).parents('tr');
        var $directSiblings = $tr.nextUntil(':not(.element-sub-field)');

        $directSiblings.toggle();
    });

    // If we have any element sub-fields that are being mapped, we want to show the panel to notify users they're mapping stuff
    $('.element-sub-field').each(function(index, element) {
        var mappingValue = $(this).find('.col-map select').val();
        var defaultValue = $(this).find('.col-default input').val();

        var rowValues = [mappingValue, defaultValue];
        var rowHasValue = false;

        // Check for inputs and selects which have a value
        $.each(rowValues, function(i, v) {
            if (v != '' && v != 'noimport' && v !== undefined) {
                rowHasValue = true;
            }
        });

        if (rowHasValue) {
            var $parentRow = $(this).prevUntil(':not(.element-sub-field)').addBack().prev();
            var $lightswitch = $parentRow.find('.lightswitch').data('lightswitch');

            $lightswitch.turnOn();
        }
    });


    //
    // Logs
    //
    $(document).on('click', '.log-detail-link', function(e) {
        e.preventDefault();

        var key = $(this).data('key');

        $('tr[data-key="' + key + '"]').toggleClass('hidden');
    });

    // Allow multiple submit actions, that trigger different actions as required
    $(document).on('click', 'input[data-action]', function(e) {
        var $form = $(this).parents('form');
        var action = $(this).data('action');

        $form.find('input[name="action"]').val(action);
        $form.submit();
    });

    $(document).on('change', '.log-type-form .select', function(e) {
        e.preventDefault();

        $(this).parents('form').submit();
    });
});
