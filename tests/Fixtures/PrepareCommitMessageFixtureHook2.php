<?php

namespace Igorsgm\GitHooks\Tests\Fixtures;

use Closure;
use Igorsgm\GitHooks\Contracts\MessageHook;
use Igorsgm\GitHooks\Git\CommitMessage;

class PrepareCommitMessageFixtureHook2 implements MessageHook
{
    /**
     * {@inheritDoc}
     */
    public function handle(CommitMessage $message, Closure $next)
    {
        $message->setMessage($message->getMessage().' hook2');

        return $next($message);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'Prepare Commit Message Hook 2';
    }
}
