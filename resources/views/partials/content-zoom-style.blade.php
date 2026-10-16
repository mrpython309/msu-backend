<style>
    /* Sidebar and navbar stay at 100%. Ctrl +/- scales only this page's content. */
    html.content-zoom-lock,
    html.content-zoom-lock body {
        height: 100%;
        overflow: hidden !important;
    }
    html.content-zoom-lock .layout-wrapper,
    html.content-zoom-lock .layout-container {
        height: 100vh;
        max-height: 100vh;
        min-height: 0 !important;
        overflow: hidden;
    }
    html.content-zoom-lock .layout-page {
        height: 100vh;
        max-height: 100vh;
        min-height: 0 !important;
        overflow: hidden;
    }
    html.content-zoom-lock .content-wrapper {
        flex: 1 1 auto;
        min-height: 0 !important;
        overflow: hidden;
    }
    html.content-zoom-lock .content-wrapper > [class*="container-"] {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
    }
    #content-zoom-viewport {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
        overscroll-behavior: contain;
    }
    #content-zoom-target {
        width: 100%;
        position: relative;
    }
    /* Filter menus are attached to the page, outside the zoomed content, so they are not clipped. */
    body > .select2-container--open {
        z-index: 10050;
    }
</style>
