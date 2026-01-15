<?php

namespace App\Repository;

use App\Models\Notirepair;
use Illuminate\Support\Facades\DB;
use App\Models\Zone;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\ReturnValueNotConfiguredException;

class NotirepairRepository
{
    public static function getAllNotirepair()
    {
        return Zone::all();
    }
    public static function getAllStaffName()
    {
        return Zone::where('StaffName')->first();
    }
    public static function getAllNames()
    {
        return Zone::where('FirstName', 'LastName')->first();
    }
    public static function getSelectZoneEmail()
    {
        return Zone::whereNotNull('email')->first();
    }
    public static function getNameandZoneEmail()
    {
        return Zone::select(['StaffName', 'email'])
            ->whereNotNull('email')
            ->first();
    }
    public static function getZoneInfoByEmail($email)
    {
        return Zone::where('email', $email)
            ->first(); // ดึงข้อมูลของ zone ที่มี email ตรงกับที่ระบุ
    }
    public static function getEmailByCode($zoneId)
    {
        return Zone::where('zoneId', $zoneId)
            ->value('email'); // ดึง email ของ branch
    }
    public static function getemailZone($zonename)
    {
        return Zone::where('email', $zonename)->value('email');
    }
    // public static function save($branch){
    //     $notirepair = new Notirepair();
    //     $notirepair->branch = $branch;
    // }
    public static function saveNotiRepair($equipmentId, $DeatailNotirepair, $Zone, $branch, $branchCode)
    {
        $noti = new Notirepair();
        $noti->equipmentId = $equipmentId;
        $noti->DeatailNotirepair = $DeatailNotirepair;
        $noti->Zone = $Zone;
        $noti->branch = $branch;
        // 🛑 บันทึกรหัสสาขาใหม่ในคอลัมน์ branch_code ที่เพิ่มเข้ามา
        $noti->branchCode = $branchCode;
        //[]
        $noti->DateNotirepair = Carbon::now();
        $noti->save();
        return $noti;
    }
    // public static function findZoneEmailByName($zonename){
    //     return Zone::where('StaffName','=',$zonename)
    //         ->first(['email']);
    // }
    public static function findZoneEmailByName($zonename)
    {
        return Zone::where('StaffName', '=', $zonename)
            ->first()
            ->email;
    }

    //ส่วนของ dashbord store
    public static function getNotirepirById($notiRepairId)
    {
        return NotiRepair::where('NotirepairId', $notiRepairId)->get();
    }
    // public static function CountNotirepair(){
    //     return Notirepair::count('NotirepairId')->get();
    // }
    public static function CountNotirepair()
    {
        return Notirepair::count();
    }
    public static function HistoryNotirepair()
    {
        return Notirepair::select('NotirepairId', 'DateNotirepair', 'DeatailNotirepair', 'equipment.equipmentName')
            ->leftJoin('equipment', 'notirepair.equipmentId', '=', 'equipment.equipmentId')
            ->get();
    }

    ///ส่วน dashbod ของ Admin crud 
    public static function getAllNotirepairByadmin()
    {
        return Notirepair::all();
    }

    public static function getAllNotiRepairWithDetails()
    {
        // เรียก Model Notirepair เป็นตัวตั้ง
        return Notirepair::leftJoin('equipment', 'notirepair.equipmentId', '=', 'equipment.equipmentId')
            ->select(
                'notirepair.*',             // เอาข้อมูลแจ้งซ่อมทั้งหมด (รวม zone, branch ที่มีอยู่แล้ว)
                'equipment.equipmentName'   // ✅ ดึงเพิ่มแค่ชื่ออุปกรณ์
            )
            ->get();
    }
    //ถ้ากระบวนการการทำงานทั้งหมดเสร็จเเล้วให้กดปิดงานโดยหน้าร้าน
    //พอมีการซ่อมเสร็จสิ้นเเล้ว พนักงานหน้าร้านจะกดปุ่มปิดงาน

