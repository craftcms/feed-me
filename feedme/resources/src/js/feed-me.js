$(function() {

    // Toggle various field when changing element type
    $(document).on('change', '#elementType', function() {
        $('.element-select').hide();
        $('.element-select-' + $(this).val()).show();
    })

    $('#elementType').trigger('change');

    // Toggle the Entry Type field when changing the section select
    $(document).on('change', '.element-parent-group select', function() {
        var sections = $(this).parents('.element-sub-group').data('items');
        var entryType = 'item_' + $(this).val();
        var entryTypes = sections[entryType];

        var currentValue = $('.element-child-group select').val();

        var newOptions = '<option value="">' + Craft.t('None') + '</option>';
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
    $('#primaryElement').keypress(function() {
        $(this).data('manual');
    });

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

    // Selectize inputs
    $('.feedme-mapping select').selectize({
        allowEmptyOption: true,
    });




    // Allow multiple submit actions, that trigger different actions as required
    $(document).on('click', 'input[data-action]', function(e) {
        var $form = $(this).parents('form');
        var action = $(this).data('action');

        $form.find('input[name="action"]').val(action);
        $form.submit();
    });

    // A nice loading animation on the success page for feeds
    new Craft.FeedMeTaskProgress();

});


(function() {

var feedMeSuccessHtml = '<div><span data-icon="check"></span> ' +
        Craft.t('Processing complete!') +
    '</div>' + 
    '<div class="feedme-success-btns">' +
        '<a class="btn submit" href="' + Craft.getUrl('feedme/feeds') + '">Back to Feeds</a>' + 
        '<a class="btn" href="' + Craft.getUrl('feedme/logs') + '">View logs</a>' + 
    '</div>';

Craft.FeedMeTaskProgress = Garnish.Base.extend({
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
        Craft.postActionRequest('tasks/getTaskInfo', $.proxy(function(taskInfo, textStatus) {
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
                this.runningTask = new Craft.FeedMeTaskProgress.Task(taskInfo);
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

Craft.FeedMeTaskProgress.Task = Garnish.Base.extend({
    progressBar: null,

    init: function(info) {
        this.$statusContainer = $('.feedme-fullpage.fullpage-running .progress-container');
        this.$statusContainer.empty();

        this.progressBar = new Craft.ProgressBar(this.$statusContainer);
        this.progressBar.showProgressBar();

        this.updateStatus(info);
    },

    updateStatus: function(info) {
        this.progressBar.setProgressPercentage(info.progress * 100);

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
        this.$statusContainer.html('<div class="error">' + Craft.t('Processing failed. <a class="go" href="' + Craft.getUrl('feedme/logs') + '">View logs</a>') + '</div>');
    },

});

})();