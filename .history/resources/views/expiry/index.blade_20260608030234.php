@extends('layouts.app')

@section('title', 'Status Kedaluwarsa')
@section('page-title', 'Status Kedaluwarsa')

@section('content')

{{-- ================================================================
     STAT CARDS — Status Batch Expiry
     ================================================================ --}}
<div class="stats-grid" id="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon stat-card-icon--blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" stroke="currentColor" stroke-width="2"/><line x1="7" y1="7" x2="7.01" y2="7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
        </div>
        <div class="stat-card-body">
            <span class="stat-card-value" id="total-batch-count">{{ $totalBatch ?? 0 }}</span>
            <span class="stat-card-label">Total Batch Terdaftar</span>
        </div>
        <div class="stat-card-trend stat-card-trend--neutral">Semua Produk</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon stat-card-icon--green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="stat-card-body">
            <span class="stat-card-value" id="safe-batch-count">{{ $safeBatch ?? 0 }}</span>
            <span class="stat-card-label">Aman</span>
        </div>
        <div class="stat-card-trend stat-card-trend--up">Exp > 14 Hari</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon stat-card-icon--amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
        </div>
        <div class="stat-card-body">
            <span class="stat-card-value" id="warning-batch-count">{{ $warningBatch ?? 0 }}</span>
            <span class="stat-card-label">Mendekati Kedaluwarsa</span>
        </div>
        <div class="stat-card-trend" style="color:#F59E0B;">H-14</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon stat-card-icon--red">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div class="stat-card-body">
            <span class="stat-card-value" id="expired-batch-count">{{ $expiredBatch ?? 0 }}</span>
            <span class="stat-card-label">Expired</span>
        </div>
        <div class="stat-card-trend stat-card-trend--down">Telah Lewat</div>
    </div>
</div>

{{-- ================================================================
     MODAL TAMBAH BATCH MANUAL
     ================================================================ --}}
<div id="add-batch-modal-overlay" class="detail-modal-overlay" style="display:none;" onclick="closeAddBatchModal(event)">
    <div class="detail-modal" style="width:420px" role="dialog" aria-modal="true">
        <div class="detail-modal-header">
            <div class="detail-modal-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" stroke="currentColor" stroke-width="2"/></svg>
            </div>
            <div style="flex:1;min-width:0;">
                <h3 class="detail-modal-title">Tambah Batch Manual</h3>
            </div>
            <button class="batch-modal-close" onclick="closeAddBatchModal()" type="button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2"/></svg>
            </button>
        </div>

        <div class="detail-modal-body">
            <form action="{{ route('expiry.store') }}" method="POST" id="form-add-batch" onsubmit="submitManualBatch(event)">
                @csrf
                <div class="form-group mb-3" style="position: relative;">
                    <label class="form-label" for="search_product">PILIH PRODUK TARGET</label>

                    {{-- Input Teks yang Menjadi Pencarian Sekaligus Penampil Hasil --}}
                    <input type="hidden" name="item_id" id="modal_item_id" required>
                    <input type="text" id="search_product" class="form-input" placeholder="Ketik kode, nama, atau barcode..." style="width:100%; height:38px;" onkeyup="filterCustomDropdown()" onclick="openCustomDropdown()" autocomplete="off" required>

                    {{-- Daftar Opsi Custom (Melayang di bawah input) --}}
                    <div id="custom_options_container" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; border:1px solid var(--grey-200); border-radius:8px; margin-top:4px; max-height:200px; overflow-y:auto; z-index:9999; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                        @foreach ($items ?? [] as $item)
                            <div class="custom-option"
                                 data-value="{{ $item->id }}"
                                 data-label="{{ $item->kode_barang }} - {{ $item->nama_barang }}"
                                 data-search="{{ strtolower($item->kode_barang . ' ' . $item->nama_barang . ' ' . ($item->barcode ?? '')) }}"
                                 onclick="selectCustomOption(this)">
                                {{ $item->kode_barang }} - <span style="font-weight: 500;">{{ $item->nama_barang }}</span>
                            </div>
                        @endforeach

                        <div id="custom_option_empty" style="display:none; padding:10px 12px; font-size:13px; color:var(--grey-400); text-align:center;">
                            Produk tidak ditemukan
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label" for="modal_expiry_date">TANGGAL KEDALUWARSA</label>
                    <input type="date" name="expiry_date" id="modal_expiry_date" class="form-input" style="width:100%;" required>
                </div>
            </form>
        </div>
        <div class="detail-modal-footer">
            <button class="btn btn--secondary" onclick="closeAddBatchModal()" type="button">Batal</button>
            <button type="submit" form="form-add-batch" class="btn btn--primary">
                Simpan Batch Expiry
            </button>
        </div>
    </div>
</div>

{{-- ================================================================
     ROW 2: PANEL FORM (kiri) + LOG SCAN (kanan)
     ================================================================ --}}
