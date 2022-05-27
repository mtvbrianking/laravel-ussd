<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\QuestionTag;
use Bmatovu\Ussd\Tests\TestCase;

class QuestionTagTest extends TestCase
{
    public function testHandleQuestion()
    {
        $this->cache->put('prefix_exp', '/*[1]');

        $node = $this->getNodeByTagName('<question name="alias" text="Enter Username: "/>', 'question');

        $tag = new QuestionTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertSame('Enter Username: ', $output);
        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[2]', $this->cache->get('prefix_exp'));
    }

    public function testProccessQuestion()
    {
        $node = $this->getNodeByTagName('<question name="alias" text="Enter Username: "/>', 'question');

        $tag = new QuestionTag($node, $this->cache, 'prefix', 30);

        $tag->process('jdoe');

        static::assertSame('jdoe', $this->cache->get('prefix_alias'));
    }

    public function testProccessQuestionValidation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Question requires an answer.');

        $node = $this->getNodeByTagName('<question name="alias" text="Enter Username: "/>', 'question');

        $tag = new QuestionTag($node, $this->cache, 'prefix', 30);

        $tag->process('');
    }
}
