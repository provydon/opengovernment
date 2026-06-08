<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateIssueAiTool;
use App\Ai\Tools\SearchSimilarIssuesAiTool;
use App\Ai\Tools\SearchSpendingRecordsAiTool;
use App\Ai\Tools\UpvoteIssueAiTool;
use App\Models\User;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class CivicAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * @param  Message[]  $history  Prior turns in the conversation; passed in by the chat controller.
     */
    public function __construct(
        protected ?User $actor = null,
        protected array $history = [],
    ) {}

    public function instructions(): Stringable|string
    {
        $brand = config('opengovernment.brand.name');
        $userBlock = $this->actor
            ? "The signed-in citizen is {$this->actor->name}. Their home LGA id is "
                .($this->actor->local_government_id ?? 'unknown')
                .'. Verified: '.($this->actor->isVerified() ? 'yes' : 'no').'.'
            : 'No user is signed in. They can still browse and ask questions, but cannot file or vote.';

        return <<<PROMPT
You are the assistant for {$brand}, a civic platform where citizens read what their local government spends on, comment on it, and post problems they want fixed.

{$userBlock}

How to help:

1. If the citizen describes a problem in their area, your job is NOT to write a new issue immediately. First call `search_similar_issues` with their description and their LGA. If any match has `likely_duplicate: true`, show them the top 1-3 in plain language and ASK whether one of those is the same problem.
   - If they say yes -> call `upvote_issue` with that issue's id, then confirm to the user that their voice was added.
   - If they say no, or there are no good matches -> call `create_issue` with a clean title and body. Don't paste their raw message as the title — rewrite it as a short, specific headline.

2. If they ask about money / spending / vendors / budgets, call `search_spending_records`.

3. Be concise. Use the user's language. Don't moralise or lecture. If you don't have the LGA, ask once.

4. Never invent issue ids, amounts, or vendors. If a tool returns nothing, say so.
PROMPT;
    }

    public function messages(): iterable
    {
        return $this->history;
    }

    public function tools(): iterable
    {
        return [
            app()->make(SearchSimilarIssuesAiTool::class, ['actor' => $this->actor]),
            app()->make(CreateIssueAiTool::class, ['actor' => $this->actor]),
            app()->make(UpvoteIssueAiTool::class, ['actor' => $this->actor]),
            app()->make(SearchSpendingRecordsAiTool::class, ['actor' => $this->actor]),
        ];
    }
}
