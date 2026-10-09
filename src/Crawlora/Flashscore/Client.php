<?php

declare(strict_types=1);

namespace Crawlora\Flashscore;

class CrawloraException extends \RuntimeException
{
    public function __construct(string $message, public readonly ?int $status = null, public readonly ?string $operationId = null, public readonly ?string $responseBody = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

class ClientException extends CrawloraException {}
class ServerException extends CrawloraException {}
class NetworkException extends CrawloraException {}

final class Client
{
    private static array $operations;
    private bool $closed = false;
    private string $apiKey;
    private string $baseUrl;
    private float $timeout;
    private ?\Closure $transport;

    public const PLATFORM = 'flashscore';
    public const VERSION = '0.2.0';
    public const OPERATION_COUNT = 38;
    public const OPERATION_IDS = ["flashscore-calendar", "flashscore-calendar-categories", "flashscore-competitions", "flashscore-match-h2h", "flashscore-match-highlights", "flashscore-match-info", "flashscore-match-lineups", "flashscore-match-missing-players", "flashscore-match-news", "flashscore-match-odds", "flashscore-match-predicted-lineups", "flashscore-match-standings", "flashscore-match-stats", "flashscore-match-tv", "flashscore-navigation", "flashscore-news", "flashscore-news-article", "flashscore-news-categories", "flashscore-odds-geos", "flashscore-player", "flashscore-player-injuries", "flashscore-player-transfers", "flashscore-ranking-categories", "flashscore-rankings", "flashscore-scores", "flashscore-search", "flashscore-sports", "flashscore-team", "flashscore-team-fixtures", "flashscore-team-news", "flashscore-team-results", "flashscore-team-squad", "flashscore-team-transfers", "flashscore-top-search", "flashscore-tournament-events", "flashscore-tournament-seasons", "flashscore-tournament-standings", "flashscore-tournament-standings-views"];

    public function __construct(?string $apiKey = null, string $baseUrl = 'https://api.crawlora.net/api/v1', float $timeout = 30.0, ?callable $transport = null)
    {
        $this->apiKey = $apiKey ?? (getenv('CRAWLORA_API_KEY') ?: '');
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->transport = $transport === null ? null : \Closure::fromCallable($transport);
        self::$operations ??= json_decode(<<<'JSON'
{"flashscore-calendar": {"id": "flashscore-calendar", "method": "GET", "params": [{"description": "Calendar category from flashscore-calendar-categories", "enum": ["tennis-atp", "tennis-wta", "golf-pga", "golf-dp-world", "badminton-bwf", "motorsport-f1"], "in": "query", "name": "category", "required": true, "type": "string", "x-example": "tennis-atp"}], "path": "/flashscore/calendar", "pathParams": [], "produces": ["application/json"], "queryParams": [{"enum": ["tennis-atp", "tennis-wta", "golf-pga", "golf-dp-world", "badminton-bwf", "motorsport-f1"], "in": "query", "name": "category", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-calendar-categories": {"id": "flashscore-calendar-categories", "method": "GET", "params": [], "path": "/flashscore/calendar-categories", "pathParams": [], "produces": ["application/json"], "queryParams": [], "security": ["ApiKeyAuth"]}, "flashscore-competitions": {"id": "flashscore-competitions", "method": "GET", "params": [{"description": "Sport key", "enum": ["football", "tennis", "basketball", "hockey", "golf", "formula-1", "baseball", "snooker", "american-football", "aussie-rules", "badminton", "bandy", "beach-soccer", "beach-volleyball", "boxing", "cricket", "cycling", "darts", "esports", "field-hockey", "floorball", "futsal", "handball", "horse-racing", "kabaddi", "mma", "motorsport", "netball", "pesapallo", "rugby-league", "rugby-union", "table-tennis", "volleyball", "water-polo", "winter-sports"], "in": "query", "name": "sport", "required": true, "type": "string", "x-example": "football"}, {"description": "Days from today; -7 to 7; defaults to 0", "in": "query", "maximum": 7, "minimum": -7, "name": "day_offset", "type": "integer", "x-example": 0}], "path": "/flashscore/competitions", "pathParams": [], "produces": ["application/json"], "queryParams": [{"enum": ["football", "tennis", "basketball", "hockey", "golf", "formula-1", "baseball", "snooker", "american-football", "aussie-rules", "badminton", "bandy", "beach-soccer", "beach-volleyball", "boxing", "cricket", "cycling", "darts", "esports", "field-hockey", "floorball", "futsal", "handball", "horse-racing", "kabaddi", "mma", "motorsport", "netball", "pesapallo", "rugby-league", "rugby-union", "table-tennis", "volleyball", "water-polo", "winter-sports"], "in": "query", "name": "sport", "required": true, "type": "string"}, {"in": "query", "name": "day_offset", "type": "integer"}], "security": ["ApiKeyAuth"]}, "flashscore-match-h2h": {"id": "flashscore-match-h2h", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "xtmHKGT0"}], "path": "/flashscore/match-h2h", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-match-highlights": {"id": "flashscore-match-highlights", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "j52jHsN8"}], "path": "/flashscore/match-highlights", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-match-info": {"id": "flashscore-match-info", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "xtmHKGT0"}], "path": "/flashscore/match-info", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-match-lineups": {"id": "flashscore-match-lineups", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "j52jHsN8"}], "path": "/flashscore/match-lineups", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-match-missing-players": {"id": "flashscore-match-missing-players", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "f75igXFG"}], "path": "/flashscore/match-missing-players", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-match-news": {"id": "flashscore-match-news", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "xtmHKGT0"}], "path": "/flashscore/match-news", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-match-odds": {"id": "flashscore-match-odds", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "p0aFxkkn"}, {"description": "Viewer market country code from flashscore-odds-geos; defaults to GB", "enum": ["AE", "AL", "AM", "AO", "AR", "AT", "AU", "AZ", "BA", "BD", "BE", "BG", "BO", "BR", "BY", "CA", "CH", "CI", "CL", "CM", "CN", "CO", "CR", "CY", "CZ", "DE", "DK", "DO", "DZ", "EC", "EE", "EG", "ES", "ET", "FI", "FR", "GB", "GE", "GH", "GR", "GT", "HK", "HN", "HR", "HU", "ID", "IE", "IL", "IN", "IQ", "IR", "IS", "IT", "JO", "JP", "KE", "KG", "KH", "KR", "KW", "KZ", "LA", "LB", "LK", "LT", "LU", "LV", "LY", "MA", "MD", "ME", "MK", "MM", "MN", "MT", "MX", "MY", "NG", "NI", "NL", "NO", "NP", "NZ", "PA", "PE", "PH", "PK", "PL", "PT", "PY", "QA", "RO", "RS", "RU", "SA", "SD", "SE", "SG", "SI", "SK", "SN", "SV", "TH", "TN", "TR", "TW", "TZ", "UA", "UG", "US", "UY", "UZ", "VE", "VN", "XK", "ZA", "ZM", "ZW"], "in": "query", "name": "geo", "type": "string", "x-example": "GB"}, {"description": "US state (with geo US) or Canadian province (with geo CA) code from flashscore-odds-geos; rejected with any other geo", "enum": ["AB", "AK", "AL", "AR", "AZ", "BC", "CA", "CO", "CT", "DC", "DE", "FL", "GA", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA", "MA", "MB", "MD", "ME", "MI", "MN", "MO", "MS", "MT", "NB", "NC", "ND", "NE", "NH", "NJ", "NL", "NM", "NS", "NT", "NU", "NV", "NY", "OH", "OK", "ON", "OR", "PA", "PE", "QC", "RI", "SC", "SD", "SK", "TN", "TX", "UT", "VA", "VT", "WA", "WI", "WV", "WY", "YT"], "in": "query", "name": "subdivision", "type": "string", "x-example": "NJ"}, {"description": "Optional filter to one betting type", "enum": ["HOME_DRAW_AWAY", "HOME_AWAY", "DRAW_NO_BET", "DOUBLE_CHANCE", "ASIAN_HANDICAP", "EUROPEAN_HANDICAP", "OVER_UNDER", "BOTH_TEAMS_TO_SCORE", "CORRECT_SCORE", "HALF_FULL_TIME", "ODD_OR_EVEN", "TO_QUALIFY", "NEXT_GOAL", "TOP_POSITION_MERGED", "TO_WIN_AND_TOP_POSITION", "WIN_EACH_WAY"], "in": "query", "name": "betting_type", "type": "string", "x-example": "HOME_DRAW_AWAY"}, {"description": "Optional filter to one scope", "enum": ["FULL_TIME", "FULL_TIME_OVER_TIME", "FIRST_HALF", "SECOND_HALF", "FIRST_PERIOD", "FIRST_QUARTER", "FIRST_SET", "SECOND_SET"], "in": "query", "name": "scope", "type": "string", "x-example": "FULL_TIME"}], "path": "/flashscore/match-odds", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}, {"enum": ["AE", "AL", "AM", "AO", "AR", "AT", "AU", "AZ", "BA", "BD", "BE", "BG", "BO", "BR", "BY", "CA", "CH", "CI", "CL", "CM", "CN", "CO", "CR", "CY", "CZ", "DE", "DK", "DO", "DZ", "EC", "EE", "EG", "ES", "ET", "FI", "FR", "GB", "GE", "GH", "GR", "GT", "HK", "HN", "HR", "HU", "ID", "IE", "IL", "IN", "IQ", "IR", "IS", "IT", "JO", "JP", "KE", "KG", "KH", "KR", "KW", "KZ", "LA", "LB", "LK", "LT", "LU", "LV", "LY", "MA", "MD", "ME", "MK", "MM", "MN", "MT", "MX", "MY", "NG", "NI", "NL", "NO", "NP", "NZ", "PA", "PE", "PH", "PK", "PL", "PT", "PY", "QA", "RO", "RS", "RU", "SA", "SD", "SE", "SG", "SI", "SK", "SN", "SV", "TH", "TN", "TR", "TW", "TZ", "UA", "UG", "US", "UY", "UZ", "VE", "VN", "XK", "ZA", "ZM", "ZW"], "in": "query", "name": "geo", "type": "string"}, {"enum": ["AB", "AK", "AL", "AR", "AZ", "BC", "CA", "CO", "CT", "DC", "DE", "FL", "GA", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA", "MA", "MB", "MD", "ME", "MI", "MN", "MO", "MS", "MT", "NB", "NC", "ND", "NE", "NH", "NJ", "NL", "NM", "NS", "NT", "NU", "NV", "NY", "OH", "OK", "ON", "OR", "PA", "PE", "QC", "RI", "SC", "SD", "SK", "TN", "TX", "UT", "VA", "VT", "WA", "WI", "WV", "WY", "YT"], "in": "query", "name": "subdivision", "type": "string"}, {"enum": ["HOME_DRAW_AWAY", "HOME_AWAY", "DRAW_NO_BET", "DOUBLE_CHANCE", "ASIAN_HANDICAP", "EUROPEAN_HANDICAP", "OVER_UNDER", "BOTH_TEAMS_TO_SCORE", "CORRECT_SCORE", "HALF_FULL_TIME", "ODD_OR_EVEN", "TO_QUALIFY", "NEXT_GOAL", "TOP_POSITION_MERGED", "TO_WIN_AND_TOP_POSITION", "WIN_EACH_WAY"], "in": "query", "name": "betting_type", "type": "string"}, {"enum": ["FULL_TIME", "FULL_TIME_OVER_TIME", "FIRST_HALF", "SECOND_HALF", "FIRST_PERIOD", "FIRST_QUARTER", "FIRST_SET", "SECOND_SET"], "in": "query", "name": "scope", "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-match-predicted-lineups": {"id": "flashscore-match-predicted-lineups", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "f75igXFG"}], "path": "/flashscore/match-predicted-lineups", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-match-standings": {"id": "flashscore-match-standings", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "xtmHKGT0"}, {"description": "Standings/table view; defaults to overall", "enum": ["overall", "home", "away", "form_overall", "overunder_overall", "form_home", "form_away", "top_scorers", "htft_overall", "htft_home", "htft_away", "live_overall", "overunder_home", "overunder_away"], "in": "query", "name": "view", "type": "string", "x-example": "overall"}], "path": "/flashscore/match-standings", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}, {"enum": ["overall", "home", "away", "form_overall", "overunder_overall", "form_home", "form_away", "top_scorers", "htft_overall", "htft_home", "htft_away", "live_overall", "overunder_home", "overunder_away"], "in": "query", "name": "view", "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-match-stats": {"id": "flashscore-match-stats", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "xtmHKGT0"}], "path": "/flashscore/match-stats", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-match-tv": {"id": "flashscore-match-tv", "method": "GET", "params": [{"description": "Eight-character Flashscore match id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "p0aFxkkn"}, {"description": "Viewer market country code from flashscore-odds-geos; defaults to GB", "enum": ["AE", "AL", "AM", "AO", "AR", "AT", "AU", "AZ", "BA", "BD", "BE", "BG", "BO", "BR", "BY", "CA", "CH", "CI", "CL", "CM", "CN", "CO", "CR", "CY", "CZ", "DE", "DK", "DO", "DZ", "EC", "EE", "EG", "ES", "ET", "FI", "FR", "GB", "GE", "GH", "GR", "GT", "HK", "HN", "HR", "HU", "ID", "IE", "IL", "IN", "IQ", "IR", "IS", "IT", "JO", "JP", "KE", "KG", "KH", "KR", "KW", "KZ", "LA", "LB", "LK", "LT", "LU", "LV", "LY", "MA", "MD", "ME", "MK", "MM", "MN", "MT", "MX", "MY", "NG", "NI", "NL", "NO", "NP", "NZ", "PA", "PE", "PH", "PK", "PL", "PT", "PY", "QA", "RO", "RS", "RU", "SA", "SD", "SE", "SG", "SI", "SK", "SN", "SV", "TH", "TN", "TR", "TW", "TZ", "UA", "UG", "US", "UY", "UZ", "VE", "VN", "XK", "ZA", "ZM", "ZW"], "in": "query", "name": "geo", "type": "string", "x-example": "GB"}], "path": "/flashscore/match-tv", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}, {"enum": ["AE", "AL", "AM", "AO", "AR", "AT", "AU", "AZ", "BA", "BD", "BE", "BG", "BO", "BR", "BY", "CA", "CH", "CI", "CL", "CM", "CN", "CO", "CR", "CY", "CZ", "DE", "DK", "DO", "DZ", "EC", "EE", "EG", "ES", "ET", "FI", "FR", "GB", "GE", "GH", "GR", "GT", "HK", "HN", "HR", "HU", "ID", "IE", "IL", "IN", "IQ", "IR", "IS", "IT", "JO", "JP", "KE", "KG", "KH", "KR", "KW", "KZ", "LA", "LB", "LK", "LT", "LU", "LV", "LY", "MA", "MD", "ME", "MK", "MM", "MN", "MT", "MX", "MY", "NG", "NI", "NL", "NO", "NP", "NZ", "PA", "PE", "PH", "PK", "PL", "PT", "PY", "QA", "RO", "RS", "RU", "SA", "SD", "SE", "SG", "SI", "SK", "SN", "SV", "TH", "TN", "TR", "TW", "TZ", "UA", "UG", "US", "UY", "UZ", "VE", "VN", "XK", "ZA", "ZM", "ZW"], "in": "query", "name": "geo", "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-navigation": {"id": "flashscore-navigation", "method": "GET", "params": [{"description": "Relative Flashscore navigation path; defaults to /. Use a path returned by flashscore-sports or flashscore-navigation.", "in": "query", "name": "path", "type": "string", "x-example": "/basketball/usa/"}], "path": "/flashscore/navigation", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "path", "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-news": {"id": "flashscore-news", "method": "GET", "params": [{"description": "News section key; defaults to all", "enum": ["all", "football", "uefa-nations-league", "tennis", "features", "premier-league", "nfl", "mlb", "nba", "nhl", "formula-1", "champions-league", "europa-league", "conference-league", "darts", "snooker", "golf", "road-cycling", "laliga", "bundesliga", "serie-a", "ligue-1", "badminton", "handball", "hockey", "basketball", "cricket", "rugby-union", "athletics", "baseball", "fifa", "rugby-league", "motorsport", "aussie-rules", "flashscore-ratings", "american-sports", "african-football", "combat-sports", "winter-sports", "transfer-news"], "in": "query", "name": "category", "type": "string", "x-example": "football"}, {"description": "1-based page, 1 to 100; defaults to 1", "in": "query", "maximum": 100, "minimum": 1, "name": "page", "type": "integer", "x-example": 1}], "path": "/flashscore/news", "pathParams": [], "produces": ["application/json"], "queryParams": [{"enum": ["all", "football", "uefa-nations-league", "tennis", "features", "premier-league", "nfl", "mlb", "nba", "nhl", "formula-1", "champions-league", "europa-league", "conference-league", "darts", "snooker", "golf", "road-cycling", "laliga", "bundesliga", "serie-a", "ligue-1", "badminton", "handball", "hockey", "basketball", "cricket", "rugby-union", "athletics", "baseball", "fifa", "rugby-league", "motorsport", "aussie-rules", "flashscore-ratings", "american-sports", "african-football", "combat-sports", "winter-sports", "transfer-news"], "in": "query", "name": "category", "type": "string"}, {"in": "query", "name": "page", "type": "integer"}], "security": ["ApiKeyAuth"]}, "flashscore-news-article": {"id": "flashscore-news-article", "method": "GET", "params": [{"description": "Eight-character Flashscore article id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "GYDHQ2yR"}], "path": "/flashscore/news-article", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-news-categories": {"id": "flashscore-news-categories", "method": "GET", "params": [], "path": "/flashscore/news-categories", "pathParams": [], "produces": ["application/json"], "queryParams": [], "security": ["ApiKeyAuth"]}, "flashscore-odds-geos": {"id": "flashscore-odds-geos", "method": "GET", "params": [], "path": "/flashscore/odds-geos", "pathParams": [], "produces": ["application/json"], "queryParams": [], "security": ["ApiKeyAuth"]}, "flashscore-player": {"id": "flashscore-player", "method": "GET", "params": [{"description": "Eight-character Flashscore player id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "UmV9iQmE"}, {"description": "Optional player slug; must match the id when supplied", "in": "query", "name": "slug", "type": "string", "x-example": "haaland-erling"}], "path": "/flashscore/player", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}, {"in": "query", "name": "slug", "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-player-injuries": {"id": "flashscore-player-injuries", "method": "GET", "params": [{"description": "Eight-character Flashscore player id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "UmV9iQmE"}, {"description": "Optional player slug; must match the id when supplied", "in": "query", "name": "slug", "type": "string", "x-example": "haaland-erling"}], "path": "/flashscore/player-injuries", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}, {"in": "query", "name": "slug", "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-player-transfers": {"id": "flashscore-player-transfers", "method": "GET", "params": [{"description": "Eight-character Flashscore player id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "UmV9iQmE"}, {"description": "Optional player slug; must match the id when supplied", "in": "query", "name": "slug", "type": "string", "x-example": "haaland-erling"}], "path": "/flashscore/player-transfers", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}, {"in": "query", "name": "slug", "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-ranking-categories": {"id": "flashscore-ranking-categories", "method": "GET", "params": [], "path": "/flashscore/ranking-categories", "pathParams": [], "produces": ["application/json"], "queryParams": [], "security": ["ApiKeyAuth"]}, "flashscore-rankings": {"id": "flashscore-rankings", "method": "GET", "params": [{"description": "Ranking category from flashscore-ranking-categories", "enum": ["fifa", "tennis-atp", "tennis-wta", "tennis-atp-race", "tennis-wta-race", "tennis-atp-doubles", "tennis-wta-doubles", "tennis-atp-doubles-race", "tennis-wta-doubles-race", "badminton-bwf-singles-men", "badminton-bwf-singles-women", "badminton-bwf-doubles-men", "badminton-bwf-doubles-women", "badminton-bwf-mixed-doubles", "golf-owgr", "golf-wwgr", "golf-pga-fedexcup", "golf-pga-money", "golf-dp-world-tour", "golf-lpga", "golf-asian-tour", "golf-japan-tour", "golf-sunshine-tour", "golf-korn-ferry", "golf-champions-tour", "darts-world-ranking", "snooker-world-ranking", "tennis-atp-live", "tennis-wta-live", "tennis-atp-race-live", "tennis-wta-race-live", "tennis-atp-doubles-live", "tennis-wta-doubles-live", "tennis-atp-doubles-race-live", "tennis-wta-doubles-race-live"], "in": "query", "name": "category", "required": true, "type": "string", "x-example": "tennis-atp"}], "path": "/flashscore/rankings", "pathParams": [], "produces": ["application/json"], "queryParams": [{"enum": ["fifa", "tennis-atp", "tennis-wta", "tennis-atp-race", "tennis-wta-race", "tennis-atp-doubles", "tennis-wta-doubles", "tennis-atp-doubles-race", "tennis-wta-doubles-race", "badminton-bwf-singles-men", "badminton-bwf-singles-women", "badminton-bwf-doubles-men", "badminton-bwf-doubles-women", "badminton-bwf-mixed-doubles", "golf-owgr", "golf-wwgr", "golf-pga-fedexcup", "golf-pga-money", "golf-dp-world-tour", "golf-lpga", "golf-asian-tour", "golf-japan-tour", "golf-sunshine-tour", "golf-korn-ferry", "golf-champions-tour", "darts-world-ranking", "snooker-world-ranking", "tennis-atp-live", "tennis-wta-live", "tennis-atp-race-live", "tennis-wta-race-live", "tennis-atp-doubles-live", "tennis-wta-doubles-live", "tennis-atp-doubles-race-live", "tennis-wta-doubles-race-live"], "in": "query", "name": "category", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-scores": {"id": "flashscore-scores", "method": "GET", "params": [{"description": "Sport key", "enum": ["football", "tennis", "basketball", "hockey", "golf", "formula-1", "baseball", "snooker", "american-football", "aussie-rules", "badminton", "bandy", "beach-soccer", "beach-volleyball", "boxing", "cricket", "cycling", "darts", "esports", "field-hockey", "floorball", "futsal", "handball", "horse-racing", "kabaddi", "mma", "motorsport", "netball", "pesapallo", "rugby-league", "rugby-union", "table-tennis", "volleyball", "water-polo", "winter-sports"], "in": "query", "name": "sport", "required": true, "type": "string", "x-example": "football"}, {"description": "Days from today; -7 to 7; defaults to 0", "in": "query", "maximum": 7, "minimum": -7, "name": "day_offset", "type": "integer", "x-example": 0}], "path": "/flashscore/scores", "pathParams": [], "produces": ["application/json"], "queryParams": [{"enum": ["football", "tennis", "basketball", "hockey", "golf", "formula-1", "baseball", "snooker", "american-football", "aussie-rules", "badminton", "bandy", "beach-soccer", "beach-volleyball", "boxing", "cricket", "cycling", "darts", "esports", "field-hockey", "floorball", "futsal", "handball", "horse-racing", "kabaddi", "mma", "motorsport", "netball", "pesapallo", "rugby-league", "rugby-union", "table-tennis", "volleyball", "water-polo", "winter-sports"], "in": "query", "name": "sport", "required": true, "type": "string"}, {"in": "query", "name": "day_offset", "type": "integer"}], "security": ["ApiKeyAuth"]}, "flashscore-search": {"id": "flashscore-search", "method": "GET", "params": [{"description": "Search phrase (2 to 80 characters)", "in": "query", "maxLength": 80, "minLength": 2, "name": "q", "required": true, "type": "string", "x-example": "Champions League"}], "path": "/flashscore/search", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "q", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-sports": {"id": "flashscore-sports", "method": "GET", "params": [], "path": "/flashscore/sports", "pathParams": [], "produces": ["application/json"], "queryParams": [], "security": ["ApiKeyAuth"]}, "flashscore-team": {"id": "flashscore-team", "method": "GET", "params": [{"description": "Eight-character Flashscore team id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "Wtn9Stg0"}], "path": "/flashscore/team", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-team-fixtures": {"id": "flashscore-team-fixtures", "method": "GET", "params": [{"description": "Eight-character Flashscore team id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "Wtn9Stg0"}, {"description": "1-based page from 1 to 300; defaults to 1", "in": "query", "maximum": 300, "minimum": 1, "name": "page", "type": "integer", "x-example": 1}], "path": "/flashscore/team-fixtures", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}, {"in": "query", "name": "page", "type": "integer"}], "security": ["ApiKeyAuth"]}, "flashscore-team-news": {"id": "flashscore-team-news", "method": "GET", "params": [{"description": "Eight-character Flashscore team id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "Wtn9Stg0"}], "path": "/flashscore/team-news", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-team-results": {"id": "flashscore-team-results", "method": "GET", "params": [{"description": "Eight-character Flashscore team id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "Wtn9Stg0"}, {"description": "1-based page from 1 to 300; defaults to 1", "in": "query", "maximum": 300, "minimum": 1, "name": "page", "type": "integer", "x-example": 1}], "path": "/flashscore/team-results", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}, {"in": "query", "name": "page", "type": "integer"}], "security": ["ApiKeyAuth"]}, "flashscore-team-squad": {"id": "flashscore-team-squad", "method": "GET", "params": [{"description": "Eight-character Flashscore team id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "Wtn9Stg0"}, {"description": "Optional team slug; must match the id when supplied", "in": "query", "name": "slug", "type": "string", "x-example": "manchester-city"}, {"description": "Squad statistics scope key from the scopes list of a previous response (for example overall-all or league-SY30SsKF); defaults to overall-all", "in": "query", "name": "scope", "type": "string", "x-example": "overall-all"}], "path": "/flashscore/team-squad", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}, {"in": "query", "name": "slug", "type": "string"}, {"in": "query", "name": "scope", "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-team-transfers": {"id": "flashscore-team-transfers", "method": "GET", "params": [{"description": "Eight-character Flashscore team id", "in": "query", "name": "id", "required": true, "type": "string", "x-example": "Wtn9Stg0"}, {"description": "Transfer direction filter; defaults to all", "enum": ["all", "arrivals", "departures"], "in": "query", "name": "type", "type": "string", "x-example": "all"}, {"description": "1-based page from 1 to 300; defaults to 1", "in": "query", "maximum": 300, "minimum": 1, "name": "page", "type": "integer", "x-example": 1}], "path": "/flashscore/team-transfers", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "id", "required": true, "type": "string"}, {"enum": ["all", "arrivals", "departures"], "in": "query", "name": "type", "type": "string"}, {"in": "query", "name": "page", "type": "integer"}], "security": ["ApiKeyAuth"]}, "flashscore-top-search": {"id": "flashscore-top-search", "method": "GET", "params": [], "path": "/flashscore/top-search", "pathParams": [], "produces": ["application/json"], "queryParams": [], "security": ["ApiKeyAuth"]}, "flashscore-tournament-events": {"id": "flashscore-tournament-events", "method": "GET", "params": [{"description": "Relative Flashscore competition season results or fixtures path", "in": "query", "name": "path", "required": true, "type": "string", "x-example": "/football/england/premier-league-2026-2027/fixtures/"}, {"description": "1-based page; defaults to 1", "in": "query", "maximum": 100, "minimum": 1, "name": "page", "type": "integer", "x-example": 1}], "path": "/flashscore/tournament-events", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "path", "required": true, "type": "string"}, {"in": "query", "name": "page", "type": "integer"}], "security": ["ApiKeyAuth"]}, "flashscore-tournament-seasons": {"id": "flashscore-tournament-seasons", "method": "GET", "params": [{"description": "Relative Flashscore competition archive path ending in /archive/", "in": "query", "name": "path", "required": true, "type": "string", "x-example": "/football/europe/champions-league/archive/"}], "path": "/flashscore/tournament-seasons", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "path", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-tournament-standings": {"id": "flashscore-tournament-standings", "method": "GET", "params": [{"description": "Relative Flashscore competition path from flashscore-navigation or flashscore-competitions; omit /standings/", "in": "query", "name": "path", "required": true, "type": "string", "x-example": "/football/england/premier-league/"}, {"description": "View returned by flashscore-tournament-standings-views; defaults to overall", "enum": ["overall", "home", "away", "form_overall", "form_home", "form_away", "overunder_overall", "overunder_home", "overunder_away", "htft_overall", "htft_home", "htft_away", "top_scorers"], "in": "query", "name": "view", "type": "string", "x-example": "overall"}], "path": "/flashscore/tournament-standings", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "path", "required": true, "type": "string"}, {"enum": ["overall", "home", "away", "form_overall", "form_home", "form_away", "overunder_overall", "overunder_home", "overunder_away", "htft_overall", "htft_home", "htft_away", "top_scorers"], "in": "query", "name": "view", "type": "string"}], "security": ["ApiKeyAuth"]}, "flashscore-tournament-standings-views": {"id": "flashscore-tournament-standings-views", "method": "GET", "params": [{"description": "Relative Flashscore competition path from flashscore-navigation or flashscore-competitions; omit /standings/", "in": "query", "name": "path", "required": true, "type": "string", "x-example": "/football/england/premier-league/"}], "path": "/flashscore/tournament-standings-views", "pathParams": [], "produces": ["application/json"], "queryParams": [{"in": "query", "name": "path", "required": true, "type": "string"}], "security": ["ApiKeyAuth"]}}
JSON, true, 512, JSON_THROW_ON_ERROR);
    }

    public function request(string $operationId, array $params = [], string $responseType = 'auto'): mixed
    {
        if ($this->closed) {
            throw new ClientException('Client is closed', null, $operationId);
        }
        $operation = self::$operations[$operationId] ?? null;
        if ($operation === null) {
            throw new ClientException('Unknown operation: ' . $operationId, null, $operationId);
        }
        if ($this->apiKey === '') {
            throw new ClientException('Crawlora API key is required', null, $operationId);
        }
        $url = $this->buildUrl($operation, $params);
        $headers = [
            'x-api-key: ' . $this->apiKey,
            'User-Agent: crawlora-flashscore-php/0.2.0',
            'Accept: ' . (in_array('text/plain', $operation['produces'], true) ? 'application/json, text/plain' : 'application/json'),
        ];
        try {
            [$status, $contentType, $body] = $this->send($url, $headers, $operationId);
        } catch (CrawloraException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new NetworkException('Crawlora request failed: ' . $exception->getMessage(), null, $operationId, null, $exception);
        }
        if ($status < 200 || $status >= 300) {
            $class = $status >= 500 ? ServerException::class : ClientException::class;
            throw new $class('Crawlora returned HTTP ' . $status, $status, $operationId, $body);
        }
        return $this->parseResponse($body, $contentType, $operation, $params, $responseType);
    }

    public function close(): void
    {
        $this->closed = true;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function operationCount(): int
    {
        return self::OPERATION_COUNT;
    }

    public function operationIds(): array
    {
        return self::OPERATION_IDS;
    }

    public function operations(): array
    {
        return self::$operations;
    }

    public function calendar(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-calendar", $params, $responseType);
    }
    public function calendar_categories(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-calendar-categories", $params, $responseType);
    }
    public function competitions(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-competitions", $params, $responseType);
    }
    public function match_h2h(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-h2h", $params, $responseType);
    }
    public function match_highlights(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-highlights", $params, $responseType);
    }
    public function match_info(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-info", $params, $responseType);
    }
    public function match_lineups(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-lineups", $params, $responseType);
    }
    public function match_missing_players(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-missing-players", $params, $responseType);
    }
    public function match_news(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-news", $params, $responseType);
    }
    public function match_odds(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-odds", $params, $responseType);
    }
    public function match_predicted_lineups(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-predicted-lineups", $params, $responseType);
    }
    public function match_standings(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-standings", $params, $responseType);
    }
    public function match_stats(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-stats", $params, $responseType);
    }
    public function match_tv(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-match-tv", $params, $responseType);
    }
    public function navigation(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-navigation", $params, $responseType);
    }
    public function news(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-news", $params, $responseType);
    }
    public function news_article(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-news-article", $params, $responseType);
    }
    public function news_categories(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-news-categories", $params, $responseType);
    }
    public function odds_geos(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-odds-geos", $params, $responseType);
    }
    public function player(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-player", $params, $responseType);
    }
    public function player_injuries(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-player-injuries", $params, $responseType);
    }
    public function player_transfers(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-player-transfers", $params, $responseType);
    }
    public function ranking_categories(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-ranking-categories", $params, $responseType);
    }
    public function rankings(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-rankings", $params, $responseType);
    }
    public function scores(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-scores", $params, $responseType);
    }
    public function search(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-search", $params, $responseType);
    }
    public function sports(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-sports", $params, $responseType);
    }
    public function team(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-team", $params, $responseType);
    }
    public function team_fixtures(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-team-fixtures", $params, $responseType);
    }
    public function team_news(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-team-news", $params, $responseType);
    }
    public function team_results(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-team-results", $params, $responseType);
    }
    public function team_squad(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-team-squad", $params, $responseType);
    }
    public function team_transfers(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-team-transfers", $params, $responseType);
    }
    public function top_search(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-top-search", $params, $responseType);
    }
    public function tournament_events(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-tournament-events", $params, $responseType);
    }
    public function tournament_seasons(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-tournament-seasons", $params, $responseType);
    }
    public function tournament_standings(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-tournament-standings", $params, $responseType);
    }
    public function tournament_standings_views(mixed ...$params): mixed
    {
        $responseType = $params['_response_type'] ?? $params['response_type'] ?? 'auto';
        unset($params['_response_type'], $params['response_type']);
        return $this->request("flashscore-tournament-standings-views", $params, $responseType);
    }

    private function buildUrl(array $operation, array $params): string
    {
        $known = array_column($operation['params'], 'name');
        $unknown = array_diff(array_keys($params), $known, ['response_type', '_response_type']);
        if ($unknown !== []) {
            throw new ClientException('Unknown parameters: ' . implode(', ', $unknown), null, $operation['id']);
        }
        $path = $operation['path'];
        foreach ($operation['params'] as $param) {
            if ($param['in'] !== 'path') {
                continue;
            }
            $name = $param['name'];
            if (!array_key_exists($name, $params) || $params[$name] === null) {
                throw new ClientException('Missing path parameter: ' . $name, null, $operation['id']);
            }
            $path = str_replace('{' . $name . '}', rawurlencode((string) $params[$name]), $path);
        }
        $pairs = [];
        foreach ($operation['queryParams'] as $param) {
            $name = $param['name'];
            $value = $params[$name] ?? ($param['default'] ?? null);
            if ($value === null) {
                if ($param['required'] ?? false) {
                    throw new ClientException('Missing query parameter: ' . $name, null, $operation['id']);
                }
                continue;
            }
            $enumValues = $param['enum'] ?? ($param['items']['enum'] ?? null);
            $values = is_array($value) ? $value : [$value];
            $invalidEnum = false;
            foreach ($values as $item) {
                if ($enumValues !== null && !in_array((string) $item, array_map('strval', $enumValues), true)) {
                    $invalidEnum = true;
                    break;
                }
            }
            if ($invalidEnum) {
                throw new ClientException('Invalid value for ' . $name, null, $operation['id']);
            }
            if (is_array($value)) {
                $format = $param['collectionFormat'] ?? 'csv';
                if ($format === 'multi') {
                    foreach ($value as $item) {
                        $pairs[] = [rawurlencode($name), rawurlencode($this->stringify($item))];
                    }
                } else {
                    $separator = ['csv' => ',', 'ssv' => ' ', 'tsv' => "\t", 'pipes' => '|'][$format] ?? ',';
                    $pairs[] = [rawurlencode($name), rawurlencode(implode($separator, array_map([$this, 'stringify'], $value)))];
                }
            } else {
                $pairs[] = [rawurlencode($name), rawurlencode($this->stringify($value))];
            }
        }
        $query = implode('&', array_map(static fn(array $pair): string => $pair[0] . '=' . $pair[1], $pairs));
        return $this->baseUrl . $path . ($query === '' ? '' : '?' . $query);
    }

    private function stringify(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }
        return (string) $value;
    }

    private function send(string $url, array $headers, string $operationId): array
    {
        if ($this->transport !== null) {
            $result = ($this->transport)($url, $headers, $this->timeout);
            return [(int) $result['status'], (string) ($result['content_type'] ?? ''), (string) ($result['body'] ?? '')];
        }
        $handle = curl_init($url);
        if ($handle === false) {
            throw new NetworkException('Could not initialize cURL', null, $operationId);
        }
        curl_setopt_array($handle, [
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT_MS => (int) ($this->timeout * 1000),
            CURLOPT_CONNECTTIMEOUT_MS => (int) ($this->timeout * 1000),
        ]);
        $body = curl_exec($handle);
        if ($body === false) {
            $message = curl_error($handle);
            curl_close($handle);
            throw new NetworkException('Crawlora request failed: ' . $message, null, $operationId);
        }
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $contentType = (string) curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
        curl_close($handle);
        return [$status, $contentType, (string) $body];
    }

    private function parseResponse(string $body, string $contentType, array $operation, array $params, string $responseType): mixed
    {
        if (!in_array($responseType, ['auto', 'json', 'text'], true)) {
            throw new ClientException('responseType must be auto, json, or text', null, $operation['id']);
        }
        $format = null;
        foreach ($operation['params'] as $param) {
            if ($param['name'] === 'format') {
                $format = $param;
                break;
            }
        }
        $textFormats = array_values(array_filter($format['enum'] ?? [], static fn($value): bool => !in_array(strtolower((string) $value), ['json', 'application/json'], true)));
        $rawFormat = isset($params['format']) && in_array((string) $params['format'], array_map('strval', $textFormats), true);
        $jsonFormat = isset($params['format']) && in_array(strtolower((string) $params['format']), ['json', 'application/json'], true);
        $isJson = $jsonFormat || stripos($contentType, 'json') !== false || $operation['produces'] === ['application/json'];
        if ($responseType === 'text' || $rawFormat || ($responseType === 'auto' && !$isJson)) {
            return $body;
        }
        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new CrawloraException('Invalid JSON response from Crawlora: ' . $exception->getMessage(), null, $operation['id'], $body, $exception);
        }
    }
}
