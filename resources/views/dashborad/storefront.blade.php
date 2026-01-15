@extends('layout.mainlayout')

@section('title', 'รายการแจ้งซ่อม (หน้าร้าน)')

@section('content')
    <h5 class="fw-bold text-dark mb-3">
        <i class="bi bi-list-task"></i> รายการแจ้งซ่อมทั้งหมด (หน้าร้าน)
    </h5>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Desktop View --}}
    <div class="card shadow-sm d-none d-md-block border-0">
        <div class="card-body table-responsive">
            <table id="storeTable" class="table table-hover align-middle">
                {{-- <thead class="table-primary text-center">
                    <tr>
                        <th style="width: 12%">รหัสแจ้งซ่อม</th>
                        <th style="width: 15%">อุปกรณ์</th>
                        <th style="width: 25%">รายละเอียด</th>
                        <th style="width: 12%">วันที่แจ้ง</th>
                        <th style="width: 12%">อัปเดตล่าสุด</th>
                        <th style="width: 12%">สถานะ</th>
                        <th style="width: 12%">จัดการ</th>
                    </tr>
                </thead> --}}
                <thead class="table-primary text-center">
                    <tr>
                        <th class="text-center">รหัสแจ้งซ่อม</th>
                        <th class="text-center">อุปกรณ์</th>
                        <th class="text-center">รายละเอียด</th> 
                        <th class="text-center">วันที่แจ้ง</th>
                        <th class="text-center">อัปเดตล่าสุด</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($noti as $item)
                        @php
                            $status = $item->status ?? 'ยังไม่ได้รับของ';
                            $isClosed = ($item->closedJobs === 'ปิดงานเรียบร้อย');
                            $isRepairFinished = str_contains($status, 'ซ่อมงานเสร็จแล้ว');
                            
                            // กำหนดสี Badge
                            $color = $isClosed ? 'success' : ($isRepairFinished ? 'info' : 'secondary');
                            if($status === 'ยังไม่ได้รับของ') $color = 'danger';
                            if($status === 'ได้รับของแล้ว') $color = 'primary';
                
                            $displayStatus = $isClosed ? 'ปิดงานเรียบร้อย' : ($isRepairFinished ? 'ซ่อมเสร็จสิ้น' : $status);
                        @endphp
                        <tr>
                            <td>
                                <span class="text-primary fw-bold" style="font-size: 0.85rem;">
                                    #{{ $item->JobId ?? $item->NotirepairId }}
                                </span>
                            </td>
                            <td class="fw-bold">{{ $item->equipmentName }}</td>
                            <td class="text-start small">
                                <div class="text-truncate" style="max-width: 250px;" title="{{ $item->DeatailNotirepair }}">
                                    {{ $item->DeatailNotirepair }}
                                </div>
                            </td>
                            <td class="small">{{ $item->DateNotirepair ? date('d-m-Y H:i', strtotime($item->DateNotirepair)) : '-' }}</td>
                            <td class="small">{{ $item->statusDate ? date('d-m-Y H:i', strtotime($item->statusDate)) : '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $color }} fw-normal">{{ $displayStatus }}</span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center">
                                    @if ($isClosed)
                                        <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill"></i> สำเร็จ</span>
                                    @elseif ($isRepairFinished)
                                        <button type="button" class="btn btn-info btn-sm px-3 text-white fw-bold btn-receive-back" 
                                                data-id="{{ $item->NotirepairId }}" 
                                                data-jobid="{{ $item->JobId ?? $item->NotirepairId }}"
                                                data-name="{{ $item->equipmentName }}">
                                            <i class="bi bi-arrow-repeat"></i> รับของคืน
                                        </button>
                                        <form id="form-receive-{{ $item->NotirepairId }}" action="{{ route('noti.close', $item->NotirepairId) }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                    @else
                                        <span class="text-muted small italic">รอช่างดำเนินการ</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile View (ใช้สไตล์เดิมที่คุณมีซึ่งสวยอยู่แล้ว) --}}
    <div class="d-md-none">
        @foreach ($noti as $item)
            @php
                $status = $item->status ?? 'ยังไม่ได้รับของ';
                $isClosed = $item->closedJobs === 'ปิดงานเรียบร้อย';
                $isRepairFinished = str_contains($status, 'ซ่อมงานเสร็จแล้ว');

                $themeColor = $isClosed ? '#198754' : match ($status) {
                    'ยังไม่ได้รับของ' => '#dc3545',
                    'ได้รับของแล้ว' => '#0d6efd',
                    default => $isRepairFinished ? '#0dcaf0' : '#6c757d'
                };
            @endphp

            <div class="card mb-3 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden; background-color: #fff;">
                <div class="d-flex">
                    <div style="width: 6px; background-color: {{ $themeColor }}; flex-shrink: 0;"></div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                            <div style="min-width: 0; flex: 1;">
                                <span class="text-muted small fw-bold">#{{ $item->JobId ?? $item->NotirepairId }}</span>
                                <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $item->equipmentName }}</h6>
                            </div>
                            <span class="badge rounded-pill flex-shrink-0" style="background-color: {{ $themeColor }}; font-size: 0.7rem; padding: 0.4em 0.8em;">
                                {{ $isClosed ? 'ปิดงานแล้ว' : ($isRepairFinished ? 'ซ่อมเสร็จแล้ว' : $status) }}
                            </span>
                        </div>

                        <p class="text-secondary mb-3 small" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word;">
                            <i class="bi bi-info-circle me-1"></i>{{ $item->DeatailNotirepair }}
                        </p>

                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div class="text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-calendar3 me-1"></i>{{ date('d/m/y H:i', strtotime($item->DateNotirepair)) }}
                            </div>

                            <div class="flex-shrink-0 ms-2">
                                @if ($isClosed)
                                    <span class="text-success small fw-bold"><i class="bi bi-patch-check-fill me-1"></i>สำเร็จ</span>
                                @elseif ($isRepairFinished)
                                    <button type="button" class="btn btn-sm px-3 py-1 fw-bold btn-receive-back" 
                                            data-id="{{ $item->NotirepairId }}" 
                                            data-jobid="{{ $item->JobId ?? $item->NotirepairId }}"
                                            data-name="{{ $item->equipmentName }}" 
                                            style="background-color: #0dcaf0; color: white; border-radius: 8px; font-size: 0.8rem;">
                                        รับของคืน
                                    </button>
                                @else
                                    <span class="badge bg-light text-dark border">รอช่าง</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $noti->links('pagination::bootstrap-5') }}
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // 1. Initialize DataTable (เฉพาะ Desktop)
            if (window.matchMedia('(min-width: 768px)').matches) {
                $('#storeTable').DataTable({
                    "searching": false, // ใช้ช่องค้นหาของระบบ Paginate เดิม
                    "paging": false,    // ใช้ระบบ Paginate ของ Laravel
                    "ordering": true,   // เปิดให้กดเรียงลำดับหัวตารางได้
                    "info": false,
                    "autoWidth": false,
                    "language": {
                        "emptyTable": "ไม่พบข้อมูล",
                        "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน"
                    }
                });
            }

            // 2. SweetAlert สำหรับรับของคืน
            $(document).on('click', '.btn-receive-back', function() {
                const notiId = $(this).data('id');
                const jobId = $(this).data('jobid');
                const equipName = $(this).data('name');

                Swal.fire({
                    title: 'ยืนยันการรับของคืน?',
                    text: `รหัส ${jobId} (${equipName}) ซ่อมเสร็จและส่งคืนหน้าร้านแล้วใช่ไหม?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0dcaf0',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ใช่, รับของคืนแล้ว',
                    cancelButtonText: 'ยกเลิก',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(`#form-receive-${notiId}`).submit();
                    }
                });
            });
        });
    </script>
@endpush