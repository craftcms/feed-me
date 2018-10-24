(function($){

if (typeof Craft.FeedMe === typeof undefined) {
    Craft.FeedMe = {};
}

Craft.FeedMe.LicenseForm = Craft.BaseElementIndex.extend({
    licenseKey: null,
    licenseKeyStatus: null,

    $headers: null,
    $views: null,

    $validLicenseHeader: null,
    $invalidLicenseHeader: null,
    $mismatchedLicenseHeader: null,
    $unknownLicenseHeader: null,

    $validLicenseView: null,
    $updateLicenseView: null,

    $unregisterLicenseForm: null,
    $updateLicenseForm: null,
    $transferLicenseForm: null,

    $unregisterLicenseSpinner: null,
    $updateLicenseSpinner: null,
    $transferLicenseSpinner: null,

    $licenseKeyLabel: null,
    $licenseKeyInput: null,
    $updateBtn: null,
    $clearBtn: null,
    $licenseKeyError: null,

    init: function(hasLicenseKey) {
        this.$headers = $('.reg-header');
        this.$views = $('.reg-view');

        this.$validLicenseHeader = $('#valid-license-header');
        this.$invalidLicenseHeader = $('#invalid-license-header');
        this.$mismatchedLicenseHeader = $('#mismatched-license-header');
        this.$unknownLicenseHeader = $('#unknown-license-header');

        this.$validLicenseView = $('#valid-license-view');
        this.$updateLicenseView = $('#update-license-view');

        this.$unregisterLicenseForm = $('#unregister-license-form');
        this.$updateLicenseForm = $('#update-license-form');
        this.$transferLicenseForm = $('#transfer-license-form');

        this.$unregisterLicenseSpinner = $('#unregister-license-spinner');
        this.$updateLicenseSpinner = $('#update-license-spinner');
        this.$transferLicenseSpinner = $('#transfer-license-spinner');

        this.$licenseKeyLabel = $('#license-key-label');
        this.$licenseKeyInput = $('#license-key-input');
        this.$updateBtn = $('#update-license-btn');
        this.$clearBtn = $('#clear-license-btn');
        this.$licenseKeyError = $('#license-key-error');

        this.addListener(this.$unregisterLicenseForm, 'submit', 'handleUnregisterLicenseFormSubmit');
        this.addListener(this.$updateLicenseForm, 'submit', 'handleUpdateLicenseFormSubmit');
        this.addListener(this.$transferLicenseForm, 'submit', 'handleTransferLicenseFormSubmit');

        this.addListener(this.$licenseKeyInput, 'focus', 'handleLicenseKeyFocus');
        this.addListener(this.$licenseKeyInput, 'textchange', 'handleLicenseKeyTextChange');
        this.addListener(this.$clearBtn, 'click', 'handleClearButtonClick');

        if (hasLicenseKey) {
            this.loadLicenseInfo();
        } else {
            this.unloadLoadingUi();
            this.setLicenseKey(null);
            this.setLicenseKeyStatus('unknown');
        }
    },

    unloadLoadingUi: function() {
        $('#loading-license-info').remove();
        $('#license-view-hr').removeClass('hidden');
    },

    loadLicenseInfo: function(action) {
        Craft.postActionRequest('feedMe/license/getLicenseInfo', $.proxy(function(response, textStatus) {
            if (textStatus == 'success') {
                if (response.success) {
                    this.unloadLoadingUi();
                    this.setLicenseKey(response.licenseKey);
                    this.setLicenseKeyStatus(response.licenseKeyStatus);
                } else {
                    $('#loading-graphic').addClass('error');
                    $('#loading-status').removeClass('light').text(Craft.t('Unable to load registration status at this time. Please try again later.'));
                }
            }
        }, this));
    },

    setLicenseKey: function(licenseKey) {
        this.licenseKey = this.normalizeLicenseKey(licenseKey);
        var formattedLicenseKey = this.formatLicenseKey(this.licenseKey);
        this.$licenseKeyLabel.text(formattedLicenseKey);
        this.$licenseKeyInput.val(formattedLicenseKey);
        this.handleLicenseKeyTextChange();
    },

    setLicenseKeyStatus: function(licenseKeyStatus) {
        this.$headers.addClass('hidden');
        this.$views.addClass('hidden');

        this.licenseKeyStatus = licenseKeyStatus;

        // Show the proper header
        this['$'+licenseKeyStatus+'LicenseHeader'].removeClass('hidden');

        // Show the proper form view
        if (this.licenseKeyStatus == 'valid') {
            this.$validLicenseView.removeClass('hidden');
        } else {
            this.$updateLicenseView.removeClass('hidden');
            this.$licenseKeyError.addClass('hidden');

            if (this.licenseKeyStatus == 'invalid') {
                this.$licenseKeyInput.addClass('error');
            } else {
                this.$licenseKeyInput.removeClass('error');
            }

            if (this.licenseKeyStatus == 'mismatched') {
                this.$transferLicenseForm.removeClass('hidden');
            } else {
                this.$transferLicenseForm.addClass('hidden');
            }
        }
    },

    normalizeLicenseKey: function(licenseKey) {
        if (licenseKey) {
            return licenseKey.toUpperCase().replace(/[^A-Z0-9]/g, '');
        }

        return '';
    },

    formatLicenseKey: function(licenseKey) {
        if (licenseKey) {
            return licenseKey.match(/.{1,4}/g).join('-');
        }

        return '';
    },

    validateLicenseKey: function(licenseKey) {
        return (licenseKey.length == 24);
    },

    handleUnregisterLicenseFormSubmit: function(ev) {
        ev.preventDefault();
        this.$unregisterLicenseSpinner.removeClass('hidden');
        Craft.postActionRequest('feedMe/license/unregister', $.proxy(function(response, textStatus) {
            this.$unregisterLicenseSpinner.addClass('hidden');
            if (textStatus == 'success') {
                if (response.success) {
                    this.setLicenseKey(response.licenseKey);
                    this.setLicenseKeyStatus('unknown');
                } else {
                    Craft.cp.displayError(response.error);
                }
            }
        }, this));
    },

    handleUpdateLicenseFormSubmit: function(ev) {
        ev.preventDefault();
        var licenseKey = this.normalizeLicenseKey(this.$licenseKeyInput.val());

        if (licenseKey && !this.validateLicenseKey(licenseKey)) {
            return;
        }

        this.$updateLicenseSpinner.removeClass('hidden');

        var data = {
            licenseKey: licenseKey
        };

        Craft.postActionRequest('feedMe/license/updateLicenseKey', data, $.proxy(function(response, textStatus) {
            this.$updateLicenseSpinner.addClass('hidden');
            if (textStatus == 'success') {
                if (response.licenseKey) {
                    this.setLicenseKey(response.licenseKey);
                    this.setLicenseKeyStatus(response.licenseKeyStatus);
                } else {
                    this.$licenseKeyError.removeClass('hidden').text(response.error || Craft.t('An unknown error occurred.'));
                }
            }
        }, this));
    },

    handleTransferLicenseFormSubmit: function(ev) {
        ev.preventDefault();
        this.$transferLicenseSpinner.removeClass('hidden');
        Craft.postActionRequest('feedMe/license/transfer', $.proxy(function(response, textStatus) {
            this.$transferLicenseSpinner.addClass('hidden');
            if (textStatus == 'success') {
                if (response.success) {
                    this.setLicenseKey(response.licenseKey);
                    this.setLicenseKeyStatus(response.licenseKeyStatus);
                } else {
                    Craft.cp.displayError(response.error);
                }
            }
        }, this));
    },

    handleLicenseKeyFocus: function() {
        this.$licenseKeyInput.get(0).setSelectionRange(0, this.$licenseKeyInput.val().length);
    },

    handleLicenseKeyTextChange: function() {
        this.$licenseKeyInput.removeClass('error');

        var licenseKey = this.normalizeLicenseKey(this.$licenseKeyInput.val());

        if (licenseKey) {
            this.$clearBtn.removeClass('hidden');
        } else {
            this.$clearBtn.addClass('hidden');
        }

        if (licenseKey != this.licenseKey && (!licenseKey || this.validateLicenseKey(licenseKey))) {
            this.$updateBtn.removeClass('disabled');
        } else {
            this.$updateBtn.addClass('disabled');
        }
    },

    handleClearButtonClick: function() {
        this.$licenseKeyInput.val('').focus();
        this.handleLicenseKeyTextChange();
    }
});

})(jQuery);
