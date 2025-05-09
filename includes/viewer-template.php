<?php

function renderPage(): void
{
    $paged    = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $searchString   = isset($_GET['search_string']) ? strtolower(sanitize_text_field($_GET['search_string'])) : '';
    $per_page = isset($_GET['per_page']) ? max(10, intval($_GET['per_page'])) : 100;

    $result = drv_get_data($searchString, $paged, $per_page);

    if(isset($result['error'])){
        echo '<div class="notice notice-error"><p>'.esc_html($result['error']).'</p></div>';

        return;
    }

    $paged_data = $result['data'];
    $total      = $result['total'];
    $offset     = ($paged - 1) * $per_page;

    echo '<div class="wrap"><h1>Domain Rank Viewer</h1>';
    echo '<form method="get"><input type="hidden" name="page" value="domain-rank-viewer" />';
    echo '<input type="text" name="search_string" placeholder="Search domain..." value="'.esc_attr(
        $searchString
      ).'" />';

    echo '<input type="submit" value="Search" class="button" />';
    echo '</form>';

    $total_pages = ceil($total / $per_page);
    if($total_pages > 1){
        renderPagination($total_pages, $paged, $searchString, $per_page);
    }

    echo '<table class="widefat"><thead><tr><th>#</th><th>Domain</th><th>Domain Authority</th><th>OpenPageRank</th></tr></thead><tbody>';

    $domains   = array_map(fn($item) => $item['rootDomain'], $paged_data);
    $pageranks = getPageRanks($domains);

    foreach($paged_data as $index => $item){
        $domain    = esc_html($item['rootDomain']);
        $authority = esc_html($item['domainAuthority']);
        $rank      = esc_html($pageranks[$item['rootDomain']] ?? 'N/A');
        $num       = $offset + $index + 1;

        echo "<tr><td>$num</td><td>$domain</td><td>$authority</td><td>$rank</td></tr>";
    }

    echo '</tbody></table>';

    if($total_pages > 1){
        renderPagination($total_pages, $paged, $searchString, $per_page);
    }

    echo '</div>';
}

function renderPagination($total_pages, $paged, $search, $per_page): void
{
    echo '<div style="text-align: center; margin: 20px 0;">';
    for($i = 1; $i <= $total_pages; $i++){
        $active = ($i == $paged) ? ' style="background-color: #0073aa; color: #fff; border-color: #006799;"' : '';
        $link   = add_query_arg([
          'paged'    => $i,
          's'        => $search,
          'per_page' => $per_page,
        ]);
        echo "<a href='$link' class='button'$active>$i</a> ";
    }
    echo '<form method="get" style="display: inline-block; margin-left: 20px;">';
    echo '<input type="hidden" name="page" value="domain-rank-viewer" />';
    echo '<input type="hidden" name="s" value="'.esc_attr($search).'" />';
    echo '<input type="hidden" name="paged" value="'.esc_attr($paged).'" />';
    echo '<label for="per_page">Per Page: </label>';
    echo '<select name="per_page" onchange="this.form.submit()">';
    foreach([50, 100, 200] as $option){
        $selected = $per_page == $option ? 'selected' : '';
        echo "<option value='$option' $selected>$option</option>";
    }
    echo '</select>';
    echo '</form>';

    echo '</div>';
}

function showMissingApiKeyNotice(): void
{
    echo '<div class="notice notice-error"><p>';
    echo '⚠️ OpenPageRank API key is missing. Please define it in your <code>wp-config.php</code>:<br><br>';
    echo '<code>define("OPEN_PAGE_RANK_API_KEY", "your-api-key-here");</code>';
    echo '</p></div>';
}