<div class="stock-control-row">

    {{-- ===== PANEL FORM (Kiri) ===== --}}
    <div class="card stock-control-panel">

        {{-- Bagian 1: Tambah Batch Manual --}}
        <div class="card-header">
            <div class="card-header-left">
                <h2 class="card-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right:4px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="16" stroke="currentColor" stroke-width="2"/><line x1="8" y1="12" x2="16" y2="12" stroke="currentColor" stroke-width="2"/></svg>
                    Aksi Manajemen
                </h2>
            </div>
        </div>
        <div class="card-body">
            {{-- Tombol pemicu Modal Tambah Batch --}}
            <button class="btn btn--primary" style="width:100%; justify-content:center; padding:10px 0; font-size:14px;" onclick="openAddBatchModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Tambah Batch Expiry
            </button>
        </div>

        {{-- Bagian 2 (Collapsible): Tambah Produk Baru ke Katalog --}}
        <div class="panel-divider"></div>
        <div class="card-header" style="cursor:pointer;padding:14px 20px;" onclick="toggleAddForm()">
            <div class="card-header-left">
                <h2 class="card-title" style="font-size:13px;font-weight:600;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:4px;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" stroke="currentColor" stroke-width="2"/></svg>
                    Tambah Produk ke Katalog
                </h2>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" id="toggle-add-icon" style="transition:transform .25s;flex-shrink:0"><polyline points="6 9 12 15 18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div class="card-body" id="add-form-body" style="display:none;padding-top:0.5%;">
            <p class="text-sm text-muted" style="margin-bottom:12px;">Gunakan jika produk belum ada di pilihan atas.</p>
            <form method="POST" action="{{ route('stock.items.store') }}">
                @csrf
                <div class="form-grid form-grid--2">
                    <div class="form-group">
                        <label class="form-label" for="kode_barang">KODE BARANG</label>
                        <input type="text" id="kode_barang" name="kode_barang" class="form-input {{ $errors->has('kode_barang') ? 'form-input--error' : '' }}" placeholder="BRG-001" value="{{ old('kode_barang') }}" required>
                        @error('kode_barang')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="barcode">BARCODE</label>
                        <input type="text" id="barcode" name="barcode" class="form-input {{ $errors->has('barcode') ? 'form-input--error' : '' }}" placeholder="1234567890" value="{{ old('barcode') }}">
                        @error('barcode')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="nama_barang">NAMA BARANG</label>
                        <input type="text" id="nama_barang" name="nama_barang" class="form-input {{ $errors->has('nama_barang') ? 'form-input--error' : '' }}" placeholder="Nama produk" value="{{ old('nama_barang') }}" required>
                        @error('nama_barang')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="satuan">SATUAN</label>
                        <input type="text" id="satuan" name="satuan" class="form-input" placeholder="PCS" value="{{ old('satuan', 'PCS') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="deskripsi">DESKRIPSI</label>
                        <input type="text" id="deskripsi" name="deskripsi" class="form-input" placeholder="Opsional" value="{{ old('deskripsi') }}">
                    </div>
                </div>
                <div class="form-actions" style="margin-top:15px;">
                    <button type="submit" class="btn btn--primary" style="font-size:13px; width:100%; justify-content:center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== LOG SCAN REAL-TIME DARI RASPI (Kanan) ===== --}}
    <div class="card stock-log-panel">
        <div class="card-header">
            <div class="card-header-left">
                <h2 class="card-title">
                    <span class="pulse-dot pulse-dot--green" id="log-pulse"></span>
                    Log Scan Pemindai
                </h2>
                <span class="badge badge--live" id="log-live-badge" style="display:none;">LIVE</span>
                <span class="badge badge--offline" id="log-offline-badge" style="display:none;">OFFLINE</span>
            </div>
            <button onclick="clearScanLog()" style="font-size:11px;padding:3px 8px;border:1px solid var(--grey-200);border-radius:5px;background:#fff;cursor:pointer;color:var(--grey-500);">Bersihkan</button>
        </div>
        <div class="card-body" style="padding:12px 16px;flex:1;">
            <div id="batch-scan-log" class="stock-scan-log">
                <div class="realtime-empty" id="log-empty-state">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" opacity="0.25"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><path d="M12 8v4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <span id="log-empty-text">Menunggu koneksi pemindai…</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ================================================================
     MODAL DETAIL BATCH PRODUK
     ================================================================ --}}
<div id="expiry-detail-modal-overlay" class="detail-modal-overlay" style="display:none;" onclick="closeExpiryDetailModal(event)">
    <div class="detail-modal" style="width:600px" role="dialog" aria-modal="true">
        <div class="detail-modal-header">
            <div class="detail-modal-icon" id="detail-modal-icon--edit">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            </div>
            <div style="flex:1;min-width:0;">
                <h3 class="detail-modal-title" id="modal-nama-produk">—</h3>
                <div style="display:flex;align-items:center;gap:8px;margin-top:3px;">
                    <span class="badge badge--code" style="font-size:11px;" id="modal-kode-produk">—</span>
                    <span class="detail-modal-barcode-badge" id="modal-barcode-produk">—</span>
                    <span class="detail-modal-satuan-badge" id="modal-satuan-produk">—</span>
                </div>
            </div>
            <button class="batch-modal-close" onclick="closeExpiryDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2"/></svg>
            </button>
        </div>

        {{-- Bagian Statistik 3 Kolom --}}
        <div class="detail-modal-stats">
            <div class="detail-stat-box">
                <span class="detail-stat-val detail-stat--green" id="modal-stat-aman">—</span>
                <span class="detail-stat-label">Aman</span>
            </div>
            <div class="detail-stat-divider"></div>
            <div class="detail-stat-box">
                <span class="detail-stat-val" style="color:#F59E0B;" id="modal-stat-warning">—</span>
                <span class="detail-stat-label">Mendekati Expired</span>
            </div>
            <div class="detail-stat-divider"></div>
            <div class="detail-stat-box">
                <span class="detail-stat-val detail-stat--red" id="modal-stat-expired">—</span>
                <span class="detail-stat-label">Expired</span>
            </div>
        </div>

        <div class="detail-modal-body">
             <div class="detail-desc-label" style="margin-bottom:10px;">Daftar Batch Terdaftar</div>
             <div style="max-height: 250px; overflow-y: auto; border: 1px solid var(--grey-200); border-radius: 8px;">
                 <table class="data-table" style="margin: 0; width: 100%;">
                     <thead style="position: sticky; top: 0; background: white; z-index: 1; box-shadow: 0 1px 0 var(--grey-200);">
                         <tr>
                             <th style="padding:10px 12px; font-size:10px; color:var(--grey-500); text-transform:uppercase;">Batch Code</th>
                             <th style="padding:10px 12px; font-size:10px; color:var(--grey-500); text-transform:uppercase;">Tanggal Kedaluwarsa</th>
                             <th style="padding:10px 12px; font-size:10px; color:var(--grey-500); text-transform:uppercase; text-align:center;">Status</th>
                             <th style="padding:10px 12px; font-size:10px; color:var(--grey-500); text-transform:uppercase; text-align:center;">Aksi</th>
                         </tr>
                     </thead>
                     <tbody id="modal-batch-list">
                         <tr><td colspan="3" style="text-align:center; padding:32px;">Memuat data...</td></tr>
                     </tbody>
                 </table>
             </div>
        </div>
        <div class="detail-modal-footer">
            <button class="btn btn--secondary" onclick="closeExpiryDetailModal()">Tutup</button>
            <button class="btn btn--primary" id="modal-btn-add">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:4px;"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Tambah Batch
            </button>
        </div>
    </div>
</div>

{{-- ===== MODAL EDIT TANGGAL EXPIRED ===== --}}
<div id="editExpiryModal" class="custom-modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 24px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--grey-100); padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--grey-800);">Edit Tanggal Kedaluwarsa</h3>
            <button onclick="closeEditModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--grey-400);">&times;</button>
        </div>

        <form id="formEditExpiry" onsubmit="submitEditForm(event)">
            @csrf
            <input type="hidden" id="edit_batch_id">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--grey-600); margin-bottom: 6px;">Nama Barang</label>
                <input type="text" id="edit_nama_barang" readonly style="width: 100%; padding: 10px; border: 1px solid var(--grey-200); border-radius: 8px; background: var(--grey-50); color: var(--grey-500); font-size: 13px; outline: none;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--grey-600); margin-bottom: 6px;">Tanggal Kedaluwarsa Baru</label>
                <input type="date" id="edit_expiry_date" required style="width: 100%; padding: 10px; border: 1px solid var(--grey-200); border-radius: 8px; font-size: 13px; outline: none; box-sizing: border-box;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--grey-100); padding-top: 14px;">
                <button type="button" onclick="closeEditModal()" style="background: var(--grey-100); color: var(--grey-700); border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500;">Batal</button>
                <button type="submit" style="background: var(--primary-600); color: white; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================
     MODAL EDIT KATALOG PRODUK
     ================================================================ --}}
