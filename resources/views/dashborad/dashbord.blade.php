{{-- @extends('layout.mainlayout')

@section('title','รายการการซ่อม')

@section('content')

<h5 class="fw-bold text-dark mb-3">
  <i class="bi bi-list-task">รายการเเจ้งซ่อมทั้งหมด</i>

</h5>

<div class="card">
    
</div>
@endsection --}}
@extends('layout.mainlayout')

@section('title', 'Dashboard ภาพรวม')

@section('content')
<div class="container-fluid">
    {{-- หัวข้อ --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard ภาพรวม
        </h4>
        <button class="btn btn-primary btn-sm">
            <i class="bi bi-download me-1"></i> Export Report
        </button>
    </div>

    {{-- ส่วนที่ 1: การ์ดแสดงสถานะ (Stat Cards) --}}
    <div class="row g-4 mb-4">
        {{-- Card 1: แจ้งซ่อมทั้งหมด --}}
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 5px solid #0d6efd !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                แจ้งซ่อมทั้งหมด</div>
                            {{-- <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $countList ?? '0' }} รายการ</div> --}}
                            <div class="h3 mb-0 font-weight-bold text-gray-800">{{$countList}} รายการ</div>

                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clipboard-data fs-1 text-gray-300 text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: ซ่อมเสร็จแล้ว --}}
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 5px solid #198754 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                ซ่อมเสร็จแล้ว</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">{{$countComplete}} รายการ</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle-fill fs-1 text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: กำลังดำเนินการ (Option เพิ่มเติม) --}}
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 5px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                รอดำเนินการ</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">{{$countPending}} รายการ</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fs-1 text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
     {{-- Card 4: ยังไม่ได้รับของ (Option เพิ่มเติม) --}}
     <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 5px solid #198754 !important;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            ของที่ได้รับเเล้ว</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">{{$countItem}} รายการ</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle-fill fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- ส่วนที่ 2: กราฟแสดงผล (Chart Area) --}}
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-bottom-0">
                    <h6 class="m-0 fw-bold text-primary">สถิติการแจ้งซ่อม (รายเดือน)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;">
                        <canvas id="myAreaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ส่วนขวา: รายละเอียดเพิ่มเติม หรือ Pie Chart --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm mb-4 border-0 text-white bg-primary">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">ภาพรวมประสิทธิภาพ</h5>
                    <p>ระบบแจ้งซ่อมบำรุง MRO</p>
                    <div class="mt-4 text-center">
                         <h1 class="display-4 fw-bold">95%</h1>
                         <p class="small text-white-50">อัตราความสำเร็จในการซ่อม</p>
                    </div>
                     <hr class="border-light opacity-25">
                     <div class="d-flex justify-content-between small">
                        <span>เดือนนี้แจ้งซ่อม: 150</span>
                        <span>สำเร็จ: 142</span>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- เรียกใช้ Chart.js ผ่าน CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- ประกาศตัวแปร Default ใน PHP Block เพื่อไม่ให้ Blade สับสน --}}
@php
    $defaultLabels = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.'];
    $defaultDataTotal = [0, 10, 5, 2, 20, 30];
    $defaultDataFinished = [0, 8, 4, 2, 15, 25];
@endphp

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // รับค่าจาก Controller หรือใช้ค่า Default ที่ประกาศไว้ข้างบน
        const labels = @json($chart_labels ?? $defaultLabels);
        const dataTotal = @json($chart_data_total ?? $defaultDataTotal);
        const dataFinished = @json($chart_data_finished ?? $defaultDataFinished);

        const ctx = document.getElementById('myAreaChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'line', 
            data: {
                labels: labels,
                datasets: [{
                    label: 'แจ้งซ่อมทั้งหมด',
                    data: dataTotal,
                    backgroundColor: 'rgba(13, 110, 253, 0.1)', 
                    borderColor: 'rgba(13, 110, 253, 1)',      
                    borderWidth: 2,
                    tension: 0.4, 
                    fill: true
                },
                {
                    label: 'ซ่อมเสร็จแล้ว',
                    data: dataFinished,
                    backgroundColor: 'rgba(25, 135, 84, 0.1)', 
                    borderColor: 'rgba(25, 135, 84, 1)',      
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 4],
                            color: "#e9ecef"
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endpush