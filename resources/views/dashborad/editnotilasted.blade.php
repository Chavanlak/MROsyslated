@extends('layout.mainlayout')

@section('title', 'แก้ไขข้อมูลการแจ้งซ่อม')

@section('content')
    <h5 class="fw-bold text-primary mb-4">
        <i class="bi bi-pencil-square"></i> แก้ไขสถานะการแจ้งซ่อม (โหมดแก้ไข)
    </h5>

    @php
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

    {{-- ส่วนแสดงรายละเอียดเครื่อง (เหมือนหน้า Update) --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>รหัสแจ้งซ่อม</th>
                            <th>อุปกรณ์</th>
                            <th>สถานะปัจจุบัน</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <tr>
                            <td>{{$updatenoti->NotirepairId}}</td>
                            <td>{{$updatenoti->equipmentName}}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ $currentStatus }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ฟอร์มแก้ไข --}}
    <div class="card shadow-sm">
        <div class="card-body">
            {{-- ใช้ Action เดียวกับหน้าอัปเดตสถานะ --}}
            <form action="{{ route('notiupdate') }}" method="POST">
                @csrf
                <input type="hidden" name="NotirepairId" value="{{$updatenoti->NotirepairId}}">
                <input type="hidden" name="statusDate" value="{{ date('Y-m-d H:i:s') }}">

                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">ระบุสถานะที่ถูกต้อง:</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="" disabled>--- กรุณาเลือกสถานะที่ต้องการแก้ไข ---</option>
                        
                        {{-- ใส่รายการสถานะทั้งหมดที่มีในระบบ เพื่อให้เลือกแก้ไขได้อิสระ --}}
                        <option value="ยังไม่ได้รับของ" {{ $currentStatus == 'ยังไม่ได้รับของ' ? 'selected' : '' }}>ยังไม่ได้รับของ</option>
                        <option value="ได้รับของแล้ว" {{ $currentStatus == 'ได้รับของแล้ว' ? 'selected' : '' }}>ได้รับของแล้ว</option>
                        <option value="กำลังดำเนินการซ่อม | ช่างStore" {{ $currentStatus == 'กำลังดำเนินการซ่อม | ช่างStore' ? 'selected' : '' }}>กำลังดำเนินการซ่อม (ช่าง Store)</option>
                        <option value="ส่งSuplierแล้ว" {{ $currentStatus == 'ส่งSuplierแล้ว' ? 'selected' : '' }}>ส่ง Supplier แล้ว</option>
                        <option value="ซ่อมงานเสร็จแล้ว | ช่างStore" {{ $currentStatus == 'ซ่อมงานเสร็จแล้ว | ช่างStore' ? 'selected' : '' }}>ซ่อมงานเสร็จแล้ว (ช่าง Store)</option>
                        <option value="ซ่อมงานเสร็จแล้ว | Supplier" {{ $currentStatus == 'ซ่อมงานเสร็จแล้ว | Supplier' ? 'selected' : '' }}>ซ่อมงานเสร็จแล้ว (Supplier)</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('noti.list') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> ย้อนกลับ
                    </a>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-save"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection