<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GitPush extends Command
{
    protected $signature   = 'git:push {message? : Commit message (auto-generated if omitted)}';
    protected $description = 'Add all changes, commit, and push to GitHub';

    public function handle(): int
    {
        $root = base_path();

        // Check if there's anything to commit
        exec("git -C \"{$root}\" status --porcelain", $status);
        if (empty($status)) {
            $this->warn('Nothing to commit — working tree is clean.');
            return 0;
        }

        // Build commit message
        $message = $this->argument('message')
            ?? 'chore: auto-commit ' . now()->format('Y-m-d H:i');

        // Show summary of changes
        $this->info('Changes to commit:');
        foreach ($status as $line) {
            $flag = trim(substr($line, 0, 2));
            $file = trim(substr($line, 3));
            $color = match(true) {
                str_contains($flag, 'M') => 'yellow',
                str_contains($flag, 'A') => 'green',
                str_contains($flag, 'D') => 'red',
                default                  => 'white',
            };
            $this->line("  <fg={$color}>{$flag}</> {$file}");
        }

        $this->line('');
        $this->info("Message: \"{$message}\"");

        if (! $this->confirm('Proceed?', true)) {
            $this->line('Aborted.');
            return 0;
        }

        // git add
        exec("git -C \"{$root}\" add .", $out1, $code1);
        if ($code1 !== 0) { $this->error('git add failed'); return 1; }
        $this->line('<fg=green>✓</> Staged all changes');

        // git commit
        exec("git -C \"{$root}\" commit -m " . escapeshellarg($message), $out2, $code2);
        if ($code2 !== 0) { $this->error('git commit failed'); return 1; }
        $this->line('<fg=green>✓</> Committed');

        // git push
        exec("git -C \"{$root}\" push 2>&1", $out3, $code3);
        if ($code3 !== 0) {
            $this->error('git push failed: ' . implode("\n", $out3));
            return 1;
        }
        $this->line('<fg=green>✓</> Pushed to GitHub');
        $this->info('Done! https://github.com/hwalima/insiza-expo');

        return 0;
    }
}