    // public static function findById($notirepaitid){
    //     return Notirepair::find($notirepaitid);
    // }

    //การจัดการสถานะ
    // public static function updateStatus($notiId, $status)
    // {
    //     return DB::connection('third')
    //         ->table('statustracking')
    //         ->insert([
    //             'NotirepairId' => $notiId,
    //             'status'       => $status,
    //             'statusDate'   => Carbon::now(),
    //         ]);
    // }
    public static function findById($id)
    {
        return Notirepair::find($id);
    }
    // public static function updateStatusTracking($notiId, $status)
    // {
    //     return DB::connection('third')
    //         ->table('statustracking')
    //         ->insert([
    //             'NotirepairId' => $notiId,
    //             'status'       => $status,
    //             'statusDate'   => Carbon::now(),
    //         ]);
    // }

    //15/1
    // NotirepairRepository.php

    public static function updateStatusTracking($notirepairid, $status, $staffcode, $staffname)
    {
        // ใช้ Query Builder หรือ Model ก็ได้ แต่ต้องมี staffname
        return DB::connection('third')->table('statustracking')->insert([
            'NotirepairId' => $notirepairid,
            'status'       => $status,      // 'ได้รับของคืนเรียบร้อย'
            'statusDate'   => now(),
            'staffcode'    => $staffcode,   // รหัสพนักงาน
            'staffname'    => $staffname,   // ✅ ชื่อพนักงาน (ถ้าบรรทัดนี้หายไป หน้าธุรการจะขึ้น N/A)
        ]);
    }
    //ดึงสถานะบ่าสุด
    public static function getCurrentStatus($notiId)
    {
        return DB::connection('third')
            ->table('statustracking')
            ->where('NotirepairId', $notiId)
            ->orderByDesc('statustrackingId')
            ->value('status');
    }
    public static function closeJobInMainTable($id)
    {
        return Notirepair::where('NotirepairId', $id)->update([
            'closedJobs' => 'ปิดงานเรียบร้อย',
            'DateCloseJobs' => Carbon::now()
        ]);
    }
    //ofiicer

    // public static function getTrackingListForAdmin($searchTerm = null, $perPage = 10)
    // {
    //     // 1. ดึง ID ล่าสุดของสถานะจากตาราง statustracking (DB ที่สาม)
    //     $latestStatusId = DB::connection('third')
    //         ->table('statustracking')
    //         ->select('NotirepairId', DB::raw('MAX(statustrackingId) as latest_id'))
    //         ->groupBy('NotirepairId');

    //     // 2. Query หลัก
    //     // $query = Notirepair::select(
    //     //         'notirepair.*',
    //     //         'equipment.equipmentName',
    //     //         DB::raw("COALESCE(latest_status.status, 'ยังไม่ได้รับของ') as current_status"),
    //     //         'latest_status.statusDate as last_status_date'
    //     //     )
    //     //     ->leftJoin('equipment', 'notirepair.equipmentId', '=', 'equipment.equipmentId')
    //     //     // Join เพื่อเอา ID ล่าสุด
    //     //     ->leftJoinSub($latestStatusId, 'latest_id_table', function ($join) {
    //     //         $join->on('notirepair.NotirepairId', '=', 'latest_id_table.NotirepairId');
    //     //     })
    //     //     // Join เพื่อเอาชื่อสถานะจริงจาก DB ที่สาม
    //     //     ->leftJoin(
    //     //         DB::raw(env('THIRD_DB_DATABASE') . '.statustracking as latest_status'),
    //     //         function ($join) {
    //     //             $join->on('latest_status.NotirepairId', '=', 'notirepair.NotirepairId')
    //     //                  ->on('latest_status.statustrackingId', '=', 'latest_id_table.latest_id');
    //     //         }
    //     //     );
    //     // 2. Query หลัก
    //     $query = Notirepair::select(
    //         'notirepair.*',
    //         'equipment.equipmentName',
    //         // ตั้งชื่อ alias ให้ชัดเจน ป้องกันการทับกับ column ใน table หลัก
    //         DB::raw("COALESCE(latest_status.status, 'ยังไม่ได้รับของ') as current_status"),
    //         'latest_status.statusDate as last_status_date'
    //     )
    //         ->leftJoin('equipment', 'notirepair.equipmentId', '=', 'equipment.equipmentId')
    //         ->leftJoinSub($latestStatusId, 'latest_id_table', function ($join) {
    //             $join->on('notirepair.NotirepairId', '=', 'latest_id_table.NotirepairId');
    //         })
    //         ->leftJoin(
    //             // ใช้ config แทน env เพื่อความเสถียร
    //             DB::raw(config('database.connections.third.database') . '.statustracking as latest_status'),
    //             function ($join) {
    //                 $join->on('latest_status.NotirepairId', '=', 'notirepair.NotirepairId')
    //                     ->on('latest_status.statustrackingId', '=', 'latest_id_table.latest_id');
    //             }
    //         );

