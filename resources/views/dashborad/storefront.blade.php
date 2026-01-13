@extends('layout.mainlayout')

@section('title', 'รายการแจ้งซ่อม')

@section('content')
    <h5 class="fw-bold text-dark mb-3">
        <i class="bi bi-list-task"></i> รายการแจ้งซ่อมทั้งหมด
    </h5>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Desktop View --}}
    <div class="card shadow-sm d-none d-md-block">
        <div class="card-body table-responsive">
            <table id="notiTable" class="table table-hover align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th style="width: 15%">รหัสแจ้งซ่อม</th>
                        <th style="width: 15%">อุปกรณ์</th>
                        <th style="width: 30%">รายละเอียด</th>
                        <th style="width: 10%">วันที่แจ้ง</th>
                        <th style="width: 15%">วันที่อัพเดทล่าสุด</th>
                        <th style="width: 10%">สถานะ</th>
                        <th style="width: 10%">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($noti as $item)
                        @php
                            $status = $item->status ?? 'ยังไม่ได้รับของ';
                            $isClosed = ($item->closedJobs === 'ปิดงานเรียบร้อย');
                            $color = $isClosed ? 'success' : match ($status) {
                                'ยังไม่ได้รับของ' => 'danger',
                                'ได้รับของแล้ว' => 'primary',
                                default => 'secondary',
                            };
                            $displayStatus = $isClosed ? 'ปิดงานเรียบร้อย' : $status;
                        @endphp
                        <tr>
                            {{-- <td>{{ $item->NotirepairId }}</td> --}}
                            {{-- <td>
                                <span class="fw-bold text-primary">{{ $item->JobId ?? $item->NotirepairId }}</span>
                            </td>                             --}}
                            <td>
                                <span class="text-primary fw-bold" style="font-size: 0.85rem;">
                                    #{{$item->JobId ?? $item->NotirepairId}}
                                </span>
                            </td>
                            <td>{{$item->equipmentName}}</td>
                            <td class="text-start">{{$item->DeatailNotirepair}}</td>
                            <td>{{$item->DateNotirepair ? date('d-m-Y H:i', strtotime($item->DateNotirepair)) : '-' }}</td>
                            <td>{{$item->statusDate ? date('d-m-Y H:i', strtotime($item->statusDate)) : '-' }}</td>
                            <td><span class="badge bg-{{ $color }}">{{ $displayStatus}}</span></td>
                            <td>
                                @if ($isClosed)
                                    <span class="text-success fw-bold"><i class="bi bi-check-all"></i> ปิดงานแล้ว</span>
                                @else
                                    @if ($status === 'ยังไม่ได้รับของ')
                                        <form action="{{ route('noti.accept', $item->NotirepairId) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('ยืนยันการรับของ?')">
                                                <i class="bi bi-box-seam"></i> รับของ
                                            </button>
                                        </form>
                                    @elseif ($status === 'ได้รับของแล้ว')
                                        <button type="button" class="btn btn-dark btn-sm w-100 btn-close-job"
                                            data-id="{{ $item->NotirepairId }}" 
                                            data-name="{{ $item->equipmentName }}">
                                            <i class="bi bi-file-earmark-check"></i> ปิดงาน
                                        </button>
                                        <form id="form-close-{{ $item->NotirepairId }}" action="{{ route('noti.close', $item->NotirepairId) }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile View --}}
{{-- Mobile View --}}
<div class="d-md-none">
    @foreach ($noti as $item)
        @php
            $status = $item->status ?? 'ยังไม่ได้รับของ';
            $isClosed = ($item->closedJobs === 'ปิดงานเรียบร้อย');
            $themeColor = $isClosed ? '#198754' : ($status === 'ยังไม่ได้รับของ' ? '#dc3545' : '#0d6efd');
            $bgColor = $isClosed ? '#f8fff9' : ($status === 'ยังไม่ได้รับของ' ? '#fffafb' : '#f0f7ff');
        @endphp

        <div class="card mb-3 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden; background-color: {{$bgColor}};">
            <div class="d-flex">
                {{-- แถบสถานะด้านซ้าย --}}
                <div style="width: 6px; background-color: {{ $themeColor }}; flex-shrink: 0;"></div>
                
                <div class="card-body p-3" style="min-width: 0;"> {{-- ใส่ min-width: 0 เพื่อให้ text-truncate ทำงาน --}}
                    <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                        <div style="min-width: 0; flex: 1;"> {{-- ให้ส่วนหัวข้อยืดหยุ่น --}}
                            <span class="text-muted small fw-bold">#{{ $item->JobId ?? $item->NotirepairId}}</span>
                            {{-- ใช้ text-truncate เพื่อกันชื่ออุปกรณ์ยาวเกินไปจนเบียด Badge --}}
                            <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $item->equipmentName }}</h6>
                        </div>
                        {{-- Badge สถานะ --}}
                        <span class="badge rounded-pill flex-shrink-0" style="background-color: {{ $themeColor }}; font-size: 0.7rem; padding: 0.4em 0.8em;">
                            {{$isClosed ? 'ปิดงานแล้ว' : $status }}
                        </span>
                    </div>

                    {{-- แก้ไขคำอธิบาย: เพิ่ม word-break เพื่อให้ตัวอักษรที่ติดกันยาวๆ ตัดบรรทัดได้ --}}
                    <p class="text-secondary mb-3 small" style="
                        display: -webkit-box; 
                        -webkit-line-clamp: 3; 
                        -webkit-box-orient: vertical; 
                        overflow: hidden; 
                        word-break: break-word; {{-- สำคัญ: บังคับตัดคำที่ยาวติดกัน --}}
                        line-height: 1.4;
                    ">
                        <i class="bi bi-info-circle me-1"></i>{{$item->DeatailNotirepair}}
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-calendar3 me-1"></i>{{date('d/m/y H:i', strtotime($item->DateNotirepair)) }}
                        </div>

                        <div class="flex-shrink-0 ms-2">
                            @if (!$isClosed)
                                @if ($status === 'ยังไม่ได้รับของ')
                                    <form action="{{route('noti.accept', $item->NotirepairId)}}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm px-3 py-1 fw-bold" 
                                            style="background-color: #198754; color: white; border-radius: 8px; font-size: 0.8rem;">
                                            รับของ
                                        </button>
                                    </form>
                                @elseif ($status === 'ได้รับของแล้ว') {{-- เช็คคำว่า 'แล้ว' ให้ตรงกับ Database --}}
                                    <button type="button" class="btn btn-sm px-3 py-1 fw-bold btn-close-job"
                                        data-id="{{$item->NotirepairId}}" 
                                        data-name="{{$item->equipmentName}}"
                                        style="background-color: #212529; color: white; border-radius: 8px; font-size: 0.8rem;">
                                        ปิดงาน
                                    </button>
                                @endif
                            @else
                                <span class="text-success small fw-bold"><i class="bi bi-patch-check-fill me-1"></i>สำเร็จ</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

    <div class="mt-4 d-flex justify-content-center">
        {{$noti->links('pagination::bootstrap-5')}}
    </div>
@endsection

{{-- ✅ ย้าย Script มาไว้ใน Section (ถ้า Layout หลักรองรับ) --}}
@push('scripts') 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $(document).on('click', '.btn-close-job', function() {
            const notiId = $(this).data('id');
            const equipName = $(this).data('name');
        
            Swal.fire({
                title: 'ยืนยันการปิดงาน?',
                text: `คุณมั่นใจใช่ไหมว่าซ่อมอุปกรณ์ "${equipName}" เสร็จสิ้นแล้ว?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754', 
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ซ่อมเสร็จแล้ว!',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // หา form ที่สัมพันธ์กับ id แล้วส่งค่า
                    $(`#form-close-${notiId}`).submit();
                }
            });
        });
    });
</script>
@endpush
