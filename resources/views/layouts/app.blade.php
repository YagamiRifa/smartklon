<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SmartKlon Mart</title>
    <meta name="description" content="Sistem Manajemen Stok Barang Berbasis UHF RFID — SmartKlon Mart">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
<div class="app-shell">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="brand-text">
                <span class="brand-name">SMARTKLON</span>
                <span class="brand-sub">MART</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-dashboard">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                        <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </span>
                <span class="nav-label">Dashboard</span>
            </a>

            <a href="{{ route('stock.index') }}" class="nav-item {{ request()->routeIs('stock.*') ? 'active' : '' }}" id="nav-stock">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" stroke="currentColor" stroke-width="2"/>
                        <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke="currentColor" stroke-width="2"/>
                        <line x1="12" y1="12" x2="12" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="10" y1="14" x2="14" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="nav-label">Ketersediaan Stok</span>
            </a>

            <a href="#" class="nav-item nav-item--soon" id="nav-rack">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <rect x="2" y="3" width="20" height="4" rx="1" stroke="currentColor" stroke-width="2"/>
                        <rect x="2" y="10" width="20" height="4" rx="1" stroke="currentColor" stroke-width="2"/>
                        <rect x="2" y="17" width="20" height="4" rx="1" stroke="currentColor" stroke-width="2"/>
                        <line x1="6" y1="7" x2="6" y2="10" stroke="currentColor" stroke-width="2"/>
                        <line x1="18" y1="7" x2="18" y2="10" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </span>
                <span class="nav-label">Monitoring Rak</span>
            </a>

            <a href="{{ route('expiry.index') }}" class="nav-item {{ request()->routeIs('expiry.*') ? 'active' : '' }}" id="nav-expiry">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <polyline points="12 6 12 12 16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="nav-label">Status Kedaluwarsa</span>
            </a>

            <a href="#" class="nav-item nav-item--soon" id="nav-log">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/>
                        <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2"/>
                        <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2"/>
                        <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </span>
                <span class="nav-label">Log Kejadian</span>
            </a>
        </nav>


        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="user-details">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-role">Administrator</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout" title="Logout" id="btn-logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <polyline points="16 17 21 12 16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- ===== MAIN CONTENT ===== --}}
    <main class="main-content" id="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
                    <span></span><span></span><span></span>
                </button>
                <div class="topbar-breadcrumb">
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                </div>
            </div>
            <div class="topbar-right">
                <div class="realtime-indicator" id="realtime-indicator">
                    <span class="indicator-dot"></span>
                    <span class="indicator-text">Real-time</span>
                </div>
                <div class="topbar-time" id="topbar-time"></div>
                {{-- ===== LONCENG NOTIFIKASI ===== --}}
                <div class="topbar-notification" id="notification-wrapper">
                    <button class="notification-btn" id="notification-btn" aria-label="Notifikasi">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{-- Hitung total gabungan notifikasi --}}
                        @php
                            $totalNotifikasi = ($globalExpiredCount ?? 0) + ($globalWarningCount ?? 0);
                        @endphp

                        {{-- Tampilkan lencana angka jika total notifikasi lebih dari 0 --}}
                        @if($totalNotifikasi > 0)
                            {{-- Menggunakan warna merah jika ada produk expired, selain itu warna amber --}}
                            <span class="notification-badge {{ ($globalExpiredCount ?? 0) > 0 ? 'notification-badge--red' : 'notification-badge--amber' }}">
                                {{ $totalNotifikasi }}
                            </span>
                        @endif
                    </button>

                    {{-- Isi Dropdown Notifikasi --}}
                    <div class="notification-dropdown" id="notification-dropdown">
                        <div class="notification-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 class="notification-title">Notifikasi Sistem</h3>

                            {{-- Tombol Bersihkan hanya muncul jika ada notifikasi aktif --}}
                            @if((($globalExpiredCount ?? 0) + ($globalWarningCount ?? 0)) > 0)
                                <button onclick="clearNotifications()" style="background: none; border: none; color: var(--primary-600); font-size: 11px; font-weight: 600; cursor: pointer; padding: 2px 6px; border-radius: 4px; transition: background 0.15s;">Bersihkan</button>
                            @endif
                        </div>
                        <div class="notification-body">
                            @if(($globalExpiredCount ?? 0) > 0)
                                <a href="{{ route('expiry.index') }}" class="notification-item notification-item--red">
                                    <div class="notification-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/><line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                                    </div>
                                    <div class="notification-text">
                                        <strong>{{ $globalExpiredCount }} Batch Telah Kedaluwarsa!</strong><br>
                                        Segera periksa dan tarik produk dari rak.
                                    </div>
                                </a>
                            @endif

                            @if(($globalWarningCount ?? 0) > 0)
                                <a href="{{ route('expiry.index') }}" class="notification-item notification-item--amber">
                                    <div class="notification-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2.5"/></svg>
                                    </div>
                                    <div class="notification-text">
                                        <strong>{{ $globalWarningCount }} Batch Mendekati Kedaluwarsa</strong><br>
                                        Memasuki periode H-14.
                                    </div>
                                </a>
                            @endif

                            @if(($globalExpiredCount ?? 0) == 0 && ($globalWarningCount ?? 0) == 0)
                                <div class="notification-empty">
                                    Belum ada notifikasi baru.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="content-area">
            @if(session('success'))
                <div class="alert alert--success" role="alert">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2"/><polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert--error" role="alert">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script>
    // Live clock
    function updateClock() {
        const el = document.getElementById('topbar-time');
        if (el) {
            const now = new Date();
            el.textContent = now.toLocaleString('id-ID', {
                weekday: 'short', day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
        }
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar--collapsed');
            mainContent.classList.toggle('main-content--expanded');
        });
    }

    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    });

    // Notification Dropdown Toggle
    const notifBtn = document.getElementById('notification-btn');
    const notifDropdown = document.getElementById('notification-dropdown');

    if(notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Mencegah klik bocor ke dokumen
            notifDropdown.classList.toggle('show');
        });

        // Menutup dropdown jika user mengklik area luar dropdown
        document.addEventListener('click', (e) => {
            if(!notifDropdown.contains(e.target)) {
                notifDropdown.classList.remove('show');
            }
        });
    }
