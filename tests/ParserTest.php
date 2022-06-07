<?php

namespace Bmatovu\Ussd\Tests;

use Bmatovu\Ussd\Parser;

class ParserTest extends TestCase
{
    public function testMissingTag()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Missing class: DummyTag');

        $xpath = $this->xmlToXpath('<dummy/>');

        $parser = new Parser($xpath, '/*[1]', 'ussd_wScXk');

        $parser->parse();
    }

    public function testExceptionExit()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Bye bye.');

        $xpath = $this->xmlToXpath('<response text="Bye bye."/>');

        $parser = new Parser($xpath, '/*[1]', 'ussd_wScXk');

        $parser->parse();
    }

    public function testProceedQuietly()
    {
        $xml = <<<'XML'
<document>
    <variable name="name" value="John Doe"/>
    <question name="alias" text="Enter username: "/>
</document>
XML;

        $xpath = $this->xmlToXpath($xml);

        $parser = new Parser($xpath, '/document/*[1]', 'ussd_wScXk');

        $output = $parser->parse();

        static::assertSame('Enter username: ', $output);
        static::assertSame('John Doe', $parser->store->get('name'));
    }

    public function testReuseSession()
    {
        $this->store->put('_session_id', 'ussd_wScXk');
        $this->store->put('_pre', '');
        $this->store->put('_exp', '/document/*[1]');

        $xml = <<<'XML'
<document>
    <question name="alias" text="Enter username: "/>
</document>
XML;

        $xpath = $this->xmlToXpath($xml);

        $parser = new Parser($xpath, '/document/*[1]', 'ussd_wScXk');

        $output = $parser->parse();

        static::assertSame('Enter username: ', $output);
        static::assertSame('ussd_wScXk', $this->store->get('_session_id'));
        static::assertSame('/document/*[1]', $this->store->get('_pre'));
        static::assertSame('/document/*[2]', $this->store->get('_exp'));
    }

    public function testBreakpoints()
    {
        $xml = <<<'XML'
<document>
    <variable name="gender" value="M"/>
    <if key="gender" value="M">
        <variable name="color" value="Blue"/>
    </if>
    <question name="greet" text="Say hi: "/>
</document>
XML;
        $this->store->put('_session_id', 'ussd_wScXk');
        $this->store->put('_pre', '/document/*[2]/*[1]');
        $this->store->put('_exp', '/document/*[2]/*[2]');
        $this->store->put('_breakpoints', '[{"\/document\/*[2]\/*[2]":"\/document\/*[3]"}]');

        $xpath = $this->xmlToXpath($xml);

        $parser = new Parser($xpath, '/document/*[1]', 'ussd_wScXk');

        $output = $parser->parse();

        static::assertSame('Say hi: ', $output);
    }
}
