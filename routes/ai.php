<?php

use App\Mcp\Servers\CivicMcpServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| MCP server routes
|--------------------------------------------------------------------------
|
| The OpenGovernment MCP server lets external clients (Claude Desktop,
| Cursor, VS Code, scripts) talk to this deployment using the Model
| Context Protocol. Read-only tools are accessible without auth; the
| write tools (create_issue, upvote_issue) require an authenticated
| Sanctum user. See README -> "Using OpenGovernment from an MCP client".
|
*/

Mcp::web('/mcp', CivicMcpServer::class);

// Local (stdio) handle for `php artisan mcp:start opengovernment` — handy
// for plugging an editor into a checked-out clone of the project.
Mcp::local('opengovernment', CivicMcpServer::class);
