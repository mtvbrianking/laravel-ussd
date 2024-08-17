<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\OptionsTag;
use Bmatovu\Ussd\Tests\TestCase;

class OptionsTagTest extends TestCase
{
    public function testHandleOptions()
    {
        $this->store->put('_exp', '/*[1]');

        $xml = <<<'XML'
<options header="Choose Gender: ">
    <option text="Male"/>
    <option text="Female"/>
</options>
XML;

        $node = $this->getNodeByTagName($xml, 'options');

        $tag = new OptionsTag($node, $this->store);

        $output = $tag->handle();

        self::assertSame("Choose Gender: \n1) Male\n2) Female\n0) Back", $output);
        self::assertSame('/*[1]', $this->store->get('_pre'));
        self::assertSame('/*[2]', $this->store->get('_exp'));
    }

    public function testHandleOptionsNoBack()
    {
        $xml = <<<'XML'
<options header="Choose Gender: " noback="no">
    <option text="Male"/>
    <option text="Female"/>
</options>
XML;

        $node = $this->getNodeByTagName($xml, 'options');

        $tag = new OptionsTag($node, $this->store);

        $output = $tag->handle();

        self::assertSame("Choose Gender: \n1) Male\n2) Female", $output);
    }

    public function testProccessOptions()
    {
        $this->store->put('_pre', '/*[1]');

        $xml = <<<'XML'
<options header="Choose Gender: " noback="no">
    <option text="Male"/>
    <option text="Female"/>
</options>
XML;

        $node = $this->getNodeByTagName($xml, 'options');

        $tag = new OptionsTag($node, $this->store);

        $tag->process('2');

        self::assertSame('/*[1]', $this->store->get('_pre'));
        self::assertSame('/*[1]/*[2]', $this->store->get('_exp'));
    }

    public function testProcessOptionsBack()
    {
        $this->store->put('_pre', '/*[1]/*[2]/*[1]');
        // $this->store->put('_exp', '');

        $xml = <<<'XML'
<options header="Send Money" noback="no">
    <option text="Americas">
        <response text="ComingSoon"/>
    </option>
    <option text="Europe">
        <options header="Europe">
            <option text="Turkey">
                <response text="ComingSoon"/>
            </option>
        </options>
    </option>
</options>
XML;

        $node = $this->getNodeByPathExp($xml, '/*[1]/*[2]/*[1]');

        $tag = new OptionsTag($node, $this->store);

        $tag->process(0);

        self::assertSame('/*[1]/*[2]/*[1]', $this->store->get('_pre'));
        self::assertSame('/*[1]', $this->store->get('_exp'));
    }

    public function testProccessOptionsValidationNoAnswer()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Make a choice.');

        $node = $this->getNodeByTagName('<options header="Gender"><option text="Male"/></options>', 'options');

        $tag = new OptionsTag($node, $this->store);

        $tag->process('');
    }

    public function testProccessOptionsValidationWrongChoice()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('InvalidChoice');

        $node = $this->getNodeByTagName('<options header="Gender" retries="0"><option text="Male"/></options>', 'options');

        $tag = new OptionsTag($node, $this->store);

        $tag->process('2');
    }

    public function testProccessOptionsValidationNoBack()
    {
        self::markTestSkipped('Change in impl');

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('InvalidChoice');

        $node = $this->getNodeByTagName('<options header="Gender" noback="no"><option text="Male"/></options>', 'options');

        $tag = new OptionsTag($node, $this->store);

        $tag->process('0');
    }
}
