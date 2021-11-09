<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Service
{
    /**
     * Get now dateTime or date.
     *
     * @param  string $type "dateTime" or "date"
     * @return string
     */
    public static function getNow(string $type = 'dateTime')
    {
        if ($type === 'dateTime') {
            return DB::select(DB::raw("SELECT NOW() date_time"))[0]->date_time;
        } elseif ($type === 'date') {
            return DB::select(DB::raw("SELECT CURDATE() date"))[0]->date;
        }

        return null;
    }

    /**
     * Consulting an external authenticate service.
     *
     * @return object
     */
    public static function consultExternalAuth()
    {
        try {
            $response = Http::acceptJson()->get('https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6');
            if ($response->json()) return $response->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return null;
    }
}
