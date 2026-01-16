<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use App\Repository\MastbranchRepository;
use App\Repository\NotirepairRepository;
use App\Repository\EquipmentRepository;
use App\Repository\EquipmentTypeRepository;
use App\Repository\PermissionBMRepository;
use App\Repository\StatustrackingRepository;

use App\Models\Notirepair;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use App\Http\Requests\StoreFileRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\EmailCenter;
use App\Mail\NotiMail;
use App\Repository\UserRepository;
use Illuminate\Support\Facades\DB;

use Illuminate\Notifications\Notification;

use function PHPUnit\Framework\directoryExists;

class NotiRepairController extends Controller
{
    // public static function getallManegers(){
    //     $manegers = NotirepairRepository::getAllNotirepair();
    //     return view('notirepair',compact('manegers'));
    // }
    public static function getallManegers()
    {
        $manegers = NotirepairRepository::getAllNames();
        return view('/branch', compact('manegers'));
    }

    public static function showallManegers()
    {
        $manegers = NotirepairRepository::getAllNotirepair();
        return view('zone', ['manegers' => $manegers]);
    }


    public static function showallZoneEmail()
    {
        $zoneEmail = NotirepairRepository::getSelectZoneEmail();
        return view('zoneemail', compact('zoneEmail'));
    }
    public function handleForm(Request $request)
    {
        $request->validate([
            'branch' => 'required|string',
            'zone' => 'required|string',
            'equipment' => 'required|string',
        ]);

        // เก็บลง session หรือส่งต่อ
        session([
            'selected_branch' => $request->branch,
            'selected_zone' => $request->zone,
            'selected_equipment' => $request->category,
        ]);

        return redirect('repair/form'); // หรือแสดงหน้าถัดไป
    }

    public static function ShowRepairForm()
    {
        $permis = Session::get('permis_BM');
        $manegers = NotirepairRepository::getAllNotirepair();
        $equipmenttype = EquipmentTypeRepository::getallEquipmentType();
        if ($permis == 'N' || $permis == 'n') {
            $branch = MastbranchRepository::selectbranch();
            return view('repair', compact('branch', 'manegers', 'equipmenttype'));
        } else {
            $branchid = PermissionBMRepository::getBranchCode(Session::get('staffcode'));
            $branchname = MastbranchRepository::getBranchName($branchid);
            return view('repairBM', compact('branchid', 'branchname', 'manegers', 'equipmenttype'));
        }
    }
    public function ShowRepairFormBM()
    {
        // 1. ดึงข้อมูล User ปัจจุบัน
        $staffcode = Session::get('staffcode');

        // 2. ดึงรหัสสาขาของ BM คนนี้
        $branchid = PermissionBMRepository::getBranchCode($staffcode);

        // 3. ดึงชื่อสาขา
        $branchname = MastbranchRepository::getBranchName($branchid);

        // 4. ดึงข้อมูลอื่นๆ ที่ต้องใช้ในหน้าเว็บ (Zone และ หมวดหมู่)
        $manegers = NotirepairRepository::getAllNotirepair(); // เอาไว้เลือก Zone
        $equipmenttype = EquipmentTypeRepository::getallEquipmentType(); // เอาไว้เลือกหมวดหมู่

        // 5. ส่งไปที่ View 'repairBM' (ไฟล์ที่คุณเพิ่งสร้าง)
        return view('repairBM', compact('branchid', 'branchname', 'manegers', 'equipmenttype'));
    }

