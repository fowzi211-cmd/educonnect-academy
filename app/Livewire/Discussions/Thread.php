<?php

namespace App\Livewire\Discussions;

use App\Models\AuditLog;
use App\Models\DiscussionThread;
use App\Notifications\DiscussionReplyPosted;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Thread extends Component
{
    public DiscussionThread $thread;

    public string $replyBody = '';

    public bool $isModerator = false;

    public function mount(DiscussionThread $thread): void
    {
        abort_unless($thread->course->isAccessibleBy(Auth::user()), 403);

        $this->thread = $thread->load('user', 'lesson', 'course');
        $this->isModerator = $thread->course->isTaughtBy(Auth::user()) || Auth::user()->can('publish courses');

        abort_if($thread->is_hidden && ! $this->isModerator, 404);
    }

    public function reply(): void
    {
        abort_if($this->thread->is_locked, 403);

        $this->validate(['replyBody' => ['required', 'string', 'max:5000']]);

        $reply = $this->thread->replies()->create([
            'user_id' => Auth::id(),
            'body' => $this->replyBody,
        ]);

        AuditLog::record('discussion_reply.created', subject: $reply);

        if ($this->thread->user_id !== Auth::id()) {
            $reply->load('user');
            $this->thread->user->notify(new DiscussionReplyPosted($reply));
        }

        $this->replyBody = '';
    }

    public function togglePin(): void
    {
        abort_unless($this->isModerator, 403);

        $this->thread->update(['is_pinned' => ! $this->thread->is_pinned]);
        AuditLog::record('discussion_thread.pin_toggled', subject: $this->thread, new: ['is_pinned' => $this->thread->is_pinned]);
    }

    public function toggleLock(): void
    {
        abort_unless($this->isModerator, 403);

        $this->thread->update(['is_locked' => ! $this->thread->is_locked]);
        AuditLog::record('discussion_thread.lock_toggled', subject: $this->thread, new: ['is_locked' => $this->thread->is_locked]);
    }

    public function toggleHideThread(): void
    {
        abort_unless($this->isModerator, 403);

        $this->thread->update(['is_hidden' => ! $this->thread->is_hidden]);
        AuditLog::record('discussion_thread.hide_toggled', subject: $this->thread, new: ['is_hidden' => $this->thread->is_hidden]);
    }

    public function toggleHideReply(int $replyId): void
    {
        abort_unless($this->isModerator, 403);

        $reply = $this->thread->replies()->findOrFail($replyId);
        $reply->update(['is_hidden' => ! $reply->is_hidden]);
        AuditLog::record('discussion_reply.hide_toggled', subject: $reply, new: ['is_hidden' => $reply->is_hidden]);
    }

    public function render()
    {
        $replies = $this->thread->replies()
            ->with('user')
            ->when(! $this->isModerator, fn ($q) => $q->where('is_hidden', false))
            ->orderBy('created_at')
            ->get();

        return view('livewire.discussions.thread', [
            'replies' => $replies,
        ]);
    }
}
