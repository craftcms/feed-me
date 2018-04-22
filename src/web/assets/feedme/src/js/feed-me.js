// ==========================================================================

// Feed Me Plugin for Craft CMS
// Author: Verbb - https://verbb.io/

// ==========================================================================

// @codekit-prepend "_help.js"    
// @codekit-prepend "_selectize.js" 

if (typeof Craft.FeedMe === typeof undefined) {
    Craft.FeedMe = {};
}

$(function() {

    // Settings pane toggle for feeds index
    $(document).on('click', '#feeds .settings', function(e) {
        e.preventDefault();

        var row = $(this).parents('tr').data('id') + '-settings';
        var $settingsRow = $('tr[data-id="' + row + '"] .settings-pane');

        $settingsRow.toggle();
    });

    // Toggle various field when changing element type
    $(document).on('change', '#elementType', function() {
        $('.element-select').hide();

        var value = $(this).val().replace(/\\/g, '-');
        $('.element-select[data-type="' + value + '"]').show();
    });

    $('#elementType').trigger('change');

    // Toggle the Entry Type field when changing the section select
    $(document).on('change', '.element-parent-group select', function() {
        var sections = $(this).parents('.element-sub-group').data('items');
        var entryType = 'item_' + $(this).val();
        var entryTypes = sections[entryType];

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
    });

    $('.element-parent-group select').trigger('change');


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

    // For elements, show the grouping select(s)
    $('.field-extra-settings .element-create input').on('change', function(e) {
        var $container = $(this).parents('.field-extra-settings').find('.element-groups');

        if ($(this).prop('checked')) {
            $container.show();
        } else {
            $container.hide();
        }
    });

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

    // On-load, hide/show upload options
    $('.assets-uploads input').trigger('change');

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






    // Allow multiple submit actions, that trigger different actions as required
    $(document).on('click', 'input[data-action]', function(e) {
        var $form = $(this).parents('form');
        var action = $(this).data('action');

        $form.find('input[name="action"]').val(action);
        $form.submit();
    });

    // A nice loading animation on the success page for feeds
    new Craft.FeedMe.TaskProgress();

});


(function() {

var feedMeSuccessHtml = '<div><span data-icon="check"></span> ' +
        Craft.t('feed-me', 'Processing complete!') +
    '</div>' + 
    '<div class="feedme-success-btns">' +
        '<a class="btn submit" href="' + Craft.getUrl('feed-me/feeds') + '">Back to Feeds</a>' + 
        '<a class="btn" href="' + Craft.getUrl('feed-me/logs') + '">View logs</a>' + 
    '</div>';

Craft.FeedMe.TaskProgress = Garnish.Base.extend({
    runningTask: null,

    $spinnerScreen: null,
    $pendingScreen: null,
    $runningScreen: null,

    init: function() {
        this.$spinnerScreen = $('.feedme-status-spinner');
        this.$pendingScreen = $('.feedme-fullpage.fullpage-waiting');
        this.$runningScreen = $('.feedme-fullpage.fullpage-running');

        this.updateTasks();
    },

    updateTasks: function() {
        Craft.postActionRequest('queue/get-job-info', $.proxy(function(taskInfo, textStatus) {
            if (textStatus == 'success') {
                this.showTaskInfo(taskInfo[0]);
            }
        }, this))
    },

    showTaskInfo: function(taskInfo) {
        this.$spinnerScreen.addClass('hidden');

        if (taskInfo) {
            this.$runningScreen.removeClass('hidden');

            if (this.runningTask) {
                this.runningTask.updateStatus(taskInfo);
            } else {
                this.runningTask = new Craft.FeedMe.TaskProgress.Task(taskInfo);
            }

            if (taskInfo.status != 'error') {
                // Keep checking for the task status every 500ms
                setTimeout($.proxy(this, 'updateTasks'), 500);
            }
        } else {
            if (this.runningTask) {
                // Task has now completed, show the UI
                this.runningTask.complete();
            } else if (this.$pendingScreen.hasClass('cp-triggered')) {
                // If this case has happened, its often the task has finished so quickly before an Ajax request
                // to the tasks controller has a chance to fire. But, we track when the user submits the 'run' action
                // through a flash variable. Technically, its finished - otherwise we end up showing the 'pending'
                // screen, which is a little confusing to the user. Simply show its completed
                this.$runningScreen.removeClass('hidden');

                this.$runningScreen.find('.progress-container').html(feedMeSuccessHtml);
            } else {
                // Show the pending screen, there are no tasks in queue, and a task isn't currently running
                this.$pendingScreen.removeClass('hidden');
            }
        }

    }
});

Craft.FeedMe.TaskProgress.Task = Garnish.Base.extend({
    progressBar: null,

    init: function(info) {
        this.$statusContainer = $('.feedme-fullpage.fullpage-running .progress-container');
        this.$statusContainer.empty();

        this.progressBar = new Craft.ProgressBar(this.$statusContainer);
        this.progressBar.showProgressBar();

        this.updateStatus(info);
    },

    updateStatus: function(info) {
        this.progressBar.setProgressPercentage(info.progress);

        if (info.status == 'error') {
            this.fail();
        }
    },

    complete: function() {
        this.progressBar.setProgressPercentage(100);
        setTimeout($.proxy(this, 'success'), 300);
    },

    success: function() {
        this.$statusContainer.html(feedMeSuccessHtml);
    },

    fail: function() {
        this.$statusContainer.html('<div class="error">' + Craft.t('feed-me', 'Processing failed. <a class="go" href="' + Craft.getUrl('feed-me/logs') + '">View logs</a>') + '</div>');
    },

});

})();