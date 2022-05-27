<?php

namespace Bmatovu\Ussd\Support;

class Helper
{
    public static function getDomElements(\DOMNodeList $nodeList, ?string $nodeName): array
    {
        $els = array_filter(iterator_to_array($nodeList), function ($node) use ($nodeName) {
            // return $node instanceof \DOMElement && ($nodeName && $node->nodeName === $nodeName);

            if(! $node instanceof \DOMElement) {
                return false;
            }

            if(! $nodeName) {
                return true;
            }

            return $node->nodeName === $nodeName;
        });

        return array_values($els);
    }
}
