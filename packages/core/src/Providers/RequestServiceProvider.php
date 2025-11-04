<?php

namespace Backstage\Providers;

use Backstage\Models\Content;
use Backstage\Models\Domain;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Request::macro('content', function () {
            return once(function () {
                $path = $this->path() == '/' ? '' : $this->path();
                $host = str_replace('www.', '', $this->getHost());

                $content = Content::select('content.*')
                    ->public()
                    ->join('sites', 'sites.ulid', 'content.site_ulid')
                    ->join('domains', 'domains.site_ulid', 'sites.ulid')
                    ->join('domain_language', function (JoinClause $join) {
                        $join->on('domain_language.domain_ulid', '=', 'domains.ulid')
                            ->on('domain_language.language_code', '=', 'content.language_code');
                    })
                    ->whereRaw('REGEXP_REPLACE(
                        REGEXP_REPLACE(
                            CONCAT(
                                IFNULL(sites.path, ""), "/",
                                IFNULL(domain_language.path, ""), "/",
                                IFNULL(content.path, ""), "/"
                            ),
                            "\/+", "/"
                        ),
                        "^/|/$", ""
                    ) = ?', [$path])
                    ->whereRaw("REPLACE(domains.name, 'www.', '') = ?", [$host]);

                return $content->first();
            });
        });

        Request::macro('domain', function () {
            return once(fn () => Domain::whereRaw("REPLACE('www.', '', name)", str_replace('www.', '', $this->getHost()))->first());
        });

        Request::macro('language', function () {
            return once(fn () => $this->content()->language); // @phpstan-ignore-line
        });

        Request::macro('site', function () {
            return once(fn () => $this->domain()->site); // @phpstan-ignore-line
        });
    }
}
