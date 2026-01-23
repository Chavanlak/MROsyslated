@extends('layout.mainlayout')

@section('title', 'รายการแจ้งซ่อม')

@section('content')

    <h5 class="fw-bold text-dark mb-3">
        <i class="bi bi-list-task"></i> รายการแจ้งซ่อมทั้งหมด
        @if (Auth::check())
            <span class="text-primary small fw-normal">({{ Auth::user()->staffname ?? 'ผู้ดูแลระบบ' }})</span>
        @endif
    </h5>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
{{-- Search Form Section --}}
    {{-- <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;"> --}}
        <div class="card-body p-3">
            {{-- action="" ปล่อยว่างไว้เพื่อให้ส่งค่ากลับมาที่หน้าเดิม --}}
            <form action="" method="GET">
                <div class="row g-2 align-items-center">

                    {{-- 1. ช่องค้นหา (ปรับให้แคบลงเป็น col-lg-4 ตามที่ขอ) --}}
                    <div class="col-12 col-lg-4">
                        <div class="input-group position-relative shadow-none border rounded-3">
                            <span class="input-group-text bg-transparent border-0 pe-1">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-0 ps-2 pe-5"
                                placeholder="ค้นหา รหัส, อุปกรณ์, สาขา..." value="{{ request('search') }}"
                                style="box-shadow: none; background: transparent;">
                            
                            {{-- ปุ่ม X สำหรับล้างคำค้นหา --}}
                            @if(request('search'))
                                <a href="{{ url()->current() }}" 
                                   class="position-absolute end-0 top-50 translate-middle-y me-2 text-muted"
                                   style="z-index: 5;">
                                    <i class="bi bi-x-circle-fill"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- 2. ตัวเลือกสถานะ (ให้กว้างขึ้นเป็น col-lg-6 เพื่อให้อ่านสถานะยาวๆ ได้ครบ) --}}
                    <div class="col-8 col-lg-2">
                        <select name="status" class="form-select border rounded-3 shadow-none">
                            <option value="">ทุกสถานะ</option>
                            @php
                                $statuses = [
                                    'ยังไม่ได้รับของ', 
                                    'ได้รับของแล้ว', 
                                    'ส่งSuplierแล้ว', 
                                    'กำลังดำเนินการซ่อม | ช่างStore', 
                                    'ซ่อมงานเสร็จแล้ว | ช่างStore', 
                                    'ซ่อมงานเสร็จแล้ว | Supplier', 
                                    'ได้รับของคืนเรียบร้อย', 
                                    'ปฏิเสธการซ่อม'
                                ];
                            @endphp
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                    {{ $st }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. ปุ่มค้นหา --}}
                    <div class="col-4 col-lg-1">
                        <button type="submit" class="btn btn-primary w-100 fw-bold rounded-3">
                            <i class="bi bi-search me-1"></i> ค้นหา
                        </button>
                    </div>

                </div>
            </form>
        </div>
    {{-- </div> --}}
    {{-- Desktop View --}}
    <div class="card shadow-sm d-none d-md-block border-0">
        <div class="card-body table-responsive">
            <table id="notiTable" class="table table-hover align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th style="width: 5%">ลำดับ</th>
                        <th style="width: 10%">รหัสเเจ้งซ่อม</th>
                        <th style="width: 12%">อุปกรณ์</th>
                        <th style="width: 23%">รายละเอียด</th>
                        <th style="width: 15%">สาขา</th>
                        <th style="width: 10%">วันที่แจ้งซ่อม</th>
                        <th style="width: 10%">อัปเดตล่าสุด</th>
                        <th style="width: 10%">สถานะปัจจุบัน</th>
                        <th style="width: 15%">จัดการ</th>
                    </tr>
                </thead>

                <tbody class="text-center">
                    @foreach ($noti as $item)
                        @php
                            $status = $item->status ?? 'ยังไม่ได้รับของ';

                            // เช็คสถานะการซ่อมเสร็จ
                            $isRepaired = str_contains($status, 'ซ่อมงานเสร็จแล้ว');
                            // เช็คสถานะปิดงาน (หน้าร้านรับของคืนแล้ว)
                            $isClosed = $status === 'ได้รับของคืนเรียบร้อย';

                            // กำหนดชื่อสถานะที่จะแสดงบนหน้าจอ
                            $displayStatus = $status;
                            // if ($isRepaired) {
                            //     $displayStatus = 'ซ่อมเสร็จสิ้น';
                            // }
                            if ($isClosed) {
                                $displayStatus = 'ได้รับของคืนเรียบร้อย';
                            }

                            $color = match ($status) {
                                'ยังไม่ได้รับของ' => 'danger',
                                'ได้รับของแล้ว' => 'primary',
                                'กำลังดำเนินการซ่อม | ช่างStore' => 'warning',
                                'ส่งSuplierแล้ว' => 'info',
                                'ซ่อมงานเสร็จแล้ว | ช่างStore', 'ซ่อมงานเสร็จแล้ว | Supplier' => 'success',
                                'ปฏิเสธการซ่อม' => 'dark', // เพิ่มสีสำหรับสถานะปฏิเสธ
                                'ได้รับของคืนเรียบร้อย' => 'success', // แก้ไขจาก sucess เป็น success
                                default => 'secondary',
                            };
                        @endphp
                  
                        <tr id="row-{{ $item->JobId ?? $item->NotirepairId }}">
                            <td>
                                {{-- สูตร: (หน้าปัจจุบัน - 1) * จำนวนต่อหน้า + ลำดับในหน้านี้ --}}
                                {{ ($noti->currentPage() - 1) * $noti->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <span class="text-primary fw-bold" style="font-size: 0.75rem;">
                                    #{{ $item->JobId ?? $item->NotirepairId }}
                                </span>
                            </td>

                            <td class="text-start small fw-bold">{{ $item->equipmentName }}</td>

                            {{-- <td style="min-width: 200px;">
                                <div style="white-space: normal; word-wrap: break-word; text-align: left;">
                                    {{ $item->DeatailNotirepair }}
                                </div>
                            </td> --}}
                            <td class="text-start small">
                                <div style="white-space: normal; min-width: 250px;">
                                    {{ $item->DeatailNotirepair }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->branchCode }}</div>
                                {{-- <div class="small text-muted">เอสพลานาด</div> --}}
                                <div class="small text-muted">{{ $branchNames[$item->branchCode] ?? 'ไม่พบชื่อสาขา' }}</div>
                            </td>

                            <td class="small">
                                {{ $item->DateNotirepair ? date('d-m-Y H:i', strtotime($item->DateNotirepair)) : '-' }}
                            </td>

                            {{-- <td class="small">
                                {{ $item->statusDate ? date('d-m-Y H:i', strtotime($item->statusDate)) : '-' }}
                            </td> --}}
                            <td class="small"
                                data-order="{{ $item->statusDate ? strtotime($item->statusDate) : strtotime($item->DateNotirepair) }}">
                                {{ $item->statusDate ? date('d-m-Y H:i', strtotime($item->statusDate)) : '-' }}
                            </td>
                            <td>
                                {{-- แสดง Badge สถานะ --}}
                                <span class="badge bg-{{ $color }} fw-normal">
                                    @if ($isClosed)
                                        <i class="bi bi-check-all"></i>
                                    @endif
                                    {{ $displayStatus }}
                                </span>
                            </td>
                            <td class="dt-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    {{-- CASE 1: ของยังมาไม่ถึงมือช่าง --}}
                                    @if ($status === 'ยังไม่ได้รับของ')
                                        <form action="{{ route('noti.accept', $item->NotirepairId) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-success btn-sm fw-bold d-flex align-items-center"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                onclick="return confirm('ยืนยันการรับของซ่อม?')">
                                                <i class="bi bi-box-seam me-1"></i> รับของซ่อม
                                            </button>
                                        </form>

                                        <form action="{{ route('noti.reject', $item->NotirepairId) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-danger btn-sm fw-bold d-flex align-items-center"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                onclick="return confirm('ยืนยันการปฏิเสธการซ่อมรหัส #{{ $item->NotirepairId }}?\n\n*หมายเหตุ: เมื่อปฏิเสธแล้ว ระบบจะทำการปิดงานนี้ทันทีและไม่สามารถกลับมาแก้ไขสถานะได้อีก')">
                                                <i class="bi bi-x-circle me-1"></i> ปฏิเสธซ่อม
                                            </button>
                                        </form>

                                        {{-- CASE 2: งานถูกปฏิเสธการซ่อม (Terminal State - จบงานแบบไม่ซ่อม) --}}
                                    @elseif ($status === 'ปฏิเสธการซ่อม')
                                        {{-- <button class="btn btn-sm btn-dark fw-bold" style="opacity: 0.8;" disabled>
                                            <i class="bi bi-x-circle-fill me-1"></i> ปิดงานเรียบร้อย
                                        </button> --}}
                                        <button class="btn btn-sm btn-light border text-success fw-bold" disabled>
                                            <i class="bi bi-check-circle-fill me-1"></i> ปิดงานเรียบร้อย
                                        </button>

                                        {{-- CASE 3: งานที่ดำเนินการเสร็จสิ้น/ปิดงานปกติ --}}
                                    @elseif ($isClosed)
                                        <button class="btn btn-sm btn-light border text-success fw-bold" disabled>
                                            <i class="bi bi-check-circle-fill me-1"></i> ปิดงานเรียบร้อย
                                        </button>

                                        {{-- CASE 4: สถานะระหว่างดำเนินการ (กำลังซ่อม / ส่ง Supplier) --}}
                                    @else
                                        @if (!$isRepaired)
                                            {{-- ปุ่มอัปเดตสถานะ (แสดงเฉพาะตอนที่ยังซ่อมไม่เสร็จ) --}}
                                            <a href="{{ route('noti.show_update_form', $item->NotirepairId) }}"
                                                class="btn btn-warning btn-sm shadow-sm fw-bold" title="อัปเดตสถานะการซ่อม">
                                                <i class="bi bi-pencil-square me-1"></i> อัปเดต
                                            </a>
                                        @else
                                            {{-- ซ่อมเสร็จแล้วแต่รอปิดงาน --}}
                                            <span class="badge bg-light text-muted border small d-flex align-items-center">
                                                <i class="bi bi-clock-history me-1"></i> รอหน้าร้านรับคืน
                                            </span>
                                        @endif

                                        {{-- ปุ่มแก้ไขข้อมูลพื้นฐาน (แสดงเฉพาะงานที่ยังไม่จบ) --}}
                                        <a href="{{ route('noti.edit', $item->NotirepairId) }}"
                                            class="btn btn-primary btn-sm fw-bold" title="แก้ไขข้อมูลพื้นฐาน">
                                            <i class="bi bi-pencil"></i> แก้ไข
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
{{-- Pagination สำหรับ Desktop (ให้แสดงเฉพาะจอ md ขึ้นไป) --}}
<div class="mt-4 d-flex justify-content-center d-none d-md-flex">
    {{ $noti->links('pagination::bootstrap-5') }}
</div>
    </div>

    {{-- Mobile View --}}
    <div class="d-md-none">
        @foreach ($noti as $item)
            @php
                //เปลี่ยนจาก $status = $item->status ?? 'ได้รับของเเล้ว';
                $status = $item->status ?? 'ยังไม่ได้รับของ';
                $isCompleted = str_contains($status, 'ซ่อมงานเสร็จแล้ว');

                // กำหนดสีและ Icon ตามสถานะ
                $statusConfig = match ($status) {
                    // 'ได้รับของแล้ว' => ['color' => 'primary', 'icon' => 'bi-box-seize', 'bg' => '#e7f1ff'],
                    'ยังไม่ได้รับของ' => ['color' => 'primary', 'icon' => 'bi-box-seize', 'bg' => '#e7f1ff'],
                    'กำลังดำเนินการซ่อม | ช่างStore' => ['color' => 'warning', 'icon' => 'bi-tools', 'bg' => '#fff3cd'],
                    'ส่งSuplierแล้ว' => ['color' => 'info', 'icon' => 'bi-truck', 'bg' => '#cff4fc'],
                    'ซ่อมงานเสร็จแล้ว | ช่างStore', 'ซ่อมงานเสร็จแล้ว | Supplier' => [
                        'color' => 'success',
                        'icon' => 'bi-check-circle-fill',
                        'bg' => '#d1e7dd',
                    ],
                    default => ['color' => 'secondary', 'icon' => 'bi-info-circle', 'bg' => '#f8f9fa'],
                };
            @endphp

            <div class="card mb-3 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                {{-- แถบสีด้านซ้ายบอกสถานะ --}}
                <div class="d-flex">
                    <div style="width: 6px; background-color: var(--bs-{{ $statusConfig['color'] }});"></div>

                    <div class="card-body p-3">
                        {{-- Header: ID และ Status Badge --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            {{-- <div>
                                
                                <span class="text-muted small fw-bold">#{{ $item->JobId }}</span>
                                <h6 class="fw-bold mb-0 text-dark">{{ $item->equipmentName }}</h6>
                            </div> --}}
                            <div>
                                <span class="badge bg-secondary mb-1">ลำดับที่ {{ ($noti->currentPage() - 1) * $noti->perPage() + $loop->iteration }}</span>
                                <br>
                                <span class="text-muted small fw-bold">#{{ $item->JobId }}</span>
                                <h6 class="fw-bold mb-0 text-dark">{{ $item->equipmentName }}</h6>
                            </div>
                            <span class="badge rounded-pill bg-{{ $statusConfig['color'] }} px-3 py-2">
                                <i class="bi {{ $statusConfig['icon'] }} me-1"></i>
                                {{ $isCompleted ? 'เสร็จสิ้น' : $status }}
                            </span>
                        </div>
                        {{-- รายละเอียด Location & Zone --}}
                        {{-- <div class="p-2 mb-2 rounded" style="background-color: #f8f9fa;">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                <span class="fw-bold small">สาขา: {{ $item->branchCode }} เอสพลานาด</span>
                            </div>
                            
                        </div> --}}
                        <div class="p-2 mb-2 rounded" style="background-color: #f8f9fa;">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                <span class="fw-bold small">
                                    สาขา: {{ $item->branchCode }}
                                    {{ $branchNames[$item->branchCode] ?? '' }}
                                </span>
                            </div>
                        </div>

                        {{-- รายละเอียดอาการ --}}
                        <p class="text-secondary small mb-3">
                            <i class="bi bi-chat-left-text me-1"></i> {{ $item->DeatailNotirepair }}
                        </p>

                        <hr class="my-2 opacity-25">


                        {{-- ค้นหาส่วนของปุ่มจัดการด้านล่างของการ์ด Mobile --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted" style="font-size: 0.75rem;">
                                <div><i class="bi bi-calendar3 me-1"></i>
                                    {{ date('d/m/y H:i', strtotime($item->DateNotirepair)) }}</div>
                            </div>

                            <div class="d-flex gap-2">
                                @if ($status === 'ยังไม่ได้รับของ')
                                    <form action="{{ route('noti.accept', $item->NotirepairId) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm fw-bold px-3"
                                            onclick="return confirm('ยืนยันการรับของซ่อม?')">
                                            รับของซ่อม
                                        </button>
                                    </form>
                                    <form action="{{ route('noti.reject', $item->NotirepairId) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm fw-bold px-3"
                                            onclick="return confirm('ยืนยันการปฏิเสธซ่อม? (งานจะถูกปิดทันที)')">
                                            ปฏิเสธซ่อม
                                        </button>
                                    </form>
                                @else
                                    {{-- <a href="{{ route('noti.edit', $item->NotirepairId) }}"
                                        class="btn btn-outline-primary btn-sm rounded-pill shadow-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a> --}}

                                    @if (!$isCompleted)
                                    <a href="{{ route('noti.edit', $item->NotirepairId) }}"
                                        class="btn btn-outline-primary btn-sm rounded-pill shadow-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                        <a href="{{ route('noti.show_update_form', $item->NotirepairId) }}"
                                            class="btn btn-warning btn-sm fw-bold px-3 shadow-sm"
                                            style="border-radius: 8px;">
                                            อัปเดต
                                        </a>
                                    @else
                                        <span class="text-success small fw-bold bg-light px-2 py-1 rounded">
                                            <i class="bi bi-check-all"></i> เรียบร้อย
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Pagination สำหรับ Mobile --}}
        <div class="d-flex justify-content-center py-3">
            {{ $noti->links('pagination::bootstrap-5') }}
        </div>
    </div>

    @push('scripts')
        <script>
     $(document).ready(function() {
    if (window.matchMedia('(min-width: 768px)').matches) {
        // 1. เริ่มทำงาน DataTable
        var table = $('#notiTable').DataTable({
            "searching": false,
            "paging": false, 
            "ordering": true,
            "info": false,
            "autoWidth": false,
            
            // ✅ แก้ไขตรงนี้: ใส่ [] เพื่อไม่ให้ DataTable จัดเรียงใหม่ตอนโหลดหน้า
            // มันจะแสดงผลตามลำดับที่ส่งมาจาก Controller (DB) ทันที
            "order": [], 

            "columnDefs": [
                { "targets": [0,1,3,4,5,6,7], "className": "dt-center" },
                // { "targets": 2, "className": "text-start" },
                { "targets": 3, "className": "text-start" },
                { "targets": 7, "className": "dt-center", "orderable": false } // ปุ่มจัดการห้าม Sort
            ],
            "language": { "emptyTable": "ไม่พบข้อมูล" }
        });

        // 2. ระบบ Highlight แถวที่เพิ่งอัปเดต
        const updatedId = "{{ session('updated_id') }}";
        if (updatedId) {
            let targetRow = $(`#row-${updatedId}`);
            if (targetRow.length) {
                // ใส่ Class สำหรับกะพริบสี (ต้องมี CSS highlight-update ในไฟล์ด้วย)
                targetRow.addClass('highlight-update'); 

                // เลื่อนหน้าจอไปหาแถวที่อัปเดต
                $('html, body').animate({
                    scrollTop: targetRow.offset().top - 150
                }, 600);

                // ลบ Class ออกหลังจากแสดงผลเสร็จ
                setTimeout(function() {
                    targetRow.removeClass('highlight-update');
                }, 2500);
            }
        }
    }
});
        </script>
    @endpush
    {{-- @push('scripts')
       
        <script>
            $(document).ready(function() {
                // --- ส่วนที่ 1: จัดการ DataTable (เดิมของคุณ) ---
                if (window.matchMedia('(min-width: 768px)').matches && $('#notiTable').length > 0) {
                    if ($.fn.DataTable.isDataTable('#notiTable')) {
                        $('#notiTable').DataTable().destroy();
                    }

                    $('#notiTable').DataTable({
                        "searching": false,
                        "paging": false,
                        "ordering": true,
                        "info": false,
                        "autoWidth": false,
                        "order": [
                            [5, "desc"]
                        ], // เรียงตาม "อัปเดตล่าสุด" จากภาพ
                        "columnDefs": [{
                                "targets": [0, 1, 3, 4, 5, 6, 7],
                                "className": "dt-center"
                            }, // ปรับ target ตามจำนวน column จริง
                            {
                                // "targets": 2,
                                "targets": [2],
                                "className": "text-start"
                            },
                            {
                                "targets": [7], // คอลัมน์จัดการ
                                "orderable": false, // ปิดการ Sort (ลูกศรจะหายไป)
                                "searchable": false // ไม่ให้ค้นหาข้อมูลในคอลัมน์นี้
                            }
                            // {
                            //     "orderable": false,
                            //     "targets": -1
                            // }
                        ],
                        "language": {
                            "emptyTable": "ไม่พบข้อมูล",
                            "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน"
                        }
                    });
                }

                // --- ส่วนที่ 2: ระบบ Highlight แถวที่เพิ่งอัปเดต (ผสมใหม่) ---
                // --- ส่วนที่ 2: ระบบ Highlight แถวที่เพิ่งอัปเดต ---
                const updatedId = "{{ session('updated_id') }}";
                if (updatedId) {
                    let targetRow = $(`#row-${updatedId}`);

                    if (targetRow.length) {
                        targetRow.addClass('highlight-update');

                        // เลื่อนหน้าจอไปหา
                        $('html, body').animate({
                            scrollTop: targetRow.offset().top - 150
                        }, 600); // เลื่อนหน้าจอเร็วขึ้นเล็กน้อย

                        // ลบ Class ออกหลังจาก 2.5 วินาที (เท่ากับเวลาใน CSS)
                        setTimeout(function() {
                            targetRow.removeClass('highlight-update');
                        }, 2500);
                    }
                }
            });
        </script>
    @endpush --}}
@endsection
