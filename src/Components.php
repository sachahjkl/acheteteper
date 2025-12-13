<?php

use Acheteteper\Utils\ViewUtils as v;

class Components
{
    private static function buttonBaseClasses(string $color): string
    {
        $colorStyles = [
            'blue' => 'bg-blue-600 shadow-[0_-1px_0_1px_#1e3a8a_inset,0_0_0_1px_#1d4ed8_inset,0_0.5px_0_1.5px_#60a5fa_inset] hover:bg-blue-700 active:bg-blue-800',
            'green' => 'bg-green-600 shadow-[0_-1px_0_1px_#166534_inset,0_0_0_1px_#15803d_inset,0_0.5px_0_1.5px_#4ade80_inset] hover:bg-green-700 active:bg-green-800',
            'red' => 'bg-red-600 shadow-[0_-1px_0_1px_#991b1b_inset,0_0_0_1px_#dc2626_inset,0_0.5px_0_1.5px_#f87171_inset] hover:bg-red-700 active:bg-red-800',
            'gray' => 'bg-zinc-600 shadow-[0_-1px_0_1px_#3f3f46_inset,0_0_0_1px_#52525b_inset,0_0.5px_0_1.5px_#a1a1aa_inset] hover:bg-zinc-700 active:bg-zinc-800',
        ];
        $base = 'inline-flex items-center justify-center select-none px-3 text-sm font-semibold leading-9 text-zinc-50 rounded-sm transition-colors duration-150 cursor-pointer';
        $style = $colorStyles[$color] ?? $colorStyles['blue'];
        return $base . ' ' . $style;
    }

    public static function LinkButton(string $label, string $url, $color = 'blue', array $attributes = []): string
    {
        $btnClasses = self::buttonBaseClasses($color);
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return '<a class="' . $btnClasses . '" href="' . v::escape($url) . '"' . $attrs . '>' . v::escape($label) . '</a>';
    }

    public static function Button(string $label, string $color = 'blue', array $attributes = []): string
    {
        $btnClasses = self::buttonBaseClasses($color);
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return '<button class="' . $btnClasses . '"' . $attrs . '>' . v::escape($label) . '</button>';
    }


    public static function DemoHeadingItem(string $label, string $url, string $description): string
    {
        return '<li class="my-2 p-3 bg-zinc-50 border-l-4 border-blue-500 shadow-sm">'
            . '<a href="' . v::url($url) . '" class="text-blue-700 font-semibold hover:underline">' . v::escape($label) . '</a>'
            . ' <span class="text-zinc-700">- ' . v::escape($description) . '</span>'
            . '</li>';
    }

    /** 
     * Create a text form input field ()
     */
    public static function FormInput(string $name, string $label, string $value = '', array $attributes = []): string
    {
        $type = $attributes['type'] ?? 'text';
        $attrs = '';
        foreach ($attributes as $key => $val) {
            if ($key === 'type') {
                continue;
            }
            $attrs .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8') . '"';
        }

        $inputClasses = 'w-full h-10 px-3 text-sm text-zinc-900 bg-white border border-zinc-300 rounded-sm shadow-[inset_0_1px_0_0_rgba(255,255,255,0.6),0_1px_2px_rgba(0,0,0,0.06)] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition';

        return '<label class="block space-y-1 text-sm font-medium text-zinc-800" for="' . v::escape($name) . '">'
            . '<span class="block ms-1 mb-1">' . v::escape($label) . '</span>'
            . '<input class="' . $inputClasses . '" type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '" name="' . v::escape($name) . '" id="' . v::escape($name) . '" value="' . v::escape($value) . '"' . $attrs . '>'
            . '</label>';
    }

    public static function Select(string $name, string $label, array $options, ?string $selected = null, array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8') . '"';
        }
        $selectClasses = 'w-full h-10 rounded-sm border border-zinc-300 bg-white px-3 text-sm text-zinc-900 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.6),0_1px_2px_rgba(0,0,0,0.06)] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition';

        $html = '<label class="block space-y-1 text-sm font-medium text-zinc-800" for="' . v::escape($name) . '">';
        $html .= '<span class="block ms-1 mb-1">' . v::escape($label) . '</span>';
        $html .= '<select class="' . $selectClasses . '" name="' . v::escape($name) . '" id="' . v::escape($name) . '"' . $attrs . '>';
        foreach ($options as $value => $text) {
            $isSelected = ($selected !== null && (string)$selected === (string)$value) ? ' selected' : '';
            $html .= '<option value="' . v::escape($value) . '"' . $isSelected . '>' . v::escape($text) . '</option>';
        }
        $html .= '</select>';
        $html .= '</label>';
        return $html;
    }
}
