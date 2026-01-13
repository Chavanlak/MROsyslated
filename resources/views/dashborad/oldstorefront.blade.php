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
                        <th style="width: 10%">รหัสแจ้งซ่อม</th>
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
                            // ✅ แก้ไขตัวสะกดจาก closedJObs เป็น closedJobs
                            $isClosed = ($item->closedJobs === 'ปิดงานเรียบร้อย');
                            
                            $color = $isClosed ? 'success' : match ($status) {
                                'ยังไม่ได้รับของ' => 'danger',
                                'ได้รับของเเล้ว' => 'primary',
                                default => 'secondary',
                            };

                            $displayStatus = $isClosed ? 'ปิดงานเรียบร้อย' : $status;
                        @endphp
                        <tr>
                            <td>{{$item->NotirepairId}}</td>
                            <td>{{$item->equipmentName}}</td>
                            <td class="text-start">{{$item->DeatailNotirepair}}</td>
                            <td>{{ $item->DateNotirepair ? date('d-m-Y H:i', strtotime($item->DateNotirepair)) : '-' }}</td>
                            <td>{{ $item->statusDate ? date('d-m-Y H:i', strtotime($item->statusDate)) : '-' }}</td>
                            <td><span class="badge bg-{{$color}}">{{$displayStatus}}</span></td> 
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
                                    @elseif ($status === 'ได้รับของเเล้ว')
                                        <form action="{{ route('noti.close', $item->NotirepairId) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-dark btn-sm w-100" onclick="return confirm('ยืนยันการปิดงาน?')">
                                                <i class="bi bi-file-earmark-check"></i> ปิดงาน
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $noti->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- Mobile View --}}
    <div class="d-md-none">
        @foreach ($noti as $item)
            @php
                $status = $item->status ?? 'ยังไม่ได้รับของ';
                $isClosed = ($item->closedJobs === 'ปิดงานเรียบร้อย');
                $color = $isClosed ? 'success' : match ($status) {
                    'ยังไม่ได้รับของ' => 'danger',
                    'ได้รับของเเล้ว' => 'primary',
                    default => 'secondary',
                };
                $displayStatus = $isClosed ? 'ปิดงานเรียบร้อย' : $status;
            @endphp

            <div class="card mb-3 shadow-sm border-start border-{{$color}} border-4">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary">📦 รหัส: {{$item->NotirepairId}}</h5>
                    <p class="mb-1"><strong>อุปกรณ์:</strong> {{$item->equipmentName}}</p>
                    <p class="mb-1 text-muted small"><i class="bi bi-clock"></i> แจ้งเมื่อ: {{ date('d-m-Y H:i', strtotime($item->DateNotirepair)) }}</p>
                    <p class="mb-2"><span class="badge bg-{{$color}} fs-6">{{$displayStatus}}</span></p>

                    @if (!$isClosed)
                        <div class="mt-2">
                            @if ($status === 'ยังไม่ได้รับของ')
                                <form action="{{ route('noti.accept', $item->NotirepairId) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100">รับของ</button>
                                </form>
                            @elseif ($status === 'ได้รับของเเล้ว')
                                <form action="{{ route('noti.close', $item->NotirepairId) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-dark btn-sm w-100">ปิดงาน</button>
                                </form>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-2 bg-light rounded">
                            <span class="text-success fw-bold small"><i class="bi bi-check-circle"></i> ดำเนินการเสร็จสิ้น</span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="mt-4 d-flex justify-content-center">
            {{ $noti->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection