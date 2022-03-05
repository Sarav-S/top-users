<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveStaleRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Removes the stale records from cache which are
     * expired.
     *
     * @return void
     */
    public function handle()
    {
        $timestamp = now()->endOfDay()->timestamp;

        $records = cache()->get('top_users_data_redis', []);
        $records = collect($records)->filter(function ($record) use ($timestamp) {
            return $record['expiration'] > $timestamp;
        });
        cache()->put('top_users_data_redis', $records);
    }
}
