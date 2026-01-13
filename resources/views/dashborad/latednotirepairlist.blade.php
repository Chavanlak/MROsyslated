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
                <tbody>
                    @foreach ($noti as $item)
                        @php
                            $role = Session::get('role');
                            $status = $item->status ?? 'ยังไม่ได้รับของ';
                            $isCompleted = str_contains($status, 'ซ่อมงานเสร็จแล้ว');
                        @endphp
                        <tr>
                            <td class="text-center">#{{ $item->JobId ?? $item->NotirepairId }}</td>
                            <td class="text-center">{{ $item->equipmentName }}</td>
                            <td>{{ $item->DeatailNotirepair }}</td>
                            <td class="text-center">{{ $item->branchCode }}</td>
                            <td class="text-center">{{ date('d/m/y', strtotime($item->DateNotirepair)) }}</td>
                            <td class="text-center">{{ $item->updated_at ? $item->updated_at->format('d/m/y') : '-' }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-{{ $isCompleted ? 'success' : ($status === 'ยังไม่ได้รับของ' ? 'danger' : 'primary') }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    {{-- เงื่อนไข: ถ้ายังไม่ได้รับของ (เหมือน storefront) --}}
                                    @if ($status === 'ยังไม่ได้รับของ')
                                        <form action="{{ route('noti.accept', $item->NotirepairId) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('ยืนยันการรับของ?')">
                                                <i class="bi bi-box-seam"></i> รับของ
                                            </button>
                                        </form>
                                    @endif

                                    {{-- สิทธิ์สำหรับ: ฝ่ายช่างสโตร์ (หลังจากรับของแล้ว) --}}
                                    @if ($role === 'AdminTechnicianStore' && $status !== 'ยังไม่ได้รับของ')
                                        @if (!$isCompleted)
                                            <a href="{{ route('noti.show_update_form', $item->NotirepairId) }}" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil-square"></i> อัปเดต
                                            </a>
                                        @else
                                            <button class="btn btn-outline-secondary btn-sm" disabled>ส่งงานคืนแล้ว</button>
                                        @endif
                                        <a href="{{ route('noti.edit', $item->NotirepairId) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    {{-- สิทธิ์สำหรับ: พนักงานหน้าร้าน --}}
                                    @elseif ($role === 'Frontstaff')
                                        @if ($isCompleted && $status !== 'ได้รับของคืนเรียบร้อย')
                                            <form action="{{ route('noti.accept_return', $item->NotirepairId) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('ยืนยันรับของคืน?')">
                                                    รับของคืน
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile View --}}
    <div class="d-md-none">
        @foreach ($noti as $item)
            @php
                $status = $item->status ?? 'ยังไม่ได้รับของ';
                $isCompleted = str_contains($status, 'ซ่อมงานเสร็จแล้ว');
                $role = Session::get('role');
            @endphp

            <div class="card mb-3 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="d-flex">
                    <div style="width: 6px; background-color: {{ $isCompleted ? '#198754' : ($status === 'ยังไม่ได้รับของ' ? '#dc3545' : '#0d6efd') }};"></div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="text-muted small fw-bold">#{{ $item->JobId }}</span>
                                <h6 class="fw-bold mb-0 text-dark">{{ $item->equipmentName }}</h6>
                            </div>
                            <span class="badge rounded-pill bg-{{ $isCompleted ? 'success' : ($status === 'ยังไม่ได้รับของ' ? 'danger' : 'primary') }}">
                                {{ $status }}
                            </span>
                        </div>

                        <p class="text-secondary small mb-3 text-truncate">{{ $item->DeatailNotirepair }}</p>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-calendar3 me-1"></i> {{ date('d/m/y H:i', strtotime($item->DateNotirepair)) }}
                            </div>

                            <div class="d-flex gap-2">
                                @if ($status === 'ยังไม่ได้รับของ')
                                    <form action="{{ route('noti.accept', $item->NotirepairId) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm fw-bold">รับของ</button>
                                    </form>
                                @else
                                    @if (!$isCompleted && $role === 'AdminTechnicianStore')
                                        <a href="{{ route('noti.show_update_form', $item->NotirepairId) }}" class="btn btn-warning btn-sm">อัปเดต</a>
                                    @endif
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