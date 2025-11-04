<?php

namespace Backstage;

use Backstage\Fields\Models\Field;
use Backstage\Fields\Plugins\JumpAnchorRichContentPlugin;
use Backstage\Models\Block;
use Illuminate\Support\Str;

class Backstage
{
    private static array $components = [];

    private static array $cachedBlocks = [
        'default' => '\Backstage\View\Components\DefaultBlock',
    ];

    public static function registerComponent(string $name, ?string $component = null): void
    {
        if (empty($component)) {
            $component = $name;
            $name = Str::snake(Str::replaceLast('Component', '', class_basename($component)), '-');
        }

        static::$components[$name] = $component;
    }

    public static function getComponents(): array
    {
        return static::$components;
    }

    public static function getComponentOptions()
    {
        return collect(static::$components)
            ->mapWithKeys(fn ($component, $name) => [$name => Str::headline(last(explode('\\', $component)))])
            ->sort();
    }

    public static function resolveComponent($slug)
    {
        if (self::$cachedBlocks[$slug] ?? false) {
            return self::$cachedBlocks[$slug];
        }

        $block = Block::select('component')->where('slug', $slug)->first();

        if (! $block) {
            return self::$cachedBlocks[$slug] = static::$cachedBlocks['default'];
        }
        $className = Str::studly($slug);
        if (file_exists(app_path("View/Components/{$className}.php"))) {
            return self::$cachedBlocks[$slug] = '\App\View\Components\\' . $className;
        }
        if (class_exists("\\Backstage\\View\\Components\\$className")) {
            return self::$cachedBlocks[$slug] = '\\Backstage\\View\\Components\\' . $className;
        }

        return self::$cachedBlocks[$slug] = static::$components[$block->component] ?? static::$cachedBlocks['default'];
    }

    /**
     * Convert
     * [
     *      "type" => "text"
     *      "data" => [
     *          "01jkgc3d2ms3749x8swc3pvg2p" => "<p>testaaaa</p>"
     *      ]
     *  ]
     * to
     * [
     *    '_type' => 'text',
     *    'body' => '<p>testaaaa</p>'
     * ]
     */
    public static function mapParams($block)
    {
        if (! $block['type'] || ! $block['data']) {
            return [];
        }

        $values = collect($block['data']);

        $fields = Field::select('ulid', 'slug', 'field_type')
            ->whereIn('ulid', $values->keys())
            ->get()
            ->keyBy('ulid');

        $params = [
            '_type' => $block['type'],
        ];

        foreach ($values as $key => $value) {
            $field = $fields[$key] ?? null;
            $fieldKey = Str::camel($field->slug ?? $key);

            // Process rich editor content
            if ($field && $field->field_type === 'rich-editor' && is_array($value)) {
                $params[$fieldKey] = self::processRichEditorContent($value);
            } else {
                $params[$fieldKey] = $value;
            }
        }

        return $params;
    }

    /**
     * Process rich editor content to HTML
     */
    private static function processRichEditorContent($content): string
    {
        if (! is_array($content) || ! isset($content['type']) || $content['type'] !== 'doc' || ! isset($content['content'])) {
            return '';
        }

        try {
            return \Filament\Forms\Components\RichEditor\RichContentRenderer::make($content)
                ->plugins([JumpAnchorRichContentPlugin::get()])
                ->toHtml();
        } catch (\Exception $e) {
            // Fallback: extract plain text
            return self::extractTextFromRichEditor($content);
        }
    }

    /**
     * Extract plain text from rich editor content as fallback.
     * This method might be removed in the future, when all content is stored as JSON.
     */
    private static function extractTextFromRichEditor(array $content): string
    {
        if (! isset($content['content']) || ! is_array($content['content'])) {
            return '';
        }

        $textParts = [];

        foreach ($content['content'] as $item) {
            if (! isset($item['type']) || ! isset($item['content']) || ! is_array($item['content'])) {
                continue;
            }

            $itemText = self::extractTextFromNodes($item['content']);
            if (! empty($itemText)) {
                $textParts[] = $itemText;
            }
        }

        return implode("\n", $textParts);
    }

    /**
     * Extract text from an array of content nodes.
     * This method might be removed in the future, when all content is stored as JSON.
     */
    private static function extractTextFromNodes(array $nodes): string
    {
        $textParts = [];

        foreach ($nodes as $node) {
            if (isset($node['type']) && $node['type'] === 'text' && isset($node['text'])) {
                $textParts[] = $node['text'];
            }
        }

        return implode(' ', $textParts);
    }
}
