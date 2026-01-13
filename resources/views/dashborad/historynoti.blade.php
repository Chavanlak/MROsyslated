@extends('layout.mainlayout')
@section('title','รายการการเเจ้งซ่อม')
@section('content')


<h5 class="fw-bold text-dark mb-3">
    <i class="bi bi-list-task"></i> รายละเอียดการเเจ้งซ่อม
</h5>

<div class="card shadow-sm d-none d-md-block">
    <div class="card-body table-responsive">
        <table id="notiTable" class="table table-hover align-middle">
            <thead class="table-primary text-center">
                <tr>
                    {{-- <th>รหัสเเจ้งซ่อม</th>
                    <th>อุปกรณ์</th>
                    <th>รายละเอียดการเเจ้งซ่อม</th>
                    <th>วันที่เเจ้งซ่อม</th>
                    <th>วันี่อัพเดทล่าสุด</th>
                    <th>สถานะล่าสุด</th>
                    <th>วันที่ปิดงาน</th>
                    <th>สถานะการปิดงาน</th> --}}
                    <th>รหัสเเจ้งซ่อม</th>
                    <th>รายละเอียดการเเจ้งซ่อม</th>
                    <th>วันที่เเจ้งซ่อม</th>
                    <th>ชื่ออุปกรณ์</th>
                    <th>จากสาขา</th>
                    <th>จากเมล</th>
                    <th>เเจ้งซ่อมโดย</th>
                    <th>สังกัดโซน</th>
                </tr>
            </thead>
            <tbody class="text-center">
                @foreach ($notirepairList as $item)

                <tr>
                    <td style="width:10%">{{$item->NotirepairId}}</td>
                    <td>{{ $item->DeatailNotirepair}}</td>
                    <td>{{ $item->DateNotirepair}}</td>
                    <td>{{ $item->equipmentName}}</td>
                </tr>
                @endforeach
                
            </tbody>
        </table>
    </div>

</div>
@endsection
