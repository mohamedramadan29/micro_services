<?php
namespace App\Http\Traits;
trait Slug_Trait
{
    function CustomeSlug($text)
    {
        // Replace non-word characters with a hyphen
        $text = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s]/u', '-', $text);

        // Replace multiple consecutive hyphens with a single hyphen
        $text = preg_replace('/-+/', '-', $text);

        // Trim leading and trailing hyphens
        $text = trim($text, '-');

        // Convert spaces to hyphens
        $text = str_replace(' ', '-', $text);

        // Convert to lowercase
        $text = mb_strtolower($text, 'UTF-8');

        return $text;
    }

}