    //     // 3. ระบบค้นหา (ถ้ามี)
    //     if ($searchTerm) {
    //         $query->where(function ($q) use ($searchTerm) {
    //             $q->where('notirepair.NotirepairId', 'like', "%$searchTerm%")
    //                 ->orWhere('notirepair.branchCode', 'like', "%$searchTerm%")
    //                 ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%");
    //         });
    //     }

    //     return $query->orderBy('notirepair.DateNotirepair', 'desc')
    //         ->paginate($perPage)
    //         ->withQueryString();
    // }
    // NotirepairRepository.php
    // NotirepairRepository.php
    // public static function getTrackingListForAdmin($searchTerm = null, $statusFilter = null, $perPage = 10)
    // {
    //     $latestStatusId = DB::connection('third')
    //         ->table('statustracking')
    //         ->select('NotirepairId', DB::raw('MAX(statustrackingId) as latest_id'))
    //         ->groupBy('NotirepairId');

    //     $query = Notirepair::select(
    //             'notirepair.*',
    //             'equipment.equipmentName',
    //             // ตรวจสอบจากฟิลด์ closedJobs ตาม image_55bb2a.png
    //             DB::raw("CASE 
    //                 WHEN notirepair.closedJobs != 'ยังไม่ปิดงาน' THEN 'ปิดงานเรียบร้อย'
    //                 ELSE COALESCE(latest_status.status, 'ยังไม่ได้รับของ')
    //             END as current_status"),
    //             'latest_status.statusDate as last_status_date'
    //         )
    //         ->leftJoin('equipment', 'notirepair.equipmentId', '=', 'equipment.equipmentId')
    //         ->leftJoinSub($latestStatusId, 'latest_id_table', function ($join) {
    //             $join->on('notirepair.NotirepairId', '=', 'latest_id_table.NotirepairId');
    //         })
    //         ->leftJoin(
    //             DB::raw(config('database.connections.third.database') . '.statustracking as latest_status'),
    //             function ($join) {
    //                 $join->on('latest_status.NotirepairId', '=', 'notirepair.NotirepairId')
    //                      ->on('latest_status.statustrackingId', '=', 'latest_id_table.latest_id');
    //             }
    //         );

    //     // ระบบค้นหา
    //     if ($searchTerm) {
    //         $query->where(function ($q) use ($searchTerm) {
    //             $q->where('notirepair.NotirepairId', 'like', "%$searchTerm%")
    //               ->orWhere('notirepair.branchCode', 'like', "%$searchTerm%")
    //               ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%");
    //         });
    //     }

    //     // กรองตามสถานะจริงจาก image_55af92.png
    //     if ($statusFilter) {
    //         if ($statusFilter === 'ปิดงานเรียบร้อย') {
    //             $query->where('notirepair.closedJobs', '!=', 'ยังไม่ปิดงาน');
    //         } else {
    //             $query->where('latest_status.status', $statusFilter)
    //                   ->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน');
    //         }
    //     }