<div id="edit-product-modal-overlay" class="detail-modal-overlay" style="display:none;" onclick="closeEditProductModal(event)">
    <div class="detail-modal" style="width:480px" role="dialog" aria-modal="true">
        <div class="detail-modal-header">
            <div class="detail-modal-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div style="flex:1;min-width:0;">
                <h3 class="detail-modal-title">Edit Katalog Produk</h3>
            </div>
            <button class="batch-modal-close" onclick="closeEditProductModal()" type="button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2"/></svg>
            </button>
        </div>

        <div class="detail-modal-body">
            <form method="POST" action="" id="form-edit-product">
                @csrf
                @method('PUT') {{-- Wajib untuk update data di Laravel --}}

                <div class="form-grid form-grid--2">
                    <div class="form-group">
                        <label class="form-label" for="edit_kode_barang">KODE BARANG</label>
                        <input type="text" id="edit_kode_barang" name="kode_barang" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_barcode">BARCODE</label>
                        <input type="text" id="edit_barcode" name="barcode" class="form-input">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label" for="edit_nama_barang">NAMA BARANG</label>
                        <input type="text" id="edit_nama_barang" name="nama_barang" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_satuan">SATUAN</label>
                        <input type="text" id="edit_satuan" name="satuan" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_deskripsi">DESKRIPSI</label>
                        <input type="text" id="edit_deskripsi" name="deskripsi" class="form-input">
                    </div>
                </div>
            </form>
        </div>
        <div class="detail-modal-footer">
            <button class="btn btn--secondary" onclick="closeEditProductModal()" type="button">Batal</button>
            <button type="submit" form="form-edit-product" class="btn btn--primary">
                Simpan Perubahan
            </button>
        </div>
    </div>
</div>

