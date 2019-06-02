<?php
declare(strict_types=1);

namespace Roots\WordPressPackager;

use Composer\Package\PackageInterface;
use Roots\WordPressPackager\ReleaseSources\WPDotOrgHTML;
use RuntimeException;

class Build
{
    public static function execute(string $gitRemote, string $wpDotOrgHtmlUrl): void
    {
        $target = Target::make($gitRemote);

        array_map(function (PackageInterface $package) use ($target): void {
            $target->add($package);
        }, static::getPackages($wpDotOrgHtmlUrl));
    }

    protected static function getPackages(string $wpDotOrgHtmlUrl): array
    {
        $html = file_get_contents($wpDotOrgHtmlUrl);
        if (!is_string($html)) {
            throw new RuntimeException("Failed to download HTML from $wpDotOrgHtmlUrl");
        }
        $wpDotOrgHtmlUrl = new WPDotOrgHTML($html);

        return $wpDotOrgHtmlUrl->getRepo()
                               ->getPackages();
    }
}
