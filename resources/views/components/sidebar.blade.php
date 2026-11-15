@php
    use App\Enums\Role;
    $user = auth()->user();
    $role = $user?->role instanceof Role ? $user->role : Role::tryFrom($user?->role ?? '');
@endphp

@persist('sidebar')
<aside id="main-sidebar" class="sidebar">
    <div class="sidebar-header">
        <a href="{{ url('/') }}" class="sidebar-brand" wire:navigate>
            <x-icon name="briefcase" :size="22" />
            <span>Biro Humas</span>
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
            <button type="submit" class="btn-logout">
                <x-icon name="logout" :size="16" />
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
@endpersist

<div id="sidebar-backdrop" class="sidebar-backdrop" onclick="toggleSidebar(false)"></div>

<script>
    function updateActivePill() {
        const nav = document.querySelector('.sidebar-nav');
        const pill = document.querySelector('.sidebar-active-pill');
        if (!nav || !pill) return;

        const currentPath = window.location.pathname;
        const links = nav.querySelectorAll('.sidebar-link');
        let activeLink = null;

        links.forEach(link => {
            const linkUrl = new URL(link.href, window.location.origin);
            if (currentPath === linkUrl.pathname || (linkUrl.pathname !== '/' && currentPath.startsWith(linkUrl.pathname))) {
                activeLink = link;
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });

        if (activeLink) {
            const navRect = nav.getBoundingClientRect();
            const linkRect = activeLink.getBoundingClientRect();
            const topOffset = linkRect.top - navRect.top + nav.scrollTop;
            
            pill.style.opacity = '1';
            pill.style.height = `${linkRect.height}px`;
            pill.style.transform = `translateY(${topOffset}px)`;
        } else {
            pill.style.opacity = '0';
        }
    }

    function toggleSidebar(open) {
        const sidebar = document.getElementById('main-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar || !backdrop) return;

        if (typeof open === 'boolean') {
            if (open) {
                sidebar.classList.add('open');
                backdrop.classList.add('active');
            } else {
                sidebar.classList.remove('open');
                backdrop.classList.remove('active');
            }
        } else {
            sidebar.classList.toggle('open');
            backdrop.classList.toggle('active');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateActivePill();
        // Tutup drawer mobile saat link diklik
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    toggleSidebar(false);
                }
            });
        });
    });

    document.addEventListener('livewire:navigated', () => {
        updateActivePill();
        if (window.innerWidth < 1024) {
            toggleSidebar(false);
        }
    });

    window.addEventListener('resize', updateActivePill);
</script>
