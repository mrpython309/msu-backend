<!-- BEGIN: Vendor JS-->

@vite(['resources/assets/vendor/libs/popper/popper.js', 'resources/assets/vendor/js/bootstrap.js'])

@vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/js/menu.js'])

<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])

<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<script>
(function () {
    function initUninitializedSelect2() {
        if (typeof window.jQuery === 'undefined' || typeof $.fn.select2 !== 'function') return;
        $('select.select2').not('#global_company_id').not('select[name="global_company_id"]').each(function () {
            var $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) return;
            $select.select2({
                placeholder: $select.find('option[value=""]').first().text() || 'Select',
                allowClear: $select.find('option[value=""]').length > 0,
                width: '100%',
                minimumResultsForSearch: 0
            });
        });
    }
    if (typeof window.jQuery !== 'undefined') {
        $(initUninitializedSelect2);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUninitializedSelect2);
    } else {
        initUninitializedSelect2();
    }
})();
</script>
<!-- END: Page JS-->

<!-- app JS -->
@vite(['resources/js/app.js'])
<!-- END: app JS-->
