<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlanService
{
    public $plan;

    public function __construct(int $planId)
    {
        $this->plan = Plan::lockForUpdate()->find($planId);
    }

    public function haveCapacity(): bool
    {
        if ($this->plan->capacity_limit === NULL) return true;
        $count = self::countActiveUsers();
        $count = $count[$this->plan->id]['count'] ?? 0;
        return ($this->plan->capacity_limit - $count) > 0;
    }

    public static function countActiveUsers()
    {
        return User::select(
            DB::raw("plan_id"),
            DB::raw("count(*) as count")
        )
            ->where('plan_id', '!=', NULL)
            ->where(function ($query) {
                $query->where('expired_at', '>=', time())
                    ->orWhere('expired_at', NULL);
            })
            ->groupBy("plan_id")
            ->get()
            ->keyBy('plan_id');
    }

    public static function countUsers($group_id) {
        $plans = Plan::where('group_id', $group_id)->get();
        $counts = 0;
        for ($i = 0; $i < count($plans); $i++) {
            $users = User::where('plan_id', $plans[$i]['id'])->count();
            
            $counts += $users;
        }

        return $counts;
    }

    public static function countUserFromPlanId($plan_id) {
        return User::where('plan_id', $plan_id)->get()->count();

    }

    public static function getTransferEnable($plan_id) {

        return Plan::where('id', $plan_id)->get()->first()->transfer_enable ?? 0;
    }

    public static function getGroupId($plan_id) {
        return Plan::where('id', $plan_id)->get()->first()->group_id ??0;
    }

    public static function getUsersANode($group_id, $parantNodeID) {
        $servers = new ServerService();
        $plans = Plan::all();
        $users = new Collection();
        foreach ($plans as $plan) {
            $user = User::where('plan_id', $plan->id)->get();
            foreach ($user as $userx) {
                $serversx = $servers->getAvailableServers($userx);
                foreach ($serversx as $server) {
                    if ((string)$server["parent_id"] == (string)$parantNodeID) {
                        $users->push($userx);
                    }
                }
            }
        }
        return $users;
    }

    public static function getUsers($group_id) {
        $plans = Plan::where('group_id', $group_id)->get();
        $users = new Collection();
        foreach ($plans as $plan) {
            $user = User::where('plan_id', $plan->id)->get();
            foreach ($user as $userx) {

                $users->push($userx);
            }
        }
        return $users;
    }
}
