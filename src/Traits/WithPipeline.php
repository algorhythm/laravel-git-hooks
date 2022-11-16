<?php

namespace Igorsgm\GitHooks\Traits;

use Closure;
use Igorsgm\GitHooks\Contracts\Hook;
use Igorsgm\GitHooks\HooksPipeline;
use Illuminate\Pipeline\Pipeline;

trait WithPipeline
{
    /**
     * Hook which is currently running in the Pipeline.
     *
     * @var Hook
     */
    public $hookExecuting;

    /**
     * Make pipeline instance
     *
     * @return Pipeline
     */
    protected function makePipeline(): Pipeline
    {
        $pipeline = new HooksPipeline(app(), $this->getHook());

        return $pipeline
            ->through($this->getRegisteredHooks())
            ->withPipeStartCallback($this->startHookConsoleTask())
            ->withPipeEndCallback($this->finishHookConsoleTask());
    }

    /**
     * {@inheritDoc}
     */
    public function getRegisteredHooks(): array
    {
        $hooks = collect((array) config('git-hooks.'.$this->getHook()));

        return $hooks->map(function ($hook, $i) {
            return is_int($i) ? $hook : $i;
        })->all();
    }

    /**
     * Show information about Hook which is being executed
     *
     * @return Closure
     */
    protected function startHookConsoleTask(): Closure
    {
        return function (Hook $hook) {
            $this->hookExecuting = $hook;

            $taskTitle = $this->getHookTaskTitle($hook);
            $loadingText = 'loading...';
            $this->output->write("$taskTitle: <comment>{$loadingText}</comment>");
        };
    }

    /**
     * Finish the console task of the Hook which just executed, with success or failure
     *
     * @return Closure
     */
    protected function finishHookConsoleTask(): Closure
    {
        return function ($success) {
            if (empty($this->hookExecuting)) {
                return;
            }

            if ($this->output->isDecorated()) { // Determines if we can use escape sequences
                // Move the cursor to the beginning of the line
                $this->output->write("\x0D");

                // Erase the line
                $this->output->write("\x1B[2K");
            } else {
                $this->output->writeln(''); // Make sure we first close the previous line
            }

            $taskTitle = $this->getHookTaskTitle($this->hookExecuting);

            $this->output->writeln(
                "$taskTitle: ".($success ? '<info>✔</info>' : '<error>failed</error>')
            );

            $this->hookExecuting = null;
        };
    }

    /**
     * @param  Hook  $hook
     * @return string
     */
    public function getHookTaskTitle(Hook $hook): string
    {
        return sprintf('  <bg=blue;fg=white> HOOK </> %s', $hook->getName());
    }
}
