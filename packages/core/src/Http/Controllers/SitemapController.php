<?php

namespace Backstage\Http\Controllers;

use Backstage\Models\Content;
use Illuminate\Http\Request;
use SimpleXMLElement;

class SitemapController
{
    public function __invoke(Request $request)
    {
        $xml = cache()
            ->rememberForever('sitemap-xml', function () {
                $doc = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

                Content::with('site')
                    ->whereNotNull('path')
                    ->where(function ($query) {
                        $query->where(function ($query) {
                            $query->whereNotNull('meta_tags')
                                ->whereRaw('NOT JSON_CONTAINS(JSON_EXTRACT(meta_tags, "$.robots"), ?)', ['"noindex"']);
                        });
                    })
                    ->public()
                    ->orderBy('published_at', 'desc')
                    ->each(function ($content) use ($doc) {
                        $url = $doc->addChild('url');
                        $url->addChild('loc', $content->url);
                        $url->addChild('priority', '1.0');
                        $url->addChild('changefreq', 'weekly');
                        $url->addChild('lastmod', $content->updated_at->toIso8601String());
                    });

                return $doc->asXML();
            });

        return response($xml, 200, [
            'Content-Type' => 'text/xml',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
