<?php

namespace App\Support\Rss;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class RssRepository
{

    public function fetch(string $url): Collection
    {
        $rssString = Http::get($url)->body();

        $atomXml = new SimpleXMLElement($rssString);

        return collect(Feed::fromAtom($atomXml)->toArray()['entry'] ?? [])
            ->map(fn (array $data) => RssEntry::fromArray($data));
    }
}
