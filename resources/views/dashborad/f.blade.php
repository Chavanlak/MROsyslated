@extends('layout.mainlayout')
@section('title', 'ตารางติดตามงานซ่อม')

@section('content')
    <style>
        /* --- Google Search Style Integration --- */
        .google-search-container {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 2rem;
        }

        .google-search-bar {
            background: #fff;
            border: 1px solid #dfe1e5;
            box-shadow: none;
            border-radius: 24px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            padding: 5px 8px;
            display: flex;
            align-items: center;
            width: 100%; 
            max-width: 850px; 
        }

        .google-search-bar:hover,
        .google-search-bar:focus-within {
            box-shadow: 0 1px 6px rgba(32, 33, 36, .28);
            border-color: rgba(223, 225, 229, 0);
        }

        .google-search-input {
            border: none;
            box-shadow: none !important;
            flex-grow: 1;
            padding-left: 10px;
            font-size: 1rem;
            min-width: 0;
        }

        .google-search-divider {
            border-left: 1px solid #dfe1e5;
            height: 28px;
            margin: 0 10px;
        }

        .google-search-select {
            border: none;
            box-shadow: none !important;
            background-color: transparent;
            max-width: 180px; 
            cursor: pointer;
            color: #5f6368;
            outline: none;
        }

        .btn-google-search {
            background-color: #1a73e8;
            color: white;
            border-radius: 20px;
            padding: 8px 24px;
            border: none;
            font-weight: 500;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .btn-google-search:hover {
            background-color: #1557b0;
            color: white;
        }

        .btn-clear-search {
            color: #70757a;
            background: transparent;
            border: none;
            padding: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s;
            text-decoration: none; 
        }
        .btn-clear-search:hover {
            background-color: #f1f3f4;
            color: #333;
        }

        /* --- Original Table Styles --- */
        #officeTrackingTable thead th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
            text-align: center !important;
            vertical-align: middle;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        .extra-small {
            font-size: 0.72rem;
            line-height: 1;
        }

        .badge-status {
            min-width: 120px;
            font-weight: 500;
            padding: 0.6em 0.8em;
            border-radius: 6px;
        }

        .dataTables_wrapper .dataTables_filter {
            display: none;
        }
    </style>

    <div class="container-fluid p-0">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-list-task text-primary me-2"></i> ตารางติดตามงาน (ธุรการ)
            </h5>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-5">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-3 text-center text-md-start">
                        <small class="text-muted d-block">งานทั้งหมด</small>
                        <span class="h5 fw-bold">{{ number_format($totalCount) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #ffc107;">
                    <div class="card-body p-3 text-center text-md-start">
                        <small class="text-muted d-block text-warning">รอดำเนินการ</small>
                        <span class="h5 fw-bold text-warning">{{ number_format($pendingCount) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #198754;">
                    <div class="card-body p-3 text-center text-md-start">
                        <small class="text-muted d-block text-success">ปิดงานแล้ว</small>
                        <span class="h5 fw-bold text-success">{{ number_format($completedCount ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Google Style Search Form --}}
        <div class="google-search-container mb-5">
            <form id="searchForm" action="{{ route('officer.tracking') }}" method="GET" class="w-100"> 
                <div class="google-search-bar">
                    <span class="ps-3 text-muted"><i class="bi bi-search"></i></span>
                    
                    {{-- Input ค้นหา --}}
                    <input type="text" name="search" class="form-control google-search-input"
                        placeholder="ค้นหา รหัสแจ้งซ่อม, สาขา, อุปกรณ์..." 
                        value="{{ request('search') }}" autocomplete="off">

                    {{-- ปุ่มล้างค่า --}}
                    @if (request()->anyFilled(['search', 'status']))
                        <a href="{{ route('officer.tracking') }}" class="btn-clear-search me-1" title="ล้างค่า">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif

                    <div class="google-search-divider d-none d-md-block"></div>

                    {{-- Select สถานะ --}}
                    <div class="d-none d-md-block">
                        <select name="status" class="form-select google-search-select status-trigger">
                            <option value="">ทุกสถานะ</option>
                            @foreach (['ยังไม่ได้รับของ', 'ได้รับของแล้ว', 'ส่งSuplierแล้ว', 'กำลังดำเนินการซ่อม', 'ซ่อมงานเสร็จแล้ว', 'ปิดงานเรียบร้อย'] as $st)
                                {{-- ใช้ str_contains เพื่อให้ค้นหาได้กว้างขึ้นใน value แต่โชว์ข้อความสั้นๆ --}}
                                <option value="{{ $st }}" {{ str_contains(request('status'), $st) ? 'selected' : '' }}>
                                    {{ Str::limit($st, 25) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-google-search ms-2">
                        ค้นหา
                    </button>
                </div>

                {{-- Mobile Select --}}
                <div class="d-md-none mt-3 px-2 w-100">
                    <select name="status" class="form-select border-0 shadow-sm status-trigger" style="border-radius: 20px;">
                        <option value="">สถานะ: ทั้งหมด</option>
                        @foreach (['ยังไม่ได้รับของ', 'ได้รับของแล้ว', 'ส่งSuplierแล้ว', 'กำลังดำเนินการซ่อม', 'ซ่อมงานเสร็จแล้ว', 'ปิดงานเรียบร้อย'] as $st)
                            <option value="{{ $st }}" {{ str_contains(request('status'), $st) ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        {{-- Desktop View --}}
        <div class="card shadow-sm d-none d-md-block border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="officeTrackingTable" class="table table-hover align-middle mb-0 w-100">
                        <thead>
                            <tr class="text-center align-middle">
                                <th>ลำดับ</th>
                                <th style="width: 8%;">รหัสแจ้งซ่อม</th>
                                <th style="width: 10%;">สาขา</th>
                                <th class="text-start" style="width: 15%;">อุปกรณ์</th>
                                <th >รายละเอียด</th> 
                                <th style="width: 14%;">สถานะปัจจุบัน</th>
                                <th style="width: 8%;">วันที่รับของซ่อม</th>
                                <th style="width: 8%;">ผู้รับของซ่อม</th>
                                <th style="width: 6%;">อัปเดตล่าสุด</th>
                                <th style="width: 8%;">วันที่ปิดงาน/โดย</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($jobs as $job)
                                @php
                                    $currentStatus = trim($job->current_status);
                                    
                                    // --- LOGIC สถานะแบบใหม่ (Group คำที่คล้ายกัน) ---
                                    $statusConfig = match (true) {
                                        // 1. ยกเลิก / ปฏิเสธ
                                        str_contains($currentStatus, 'ปฏิเสธ') || str_contains($currentStatus, 'ยกเลิก')
                                            => ['bg' => 'danger', 'icon' => 'bi-x-circle-fill', 'text' => 'white', 'border' => '#dc3545'],

                                        // 2. ปิดงานแล้ว (จบกระบวนการ)
                                        str_contains($currentStatus, 'ปิดงาน') 
                                            => ['bg' => 'success', 'icon' => 'bi-check-circle-fill', 'text' => 'white', 'border' => '#198754'],

                                        // 3. ซ่อมเสร็จ (รอส่งคืน หรือ รออนุมัติปิดงาน)
                                        str_contains($currentStatus, 'ซ่อมงานเสร็จ') || str_contains($currentStatus, 'รอส่งคืน')
                                            => ['bg' => 'success', 'icon' => 'bi-check-circle', 'text' => 'white', 'border' => '#198754'],

                                        // 4. Supplier / ภายนอก
                                        str_contains($currentStatus, 'Supplier') || str_contains($currentStatus, 'Suplier') || str_contains($currentStatus, 'ส่งภายนอก')
                                            => ['bg' => 'info', 'icon' => 'bi-truck', 'text' => 'dark', 'border' => '#0dcaf0'],

                                        // 5. กำลังซ่อม / ดำเนินการ (ภายใน)
                                        str_contains($currentStatus, 'กำลังดำเนินการ') || str_contains($currentStatus, 'ช่าง')
                                            => ['bg' => 'warning', 'icon' => 'bi-tools', 'text' => 'dark', 'border' => '#ffc107'],

                                        // 6. รับของแล้ว (อยู่ในคิว)
                                        $currentStatus === 'ได้รับของแล้ว' || str_contains($currentStatus, 'รับของ')
                                            => ['bg' => 'primary', 'icon' => 'bi-box-seam', 'text' => 'white', 'border' => '#0d6efd'],

                                        // Default: ยังไม่รับ / รอดำเนินการ
                                        default => ['bg' => 'secondary', 'icon' => 'bi-hourglass-split', 'text' => 'white', 'border' => '#6c757d'],
                                    };
                                @endphp
                                <tr>
                                    <td>{{ ($jobs->currentPage() - 1) * $jobs->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <span class="text-primary fw-bold" style="font-size: 0.75rem;">
                                            #{{ $job->JobId ?? $job->NotirepairId }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $job->branchCode }}</div>
                                        <div class="small text-muted">
                                            {{ $branchNames[$job->branchCode] ?? 'ไม่พบชื่อสาขา' }}</div>
                                    </td>
                                    <td class="text-start small">
                                        <div class="fw-medium text-dark" style="white-space: normal;">
                                            {{ $job->equipmentName }}
                                        </div>
                                    </td>
                                    <td class="text-start small">
                                        <div style="white-space: normal; min-width: 200px;">
                                            {{ $job->DeatailNotirepair }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-status bg-{{ $statusConfig['bg'] }} text-{{ $statusConfig['text'] }} shadow-sm border border-white">
                                            <i class="{{ $statusConfig['icon'] }} me-1"></i> {{ $currentStatus }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($job->received_at)
                                            <div class="small">{{ date('d/m/Y', strtotime($job->received_at)) }}</div>
                                            <div class="text-muted extra-small">
                                                {{ date('H:i', strtotime($job->received_at)) }}</div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $job->receiver_name ?? '-' }}</td>
                                    <td>
                                        <div class="small">
                                            {{ $job->last_update ? date('d/m/Y', strtotime($job->last_update)) : '-' }}
                                        </div>
                                        <div class="text-muted extra-small">
                                            {{ $job->last_update ? date('H:i', strtotime($job->last_update)) : '' }}</div>
                                    </td>
                                    <td>
                                        @if ($job->closedJobs !== 'ยังไม่ปิดงาน' || str_contains($currentStatus, 'ปฏิเสธ'))
                                            @if ($job->DateCloseJobs)
                                                <div class="text-success fw-bold small">
                                                    {{ date('d/m/Y H:i', strtotime($job->DateCloseJobs)) }}</div>
                                            @endif
                                            <div class="text-muted extra-small">
                                                <i class="bi bi-person-check"></i>
                                                {{ Str::limit($job->closer_name ?? '-', 20) }}
                                            </div>
                                        @else
                                            <span class="badge bg-light text-muted fw-normal border extra-small">ยังไม่ปิดงาน</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Mobile View --}}
        <div class="d-md-none">
            @forelse ($jobs as $job)
                @php
                    $currentStatus = trim($job->current_status);
                    
                    // --- ใช้ Logic เดียวกับ Desktop ---
                    $statusConfig = match (true) {
                        str_contains($currentStatus, 'ปฏิเสธ') || str_contains($currentStatus, 'ยกเลิก')
                            => ['bg' => 'danger', 'icon' => 'bi-x-circle-fill', 'text' => 'text-danger', 'border' => '#dc3545', 'badge_text' => 'white'],

                        str_contains($currentStatus, 'ปิดงาน') 
                            => ['bg' => 'success', 'icon' => 'bi-check-circle-fill', 'text' => 'text-success', 'border' => '#198754', 'badge_text' => 'white'],

                        str_contains($currentStatus, 'ซ่อมงานเสร็จ') || str_contains($currentStatus, 'รอส่งคืน')
                            => ['bg' => 'success', 'icon' => 'bi-check-circle', 'text' => 'text-success', 'border' => '#198754', 'badge_text' => 'white'],

                        str_contains($currentStatus, 'Supplier') || str_contains($currentStatus, 'Suplier')
                            => ['bg' => 'info', 'icon' => 'bi-truck', 'text' => 'text-info', 'border' => '#0dcaf0', 'badge_text' => 'dark'],

                        str_contains($currentStatus, 'กำลังดำเนินการ') || str_contains($currentStatus, 'ช่าง')
                            => ['bg' => 'warning', 'icon' => 'bi-tools', 'text' => 'text-warning', 'border' => '#ffc107', 'badge_text' => 'dark'],

                        $currentStatus === 'ได้รับของแล้ว' 
                            => ['bg' => 'primary', 'icon' => 'bi-box-seam', 'text' => 'text-primary', 'border' => '#0d6efd', 'badge_text' => 'white'],

                        default => ['bg' => 'secondary', 'icon' => 'bi-hourglass-split', 'text' => 'text-secondary', 'border' => '#6c757d', 'badge_text' => 'white'],
                    };

                    $isClosed = $job->closedJobs !== 'ยังไม่ปิดงาน';
                @endphp

                <div class="card mb-3 border-0 shadow-sm"
                    style="border-radius: 15px; border-left: 5px solid {{ $statusConfig['border'] }} !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fw-bold text-primary">#{{ $job->JobId ?? $job->NotirepairId }}</span>
                                <div class="h6 fw-bold mt-1 mb-0">{{ $job->equipmentName }}</div>
                            </div>
                            <span class="badge bg-light text-dark border">{{ $job->branchCode }}</span>
                        </div>

                        <div class="mb-2 small">
                            <span class="text-muted">สถานะ:</span>
                            {{-- กรณีสีเหลือง/ฟ้า ให้ใช้ text-dark เพื่อให้อ่านง่าย ถ้าสีเข้มใช้สีตาม status --}}
                            <span class="badge bg-{{ $statusConfig['bg'] }} text-{{ $statusConfig['badge_text'] }} fw-normal">
                                <i class="{{ $statusConfig['icon'] }}"></i> {{ $currentStatus }}
                            </span>
                        </div>

                        <div class="mb-2 small text-muted">
                            {{ $job->DeatailNotirepair }}
                        </div>

                        <div class="p-2 mb-2 bg-light rounded-3 extra-small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">ผู้รับของ:</span>
                                <span class="text-dark fw-medium">{{ $job->receiver_name ?? '-' }}</span>
                            </div>
                            @if ($isClosed || str_contains($currentStatus, 'ปฏิเสธ'))
                                <div class="d-flex justify-content-between border-top pt-1 mt-1">
                                    <span class="text-muted">ผู้ปิดงาน:</span>
                                    <span class="text-dark fw-medium">{{ $job->closer_name ?? '-' }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="row g-0 pt-2 border-top text-center">
                            <div class="col-6 border-end">
                                <small class="text-muted d-block extra-small">อัปเดตล่าสุด</small>
                                <small class="fw-bold">{{ $job->last_update ? date('d/m/y H:i', strtotime($job->last_update)) : '-' }}</small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block extra-small">วันที่ปิดงาน</small>
                                @if ($isClosed || str_contains($currentStatus, 'ปฏิเสธ'))
                                    <small class="text-success fw-bold">
                                        {{ $job->DateCloseJobs ? date('d/m/y', strtotime($job->DateCloseJobs)) : 'จบงาน' }}
                                    </small>
                                @else
                                    <small class="text-danger fw-bold">ยังไม่ปิดงาน</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 bg-white rounded shadow-sm">ไม่พบข้อมูล</div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $jobs->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // 1. สั่ง Submit Form อัตโนมัติเมื่อเลือก Dropdown
                $('.status-trigger').on('change', function() {
                    $('#searchForm').submit();
                });

                // 2. ตั้งค่า DataTables (เฉพาะ Desktop)
                if ($('#officeTrackingTable').length > 0 && window.innerWidth >= 768) {
                    var table = $('#officeTrackingTable').DataTable({
                        "paging": false,
                        "info": false, 
                        "searching": false, 
                        "ordering": true, 
                        "order": [
                            [6, "desc"] // เรียงตามวันที่รับของล่าสุดก่อน
                        ], 
                        "autoWidth": false,
                        "responsive": true,
                        "language": {
                            "emptyTable": "ไม่พบข้อมูลรายการแจ้งซ่อม",
                            "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน"
                        }
                    });
                    
                    $('#officeTrackingTable thead th').addClass('text-center align-middle');
                }
            });
        </script>
    @endpush
@endsection