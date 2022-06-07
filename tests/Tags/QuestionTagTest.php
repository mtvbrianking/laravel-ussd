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
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Question requires an answer.');

        $node = $this->getNodeByTagName('<question name="alias" text="Enter Username: "/>', 'question');

        $tag = new QuestionTag($node, $this->store);

        $tag->process('');
    }
}
