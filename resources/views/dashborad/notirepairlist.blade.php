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
            {{session('success')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Desktop View --}}
    <div class="card shadow-sm d-none d-md-block border-0">
        <div class="card-body table-responsive">
            <table id="notiTable" class="table table-hover align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th style="width: 10%">รหัสเเจ้งซ่อม</th>
                        <th style="width: 12%">อุปกรณ์</th>
                        <th style="width: 23%">รายละเอียด</th>
                        <th style="width: 15%">สาขา</th>
                        <th style="width: 10%">วันที่แจ้ง</th>
                        <th style="width: 10%">อัปเดตล่าสุด</th>
                        <th style="width: 10%">สถานะ</th>
                        <th style="width: 15%">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($noti as $item)
                        @php
                        //เปลี่ยนจาก $status = $item->status ?? 'ยังไม่ได้รับของ';
                            $status = $item->status ?? 'ยังไม่ได้รับของ';
                            $isCompleted = str_contains($status, needle: 'ซ่อมงานเสร็จแล้ว');
                            $displayStatus = $isCompleted ? 'ซ่อมเสร็จสิ้น' : $status;

                            $color = match ($status) {
                                'ยังไม่ได้รับของ' => 'danger',
                                'ได้รับของแล้ว' => 'primary',
                                'กำลังดำเนินการซ่อม | ช่างStore' => 'warning',
                                'ส่งSuplierแล้ว' => 'info',
                                'ซ่อมงานเสร็จแล้ว | ช่างStore', 'ซ่อมงานเสร็จแล้ว | Supplier' => 'success',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            {{-- ส่วนที่ปรับปรุง: รหัส JobId แบบอักษรปกติ สีฟ้า --}}
                            <td>
                                <span class="text-primary fw-bold" style="font-size: 0.85rem;">
                                    #{{$item->JobId ?? $item->NotirepairId}}
                                </span>
                            </td>

                            <td class="fw-bold">{{$item->equipmentName}}</td>

                            <td class="text-start small">
                                <div class="text-truncate" style="max-width: 220px;" title="{{$item->DeatailNotirepair}}">
                                    {{$item->DeatailNotirepair}}
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold text-dark">{{$item->branchCode}}</div>
                                <div class="small text-muted">เอสพลานาด</div>
                            </td>

                            <td class="small">
                                {{ $item->DateNotirepair ? date('d-m-Y H:i', strtotime($item->DateNotirepair)) : '-' }}
                            </td>

                            <td class="small">
                                {{ $item->statusDate ? date('d-m-Y H:i', strtotime($item->statusDate)) : '-' }}
                            </td>

                            <td>
                                <span class="badge bg-{{ $color }} fw-normal">{{ $displayStatus }}</span>
                            </td>


                            {{-- <td>
                                <div class="d-flex gap-1 justify-content-center">
                                
                                    <a href="{{ route('noti.edit', $item->NotirepairId) }}"
                                       class="btn btn-outline-primary btn-sm" title="แก้ไขข้อมูล">
                                        <i class="bi bi-pencil"></i>
                                    </a>
    
                                
                                    @if ($isCompleted)
                                        <button class="btn btn-outline-secondary btn-sm" disabled title="เสร็จสิ้นแล้ว">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('noti.show_update_form', $item->NotirepairId) }}"
                                           class="btn btn-warning btn-sm" title="อัปเดตสถานะ">
                                            <i class="bi bi-gear-fill"></i>
                                        </a>
                                    @endif
                                </div>
                            </td> --}}
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    @if ($status === 'ยังไม่ได้รับของ')
                                        {{-- ปุ่มกดรับของ --}}
                                        <form action="{{ route('noti.accept', $item->NotirepairId) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm px-3 fw-bold" onclick="return confirm('ยืนยันการรับอุปกรณ์?')">
                                                <i class="bi bi-box-seam me-1"></i> รับของ
                                            </button>
                                        </form>
                                    @elseif($status === 'รับของแล้ว')
                                         <a href="{{ route('noti.show_update_form', $item->NotirepairId) }}"
                                            class="btn btn-warning btn-sm" title="อัปเดตสถานะ">
                                            <i class="bi bi-pencil-square"></i> อัปเดต
                                        </a>
                            
                                    @elseif($status === 'ซ่อมงานเสร็จแล้ว | ช่างStore')
                                        <a href="{{ route('noti.edit', $item->NotirepairId) }}"
                                            class="btn btn-primary btn-sm fw-bold">
                                            <i class="bi bi-pencil"></i> แก้ไข
                                        </a>
                                    @elseif($status === 'ซ่อมงานเสร็จแล้ว | Supplier')
                                        <a href="{{ route('noti.edit', $item->NotirepairId) }}"
                                            class="btn btn-primary btn-sm fw-bold">
                                            <i class="bi bi-pencil"></i> แก้ไข
                                        </a>
                            
                                    @else
                                        {{-- ถ้าได้รับของแล้ว จะแสดงปุ่ม อัปเดต และ แก้ไข --}}
                                        @if (!$isCompleted)
                                            <a href="{{ route('noti.show_update_form', $item->NotirepairId) }}"
                                                class="btn btn-warning btn-sm" title="อัปเดตสถานะ">
                                                <i class="bi bi-pencil-square"></i> อัปเดต
                                            </a>
                                        @endif
                            
                                        <a href="{{ route('noti.edit', $item->NotirepairId) }}"
                                            class="btn btn-primary btn-sm fw-bold">
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
    <div class="mt-4 d-flex justify-content-center">
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
                            <div>
                                {{-- <span class="text-muted small fw-bold">#{{ $item->NotirepairId}}</span> --}}
                                <span class="text-muted small fw-bold">#{{ $item->JobId }}</span>
                                <h6 class="fw-bold mb-0 text-dark">{{ $item->equipmentName }}</h6>
                            </div>
                            <span class="badge rounded-pill bg-{{ $statusConfig['color'] }} px-3 py-2">
                                <i class="bi {{ $statusConfig['icon'] }} me-1"></i>
                                {{ $isCompleted ? 'เสร็จสิ้น' : $status }}
                            </span>
                        </div>

                        {{-- รายละเอียด Location & Zone --}}
                        <div class="p-2 mb-2 rounded" style="background-color: #f8f9fa;">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                <span class="fw-bold small">สาขา: {{ $item->branchCode }} เอสพลานาด</span>
                            </div>
                            {{-- <div class="d-flex align-items-center">
                                <i class="bi bi-layers text-muted me-2"></i>
                                <span class="text-muted small">โซน: {{ $item->zone ?? 'ไม่ได้ระบุ' }}</span>
                            </div> --}}
                        </div>

                        {{-- รายละเอียดอาการ --}}
                        <p class="text-secondary small mb-3">
                            <i class="bi bi-chat-left-text me-1"></i> {{ $item->DeatailNotirepair }}
                        </p>

                        <hr class="my-2 opacity-25">

                        {{-- Footer: วันที่ และ ปุ่มจัดการ --}}
                        {{-- <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted" style="font-size: 0.75rem;">
                                <div><i class="bi bi-calendar3 me-1"></i>
                                    {{ date('d/m/y H:i', strtotime($item->DateNotirepair)) }}</div>
                            </div>

                            @if (!$isCompleted)
                                <a href="{{ route('noti.show_update_form', $item->NotirepairId) }}"
                                    class="btn btn-warning btn-sm fw-bold px-3 shadow-sm" style="border-radius: 8px;">
                                    <i class="bi bi-pencil-square me-1"></i> อัปเดต
                                </a>
                            @else
                                <span class="text-success small fw-bold">
                                    <i class="bi bi-check-all"></i> งานเรียบร้อย
                                </span>
                            @endif
                        </div> --}}
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
                                        <button type="submit" class="btn btn-success btn-sm fw-bold px-3" onclick="return confirm('ยืนยันการรับของ?')">
                                            รับของ
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('noti.edit', $item->NotirepairId) }}"
                                        class="btn btn-outline-primary btn-sm rounded-pill shadow-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                            
                                    @if (!$isCompleted)
                                        <a href="{{ route('noti.show_update_form', $item->NotirepairId) }}"
                                            class="btn btn-warning btn-sm fw-bold px-3 shadow-sm" style="border-radius: 8px;">
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
                    $('#notiTable').DataTable({
                        "searching": false,
                        "paging": false,
                        "ordering": true,
                        "info": false,
                        "autoWidth": false,
                        "columnDefs": [{
                                "targets": 0,
                                "className": "dt-center"
                            },
                            {
                                "targets": 1,
                                "className": "dt-center"
                            },
                            {
                                "targets": 2,
                                "className": "text-start"
                            },
                            {
                                "targets": 3,
                                "className": "dt-center"
                            }, // ✅ คอลัมน์สาขา/โซน
                            {
                                "targets": 4,
                                "className": "dt-center"
                            },
                            {
                                "targets": 5,
                                "className": "dt-center"
                            },
                            {
                                "targets": 6,
                                "className": "dt-center"
                            },
                            {
                                "targets": 7,
                                "className": "dt-center"
                            }
                        ],
                        "language": {
                            "emptyTable": "ไม่พบข้อมูล",
                            "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน"
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection