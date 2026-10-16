<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

<!-- Fonts Icons -->
@vite(['resources/assets/vendor/fonts/iconify/iconify.css'])

<!-- Core CSS -->
@vite(['resources/assets/vendor/scss/core.scss', 'resources/assets/css/demo.css'])

<!-- Vendor Styles -->
@vite('resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss')
@yield('vendor-style')

<!-- Page Styles -->
@yield('page-style')

<style>
/* Compact content + navbar spacing for all modules */
.layout-navbar {
  block-size: 3rem !important;
  min-block-size: 3rem !important;
  padding-block: 0.25rem !important;
}
.layout-navbar.navbar-detached {
  margin-block: 0.25rem 0 !important;
}
.content-wrapper > [class*="container-"] {
  padding-inline: 0.75rem !important;
  padding-block-start: 0.25rem !important;
  padding-block-end: 1rem !important;
}
.content-wrapper > [class*="container-"].container-p-y,
.layout-navbar-fixed .content-wrapper > [class*="container-"].container-p-y,
.content-wrapper > [class*="container-"].pt-2,
.content-wrapper > [class*="container-"].pt-1 {
  padding-block-start: 0.25rem !important;
  padding-block-end: 1rem !important;
}
.layout-navbar-fixed .layout-wrapper:not(.layout-horizontal):not(.layout-without-menu) .layout-page {
  padding-top: 3.5rem !important;
}
.layout-navbar-fixed .layout-wrapper:not(.layout-without-menu) .layout-page {
  padding-top: 3.25rem !important;
}
@media (min-width: 992px) {
  .content-wrapper > [class*="container-"] {
    padding-inline: 1rem !important;
  }
}
</style>

<!-- app CSS -->
@vite(['resources/css/app.css'])
<!-- END: app CSS-->
