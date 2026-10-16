@php
  use Illuminate\Support\Facades\Route;

  $menuUser = auth()->user();

  $menuIsSuperAdmin = false;
  try {
      $menuIsSuperAdmin = (bool) $menuUser?->hasAnyRole(['superadmin', 'Super Admin']);
  } catch (\Throwable) {
      $menuIsSuperAdmin = false;
  }

  $menuCan = function (?string $permissionExpression) use ($menuUser, $menuIsSuperAdmin): bool {
      if ($menuIsSuperAdmin) {
          return true;
      }

      if (! $menuUser || $permissionExpression === null || trim($permissionExpression) === '') {
          return false;
      }

      $parts = array_values(array_filter(array_map('trim', explode('|', $permissionExpression))));

      return $parts !== [] && $menuUser->canAny($parts);
  };

  $menuChildVisible = function ($child) use ($menuUser, $menuCan, $menuIsSuperAdmin): bool {
      if (! $menuUser) {
          return false;
      }

      if ($menuIsSuperAdmin) {
          return true;
      }

      if (isset($child->role) && ! empty($child->role)) {
          $roles = array_filter(array_map('trim', explode('|', (string) $child->role)));

          return $roles !== [] && $menuUser->hasAnyRole($roles);
      }

      if (isset($child->permission) && ! empty($child->permission)) {
          return $menuCan((string) $child->permission);
      }

      if (isset($child->slug) && ! empty($child->slug) && ! is_array($child->slug)) {
          return $menuCan(str_replace('/', '.', (string) $child->slug).':view');
      }

      return false;
  };
@endphp

<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)
      @php
        $canView = false;

        if (isset($submenu->role) && ! empty($submenu->role)) {
            $roles = array_filter(array_map('trim', explode('|', (string) $submenu->role)));
            $canView = $roles !== [] && $menuUser && $menuUser->hasAnyRole($roles);
        } elseif ($menuIsSuperAdmin) {
            $canView = true;
        } elseif (isset($submenu->permission) && ! empty($submenu->permission)) {
            $canView = $menuCan((string) $submenu->permission);
        } elseif (isset($submenu->slug) && ! empty($submenu->slug)) {
            if (is_array($submenu->slug)) {
                foreach ($submenu->slug as $slug) {
                    if ($menuCan(str_replace('/', '.', $slug).':view')) {
                        $canView = true;
                        break;
                    }
                }
            } else {
                $canView = $menuCan(str_replace('/', '.', (string) $submenu->slug).':view');
            }
        }

        $hasVisibleChild = false;
        if (isset($submenu->submenu) && is_array($submenu->submenu)) {
            foreach ($submenu->submenu as $child) {
                if ($menuChildVisible($child)) {
                    $hasVisibleChild = true;
                    break;
                }
            }
        }
      @endphp

      @if (! $canView && ! $hasVisibleChild)
        @continue
      @endif

      @php
        $activeClass = '';
        $currentRouteName = Route::currentRouteName();
        $slugs = isset($submenu->slug)
            ? (is_array($submenu->slug) ? $submenu->slug : [$submenu->slug])
            : [];

        foreach ($slugs as $slug) {
            if ($currentRouteName === $slug || str_starts_with((string) $currentRouteName, $slug)) {
                $activeClass = 'active';
                break;
            }
        }

        if (empty($activeClass) && isset($submenu->submenu)) {
            foreach ($submenu->submenu as $child) {
                if (isset($child->slug) && ($currentRouteName === $child->slug || str_starts_with((string) $currentRouteName, $child->slug))) {
                    $activeClass = 'active open';
                    break;
                }
            }
        }
      @endphp

      <li class="menu-item {{ $activeClass }}">
        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}"
          class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
          @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
          @if (isset($submenu->icon))
            <i class="{{ $submenu->icon }}"></i>
          @endif
          <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
          @isset($submenu->badge)
            <div class="badge rounded-pill bg-{{ $submenu->badge[0] }} text-uppercase ms-auto">{{ $submenu->badge[1] }}
            </div>
          @endisset
        </a>

        @if (isset($submenu->submenu))
          @include('layouts.sections.menu.submenu', ['menu' => $submenu->submenu])
        @endif
      </li>
    @endforeach
  @endif
</ul>
