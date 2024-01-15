<?php

namespace Aifrus\Fc2s;

class FetchFAA
{
    const HOME_PAGE = 'https://www.faa.gov/air_traffic/flight_info/aeronav/aero_data/NASR_Subscription/';
    const DATA_FILE = 'https://nfdc.faa.gov/webContent/28DaySub/extra/d_M_Y_CSV.zip';
    const HEADERS = [
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
        "Accept-Encoding: gzip, deflate, br",
        "Accept-Language: en-US,en;q=0.9",
        "Cache-Control: max-age=0",
        "Dnt: 1",
        "Referer: https://www.faa.gov/air_traffic/flight_info/aeronav/aero_data/NASR_Subscription/",
        "Sec-Ch-Ua: \"Not_A Brand\";v=\"8\", \"Chromium\";v=\"120\", \"Google Chrome\";v=\"120\"",
        "Sec-Ch-Ua-Mobile: ?0",
        "Sec-Ch-Ua-Platform: \"Windows\"",
        "Sec-Fetch-Dest: document",
        "Sec-Fetch-Mode: navigate",
        "Sec-Fetch-Site: same-origin",
        "Sec-Fetch-User: ?1",
        "Upgrade-Insecure-Requests: 1",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    ];

    public static function get_home_page_html()
    {
        return HTTPS::get(self::HOME_PAGE, self::HEADERS);
    }

    public static function get_current_date()
    {
        $dates = self::get_available_dates();
        return strtotime($dates[0]) > time() ? $dates[1] : $dates[0];
    }

    public static function get_data_file_url(string $date)
    {
        return str_replace('d_M_Y', date('d_M_Y', strtotime($date)), self::DATA_FILE);
    }

    public static function get_available_dates()
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML(self::get_home_page_html());
        $xpath = new \DOMXPath($dom);
        $dates = [];
        foreach (['Preview', 'Current', 'Archives'] as $section) {
            $nodes = $xpath->query("//h2[text()='$section']/following-sibling::ul[1]/li");
            foreach ($nodes as $node) if (preg_match('/\b(\w+ \d{1,2}, \d{4})\b/', $node->textContent, $matches)) $dates[] = date('Y-m-d', strtotime($matches[0]));
        }
        return $dates;
    }
}
