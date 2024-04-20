<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\QuestionTag;
use Bmatovu\Ussd\Tests\TestCase;

class QuestionTagTest extends TestCase
{
    public function testHandleQuestion()
    {
        $this->store->put('_exp', '/*[1]');

        $node = $this->getNodeByTagName('<question name="alias" text="Enter Username: "/>', 'question');

        $tag = new QuestionTag($node, $this->store);

        $output = $tag->handle();

        static::assertSame('Enter Username: ', $output);
        static::assertSame('/*[1]', $this->store->get('_pre'));
        static::assertSame('/*[2]', $this->store->get('_exp'));
    }

    public function testProccessQuestion()
    {
        $node = $this->getNodeByTagName('<question name="alias" text="Enter Username: "/>', 'question');

        $tag = new QuestionTag($node, $this->store);

        $tag->process('jdoe');

        static::assertSame('jdoe', $this->store->get('alias'));
    }

    public function testProccessQuestionValidation()
    {
        $qn = <<<XML
<question
    name="pin"
    text="Enter PIN: "
    retries="1"
    pattern="^[0-9]{5}$"
    error="You entered the wrong PIN. Try again" />
XML;

        $node = $this->getNodeByTagName($qn, 'question');

        $tag = new QuestionTag($node, $this->store);

        $this->store->put('_pre', $_pre = '/*[1]');
        $this->store->put('_exp', $_exp = '/*[2]');

        $tag->process('524');

        static::assertSame('/*[0]', $this->store->get('_pre'));
        static::assertSame('/*[1]', $this->store->get('_exp'));
    }
}
