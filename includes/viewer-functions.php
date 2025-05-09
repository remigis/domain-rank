<?php

function drv_get_data($search = '', $paged = 1, $per_page = 100): array
{
    $json_path = plugin_dir_path(__DIR__).'top-sites.json';

    if(!file_exists($json_path)){
        return ['error' => 'top-sites.json file not found'];
    }

    $json_data = json_decode(file_get_contents($json_path), true);
    if(!$json_data){
        return ['error' => 'Invalid JSON structure'];
    }

    $filtered = array_filter($json_data, function($item) use ($search){
        return !$search || stripos($item['rootDomain'], $search) !== false;
    });

    $offset     = ($paged - 1) * $per_page;
    $paged_data = array_slice($filtered, $offset, $per_page);

    return [
      'data'  => $paged_data,
      'total' => count($filtered),
    ];
}

function getPageRanks(array $domains): array
{
    $results = array_fill_keys($domains, 'N/A');

    if(empty($domains)){
        return $results;
    }

    $api_key = defined('OPEN_PAGE_RANK_API_KEY') ? OPEN_PAGE_RANK_API_KEY : '';
    $url     = 'https://openpagerank.com/api/v1.0/getPageRank?'.http_build_query([
        'domains' => $domains,
      ]);

    $response = wp_remote_get($url, [
      'headers' => ['API-OPR' => $api_key],
      'timeout' => 15,
    ]);

    if(is_wp_error($response)){
        return array_fill_keys($domains, 'API error');
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if(!empty($body['response'])){
        foreach($body['response'] as $entry){
            $domain = $entry['domain'] ?? '';
            $rank   = $entry['page_rank_decimal'] ?? 'N/A';
            if($domain){
                $results[$domain] = $rank;
            }
        }
    }

    return $results;
}
