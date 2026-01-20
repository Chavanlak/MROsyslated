@extends('layout.mainlayout')
@section('title', 'ตารางติดตามงานซ่อม')

@section('content')
    <style>
        /* ปรับแต่งหัวตารางและข้อมูลให้ดูเป็นระเบียบ */
        #officeTrackingTable thead th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
            text-align: center !important;
            vertical-align: middle;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        /* ปรับปรุงความสวยงามของสถานะและตัวอักษรเล็ก */
        .extra-small {
            font-size: 0.72rem;
            line-height: 1;
        }

        .badge-status {
            min-width: 100px;
            font-weight: 500;
        }

        /* ให้ DataTable แสดงผลเต็มความกว้างและจัดการช่องว่าง */
        .dataTables_wrapper .dataTables_filter {
            display: none;
            /* ซ่อนช่องค้นหาของ DataTable เพราะเราใช้ Form ด้านบนแล้ว */
        }

        .text-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* โชว์ 2 บรรทัด */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            line-height: 1.4;
            max-width: 300px;
            /* ปรับความกว้างสูงสุดตามใจชอบ */
        }
    </style>

    <div class="container-fluid p-0">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-list-task text-primary me-2"></i> ตารางติดตามงาน (ธุรการ)
            </h5>
            {{-- <button class="btn btn-outline-secondary btn-sm shadow-sm" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> รีเฟรช
        </button> --}}
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
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
        </div>

        {{-- Search Form --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-body p-3">
                <form action="{{ route('officer.tracking') }}" method="GET" class="row g-2">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0"
                                placeholder="ค้นหา JobId, สาขา, อุปกรณ์..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="status" class="form-select">
                            <option value="">ทุกสถานะ</option>
                            @foreach (['ยังไม่ได้รับของ', 'ได้รับของแล้ว', 'ส่งSuplierแล้ว', 'กำลังดำเนินการซ่อม | ช่างStore', 'ซ่อมงานเสร็จแล้ว | ช่างStore', 'ซ่อมงานเสร็จแล้ว | Supplier', 'ปิดงานเรียบร้อย'] as $st)
                                <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                    {{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">ค้นหา</button>
                    </div>
                    @if (request()->anyFilled(['search', 'status']))
                        <div class="col-12 col-md-2">
                            <a href="{{ route('officer.tracking') }}" class="btn btn-light w-100 text-muted">ล้างค่า</a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Desktop View --}}
        <div class="card shadow-sm d-none d-md-block border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="officeTrackingTable" class="table table-hover align-middle mb-0 w-100">
                        <thead>
                            <tr class="text-center align-middle">
                                <th style="width: 8%;">JobId</th>
                                <th style="width: 10%;">สาขา</th>
                                <th class="text-start" style="width: 15%;">อุปกรณ์</th>
                                
                                {{-- จุดสำคัญ: ให้พื้นที่รายละเอียด 35% ของหน้าจอ --}}
                                <th style="width: 35%;">รายละเอียด</th> 
                                <th style="width: 10%;">สถานะปัจจุบัน</th>
                                <th style="width: 8%;">รับของ</th>
                                <th style="width: 8%;">ผู้รับ</th>
                                <th style="width: 6%;">อัปเดต</th>
                                <th style="width: 8%;">ปิดงาน</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($jobs as $job)
                                @php
                                    $currentStatus = trim($job->current_status);

                                    // เช็คว่าเป็นเคสปฏิเสธซ่อมหรือไม่
                                    $isRefused = str_contains($currentStatus, 'ปฏิเสธการซ่อม');

                                    // กำหนดสีและไอคอน (เพิ่ม case 'ปฏิเสธการซ่อม' เข้าไป)
                                    $statusStyle = match (true) {
                                        $isRefused => [
                                            'bg' => 'dark',
                                            'icon' => 'bi-x-circle-fill',
                                            'text' => 'white',
                                        ], // สีดำ หรือใช้ 'danger' ถ้าอยากได้สีแดง
                                        $currentStatus === 'ยังไม่ได้รับของ' => [
                                            'bg' => 'secondary',
                                            'icon' => 'bi-clock-history',
                                            'text' => 'white',
                                        ],
                                        $currentStatus === 'ได้รับของแล้ว' => [
                                            'bg' => 'primary',
                                            'icon' => 'bi-box-seam',
                                            'text' => 'white',
                                        ],
                                        $currentStatus === 'ส่งSuplierแล้ว' => [
                                            'bg' => 'info',
                                            'icon' => 'bi-truck',
                                            'text' => 'dark',
                                        ],
                                        str_contains($currentStatus, 'กำลังดำเนินการซ่อม') => [
                                            'bg' => 'warning',
                                            'icon' => 'bi-tools',
                                            'text' => 'dark',
                                        ],
                                        str_contains($currentStatus, 'ซ่อมงานเสร็จแล้ว') => [
                                            'bg' => 'success',
                                            'icon' => 'bi-check-circle-fill',
                                            'text' => 'white',
                                        ],
                                        $currentStatus === 'ปิดงานเรียบร้อย' => [
                                            'bg' => 'success',
                                            'icon' => 'bi-check-all',
                                            'text' => 'white',
                                        ], // ปรับเป็นสีเขียว (success) หรือ dark ตามชอบ
                                        default => ['bg' => 'light', 'icon' => 'bi-question-circle', 'text' => 'dark'],
                                    };
                                @endphp
                                <tr>
                                    <td class="fw-bold text-primary" >#{{ $job->JobId ?? $job->NotirepairId }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $job->branchCode }}</div>
                                        <div class="small text-muted">
                                            {{ $branchNames[$job->branchCode] ?? 'ไม่พบชื่อสาขา' }}</div>
                                    </td>
                                    <td class="text-start">
                                        <div class="fw-medium text-dark text-truncate" style="max-width: 180px;"
                                            title="{{ $job->equipmentName }}">
                                            {{ $job->equipmentName }}
                                        </div>
                                    </td>

                                    {{-- จุดที่แก้ไข: แสดงรายละเอียดทั้งหมด (ลบ text-truncate ออก) --}}
                                    {{-- <td class="text-start">
                                        <div style="white-space: normal; min-width: 250px;">
                                            {{ $job->DeatailNotirepair }}
                                        </div>
                                    </td> --}}
                                    <td class="text-start">
                                        {{-- ใช้ div ครอบเพื่อจัดสไตล์ --}}
                                        <div style="
                                            display: -webkit-box;
                                            -webkit-line-clamp: 2;           /* โชว์แค่ 2 บรรทัด (แก้เลขได้ถ้าอยากได้ 3) */
                                            -webkit-box-orient: vertical;
                                            overflow: hidden;
                                            text-overflow: ellipsis;
                                            white-space: normal;             /* ให้ตัดคำลงบรรทัดใหม่ได้ */
                                            max-width: 350px;                /* จำกัดความกว้างไม่ให้ดันช่องอื่น */
                                            line-height: 1.5;                /* ระยะห่างบรรทัดให้อ่านง่าย */
                                            height: auto;
                                        " title="{{ $job->DeatailNotirepair }}">  {{-- ใส่ title เพื่อให้เมาส์ชี้แล้วเห็นข้อความเต็ม --}}
                                            
                                            {{ $job->DeatailNotirepair }}
                                            
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-status bg-{{ $statusStyle['bg'] }} text-{{ $statusStyle['text'] }} shadow-sm">
                                            <i class="{{ $statusStyle['icon'] }} me-1"></i> {{ $currentStatus }}
                                        </span>
                                    </td>

                                    {{-- ส่วนอื่นๆ ของตารางเหมือนเดิม --}}
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
                                        {{-- ถ้าปฏิเสธซ่อม หรือ ปิดงานแล้ว ให้แสดงข้อมูล --}}
                                        @if ($job->closedJobs !== 'ยังไม่ปิดงาน' || $isRefused)
                                            @if ($job->DateCloseJobs)
                                                <div class="text-success fw-bold small">
                                                    {{ date('d/m/Y', strtotime($job->DateCloseJobs)) }}</div>
                                            @endif
                                            <div class="text-muted extra-small">
                                                <i class="bi bi-person-check"></i>
                                                {{ Str::limit($job->closer_name ?? '-', 12) }}
                                            </div>
                                        @else
                                            <span
                                                class="badge bg-light text-muted fw-normal border extra-small">ยังไม่ปิดงาน</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                            {{-- @forelse ($jobs as $job)
                            @php
                                $currentStatus = trim($job->current_status);
                                $statusStyle = match ($currentStatus) {
                                    'ยังไม่ได้รับของ' => ['bg' => 'secondary', 'icon' => 'bi-clock-history', 'text' => 'white'],
                                    'ได้รับของแล้ว' => ['bg' => 'primary', 'icon' => 'bi-box-seam', 'text' => 'white'],
                                    'ส่งSuplierแล้ว' => ['bg' => 'info', 'icon' => 'bi-truck', 'text' => 'dark'],
                                    'กำลังดำเนินการซ่อม | ช่างStore' => ['bg' => 'warning', 'icon' => 'bi-tools', 'text' => 'dark'],
                                    'ซ่อมงานเสร็จแล้ว | ช่างStore', 'ซ่อมงานเสร็จแล้ว | Supplier' => ['bg' => 'success', 'icon' => 'bi-check-circle-fill', 'text' => 'white'],
                                    'ปิดงานเรียบร้อย' => ['bg' => 'dark', 'icon' => 'bi-check-all', 'text' => 'white'],
                                    default => ['bg' => 'light', 'icon' => 'bi-question-circle', 'text' => 'dark'],
                                };
                            @endphp
                            <tr>
                                <td class="fw-bold text-primary">#{{$job->JobId ?? $job->NotirepairId}}</td>
                         
                                <td>
                                    <div class="fw-bold text-dark">{{ $job->branchCode }}</div>
                             
                                    <div class="small text-muted">{{ $branchNames[$job->branchCode] ?? 'ไม่พบชื่อสาขา' }}</div>
                                </td>
                                <td class="text-start">
                                    <div class="fw-medium text-dark text-truncate" style="max-width: 180px;" title="{{ $job->equipmentName }}">
                                        {{ $job->equipmentName }}
                                    </div>
                                </td>
                                <td>  <div class="text-truncate" style="max-width: 250px;" title="{{ $job->DeatailNotirepair }}">
                                    {{ $job->DeatailNotirepair }}
                                </div></td>
                                <td>
                                    <span class="badge badge-status bg-{{ $statusStyle['bg'] }} text-{{ $statusStyle['text'] }} shadow-sm">
                                        <i class="{{ $statusStyle['icon'] }} me-1"></i> {{ $currentStatus }}
                                    </span>
                                </td>
                                <td>
                                    @if ($job->received_at)
                                        <div class="small">{{ date('d/m/Y', strtotime($job->received_at)) }}</div>
                                        <div class="text-muted extra-small">{{ date('H:i', strtotime($job->received_at)) }}</div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="small">{{ $job->receiver_name ?? '-' }}</td>
                                <td>
                                    <div class="small">{{ $job->last_update ? date('d/m/Y', strtotime($job->last_update)) : '-' }}</div>
                                    <div class="text-muted extra-small">{{ $job->last_update ? date('H:i', strtotime($job->last_update)) : '' }}</div>
                                </td>
                                <td>
                                    @if ($job->closedJobs !== 'ยังไม่ปิดงาน')
                                        <div class="text-success fw-bold small">{{ date('d/m/Y', strtotime($job->DateCloseJobs)) }}</div>
                                        <div class="text-muted extra-small">
                                            <i class="bi bi-person-check"></i> {{ Str::limit($job->closer_name, 12) }}
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted fw-normal border extra-small">ยังไม่ปิดงาน</span>
                                    @endif
                                </td>
                               
                            </tr>
                        @empty
                        @endforelse --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Mobile View --}}
        {{-- <div class="d-md-none">
        @forelse ($jobs as $job)
            <div class="card mb-3 border-0 shadow-sm"
                style="border-radius: 15px; border-left: 5px solid {{ $job->closedJobs !== 'ยังไม่ปิดงาน' ? '#198754' : '#ffc107' }} !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="fw-bold text-primary">#{{ $job->JobId ?? $job->NotirepairId }}</span>
                            <div class="h6 fw-bold mt-1 mb-0">{{ $job->equipmentName }}</div>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $job->branchCode }}</span>
                    </div>

                    <div class="mb-2 small">
                        <span class="text-muted">สถานะ:</span> <span class="fw-bold text-dark">{{ $job->current_status }}</span>
                    </div>

                    <div class="p-2 mb-2 bg-light rounded-3 extra-small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">ผู้รับของ:</span>
                            <span class="text-dark fw-medium">{{ $job->receiver_name ?? '-' }}</span>
                        </div>
                        @if ($job->closedJobs !== 'ยังไม่ปิดงาน')
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
                            @if ($job->closedJobs !== 'ยังไม่ปิดงาน')
                                <small class="text-success fw-bold">{{ date('d/m/y', strtotime($job->DateCloseJobs)) }}</small>
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
    </div> --}}
        <div class="d-md-none">
            @forelse ($jobs as $job)
                @php
                    // ตรวจสอบสถานะ
                    $currentStatus = trim($job->current_status);
                    $isRefused = str_contains($currentStatus, 'ปฏิเสธการซ่อม');
                    $isClosed = $job->closedJobs !== 'ยังไม่ปิดงาน';

                    // กำหนดสีขอบซ้าย (ถ้าปิดงาน หรือ ปฏิเสธซ่อม ให้เป็นสีเขียว)
                    $borderColor = $isClosed || $isRefused ? '#198754' : '#ffc107';

                    // ถ้าอยากให้ ปฏิเสธซ่อม เป็นสีเทาเข้ม ให้แก้บรรทัดบนเป็น:
                    // $borderColor = $isRefused ? '#212529' : ($isClosed ? '#198754' : '#ffc107');

                @endphp

                <div class="card mb-3 border-0 shadow-sm"
                    style="border-radius: 15px; border-left: 5px solid {{ $borderColor }} !important;">
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
                            {{-- เพิ่มสีตัวอักษรให้ชัดเจนขึ้น --}}
                            <span class="fw-bold {{ $isRefused ? 'text-danger' : 'text-dark' }}">
                                {{ $job->current_status }}
                            </span>
                        </div>

                        {{-- เพิ่มส่วนแสดงรายละเอียดในมือถือด้วย (เผื่ออยากเห็น) --}}
                        <div class="mb-2 small text-muted">
                            {{ $job->DeatailNotirepair }}
                        </div>

                        <div class="p-2 mb-2 bg-light rounded-3 extra-small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">ผู้รับของ:</span>
                                <span class="text-dark fw-medium">{{ $job->receiver_name ?? '-' }}</span>
                            </div>
                            @if ($isClosed || $isRefused)
                                <div class="d-flex justify-content-between border-top pt-1 mt-1">
                                    <span class="text-muted">ผู้ปิดงาน/ล่าสุด:</span>
                                    <span class="text-dark fw-medium">{{ $job->closer_name ?? '-' }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="row g-0 pt-2 border-top text-center">
                            <div class="col-6 border-end">
                                <small class="text-muted d-block extra-small">อัปเดตล่าสุด</small>
                                <small
                                    class="fw-bold">{{ $job->last_update ? date('d/m/y H:i', strtotime($job->last_update)) : '-' }}</small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block extra-small">วันที่ปิดงาน</small>
                                @if ($isClosed || $isRefused)
                                    {{-- ถ้ามีวันที่ปิดงานโชว์วันที่ ถ้าไม่มี (เคสปฏิเสธบางทีอาจไม่มีวันที่ปิด) ให้โชว์คำว่า จบงาน --}}
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
                // เรียกใช้งาน DataTable เมื่ออยู่บนหน้าจอ Desktop
                if ($('#officeTrackingTable').length > 0 && window.innerWidth >= 768) {
                    var table = $('#officeTrackingTable').DataTable({
                        "paging": false, // ใช้ Laravel Pagination แทน
                        "info": false, // ซ่อนข้อมูลจำนวนรายการ (เพราะเรามี Summary Card แล้ว)
                        "searching": false, // ซ่อนช่องค้นหา (เราใช้ Form ด้านบน)
                        "ordering": true, // เปิดใช้งานการเรียงลำดับ
                        "order": [
                            [6, "desc"]
                        ], // ตั้งต้นให้เรียงจาก อัปเดตล่าสุด
                        "autoWidth": false,
                        "responsive": true,
                        "language": {
                            "emptyTable": "ไม่พบข้อมูลรายการแจ้งซ่อม",
                            "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน"
                        }
                    });

                    // บังคับให้หัวตารางที่ถูกสร้างใหม่โดย DataTable มีการจัดวางที่ถูกต้อง
                    $('#officeTrackingTable thead th').addClass('text-center align-middle');
                }
            });
        </script>
    @endpush
@endsection
