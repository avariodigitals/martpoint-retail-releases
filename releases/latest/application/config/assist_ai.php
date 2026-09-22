<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| MartPoint Assist — AI understanding layer
|--------------------------------------------------------------------------
|
| Assist works fully offline/rule-based by default. When enabled here, an
| OpenAI-compatible chat-completions endpoint is used to understand free-form
| instructions and route them to the existing flows. The AI NEVER writes to
| the database directly — it only picks a capability and fills in details.
|
| Works with any OpenAI-compatible API:
|   OpenAI:      https://api.openai.com/v1/chat/completions    (gpt-4o-mini)
|   OpenRouter:  https://openrouter.ai/api/v1/chat/completions (openai/gpt-4o-mini)
|   Groq:        https://api.groq.com/openai/v1/chat/completions (llama-3.1-8b-instant)
|   Together:    https://api.together.xyz/v1/chat/completions
|   Gemini:      https://generativelanguage.googleapis.com/v1beta/openai/chat/completions
|
*/
/*
| NOTE: Site Settings (db_sitesettings) takes precedence over this file —
| enable/provider/endpoint/model/key are managed from the admin UI.
| This file is the fallback for installs that have not run migration
| 4.0.9.35, and it still supplies headers/timeout/max_tokens/whitelist.
*/
$config['assist_ai'] = [
	// Master switch — set true once api_key is filled in
	'enabled'    => false,

	// Platform-level key (one key for all stores). Keep this file out of VCS.
	'api_key'    => '',

	// Any OpenAI-compatible chat completions endpoint
	'endpoint'   => 'https://api.openai.com/v1/chat/completions',

	// Model name the provider expects
	'model'      => 'gpt-4o-mini',

	// Optional headers some providers want (e.g. OpenRouter referer)
	'headers'    => [],

	// Network behaviour
	'timeout'    => 12,     // seconds
	'max_tokens' => 800,    // leave room for reasoning models (gpt-oss, qwen)

	// Extra safety: never send these to the AI (defence in depth — the brain
	// only gets labels/counts anyway, never raw customer data)
	'enabled_capabilities' => null, // null = all; or array of capability names to whitelist
];
