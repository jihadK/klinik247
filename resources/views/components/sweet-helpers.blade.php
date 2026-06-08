<script>
(function () {
    'use strict';

    window.sweetConfirm = function (opts) {
        opts = opts || {};
        return Swal.fire({
            icon: opts.icon || 'warning',
            title: opts.title || 'Apakah Anda yakin?',
            text: opts.text || '',
            html: opts.html || null,
            showCancelButton: true,
            confirmButtonText: opts.confirmText || 'Ya, lanjutkan',
            cancelButtonText: opts.cancelText || 'Batal',
            customClass: {
                confirmButton: opts.confirmClass || 'btn btn-primary me-2',
                cancelButton:  opts.cancelClass  || 'btn btn-light'
            },
            buttonsStyling: false, reverseButtons: true
        });
    };

    function bindSweetConfirm(scope) {
        scope = scope || document;
        scope.querySelectorAll('[data-sweet-confirm]').forEach(function (el) {
            if (el.dataset.sweetBound === '1') return;
            el.dataset.sweetBound = '1';

            if (el.tagName === 'FORM') {
                el.addEventListener('submit', function (e) {
                    if (this.dataset.sweetConfirmed === '1') return;
                    e.preventDefault();
                    var form = this;
                    sweetConfirm({
                        title: form.dataset.sweetTitle, text: form.dataset.sweetText,
                        html: form.dataset.sweetHtml, icon: form.dataset.sweetIcon,
                        confirmText: form.dataset.sweetConfirmText,
                        confirmClass: form.dataset.sweetConfirmClass,
                    }).then(function (r) {
                        if (r.isConfirmed) { form.dataset.sweetConfirmed = '1'; form.submit(); }
                    });
                });
                return;
            }

            el.addEventListener('click', function (e) {
                e.preventDefault();
                var btn = this;
                sweetConfirm({
                    title: btn.dataset.sweetTitle, text: btn.dataset.sweetText,
                    html: btn.dataset.sweetHtml, icon: btn.dataset.sweetIcon,
                    confirmText: btn.dataset.sweetConfirmText,
                    confirmClass: btn.dataset.sweetConfirmClass,
                }).then(function (r) {
                    if (! r.isConfirmed) return;
                    var formSel = btn.dataset.sweetForm;
                    if (formSel) {
                        var form = document.querySelector(formSel);
                        if (form) { form.dataset.sweetConfirmed = '1'; form.submit(); return; }
                    }
                    var parentForm = btn.closest('form');
                    if (parentForm) { parentForm.dataset.sweetConfirmed = '1'; parentForm.submit(); }
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () { bindSweetConfirm(); });
    window.bindSweetConfirm = bindSweetConfirm;
})();
</script>
