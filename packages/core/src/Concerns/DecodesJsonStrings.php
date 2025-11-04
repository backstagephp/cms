<?php

namespace Backstage\Concerns;

use Backstage\Models\ContentFieldValue;
use Illuminate\Support\HtmlString;

trait DecodesJsonStrings
{
    /**
     * Recursively decode all JSON strings in an array or value
     */
    protected function decodeAllJsonStrings($data, $path = ''): array | HtmlString | null
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $currentPath = $path === '' ? $key : $path . '.' . $key;
                if (is_string($value)) {
                    $decoded = $value;
                    $decodeCount = 0;
                    while (is_string($decoded)) {
                        $json = json_decode($decoded, true);
                        if ($json !== null && (is_array($json) || is_object($json))) {
                            $decoded = $json;
                            $decodeCount++;
                        } else {
                            break;
                        }
                    }
                    if ($decodeCount > 0) {
                        $data[$key] = $this->decodeAllJsonStrings($decoded, $currentPath);
                    }
                } elseif (is_array($value)) {
                    if (isset($value['type']) && $value['type'] === 'doc') {
                        $value = ContentFieldValue::getRichEditorHtml($value);
                    } else {
                        $value = $this->decodeAllJsonStrings($value, $currentPath);
                    }
                    $data[$key] = $value;
                }
            }
        }

        return $data;
    }
}