    //     return $query->orderBy('notirepair.DateNotirepair', 'desc')
    //                  ->paginate($perPage)
    //                  ->withQueryString();
    // }

    //15/1
    public static function getTrackingListForAdmin($searchTerm = null, $statusFilter = null, $perPage = 10)
    {
        // 1) Subquery หาสถานะล่าสุด (เพื่อหาคนปิดงาน)
        $latestStatusIdSub = DB::connection('third')->table('statustracking')
            ->select('NotirepairId', DB::raw('MAX(statustrackingId) as max_id'))
            ->groupBy('NotirepairId');
    
        // 2) Subquery คนรับของ
        $receiverSub = DB::connection('third')->table('statustracking')
            ->where('status', 'LIKE', '%ได้รับของ%')
            ->select('NotirepairId', 'staffname as receiver_name');
    
        // ✅ 3) เพิ่ม Subquery วันที่ได้รับของ (ดึง statusDate ของบรรทัด 'ได้รับของแล้ว')
        $receivedDateSub = DB::connection('third')->table('statustracking')
            ->where('status', 'ได้รับของแล้ว')
            ->select('NotirepairId', 'statusDate as received_at');
    
        $query = DB::connection('third')->table('notirepair')
            ->select(
                'notirepair.*',
                'equipment.equipmentName',
                'latest_status.status as db_status',
                'latest_status.statusDate as last_update',
                'latest_status.staffname as closer_name',
                'rc.receiver_name',
                // ✅ ดึงวันที่ได้รับของออกมาใช้
                'rd.received_at', 
                DB::raw("CASE 
                    WHEN notirepair.closedJobs != 'ยังไม่ปิดงาน' THEN 'ปิดงานเรียบร้อย'
                    ELSE COALESCE(latest_status.status, 'ยังไม่ได้รับของ')
                END as current_status")
            )
            ->leftJoin('equipment', 'notirepair.equipmentId', '=', 'equipment.equipmentId')
            ->leftJoinSub($latestStatusIdSub, 'ls_id', 'notirepair.NotirepairId', '=', 'ls_id.NotirepairId')
            ->leftJoin('statustracking as latest_status', 'ls_id.max_id', '=', 'latest_status.statustrackingId')
            ->leftJoinSub($receiverSub, 'rc', 'notirepair.NotirepairId', '=', 'rc.NotirepairId')
            // ✅ Join เพื่อดึงวันที่รับของ
            ->leftJoinSub($receivedDateSub, 'rd', 'notirepair.NotirepairId', '=', 'rd.NotirepairId');
    
        // ... (ส่วน Filter searchTerm และ statusFilter เหมือนเดิมของคุณ) ...
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('notirepair.NotirepairId', 'like', "%$searchTerm%")
                    ->orWhere('notirepair.branchCode', 'like', "%$searchTerm%")
                    ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%");
            });
        }
    
        if ($statusFilter) {
            if ($statusFilter === 'ปิดงานเรียบร้อย') {
                $query->where('notirepair.closedJobs', '!=', 'ยังไม่ปิดงาน');
            } else {
                $query->where('latest_status.status', $statusFilter)
                    ->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน');
            }
        }
    
        return $query->orderBy('notirepair.DateNotirepair', 'desc')->paginate($perPage);
    }
    //15/1
    // public static function getTrackingListForAdmin($searchTerm = null, $statusFilter = null, $perPage = 10)
    // {
    //     /*
    //     |--------------------------------------------------
    //     | 1) subquery หา statustrackingId ล่าสุดต่อ 1 งาน
    //     |--------------------------------------------------
    //     */
    //     $latestStatusIdSub = DB::connection('third')->table('statustracking')
    //         ->select('NotirepairId', DB::raw('MAX(statustrackingId) as max_id'))
    //         ->groupBy('NotirepairId');

    //     /*
    //     |--------------------------------------------------
    //     | 2) subquery คนรับของ
    //     |--------------------------------------------------
    //     */
    //     // $receiverSub = DB::connection('third')->table('statustracking as st')
    //     //     ->leftJoin(
    //     //         DB::connection('mysql')->getDatabaseName() . '.staff_rc as src',
    //     //         'st.staffcode',
    //     //         '=',
    //     //         'src.staffcode'
    //     //     )
    //     //     ->where('st.status', 'ได้รับของเเล้ว')
    //     //     ->select(
    //     //         'st.NotirepairId',
    //     //         'src.staffName as receiver_name'
    //     //     );
    // /* 2) subquery คนรับของ */
    // $receiverSub = DB::connection('third')->table('statustracking')
    //     ->where('status', 'LIKE', '%ได้รับของ%') // ใช้ LIKE ปลอดภัยกว่าสระ แ หรือ เเ
    //     ->select(
    //         'NotirepairId',
    //         'staffname as receiver_name' // ดึงจากคอลัมน์ staffname ที่คุณเพิ่งเพิ่มเข้าไป
    //     );
    //     /*
    //     |--------------------------------------------------
    //     | 3) query หลัก 
    //     |--------------------------------------------------
    //     */
    //     $query = DB::connection('third')->table('notirepair')
    //         ->select(
    //             'notirepair.*',
    //             'equipment.equipmentName',

    //             // สถานะจาก statustracking ล่าสุด
    //             'latest_status.status as db_status',
    //             'latest_status.statusDate as last_update',

    //             // ชื่อคนปิดงาน (อิงจากสถานะล่าสุด)
    //             'closer.staffName as closer_name',

    //             // ชื่อคนรับของ
    //             'rc.receiver_name',

    //             // current_status (คง logic เดิมของคุณ)
    //             DB::raw("
    //                 CASE 
    //                     WHEN notirepair.closedJobs != 'ยังไม่ปิดงาน'
    //                         THEN 'ปิดงานเรียบร้อย'
    //                     ELSE COALESCE(latest_status.status, 'ยังไม่ได้รับของ')
    //                 END as current_status
    //             ")
    //         )

    //         // equipment
    //         ->leftJoin('equipment', 'notirepair.equipmentId', '=', 'equipment.equipmentId')

    //         // join หา statustracking ล่าสุด
    //         ->leftJoinSub(
    //             $latestStatusIdSub,
    //             'ls_id',
    //             'notirepair.NotirepairId',
    //             '=',
    //             'ls_id.NotirepairId'
    //         )
    //         ->leftJoin(
    //             'statustracking as latest_status',
    //             'ls_id.max_id',
    //             '=',
    //             'latest_status.statustrackingId'
    //         )

    //         // join คนปิดงาน (staff_rc)
    //         ->leftJoin(
    //             DB::connection('mysql')->getDatabaseName() . '.staff_rc as closer',
    //             'latest_status.staffcode',
    //             '=',
    //             'closer.staffcode'
    //         )

    //         // join คนรับของ
    //         ->leftJoinSub(
    //             $receiverSub,
    //             'rc',
    //             'notirepair.NotirepairId',
    //             '=',
    //             'rc.NotirepairId'
    //         );

    //     /*
    //     |--------------------------------------------------
    //     | 4) Search
    //     |--------------------------------------------------
    //     */
    //     if ($searchTerm) {
    //         $query->where(function ($q) use ($searchTerm) {
    //             $q->where('notirepair.NotirepairId', 'like', "%$searchTerm%")
    //               ->orWhere('notirepair.branchCode', 'like', "%$searchTerm%")
    //               ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%")
    //               ->orWhere('closer.staffName', 'like', "%$searchTerm%")
    //               ->orWhere('rc.receiver_name', 'like', "%$searchTerm%");
    //         });
    //     }

    //     /*
    //     |--------------------------------------------------
    //     | 5) Filter สถานะ (คง logic เดิม)
    //     |--------------------------------------------------
    //     */
    //     if ($statusFilter) {
    //         if ($statusFilter === 'ปิดงานเรียบร้อย') {
    //             $query->where('notirepair.closedJobs', '!=', 'ยังไม่ปิดงาน');
    //         } elseif ($statusFilter === 'ยังไม่ได้รับของ') {
    //             $query->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน')
    //                 ->where(function ($q) {
    //                     $q->whereNull('latest_status.status')
    //                       ->orWhere('latest_status.status', 'LIKE', '%ยังไม่ได้รับของ%');
    //                 });
    //         } else {
    //             $cleanFilter = trim($statusFilter);
    //             $query->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน')
    //                   ->where('latest_status.status', 'LIKE', "%$cleanFilter%");
    //         }
    //     }

    //     return $query
    //         ->orderBy('notirepair.DateNotirepair', 'desc')
    //         ->paginate($perPage);
    // }



    //old
    // public static function getTrackingListForAdmin($searchTerm = null, $statusFilter = null, $perPage = 10)
    // {
    //     // 1. ระบุ connection('third') เพื่อหา ID ล่าสุดจากตาราง statustracking
    //     $latestStatusQuery = DB::connection('third')->table('statustracking')
    //         ->select('NotirepairId', DB::raw('MAX(statustrackingId) as max_id'))
    //         ->groupBy('NotirepairId');

    //     // 2. ระบุ connection('third') ที่ตารางหลัก (notirepair)
    //     $query = DB::connection('third')->table('notirepair')
    //         ->select(
    //             'notirepair.*',
    //             'equipment.equipmentName',
    //             'latest_status.status as db_status',
    //             'latest_status.statusDate as last_update',
    //             // ตรวจสอบเงื่อนไขปิดงานจากฟิลด์ closedJobs
    //             DB::raw("CASE 
    //             WHEN notirepair.closedJobs != 'ยังไม่ปิดงาน' THEN 'ปิดงานเรียบร้อย'
    //             ELSE COALESCE(latest_status.status, 'ยังไม่ได้รับของ')
    //         END as current_status")
    //         )
    //         // ตารางที่ Join ต้องอยู่ใน connection เดียวกันหรือระบุชื่อ database นำหน้า
    //         ->leftJoin('equipment', 'notirepair.equipmentId', '=', 'equipment.equipmentId')
    //         ->leftJoinSub($latestStatusQuery, 'ls_id', 'notirepair.NotirepairId', '=', 'ls_id.NotirepairId')
    //         ->leftJoin('statustracking as latest_status', 'ls_id.max_id', '=', 'latest_status.statustrackingId');
    //         //letfjoin subคิวรี่ คนรับของคนปิดงาน

    //     // 3. กรองข้อมูล (Search)
    //     if ($searchTerm) {
    //         $query->where(function ($q) use ($searchTerm) {
    //             $q->where('notirepair.NotirepairId', 'like', "%$searchTerm%")
    //                 ->orWhere('notirepair.branchCode', 'like', "%$searchTerm%")
    //                 ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%")
    //                 ->orWhere(DB::raw("CASE 
    //                 WHEN notirepair.closedJobs != 'ยังไม่ปิดงาน' THEN 'ปิดงานเรียบร้อย'
    //                 ELSE COALESCE(latest_status.status, 'ยังไม่ได้รับของ')
    //             END"), 'like', "%$searchTerm%");
    //         });
    //     }

    //     if ($statusFilter) {
    //         if ($statusFilter === 'ปิดงานเรียบร้อย') {
    //             $query->where('notirepair.closedJobs', '!=', 'ยังไม่ปิดงาน');
    //         } elseif ($statusFilter === 'ยังไม่ได้รับของ') {
    //             $query->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน')
    //                 ->where(function ($q) {
    //                     $q->whereNull('latest_status.status')
    //                         ->orWhere('latest_status.status', 'LIKE', '%ยังไม่ได้รับของ%');
    //                         // ->orWhere('latest_status.status','LIKE','%ได้รับของเเล้ว%');
    //                 });
    //         } else {
    //             // สำหรับสถานะอื่นๆ เช่น 'ได้รับของแล้ว', 'ส่งSuplierแล้ว'
    //             // แนะนำให้ตัดคำยาวๆ ให้สั้นลง หรือใช้ส่วนหนึ่งของคำในการค้นหา
    //             // หรือใช้ trim() และจัดการเรื่องสระเอสองตัว (Optional)
    //             $cleanFilter = trim($statusFilter);
    //             $query->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน')
    //                 ->where('latest_status.status', 'LIKE', "%$cleanFilter%");
    //         }
    //     }
    //     return $query->orderBy('notirepair.DateNotirepair', 'desc')->paginate($perPage);
    // }
    // 4. กรองตามสถานะ (Status Filter)
    // if ($statusFilter) {
    //     if ($statusFilter === 'ปิดงานเรียบร้อย') {
    //         $query->where('notirepair.closedJobs', '!=', 'ยังไม่ปิดงาน');
    //     } elseif ($statusFilter === 'ยังไม่ได้รับของ') {
    //         $query->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน')
    //             ->where(function ($q) {
    //                 $q->whereNull('latest_status.status')
    //                     ->orWhere('latest_status.status', '=', 'ยังไม่ได้รับของ');
    //             });
    //     } else {
    //         $query->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน')
    //             ->where('latest_status.status', '=', $statusFilter);
    //     }

    // }
    // 4. ส่วนการกรองจาก Dropdown (Select Filter)
    // ไฟล์ NotirepairRepository.php ส่วนการกรอง (Status Filter)
    // 4. กรองตามสถานะ (Status Filter)
    // if ($statusFilter) {
    //     if ($statusFilter === 'ปิดงานเรียบร้อย') {
    //         $query->where('notirepair.closedJobs', '!=', 'ยังไม่ปิดงาน');
    //     } else {
    //         $query->where('latest_status.status', $statusFilter)
    //               ->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน');
    //     }
    // }
    // if ($statusFilter) {
    //     if ($statusFilter === 'ปิดงานเรียบร้อย') {
    //         // กรณีปิดงาน: เช็คจากตารางหลัก
    //         $query->where('notirepair.closedJobs', '!=', 'ยังไม่ปิดงาน');
    //     } 
    //     elseif ($statusFilter === 'ยังไม่ได้รับของ') {
    //         // กรณี "ยังไม่ได้รับของ": ต้องเช็คทั้งคนที่มีสถานะนี้ และคนที่ "ยังไม่มีประวัติเลย" (NULL)
    //         $query->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน')
    //               ->where(function($q) {
    //                   $q->where('latest_status.status', 'ยังไม่ได้รับของ')
    //                     ->orWhereNull('latest_status.status'); 
    //               });
    //     }
    //     else {
    //         // สถานะอื่น ๆ (ได้รับของแล้ว, ส่งSuplierแล้ว ฯลฯ): เช็คจากตาราง Tracking
    //         $query->where('latest_status.status', '=', $statusFilter)
    //               ->where('notirepair.closedJobs', '=', 'ยังไม่ปิดงาน');
    //     }
    // }
    // if ($statusFilter) {
    //     $query->whereRaw("(CASE 
    //         WHEN notirepair.closedJobs != 'ยังไม่ปิดงาน' THEN 'ปิดงานเรียบร้อย'
    //         ELSE COALESCE(latest_status.status, 'ยังไม่ได้รับของ')
    //     END) = ?", [$statusFilter]);
    // }
}
