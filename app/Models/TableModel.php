<?php
namespace App\Models;
class TableModel extends BaseModel
{
    protected $table      = 'tables';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'branch_id','area_id','table_number','qr_code','capacity','shape','status','sort_order','is_active'
    ];
    public function getTablesByArea($branchId)
    {
        $areas = $this->db->table('table_areas')
            ->where('branch_id', $branchId)->where('is_active',1)
            ->orderBy('sort_order','ASC')->get()->getResultArray();

        foreach ($areas as &$area) {
            $area['tables'] = $this->db->table('tables')
                ->where('branch_id', $branchId)->where('area_id', $area['id'])->where('is_active',1)
                ->orderBy('sort_order','ASC')->get()->getResultArray();
        }

        // Tables without area
        $noArea = $this->db->table('tables')
            ->where('branch_id', $branchId)->where('is_active',1)
            ->where('area_id IS NULL','',false)
            ->orderBy('sort_order','ASC')->get()->getResultArray();

        if ($noArea) {
            $areas[] = ['id'=>0,'name'=>'General','tables'=>$noArea];
        }
        return $areas;
    }
}
