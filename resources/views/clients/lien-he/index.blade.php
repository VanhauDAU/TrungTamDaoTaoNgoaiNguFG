@extends('layouts.client')

@section('title', 'Liên hệ - Trung tâm Anh ngữ Five Genius')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/contact.css') }}">
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')
    <div class="contact_page py-80">
        <div class="container">

            {{-- ── HEADER ────────────────────────────────────────────────── --}}
            <div class="title_animate mb-4 pe-5 mb-lg-5">
                <h1 class="fs-48 ff-title cl-green mb-0">Có thắc mắc hoặc<br>cần thêm thông tin?</h1>
                <div class="title_icon">
                    <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/plane.svg"
                        class="img-fluid" alt="">
                </div>
            </div>

            {{-- ── CTA CARDS ──────────────────────────────────────────────── --}}
            <div class="row g-4 mb-5">
                <div class="col-lg-4">
                    <div class="contact_item item_1">
                        <h4 class="fs-32 ff-title cl-green ls-1 mb-3">Học IELTS</h4>
                        <div class="desc fw-light mb-5">Nhận tư vấn về khoá học IELTS, SAT, tiếng anh trẻ em và thanh thiếu
                            niên.</div>
                        <a href="" class="contact_btn ff-title" data-bs-toggle="modal" data-bs-target="#adviseModal">
                            Form Đăng ký <img
                                src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/readmore.png"
                                alt="">
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="contact_item item_2">
                        <h4 class="fs-32 ff-title cl-green ls-1 mb-3">Thi IELTS</h4>
                        <div class="desc fw-light mb-5">Đăng kí lịch thi iELTS tại The Form và đối tác IDP</div>
                        <a href="" class="contact_btn ff-title" data-bs-toggle="modal" data-bs-target="#testModal">
                            Form đăng ký <img
                                src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/readmore.png"
                                alt="">
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="contact_item item_3">
                        <h4 class="fs-32 ff-title cl-green ls-1 mb-3">Liên hệ</h4>
                        <div class="desc fw-light mb-5">Hợp tác, hoặc các thắc mắc khác.</div>
                        <a href="mailto:hi@theforumcenter.vn" class="contact_btn ff-title">
                            Gửi email <img
                                src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/readmore.png"
                                alt="">
                        </a>
                    </div>
                </div>
            </div>

            {{-- ── HỆ THỐNG CƠ SỞ ─────────────────────────────────────────── --}}
            <div class="branch_section">
                <div class="branch_section_header">
                    <h2 class="fs-36 ff-title cl-green mb-0">Hệ thống cơ sở</h2>
                    <span class="branch_count_badge" id="branchCountBadge">{{ $coSoDaoTao->count() }} cơ sở</span>
                </div>

                {{-- ── BỘ LỌC ──────────────────────────────────────────────── --}}
                <div class="branch_filter_bar">
                    {{-- Tìm kiếm text --}}
                    <div class="filter_item filter_search">
                        <i class="fas fa-search filter_icon"></i>
                        <input type="text" id="searchInput" placeholder="Tìm tên cơ sở, địa chỉ..." autocomplete="off">
                    </div>

                    {{-- Dropdown Tỉnh/Thành --}}
                    <div class="filter_item filter_select">
                        <i class="fas fa-map-marker-alt filter_icon"></i>
                        <select id="filterTinh">
                            <option value="">Tất cả tỉnh/thành</option>
                            @foreach ($tinhThanhs as $tinh)
                                <option value="{{ $tinh->tinhThanhId }}" data-ma-api="{{ $tinh->maAPI }}">
                                    {{ $tinh->tenTinhThanh }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown Phường/Xã --}}
                    <div class="filter_item filter_select">
                        <i class="fas fa-location-dot filter_icon"></i>
                        <select id="filterPhuong" disabled>
                            <option value="">Tất cả phường/xã</option>
                        </select>
                    </div>

                    {{-- Nút reset --}}
                    <button class="filter_reset_btn" id="resetFilter" title="Xóa bộ lọc">
                        <i class="fas fa-rotate-left"></i>
                    </button>
                </div>

                {{-- ── LAYOUT: CARD LIST + MAP ─────────────────────────────── --}}
                <div class="branch_layout">

                    {{-- Danh sách cơ sở --}}
                    <div class="branch_list" id="branchList">
                        @forelse ($coSoDaoTao as $coSo)
                            <div class="branch_card" data-id="{{ $coSo->coSoId }}" data-tinh="{{ $coSo->tinhThanhId }}"
                                data-phuong="{{ $coSo->maPhuongXa }}" data-ten="{{ strtolower($coSo->tenCoSo) }}"
                                data-dia-chi="{{ strtolower($coSo->diaChi . ' ' . $coSo->tenPhuongXa) }}"
                                data-lat="{{ $coSo->viDo }}" data-lng="{{ $coSo->kinhDo }}">

                                <div class="branch_card_header">
                                    <div class="branch_icon"><i class="fas fa-building"></i></div>
                                    <div>
                                        <div class="branch_name">{{ $coSo->tenCoSo }}</div>
                                        <div class="branch_province">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ optional($coSo->tinhThanh)->tenTinhThanh ?? '—' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="branch_card_body">
                                    @if ($coSo->tenPhuongXa)
                                        <div class="branch_info">
                                            <i class="fas fa-location-dot"></i>
                                            <span>{{ $coSo->tenPhuongXa }}</span>
                                        </div>
                                    @endif
                                    @if ($coSo->diaChi)
                                        <div class="branch_info">
                                            <i class="fas fa-road"></i>
                                            <span>{{ $coSo->diaChi }}</span>
                                        </div>
                                    @endif
                                    @if ($coSo->soDienThoai)
                                        <div class="branch_info">
                                            <i class="fas fa-phone"></i>
                                            <span><a
                                                    href="tel:{{ $coSo->soDienThoai }}">{{ $coSo->soDienThoai }}</a></span>
                                        </div>
                                    @endif
                                    @if ($coSo->email)
                                        <div class="branch_info">
                                            <i class="fas fa-envelope"></i>
                                            <span><a href="mailto:{{ $coSo->email }}">{{ $coSo->email }}</a></span>
                                        </div>
                                    @endif
                                </div>

                                <div class="branch_card_footer">
                                    @if ($coSo->viDo && $coSo->kinhDo)
                                        <button class="btn_view_map" data-id="{{ $coSo->coSoId }}"
                                            data-lat="{{ $coSo->viDo }}" data-lng="{{ $coSo->kinhDo }}"
                                            data-ten="{{ $coSo->tenCoSo }}">
                                            <i class="fas fa-map"></i> Xem trên bản đồ
                                        </button>
                                    @endif
                                    <a href="{{ route('home.contact.consultation.store') }}" class="btn_contact"
                                        data-bs-toggle="modal" data-bs-target="#adviseModal">
                                        <i class="fas fa-comments"></i> Tư vấn
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="branch_empty" id="noResult" style="display:block;">
                                <i class="fas fa-building-circle-xmark"></i>
                                <p>Chưa có cơ sở nào đang hoạt động.</p>
                            </div>
                        @endforelse

                        {{-- No result khi filter --}}
                        <div class="branch_empty" id="noResult" style="display:none;">
                            <i class="fas fa-magnifying-glass"></i>
                            <p>Không tìm thấy cơ sở phù hợp.</p>
                        </div>
                    </div>

                    {{-- Bản đồ Leaflet --}}
                    <div class="branch_map_wrap">
                        <div id="branchMap"></div>
                        <div class="map_hint_text"><i class="fas fa-info-circle"></i> Nhấn vào cơ sở để xem trên bản đồ
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <x-client.register-advice />
@endsection

