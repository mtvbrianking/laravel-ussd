<?php

namespace Bmatovu\Ussd\Tests;

use Bmatovu\Ussd\Parser;

class ParserTest extends TestCase
{
    public function testMissingTag()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Missing tag DummyTag.');

        $xpath = $this->xmlToXpath('<dummy/>');

        $parser = (new Parser($xpath, 'ussd_wScXk'))->entry('/*[1]');

        $parser->parse();
    }

    public function testExceptionExit()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Bye bye.');

        $xpath = $this->xmlToXpath('<response text="Bye bye."/>');

        $parser = (new Parser($xpath, 'ussd_wScXk'))->entry('/*[1]');

        $parser->parse();
    }

    public function testProceedQuietly()
    {
        $xml = <<<'XML'
<menu>
    <variable name="name" value="John Doe"/>
    <question name="alias" text="Enter username: "/>
</menu>
XML;

        $xpath = $this->xmlToXpath($xml);

        $parser = new Parser($xpath, 'ussd_wScXk');

        $output = $parser->parse();

        static::assertSame('Enter username: ', $output);
        static::assertSame('John Doe', $parser->store->get('name'));
    }

    public function testReuseSession()
    {
        $this->store->put('_session_id', 'ussd_wScXk');
        $this->store->put('_pre', '');
        $this->store->put('_exp', '/menu/*[1]');

        $xml = <<<'XML'
<menu>
    <question name="alias" text="Enter username: "/>
</menu>
XML;

        $xpath = $this->xmlToXpath($xml);

        $parser = new Parser($xpath, 'ussd_wScXk');

        $output = $parser->parse();

        static::assertSame('Enter username: ', $output);
        static::assertSame('ussd_wScXk', $this->store->get('_session_id'));
        static::assertSame('/menu/*[1]', $this->store->get('_pre'));
        static::assertSame('/menu/*[2]', $this->store->get('_exp'));
    }

    public function testBreakpoints()
    {
        $xml = <<<'XML'
<menu>
    <variable name="gender" value="M"/>
    <if key="gender" value="M">
        <variable name="color" value="Blue"/>
    </if>
    <question name="greet" text="Say hi: "/>
</menu>
XML;
        $this->store->put('_session_id', 'ussd_wScXk');
        $this->store->put('_pre', '/menu/*[2]/*[1]');
        $this->store->put('_exp', '/menu/*[2]/*[2]');
        $this->store->put('_breakpoints', '[{"\/menu\/*[2]\/*[2]":"\/menu\/*[3]"}]');

        $xpath = $this->xmlToXpath($xml);

        $parser = new Parser($xpath, 'ussd_wScXk');

        $output = $parser->parse();

        static::assertSame('Say hi: ', $output);
    }
}
