<?php
declare(strict_types=1);

/*
 * Editorial content: expert answers placed on existing pages ("insights") and guide articles (/guides).
 * Content is written once as blocks and rendered to HTML, Markdown (for AI assistants) and JSON-LD.
 *
 * Block types:
 *   ['p', text]  ['h2', text, id]  ['h3', text]  ['ul', [items]]  ['ol', [items]]
 *   ['verdict', title, text]                     — a highlighted definitive answer
 *   ['proscons', [name => ['pros' => [...], 'cons' => [...]], ...]]
 *   ['table', [head cells], [[row cells], ...]]
 *   ['note', text]                               — a small caveat line
 * Inline text supports **bold** and [label](/path or https://url).
 */

function md_inline(string $t): string
{
    $h = e($t);
    $h = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $h);
    return preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', function ($m) {
        $href = html_entity_decode($m[2], ENT_QUOTES);
        $ext = preg_match('#^https?://#', $href) === 1;
        return '<a href="' . e($href) . '"' . ($ext ? ' rel="noopener" target="_blank"' : '') . '>' . $m[1] . '</a>';
    }, $h);
}

/** Plain text (for JSON-LD answers and meta descriptions). */
function md_plain(string $t): string
{
    return preg_replace(['/\*\*(.+?)\*\*/', '/\[([^\]]+)\]\([^)]+\)/'], ['$1', '$1'], $t);
}

/** Markdown (absolute links) for .md pages and llms-full.txt. */
function md_text(string $t): string
{
    return preg_replace_callback('/\[([^\]]+)\]\((\/[^)\s]*)\)/', fn($m) => '[' . $m[1] . '](' . url($m[2]) . ')', $t);
}

function blocks_html(array $blocks): string
{
    $o = '';
    foreach ($blocks as $b) {
        switch ($b[0]) {
            case 'p': $o .= '<p>' . md_inline($b[1]) . '</p>'; break;
            case 'h2': $o .= '<h2' . (isset($b[2]) ? ' id="' . e($b[2]) . '"' : '') . '>' . e($b[1]) . '</h2>'; break;
            case 'h3': $o .= '<h3>' . e($b[1]) . '</h3>'; break;
            case 'ul': case 'ol':
                $o .= "<{$b[0]}>" . implode('', array_map(fn($i) => '<li>' . md_inline($i) . '</li>', $b[1])) . "</{$b[0]}>"; break;
            case 'verdict':
                $o .= '<div class="verdict"><span class="label">' . icon('check', 14) . e($b[1]) . '</span><p>' . md_inline($b[2]) . '</p></div>'; break;
            case 'note': $o .= '<p class="small muted">' . md_inline($b[1]) . '</p>'; break;
            case 'proscons':
                $o .= '<div class="pc-compare">';
                foreach ($b[1] as $name => $pc) {
                    $o .= '<div class="pc-col"><h4>' . e($name) . '</h4><div class="pc">'
                        . '<div><h5>' . icon('thumb-up', 14) . 'Pros</h5><ul>' . implode('', array_map(fn($i) => '<li>' . md_inline($i) . '</li>', $pc['pros'])) . '</ul></div>'
                        . '<div class="cons"><h5>' . icon('thumb-down', 14) . 'Cons</h5><ul>' . implode('', array_map(fn($i) => '<li>' . md_inline($i) . '</li>', $pc['cons'])) . '</ul></div>'
                        . '</div></div>';
                }
                $o .= '</div>'; break;
            case 'table':
                $wide = count($b[1]) >= 4;
                $o .= ($wide ? '<p class="swipe-hint small muted">Swipe sideways to see every column.</p>' : '') . '<div class="table-scroll"><table class="data' . ($wide ? ' wide' : '') . '"><thead><tr>' . implode('', array_map(fn($c) => '<th scope="col">' . md_inline($c) . '</th>', $b[1])) . '</tr></thead><tbody>';
                foreach ($b[2] as $row) {
                    $o .= '<tr>';
                    foreach ($row as $i => $c) $o .= $i === 0 ? '<th scope="row">' . md_inline($c) . '</th>' : '<td>' . md_inline($c) . '</td>';
                    $o .= '</tr>';
                }
                $o .= '</tbody></table></div>'; break;
        }
    }
    return $o;
}