@section('script')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ── Dữ liệu cơ sở từ PHP ──────────────────────────────────────────────
            @php
                $branchData = $coSoDaoTao
                    ->map(function ($c) {
                        return [
                            'id' => $c->coSoId,
                            'ten' => $c->tenCoSo,
                            'diaChi' => $c->diaChi,
                            'tenPhuongXa' => $c->tenPhuongXa,
                            'tinhThanh' => optional($c->tinhThanh)->tenTinhThanh,
                            'tinhThanhId' => $c->tinhThanhId,
                            'maPhuongXa' => $c->maPhuongXa,
                            'soDienThoai' => $c->soDienThoai,
                            'lat' => $c->viDo,
                            'lng' => $c->kinhDo,
                        ];
                    })
                    ->values()
                    ->all();
            @endphp
            const branches = @json($branchData);

            // ── Leaflet Map ────────────────────────────────────────────────────────
            const defaultCenter = [16.0, 106.0];
            const map = L.map('branchMap', {
                zoomControl: true
            }).setView(defaultCenter, 6);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(map);

            // Custom marker icon
            const markerIcon = L.divIcon({
                className: 'custom-marker',
                html: '<div class="marker-pin"><i class="fas fa-graduation-cap"></i></div>',
                iconSize: [36, 44],
                iconAnchor: [18, 44],
                popupAnchor: [0, -44],
            });
            const markerIconActive = L.divIcon({
                className: 'custom-marker active',
                html: '<div class="marker-pin active"><i class="fas fa-graduation-cap"></i></div>',
                iconSize: [40, 48],
                iconAnchor: [20, 48],
                popupAnchor: [0, -48],
            });

            // Tạo markers
            const markers = {};
            let activeMarkerId = null;

            branches.forEach(b => {
                if (!b.lat || !b.lng) return;
                const marker = L.marker([b.lat, b.lng], {
                    icon: markerIcon
                });
                const popup = L.popup({
                    maxWidth: 240,
                    className: 'branch-popup'
                }).setContent(`
            <div class="popup-content">
                <strong>${b.ten}</strong>
                ${b.tenPhuongXa ? `<div class="popup-ward">${b.tenPhuongXa}</div>` : ''}
                ${b.diaChi ? `<div class="popup-addr"><i class="fas fa-road"></i> ${b.diaChi}</div>` : ''}
                ${b.soDienThoai ? `<div class="popup-phone"><i class="fas fa-phone"></i> <a href="tel:${b.soDienThoai}">${b.soDienThoai}</a></div>` : ''}
            </div>
        `);
                marker.bindPopup(popup);
                marker.addTo(map);
                markers[b.id] = marker;

                marker.on('click', () => {
                    highlightCard(b.id);
                    setActiveMarker(b.id);
                });
            });

            function setActiveMarker(id) {
                if (activeMarkerId && markers[activeMarkerId]) {
                    markers[activeMarkerId].setIcon(markerIcon);
                }
                activeMarkerId = id;
                if (markers[id]) {
                    markers[id].setIcon(markerIconActive);
                }
            }

            function panToMarker(id, lat, lng) {
                setActiveMarker(id);
                map.flyTo([lat, lng], 15, {
                    animate: true,
                    duration: 1
                });
                if (markers[id]) markers[id].openPopup();
            }

            function highlightCard(id) {
                document.querySelectorAll('.branch_card').forEach(c => c.classList.remove('active'));
                const card = document.querySelector(`.branch_card[data-id="${id}"]`);
                if (card) {
                    card.classList.add('active');
                    card.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }

            // Nút "Xem trên bản đồ"
            document.querySelectorAll('.btn_view_map').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = parseInt(this.dataset.id);
                    const lat = parseFloat(this.dataset.lat);
                    const lng = parseFloat(this.dataset.lng);
                    panToMarker(id, lat, lng);
                });
            });

            // ── BỘ LỌC ────────────────────────────────────────────────────────────
            const searchInput = document.getElementById('searchInput');
            const filterTinh = document.getElementById('filterTinh');
            const filterPhuong = document.getElementById('filterPhuong');
            const resetBtn = document.getElementById('resetFilter');
            const noResult = document.getElementById('noResult');
            const countBadge = document.getElementById('branchCountBadge');

            // Lọc phường/xã theo tỉnh (dùng API)
            filterTinh.addEventListener('change', async function() {
                const opt = this.options[this.selectedIndex];
                const maAPI = opt ? opt.dataset.maApi : null;

                filterPhuong.innerHTML = '<option value="">Tất cả phường/xã</option>';
                filterPhuong.disabled = true;

                if (maAPI) {
                    try {
                        const res = await fetch(`/api/phuong-xa/${maAPI}`);
                        const data = await res.json();

                        // Chỉ hiển thị phường/xã CÓ cơ sở
                        const tinhId = parseInt(this.value);
                        const validMas = new Set(
                            branches
                            .filter(b => parseInt(b.tinhThanhId) === tinhId)
                            .map(b => Number(b.maPhuongXa))
                            .filter(v => Number.isFinite(v) && v > 0)
                        );

                        (data.wards || []).forEach(w => {
                            const wardCode = Number(w.code);
                            if (!validMas.has(wardCode)) return;
                            const opt = document.createElement('option');
                            opt.value = wardCode;
                            opt.textContent = w.name;
                            filterPhuong.appendChild(opt);
                        });

                        if (filterPhuong.options.length > 1) filterPhuong.disabled = false;
                    } catch (e) {}
                }

                applyFilter();
            });

            filterPhuong.addEventListener('change', applyFilter);
            searchInput.addEventListener('input', applyFilter);

            function applyFilter() {
                const q = searchInput.value.toLowerCase().trim();
                const tinhId = filterTinh.value ? parseInt(filterTinh.value) : null;
                const phuongMa = filterPhuong.value ? parseInt(filterPhuong.value) : null;

                const cards = document.querySelectorAll('.branch_card');
                let visible = 0;

                cards.forEach(card => {
                    const cardTinh = card.dataset.tinh ? parseInt(card.dataset.tinh) : null;
                    const cardPhuong = card.dataset.phuong ? parseInt(card.dataset.phuong) : null;
                    const cardTen = card.dataset.ten || '';
                    const cardAddr = card.dataset.diaChi || '';

                    const matchTinh = !tinhId || cardTinh === tinhId;
                    const matchPhuong = !phuongMa || cardPhuong === phuongMa;
                    const matchSearch = !q || cardTen.includes(q) || cardAddr.includes(q);

                    const show = matchTinh && matchPhuong && matchSearch;
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;

                    // Hiện/ẩn marker
                    const id = parseInt(card.dataset.id);
                    if (markers[id]) {
                        if (show) {
                            map.addLayer(markers[id]);
                        } else {
                            map.removeLayer(markers[id]);
                        }
                    }
                });

                noResult.style.display = visible === 0 ? 'flex' : 'none';
                countBadge.textContent = visible + ' cơ sở';

                // Nếu chỉ 1 kết quả và có tọa độ thì auto pan
                if (visible === 1) {
                    const visibleCard = [...cards].find(c => c.style.display !== 'none');
                    if (visibleCard) {
                        const id = parseInt(visibleCard.dataset.id);
                        const lat = parseFloat(visibleCard.dataset.lat);
                        const lng = parseFloat(visibleCard.dataset.lng);
                        if (lat && lng) panToMarker(id, lat, lng);
                    }
                } else if (tinhId && visible > 0) {
                    // Fit bounds những marker đang hiện
                    const latlngs = [...cards]
                        .filter(c => c.style.display !== 'none')
                        .map(c => [parseFloat(c.dataset.lat), parseFloat(c.dataset.lng)])
                        .filter(([la, ln]) => la && ln);
                    if (latlngs.length) map.fitBounds(latlngs, {
                        padding: [40, 40]
                    });
                }
            }

            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                filterTinh.value = '';
                filterPhuong.innerHTML = '<option value="">Tất cả phường/xã</option>';
                filterPhuong.disabled = true;
                applyFilter();
                map.setView(defaultCenter, 6);
            });

        });
    </script>
@endsection
