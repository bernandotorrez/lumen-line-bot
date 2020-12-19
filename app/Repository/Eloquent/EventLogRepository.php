<?php 

namespace App\Repository\Eloquent;

use App\Models\EventLog;

class EventLogRepository
{
    public function saveLog(string $signature, string $body)
    {
        return EventLog::create([
            'signature' => $signature,
            'event' => $body
        ]);
    }

}