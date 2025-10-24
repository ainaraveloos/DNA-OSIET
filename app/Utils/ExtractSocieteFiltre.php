<?php

namespace App\Utils;

use Illuminate\Http\Request;

class ExtractSocieteFiltre extends ExtractFiltre
{
    public static function extractFilter(Request $request)
    {


        $baseFilters = parent::extractFilter($request);

        $vehiculeFilters = [
            'status' => $request->status
        ];
        return array_merge($baseFilters, $vehiculeFilters);
    }
}