    public static function saveNotiRepair(Request $req)
    {
        $formToken = $req->input('submission_token');
        $sessionToken = Session::get('submission_token');
        if (!$formToken || $formToken !== $sessionToken) {
            return redirect()->back()->with('error', 'ฟอร์มนี้ถูกส่งไปแล้ว กรุณาอย่าส่งซ้ำ');
        }
        $maxSize = 25 * 1024 * 1024;
        $countfiles = count($req->file('filepic'));
        if ($countfiles > 5) {
            return redirect()->back()->with('error', 'อัพโหลดได้ไม่เกิน 5 ไฟล์ กรุณาเลือกไฟล์ใหม่');
        }
        foreach ($req->file('filepic') as $file) {
            if ($file->getSize() > $maxSize) {
                // return response()->json(['error' => 'File size exceeds the 25 MB limit.'], 413);
                return redirect()->back()->with('error', 'ขนาดไฟล์เกิน 25 MB กรุณาเลือกไฟล์ใหม่');
            }
        }
        Session::forget('submission_token');
        // 🛑 1. ดึง Branch Code จาก Session ก่อนบันทึก
        $userBranchCode = $req->input('branchCode');

        if (empty($userBranchCode)) {
            // จัดการกรณีที่ไม่พบรหัสสาขา
            // หากโค้ดมาถึงตรงนี้ แสดงว่าค่า $req->input('branchCode') เป็น null
            return redirect()->back()->with('error', 'ไม่พบรหัสสาขาในระบบ กรุณาล็อกอินใหม่');
        }

        // บันทึกข้อมูล (โดยใช้ $userBranchCode ที่ได้มาจากการแก้ไขข้างต้น)
        $noti = NotirepairRepository::saveNotiRepair($req->category, $req->detail, $req->email2, $req->email1, $userBranchCode);
        // if ($noti) { // ตรวจสอบให้แน่ใจว่าเป็น Model instance จริงๆ
        //     $noti->branch_code = $userBranchCode;
        //     $noti->save();
        // }
        // $uploadedFiles = []; // เก็บ path ของไฟล์ที่จะส่งทางเมล

        // $mimeType = [];
        // $branchEmail = MastbranchRepository::getallBranchEmail();
        foreach ($req->file('filepic') as $file) {
            $file->getClientOriginalName();
            $filename = explode('.', $file->getClientOriginalName());
            $fileName = $filename[0] . "upload" . date("Y-m-d") . "." . $file->getClientOriginalExtension();
            $path = Storage::putFileAs('public/', $file, $fileName);
            $fileup = new FileUpload();
            $fileup->filename = $fileName;
            $fileup->filepath = $path;
            $fileup->NotirepairId = $noti->NotirepairId;
            $fileup->save();
            $realPath = Storage::path($path);
            $imageData = Storage::get($path);

            // $uploadedFiles[] = [
            //     'data' => base64_encode($imageData),
            //     'mime' => str_replace('image/', '', mime_content_type($realPath))
            // ];
        }

        $branchDisplay = $req->branchid . ' ' . $req->branch;

        if ($req->email1 == 'example@mail.com') {

            $data = [

                'title' => 'เเจ้งซ่อมอุปกรณ์',
                // 'img' => $uploadedFiles,
                // 'mime'=>$mimeType,
                'linkmail' => url("picshow/" . $noti->NotirepairId),
                'branch' => 'ไม่มีอีเมลสาขา',
                'branchname' => $branchDisplay,
                // 'branchname'=>$req->branch,
                //branch มาจาก <input type="text" name="branch" value="{{ $branchname }}">
                'name' => $req->session()->get('staffname'),
                // 'branchname'=>$branchname,

                //ใช้อันนี้
                // 'zone'=>$req->zone,
                'zone' => $req->email2,
                //zone มาจาก <input type="text" name="zone" value="{{ $zonename}}"> หน้าrepair2
                'staffname' => $req->zone,
                'equipmentname' => EquipmentRepository::getEquipmentnameByID($req->category)->equipmentName,
                'detail' => $req->detail
            ];
        } else {

            $data = [

                'title' => 'เเจ้งซ่อมอุปกรณ์',
                // 'img' => $uploadedFiles,
                // 'mime'=>$mimeType,
                'linkmail' => url("picshow/" . $noti->NotirepairId),
                // 'branchname'=>$req->branchname,
                // 'emailZone'=>$req->emailZone,
                // 'zonename'=>$req->zonename,
                'branch' => $req->email1,
                // 'branchname'=>$req->branch,
                'branchname' => $branchDisplay,
                //branch มาจาก <input type="text" name="branch" value="{{ $branchname }}">
                'name' => $req->session()->get('staffname'),
                // 'branchname'=>$branchname,

                //ใช้อันนี้
                // 'zone'=>$req->zone,
                'zone' => $req->email2,
                //zone มาจาก <input type="text" name="zone" value="{{ $zonename}}"> หน้าrepair2
                'staffname' => $req->zone,
                'equipmentname' => EquipmentRepository::getEquipmentnameByID($req->category)->equipmentName,
                'detail' => $req->detail
            ];
        }
        // dd($data);
        //   cc
        $toRecipient = $req->email3;
        $ccRecipients = [];

        if (!empty($req->email1)) {
            $ccRecipients[] = $req->email1;
        }
        if (!empty($req->email2)) {
            $ccRecipients[] = $req->email2;
        }
        $dateNotirepair = date("Ymd", strtotime($noti->DateNotirepair));
        $branchCode = $req->branchid;
        $today = Carbon::parse($noti->DateNotirepair)->toDateString();
        $dailyCount = Notirepair::whereDate('DateNotirepair', $today)->count();
        $paddedId = str_pad($dailyCount, 3, '0', STR_PAD_LEFT);
        // $subjectname = "เเจ้งปัญหา #MRO-" . $branchCode . "-" . $dateNotirepair . "-" . $paddedId;
        $JobId = "MRO-" . $branchCode . "-" . $dateNotirepair . "-" . $paddedId;
        $noti->JobId = $JobId;
        $noti->save();
        $subjectname = "แจ้งปัญหา #" . $JobId;
        // $equipmentname = EquipmentRepository::getEquipmentnameByID($req->category)->equipmentName;
        // $subjectname = "แจ้งซ่อมอุปกรณ์ " . $equipmentname . " จากสาขา " . $branchDisplay;

        Mail::to($toRecipient)
            ->cc($ccRecipients) // Add all CC recipients at once.
            ->send(new NotiMail($data, $subjectname));

        //ใช้อันนี้
        // Mail::to($req->email1)->send(new NotiMail($data));
        // Mail::to($req->email2)->send(new NotiMail($data));
        // Mail::to($req->email3)->send(new NotiMail($data));
        // dd("Email sent successfully!");
        // $recipients = [
        //     $req->email1,
        //     $req->email2,
        //     $req->email3,
        // ];

        // Mail::to($recipients)->send(new NotiMail($data));
        return redirect()->route('success');
    }

    //ส่วนของ dashbordช่าง
    // public static function checkNotiRepair(Request $request)
    // {
    //     //ส่วนของหน้า login
    //     $role = Session::get('role');
    //     if ($role === 'AdminTechnicianStore') {
    //         $searchTerm = $request->input('search');

    //         // 1) ดึงสถานะล่าสุดจากฐานที่สาม
    //         $latestStatusId = DB::connection('third')
    //             ->table('statustracking')
    //             ->select('NotirepairId', DB::raw('MAX(statustrackingId) as latest_id'))
    //             ->groupBy('NotirepairId');

    //         $query = NotiRepair::select(
    //             'notirepair.branchCode', // ต้อง Select คอลัมน์ branch มาด้วย
    //             'notirepair.*',
    //             DB::raw("COALESCE(latest_status.status, 'ยังไม่ได้รับของ') as status"),
    //             'latest_status.statusDate as statusDate',
    //             'equipment.equipmentName as equipmentName'
    //             // 'latest_status.status as status',
    //             // 'latest_status.statusDate as statusDate',
    //             // 'equipment.equipmentName as equipmentName'
    //         )
    //             ->leftJoin('equipment', 'equipment.equipmentId', '=', 'notirepair.equipmentId')
    //             // 2) Join subquery
    //             ->leftJoinSub($latestStatusId, 'latest_id_table', function ($join) {
    //                 $join->on('notirepair.NotirepairId', '=', 'latest_id_table.NotirepairId');
    //             })

