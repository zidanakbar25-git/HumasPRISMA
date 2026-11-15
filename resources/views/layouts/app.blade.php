<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Biro Humas') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Murni (Tanpa Framework) Sesuai UI_GUIDE -->
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">

    @livewireStyles
    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        {{-- Sidebar Bersama Sesuai Role Pengguna --}}
        <x-sidebar />

        <div class="main-area">
            {{-- Topbar Khusus Layar Tablet & HP (< 1024px) --}}
            <header class="mobile-topbar">
                <button type="button" class="btn-hamburger" onclick="toggleSidebar()" aria-label="Buka Menu">
                    <x-icon name="menu" :size="24" />
                </button>

                <div class="mobile-brand">
                    <span>Biro Humas</span>
                </div>

                <div style="width: 40px; display: flex; justify-content: flex-end;">
                    <div class="sidebar-avatar" style="width: 32px; height: 32px; font-size: 0.8125rem;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                </div>
            </header>

            {{-- Area Konten Utama --}}
            <main class="content-container">
                @if(session('success'))
                    <div class="alert alert-success">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
        </div>
    </div>

    {{-- Container Toast Global (Livewire Dispatch Toast) --}}
    <div x-data="{
        toasts: [],
        addToast(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, type });
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 3500);
        }
    }" 
    @toast.window="addToast($event.detail.message, $event.detail.type || 'success')" 
    class="toast-container">
        <template x-for="t in toasts" :key="t.id">
            <div class="toast-item" :class="t.type === 'error' ? 'toast-error' : 'toast-success'">
                <span x-text="t.message"></span>
            </div>
        </template>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
