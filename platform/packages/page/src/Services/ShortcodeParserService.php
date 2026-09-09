<?php

namespace Botble\Page\Services;

class ShortcodeParserService
{
    protected const SHORTCODE_NAME_PATTERN = '/^[a-zA-Z0-9_-]+$/';

    protected array $shortcodes = [];

    protected int $idCounter = 0;

    public function parse(string $content): array
    {
        $this->shortcodes = [];
        $this->idCounter = 0;

        $pattern = $this->getShortcodeRegex();

        if (empty($content)) {
            return [];
        }

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        foreach ($matches as $match) {
            $shortcode = $this->parseShortcode($match);
            if ($shortcode) {
                $this->shortcodes[] = $shortcode;
            }
        }

        return $this->shortcodes;
    }

    protected function parseShortcode(array $match): ?array
    {
        $fullMatch = $match[0][0] ?? '';
        $position = $match[0][1] ?? 0;
        $name = $match[2][0] ?? '';
        $attributesString = $match[3][0] ?? '';
        $content = $match[5][0] ?? '';

        if (empty($name)) {
            return null;
        }

        $attributes = $this->parseAttributes($attributesString);

        $id = $attributes['data-vb-id'] ?? null;
        if (empty($id)) {
            $id = 'shortcode_' . time() . '_' . $this->idCounter++;
        }

        unset($attributes['data-vb-id']);

        return [
            'id' => $id,
            'name' => $name,
            'attributes' => $attributes,
            'content' => $content,
            'position' => $position,
            'raw' => $fullMatch,
            'isSelfClosing' => empty($match[5][0] ?? ''),
        ];
    }

    protected function parseAttributes(string $attributesString): array
    {
        $attributes = [];

        if (empty($attributesString)) {
            return $attributes;
        }

        $attributesString = html_entity_decode($attributesString, ENT_NOQUOTES, 'UTF-8');

        $pattern = '/(\w+)\s*=\s*(?:"((?:[^\\\\"]|\\\\.)*)"|\'((?:[^\\\\\']|\\\\.)*)\'|([^\s]+))/';

        preg_match_all($pattern, $attributesString, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $key = $match[1];
            $value = $match[2] ?? $match[3] ?? $match[4] ?? '';
            $attributes[$key] = $this->unescapeAttribute($value);
        }

        return $attributes;
    }

    protected function getShortcodeRegex(): string
    {
        return '/\[(\[?)([\w-]+)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*(?:\[(?!\/\2\])[^\[]*)*)(\[\/\2\]))?)(\]?)/';
    }

    public function serialize(array $shortcodes): string
    {
        $content = '';

        foreach ($shortcodes as $shortcode) {
            $name = $shortcode['name'] ?? '';
            $attributes = $shortcode['attributes'] ?? [];
            $innerContent = $shortcode['content'] ?? '';
            $id = $shortcode['id'] ?? '';

            if (empty($name) || ! $this->isValidShortcodeName($name)) {
                continue;
            }

            $shortcodeString = '[' . $name;

            foreach ($attributes as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $escapedValue = $this->escapeAttribute($value);
                $shortcodeString .= ' ' . $key . '="' . $escapedValue . '"';
            }

            $shortcodeString .= ']';
            if (! empty($innerContent)) {
                $shortcodeString .= $innerContent;
            }
            $shortcodeString .= '[/' . $name . ']';

            $content .= $shortcodeString . "\n\n";
        }

        return trim($content);
    }

    protected function escapeAttribute(mixed $value): string
    {
        if (! is_string($value)) {
            $value = (string) $value;
        }

        $value = preg_replace('/<br[^>]*\/?>/i', '{{BR}}', $value);
        $value = str_replace('&nbsp;', '{{NBSP}}', $value);

        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }

    protected function unescapeAttribute(string $value): string
    {
        return str_replace(['\\"', '\\\\'], ['"', '\\'], $value);
    }

    public function getShortcodes(): array
    {
        return $this->shortcodes;
    }

    protected function isValidShortcodeName(string $name): bool
    {
        return (bool) preg_match(self::SHORTCODE_NAME_PATTERN, $name);
    }

    public function decodeForVisualBuilder(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    }

    public static function removeVisualBuilderIds(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        return preg_replace('/\s*data-vb-id="[^"]*"/', '', $content);
    }
}
