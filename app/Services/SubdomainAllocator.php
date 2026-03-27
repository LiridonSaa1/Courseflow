<?php

namespace App\Services;

use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class SubdomainAllocator
{
    private const MAX_LENGTH = 48;

    /** @var list<string> */
    private const RESERVED = [
        'www', 'api', 'admin', 'app', 'mail', 'ftp', 'cdn', 'staging', 'dashboard',
        'smtp', 'imap', 'pop', 'ns1', 'ns2', 'test', 'dev',
    ];

    /**
     * @param  string  $courseName  Primary source when $override is empty.
     * @param  string|null  $override  Optional custom subdomain (slugified); must stay unique.
     */
    public function allocate(string $courseName, ?string $override = null): string
    {
        $preferred = $override !== null && trim($override) !== ''
            ? $this->finalizeSlug(Str::slug(trim($override)))
            : $this->finalizeSlug(Str::slug($this->normalizeCourseNameForSubdomain($courseName)));

        if ($preferred === '') {
            $preferred = 'school';
        }

        if ($this->isReserved($preferred)) {
            $preferred = $preferred.'-workspace';
        }

        $preferred = $this->truncate($preferred, self::MAX_LENGTH);

        $candidate = $preferred;
        $i = 2;

        while ($this->exists($candidate)) {
            $suffix = '-'.$i;
            $stem = $this->truncate($preferred, self::MAX_LENGTH - strlen($suffix));
            if ($stem === '') {
                $stem = 'school';
            }
            $candidate = $stem.$suffix;
            $i++;
        }

        return $candidate;
    }

    /**
     * Make course titles safe as a single subdomain label (e.g. avoid "britanikacourseflowcom"
     * from "BritanikaCourseflow.com").
     */
    private function normalizeCourseNameForSubdomain(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        if (str_contains($name, '://')) {
            $host = parse_url($name, PHP_URL_HOST);
            if (is_string($host) && $host !== '') {
                $name = explode('.', $host)[0] ?? $host;
            }
        } elseif (! str_contains($name, ' ') && str_contains($name, '.')) {
            // Dotted host or "Brand.tld" without spaces — use first label only
            $name = explode('.', $name)[0] ?? $name;
        }

        // Studly/camelCase → spaced words (BritanikaCourseflow → Britanika Courseflow)
        $name = (string) preg_replace('/([a-z\d])([A-Z])/', '$1 $2', $name);
        $name = (string) preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1 $2', $name);

        // Strip product / URL fragments that should not appear in the tenant subdomain
        $name = (string) preg_replace('/\bcourseflow\b/i', '', $name);
        $name = (string) preg_replace('/\.(?:com|org|net|io|co|dev|app)\b/i', '', $name);
        $name = (string) preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }

    /**
     * Remove hyphenated noise left after slugging (.com → "-com", "courseflow" token).
     */
    private function finalizeSlug(string $slug): string
    {
        $slug = trim($slug);
        if ($slug === '') {
            return '';
        }

        $slug = (string) preg_replace('/-courseflow(?=-|$)/', '', $slug);
        $slug = (string) preg_replace('/-com$/', '', $slug);

        return trim($slug, '-');
    }

    private function truncate(string $slug, int $maxLen): string
    {
        if ($maxLen < 1) {
            return '';
        }

        if (strlen($slug) <= $maxLen) {
            return rtrim($slug, '-');
        }

        return rtrim(substr($slug, 0, $maxLen), '-');
    }

    private function exists(string $domain): bool
    {
        return Domain::query()->where('domain', $domain)->exists();
    }

    private function isReserved(string $sub): bool
    {
        return in_array(strtolower($sub), self::RESERVED, true);
    }
}
