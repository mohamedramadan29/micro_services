<?php

namespace App\Services\SiteTests;

use RuntimeException;

/**
 * Only public web pages are checked (spec page 15): http(s), no user:password,
 * standard ports, and a host that resolves to public addresses only — never
 * the server itself or an internal network.
 */
class SiteUrlGuard
{
    /** Normalise and validate; returns the URL to check or throws with a message for the client. */
    public function check(string $input): string
    {
        $url = trim($input);
        if ($url !== '' && ! preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower(rtrim($parts['host'] ?? '', '.'));
        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || strlen($url) > 2000) {
            throw new RuntimeException('اكتب رابطًا صحيحًا يبدأ بـ https://');
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new RuntimeException('لا تضع اسم مستخدم أو كلمة مرور في الرابط؛ نفحص الصفحات العامة فقط.');
        }
        if (isset($parts['port']) && ! in_array((int) $parts['port'], [80, 443], true)) {
            throw new RuntimeException('نفحص المواقع العامة على المنافذ المعتادة فقط.');
        }
        if (! str_contains($host, '.') || str_ends_with($host, '.local') || str_ends_with($host, '.internal') || $host === 'localhost') {
            throw new RuntimeException('هذا ليس رابط موقع عام.');
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : $this->resolve($host);
        if (! $ips) {
            throw new RuntimeException('تعذّر الوصول إلى هذا النطاق. تأكد من كتابة الرابط بشكل صحيح.');
        }
        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new RuntimeException('هذا الرابط لا يشير إلى موقع عام.');
            }
        }

        return $scheme . '://' . $host . (isset($parts['port']) ? ':' . $parts['port'] : '') . ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }

    /** @return string[] IPv4 and IPv6 addresses of the host */
    protected function resolve(string $host): array
    {
        $records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];

        return array_values(array_filter(array_map(fn ($r) => $r['ip'] ?? $r['ipv6'] ?? null, $records)));
    }
}
