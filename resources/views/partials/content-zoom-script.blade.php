<script>
(function () {
    var viewport = document.getElementById('content-zoom-viewport');
    var target = document.getElementById('content-zoom-target');
    if (!viewport || !target) return;

    document.documentElement.classList.add('content-zoom-lock');

    var zoomStorageKey = @json($zoomStorageKey ?? 'content-zoom');
    var contentZoom = parseFloat(sessionStorage.getItem(zoomStorageKey) || '1') || 1;
    var canUseZoom = 'zoom' in document.body.style;

    function clampZoom(next) {
        return Math.min(1.6, Math.max(0.5, Math.round(next * 10) / 10));
    }

    function closeSelect2() {
        if (!window.jQuery || typeof $.fn.select2 !== 'function') return;
        $('select.select2').each(function () {
            var instance = $(this).data('select2');
            if (instance && instance.isOpen && instance.isOpen()) {
                $(this).select2('close');
            }
        });
    }

    function setContentZoom(next) {
        contentZoom = clampZoom(next);
        if (canUseZoom) {
            target.style.zoom = contentZoom;
            target.style.transform = '';
            target.style.width = '100%';
        } else {
            target.style.transformOrigin = 'top left';
            target.style.transform = 'scale(' + contentZoom + ')';
            target.style.width = (100 / contentZoom) + '%';
        }
        sessionStorage.setItem(zoomStorageKey, String(contentZoom));
        closeSelect2();
    }

    setContentZoom(contentZoom);

    function onZoomKey(e) {
        if (!(e.ctrlKey || e.metaKey) || e.altKey) return;
        var isPlus = e.key === '+' || e.key === '=' || e.code === 'NumpadAdd' || e.code === 'Equal';
        var isMinus = e.key === '-' || e.key === '_' || e.code === 'NumpadSubtract' || e.code === 'Minus';
        var isReset = e.key === '0' || e.code === 'Digit0' || e.code === 'Numpad0';
        if (!isPlus && !isMinus && !isReset) return;
        e.preventDefault();
        e.stopPropagation();
        if (isPlus) setContentZoom(contentZoom + 0.1);
        else if (isMinus) setContentZoom(contentZoom - 0.1);
        else setContentZoom(1);
    }

    window.addEventListener('keydown', onZoomKey, true);

    window.addEventListener('wheel', function (e) {
        if (!(e.ctrlKey || e.metaKey)) return;
        e.preventDefault();
        setContentZoom(contentZoom + (e.deltaY < 0 ? 0.1 : -0.1));
    }, { passive: false, capture: true });

    viewport.addEventListener('scroll', closeSelect2, { passive: true });

    function placeSelect2Dropdown(dropdown, containerEl) {
        if (!containerEl || !dropdown.$dropdownContainer || !dropdown.$dropdown) return;
        var rect = containerEl.getBoundingClientRect();
        var width = Math.max(rect.width, 240);
        var left = rect.left;
        if (left + width > window.innerWidth - 8) {
            left = Math.max(8, window.innerWidth - width - 8);
        }
        dropdown.$dropdown.css({
            position: 'relative',
            left: 0,
            top: 0,
            width: '100%'
        });
        var dropdownHeight = dropdown.$dropdown.outerHeight(false) || 0;
        var spaceBelow = window.innerHeight - rect.bottom;
        var spaceAbove = rect.top;
        var top = rect.bottom;
        if (dropdownHeight > spaceBelow && spaceAbove > spaceBelow) {
            top = Math.max(8, rect.top - dropdownHeight);
            dropdown.$dropdown.removeClass('select2-dropdown--below').addClass('select2-dropdown--above');
        } else {
            dropdown.$dropdown.removeClass('select2-dropdown--above').addClass('select2-dropdown--below');
        }
        dropdown.$dropdownContainer.css({
            position: 'fixed',
            top: top,
            left: left,
            width: width,
            zIndex: 10050
        });
    }

    function patchSelect2Position(instance) {
        var dropdown = instance.dropdown;
        if (!dropdown || dropdown._contentZoomPatched || typeof dropdown._positionDropdown !== 'function') return;
        var containerEl = instance.$container && instance.$container[0];
        dropdown._positionDropdown = function () {
            placeSelect2Dropdown(this, containerEl);
        };
        dropdown._resizeDropdown = function () {
            if (this.$dropdown) {
                this.$dropdown.css({
                    position: 'relative',
                    left: 0,
                    top: 0,
                    width: '100%'
                });
            }
        };
        dropdown._contentZoomPatched = true;
    }

    $(document).ready(function () {
        if (typeof $.fn.select2 !== 'function') return;
        $('select.select2').each(function () {
            var $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                placeholder: $select.find('option[value=""]').first().text() || 'Select',
                allowClear: $select.find('option[value=""]').length > 0,
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownParent: $(document.body)
            });
            var instance = $select.data('select2');
            if (instance) patchSelect2Position(instance);
        });
    });
})();
</script>
