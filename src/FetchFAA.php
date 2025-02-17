<?php

namespace Aifrus\Fc2s;

/**
 * Class FetchFAA
 * Handles fetching and processing of FAA aeronautical data.
 */
class FetchFAA
{
    /**
     * URL of the FAA home page for aeronautical data.
     */
    const HOME_PAGE = 'https://www.faa.gov/air_traffic/flight_info/aeronav/aero_data/NASR_Subscription/';

    /**
     * Template URL for the FAA data file.
     */
    const DATA_FILE = 'https://nfdc.faa.gov/webContent/28DaySub/extra/d_M_Y_CSV.zip';

    /**
     * HTTP headers for requests.
     */
    const HEADERS = [
        "Accept: text/html,application/xhtml+xml,application/xml;q=.9,image/avif,image/webp,image/apng,*/*;q=.8,application/signed-exchange;v=b3;q=.7",
        "Accept-Encoding: gzip, deflate, br",
        "Accept-Language: en-US,en;q=.9",
        "Cache-Control: max-age=",
        "Dnt: 1",
        "Referer: https://www.faa.gov/air_traffic/flight_info/aeronav/aero_data/NASR_Subscription/",
        "Sec-Ch-Ua: \"Not_A Brand\";v=\"8\", \"Chromium\";v=\"120\", \"Google Chrome\";v=\"120\"",
        "Sec-Ch-Ua-Mobile: ?",
        "Sec-Ch-Ua-Platform: \"Windows\"",
        "Sec-Fetch-Dest: document",
        "Sec-Fetch-Mode: navigate",
        "Sec-Fetch-Site: same-origin",
        "Sec-Fetch-User: ?1",
        "Upgrade-Insecure-Requests: 1",
        "User-Agent: Mozilla/5. (Windows NT 10.; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120... Safari/537.36"
    ];

    /**
     * Fetches the HTML content of the FAA home page.
     *
     * @return string The HTML content of the home page.
     */
    public static function getHomePageHtml(): string
    {
        $html = HTTPS::get(self::HOME_PAGE, self::HEADERS);
        if (empty(trim($html))) {
            throw new \RuntimeException("Empty HTML content received from FAA home page.");
        }
        return $html;
    }

    /**
     * Determines the current date for the data file.
     *
     * @return string The current date in 'Y-m-d' format.
     */
    public static function getCurrentDate(): string
    {
        $dates = self::getAvailableDates();
        return strtotime($dates[0]) > time() ? $dates[1] : $dates[0];
    }

    /**
     * Constructs the URL for the data file based on the given date.
     *
     * @param string $date The date for which to construct the URL.
     * @return string The constructed data file URL.
     */
    public static function getDataFileUrl(string $date): string
    {
        return str_replace('d_M_Y', date('d_M_Y', strtotime($date)), self::DATA_FILE);
    }

    /**
     * Retrieves available dates from the FAA home page.
     *
     * @return array An array of available dates in 'Y-m-d' format.
     */
    public static function getAvailableDates(): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML(self::getHomePageHtml());
        $xpath = new \DOMXPath($dom);
        $dates = [];
        foreach (['Preview', 'Current', 'Archives'] as $section) {
            $nodes = $xpath->query("//h2[text()='$section']/following-sibling::ul[1]/li");
            foreach ($nodes as $node) {
                if (preg_match('/\b(\w+ \d{1,2}, \d{4})\b/', $node->textContent, $matches)) {
                    $dates[] = date('Y-m-d', strtotime($matches[0]));
                }
            }
        }
        return $dates;
    }
}