async function clearNotifications() {
        try {
            const response = await fetch('{{ route("notifications.clear") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                // Muat ulang halaman agar angka di lencana lonceng langsung ter-update
                window.location.reload();
            }
        } catch (error) {
            console.error('Gagal membersihkan notifikasi:', error);
        }
    }

    //---------------------------------- Real-time Updates dengan Laravel Echo ----------------------------------
    // 1. Fungsi pembuat Toast agar bisa dipanggil dari mana saja
    function showGlobalToast(title, message) {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.cssText = 'position: fixed; top: 70px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.style.cssText = 'background: white; border-left: 4px solid #10B981; padding: 14px 18px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 12px; transform: translateX(120%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); min-width: 250px;';
        toast.innerHTML = `
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" style="color: #10B981; flex-shrink: 0;"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <div>
                <strong style="color: #111827; font-size: 13px; display: block; margin-bottom: 2px;">${title}</strong>
                <span style="color: #6B7280; font-size: 12px;">${message}</span>
            </div>
        `;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.style.transform = 'translateX(0)', 10);
        setTimeout(() => { toast.style.transform = 'translateX(120%)'; setTimeout(() => toast.remove(), 300); }, 5000); // Hilang otomatis setelah 5 detik
    }

    // // 2. Pendengar Reverb Global untuk Raspi (Scanner Expiry)
    // document.addEventListener('DOMContentLoaded', function () {
    //     if (window.Echo) {
    //         window.Echo.channel('scanner-channel')
    //             .listen('.batch.scanned', (e) => {
    //                 // Munculkan Toast di halaman APA PUN yang sedang dibuka
    //                 showGlobalToast('Scan Raspi Berhasil!', `Data masuk (Barcode: ${e.barcode})`);

    //                 // Sebarkan sinyal khusus ke halaman (berguna untuk update log di halaman Expiry)
    //                 window.dispatchEvent(new CustomEvent("global-batch-scanned", { detail: e }));
    //             });
    //     }
    // });

    // 2. Pendengar Reverb Global untuk Raspi (Scanner Expiry)
    // Menggunakan interval untuk menunggu Vite selesai memuat app.js (Echo)
    const checkEcho = setInterval(() => {
        if (window.Echo) {
            clearInterval(checkEcho); // Hentikan pengecekan setelah Echo siap

            window.Echo.channel('scanner-channel')
                .listen('.batch.scanned', (e) => {
                    // Munculkan Toast di halaman APA PUN yang sedang dibuka
                    showGlobalToast('Scan Berhasil!', `Batch ${e.nama_barang} berhasil ditambah.`);

                    // Sebarkan sinyal khusus ke halaman (berguna untuk update log di halaman Expiry)
                    window.dispatchEvent(new CustomEvent("global-batch-scanned", { detail: e }));
                });
        }
    }, 150); // Sistem akan mengecek ketersediaan Echo setiap 150 milidetik
</script>

<style>
/* ── Lonceng Notifikasi ── */
.topbar-notification { position: relative; display: flex; align-items: center; }
.notification-btn { background: none; border: none; color: var(--grey-500); cursor: pointer; padding: 6px; position: relative; border-radius: 50%; transition: all 0.2s; display: flex; align-items: center; justify-content: center;}
.notification-btn:hover { background: var(--grey-100); color: var(--grey-800); }
.notification-badge { position: absolute; top: 0; right: 0; border-radius: 50px; font-size: 9px; font-weight: 700; color: white; padding: 2px 5px; line-height: 1; border: 2px solid white; }
.notification-badge--red { background: #DC2626; }
.notification-badge--amber { background: #D97706; }

/* ── Dropdown Notifikasi ── */
.notification-dropdown { position: absolute; top: calc(100% + 10px); right: -10px; width: 320px; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: 1px solid var(--grey-200); opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s; z-index: 1000; }
.notification-dropdown.show { opacity: 1; visibility: visible; transform: translateY(0); }
.notification-header { padding: 14px 16px; border-bottom: 1px solid var(--grey-100); }
.notification-title { font-size: 13px; font-weight: 700; color: var(--grey-800); margin: 0; }
.notification-body { max-height: 300px; overflow-y: auto; }
.notification-item { display: flex; gap: 12px; padding: 14px 16px; text-decoration: none; border-bottom: 1px solid var(--grey-50); transition: background 0.2s; }
.notification-item:hover { background: var(--grey-50); }
.notification-item--red .notification-icon { color: #DC2626; background: #FEE2E2; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notification-item--amber .notification-icon { color: #D97706; background: #FEF3C7; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notification-text { font-size: 12px; color: var(--grey-500); line-height: 1.4; }
.notification-text strong { color: var(--grey-800); font-size: 13px; }
.notification-empty { padding: 24px 16px; text-align: center; font-size: 12px; color: var(--grey-400); }


.badge--amber { background:#FFFBEB; color:#F59E0B; border:1px solid #FDE68A; }
</style>
@stack('scripts')
</body>
</html>
