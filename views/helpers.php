<?php

function e($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function chars($text)
{
    $out = '';
    $index = 0;
    $parts = explode(' ', $text);

    foreach ($parts as $i => $word) {
        $out .= '<span class="hero__word" aria-hidden="true">';

        foreach (preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) as $char) {
            $out .= '<span class="hero__char" style="--i: ' . $index . '">' . e($char) . '</span>';
            $index++;
        }

        if ($i < count($parts) - 1) {
            $out .= '&nbsp;';
        }

        $out .= '</span>';
    }

    return $out;
}