function blocks_md(array $blocks, string $h2 = '##'): string
{
    $h3 = $h2 . '#';
    $o = [];
    foreach ($blocks as $b) {
        switch ($b[0]) {
            case 'p': $o[] = md_text($b[1]); break;
            case 'h2': $o[] = "$h2 {$b[1]}"; break;
            case 'h3': $o[] = "$h3 {$b[1]}"; break;
            case 'ul': $o[] = implode("\n", array_map(fn($i) => '- ' . md_text($i), $b[1])); break;
            case 'ol': $o[] = implode("\n", array_map(fn($k, $i) => ($k + 1) . '. ' . md_text($i), array_keys($b[1]), $b[1])); break;
            case 'verdict': $o[] = '**' . $b[1] . ':** ' . md_text($b[2]); break;
            case 'note': $o[] = '_' . md_text($b[1]) . '_'; break;
            case 'proscons':
                foreach ($b[1] as $name => $pc) {
                    $o[] = "**$name — pros**\n" . implode("\n", array_map(fn($i) => '- ' . md_text($i), $pc['pros']));
                    $o[] = "**$name — cons**\n" . implode("\n", array_map(fn($i) => '- ' . md_text($i), $pc['cons']));
                }
                break;
            case 'table':
                $row = fn($cells) => '| ' . implode(' | ', array_map(fn($c) => str_replace('|', '\|', md_text($c)), $cells)) . ' |';
                $o[] = implode("\n", array_merge([$row($b[1]), '|' . str_repeat(' --- |', count($b[1]))], array_map($row, $b[2])));
                break;
        }
    }
    return implode("\n\n", $o);
}

/* ---------- Expert answers on existing pages ---------- */

function insights(): array
{
    static $all = null;
    return $all ??= require APP . '/content/insights.php';
}

function insight(string $key): ?array
{
    return insights()[$key] ?? null;
}

/** Renders an insight: question-led sections with a verdict, then related guides and sources. */
function insight_html(?array $in): string
{
    if (!$in) return '';
    $o = '<section class="section insight" aria-labelledby="ins-' . e($in['id']) . '">'
        . '<div class="insight-head"><span class="eyebrow">' . icon('sparkles', 14) . 'Expert analysis</span>'
        . '<h2 id="ins-' . e($in['id']) . '">' . e($in['title']) . '</h2>'
        . (!empty($in['intro']) ? '<p class="lede">' . md_inline($in['intro']) . '</p>' : '')
        . '<p class="research-line">' . icon('clock', 15) . 'Researched <time datetime="' . e($in['updated']) . '">' . e(format_date($in['updated'])) . '</time> from vendor documentation and independent review sites · Not sponsored</p></div>';
    if (count($in['sections']) > 1) {
        $o .= '<nav class="insight-toc" aria-label="In this analysis"><ul>';
        foreach ($in['sections'] as $s) $o .= '<li><a href="#' . e($s['id']) . '">' . e($s['nav'] ?? $s['heading']) . '</a></li>';
        $o .= '</ul></nav>';
    }
    foreach ($in['sections'] as $s) {
        $o .= '<article class="insight-section prose" id="' . e($s['id']) . '"><h3 class="q">' . e($s['heading']) . '</h3>' . blocks_html($s['blocks']) . '</article>';
    }
    $o .= related_articles_html($in['related'] ?? []);
    $o .= sources_html($in['sources'] ?? []);
    return $o . '</section>';
}

/** Question/answer pairs from an insight, for the page's FAQ list and FAQPage schema. */
function insight_faqs(?array $in): array
{
    return $in['faqs'] ?? [];
}

function insight_md(?array $in): string
{
    if (!$in) return '';
    $o = ["## {$in['title']}", '', '_Researched ' . format_date($in['updated']) . '._', ''];
    if (!empty($in['intro'])) array_push($o, md_text($in['intro']), '');
    foreach ($in['sections'] as $s) array_push($o, "### {$s['heading']}", '', blocks_md($s['blocks'], '####'), '');
    return implode("\n", $o);
}

