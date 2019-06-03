<?php
declare(strict_types=1);

namespace Roots\WordPressPackager;

use Composer\Package\PackageInterface;
use Cz\Git\GitRepository;
use Roots\WordPressPackager\ReleaseSources\WPDotOrgHTML;
use Roots\WordPressPackager\Util\Directory;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class Build
{
    public static function execute(string $gitRemote, string $wpDotOrgHtmlUrl): void
    {
        $fs = new Filesystem();
        $destination = Directory::mktemp($fs);
        $gitRepo = GitRepository::cloneRepository(
            $gitRemote,
            $destination
        );
        $target = new Target($gitRepo, $fs);

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
