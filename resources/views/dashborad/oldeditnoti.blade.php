@extends('layout.mainlayout')

@section('title', 'แก้ไขข้อมูลแจ้งซ่อม')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-header bg-warning text-dark fw-bold py-3">
            <i class="bi bi-pencil-square me-2"></i> แก้ไขข้อมูลแจ้งซ่อม: {{ $noti->JobId }}
        </div>
        <div class="card-body p-4">
            <form action="{{ route('noti.update_info') }}" method="POST" id="editForm">
                @csrf
                {{-- ส่ง ID ไปเพื่อให้ Controller รู้ว่าต้อง Update แถวไหน --}}
                <input type="hidden" name="NotirepairId" value="{{ $noti->NotirepairId }}">
{{-- 
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">ชื่ออุปกรณ์</label>
                        <input type="text" name="equipmentName" class="form-control" 
                               value="{{ $noti->equipmentName}}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">รหัสสาขา</label>
                        <input type="text" name="branchCode" class="form-control" 
                               value="{{ $noti->branchCode }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">รายละเอียดอาการเสีย</label>
                        <textarea name="DeatailNotirepair" class="form-control" rows="4" required>{{ $noti->DeatailNotirepair }}</textarea>
                    </div>
                </div> --}}
                
                {{-- <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" aria-disabled="">ชื่ออุปกรณ์</label>
                        <input type="text" name="equipmentName" class="form-control" 
                               value="{{ $noti->equipmentName}}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold" disabled>รหัสสาขา</label>
                        <input type="text" name="branchCode" class="form-control" 
                               value="{{ $noti->branchCode }}" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">รายละเอียดอาการเสีย</label>
                        <textarea name="DeatailNotirepair" class="form-control" rows="4" readonly>{{ $noti->DeatailNotirepair }} </textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('noti.list') }}" class="btn btn-light border px-4">
                        <i class="bi bi-x-circle"></i> ยกเลิก
                    </a>
                    <button type="button" class="btn btn-warning px-4 fw-bold" onclick="confirmEdit()">
                        <i class="bi bi-check-lg"></i> บันทึกการแก้ไข
                    </button>
                </div> --}}
            </form>
        </div>
    </div>
</div>

<script>
    function confirmEdit() {
        if(confirm('คุณมั่นใจใช่ไหมที่จะแก้ไขข้อมูลพื้นฐานของรายการนี้?')) {
            document.getElementById('editForm').submit();
        }
    }
</script>
@endsection