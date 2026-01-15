@extends('layout.mainlayout')
@section('title', 'ตารางติดตามงานซ่อม')

@section('content')
<style>
    /* บังคับหัวตารางให้อยู่ตรงกลางและไม่ตัดบรรทัด */
    #officeTrackingTable thead th {
        text-align: center !important;
        vertical-align: middle !important;
        white-space: nowrap;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    /* ปรับแต่งความละเอียดของข้อมูลวันที่และชื่อ */
    .datetime-box {
        line-height: 1.2;
    }
    .extra-small {
        font-size: 0.72rem;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
</style>

<div class="container-fluid p-0">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-list-task text-primary me-2"></i> ตารางติดตามงาน (ธุรการ)
        </h5>
        <button class="btn btn-outline-secondary btn-sm shadow-sm" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> รีเฟรช
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <small class="text-muted d-block">งานทั้งหมด</small>
                    <span class="h5 fw-bold">{{ number_format($totalCount) }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #ffc107;">
                <div class="card-body p-3">
                    <small class="text-muted d-block">รอดำเนินการ</small>
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
                            <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
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
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-3">JobId</th>
                            <th>สาขา</th>
                            <th class="text-start">อุปกรณ์</th>
                            <th>สถานะปัจจุบัน</th>
                            <th>วันที่รับของ</th>
                            <th>ผู้รับของ</th>
                            <th>อัปเดตล่าสุด</th>
                            <th>วันที่ปิดงาน/โดย</th>
                            <th style="width: 50px;">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse ($jobs as $job)
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
                                <td class="ps-3 fw-bold text-primary">#{{ $job->JobId ?? $job->NotirepairId }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary small">
                                        {{ $job->branchCode }}
                                    </span>
                                </td>
                                <td class="text-start">
                                    <div class="fw-medium text-dark text-truncate" style="max-width: 180px;" title="{{ $job->equipmentName }}">
                                        {{ $job->equipmentName }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusStyle['bg'] }} text-{{ $statusStyle['text'] }} shadow-sm extra-small">
                                        <i class="{{ $statusStyle['icon'] }} me-1"></i> {{ $currentStatus }}
                                    </span>
                                </td>
                                <td>
                                    @if ($job->received_at)
                                        <div class="datetime-box small">
                                            <div class="fw-bold">{{ date('d/m/Y', strtotime($job->received_at)) }}</div>
                                            <div class="text-muted extra-small">{{ date('H:i', strtotime($job->received_at)) }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="small">{{ $job->receiver_name ?? '-' }}</td>
                                <td>
                                    <div class="datetime-box small">
                                        <div class="text-dark">{{ $job->last_update ? date('d/m/Y', strtotime($job->last_update)) : '-' }}</div>
                                        <div class="text-muted extra-small">{{ $job->last_update ? date('H:i', strtotime($job->last_update)) : '' }}</div>
                                    </div>
                                </td>
                                <td>
                                    @if ($job->closedJobs !== 'ยังไม่ปิดงาน')
                                        <div class="datetime-box">
                                            <div class="text-success fw-bold small">{{ date('d/m/Y', strtotime($job->DateCloseJobs)) }}</div>
                                            <div class="text-muted extra-small">
                                                <i class="bi bi-person-check"></i> {{ Str::limit($job->closer_name, 12) }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted fw-normal border extra-small">ยังไม่ปิดงาน</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-light border text-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            {{-- DataTable handles empty through config --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Mobile View --}}
    <div class="d-md-none">
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
                        <span class="text-muted">สถานะ:</span> <span class="fw-bold">{{ $job->current_status }}</span>
                    </div>
                    <div class="p-2 mb-2 bg-light rounded-3 extra-small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">ผู้รับของ:</span>
                            <span class="text-dark fw-medium">{{ $job->receiver_name ?? '-' }}</span>
                        </div>
                        @if ($job->closedJobs !== 'ยังไม่ปิดงาน')
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">ผู้ปิดงาน:</span>
                                <span class="text-success fw-medium">{{ $job->closer_name ?? '-' }}</span>
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
                            <small class="{{ $job->closedJobs !== 'ยังไม่ปิดงาน' ? 'text-success' : 'text-danger' }} fw-bold">
                                {{ $job->closedJobs !== 'ยังไม่ปิดงาน' ? date('d/m/y', strtotime($job->DateCloseJobs)) : 'ยังไม่ปิดงาน' }}
                            </small>
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
    if ($('#officeTrackingTable').length > 0 && window.innerWidth >= 768) {
        $('#officeTrackingTable').DataTable({
            "paging": false,
            "info": false,
            "searching": false,
            "ordering": true,
            "order": [[6, "desc"]], // เรียงตาม "อัปเดตล่าสุด"
            "autoWidth": false,
            "responsive": true,
            "language": {
                "emptyTable": "ไม่พบข้อมูลรายการแจ้งซ่อม",
                "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน"
            },
            "columnDefs": [
                { "orderable": false, "targets": 8 }, // ปิด Sort Action
                { "className": "text-center", "targets": [0,1,3,4,5,6,7] }
            ]
        });
    }
});
</script>
@endpush
@endsection