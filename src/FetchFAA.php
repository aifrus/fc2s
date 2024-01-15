<?php

namespace Aifrus\Fc2s;

class FetchFAA
{
    const HOME_PAGE = 'https://www.faa.gov/air_traffic/flight_info/aeronav/aero_data/NASR_Subscription/';
    const DATA_FILE = 'https://nfdc.faa.gov/webContent/28DaySub/extra/DD_Mon_YYYY_CSV.zip';
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

    public static function get_available_dates()
    {
        $html = self::get_home_page_html();

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $dates = [];

        // Get dates from the "Preview", "Current", and "Archives" sections
        foreach (['Preview', 'Current', 'Archives'] as $section) {
            $nodes = $xpath->query("//h2[text()='$section']/following-sibling::ul[1]/li");
            foreach ($nodes as $node) {
                if (preg_match('/\b(\w+ \d{1,2}, \d{4})\b/', $node->textContent, $matches)) {
                    // Convert the date to the format YYYY-MM-DD
                    $date = date('Y-m-d', strtotime($matches[0]));
                    $dates[] = $date;
                }
            }
        }

        return $dates;
    }
}
