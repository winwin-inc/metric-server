<?php

declare(strict_types=1);

namespace winwin\metric\application;

use kuiper\helper\Arrays;

class TagSerializer
{
    public function serialize(?array $tags): string
    {
        if (empty($tags)) {
            return '';
        }
        $tags = Arrays::filter($tags);
        if (empty($tags)) {
            return '';
        }
        ksort($tags);

        return http_build_query($tags);
    }

    public function deserialize(string $tagsStr): array
    {
        if (empty($tagsStr)) {
            return [];
        }
        parse_str($tagsStr, $tags);

        return $tags;
    }
}
