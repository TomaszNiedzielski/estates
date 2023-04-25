<?php

namespace App\Jobs;

use App\Models\Estate;
use App\Models\UsersShift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChangeSupervisorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get new shifts
        $newShifts = UsersShift::where([
            ['date_from', '>=', DB::raw('CURDATE()')],
            ['temp_changes', '=', null]
        ])
        ->select('id', 'user_id', 'substitude_user_id')
        ->get();

        foreach ($newShifts as $newShift) {
            // Get id's of updating estates
            $estateToUpdateIds = Estate::where('supervisor_user_id', $newShift->user_id)
                ->pluck('id');

            // Change the supervisor in estates table to substitu
            Estate::where('supervisor_user_id', $newShift->user_id)
                ->update([
                    'supervisor_user_id' => $newShift->substitude_user_id
                ]);

            // Save ids of updated estates to temp_changes column
            UsersShift::where('id', $newShift->id)
                ->update([
                    'temp_changes' => $estateToUpdateIds
                ]);
        }


        // Get expired shifts
        $expiredShiftModels = UsersShift::where([
            ['date_to', '<', DB::raw('CURDATE()')],
            ['temp_changes', '!=', null]
        ]);

        $expiredShifts = $expiredShiftModels
            ->select('user_id', 'temp_changes')
            ->get();

        // Restore supervisors to their pre-change state
        foreach ($expiredShifts as $expiredShift) {
            Estate::whereIn('id', $expiredShift->temp_changes)
                ->update([
                    'supervisor_user_id' => $expiredShift->user_id
                ]);
        }

        // Delete expired shifts
        $expiredShiftModels->delete();
    }
}
