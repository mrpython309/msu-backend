<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('/assets') . '/' }}" dir="ltr" data-skin="default" data-base-url="{{ url('/') }}" data-framework="laravel" data-bs-theme="light" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>
        @yield('title') | {{ config('variables.templateName') ? config('variables.templateName') : 'TemplateName' }}
        - {{ config('variables.templateSuffix') ? config('variables.templateSuffix') : 'TemplateSuffix' }}
    </title>
    <meta name="description" content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
    <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}" />
    <meta property="og:title" content="{{ config('variables.ogTitle') ? config('variables.ogTitle') : '' }}" />
    <meta property="og:type" content="{{ config('variables.ogType') ? config('variables.ogType') : '' }}" />
    <meta property="og:url" content="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
    <meta property="og:image" content="{{ config('variables.ogImage') ? config('variables.ogImage') : '' }}" />
    <meta property="og:description" content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
    <meta property="og:site_name" content="{{ config('variables.creatorName') ? config('variables.creatorName') : '' }}" />
    <meta name="robots" content="noindex, nofollow" />
    <!-- laravel CRUD token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- Canonical SEO -->
    <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">


    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">


    <!-- Include Styles -->
    @include('layouts/sections/styles')

    <style>
        /* Global Warm Linen & Stone Background */
        body, .layout-wrapper, .content-wrapper, .layout-container, .layout-page {
            background: linear-gradient(135deg, #f5f3ef 0%, #e6e3dd 100%) !important;
        }

        /* Sidebar active color overrides (Purple to Brown) */
        .layout-menu, .menu, .bg-menu-theme, #layout-menu {
            --bs-primary: #8B5E3C !important;
            --bs-primary-rgb: 139, 94, 60 !important;
            --bs-primary-bg-subtle: rgba(139, 94, 60, 0.12) !important;
            --bs-primary-border-subtle: rgba(139, 94, 60, 0.2) !important;
            --bs-purple: #8B5E3C !important;
            --bs-purple-rgb: 139, 94, 60 !important;
        }
        
        .bg-menu-theme .menu-inner > .menu-item.active > .menu-link {
            background-color: rgba(139, 94, 60, 0.12) !important;
            color: #8B5E3C !important;
        }
        .bg-menu-theme .menu-inner > .menu-item.active > .menu-link::after {
            background-color: #8B5E3C !important;
            border-color: #8B5E3C !important;
        }
        .bg-menu-theme .menu-sub > .menu-item.active > .menu-link {
            color: #8B5E3C !important;
        }
        .bg-menu-theme .menu-sub > .menu-item.active > .menu-link::before {
            background-color: #8B5E3C !important;
        }
        .menu-vertical .menu-item.active > .menu-link {
            color: #8B5E3C !important;
        }
        .menu-vertical .menu-item.active > .menu-link i {
            color: #8B5E3C !important;
        }
        .menu-item.active > .menu-link::after {
            background-color: #8B5E3C !important;
            border-color: #8B5E3C !important;
        }
        .bg-menu-theme .menu-sub > .menu-item.active > .menu-link::before {
            background-color: #8B5E3C !important;
            border-color: #8B5E3C !important;
        }
        .menu-vertical .menu-item.active > .menu-link::after {
            background-color: #8B5E3C !important;
            border-color: #8B5E3C !important;
        }
        .menu-vertical .menu-item.active > .menu-link::before {
            background-color: #8B5E3C !important;
            border-color: #8B5E3C !important;
        }
        
        /* Direct targeting of the active indicator bar */
        .menu-item.active > .menu-link::after,
        .menu-item.active > .menu-link::before,
        .menu-item.active::after,
        .menu-item.active::before {
            background-color: #8B5E3C !important;
            border-color: #8B5E3C !important;
        }
        
        /* If the indicator stripe is implemented as a border-right/border-left */
        .menu-item.active,
        .menu-item.active > .menu-link,
        .menu-vertical .menu-item.active {
            border-right-color: #8B5E3C !important;
            border-left-color: #8B5E3C !important;
        }

        /* Force toastr colors */
        #toast-container > .toast-success {
            background-color: #28a745 !important;
            color: #ffffff !important;
        }

        #toast-container > .toast-info {
            background-color: #0dcaf0 !important;
            color: #000000 !important;
        }

        #toast-container > .toast-warning {
            background-color: #ffc107 !important;
            color: #000000 !important;
        }

        #toast-container > .toast-error {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }

        /* Title + message */
        #toast-container > div .toast-title,
        #toast-container > div .toast-message {
            color: inherit !important;
        }
    </style>

    <!-- Include Scripts for customizer, helper, analytics, config -->
    @include('layouts/sections/scriptsIncludes')
</head>

<body>
    <!-- Layout Content -->
    @yield('layoutContent')
    <!--/ Layout Content -->



    <!-- Include Scripts -->
    @include('layouts/sections/scripts')

    @stack('scripts')
    
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        (function () {
            var path = window.location.pathname || '';
            var isProcurement = /\/(indents|msu-work-orders|msu-work-invoices|msu-purchase-orders|purchase-orders|purchase-requisitions|rfqs|msu-grns|grns|vendor-quotations|approval-templates|procurement|item-masters|item-master|item-stocks|stock-adjustments|material-requests|material-issues|employees|users|roles|permissions)(\/|$)/i.test(path)
                || /\/quotations\//i.test(path);

            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: isProcurement ? 800 : 5000,
                extendedTimeOut: isProcurement ? 0 : 2000,
            };

            document.addEventListener('DOMContentLoaded', function () {
                if (isProcurement && typeof Swal !== 'undefined') {
                    const originalSwalFire = Swal.fire;
                    Swal.fire = function(...args) {
                        let config = args[0];
                        if (typeof config === 'object' && config !== null) {
                            // If it's not a confirmation dialog, cap the timer
                            if (!config.showCancelButton && !config.showDenyButton) {
                                config.timer = 1000;
                                // Auto-closing, so hide the confirm button to make it cleaner
                                if (config.showConfirmButton === undefined) {
                                    config.showConfirmButton = false;
                                }
                            }
                        } else if (typeof config === 'string') {
                            return originalSwalFire({
                                title: args[0],
                                text: args[1],
                                icon: args[2],
                                timer: 1000,
                                showConfirmButton: false
                            });
                        }
                        return originalSwalFire.apply(this, args);
                    };
                }
            });

            @if(session('success'))
                toastr.success(@json(session('success')));
            @endif

            @if(session('info'))
                toastr.info(@json(session('info')));
            @endif

            @if(session('error'))
                toastr.error(@json(session('error')));
            @endif

            @if(session('warning'))
                toastr.warning(@json(session('warning')));
            @endif

            @if(session('workflow_error'))
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: @json(session('workflow_error.title', 'Configure approval workflow first')),
                            html: @json(session('workflow_error.message', 'Please assign the approval workflow before submitting this indent.')),
                            confirmButtonText: 'OK',
                            timer: isProcurement ? 1000 : undefined,
                            timerProgressBar: !!isProcurement,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            @endif

            // Auto-hide Bootstrap flash alerts on procurement pages after 1s
            if (isProcurement) {
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('.alert.alert-success, .alert.alert-danger, .alert.alert-warning, .alert.alert-info').forEach(function (el) {
                        if (el.id === 'po-payment-form-errors' || el.classList.contains('d-none')) return;
                        setTimeout(function () {
                            el.classList.add('fade');
                            el.style.opacity = '0';
                            setTimeout(function () { el.remove(); }, 200);
                        }, 800);
                    });
                });
            }
        })();
</script>

</body>

</html>
