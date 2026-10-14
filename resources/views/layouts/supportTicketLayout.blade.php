@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@extends('layouts/commonMaster')

@php
$contentNavbar = $contentNavbar ?? true;
$containerNav = $containerNav ?? 'container-xxl';
$isNavbar = $isNavbar ?? false;
$isMenu = $isMenu ?? true;
$isFlex = $isFlex ?? false;
$isFooter = $isFooter ?? true;
$customizerHidden = $customizerHidden ?? '';

$navbarDetached = 'navbar-detached';
$menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
if (isset($navbarType)) {
    $configData['navbarType'] = $navbarType;
}
$navbarType = isset($configData['navbarType']) ? $configData['navbarType'] : '';
$footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';
$menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';

$container = ($container ?? 'container-xxl');
@endphp

@section('layoutContent')
<div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
    <div class="layout-container">

        @if ($isMenu)
        @include('layouts/sections/menu/verticalMenu')
        @endif

        <div class="layout-page">

            @if ($isNavbar)
            <nav class="layout-navbar support-ticket-navbar navbar navbar-expand-xl align-items-center" id="layout-navbar">
                @include('layouts/sections/navbar/support-ticket-navbar-partial')
            </nav>
            @endif

            <div class="content-wrapper">

                @if ($isFlex)
                <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
                @else
                <div class="{{ $container }} flex-grow-1 pt-2 pb-3 px-3">
                @endif

                    @yield('content')

                </div>

                @if ($isFooter)
                @include('layouts/sections/footer/footer')
                @endif
                <div class="content-backdrop fade"></div>
            </div>
        </div>

        @if ($isMenu)
        <div class="layout-overlay layout-menu-toggle"></div>
        @endif
        <div class="drag-target"></div>
    </div>
</div>
@endsection
