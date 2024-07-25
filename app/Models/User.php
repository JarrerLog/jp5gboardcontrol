<?php

namespace App\Models;

use App\Services\PlanService;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'v2_user';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];

    public function getTransferEnable() {
        return PlanService::getTransferEnable($this->plan_id);
    }

    public function getGroupId() {
        return PlanService::getGroupId($this->plan_id);

    }
}
