<?php

namespace Bmatovu\Ussd\Support;

class Dom
{
    public static function render(\DOMNode $node): string
    {
        $attrs = '';

        foreach ($node->attributes as $attr) {
            $attrs .= " {$attr->name}=\"{$attr->value}\"";
        }

        return "<{$node->tagName}{$attrs}/>";
    }

    public static function getElements(\DOMNodeList $nodeList, ?string $nodeName): array
    {
        $els = array_filter(iterator_to_array($nodeList), static function ($node) use ($nodeName) {
            if (! $node instanceof \DOMElement) {
                return false;
            }

            if (! $nodeName) {
                return true;
            }

            return $node->nodeName === $nodeName;
        });

        return array_values($els);
    }
}
