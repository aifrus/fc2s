<?php

namespace Aifrus\Fc2s;

class FetchFAA
{
    const HOME_PAGE = 'https://www.faa.gov/air_traffic/flight_info/aeronav/aero_data/NASR_Subscription/';
    const DATA_FILE = 'https://nfdc.faa.gov/webContent/28DaySub/extra/DD_Mon_YYYY_CSV.zip';
    const HEADERS = [
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    ];

    public static function get_home_page_html()
    {
        return HTTPS::get(self::HOME_PAGE, self::HEADERS);
    }
}
