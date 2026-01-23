@extends('layout.mainlayout')

@section('title', 'แก้ไขสถานะการแจ้งซ่อม')

@section('content')
    <h5 class="fw-bold text-primary mb-4">
        <i class="bi bi-pencil-square"></i> แก้ไขสถานะการแจ้งซ่อม (โหมดแก้ไข)
    </h5>

    {{-- ✅✅ เพิ่มส่วนเช็ค Route ตรงนี้ครับ --}}
    @php
        // เช็ค Role ว่าใครกำลังใช้งาน
        $userRole = \Illuminate\Support\Facades\Session::get('role');

        // ถ้าเป็น Interior ให้วิ่งไปเส้น Interior, ถ้าไม่ใช่ ให้ไปเส้น Admin (notiupdate)
        if ($userRole == 'Interior') {
            $formAction = route('interior.update'); // ต้องตรงกับชื่อใน web.php กลุ่ม Interior
        } else {
            $formAction = route('notiupdate'); // ของ Admin
        }

        // ตัวแปรสำหรับ Badge สี
        $currentStatus = $updatenoti->latest_status ?? 'ยังไม่ได้รับของ';
        $badgeClass = match ($currentStatus) {
            'ยังไม่ได้รับของ' => 'bg-danger',
            'ได้รับของแล้ว' => 'bg-info',
            'กำลังดำเนินการซ่อม | ช่างStore' => 'bg-warning text-dark',
            'ส่งSuplierแล้ว' => 'bg-primary',
            'ซ่อมงานเสร็จแล้ว | ช่างStore', 'ซ่อมงานเสร็จแล้ว | Supplier' => 'bg-success',
            default => 'bg-secondary',
        };
    @endphp

    {{-- ตารางแสดงข้อมูล (เหมือนเดิม) --}}
    {{-- <div class="card shadow-sm mb-4">
        <div class="card-body"> --}}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-lg-9 ms-md-1">

                <div class="card shadow-sm mb-4 d-none d-lg-block">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th>รหัสแจ้งซ่อม</th>
                                        <th>อุปกรณ์</th>
                                        <th style="width: 15%;">วันที่แจ้งซ่อม</th>
                                        <th>รายละเอียดเเจงซ่อม</th>
                                        <th>สถานะปัจจุบัน</th>
                                        <th>วันที่อัปเดตสถานะล่าสุด</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    <tr>
                                        {{-- <td>{{ $updatenoti->NotirepairId }}</td> --}}
                                        <td>
                                            @if (!empty($updatenoti->JobId))
                                                {{-- ถ้ามี JobId ให้โชว์ JobId (MRO-...) --}}
                                                <span class="fw-bold text-primary">{{ $updatenoti->JobId }}</span>
                                            @else
                                                {{-- ถ้าไม่มี JobId ให้โชว์ NotirepairId (311) แทน --}}
                                                {{ $updatenoti->NotirepairId }}
                                            @endif
                                        </td>
                                        <td>{{ $updatenoti->equipmentName }}</td>
                                        <td>{{ $updatenoti->DateNotirepair }}</td>
                                        <td>{{ $updatenoti->DeatailNotirepair }}</td>
                                        <td><span class="badge {{ $badgeClass }}">{{ $currentStatus }}</span></td>
                                        {{-- <td class="small"
                                            data-order="{{ $updatenoti->statusDate ? strtotime($updatenoti->statusDate) : strtotime($updatenoti->DateNotirepair) }}">
                                            {{ $updatenoti->statusDate ? date('d-m-Y H:i', strtotime($updatenoti->statusDate)) : '-' }}
                                        </td> --}}
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

            </div>
        </div>
    </div>
    {{-- ฟอร์มแก้ไข --}}
    {{-- <div class="card shadow-sm">
        <div class="card-body"> --}}
    {{-- ✅✅ แก้ตรง action ให้ใช้ตัวแปร $formAction ที่เรากำหนดข้างบน --}}
    <div class="container-fluid">
        <div class="row mt-4">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="{{ $formAction }}" method="POST">
                            @csrf
                            <input type="hidden" name="NotirepairId" value="{{ $updatenoti->NotirepairId }}">
                            <input type="hidden" name="statusDate" value="{{ date('Y-m-d H:i:s') }}">
                        
                            {{-- 🔴 จุดที่แก้ไข: เพิ่มกลุ่มสถานะที่ต้องการล็อก --}}
                            @php
                                // 1. รายการสถานะทั้งหมด
                                $allOptions = [
                                    'ยังไม่ได้รับของ',
                                    'ได้รับของแล้ว',
                                    'กำลังดำเนินการซ่อม | ช่างStore',
                                    'ส่งSuplierแล้ว',
                                    'ซ่อมงานเสร็จแล้ว | ช่างStore',
                                    'ซ่อมงานเสร็จแล้ว | Supplier',
                                ];
                        
                                // 2. กลุ่มที่ 1: กำลังซ่อม (ล็อกให้เลือกได้แค่ 2 อันนี้)

                                $reciveitemStatus = [
                                    'ยังไม่ได้รับของ',
                                    'ได้รับของแล้ว'
                                ];
                                $repairingStatuses = [
                                    'กำลังดำเนินการซ่อม | ช่างStore',
                                    'ส่งSuplierแล้ว'
                                ];
                        
                                // 3. กลุ่มที่ 2: ซ่อมเสร็จ (ล็อกให้เลือกได้แค่ 2 อันนี้)
                                $completedStatuses = [
                                    'ซ่อมงานเสร็จแล้ว | ช่างStore',
                                    'ซ่อมงานเสร็จแล้ว | Supplier'
                                ];
                        
                                // 4. ตั้งค่าเริ่มต้น: ให้เห็นทั้งหมดไว้ก่อน (สำหรับสถานะอื่นๆ เช่น รับของ)
                                $visibleOptions = $allOptions;
                        
                                // 5. เงื่อนไขการกรอง
                                if
                                    (in_array($currentStatus, $reciveitemStatus)) {
                                    // ถ้ากำลังซ่อม -> ให้เลือกได้แค่กลุ่มกำลังซ่อม
                                    $visibleOptions = $reciveitemStatus;
                                }
                                elseif (in_array($currentStatus, $repairingStatuses)) {
                                    // ถ้ากำลังซ่อม -> ให้เลือกได้แค่กลุ่มกำลังซ่อม
                                    $visibleOptions = $repairingStatuses;
                                } elseif (in_array($currentStatus, $completedStatuses)) {
                                    // ถ้าซ่อมเสร็จ -> ให้เลือกได้แค่กลุ่มซ่อมเสร็จ
                                    $visibleOptions = $completedStatuses;
                                }
                            @endphp
                        
                            <div class="mb-3">
                                <label for="status" class="form-label fw-bold">ระบุสถานะที่ถูกต้อง:</label>
                        
                                <select name="status" id="status" class="form-select" required>
                                    <option value="" disabled selected>--- กรุณาเลือกสถานะ ---</option>
                        
                                    @foreach ($visibleOptions as $option)
                                        <option value="{{ $option }}"
                                            {{ $currentStatus == $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                        
                                </select>
                                
                              
                            </div>
                        
                            <div class="d-flex justify-content-between">
                                <a href="{{ $userRole == 'Interior' ? route('interior.list') : route('noti.list') }}"
                                    class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                                </a>
                                <button type="submit" class="btn btn-warning px-4">
                                    <i class="bi bi-save"></i> บันทึกการแก้ไข
                                </button>
                            </div>
                        </form>
                      
                       
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

{{-- 2 2 4 --}}
  {{-- <form action="{{ $formAction }}" method="POST">
                            @csrf
                            <input type="hidden" name="NotirepairId" value="{{ $updatenoti->NotirepairId }}">
                            <input type="hidden" name="statusDate" value="{{ date('Y-m-d H:i:s') }}">

                            @php

                                $allOptions = [
                                    'ยังไม่ได้รับของ',
                                    'ได้รับของแล้ว',
                                    'กำลังดำเนินการซ่อม | ช่างStore',
                                    'ส่งSuplierแล้ว',
                                    'ซ่อมงานเสร็จแล้ว | ช่างStore',
                                    'ซ่อมงานเสร็จแล้ว | Supplier',
                                ];

                                $visibleOptions = [];

                                if ($currentStatus == 'ได้รับของแล้ว') {
                                    $visibleOptions = ['ยังไม่ได้รับของ', 'ได้รับของแล้ว'];
                                } elseif (str_contains($currentStatus, 'ซ่อมงานเสร็จแล้ว')) {
                                    $visibleOptions = $allOptions;
                                } else {
                                    $visibleOptions = array_diff($allOptions, ['ยังไม่ได้รับของ', 'ได้รับของแล้ว']);

                                    if (!in_array($currentStatus, $visibleOptions)) {
                                        array_unshift($visibleOptions, $currentStatus);
                                    }
                                }
                            @endphp

                            <div class="mb-3">
                                <label for="status" class="form-label fw-bold">ระบุสถานะที่ถูกต้อง:</label>

                                <select name="status" id="status" class="form-select" required>
                                    <option value="" disabled>--- กรุณาเลือกสถานะที่ต้องการแก้ไข ---</option>

                                    @foreach ($visibleOptions as $option)
                                        <option value="{{ $option }}"
                                            {{ $currentStatus == $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ $userRole == 'Interior' ? route('interior.list') : route('noti.list') }}"
                                    class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                                </a>
                                <button type="submit" class="btn btn-warning px-4">
                                    <i class="bi bi-save"></i> บันทึกการแก้ไข
                                </button>
                            </div>
                        </form> --}}
{{-- 2 2 4 --}}
{{-- <form action="{{ $formAction }}" method="POST">
    @csrf
    <input type="hidden" name="NotirepairId" value="{{ $updatenoti->NotirepairId }}">
    <input type="hidden" name="statusDate" value="{{ date('Y-m-d H:i:s') }}">

    @php
        $allOptions = [
            'ยังไม่ได้รับของ',
            'ได้รับของแล้ว',
            'กำลังดำเนินการซ่อม | ช่างStore',
            'ส่งSuplierแล้ว',
            'ซ่อมงานเสร็จแล้ว | ช่างStore',
            'ซ่อมงานเสร็จแล้ว | Supplier',
        ];

        $repairingStatuses = [
            'กำลังดำเนินการซ่อม | ช่างStore',
            'ส่งSuplierแล้ว'
        ];

        $visibleOptions = $allOptions;

        if (in_array($currentStatus, $repairingStatuses)) {
            $visibleOptions = $repairingStatuses;
        }
    @endphp

    <div class="mb-3">
        <label for="status" class="form-label fw-bold">ระบุสถานะที่ถูกต้อง:</label>

        <select name="status" id="status" class="form-select" required>
            <option value="" disabled selected>--- กรุณาเลือกสถานะ ---</option>

            @foreach ($visibleOptions as $option)
                <option value="{{ $option }}"
                    {{ $currentStatus == $option ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endforeach

        </select>
        
        <div class="form-text text-muted small mt-2">
            @if(in_array($currentStatus, $repairingStatuses))
                <span class="text-danger">
                    <i class="bi bi-lock-fill"></i> ล็อกการแก้ไขเฉพาะสถานะ "ดำเนินการซ่อม" เท่านั้น
                </span>
            @else
                <i class="bi bi-pencil"></i> สามารถแก้ไขได้ทุกสถานะ
            @endif
        </div>

    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ $userRole == 'Interior' ? route('interior.list') : route('noti.list') }}"
            class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> ย้อนกลับ
        </a>
        <button type="submit" class="btn btn-warning px-4">
            <i class="bi bi-save"></i> บันทึกการแก้ไข
        </button>
    </div>
</form> --}}
{{-- staus 4 4 4 --}}
 {{-- <form action="{{ $formAction }}" method="POST">
                            @csrf
                            <input type="hidden" name="NotirepairId" value="{{ $updatenoti->NotirepairId }}">
                            <input type="hidden" name="statusDate" value="{{ date('Y-m-d H:i:s') }}">

                            <div class="mb-3">
                                <label for="status" class="form-label fw-bold">ระบุสถานะที่ถูกต้อง:</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="" disabled>--- กรุณาเลือกสถานะที่ต้องการแก้ไข ---</option>
                                    
                                    <option value="ยังไม่ได้รับของ"
                                        {{ $currentStatus == 'ยังไม่ได้รับของ' ? 'selected' : '' }}>ยังไม่ได้รับของ
                                    </option>
                                    <option value="ได้รับของแล้ว"
                                        {{ $currentStatus == 'ได้รับของแล้ว' ? 'selected' : '' }}>
                                        ได้รับของแล้ว</option>
                                    <option value="กำลังดำเนินการซ่อม | ช่างStore"
                                        {{ $currentStatus == 'กำลังดำเนินการซ่อม | ช่างStore' ? 'selected' : '' }}>
                                        กำลังดำเนินการซ่อม (ช่าง Store)</option>
                                    <option value="ส่งSuplierแล้ว"
                                        {{ $currentStatus == 'ส่งSuplierแล้ว' ? 'selected' : '' }}>ส่ง Supplier แล้ว
                                    </option>
                                    <option value="ซ่อมงานเสร็จแล้ว | ช่างStore"
                                        {{ $currentStatus == 'ซ่อมงานเสร็จแล้ว | ช่างStore' ? 'selected' : '' }}>
                                        ซ่อมงานเสร็จแล้ว (ช่าง Store)</option>
                                    <option value="ซ่อมงานเสร็จแล้ว | Supplier"
                                        {{ $currentStatus == 'ซ่อมงานเสร็จแล้ว | Supplier' ? 'selected' : '' }}>
                                        ซ่อมงานเสร็จแล้ว (Supplier)</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ $userRole == 'Interior' ? route('interior.list') : route('noti.list') }}"
                                    class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                                </a>
                                <button type="submit" class="btn btn-warning px-4">
                                    <i class="bi bi-save"></i> บันทึกการแก้ไข
                                </button>
                            </div>
                        </form> --}}
                        {{-- </div>
                        </div> --}}
