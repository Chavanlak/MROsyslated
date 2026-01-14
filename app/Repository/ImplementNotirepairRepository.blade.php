// ใน NotirepairRepository.php
public static function getRepairListByFilter($params = [])
{
    $searchTerm = $params['search'] ?? null;
    $branchCode = $params['branchCode'] ?? null;
    $excludeStatus = $params['excludeStatus'] ?? null;

    // 1. Subquery สำหรับสถานะล่าสุด (ย้ายจาก Controller มาไว้ที่นี่)
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
        ->leftJoinSub($latestStatusId, 'latest_id_table', function ($join) {
            $join->on('notirepair.NotirepairId', '=', 'latest_id_table.NotirepairId');
        })
        ->leftJoin(
            DB::raw(env('THIRD_DB_DATABASE') . '.statustracking as latest_status'),
            function ($join) {
                $join->on('latest_status.NotirepairId', '=', 'notirepair.NotirepairId')
                     ->on('latest_status.statustrackingId', '=', 'latest_id_table.latest_id');
            }
        );

    // 2. กรองตามรหัสสาขา (ถ้ามี - สำหรับพนักงานสาขา)
    if ($branchCode) {
        $query->where('notirepair.branchCode', $branchCode);
    }

    // 3. กรองสถานะที่ไม่ต้องการ (ถ้ามี - สำหรับช่างที่ไม่อยากเห็นของที่ยังไม่ส่งมา)
    if ($excludeStatus) {
        $query->where('latest_status.status', '!=', $excludeStatus);
    }

    // 4. ค้นหา Keyword
    if ($searchTerm) {
        $query->where(function ($q) use ($searchTerm) {
            $q->where('notirepair.JobId', 'like', "%$searchTerm%")
              ->orWhere('equipment.equipmentName', 'like', "%$searchTerm%")
              ->orWhere('notirepair.branchCode', 'like', "%$searchTerm%");
        });
    }

    return $query->orderBy('notirepair.DateNotirepair', 'desc')->paginate(5)->withQueryString();
}