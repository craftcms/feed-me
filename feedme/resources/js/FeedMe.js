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

Craft.FeedMeTaskProgress = Garnish.Base.extend({
    tasksById: null,
    completedTasks: null,
    updateTasksTimeout: null,

    completed: false,

    init: function() {
        this.tasksById = {};
        this.completedTasks = [];

        // Force the tasks icon to run
        setTimeout($.proxy(function() {
            this.updateTasks();
        }, this), 1000);

        Craft.cp.stopTrackingTaskProgress();
    },

    updateTasks: function() {
        this.completed = false;

        Craft.postActionRequest('tasks/getTaskInfo', $.proxy(function(taskInfo, textStatus) {
            if (textStatus == 'success') {
                this.showTaskInfo(taskInfo[0]);
            }
        }, this))
    },

    showTaskInfo: function(taskInfo) {
        // First remove any tasks that have completed
        var newTaskIds = [];

        if (taskInfo) {
            newTaskIds.push(taskInfo.id);
        } else {
            // Likely too fast for Craft to register this was even a task!
            $('.progress-container').html('<div><span data-icon="check"></span> ' + Craft.t('Processing complete!') + '</div>');
        }

        for (var id in this.tasksById) {
            if (!Craft.inArray(id, newTaskIds)) {
                this.tasksById[id].complete();
                this.completedTasks.push(this.tasksById[id]);
                delete this.tasksById[id];
            }
        }

        // Now display the tasks that are still around
        if (taskInfo) {
            var anyTasksRunning = false,
                anyTasksFailed = false;

            if (!anyTasksRunning && taskInfo.status == 'running') {
                anyTasksRunning = true;
            } else if (!anyTasksFailed && taskInfo.status == 'error') {
                anyTasksFailed = true;
            }

            if (this.tasksById[taskInfo.id]) {
                this.tasksById[taskInfo.id].updateStatus(taskInfo);
            } else {
                this.tasksById[taskInfo.id] = new Craft.FeedMeTaskProgress.Task(taskInfo);
            }

            if (anyTasksRunning) {
                this.updateTasksTimeout = setTimeout($.proxy(this, 'updateTasks'), 500);
            } else {
                this.completed = true;

                if (anyTasksFailed) {
                    Craft.cp.setRunningTaskInfo({ status: 'error' });
                }
            }
        } else {
            this.completed = true;
            Craft.cp.setRunningTaskInfo(null);
        }
    }
});

Craft.FeedMeTaskProgress.Task = Garnish.Base.extend({
    id: null,
    level: null,
    description: null,

    status: null,
    progress: null,

    $container: null,
    $statusContainer: null,
    $descriptionContainer: null,

    _progressBar: null,

    init: function(info) {
        this.id = info.id;
        this.level = info.level;
        this.description = info.description;

        this.$container = $('.progress-container').html($('<div class="task"/>'));
        this.$statusContainer = $('<div class="task-status"/>').appendTo(this.$container);

        this.$container.data('task', this);

        this.updateStatus(info);
    },

    updateStatus: function(info) {
        if (this.status != info.status) {
            this.$statusContainer.empty();
            this.status = info.status;

            switch (this.status) {
                case 'running': {
                    this._progressBar = new Craft.ProgressBar(this.$statusContainer);
                    this._progressBar.showProgressBar();
                    break;
                }
                case 'error': {
                    $('<div class="error">' + Craft.t('Processing failed. <a class="go" href="' + Craft.getUrl('feedme/logs') + '">View logs</a>') + '</div>').appendTo(this.$statusContainer);
                    break;
                }
            }
        }

        if (this.status == 'running') {
            this._progressBar.setProgressPercentage(info.progress*100);

            if (this.level == 0) {
                // Update the task icon
                Craft.cp.setRunningTaskInfo(info, true);
            }
        }
    },

    complete: function()
    {
        this.$statusContainer.empty();
        $('<div><span data-icon="check"></span> ' + Craft.t('Processing complete!') + '</div>').appendTo(this.$statusContainer);
    },

    destroy: function() {
        this.$container.remove();
        this.base();
    }
});

})();