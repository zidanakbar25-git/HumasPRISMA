@php
    use App\Enums\Role;
    $user = auth()->user();
    $role = $user?->role instanceof Role ? $user->role : Role::tryFrom($user?->role ?? '');
@endphp

@persist('sidebar')
<aside id="main-sidebar" class="sidebar">
    <div class="sidebar-header">
        <a href="{{ url('/') }}" class="sidebar-brand" wire:navigate>
            <div style="width: 36px; height: 36px; background-color: #556B2F; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo Biro Humas" style="width: 22px; height: 22px; object-fit: contain;">
            </div>
            <span>PRISMA</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <!-- Pill Penanda Menu Aktif (Animasi Gerak 300ms) -->
        <div class="sidebar-active-pill" style="opacity: 0;"></div>

        @if($role === Role::Kabag)
            {{-- Menu Kabag --}}
            <a href="{{ route('kabag.jadwal-kegiatan.index') }}" 
               class="sidebar-link {{ request()->routeIs('kabag.jadwal-kegiatan.*') ? 'active' : '' }}" 
               wire:navigate>
                <x-icon name="calendar" :size="19" />
                <span>Jadwal Kegiatan</span>
            </a>

            <a href="{{ route('kabag.tugas.index') }}" 
               class="sidebar-link {{ request()->routeIs('kabag.tugas.*') ? 'active' : '' }}" 
               wire:navigate>
                <x-icon name="tugas" :size="19" />
                <span>Tugas</span>
            </a>

            <a href="{{ route('kabag.manajemen-user.index') }}" 
               class="sidebar-link {{ request()->routeIs('kabag.manajemen-user.*') ? 'active' : '' }}" 
               wire:navigate>
                <x-icon name="users" :size="19" />
                <span>Manajemen User</span>
            </a>

            <a href="{{ route('kabag.backup-db.index') }}" 
               class="sidebar-link {{ request()->routeIs('kabag.backup-db.*') ? 'active' : '' }}" 
               wire:navigate>
                <x-icon name="database" :size="19" />
                <span>Backup Database</span>
            </a>
        @else
            {{-- Menu Staff & Intern --}}
            <a href="{{ route('staff.jadwal-kegiatan.index') }}" 
               class="sidebar-link {{ request()->routeIs('staff.jadwal-kegiatan.*') ? 'active' : '' }}" 
               wire:navigate>
                <x-icon name="calendar" :size="19" />
                <span>Jadwal Kegiatan</span>
            </a>

            <a href="{{ route('staff.tugas.index') }}" 
               class="sidebar-link {{ request()->routeIs('staff.tugas.*') ? 'active' : '' }}" 
               wire:navigate>
                <x-icon name="tugas" :size="19" />
                <span>Tugas</span>
            </a>

            <a href="{{ route('staff.manajemen-akun.index') }}" 
               class="sidebar-link {{ request()->routeIs('staff.manajemen-akun.*') ? 'active' : '' }}" 
               wire:navigate>
                <x-icon name="user" :size="19" />
                <span>Akun Saya</span>
            </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user-card">
            <div class="sidebar-avatar">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name" title="{{ $user->name ?? '' }}">{{ $user->name ?? 'User' }}</span>
                <span class="sidebar-user-meta">&#64;{{ $user->username ?? '' }} &bull; {{ $role?->label() ?? 'Anggota' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <input type="hidden" name="role" value="{{ $role?->value ?? '' }}">
            <button type="submit" class="btn-logout">
                <x-icon name="logout" :size="16" />
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
@endpersist

<div id="sidebar-backdrop" class="sidebar-backdrop" onclick="toggleSidebar(false)"></div>

<script data-navigate-once>
    function updateActivePill(instant = false) {
        const nav = document.querySelector('.sidebar-nav');
        const pill = document.querySelector('.sidebar-active-pill');
        if (!nav || !pill) return;

        const currentPath = window.location.pathname;
        let activeLink = null;

        nav.querySelectorAll('.sidebar-link').forEach(link => {
            const linkUrl = new URL(link.href, window.location.origin);
            const isActive = currentPath === linkUrl.pathname ||
                (linkUrl.pathname !== '/' && currentPath.startsWith(linkUrl.pathname));
            link.classList.toggle('active', isActive);
            if (isActive) activeLink = link;
        });

        if (!activeLink) {
            pill.style.opacity = '0';
            return;
        }

        const navRect = nav.getBoundingClientRect();
        const linkRect = activeLink.getBoundingClientRect();
        const x = linkRect.left - navRect.left - nav.clientLeft + nav.scrollLeft;
        const y = linkRect.top - navRect.top - nav.clientTop + nav.scrollTop;

        if (instant) pill.classList.add('no-anim');

        pill.style.opacity = '1';
        pill.style.width = `${linkRect.width}px`;
        pill.style.height = `${linkRect.height}px`;
        pill.style.transform = `translate(${x}px, ${y}px)`;

        if (instant) {
            void pill.offsetHeight;
            pill.classList.remove('no-anim');
        }
    }

    function toggleSidebar(open) {
        const sidebar = document.getElementById('main-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar || !backdrop) return;

        if (typeof open === 'boolean') {
            sidebar.classList.toggle('open', open);
            backdrop.classList.toggle('active', open);
        } else {
            sidebar.classList.toggle('open');
            backdrop.classList.toggle('active');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateActivePill(true);
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) toggleSidebar(false);
            });
        });
    });

    window.addEventListener('load', () => updateActivePill(true));
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => updateActivePill(true));
    }

    document.addEventListener('livewire:navigated', () => {
        updateActivePill();
        if (window.innerWidth < 1024) toggleSidebar(false);
    });

    window.addEventListener('resize', () => updateActivePill(true));
</script>