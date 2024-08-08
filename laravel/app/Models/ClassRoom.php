<?php

namespace App\Models;

use App\Services\ClassRoleService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ClassRoom extends Model
{
    public $timestamps = true;
    protected $fillable = [
        'name',
        'owner_id'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function students()
    {
        $roleStudent = ClassRoleService::get('student');

        return DB::table('class_member_roles as cmr')
            ->join('class_members as cm', function ($join) {
                $join->on('cm.class_room_id', '=', 'cmr.class_room_id')
                    ->where(function ($query) {
                        $query->on('cm.user_id', '=', 'cmr.user_id')
                            ->orWhere(function ($query) {
                                $query->whereNull('cm.user_id')
                                    ->whereNull('cmr.user_id');
                            });
                    });
            })
            ->where('cmr.class_room_id', $this->id)
            ->where('cmr.class_role_id', $roleStudent->id)
            ->groupBy('cm.id')
            ->select('cm.*')
            ->get();
    }
}
