(function($) {

Craft.FeedMeHelp = Garnish.Base.extend({
    widgetId: 0,
    loading: false,

    $widget: null,
    $message: null,
    $fromEmail: null,
    $attachLogs: null,
    $attachDbBackup: null,
    $attachAdditionalFile: null,
    $sendBtn: null,
    $spinner: null,
    $error: null,
    $errorList: null,
    $iframe: null,

    init: function() {
        this.$widget = $('.feedme-help-form');
        this.$message = this.$widget.find('.message:first');
        this.$fromEmail = this.$widget.find('.fromEmail:first');
        this.$attachLogs = this.$widget.find('.attachLogs:first');
        this.$attachAdditionalFile = this.$widget.find('.attachAdditionalFile:first');
        this.$sendBtn = this.$widget.find('.submit:first');
        this.$spinner = this.$widget.find('.buttons .spinner');
        this.$error = this.$widget.find('.error:first');
        this.$form = this.$widget.find('form:first');

        this.addListener(this.$sendBtn, 'activate', 'sendMessage');
    },

    sendMessage: function() {
        if (this.loading) return;

        this.loading = true;
        this.$sendBtn.addClass('active');
        this.$spinner.removeClass('hidden');

        var data = this.$form.serialize();

        Craft.postActionRequest('feedMe/help/sendSupportRequest', data, $.proxy(this, 'parseResponse'));
    },

    parseResponse: function(response, textStatus) {
        this.loading = false;
        this.$sendBtn.removeClass('active');
        this.$spinner.addClass('hidden');

        if (this.$errorList) {
            this.$errorList.children().remove();
        }

        if (!response) {
            if (!this.$errorList) {
                this.$errorList = $('<ul class="errors"/>').insertAfter(this.$form);
            }

            $('<li>Something went wrong...</li>').appendTo(this.$errorList);
        }

        if (response.errors) {
            if (!this.$errorList) {
                this.$errorList = $('<ul class="errors"/>').insertAfter(this.$form);
            }

            for (var attribute in response.errors) {
                for (var i = 0; i < response.errors[attribute].length; i++)
                {
                    var error = response.errors[attribute][i];
                    $('<li>'+error+'</li>').appendTo(this.$errorList);
                }
            }
        }

        if (response.success) {
            Craft.cp.displayNotice(Craft.t('Message sent successfully.'));
            this.$message.val('');
            this.$attachAdditionalFile.val('');
        }
    }
});

})(jQuery);
