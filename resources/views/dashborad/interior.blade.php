@extends('layout.mainlayout')

@section('title', 'รายการแจ้งซ่อม (Interior)')

@section('content')

    <h5 class="fw-bold text-dark mb-3">
        <i class="bi bi-list-task"></i> รายการแจ้งซ่อม (Interior)
        @if (Auth::check())
            <span class="text-primary small fw-normal">({{ Auth::user()->staffname ?? 'Interior Staff' }})</span>
        @endif
    </h5>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search Form Section --}}
    <div class="card-body p-3 mb-3">
        {{-- action="" ว่างไว้เพื่อให้ Submit กลับมาที่ URL เดิม (/interior/noti) --}}
        <form action="" method="GET">
            <div class="row g-2 align-items-center">

                {{-- 1. ช่องค้นหา --}}
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

                {{-- 2. ตัวเลือกสถานะ --}}
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
                            $isRepaired = str_contains($status, 'ซ่อมงานเสร็จแล้ว');
                            $isClosed = $status === 'ได้รับของคืนเรียบร้อย';

                            // ชื่อสถานะแสดงผล
                            $displayStatus = $status;
                            if ($isClosed) {
                                // $displayStatus = 'ได้รับของคืนเรียบร้อย';
                                $displayStatus = 'สำเร็จ';
                            }

                            $color = match ($status) {
                                'ยังไม่ได้รับของ' => 'danger',
                                'ได้รับของแล้ว' => 'primary',
                                'กำลังดำเนินการซ่อม | ช่างStore' => 'warning',
                                'ส่งSuplierแล้ว' => 'info',
                                'ซ่อมงานเสร็จแล้ว | ช่างStore', 'ซ่อมงานเสร็จแล้ว | Supplier' => 'success',
                                'ปฏิเสธการซ่อม' => 'dark',
                                'ได้รับของคืนเรียบร้อย' => 'success',
                                default => 'secondary',
                            };
                        @endphp
                  
                        <tr id="row-{{ $item->JobId ?? $item->NotirepairId }}">
                            <td>
                                {{ ($noti->currentPage() - 1) * $noti->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <span class="text-primary fw-bold" style="font-size: 0.75rem;">
                                    #{{ $item->JobId ?? $item->NotirepairId }}
                                </span>
                            </td>

                            <td class="text-start small fw-bold">{{ $item->equipmentName }}</td>

                            <td class="text-start small">
                                <div style="white-space: normal; min-width: 250px;">
                                    {{ $item->DeatailNotirepair }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->branchCode }}</div>
                                <div class="small text-muted">{{ $branchNames[$item->branchCode] ?? 'ไม่พบชื่อสาขา' }}</div>
                            </td>

                            <td class="small">
                                {{ $item->DateNotirepair ? date('d-m-Y H:i', strtotime($item->DateNotirepair)) : '-' }}
                            </td>

                            <td class="small"
                                data-order="{{ $item->statusDate ? strtotime($item->statusDate) : strtotime($item->DateNotirepair) }}">
                                {{ $item->statusDate ? date('d-m-Y H:i', strtotime($item->statusDate)) : '-' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $color }} fw-normal">
                                    @if ($isClosed)
                                        <i class="bi bi-check-all"></i>
                                    @endif
                                    {{ $displayStatus }}
                                </span>
                            </td>
                            <td class="dt-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    {{-- CASE 1: ยังไม่รับของ --}}
                                    @if ($status === 'ยังไม่ได้รับของ')
                                        {{-- รับงาน --}}
                                        {{-- แก้ route เป็น interior.accept --}}
                                        <form action="{{ route('interior.accept', $item->NotirepairId) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm fw-bold d-flex align-items-center"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                onclick="return confirm('ยืนยันการรับงาน  รหัส #{{ $item->JobId }}?')">
                                                <i class="bi bi-box-seam me-1"></i> รับงาน
                                            </button>
                                        </form>
                            
                                        {{-- ปฏิเสธงาน --}}
                                        {{-- แก้ route เป็น interior.reject --}}
                                        <form action="{{ route('interior.reject', $item->NotirepairId) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm fw-bold d-flex align-items-center"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                onclick="return confirm('ยืนยันการปฏิเสธงานซ่อม?\n\n*ระบบจะปิดงานทันที')">
                                                <i class="bi bi-x-circle me-1"></i> ปฏิเสธ
                                            </button>
                                        </form>
                            
                                    {{-- CASE 2: ปฏิเสธการซ่อม --}}
                                    @elseif ($status === 'ปฏิเสธการซ่อม')
                                        <button class="btn btn-sm btn-light border text-secondary fw-bold" disabled>
                                            <i class="bi bi-x-circle-fill me-1"></i> ปิดงาน (ปฏิเสธ)
                                        </button>
                            
                                    {{-- CASE 3: งานปิดแล้ว --}}
                                    @elseif ($isClosed)
                                        <button class="btn btn-sm btn-light border text-success fw-bold" disabled>
                                            <i class="bi bi-check-circle-fill me-1"></i> ปิดงานเรียบร้อย
                                        </button>
                            
                                    {{-- CASE 4: ระหว่างดำเนินการ --}}
                                    @else
                                        @if (!$isRepaired)
                                            {{-- ปุ่มอัปเดต: แก้ route เป็น interior.show_update_form --}}
                                            <a href="{{ route('interior.show_update_form', $item->NotirepairId) }}"
                                                class="btn btn-warning btn-sm shadow-sm fw-bold" title="อัปเดตสถานะ">
                                                <i class="bi bi-pencil-square me-1"></i> อัปเดต
                                            </a>
                                        @else
                                            <span class="badge bg-light text-muted border small d-flex align-items-center">
                                                <i class="bi bi-clock-history me-1"></i> รอหน้าร้านกดปิดงาน
                                            </span>
                                        @endif
                            
                                        <a href="{{ route('interior.edit', $item->NotirepairId) }}"
                                            class="btn btn-primary btn-sm fw-bold" title="แก้ไขข้อมูล">
                                            <i class="bi bi-pencil"></i> เเก้ไข
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

    {{-- Pagination Desktop --}}
    <div class="mt-4 d-flex justify-content-center d-none d-md-flex">
        {{ $noti->links('pagination::bootstrap-5') }}
    </div>


    {{-- Mobile View --}}
    <div class="d-md-none">
        @foreach ($noti as $item)
            @php
                $status = $item->status ?? 'ยังไม่ได้รับของ';
                $isCompleted = str_contains($status, 'ซ่อมงานเสร็จแล้ว');

                $statusConfig = match ($status) {
                    'ยังไม่ได้รับของ' => ['color' => 'danger', 'icon' => 'bi-exclamation-circle-fill'],
                    'กำลังดำเนินการซ่อม | ช่างStore' => ['color' => 'warning', 'icon' => 'bi-tools'],
                    'ส่งSuplierแล้ว' => ['color' => 'info', 'icon' => 'bi-truck'],
                    'ซ่อมงานเสร็จแล้ว | ช่างStore', 'ซ่อมงานเสร็จแล้ว | Supplier', 'ได้รับของคืนเรียบร้อย' => [
                        'color' => 'success',
                        'icon' => 'bi-check-circle-fill',
                    ],
                    'ปฏิเสธการซ่อม' => ['color' => 'dark', 'icon' => 'bi-x-circle-fill'],
                    default => ['color' => 'secondary', 'icon' => 'bi-info-circle'],
                };
            @endphp

            <div class="card mb-3 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                {{-- แถบสีด้านซ้าย --}}
                <div class="d-flex">
                    <div style="width: 6px; background-color: var(--bs-{{ $statusConfig['color'] }});"></div>

                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-secondary mb-1">#{{ $item->JobId }}</span>
                                <h6 class="fw-bold mb-0 text-dark">{{ $item->equipmentName }}</h6>
                            </div>
                            <span class="badge rounded-pill bg-{{ $statusConfig['color'] }} px-2 py-1">
                                <i class="bi {{ $statusConfig['icon'] }} me-1"></i>
                                {{ $isCompleted ? 'เสร็จสิ้น' : $status }}
                            </span>
                        </div>

                        <div class="p-2 mb-2 rounded" style="background-color: #f8f9fa;">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                <span class="fw-bold small">
                                    {{ $item->branchCode }} : {{ $branchNames[$item->branchCode] ?? '' }}
                                </span>
                            </div>
                        </div>

                        <p class="text-secondary small mb-3">
                            <i class="bi bi-chat-left-text me-1"></i> {{ $item->DeatailNotirepair }}
                        </p>

                        <hr class="my-2 opacity-25">

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted" style="font-size: 0.75rem;">
                                <div><i class="bi bi-calendar3 me-1"></i>
                                    {{ date('d/m/y H:i', strtotime($item->DateNotirepair)) }}</div>
                            </div>

                            <div class="d-flex gap-2">
                                @if ($status === 'ยังไม่ได้รับของ')
                                    {{-- แก้ route เป็น interior.accept --}}
                                    <form action="{{ route('interior.accept', $item->NotirepairId) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm fw-bold px-3"
                                            onclick="return confirm('ยืนยันรับงาน?')">รับงาน</button>
                                    </form>
                                    
                                    {{-- แก้ route เป็น interior.reject --}}
                                    <form action="{{ route('interior.reject', $item->NotirepairId) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm fw-bold px-3"
                                            onclick="return confirm('ยืนยันปฏิเสธ?')">ปฏิเสธ</button>
                                    </form>
                                @elseif($status === 'ปฏิเสธการซ่อม' || $status === 'ได้รับของคืนเรียบร้อย')
                                     {{-- ไม่แสดงปุ่ม --}}
                                @else
                                    <a href="{{ route('interior.edit', $item->NotirepairId) }}"
                                        class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    @if (!$isCompleted)
                                        <a href="{{ route('interior.show_update_form', $item->NotirepairId) }}"
                                            class="btn btn-warning btn-sm fw-bold px-3">
                                            อัปเดต
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-center py-3">
            {{ $noti->links('pagination::bootstrap-5') }}
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                if (window.matchMedia('(min-width: 768px)').matches) {
                    var table = $('#notiTable').DataTable({
                        "searching": false,
                        "paging": false,
                        "ordering": true,
                        "info": false,
                        "autoWidth": false,
                        // ปิดการ Sort อัตโนมัติ เพื่อให้ใช้ลำดับจาก Controller
                        "order": [], 
                        "columnDefs": [
                            { "targets": [0, 1, 4, 5, 6, 7, 8], "className": "dt-center" },
                            { "targets": [2, 3], "className": "text-start" },
                            { "targets": 8, "orderable": false } // ปุ่มจัดการห้าม Sort
                        ],
                        "language": {
                            "emptyTable": "ไม่พบข้อมูลรายการแจ้งซ่อม (Interior)"
                        }
                    });

                    // Highlight Updated Row Logic
                    const updatedId = "{{ session('updated_id') }}";
                    if (updatedId) {
                        let targetRow = $(`#row-${updatedId}`);
                        if (targetRow.length) {
                            targetRow.addClass('highlight-update');
                            $('html, body').animate({
                                scrollTop: targetRow.offset().top - 150
                            }, 600);
                            setTimeout(function() {
                                targetRow.removeClass('highlight-update');
                            }, 2500);
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection