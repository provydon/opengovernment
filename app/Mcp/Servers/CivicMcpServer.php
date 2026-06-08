<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateIssueTool;
use App\Mcp\Tools\GetLgaSummaryTool;
use App\Mcp\Tools\ListLocalGovernmentsTool;
use App\Mcp\Tools\SearchSimilarIssuesTool;
use App\Mcp\Tools\SearchSpendingRecordsTool;
use App\Mcp\Tools\UpvoteIssueTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('OpenGovernment')]
#[Version('0.1.0')]
#[Instructions(<<<'TXT'
This MCP server exposes an OpenGovernment instance — a civic platform where
citizens read what their local government spends on and post problems they
want fixed.

Reading tools (no auth required):
  - list_local_governments
  - search_spending_records
  - search_similar_issues
  - get_lga_summary

Writing tools (require an authenticated, identity-verified citizen):
  - create_issue
  - upvote_issue

Always call search_similar_issues before create_issue, so duplicate problems
fold into the existing issue instead of competing for votes.
TXT)]
class CivicMcpServer extends Server
{
    protected array $tools = [
        ListLocalGovernmentsTool::class,
        SearchSpendingRecordsTool::class,
        GetLgaSummaryTool::class,
        SearchSimilarIssuesTool::class,
        CreateIssueTool::class,
        UpvoteIssueTool::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