    //             // 3) Join ตาราง statustracking จากฐานข้อมูล third
    //             ->leftJoin(
    //                 DB::raw(env('THIRD_DB_DATABASE') . '.statustracking as latest_status'),
    //                 function ($join) {
    //                     $join->on('latest_status.NotirepairId', '=', 'notirepair.NotirepairId')
    //                         ->on('latest_status.statustrackingId', '=', 'latest_id_table.latest_id');
    //                 }
    //             )

    //             // 4) Filter
    //             ->where(function ($q) {
    //                 $q->where('latest_status.status', '!=', 'ยังไม่ได้รับของ');
    //             })
    //             ->orderBy('notirepair.DateNotirepair', 'desc');

    //         // 5) search keyword
    //         if ($searchTerm) {
    //             $query->where(function ($q) use ($searchTerm) {
    //                 $q->where('notirepair.NotirepairId', 'like', "%$searchTerm%")
    //                     ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%")
    //                     ->orWhere('notirepair.branchCode', 'like', "%$searchTerm%") // ค้นหาด้วยรหัสสาขา
    //                     ->orWhere('notirepair.DeatailNotirepair', 'like', "%$searchTerm%")
    //                     ->orWhere('latest_status.status', 'like', "%$searchTerm%");
    //             });
    //         }

