<?php 

namespace App\Repository\Eloquent;

use App\Models\EventLog;

class EventLogRepository
{
    public function saveLog(string $signature, string $body)
    {
        EventLog::create([
            'signature' => $signature,
            'events' => $body
        ]);
    }

}