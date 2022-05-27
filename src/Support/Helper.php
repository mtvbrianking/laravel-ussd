<?php

namespace Bmatovu\Ussd\Support;

class Helper
{
    public static function getDomElements(\DOMNodeList $nodeList, ?string $nodeName): array
    {
        return array_filter(iterator_to_array($nodeList), function ($node) use ($nodeName) {
            // return $node instanceof \DOMElement && ($nodeName && $node->nodeName === $nodeName);

            if(! $node instanceof \DOMElement) {
                return false;
            }

            if(! $nodeName) {
                return true;
            }

            return $node->nodeName === $nodeName;
        });
    }
}