    //         $noti = $query->paginate(5)->withQueryString();
    //         return view('dashborad.notirepairlist', compact('noti'));
    //     }
    // }
    public static function checkNotiRepair(Request $request)
    {
        $role = Session::get('role');
        if ($role === 'AdminTechnicianStore') {
            $searchTerm = $request->input('search');

            // 1. หา ID สถานะล่าสุด
            $latestStatusId = DB::connection('third')
                ->table('statustracking')
                ->select('NotirepairId', DB::raw('MAX(statustrackingId) as latest_id'))
                ->groupBy('NotirepairId');

            $query = NotiRepair::select(
                'notirepair.*',
                DB::raw("COALESCE(latest_status.status, 'ยังไม่ได้รับของ') as status"),
                'latest_status.statusDate as statusDate',
                'equipment.equipmentName as equipmentName'
            )
                ->leftJoin('equipment', 'equipment.equipmentId', '=', 'notirepair.equipmentId')
                // แก้ไข: นำ orderBy ออกจากฟังก์ชัน Join
                ->leftJoinSub($latestStatusId, 'latest_id_table', function ($join) {
                    $join->on('notirepair.NotirepairId', '=', 'latest_id_table.NotirepairId');
                })
                ->leftJoin(
                    DB::raw(env('THIRD_DB_DATABASE') . '.statustracking as latest_status'),
                    function ($join) {
                        $join->on('latest_status.NotirepairId', '=', 'notirepair.NotirepairId')
                            ->on('latest_status.statustrackingId', '=', 'latest_id_table.latest_id');
                    }
                )
                // 2. เรียงลำดับจากวันที่ล่าสุด (DateNotirepair DESC)
                ->orderBy('notirepair.DateNotirepair', 'desc');

            // 3. Search Logic
            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('notirepair.NotirepairId', 'like', "%$searchTerm%")
                        ->orWhere('notirepair.JobId', 'like', "%$searchTerm%")
                        ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%")
                        ->orWhere('notirepair.branchCode', 'like', "%$searchTerm%")
                        ->orWhere('latest_status.status', 'like', "%$searchTerm%");
                });
            }

            $noti = $query->paginate(10)->withQueryString(); // ปรับเป็น 10 รายการต่อหน้าเพื่อให้เห็นงานเยอะขึ้น
            return view('dashborad.notirepairlist', compact('noti'));
        }
    }
    public static function reciveNotirepair($notirepaitid)
    {
        $recivenoti = NotiRepairRepository::getNotirepirById($notirepaitid);

        return view('dashborad.notripair', compact('recivenoti'));
    }
    //เดิม
    // public static function acceptNotisRepair($notirepaitid){
    //     //acceot พอ save ในการกดรับให้ redirect ไป route Route::get('/updatestatus/form/{notirepaitid}'
    //     //,[NotiRepairContoller::class,'showUpdateStatusForm'])->name('noti.show_update_form');
    // $acceptnoti = StatustrackingRepository::acceptNotirepair($notirepaitid);
    // return redirect()->route('noti.show_update_form', ['notirepaitid' => $notirepaitid])
    //         ->with('success', 'รับเรื่องเรียบร้อยแล้ว! เข้าสู่หน้าอัพเดตสถานะ');
    // }

    //front ล่าสุด 13/1/69///
    public function acceptNotisRepair(Request $request, $notirepaitid)
    {

        $noti = NotiRepair::find($notirepaitid);

        if (!$noti) {
            return redirect()->back()->with('error', 'ไม่พบรายการแจ้งซ่อม');
        }
        // $JobId = $noti->JobId ?? $notirepaitid;
        $JobId = $noti->JobId;
        // 1. ตรวจสอบสถานะปัจจุบัน (ป้องกันการรับซ้ำ)
        $currentStatus = DB::connection('third')
            ->table('statustracking')
            ->where('NotirepairId', $notirepaitid)
            ->orderByDesc('statustrackingId')
            ->value('status');

        if ($currentStatus && $currentStatus !== 'ยังไม่ได้รับของ') {
            return redirect()->back()->with('error', 'รายการนี้ถูกรับแล้ว สถานะปัจจุบันคือ: ' . $currentStatus);
        }

        // 2. บันทึกสถานะใหม่ลงในตาราง statustracking
        DB::connection('third')
            ->table('statustracking')
            ->insert([
                'NotirepairId' => $notirepaitid,
                'status' => 'ได้รับของแล้ว',
                'staffcode' => Session::get('staffcode'),
                'staffname' => Session::get('staffname'),
                'statusDate' => Carbon::now(),
                // 'created_at' => Carbon::now(),
                // 'updated_at' => Carbon::now(),
            ]);

        // return redirect()->back()->with('success', 'รายการแจ้งซ่อมรหัส ' . $notirepaitid . ' ได้รับเรื่องเรียบร้อยแล้ว');
        return redirect()->back()->with('success', 'รายการแจ้งซ่อมรหัส ' . $JobId . ' ได้รับเรื่องเรียบร้อยแล้ว');
    }

    // ฟังก์ชันสำหรับหน้าร้านกดรับของคืนจากช่าง
    // public function acceptNotisRepair(Request $request, $notirepaitid)
    // {
    //     $noti = NotiRepair::find($notirepaitid);
    //     if (!$noti) return redirect()->back()->with('error', 'ไม่พบรายการ');

    //     // ตรวจสอบว่าช่างซ่อมเสร็จหรือยังก่อนจะให้กดรับ
    //     $currentStatus = DB::connection('third')
    //         ->table('statustracking')
    //         ->where('NotirepairId', $notirepaitid)
    //         ->orderByDesc('statustrackingId')
    //         ->value('status');

    //     if (!str_contains($currentStatus, 'ซ่อมงานเสร็จแล้ว')) {
    //         return redirect()->back()->with('error', 'ไม่สามารถรับของได้ เนื่องจากช่างยังซ่อมไม่เสร็จ');
    //     }

    //     // บันทึกว่าหน้าร้านรับคืนแล้ว
    //     DB::connection('third')->table('statustracking')->insert([
    //         'NotirepairId' => $notirepaitid,
    //         'status' => 'ได้รับของคืนเรียบร้อย',
    //         'staffcode' => Session::get('staffcode'),
    //         'staffname' => Session::get('staffname'),
    //         'statusDate' => Carbon::now(),
    //     ]);

    //     return redirect()->back()->with('success', 'ยืนยันรับอุปกรณ์คืนรหัส ' . $noti->JobId . ' เรียบร้อย');
    // }

    // // ฟังก์ชันปิดงาน (Closed Job)
    // public function closedJobs(Request $request, $notirepairid)
    // {
    //     $currentStatus = NotirepairRepository::getCurrentStatus($notirepairid);

    //     // ต้องผ่านสถานะ "ได้รับของคืนเรียบร้อย" ก่อนเท่านั้นถึงจะปิดงานได้
    //     if ($currentStatus !== 'ได้รับของคืนเรียบร้อย') {
    //         return redirect()->back()->with('error', 'กรุณากดยืนยันการรับของคืนก่อนปิดงาน');
    //     }

    //     try {
    //         DB::transaction(function () use ($notirepairid) {
    //             NotirepairRepository::closeJobInMainTable($notirepairid);
    //             NotirepairRepository::updateStatusTracking(
    //                 $notirepairid, 
    //                 'ปิดงานเรียบร้อย', 
    //                 Session::get('staffcode'), 
    //                 Session::get('staffname')
    //             );
    //         });
    //         return redirect()->back()->with('success', "ปิดงานเรียบร้อยแล้ว");
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    //     }
    // }

    // public static function closedJobs(Request $request,$notirepairid){
    //     $noti = NotirepairRepository::findById($notirepairid);
    //     if(!$noti){
    //         return redirect()->back()->with('error','ไม่พบรหัสการเเจ้งซ่อม');
    //     }
    //     $currentStatus = NotirepairRepository::getCurrentStatus($notirepairid);
    //     if($currentStatus !== 'ได้รับของเเล้ว'){
    //         return redirect()->back()->with('error', value: 'ไม่สามารถปิดงานได้ (สถานะปัจจุบัน: ' . ($currentStatus ?: 'ยังไม่ได้รับของ') . ')');
    //     }

    //     NotirepairRepository::updateStatus($notirepairid,'ปิดงาน');
    //     return redirect()->back()->with('success', "ปิดงานรหัส $notirepairid เรียบร้อยแล้ว");

    // }
    //old
    //dashborad frontstaff การปิดงานของพนักงาน
    //     public function closedJobs(Request $request, $notirepairid)
    // {
    //     // 1. ค้นหาข้อมูลผ่าน Repo
    //     $noti = NotirepairRepository::findById($notirepairid);
    //     if (!$noti) {
    //         return redirect()->back()->with('error', 'ไม่พบรายการแจ้งซ่อม');
    //     }

    //     // 2. เช็คสถานะปัจจุบัน
    //     $currentStatus = NotirepairRepository::getCurrentStatus($notirepairid);
    //     if ($currentStatus !== 'ได้รับของแล้ว') {
    //         return redirect()->back()->with('error', 'ไม่สามารถปิดงานได้ (ต้องได้รับของก่อน)');
    //     }

    //     try {
    //         DB::transaction(function () use ($notirepairid) {
    //             // ✅ 3.1 อัปเดสตารางหลัก (ฟิลด์ closedJobs, DateCloseJobs)
    //             NotirepairRepository::closeJobInMainTable($notirepairid);

    //             // ✅ 3.2 บันทึกประวัติใน statustracking
    //             NotirepairRepository::updateStatusTracking($notirepairid, 'ปิดงาน');
    //         });

    //         return redirect()->back()->with('success', "ปิดงานรหัส $notirepairid และบันทึกข้อมูลลงฐานข้อมูลเรียบร้อยแล้ว");

    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
    //     }
    // }
    //ล่าสุด 13/1/69
    // public function closedJobs(Request $request, $notirepairid)
    // {
    //     // 1. ค้นหาข้อมูลผ่าน Repo
    //     $noti = NotirepairRepository::findById($notirepairid);
    //     if (!$noti) {
    //         return redirect()->back()->with('error', 'ไม่พบรายการแจ้งซ่อม');
    //     }

    //     // 2. เช็คสถานะปัจจุบัน
    //     $currentStatus = NotirepairRepository::getCurrentStatus($notirepairid);
    //     if ($currentStatus !== 'ได้รับของแล้ว') {
    //         return redirect()->back()->with('error', 'ไม่สามารถปิดงานได้ (ต้องได้รับของก่อน)');
    //     }

    //     // --- เพิ่ม: ดึงข้อมูลพนักงานจาก Session ---
    //     $staffcode = Session::get('staffcode');
    //     $staffname = Session::get('staffname');

    //     try {
    //         DB::transaction(function () use ($notirepairid, $staffcode, $staffname) {
    //             // ✅ 3.1 อัปเดตตารางหลัก (ปิดงานเรียบร้อย)
    //             NotirepairRepository::closeJobInMainTable($notirepairid);

    //             // ✅ 3.2 บันทึกประวัติใน statustracking พร้อมชื่อพนักงาน
    //             // เปลี่ยนจาก 'ปิดงาน' เป็น 'ปิดงานเรียบร้อย' เพื่อให้ตรงกับเงื่อนไขใน Blade
    //             NotirepairRepository::updateStatusTracking($notirepairid, 'ปิดงานเรียบร้อย', $staffcode, $staffname);
    //         });

    //         return redirect()->back()->with('success', "ปิดงานรหัส $notirepairid เรียบร้อยแล้ว");

    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
    //     }
    // }

    //14/1
    // public function closedJobs(Request $request, $notirepairid)
    // {
    //     // 1. ค้นหาข้อมูลผ่าน Repo
    //     $noti = NotirepairRepository::findById($notirepairid);
    //     if (!$noti) {
    //         return redirect()->back()->with('error', 'ไม่พบรายการแจ้งซ่อม');
    //     }

    //     // 2. เช็คสถานะปัจจุบัน (ปรับปรุงเงื่อนไขตรงนี้)
    //     $currentStatus = NotirepairRepository::getCurrentStatus($notirepairid);

    //     // ตรวจสอบว่าสถานะต้องเป็น 'ได้รับของแล้ว' หรือมีคำว่า 'ซ่อมงานเสร็จแล้ว'
    //     // $isValidStatus = ($currentStatus === 'ได้รับของแล้ว' || str_contains($currentStatus, 'ซ่อมงานเสร็จแล้ว'));
    //     $isValidStatus = str_contains($currentStatus, 'ซ่อมงานเสร็จแล้ว');
    //     if (!$isValidStatus) {
    //         // return redirect()->back()->with('error', 'ไม่สามารถปิดงานได้ (สถานะปัจจุบันไม่ถูกต้อง)');
    //         return redirect()->back()->with('error', 'ไม่สามารถรับของคืนได้ เนื่องจากช่างยังซ่อมไม่เสร็จ');
    //     }

    //     // ... ส่วนที่เหลือคงเดิม (DB Transaction) ...
    //     $staffcode = Session::get('staffcode');
    //     $staffname = Session::get('staffname');

    //     try {
    //         DB::transaction(function () use ($notirepairid, $staffcode, $staffname) {
    //             NotirepairRepository::closeJobInMainTable($notirepairid);
    //             NotirepairRepository::updateStatusTracking($notirepairid, 'ปิดงานเรียบร้อย', $staffcode, $staffname);
    //         });

    //         return redirect()->back()->with('success', "ปิดงานรหัส $notirepairid เรียบร้อยแล้ว");
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    //     }
    // }
    //15/1
    // NotiRepairController.php

    public function closedJobs(Request $request, $notirepairid)
    {
        // 1. ค้นหาข้อมูลผ่าน Repo
        $noti = NotirepairRepository::findById($notirepairid);
        if (!$noti) {
            return redirect()->back()->with('error', 'ไม่พบรายการแจ้งซ่อม');
        }

        // 2. เช็คสถานะปัจจุบัน (ปรับปรุงเงื่อนไขตรงนี้)
        $currentStatus = NotirepairRepository::getCurrentStatus($notirepairid);

        // ตรวจสอบว่าสถานะต้องเป็น 'ได้รับของแล้ว' หรือมีคำว่า 'ซ่อมงานเสร็จแล้ว'
        // $isValidStatus = ($currentStatus === 'ได้รับของแล้ว' || str_contains($currentStatus, 'ซ่อมงานเสร็จแล้ว'));
        $isValidStatus = str_contains($currentStatus, 'ซ่อมงานเสร็จแล้ว');
        if (!$isValidStatus) {
            // return redirect()->back()->with('error', 'ไม่สามารถปิดงานได้ (สถานะปัจจุบันไม่ถูกต้อง)');
            return redirect()->back()->with('error', 'ไม่สามารถรับของคืนได้ เนื่องจากช่างยังซ่อมไม่เสร็จ');
        }


        $staffcode = Session::get('staffcode');
        $staffname = Session::get('staffname');

        try {
            DB::transaction(function () use ($notirepairid, $staffcode, $staffname) {
                // 1. อัปเดตตารางหลัก ให้เป็น 'ปิดงานเรียบร้อย' (รูปที่ 10)
                NotirepairRepository::closeJobInMainTable($notirepairid);

                // 2. บันทึกลงตาราง statustracking (ประวัติ)
                // ✅ แนะนำให้ใช้ 'ได้รับของคืนเรียบร้อย' ให้ตรงกับ ENUM ในรูปที่ 9 ครับ
                NotirepairRepository::updateStatusTracking(
                    $notirepairid,
                    'ได้รับของคืนเรียบร้อย',
                    $staffcode,
                    $staffname
                );
            });

            return redirect()->back()->with('success', "ปิดงานรหัส $notirepairid เรียบร้อยแล้ว");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //dashbordAdminช่าง
    public function showUpdateStatusForm($notirepaitid)
    {
        // ดึงข้อมูลการแจ้งซ่อมที่ต้องการอัพเดต
        $updatenoti = StatustrackingRepository::getNotiDetails($notirepaitid);
        if (!$updatenoti) {
            return redirect()->route('noti.list')->with('error', 'ไม่พบรายการแจ้งซ่อม');
        }
        // คืนค่า View dashborad.updatestatus
        return view('dashborad.updatestatus', compact('updatenoti'));
    }
    //dashbordAdminช่าง
    public static function editUpdateNoti($notirepairid)
    {
        $updatenoti = StatustrackingRepository::getNotiDetails($notirepairid);
        return view('dashborad.editnoti', compact('updatenoti'));
    }
    //edit
    // ฟังก์ชันแสดงหน้าแก้ไขข้อมูล
    public function editNoti($notirepaitid)
    {
        $noti = NotiRepair::find($notirepaitid);
        if (!$noti) {
            return redirect()->back()->with('error', 'ไม่พบรายการแจ้งซ่อม');
        }
        return view('dashborad.editnoti', compact('noti')); // สร้างไฟล์ view นี้แยกต่างหาก
    }

    // ฟังก์ชันบันทึกการแก้ไข
    public function updateNotiData(Request $request)
    {
        $noti = NotiRepair::find($request->NotirepairId);
        if ($noti) {
            $noti->equipmentName = $request->equipmentName;
            $noti->DeatailNotirepair = $request->DeatailNotirepair;
            // เพิ่มฟิลด์อื่นๆ ที่ต้องการให้แก้ได้
            $noti->save();

            return redirect()->route('noti.list')->with('success', 'แก้ไขข้อมูลรหัส ' . ($noti->JobId ?? $noti->NotirepairId) . ' สำเร็จ');
        }
        return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการแก้ไข');
    }
    //บันทึกการอัพเดทสถานะ
    public function updateStatus(Request $request)
    {
        $่jobId = $request->JobId;
        $notirepaitid = $request->NotirepairId;
        $statusData = $request->status;
        $statusDate = $request->statusDate;
        // $statusDate = Carbon::parse($request->statusDate)->format('d/m/Y'); //เดิมอันนี้เป็น เดือน/วัน/ปี
        // $statusDate = Carbon::createFromFormat('d/m/Y', $request->statusDate)->format('Y-m-d'); //เเต่ต้องมาพิมวันที่อยู่
        //status เป็นเเค่ชื่อที่ตั้งให้เหมือน name ใน html เเต่ตั้งชื่อให้เหมือน database
        $staffcode = Session::get('staffcode');
        $staffname = Session::get('staffname');
        // เรียกใช้ Repository เพื่ออัพเดตสถานะ
        StatustrackingRepository::updateNotiStatus($notirepaitid, $statusData, $statusDate, $staffcode, $staffname);
        $displayId = $่jobId ?: $notirepaitid;
        // เปลี่ยนเส้นทางกลับไปยังหน้ารายการแจ้งซ่อมพร้อมข้อความสำเร็จ
        return redirect()->route('noti.list')
            // ->with('success', 'อัพเดตสถานะเรียบร้อยแล้ว!');
            // ->with('success','อัพเดตสถานะรหัส'.' '.$displayId.' '.'เรียบร้อยเเล้ว!');
            ->with('success', 'อัพเดตสถานะรหัส' . ' ' . $displayId . ' ' . 'เรียบร้อยเเล้ว!');
    }
    //dashbord frontstore
    public static function getStatusNotreciveItem($notirepairid)
    {
        $noti = StatustrackingRepository::getLatestStatusByNotiRepairId($notirepairid);
        return $noti;
    }
    public static function getItemrRepair($notirepairid)
    {
        $noti = StatustrackingRepository::acceptNotirepair($notirepairid);
        return view('dashborad.storefront', compact('noti'));
    }
    //เดิม
    // public function getNotiForStoreFront(Request $request)
    // {
    //     $role = Session::get('role');
    //     if ($role === 'Frontstaff') {
    //         //เพิ่ม
    //         $searchTerm = $request->input('search');
    //         // Subquery: หา statustrackingId ล่าสุด
    //         $latestStatusId = DB::connection('third')
    //             ->table('statustracking')
    //             ->select('NotirepairId', DB::raw('MAX(statustrackingId) as latest_id'))
    //             ->groupBy('NotirepairId');

    //         $query = NotiRepair::select(
    //             'notirepair.*',
    //             DB::raw("COALESCE(latest_status.status, 'ยังไม่ได้รับของ') as status"),
    //             'latest_status.statusDate as statusDate',
    //             'equipment.equipmentName as equipmentName'
    //         )
    //             ->leftJoin('equipment', 'equipment.equipmentId', '=', 'notirepair.equipmentId')

    //             ->leftJoinSub($latestStatusId, 'latest_id_table', function ($join) {
    //                 $join->on('notirepair.NotirepairId', '=', 'latest_id_table.NotirepairId');
    //             })

    //             // JOIN ข้าม DB ต้องระบุชื่อฐานข้อมูล
    //             ->leftJoin(
    //                 DB::raw(env('THIRD_DB_DATABASE') . '.statustracking as latest_status'),
    //                 function ($join) {
    //                     $join->on('latest_status.NotirepairId', '=', 'notirepair.NotirepairId')
    //                         ->on('latest_status.statustrackingId', '=', 'latest_id_table.latest_id');
    //                 }
    //             )

    //             ->orderBy('notirepair.DateNotirepair', 'desc');

    //         if ($searchTerm) {
    //             $query->where(function ($q) use ($searchTerm) {
    //                 $q->where('notirepair.NotirepairId', 'like', "%$searchTerm%")
    //                     ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%")
    //                     ->orWhere('notirepair.DeatailNotirepair', 'like', "%$searchTerm%")
    //                     ->orWhere(DB::raw("COALESCE(latest_status.status, 'ยังไม่ได้รับของ')"), 'like', "%$searchTerm%");
    //             });
    //         }

    //         $noti = $query->paginate(5)->withQueryString();

    //         return view('dashborad.storefront', compact('noti'));
    //     }
    // }

    public function getNotiForStoreFront(Request $request)
    {
        $role = Session::get('role');
        if ($role === 'Frontstaff') {

            // --- ส่วนที่ 1: ดึงรหัสสาขาและเตรียมการกรอง ---
            $staffcode = Session::get('staffcode');

            if (empty($staffcode)) {
                // ถ้าไม่พบ staffcode ใน Session (เช่น Session หมดอายุ)
                return back()->with('error', 'ไม่พบรหัสพนักงานใน Session กรุณาล็อกอินใหม่');
            }

            try {
                // 1. ดึงรหัสสาขา (Branch Code) จาก PermissionBM Repository (ฐานข้อมูล MMS)
                // เช่น staffcode '0042786' จะได้ branchCode 'FQ01'
                $frontstaffBranchCode = PermissionBMRepository::getBranchCode($staffcode);
            } catch (\Throwable $th) {
                // จัดการกรณีที่อาจจะไม่มีข้อมูลใน permission_bm 
                return back()->with('error', 'ไม่สามารถดึงข้อมูลสาขาจากตาราง PermissionBM ได้');
            }

            if (empty($frontstaffBranchCode)) {
                return back()->with('error', 'ไม่พบข้อมูลสาขาในตาราง permission_bm สำหรับพนักงานคนนี้');
            }

            $searchTerm = $request->input('search');

            // Subquery: หา statustrackingId ล่าสุด
            $latestStatusId = DB::connection('third')
                ->table('statustracking')
                ->select('NotirepairId', DB::raw('MAX(statustrackingId) as latest_id'))
                ->groupBy('NotirepairId');

            $query = NotiRepair::select(
                'notirepair.branch', // ต้อง Select คอลัมน์ branch มาด้วย
                'notirepair.*',
                DB::raw("COALESCE(latest_status.status, 'ยังไม่ได้รับของ') as status"),
                'latest_status.statusDate as statusDate',
                'equipment.equipmentName as equipmentName'
            )
                ->leftJoin('equipment', 'equipment.equipmentId', '=', 'notirepair.equipmentId')

                // 🛑 จุดสำคัญ: กรองเฉพาะงานที่มีรหัสสาขาตรงกับพนักงานที่ล็อกอิน
                ->where('notirepair.branchCode', $frontstaffBranchCode)

                ->leftJoinSub($latestStatusId, 'latest_id_table', function ($join) {
                    $join->on('notirepair.NotirepairId', '=', 'latest_id_table.NotirepairId');
                })

                // JOIN ข้าม DB ต้องระบุชื่อฐานข้อมูล
                ->leftJoin(
                    DB::raw(env('THIRD_DB_DATABASE') . '.statustracking as latest_status'),
                    function ($join) {
                        $join->on('latest_status.NotirepairId', '=', 'notirepair.NotirepairId')
                            ->on('latest_status.statustrackingId', '=', 'latest_id_table.latest_id');
                    }
                )

                ->orderBy('notirepair.DateNotirepair', 'desc');

            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('notirepair.NotirepairId', 'like', "%$searchTerm%")
                        ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%")
                        ->orWhere('notirepair.DeatailNotirepair', 'like', "%$searchTerm%")
                        ->orWhere(DB::raw("COALESCE(latest_status.status, 'ยังไม่ได้รับของ')"), 'like', "%$searchTerm%");
                });
            }

            $noti = $query->paginate(5)->withQueryString();
            $branchNames = \App\Models\Mastbranchinfo::all()
            ->mapWithKeys(function ($item) {
                return [trim($item->MBranchInfo_Code) => trim($item->Location)];
            })->toArray();
            return view('dashborad.storefront', compact('noti','branchNames'));
        }
    }
    public function receiveBack($NotirepairId)
    {
        try {
            // บันทึกสถานะใหม่ลงในตาราง statustracking (DB ตัวที่สาม)
            DB::connection('third')->table('statustracking')->insert([
                'NotirepairId' => $NotirepairId,
                'status' => 'ปิดงาน (พนักงานรับของคืนแล้ว)',
                'statusDate' => now(),
                'staffcode' => Session::get('staffcode'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', 'ยืนยันการรับอุปกรณ์คืนเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
    public static function checkall()
    {
        $check = StatustrackingRepository::getAllStatustracking();
        return $check;
    }
    //dashbordofficer
    public static function showState() {}
    //dashbord store 
    public static function NotiRepairHistory()
    {
        // $notirepairList = NotirepairRepository::HistoryNotirepair();
        // $notirepairList = NotirepairRepository::getAllNotirepairByadmin();
        $notirepairList = NotirepairRepository::HistoryNotirepair();
        // $equipmentList = EquipmentRepository::getallEquipment(); //เอาไอดีคนที่กดรับกดปิดงานฝั่งหน้าร้าน
        // dd($notirepairList);
        return view('dashborad.historynoti', compact('notirepairList'));
    }
    //dashbord AdminIt
    public static function ShowallNotirepair()
    {
        $notirepairList = NotirepairRepository::getAllNotiRepairWithDetails();
        return view('dashborad.adminall', compact('notirepairList'));
    }
    public static function getCountNotirepair()
    {
        $countList = NotirepairRepository::CountNotirepair();
        $countComplete = StatustrackingRepository::CountCompleteStatus();
        $countPending = StatustrackingRepository::CountPendingStatus();
        $countItem = StatustrackingRepository::CountItemComplte();
        // dd($countList);
        return view('dashborad.dashbord', compact('countList', 'countComplete', 'countPending', 'countItem'));
    }
    public static function getCountComplteStatus()
    {
        $countComplete = StatustrackingRepository::CountCompleteStatus();
        return view('dashborad.dashbord', compact('countComplete'));
    }


    // public static function getClosedJobs(){
    //     $closedJob = StatustrackingRepository::closeedJobStatus();
    //     return view('',compact('closedJob'));
    // }
    // }
    // เพิ่มใน NotirepairRepository.php

    // public static function getTrackingListForAdmin($searchTerm = null, $perPage = 15)
    // {
    //     // 1. ดึง ID ล่าสุดของสถานะจากตาราง statustracking (DB ที่สาม)
    //     $latestStatusId = DB::connection('third')
    //         ->table('statustracking')
    //         ->select('NotirepairId', DB::raw('MAX(statustrackingId) as latest_id'))
    //         ->groupBy('NotirepairId');

    //     // 2. Query หลัก
    //     $query = Notirepair::select(
    //             'notirepair.*',
    //             'equipment.equipmentName',
    //             DB::raw("COALESCE(latest_status.status, 'ยังไม่ได้รับของ') as current_status"),
    //             'latest_status.statusDate as last_status_date'
    //         )
    //         ->leftJoin('equipment', 'notirepair.equipmentId', '=', 'equipment.equipmentId')
    //         // Join เพื่อเอา ID ล่าสุด
    //         ->leftJoinSub($latestStatusId, 'latest_id_table', function ($join) {
    //             $join->on('notirepair.NotirepairId', '=', 'latest_id_table.NotirepairId');
    //         })
    //         // Join เพื่อเอาชื่อสถานะจริงจาก DB ที่สาม
    //         ->leftJoin(
    //             DB::raw(env('THIRD_DB_DATABASE') . '.statustracking as latest_status'),
    //             function ($join) {
    //                 $join->on('latest_status.NotirepairId', '=', 'notirepair.NotirepairId')
    //                      ->on('latest_status.statustrackingId', '=', 'latest_id_table.latest_id');
    //             }
    //         );

    //     // 3. ระบบค้นหา (ถ้ามี)
    //     if ($searchTerm) {
    //         $query->where(function ($q) use ($searchTerm) {
    //             $q->where('notirepair.NotirepairId', 'like', "%$searchTerm%")
    //               ->orWhere('notirepair.branchCode', 'like', "%$searchTerm%")
    //               ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%");
    //         });
    //     }

    //     return $query->orderBy('notirepair.DateNotirepair', 'desc')
    //                  ->paginate($perPage)
    //                  ->withQueryString();
    // }
    // เพิ่มใน NotiRepairController.php

    // public function officerTracking(Request $request)
    // {
    //     // รับค่าค้นหาจากหน้าเว็บ
    //     $search = $request->input('search');
    //     $status = $request->input('status');

    //     $jobs = NotirepairRepository::getTrackingListForAdmin($search);

    //     $totalCount = Notirepair::count();
    //     $pendingCount = DB::connection('third')->table('statustracking')->where('status','ได้รับของเเล้ว')->count();
    //     return view('dashborad.office', compact('jobs','pendingCount','totalCount'));
    // }

    // NotirepairController.php
    // NotirepairController.php
    public function officerTracking(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $jobs = NotirepairRepository::getTrackingListForAdmin($search, $status);

        // ยอดรวมทั้งหมด
        // $totalCount = Notirepair::count();

        // // งานที่รอดำเนินการ (เช็คจากฟิลด์ closedJobs)
        // $pendingCount = Notirepair::where('closedJobs', '=', 'ยังไม่ปิดงาน')->count();
        $totalCount = DB::connection('third')->table('notirepair')->count();
        // $totalCount = $jobs->total();
        // แก้ไข: ระบุเงื่อนไขรอดำเนินการให้ตรงกับฟิลด์ closedJobs
        $pendingCount = DB::connection('third')->table('notirepair')
            ->where('closedJobs', '=', 'ยังไม่ปิดงาน')
            ->count();

        return view('dashborad.office', compact('jobs', 'totalCount', 'pendingCount'));
    }
    // public function officerTracking(Request $request)
    // {
    //     $search = $request->input('search');
    //     $status = $request->input('status');

    //     $query = DB::table('notirepair') 
    //         ->select('NotirepairId', 'branchCode', 'equipmentName', 'current_status', 'last_status_date', 'DateCloseJobs');

    //     // ระบบค้นหา
    //     if ($search) {
    //         $query->where(function($q) use ($search) {
    //             $q->where('NotirepairId', 'LIKE', "%{$search}%")
    //               ->orWhere('branchCode', 'LIKE', "%{$search}%")
    //               ->orWhere('equipmentName', 'LIKE', "%{$search}%");
    //         });
    //     }

    //     // กรองตามสถานะ (ถ้ามีการเลือก)
    //     if ($status) {
    //         $query->where('current_status', $status);
    //     }

    //     $jobs = $query->orderBy('last_status_date', 'desc')->paginate(10);

    //     return view('officer_tracking', compact('jobs'));
    // }

}