{{-- ================================================================
     TABEL EXPIRY BATCH PRODUK
     ================================================================ --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-left">
            <h2 class="card-title">Daftar Produk</h2>
        </div>
        <div class="stock-table-filters">
            {{-- Search --}}
            <div class="search-wrapper">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><line x1="21" y1="21" x2="16.65" y2="16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" class="search-input" id="expiry-search" placeholder="Cari nama atau kode…" oninput="applyFilters()">
            </div>

            {{-- Filter dropdown --}}
            <select class="filter-select" id="expiry-filter" onchange="applyFilters()">
                <option value="all">Semua</option>
                <option value="has_expired">Expired</option>
                <option value="has_warning">Pre-Expired</option>
                <option value="has_safe">Aman</option>
            </select>

            {{-- Sort dropdown --}}
            <select class="filter-select" id="expiry-sort" onchange="applyFilters()">
                <option value="name_asc">Nama A–Z</option>
                <option value="name_desc">Nama Z–A</option>
                <option value="date_asc">Date ↑</option>
                <option value="date_desc">Date ↓</option>
            </select>
        </div>
    </div>
    <div class="card-body card-body--no-padding">
        <table class="data-table" id="expiry-table">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th style="text-align:center;">KODE</th>
                    <th>BARCODE</th>
                    <th>NAMA BARANG</th>
                    <th>SATUAN</th>
                    <th style="text-align:center;">TOTAL</th>
                    <th style="text-align:center;">AMAN</th>
                    <th style="text-align:center;">PRE-EXPIRED</th>
                    <th style="text-align:center;">EXPIRED</th>
                    <th style="width:100px;text-align:center;">AKSI</th>
                </tr>
            </thead>
            <tbody id="expiry-table-body">
                @forelse($items ?? [] as $item)
                <tr class="stock-row" id="row-{{ $item->id }}"
                    data-item-id="{{ $item->id }}"
                    data-name="{{ $item->nama_barang }}"
                    data-kode="{{ $item->kode_barang }}"
                    data-barcode="{{ $item->barcode }}"
                    data-satuan="{{ $item->satuan ?? 'PCS' }}"
                    data-deskripsi="{{ $item->deskripsi }}"
                    data-total="{{ $item->batchExpiries->count() ?? 0 }}"
                    data-safe="{{ $item->safe_count }}"
                    data-warning="{{ $item->warning_count }}"
                    data-expired="{{ $item->expired_count }}"
                    data-earliest-date="{{ $item->earliest_date }}">
                    <td style="padding:12px 16px;">
                        <button class="expand-btn" onclick="toggleBatchList({{ $item->id }})" id="expand-btn-{{ $item->id }}">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" class="expand-icon" id="expand-icon-{{ $item->id }}">
                                <polyline points="6 9 12 15 18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </td>
                    <td style="padding:12px 16px;"><span class="badge badge--code" style="font-size:12px;">{{ $item->kode_barang }}</span></td>

                    <td style="font-family: monospace; color: var(--grey-600); padding:12px 16px; font-size:12px;">
                        {{ $item->barcode ?? '-' }}
                    </td>

                    <td class="font-medium" style="padding:12px 16px; font-size:14px; font-weight:500;">{{ $item->nama_barang }}</td>
                    <td style="padding:12px 16px; font-size:13px; color:var(--grey-500);">{{ $item->satuan ?? 'PCS' }}</td>
                    <td style="padding:12px 16px; text-align:center;"><span class="count-pill" style="font-size:12px; font-weight:600; padding:2px 8px; border-radius:12px;">{{ $item->batchExpiries->count() ?? 0 }}</span></td>

                    <td style="padding:12px 16px; text-align:center;"><span class="count-pill count-pill--green" style="font-size:12px; font-weight:600; padding:2px 8px; border-radius:12px;">{{ $item->safe_count }}</span></td>
                    <td style="padding:12px 16px; text-align:center;"><span class="count-pill count-pill--amber" style="font-size:12px; font-weight:600; padding:2px 8px; border-radius:12px;">{{ $item->warning_count }}</span></td>
                    <td style="padding:12px 16px; text-align:center;"><span class="count-pill count-pill--red" style="font-size:12px; font-weight:600; padding:2px 8px; border-radius:12px;">{{ $item->expired_count }}</span></td>
                    <td style="padding:12px 16px; text-align:center;">
                        <button class="btn-row-icon btn-row-icon--detail" onclick="openExpiryDetailModal({{ $item->id }})" title="Lihat detail batch">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        </button>
                        {{-- Tombol Edit Produk (Pensil) --}}
                        <button class="btn-row-icon btn-row-icon--edit" onclick="openEditProductModal({{ $item->id }})" title="Edit Katalog Produk">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </td>

                </tr>
                <tr class="tag-list-row" id="batches-{{ $item->id }}" style="display:none; background-color: #f8fafc; margin: auto;">
                    <td colspan="10" class="tag-list-cell" style="padding: 16px;">
                        <div class="tag-list-inner" id="batch-list-inner-{{ $item->id }}">
                            <div class="tag-list-loading" style="display:flex; align-items:center; gap:8px; color:var(--grey-500);"><div class="spinner" style="width:16px; height:16px; border:2px solid var(--grey-200); border-top-color:var(--primary-500); border-radius:50%; animation:spin 1s linear infinite;"></div><span style="font-size:13px;">Memuat data batch…</span></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted" style="padding:32px; text-align:center; color:var(--grey-500); font-size:14px;">Belum ada produk atau data batch.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ==============================================================
// 0. FUNGSI UNTUK PANEL COLLAPSIBLE
// ==============================================================
function toggleAddForm() {
    const body    = document.getElementById('add-form-body');
    const icon    = document.getElementById('toggle-add-icon');
    const visible = body.style.display !== 'none';

    body.style.display   = visible ? 'none' : 'block';
    icon.style.transform = visible ? '' : 'rotate(180deg)';
}

@if($errors->any())
    toggleAddForm();
@endif
// ==============================================================
// PEMANTAU STATUS KONEKSI REVERB (LIVE / OFFLINE)
// ==============================================================
let wsConnected = false;

window.addEventListener('reverb-connected', () => {
    wsConnected = true;
    document.getElementById('log-live-badge').style.display    = 'inline-block';
    document.getElementById('log-offline-badge').style.display = 'none';

    const emptyText = document.getElementById('log-empty-text');
    if (emptyText) emptyText.textContent = 'Menunggu scan dari pemindai...';

    const pulse = document.getElementById('log-pulse');
    if (pulse) {
        pulse.classList.add('pulse-dot--green');
        pulse.style.background = ''; // Kembalikan ke warna CSS asal
    }
});

window.addEventListener('reverb-disconnected', () => {
    wsConnected = false;
    document.getElementById('log-live-badge').style.display    = 'none';
    document.getElementById('log-offline-badge').style.display = 'inline-block';

    const emptyText = document.getElementById('log-empty-text');
    if (emptyText) emptyText.textContent = 'WebSocket terputus. Periksa koneksi Reverb.';

    const pulse = document.getElementById('log-pulse');
    if (pulse) {
        pulse.classList.remove('pulse-dot--green');
        pulse.style.background = 'var(--grey-400)'; // Matikan lampu hijau
    }
});

// ==============================================================
// 1. MOCKUP LISTENER WEBSOCKET UNTUK RASPI
// ==============================================================
// ==============================================================
// FUNGSI UPDATE ANGKA STATISTIK REAL-TIME (TANPA RELOAD)
// ==============================================================
function updateRealtimeStats(barcode, expiryDateStr) {
    // 1. Hitung Status Expiry dari Tanggal (Format: dd/mm/yyyy)
    const parts = expiryDateStr.split('/');
    const expDate = new Date(parts[2], parts[1] - 1, parts[0]);

    const today = new Date();
    today.setHours(0, 0, 0, 0); // Reset jam agar hitungan harinya akurat

    const warningDate = new Date(today);
    warningDate.setDate(warningDate.getDate() + 14); // Batas H-14

    let status = 'safe';
    if (expDate < today) {
        status = 'expired';
    } else if (expDate <= warningDate) {
        status = 'warning';
    }

    // 2. Update Angka di Kartu Statistik Atas
    const totalCard = document.getElementById('total-batch-count');
    if (totalCard) totalCard.textContent = parseInt(totalCard.textContent) + 1;

    if (status === 'safe') {
        const card = document.getElementById('safe-batch-count');
        if (card) card.textContent = parseInt(card.textContent) + 1;
    } else if (status === 'warning') {
        const card = document.getElementById('warning-batch-count');
        if (card) card.textContent = parseInt(card.textContent) + 1;
    } else if (status === 'expired') {
        const card = document.getElementById('expired-batch-count');
        if (card) card.textContent = parseInt(card.textContent) + 1;
    }

    // 3. Update Angka di Baris Tabel Produk yang Bersangkutan
    const row = document.querySelector(`.stock-row[data-barcode="${barcode}"]`);
    if (row) {
        // Update Data Attribute (Berguna jika kamu menggunakan fitur sorting/filter)
        row.setAttribute('data-total', parseInt(row.getAttribute('data-total') || 0) + 1);

        // Tambah +1 pada kolom Total Batch (Kolom ke-6)
        const tdTotal = row.querySelector('td:nth-child(6) .count-pill');
        if (tdTotal) tdTotal.textContent = parseInt(tdTotal.textContent) + 1;

        // Tambah +1 pada kolom status spesifik
        if (status === 'safe') {
            row.setAttribute('data-safe', parseInt(row.getAttribute('data-safe') || 0) + 1);
            const tdSafe = row.querySelector('td:nth-child(7) .count-pill');
            if (tdSafe) tdSafe.textContent = parseInt(tdSafe.textContent) + 1;
        } else if (status === 'warning') {
            row.setAttribute('data-warning', parseInt(row.getAttribute('data-warning') || 0) + 1);
            const tdWarn = row.querySelector('td:nth-child(8) .count-pill');
            if (tdWarn) tdWarn.textContent = parseInt(tdWarn.textContent) + 1;
        } else if (status === 'expired') {
            row.setAttribute('data-expired', parseInt(row.getAttribute('data-expired') || 0) + 1);
            const tdExp = row.querySelector('td:nth-child(9) .count-pill');
            if (tdExp) tdExp.textContent = parseInt(tdExp.textContent) + 1;
        }

        // Buat baris tabel berkedip sebentar agar pengguna notice ada data masuk
        row.style.backgroundColor = '#ecfdf5';
        setTimeout(() => row.style.backgroundColor = '', 800);
    }
}

// A. Fungsi Log HANYA untuk halaman Expiry ini
function addBatchLog(data, isManual = false) {
    const log = document.getElementById('batch-scan-log');
    const emp = document.getElementById('log-empty-state');
    if (emp) emp.remove();

    const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    const el = document.createElement('div');
    el.className = 'scan-log-item scan-log-item--in fade-in';
    el.innerHTML = `
        <div class="scan-log-indicator scan-log-dot--in"></div>
        <div class="scan-log-body">
            <span class="scan-log-name">${data.nama_barang}</span>
            <span class=".scan-log-exp">Exp: ${data.expiry_date}</span>
        </div>
        <div class="scan-log-right">
            <span class="badge badge--success" style="font-size:10px">${isManual ? 'MANUAL IN' : 'SCANNED IN'}</span>
            <span class="scan-log-time">${time}</span>
        </div>`;

    log.insertBefore(el, log.firstChild);
    const items = log.querySelectorAll('.scan-log-item');
    if (items.length > 10) items[items.length - 1].remove();

    // ==> TAMBAHKAN BARIS INI <==
    // Panggil update statistik tabel secara ajaib!
    if (data.barcode && data.expiry_date) {
        updateRealtimeStats(data.barcode, data.expiry_date);
    }
}

// B. Tangkap sinyal Global dari app.blade.php untuk menambah Log (Tanpa membuat Toast lagi)
window.addEventListener("global-batch-scanned", (e) => {
    addBatchLog({ barcode: e.detail.barcode, nama_barang: e.detail.nama_barang, expiry_date: e.detail.expiry_date }, false);
});

// C. Form Manual Input (Panggil Toast Global secara manual)
async function submitManualBatch(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } });
        const result = await response.json();

        if (response.ok && result.success) {
            if (typeof closeAddBatchModal === 'function') closeAddBatchModal();
            form.reset();

            // Panggil fungsi pembuat Toast yang ada di app.blade.php
            if (typeof showGlobalToast === 'function') {
                showGlobalToast('Input Manual Berhasil!', `Batch ${result.data.nama_barang} berhasil ditambah.`);
            }

            addBatchLog(result.data, true);
        } else {
            alert('Gagal: ' + (result.message || 'Periksa inputan.'));
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
function clearScanLog() {
    document.getElementById('batch-scan-log').innerHTML = `
        <div class="realtime-empty" id="log-empty-state" style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:24px 0; color:var(--grey-400);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" opacity="0.25"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><path d="M12 8v4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span id="log-empty-text" style="font-size:12px;">Log dibersihkan. Menunggu payload raspi…</span>
        </div>`;
}

// ==============================================================
// 2. TOGGLE EXPAND BARIS TABEL BATCH
// ==============================================================
async function toggleBatchList(itemId) {
    const row   = document.getElementById('batches-' + itemId);
    const icon  = document.getElementById('expand-icon-' + itemId);
    const inner = document.getElementById('batch-list-inner-' + itemId);

    if (!row) return;

    if (row.style.display === 'none' || !row.style.display) {
        row.style.display = 'table-row';
        icon.style.transform = 'rotate(180deg)';
        inner.innerHTML = '<div class="tag-list-loading" style="display:flex; align-items:center; gap:8px; color:var(--grey-500);"><div class="spinner" style="width:16px; height:16px; border:2px solid var(--grey-200); border-top-color:var(--primary-500); border-radius:50%; animation:spin 1s linear infinite;"></div><span style="font-size:13px;">Memuat batch…</span></div>';

        try {
            const r = await fetch(`{{ url('/api/items') }}/${itemId}/batches`);
            const d = await r.json();
            renderBatchList(inner, d.batches);
        } catch {
            inner.innerHTML = '<p class="text-muted text-sm" style="padding:12px;">Gagal memuat batch.</p>';
        }
    } else {
        row.style.display = 'none';
        icon.style.transform = 'rotate(0)';
    }
}

// ==============================================================
// FUNGSI UNTUK MENYARING CHIP BATCH DI DALAM ACCORDION
// ==============================================================
function filterVisibleChips() {
    // Ambil nilai filter aktif dari dropdown atas
    const currentFilter = document.getElementById('expiry-filter').value;
    const chips = document.querySelectorAll('.tag-chip');

    chips.forEach(chip => {
        const status = chip.getAttribute('data-status'); // Nilainya: 'expired', 'warning', atau 'aman'
        let show = true;

        // Tentukan apakah chip harus disembunyikan berdasarkan pilihan filter dropdown
        if (currentFilter === 'has_expired' && status !== 'expired') show = false;
        else if (currentFilter === 'has_warning' && status !== 'warning') show = false;
        else if (currentFilter === 'has_safe' && status !== 'aman') show = false;

        // Terapkan perubahan display (inline-flex untuk menjaga layout chip)
        chip.style.display = show ? 'inline-flex' : 'none';
    });
}

function renderBatchList(container, batches) {
    if (!batches?.length) {
        container.innerHTML = '<div class="tag-empty-state" style="padding:16px; text-align:center; color:var(--grey-500); background:#fff; border-radius:8px; border:1px dashed var(--grey-200);"><p style="margin:0; font-size:13px;">Belum ada batch expiry untuk produk ini.</p></div>';
        return;
    }

    const itemId = container.id.replace('batch-list-inner-', '');
    let html = `<div class="tag-grid"><div class="tag-chips">`;

    batches.forEach(b => {
        const badgeClass = b.status === 'expired' ? 'badge--danger' : (b.status === 'warning' ? 'badge--amber' : 'badge--success');
        const badgeLabel = b.status === 'expired' ? 'EXPIRED' : (b.status === 'warning' ? 'PRE-EXPIRED' : 'AMAN');
        const chipClass  = b.status === 'expired' ? 'tag-chip--out' : (b.status === 'warning' ? 'tag-chip--warning' : 'tag-chip--in');

        // ==> TAMBAHKAN data-status="${b.status}" DI SINI <==
        html += `<div class="tag-chip ${chipClass}" data-status="${b.status}" style="position:relative; width:32%; padding-right:28px;">
            <span class="tag-bth">${b.batch_code}</span>
            <span class="tag-date">${b.expiry_date}</span>
            <span class="badge ${badgeClass}" style="font-size:10px; margin-left: auto">${badgeLabel}</span>
            <button onclick="deleteBatch(${b.id}, ${itemId})" class="btn-delete-batch" title="Hapus Batch">✕</button>
        </div>`;
    });
    html += '</div></div>';
    container.innerHTML = html;

    // ==> TAMBAHKAN BARIS INI <==
    // Agar ketika accordion dibuka, chip langsung tersaring mengikuti filter aktif
    sortVisibleChips();
    filterVisibleChips();
}

// ==============================================================
// FUNGSI UNTUK MENGURUTKAN CHIP BATCH DI DALAM ACCORDION
// ==============================================================
function sortVisibleChips() {
    const sort = document.getElementById('expiry-sort').value;
    const allChipContainers = document.querySelectorAll('.tag-chips');

    allChipContainers.forEach(container => {
        const chips = Array.from(container.children);

        chips.sort((a, b) => {
            const nameA = a.querySelector('.tag-bth').textContent;
            const nameB = b.querySelector('.tag-bth').textContent;

            // Ambil teks tanggal (dd/mm/yyyy) dan bongkar agar bisa dibaca JavaScript
            const dateAStr = a.querySelector('.tag-date').textContent.split('/');
            const dateBStr = b.querySelector('.tag-date').textContent.split('/');

            // Format: new Date(Tahun, Bulan - 1, Tanggal)
            const dateA = new Date(dateAStr[2], dateAStr[1] - 1, dateAStr[0]).getTime();
            const dateB = new Date(dateBStr[2], dateBStr[1] - 1, dateBStr[0]).getTime();

            // Logika Pengurutan
            if (sort === 'name_asc') {
                return nameA.localeCompare(nameB);
            } else if (sort === 'name_desc') {
                return nameB.localeCompare(nameA);
            } else if (sort === 'date_asc') {
                return dateA - dateB;
            } else if (sort === 'date_desc') {
                return dateB - dateA;
            }
            return 0;
        });

        // Tanamkan kembali chip ke layar
        chips.forEach(chip => container.appendChild(chip));
    });
}

async function deleteBatch(batchId, itemId) {
    if (!confirm('Apakah kamu yakin ingin menghapus batch ini? Data yang dihapus tidak dapat dikembalikan.')) return;

    try {
        const response = await fetch(`{{ url('/api/batches') }}/${batchId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            alert('Gagal menghapus: ' + data.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan jaringan.');
    }
}

// ==============================================================
// 3. FITUR SEARCH, FILTER & SORT TABEL
// ==============================================================
function applyFilters() {
    const q      = (document.getElementById('expiry-search').value || '').toLowerCase();
    const filter = document.getElementById('expiry-filter').value;
    const sort   = document.getElementById('expiry-sort').value;

    const rows   = Array.from(document.querySelectorAll('.stock-row'));

    // 1. FILTERING
    rows.forEach(row => {
        const name    = row.getAttribute('data-name') || '';
        const kode    = row.getAttribute('data-kode') || '';
        const safe    = parseInt(row.getAttribute('data-safe') || 0);
        const warning = parseInt(row.getAttribute('data-warning') || 0);
        const expired = parseInt(row.getAttribute('data-expired') || 0);

        const matchSearch = !q || name.includes(q) || kode.includes(q);

        let matchFilter = true;
        if (filter === 'has_expired') matchFilter = expired > 0;
        else if (filter === 'has_warning') matchFilter = warning > 0;
        else if (filter === 'has_safe') matchFilter = safe > 0;

        const show = matchSearch && matchFilter;
        row.style.display = show ? '' : 'none';

        const batchRow = document.getElementById('batches-' + row.getAttribute('data-item-id'));
        if (batchRow && !show) batchRow.style.display = 'none';
    });

    // 2. SORTING
    const tbody  = document.getElementById('expiry-table-body');
    const visible = rows.filter(r => r.style.display !== 'none');

    visible.sort((a, b) => {
        const aName = a.getAttribute('data-name') || '';
        const bName = b.getAttribute('data-name') || '';
        const aDate = a.getAttribute('data-earliest-date') || '9999-12-31';
        const bDate = b.getAttribute('data-earliest-date') || '9999-12-31';

        if (sort === 'name_asc')  return aName.localeCompare(bName);
        if (sort === 'name_desc') return bName.localeCompare(aName);
        if (sort === 'date_asc')  return aDate.localeCompare(bDate); // Terdekat di atas
        if (sort === 'date_desc') return bDate.localeCompare(aDate); // Terjauh di atas
        return 0;
    });

    visible.forEach(row => {
        tbody.appendChild(row);
        const batchRow = document.getElementById('batches-' + row.getAttribute('data-item-id'));
        if (batchRow) tbody.appendChild(batchRow);
    });

    sortVisibleChips();
    filterVisibleChips();
}

// ==============================================================
// 4. MODAL DETAIL BATCH PRODUK
// ==============================================================
async function openExpiryDetailModal(itemId) {
    const row = document.getElementById('row-' + itemId);
    // Set informasi satuan produk ke modal
    const satuan = row.getAttribute('data-satuan') || 'PCS';

    if (!row) return;

    // Set info produk di header modal
    document.getElementById('modal-nama-produk').textContent = row.getAttribute('data-name').toUpperCase();
    document.getElementById('modal-kode-produk').textContent = row.getAttribute('data-kode').toUpperCase();
    document.getElementById('modal-satuan-produk').textContent = satuan;
    // Ambil teks barcode dari tabel utama
    const barcodeCell = row.children[2];
    document.getElementById('modal-barcode-produk').textContent = barcodeCell && barcodeCell.textContent.trim() !== '-' ? barcodeCell.textContent.trim() : 'Tanpa Barcode';

    // Set nilai statistik dari data tabel
    document.getElementById('modal-stat-aman').textContent = row.getAttribute('data-safe') || '0';
    document.getElementById('modal-stat-warning').textContent = row.getAttribute('data-warning') || '0';
    document.getElementById('modal-stat-expired').textContent = row.getAttribute('data-expired') || '0';

    document.getElementById('modal-btn-add').onclick = () => {
        closeExpiryDetailModal();
        openAddBatchModal(itemId);
    };

    // Tampilkan modal
    document.getElementById('expiry-detail-modal-overlay').style.display = 'flex';
    setTimeout(() => document.getElementById('expiry-detail-modal-overlay').classList.add('detail-modal-overlay--visible'), 10);

    // Tampilkan animasi loading di tabel modal
    const tbody = document.getElementById('modal-batch-list');
    tbody.innerHTML = `<tr><td colspan="3" style="text-align:center; padding:40px; color:var(--grey-500);"><div class="spinner" style="width:24px; height:24px; margin:0 auto 12px; border:2px solid var(--grey-200); border-top-color:var(--primary-500); border-radius:50%; animation:spin 1s linear infinite;"></div>Memuat data batch...</td></tr>`;

    try {
        const r = await fetch(`{{ url('/api/items') }}/${itemId}/batches`);
        const d = await r.json();

        if (!d.batches || d.batches.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" style="text-align:center; padding:32px; color:var(--grey-500); font-style:italic;">Tidak ada data batch terdaftar.</td></tr>`;
            return;
        }

        let html = '';
        d.batches.forEach(b => {
            const badgeClass = b.status === 'expired' ? 'badge--danger' : (b.status === 'warning' ? 'badge--amber' : 'badge--success');
            const badgeLabel = b.status === 'expired' ? 'EXPIRED' : (b.status === 'warning' ? 'PREEXPIRED' : 'AMAN');

            html += `
                <tr style="border-bottom: 1px solid var(--grey-100);">
                    <td style="padding:10px 12px; font-family:monospace; font-size:12px; color:var(--grey-700); font-weight:600;">${b.batch_code}</td>
                    <td style="padding:10px 12px; font-size:12px; color:var(--grey-800);">${b.expiry_date}</td>
                    <td style="padding:10px 12px; text-align:center;">
                        <span class="badge ${badgeClass}" style="font-size:10px;">${badgeLabel}</span>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;

    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="3" style="text-align:center; padding:32px; color:var(--red-500);">Terjadi kesalahan saat memuat data.</td></tr>`;
    }
}

function closeExpiryDetailModal(e) {
    if (e && e.target !== document.getElementById('expiry-detail-modal-overlay')) return;
    document.getElementById('expiry-detail-modal-overlay').classList.remove('detail-modal-overlay--visible');
    setTimeout(() => { document.getElementById('expiry-detail-modal-overlay').style.display = 'none'; }, 250);
}

// ==============================================================
// MODAL TAMBAH BATCH MANUAL & FILTER PENCARIAN (VERSI 1 KOLOM)
// ==============================================================

function openAddBatchModal(itemId = null) {
    const modal = document.getElementById('add-batch-modal-overlay');
    if (!modal) return;

    const hiddenInput = document.getElementById('modal_item_id');
    const searchInput = document.getElementById('search_product');

    // 1. Set/Reset Pilihan Produk
    if (itemId) {
        hiddenInput.value = itemId;
        // Cari opsi yang sesuai untuk menampilkan nama produk di layar
        const selectedOpt = document.querySelector(`.custom-option[data-value="${itemId}"]`);
        if (selectedOpt) searchInput.value = selectedOpt.getAttribute('data-label');
    } else {
        hiddenInput.value = '';
        searchInput.value = '';
    }

    // Pastikan dropdown tertutup saat modal baru dibuka
    const dropdown = document.getElementById('custom_options_container');
    if (dropdown) dropdown.style.display = 'none';

    // 2. Reset Tanggal Kedaluwarsa
    const dateInput = document.getElementById('modal_expiry_date');
    if (dateInput && !itemId) dateInput.value = '';

    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('detail-modal-overlay--visible'), 10);
}

// Buka dropdown saat input diklik
function openCustomDropdown() {
    document.getElementById('custom_options_container').style.display = 'block';
    filterCustomDropdown(false); // false = jangan hapus item_id saat baru diklik
}

// Filter saat pengguna mengetik
function filterCustomDropdown(isTyping = true) {
    const input = document.getElementById("search_product").value.toLowerCase();
    const options = document.querySelectorAll('.custom-option');
    let hasMatch = false;

    // Jika user mengetik, hapus item_id lama karena pilihannya berubah
    if (isTyping) {
        document.getElementById('modal_item_id').value = '';
    }

    options.forEach(opt => {
        const searchData = opt.getAttribute('data-search') || '';
        if (searchData.includes(input)) {
            opt.style.display = 'block';
            hasMatch = true;
        } else {
            opt.style.display = 'none';
        }
    });

    document.getElementById('custom_option_empty').style.display = hasMatch ? 'none' : 'block';
    document.getElementById('custom_options_container').style.display = 'block';
}

// Aksi saat opsi produk diklik
function selectCustomOption(el) {
    document.getElementById('modal_item_id').value = el.getAttribute('data-value');
    document.getElementById('search_product').value = el.getAttribute('data-label');
    document.getElementById('custom_options_container').style.display = 'none';
}

// Menutup dropdown jika klik sembarang tempat di luar kotak
document.addEventListener('click', function(e) {
    const container = document.getElementById('custom_options_container');
    const input = document.getElementById('search_product');

    if (container && input && !container.contains(e.target) && e.target !== input) {
        container.style.display = 'none';
        // Mencegah input 'menggantung': bersihkan teks jika user tidak jadi memilih produk
        if (document.getElementById('modal_item_id').value === '') {
             input.value = '';
        }
    }
});

function closeAddBatchModal(e) {
    if (e && e.target !== document.getElementById('add-batch-modal-overlay')) return;

    const modal = document.getElementById('add-batch-modal-overlay');
    modal.classList.remove('detail-modal-overlay--visible');
    setTimeout(() => { modal.style.display = 'none'; }, 250);
}

// // ==============================================================
// // MODAL EDIT KATALOG PRODUK
// // ==============================================================
function openEditProductModal(itemId) {
    const row = document.getElementById('row-' + itemId);
    if (!row) return;

    // Isi form dengan data dari atribut baris tabel
    document.getElementById('edit_kode_barang').value = row.getAttribute('data-kode');
    document.getElementById('edit_barcode').value = row.getAttribute('data-barcode');
    document.getElementById('edit_nama_barang').value = row.getAttribute('data-name');
    document.getElementById('edit_satuan').value = row.getAttribute('data-satuan');
    document.getElementById('edit_deskripsi').value = row.getAttribute('data-deskripsi') || '';

    // Ubah action form secara dinamis (mengarah ke route update produkmu)
    const form = document.getElementById('form-edit-product');
    form.action = `{{ url('/stock/items') }}/${itemId}`;

    // Tampilkan modal
    const modal = document.getElementById('edit-product-modal-overlay');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('detail-modal-overlay--visible'), 10);
}

function closeEditProductModal(e) {
    if (e && e.target !== document.getElementById('edit-product-modal-overlay')) return;
    const modal = document.getElementById('edit-product-modal-overlay');
    modal.classList.remove('detail-modal-overlay--visible');
    setTimeout(() => { modal.style.display = 'none'; }, 250);
}
</script>

<style>
/* ── Warna Status Expiry ── */
.count-pill--amber { background:#FFFBEB; color:#F59E0B; }


/* ── Layout 2 Kolom ── */
.stock-control-row {display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;}
.stock-control-panel, .stock-log-panel { display: flex; flex-direction: column;}
.panel-divider { height:1px; background:var(--grey-100); margin:0; }

@media (max-width: 900px) { .stock-control-row { grid-template-columns: 1fr;}
}

/* Row button */
.btn-row-icon { width:28px;height:28px;border-radius:7px;border:1px solid var(--grey-200);background:white;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s,border-color .15s; }
.btn-row-icon--detail { color:var(--primary-500); }
.btn-row-icon--detail:hover { background:var(--primary-50);border-color:var(--primary-300); }
.btn-row-icon--edit { color:var(--amber-500); }
.btn-row-icon--edit:hover { background:var(--amber-50);border-color:var(--amber-300); }

/* ── Detail Modal Expiry ── */
.detail-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:9990;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .25s; }
.detail-modal-overlay--visible { opacity:1; }
.detail-modal { background:white;border-radius:16px;max-width:calc(100vw - 32px);overflow:hidden;box-shadow:0 16px 60px rgba(0,0,0,.2);transform:scale(.96) translateY(8px);transition:transform .25s; }
.detail-modal-overlay--visible .detail-modal { transform:scale(1) translateY(0); }
.detail-modal-header { display:flex;align-items:flex-start;gap:12px;padding:20px 20px 16px;border-bottom:1px solid var(--grey-100); position:relative; }
.detail-modal-icon { width:38px;height:38px;background:var(--primary-50);border-radius:9px;display:flex;align-items:center;justify-content:center;color:var(--primary-600);flex-shrink:0; }
.detail-modal-icon--edit {  color:var(--amber-500); background:var(--amber-50);border-color:var(--amber-300);}
.detail-modal-title { font-size:16px;font-weight:700;color:var(--grey-800);margin:0; }
.detail-modal-barcode-badge { font-size:11px;color:var(--grey-500);background:var(--grey-100);border-radius:10px;padding:1px 8px; }
.detail-modal-satuan-badge { font-size:11px;color:var(--grey-500);background:var(--grey-100);border-radius:10px;padding:1px 8px; }
.batch-modal-close { position:absolute;right:16px;top:16px;border:none;background:none;cursor:pointer;color:var(--grey-400);padding:4px; }
.detail-modal-stats { display:flex;align-items:stretch;border-bottom:1px solid var(--grey-100); }
.detail-stat-box { flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;padding:16px 12px; }
.detail-stat-val { font-size:24px;font-weight:700;line-height:1; }
.detail-stat--green { color:var(--green-600); }
.detail-stat--red   { color:var(--red-500); }
.detail-stat-label  { font-size:11px;color:var(--grey-400);font-weight:500; }
.detail-stat-divider { width:1px;background:var(--grey-100);margin:12px 0; }
.detail-modal-body { padding:16px 20px; }
.detail-desc-label { font-size:11px;font-weight:600;color:var(--grey-400);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px; }
.detail-modal-footer { display:flex;gap:8px;justify-content:flex-end;padding:12px 20px 20px;border-top:1px solid var(--grey-100); }

/* ── Form Input ── */
.form-label { font-size: 11px;font-weight: 600;color: var(--grey-500);text-transform: uppercase;letter-spacing:.05em;margin-bottom: 6px;display: block;}
.form-input, .form-control { width: 100%;border: 1px solid var(--grey-200);border-radius: 8px;font-size: 13px;color: var(--grey-800);background: #fff;outline: none;transition: border-color .15s;}
.form-input:focus, .form-control:focus {border-color: var(--primary-400);}
.form-grid--2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
#add-form-body .form-input { padding: 8px 12px; }
#add-form-body .form-input::placeholder { color: var(--grey-400); }

/* ── Tombol Expand ── */
.expand-btn { background: none;border: none;cursor: pointer;color: var(--grey-500);display: flex;align-items: center;justify-content: center;width: 24px;height: 24px;border-radius: 6px;transition: background 0.15s, color 0.15s;}
.expand-btn:hover { background: var(--grey-100);color: var(--grey-700);}
.expand-icon { transition: transform 0.2s ease-in-out;}

/* ── Tag / Chip Batch ── */
.tag-grid { padding: 12px 16px;}
.tag-chips { display: flex;flex-wrap: wrap;gap: 8px;}
.tag-chip {display: flex;align-items: center;gap: 8px;padding: 6px 10px;border-radius: 8px;border: 1px solid;background: #fff;font-size: 12px;}
.tag-chip--in { border-color: #bbf7d0; }
.tag-chip--warning { border-color: #FDE68A; }
.tag-chip--out { border-color: #fecaca; }
.tag-bth { font-family: monospace; font-weight: 600; color: var(--grey-700); }
.tag-date { color: var(--grey-500); font-size: 11px; }

/* ── Tombol Hapus Batch (Silang) ── */
.btn-delete-batch { position: absolute;right: 4px;top: 50%;transform: translateY(-50%);background: none;border: none;color: var(--grey-400);font-size: 10px;cursor: pointer;width: 20px;height: 20px;display: flex;align-items: center;justify-content: center;border-radius: 50%;transition: all 0.2s;}
.btn-delete-batch:hover { background: var(--red-100);color: var(--red-600);}

@keyframes spin { 100% { transform:rotate(360deg); } }

/* ── Filter row ── */
.stock-table-filters { display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.filter-select { height:34px;padding:0 10px;border:1px solid var(--grey-200);border-radius:8px;font-size:12px;background:white;color:var(--grey-700);cursor:pointer;outline:none; }
.filter-select:focus { border-color:var(--primary-400); }

/* ── Log panel ── */
.stock-scan-log { display:flex;flex-direction:column;gap:5px;max-height:300px;overflow-y:auto;padding-right:2px; }
.stock-scan-log::-webkit-scrollbar{width:3px;} .stock-scan-log::-webkit-scrollbar-thumb{background:var(--grey-200);border-radius:2px;}
.scan-log-item { display:flex;align-items:center;gap:9px;padding:7px 10px;border-radius:7px;border-left:2px solid transparent; }
.scan-log-item--in    { background:var(--green-50);border-left-color:var(--green-400); }
.scan-log-item--out   { background:var(--red-50);border-left-color:var(--red-400); }
.scan-log-item--check { background:#eff6ff;border-left-color:#60a5fa; }
.scan-log-indicator { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
.scan-log-dot--in    { background:var(--green-500); }
.scan-log-dot--out   { background:var(--red-500); }
.scan-log-dot--check { background:#3b82f6; }
.badge--info { background:#dbeafe;color:#1d4ed8;border:1px solid #bfdbfe; }
.scan-log-body { flex:1;min-width:0; }
.scan-log-name { display:block;font-size:12px;font-weight:600;color:var(--grey-800);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.scan-log-bth  { display:block;font-size:10px;color:var(--grey-400);font-family:monospace; }
.scan-log-right { display:flex;flex-direction:column;align-items:flex-end;gap:2px;flex-shrink:0; }
.scan-log-time  { font-size:10px;color:var(--grey-400);font-variant-numeric:tabular-nums; }
.badge--offline { background:#FEE2E2;color:#B91C1C;border:1px solid #FECACA; font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;letter-spacing:.06em; }


/* ── nav-item--soon ── */
.nav-item--soon { opacity:.5; pointer-events:none; }

/* ── Custom Searchable Dropdown ── */
.custom-option { padding: 10px 12px; cursor: pointer; font-size: 13px; color: var(--grey-700); border-bottom: 1px solid var(--grey-50); transition: background 0.15s; }
.custom-option:hover { background: var(--primary-50); color: var(--primary-600); }
.custom-option:last-child { border-bottom: none; }
</style>
@endpush
