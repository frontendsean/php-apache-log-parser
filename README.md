You can run this directly with the PHP CLI:
`php -f parse_logs.php apache_access_logs_to_parse.txt`

****

Outputs:
* Number of total requests
* Number of successful requests
* Number of error requests
* List requested URLs (most common first)
* List referrers (most common first)
* List user agents (most common first)
