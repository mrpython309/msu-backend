@php $wrap = $wrap ?? true; @endphp
@if ($wrap)
  <div class="p-3 bg-white rounded" style="box-shadow: 0 1px 6px rgba(0,0,0,0.06);">
    <div class="input-group">
      <span class="input-group-text"><i class="bx bx-search"></i></span>
      <input type="text" class="form-control live-search-input" placeholder="Search..."
        data-target="{{ $target ?? '.table tbody' }}" value="{{ $value ?? request('q', '') }}" />
    </div>
  </div>
@else
  <div class="input-group">
    <span class="input-group-text"><i class="bx bx-search"></i></span>
    <input type="text" class="form-control live-search-input" placeholder="Search..."
      data-target="{{ $target ?? '.table tbody' }}" value="{{ $value ?? request('q', '') }}" />
  </div>
@endif

@push('scripts')
  <script>
    (function() {
      // Scoped live-search initializer. Each page can include this partial and pass a data-target selector.
      function initLiveSearch(container) {
        const input = container.querySelector('.live-search-input');
        if (!input) return;
        const tableSelector = input.dataset.target || '.table tbody';
        const table = document.querySelector(tableSelector);

        function debounce(fn, delay) {
          let t;
          return function() {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, arguments), delay);
          };
        }

        function liveFilterRows(query) {
          if (!table) return;
          const q = (query || '').toLowerCase().trim();
          table.querySelectorAll('tr').forEach(row => {
            if (row.querySelector('td[colspan]')) return;
            const text = row.innerText.toLowerCase();
            row.style.display = q === '' || text.indexOf(q) !== -1 ? '' : 'none';
          });
        }

        input.addEventListener('input', debounce(function(e) {
          liveFilterRows(e.target.value);
        }, 200));
      }

      // auto-init for any partials rendered on the page
      document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.live-search-input').forEach(el => initLiveSearch(el.closest('.input-group')));
      });
    })();
  </script>
@endpush
