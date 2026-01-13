@extends('layout.mainlayout')

@section('title', 'อัพเดตสถานะแจ้งซ่อม')

@section('content')
    <h5 class="fw-bold text-primary mb-4">
        <i class="bi bi-pencil-square"></i> อัพเดตสถานะแจ้งซ่อม
    </h5>

    {{-- ดึงสถานะปัจจุบันออกมาเก็บในตัวแปรเพื่อใช้อ้างอิง --}}
    @php
        // 💡 ใช้ latest_status ที่ถูกแนบมาจาก Repository::getNotiDetails()
        $currentStatus = $updatenoti->latest_status ?? 'ยังไม่ได้รับของ';
        // ✅ เพิ่มการเช็คว่าสถานะปัจจุบันคือ "ซ่อมงานเสร็จเเล้ว" แล้วหรือไม่
        $isCompleted = (Str::contains($currentStatus, 'ซ่อมงานเสร็จแเล้ว'));

        $badgeClass = match ($currentStatus) {
            'ยังไม่ได้รับของ' => 'bg-danger',
            'ได้รับขอแล้ว' => 'bg-info',
            'กำลังดำเนินการซ่อม | ช่างStore' => 'bg-warning text-dark',
            'ส่งSuplierแล้ว' => 'bg-primary',
            // รวมสถานะซ่อมเสร็จเเล้วทั้งสองแบบ
            'ซ่อมงานเสร็จแล้ว | ช่างStore', 'ซ่อมงานเสร็จแล้ว | Supplier' => 'bg-success',
            default => 'bg-secondary',
        };
        
        // กำหนดสถานะที่แสดงผล (ซ่อมเสร็จแล้วจะแสดงเป็น 'ซ่อมเสร็จสิ้น')
        $displayStatus = $isCompleted ? 'ซ่อมเสร็จสิ้น' : $currentStatus;

    @endphp

    <div class="card shadow-sm mb-4 d-none d-lg-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>รหัสแจ้งซ่อม</th>
                            <th>อุปกรณ์</th>
                            <th>รายละเอียด</th>
                            <th>วันที่แจ้ง</th>
                            <th>สถานะปัจจุบัน</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <tr>
                            <td>{{$updatenoti->NotirepairId}}</td>
                            <td>{{$updatenoti->equipmentName}}</td>
                            <td>{{$updatenoti->DeatailNotirepair}}</td>
                            <td>{{$updatenoti->DateNotirepair}}</td>
                            <td>
                                {{-- ✅ ใช้ $displayStatus ในการแสดงผล --}}
                                <span class="badge {{ $badgeClass}}">
                                    {{$displayStatus}}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="card shadow-sm mb-4 d-lg-none">
        <div class="card-body">
            <p class="fw-bold mb-1">
                <i class="bi bi-box-seam text-primary"></i> รหัสแจ้งซ่อม:
                <span class="fw-normal">{{$updatenoti->NotirepairId}}</span>
            </p>
            <p class="mb-1">
                <i class="bi bi-tag"></i> อุปกรณ์:
                <span class="fw-normal">{{$updatenoti->equipmentName}}</span>
            </p>
            <p class="mb-1">
                <i class="bi bi-file-text"></i> รายละเอียด:
                <span class="fw-normal">{{$updatenoti->DeatailNotirepair}}</span>
            </p>
            <p class="mb-3">
                <i class="bi bi-calendar"></i> วันที่แจ้ง:
                <span class="fw-normal">{{$updatenoti->DateNotirepair}}</span>
            </p>

            <div class="d-flex align-items-center">
                <span class="fw-bold me-2">สถานะปัจจุบัน:</span>
                {{-- ✅ ใช้ $displayStatus ในการแสดงผล --}}
                <span class="badge {{$badgeClass}} fs-6">{{$displayStatus}}</span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{route('notiupdate')}}" method="POST">
                @csrf
                <input type="hidden" name="NotirepairId" value="{{$updatenoti->NotirepairId}}">
    
                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">
                        อัปเดต/แก้ไขสถานะ (สถานะปัจจุบัน: <span class="badge {{$badgeClass}}">{{$displayStatus}}</span>)
                    </label>
                    
                    {{-- 1. เอา disabled ออกเพื่อให้แก้ไขได้เสมอ --}}
                    <select name="status" id="status" class="form-select" required>
                    {{-- <select name="status" id="status" class="form-select" @if($isCompleted)  required> --}}
                        {{-- @if($isCompleted)
                            <option value="" selected></option>
                        @else
                        <option value="" disabled>--- กรุณาเลือกสถานะที่ต้องการเปลี่ยน ---</option> --}}
                        
                        {{-- 2. รายการสถานะทั้งหมด: ใช้ selected เพื่อเช็คว่าปัจจุบันคืออันไหน --}}
                        @if ($currentStatus == 'ได้รับของแล้ว')
                        <option value="กำลังดำเนินการซ่อม | ช่างStore">กำลังดำเนินการซ่อม (ช่าง Store)</option>
                        <option value="ส่งSuplierแล้ว">ส่ง Supplier แล้ว</option>                        @endif
                        <option value="ได้รับของแล้ว" {{ $currentStatus == 'ได้รับของแล้ว' ? 'selected' : '' }}>
                            ได้รับของแล้ว
                        </option>
                        
                        <option value="กำลังดำเนินการซ่อม | ช่างStore" {{ $currentStatus == 'กำลังดำเนินการซ่อม | ช่างStore' ? 'selected' : '' }}>
                            กำลังดำเนินการซ่อม (ช่าง Store)
                        </option>
                        
                        <option value="ส่งSuplierแล้ว" {{ $currentStatus == 'ส่งSuplierแล้ว' ? 'selected' : '' }}>
                            ส่ง Supplier แล้ว
                        </option>
                        
                        <option value="ซ่อมงานเสร็จแล้ว | ช่างStore" {{ $currentStatus == 'ซ่อมงานเสร็จแล้ว | ช่างStore' ? 'selected' : '' }}>
                            ซ่อมงานเสร็จแล้ว (โดยช่าง Store)
                        </option>
                        
                        <option value="ซ่อมงานเสร็จแล้ว | Supplier" {{ $currentStatus == 'ซ่อมงานเสร็จแล้ว | Supplier' ? 'selected' : '' }}>
                            ซ่อมงานเสร็จแล้ว (โดย Supplier)
                        </option>
                    </select>
                    <div class="form-text text-muted mt-2">
                        * หากบันทึกผิด คุณสามารถเลือกสถานะใหม่แล้วกดบันทึกซ้ำเพื่อแก้ไขได้
                    </div>
                </div>
    
                <div class="d-flex justify-content-between">
                    <a href="{{ route('noti.list') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> ย้อนกลับ
                    </a>
    
                    {{-- 3. ปุ่มบันทึกให้กดได้ตลอดเวลา --}}
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save"></i> บันทึกการเเก้ไขสถานะ
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection