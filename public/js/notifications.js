/**
 * In-house toast notifications (no alerts)
 */
(function () {
    var container = null;
    var defaultDuration = 4000;

    function getContainer() {
        if (container) return container;
        container = document.createElement('div');
        container.className = 'notify-container';
        container.setAttribute('aria-live', 'polite');
        document.body.appendChild(container);
        return container;
    }

    function showToast(message, type, duration) {
        type = type || 'info';
        duration = duration === undefined ? defaultDuration : duration;

        var el = document.createElement('div');
        el.className = 'notify-toast notify-' + type;
        el.textContent = message;

        var c = getContainer();
        c.appendChild(el);

        if (duration > 0) {
            setTimeout(function () {
                el.classList.add('notify-out');
                setTimeout(function () {
                    if (el.parentNode) el.parentNode.removeChild(el);
                }, 250);
            }, duration);
        }

        return el;
    }

    function confirm(message, options) {
        options = options || {};
        var okLabel = options.okLabel || 'OK';
        var cancelLabel = options.cancelLabel || 'Cancel';

        return new Promise(function (resolve) {
            var backdrop = document.createElement('div');
            backdrop.className = 'notify-backdrop';

            var el = document.createElement('div');
            el.className = 'notify-toast notify-info notify-confirm';
            el.innerHTML =
                '<span>' + escapeHtml(message || 'Continue?') + '</span>' +
                '<div class="notify-confirm-buttons">' +
                '<button type="button" class="notify-btn-cancel">' + escapeHtml(cancelLabel) + '</button>' +
                '<button type="button" class="notify-btn-ok">' + escapeHtml(okLabel) + '</button>' +
                '</div>';

            function escapeHtml(s) {
                var div = document.createElement('div');
                div.textContent = s;
                return div.innerHTML;
            }

            function done(value) {
                el.classList.add('notify-out');
                backdrop.classList.add('notify-out');
                setTimeout(function () {
                    if (backdrop.parentNode) backdrop.parentNode.removeChild(backdrop);
                    if (el.parentNode) el.parentNode.removeChild(el);
                }, 250);
                resolve(value);
            }

            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) done(false);
            });
            el.querySelector('.notify-btn-cancel').addEventListener('click', function (e) { e.preventDefault(); done(false); });
            el.querySelector('.notify-btn-ok').addEventListener('click', function (e) { e.preventDefault(); done(true); });

            document.body.appendChild(backdrop);
            document.body.appendChild(el);
        });
    }

    window.notify = {
        success: function (msg, duration) { return showToast(msg, 'success', duration); },
        error: function (msg, duration) { return showToast(msg, 'error', duration); },
        info: function (msg, duration) { return showToast(msg, 'info', duration); },
        confirm: confirm
    };
})();
