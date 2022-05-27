<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\OptionsTag;
use Bmatovu\Ussd\Tests\TestCase;

class OptionsTagTest extends TestCase
{
    public function testHandleOptions()
    {
        $this->cache->put('prefix_exp', '/*[1]');

        $xml = <<<XML
<options header="Choose Gender: ">
    <option text="Male"/>
    <option text="Female"/>
</options>
XML;

        $node = $this->getNodeByTagName($xml, 'options');

        $tag = new OptionsTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertSame("Choose Gender: \n1) Male\n2) Female\n0) Back", $output);
        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[2]', $this->cache->get('prefix_exp'));
    }

    public function testHandleOptionsNoBack()
    {
        $xml = <<<XML
<options header="Choose Gender: " noback="no">
    <option text="Male"/>
    <option text="Female"/>
</options>
XML;

        $node = $this->getNodeByTagName($xml, 'options');

        $tag = new OptionsTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertSame("Choose Gender: \n1) Male\n2) Female", $output);
    }

    public function testProccessOptions()
    {
        $this->cache->put('prefix_pre', '/*[1]');

        $xml = <<<XML
<options header="Choose Gender: " noback="no">
    <option text="Male"/>
    <option text="Female"/>
</options>
XML;

        $node = $this->getNodeByTagName($xml, 'options');

        $tag = new OptionsTag($node, $this->cache, 'prefix', 30);

        $tag->process('2');

        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[1]/*[2]', $this->cache->get('prefix_exp'));
    }

    public function testProccessOptionsValidationNoAnswer()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Make a choice.');

        $node = $this->getNodeByTagName('<options header="Gender"><option text="Male"/></options>', 'options');

        $tag = new OptionsTag($node, $this->cache, 'prefix', 30);

        $tag->process('');
    }

    public function testProccessOptionsValidationWrongChoice()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Invalid choice.');

        $node = $this->getNodeByTagName('<options header="Gender"><option text="Male"/></options>', 'options');

        $tag = new OptionsTag($node, $this->cache, 'prefix', 30);

        $tag->process('2');
    }
}
