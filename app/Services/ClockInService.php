<?php

namespace App\Services;

use App\Exceptions\BadRequestException;
use App\Models\ClockIn;
use App\Services\Helpers\UserIp;
use Carbon\Carbon;

class ClockInService
{
    private $userIp;

    public function __construct(UserIp $userIp)
    {
        $this->userIp = $userIp;
    }

    public function index()
    {
        return ClockIn::join('users', 'users.id', '=', 'clock_ins.user_id')
            ->select('users.fullName', 'users.id as user_id', 'clock_ins.*')
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function clockIn($data)
    {
        date_default_timezone_set('Africa/Lagos');

        $user_id = $data['id'];

        $clockedIn = $this->check_clockin($user_id);

        if ($clockedIn) {
            throw new BadRequestException('You have already clocked in');
        }

        ClockIn::create([
            'user_id' => $user_id,
            'clockin_time' => date('H:i:s', time()),
            'user_ip_address' => $this->userIp->getIp(),
            'status' => 'clockedin'
        ]);
    }

    public function clockout($data)
    {
        date_default_timezone_set('Africa/Lagos');

        $user_id = $data['id'];

        $clockedIn = $this->check_clockin($user_id);
        if (!$clockedIn) {
            throw new BadRequestException('You have not clocked in');
        }

        $clockin = ClockIn::where('user_id', $user_id)
            ->whereDate('created_at', Carbon::today())
            ->latest('clock_ins.created_at')
            ->first();

        $clockin->update([
            'clockout_time' => date('H:i:s', time()),
            'status' => 'clockedout'
        ]);
    }

    public function clockin_history($uu_id)
    {
        return ClockIn::join('users', 'users.id', '=', 'clock_ins.user_id')
            ->select('users.fullName', 'users.id as user_id', 'clock_ins.*')
            ->where('users.uuid', $uu_id)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function verify_clockin($uuid)
    {
        $clockin = ClockIn::join('users', 'users.id', '=', 'clock_ins.user_id')
            ->where('users.uuid', $uuid)
            ->whereDate('clock_ins.created_at', Carbon::today())
            ->latest('clock_ins.created_at')
            ->first();

        if (!$clockin) {
            return false;
        }

        if ($clockin->status == 'clockedin') {
            return true;
        } elseif ($clockin->status == 'clockedout') {
            return false;
        }
    }

    private function check_clockin($user_id)
    {
        $clockin = ClockIn::where('user_id', $user_id)
            ->whereDate('created_at', Carbon::today())
            ->latest('clock_ins.created_at')
            ->first();

        if ($clockin != null) {
            return true;
        }

        return false;
    }
}
