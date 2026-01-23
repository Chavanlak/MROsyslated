@extends('layout.mainlayout')

@section('title', 'อัพเดตสถานะแจ้งซ่อม')

@section('content')
    <h5 class="fw-bold text-primary mb-4">
        <i class="bi bi-pencil-square"></i> อัพเดตสถานะแจ้งซ่อม
    </h5>


    @php
        // 1. ดึงข้อมูลสถานะปัจจุบัน
        $currentStatus = $updatenoti->latest_status ?? 'ยังไม่ได้รับของ';

        // 2. เช็คว่าซ่อมเสร็จหรือยัง
        $isCompleted =
            Str::contains($currentStatus, 'ซ่อมงานเสร็จแเล้ว') || Str::contains($currentStatus, 'ซ่อมงานเสร็จแล้ว');

        // 3. กำหนดสี Badge
        $badgeClass = match ($currentStatus) {
            'ยังไม่ได้รับของ' => 'bg-danger',
            'ได้รับของแล้ว' => 'bg-info',
            'กำลังดำเนินการซ่อม | ช่างStore' => 'bg-warning text-dark',
            'ส่งSuplierแล้ว' => 'bg-primary',
            'ซ่อมงานเสร็จแล้ว | ช่างStore', 'ซ่อมงานเสร็จแล้ว | Supplier' => 'bg-success',
            default => 'bg-secondary',
        };

        $displayStatus = $isCompleted ? 'ซ่อมเสร็จสิ้น' : $currentStatus;

        // -----------------------------------------------------------
        // เราใช้ Auth::check() ตรวจสอบก่อน ถ้าไม่มี User ให้เป็น null
        // $userRole = Auth::check() ? Auth::user()->role : null;
        $userRole = Session::get('role');

        // แยก Route ตาม Role
        if ($userRole == 'Interior') {
            // 🟡 Route สำหรับ Interior
            $formAction = route('interior.update');
            $backRoute = route('interior.list');
        } else {
            // 🔵 Route สำหรับ Admin / Technician
            $formAction = route('notiupdate');
            $backRoute = route('noti.list');
        }
    @endphp
    {{-- ============================================================== --}}


    {{-- แสดงผลตาราง (Desktop) --}}
    {{-- <div class="card shadow-sm mb-4 d-none d-lg-block">
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
                            <td>{{$updatenoti->JobId ?? $updatenoti->NotirepairId}}</td>
                            <td>{{$updatenoti->equipmentName}}</td>
                            <td>{{$updatenoti->DeatailNotirepair}}</td>
                            <td>{{$updatenoti->DateNotirepair}}</td>
                            <td>
                                <span class="badge {{ $badgeClass}}">{{$displayStatus}}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div> --}}
    <div class="container-fluid">
        <div class="row">
            {{-- ✅ col-lg-9 : กำหนดความกว้างประมาณ 75% ของหน้าจอ (ปรับเลขลดลงได้ถ้าอยากให้แคบกว่านี้) --}}
            {{-- ✅ ms-md-4 : เว้นระยะจากซ้ายเล็กน้อย เพื่อให้ตรงกับฟอร์มด้านล่าง --}}
            <div class="col-md-10 col-lg-9 ms-md-1">

                <div class="card shadow-sm mb-4 d-none d-lg-block">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th style="width: 15%;">รหัสแจ้งซ่อม</th>
                                        <th style="width: 20%;">อุปกรณ์</th>
                                        <th style="width: 15%;">วันที่แจ้งซ่อม</th>
                                        <th>รายละเอียดแจ้งซ่อม</th>
                                        <th style="width: 15%;">สถานะปัจจุบัน</th>
                                        <th>วันที่อัปเดตสถานะล่าสุด</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center align-middle">
                                    <tr>
                                        <td>{{ $updatenoti->JobId ?? $updatenoti->NotirepairId }}</td>
                                        <td>{{ $updatenoti->equipmentName }}</td>
                                        {{-- <td>{{$updatenoti->DateNotirepair}}</td> --}}
                                        <td>
                                            @if ($updatenoti->DateNotirepair)
                                            {{-- แสดงวันที่ และ เวลา --}}
                                            {{ date('d-m-Y H:i', strtotime($updatenoti->DateNotirepair)) }}
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                        </td>
                                         <td class="text-start">{{ $updatenoti->DeatailNotirepair }}</td>
                                        <td>
                                            <span class="badge {{ $badgeClass }}">{{ $displayStatus }}</span>
                                        </td>
                                        <td>
                                            @if ($updatenoti->last_update)
                                                {{-- แสดงวันที่ และ เวลา --}}
                                                {{ date('d-m-Y H:i', strtotime($updatenoti->last_update)) }}
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div> {{-- จบ Column --}}
        </div> {{-- จบ Row --}}
    </div>

    {{-- แสดงผลการ์ด (Mobile) --}}
    <div class="card shadow-sm mb-4 d-lg-none">
        <div class="card-body">
            <p class="fw-bold mb-1">
                <i class="bi bi-box-seam text-primary"></i> รหัสแจ้งซ่อม:
                <span class="fw-normal">{{ $updatenoti->JobId ?? $updatenoti->NotirepairId }}</span>
            </p>
            <p class="mb-1">
                <i class="bi bi-tag"></i> อุปกรณ์:
                <span class="fw-normal">{{ $updatenoti->equipmentName }}</span>
            </p>
            <p class="mb-1">
                <i class="bi bi-file-text"></i> รายละเอียด:
                <span class="fw-normal">{{ $updatenoti->DeatailNotirepair }}</span>
            </p>
            <p class="mb-3">
                <i class="bi bi-calendar"></i> วันที่แจ้ง:
                <span class="fw-normal">{{ $updatenoti->DateNotirepair }}</span>
            </p>
            <div class="d-flex align-items-center">
                <span class="fw-bold me-2">สถานะปัจจุบัน:</span>
                <span class="badge {{ $badgeClass }} fs-6">{{ $displayStatus }}</span>
            </div>
        </div>
    </div>

    {{-- ฟอร์มอัพเดตสถานะ --}}
    {{-- ✅ เพิ่ม Container และ Grid เพื่อจัดกึ่งกลางและจำกัดความกว้าง --}}
    <div class="container-fluid">
        {{-- ✅ ลบ justify-content-center ออกเพื่อให้ชิดซ้าย --}}
        {{-- ✅ เพิ่ม mt-4 เพื่อเว้นระยะจากด้านบนเล็กน้อย --}}
        <div class="row mt-4">

            {{-- ✅ col-md-6: ความกว้างครึ่งจอ --}}
            {{-- ✅ ms-md-4: (Margin Start) ขยับออกจากขอบซ้ายมานิดหน่อย ไม่ให้ติดขอบจินเกินไป --}}
            {{-- <div class="col-md-8 col-lg-6 ms-md-2">  --}}
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <form action="{{ $formAction }}" method="POST">
                            @csrf
                            <input type="hidden" name="NotirepairId" value="{{ $updatenoti->NotirepairId }}">
                            <input type="hidden" name="JobId" value="{{ $updatenoti->JobId }}">

                            <div class="mb-3">
                                <label for="status" class="form-label fw-semibold">
                                    อัพเดตสถานะใหม่ (สถานะปัจจุบัน: **{{ $displayStatus }}**)
                                </label>

                                <select name="status" id="status" class="form-select"
                                    @if ($isCompleted) disabled @endif required>
                                    @if ($isCompleted)
                                        <option value="" disabled selected>ซ่อมเสร็จสิ้น ไม่ต้องอัพเดตแล้ว</option>
                                    @else
                                        <option value="" disabled selected>--- กรุณาอัพเดตสถานะแจ้งซ่อม ---</option>
                                    @endif

                                    {{-- Logic Dropdown เดิม --}}
                                    @if ($currentStatus == 'ได้รับของแล้ว')
                                        <option value="กำลังดำเนินการซ่อม | ช่างStore">กำลังดำเนินการซ่อม (ช่าง Store)
                                        </option>
                                        <option value="ส่งSuplierแล้ว">ส่ง Supplier แล้ว</option>
                                    @elseif ($currentStatus == 'กำลังดำเนินการซ่อม | ช่างStore')
                                        <option value="ซ่อมงานเสร็จแล้ว | ช่างStore">ซ่อมงานเสร็จแล้ว (โดยช่าง Store)
                                        </option>
                                        <option value="ส่งSuplierแล้ว">เปลี่ยนส่ง Supplier (ซ่อมเองไม่ได้)</option>
                                    @elseif ($currentStatus == 'ส่งSuplierแล้ว')
                                        <option value="ซ่อมงานเสร็จแล้ว | Supplier">ซ่อมงานเสร็จแล้ว (โดย Supplier)</option>
                                        <option value="กำลังดำเนินการซ่อม | ช่างStore">ดึงกลับมาซ่อมเอง (ช่าง Store)
                                        </option>
                                    @endif
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ $backRoute }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left-circle"></i> ย้อนกลับ
                                </a>

                                @if ($isCompleted)
                                    <button type="button" class="btn btn-secondary" disabled>
                                        <i class="bi bi-check-circle"></i> ซ่อมเสร็จสิ้น
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> บันทึกสถานะ
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

            </div> {{-- จบ Column --}}
        </div> {{-- จบ Row --}}
    </div>
    {{-- <div class="card shadow-sm ">
        <div class="card-body">
            
            <form action="{{ $formAction }}" method="POST">
                @csrf
                <input type="hidden" name="NotirepairId" value="{{$updatenoti->NotirepairId}}">
                <input type="hidden" name="JobId" value="{{$updatenoti->JobId }}">
                
                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">
                        อัพเดตสถานะใหม่ (สถานะปัจจุบัน: **{{$displayStatus}}**)
                    </label>
                    
                    <select name="status" id="status" class="form-select" @if ($isCompleted) disabled @endif required>
                        
                        @if ($isCompleted)
                            <option value="" disabled selected>ซ่อมเสร็จสิ้น ไม่ต้องอัพเดตแล้ว</option>
                        @else
                            <option value="" disabled selected >--- กรุณาอัพเดตสถานะแจ้งซ่อม ---</option>
                        @endif
                    
                        
                        @if ($currentStatus == 'ได้รับของแล้ว')
                            <option value="กำลังดำเนินการซ่อม | ช่างStore">กำลังดำเนินการซ่อม (ช่าง Store)</option>
                            <option value="ส่งSuplierแล้ว">ส่ง Supplier แล้ว</option>
                        
                        @elseif ($currentStatus == 'กำลังดำเนินการซ่อม | ช่างStore')
                            <option value="ซ่อมงานเสร็จแล้ว | ช่างStore">ซ่อมงานเสร็จแล้ว (โดยช่าง Store)</option>
                            <option value="ส่งSuplierแล้ว">เปลี่ยนส่ง Supplier (ซ่อมเองไม่ได้)</option>
                        
                        @elseif ($currentStatus == 'ส่งSuplierแล้ว')
                            <option value="ซ่อมงานเสร็จแล้ว | Supplier">ซ่อมงานเสร็จแล้ว (โดย Supplier)</option>
                            <option value="กำลังดำเนินการซ่อม | ช่างStore">ดึงกลับมาซ่อมเอง (ช่าง Store)</option>
                        @endif
                    
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ $backRoute }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> ย้อนกลับ
                    </a>

                    @if ($isCompleted)
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="bi bi-check-circle"></i> ซ่อมเสร็จสิ้น
                        </button>
                    @else
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> บันทึกสถานะ
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div> --}}

@endsection