function sources_html(array $sources): string
{
    if (!$sources) return '';
    $o = '<details class="sources"><summary>Sources (' . count($sources) . ')</summary><ol>';
    foreach ($sources as [$label, $href]) $o .= '<li><a href="' . e($href) . '" rel="noopener nofollow" target="_blank">' . e($label) . '</a></li>';
    return $o . '</ol></details>';
}

/* ---------- Blog articles (/blog) ---------- */

function articles(): array
{
    static $all = null;
    if ($all !== null) return $all;
    $all = [];
    foreach (glob(APP . '/content/blog/*.php') ?: [] as $f) {
        $g = require $f;
        $g['slug'] = basename($f, '.php');
        $all[$g['slug']] = $g;
    }
    uasort($all, fn($a, $b) => [$b['updated'], $a['title']] <=> [$a['updated'], $b['title']]);
    return $all;
}

function article(string $slug): ?array
{
    return articles()[$slug] ?? null;
}

function article_url(string $slug): string
{
    return "/blog/$slug";
}

function related_articles_html(array $slugs, string $heading = 'Related articles'): string
{
    $gs = array_values(array_filter(array_map('article', $slugs)));
    if (!$gs) return '';
    $o = '<div class="related-guides"><h3>' . e($heading) . '</h3><ul>';
    foreach ($gs as $g) $o .= '<li><a href="' . e(article_url($g['slug'])) . '">' . icon('file', 18) . '<span><strong>' . e($g['title']) . '</strong><small>' . e($g['dek']) . '</small></span></a></li>';
    return $o . '</ul></div>';
}

/** Full guide as Markdown (for /guides/{slug}.md and llms-full.txt). */
function article_md(array $g, bool $full = true): string
{
    $h1 = $full ? '#' : '##';
    $o = ["$h1 {$g['title']}", '', '> ' . md_text($g['answer']), '', '- URL: ' . url(article_url($g['slug'])), '- Published: ' . $g['published'], '- Updated: ' . $g['updated'], ''];
    $o[] = blocks_md($g['blocks'], $full ? '##' : '###');
    $o[] = '';
    if (!empty($g['faqs'])) {
        $o[] = ($full ? '##' : '###') . ' FAQ';
        $o[] = '';
        foreach ($g['faqs'] as [$q, $a]) array_push($o, "**$q**", '', md_text($a), '');
    }
    if (!empty($g['sources'])) {
        $o[] = ($full ? '##' : '###') . ' Sources';
        $o[] = '';
        foreach ($g['sources'] as [$label, $href]) $o[] = "- [$label]($href)";
        $o[] = '';
    }
    return implode("\n", $o);
}

/** Articles placed on a page (by page key, e.g. "software:docketwise", "category:corporate-immigration"). */
function articles_for(string $key): array
{
    return array_keys(array_filter(articles(), fn($a) => in_array($key, $a['placements'] ?? [], true)));
}

/** Expert comparisons (insight pages) that involve a product, as [label, path]. */
function insight_links_for(string $slug): array
{
    $out = [];
    foreach (insights() as $key => $in) {
        if (!str_starts_with($key, 'compare:')) continue;
        $slugs = explode('-vs-', substr($key, 8));
        if (!in_array($slug, $slugs, true)) continue;
        $out[] = [$in['title'], '/compare/' . substr($key, 8)];
    }
    if (isset(insights()["alternatives:$slug"])) $out[] = [insights()["alternatives:$slug"]['title'], "/software/$slug/alternatives"];
    return $out;
}

/** Merges FAQ lists without repeating a question. */
function merge_faqs(array ...$lists): array
{
    $seen = [];
    $out = [];
    foreach ($lists as $l) foreach ($l as $f) {
        $k = strtolower(trim($f[0]));
        if (isset($seen[$k])) continue;
        $seen[$k] = true;
        $out[] = $f;
    }
    return $out;
}
