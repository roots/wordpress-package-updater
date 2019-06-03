<?php
declare(strict_types=1);

namespace Roots\WordPressPackager;

use Composer\Package\PackageInterface;
use Cz\Git\IGit;
use Symfony\Component\Filesystem\Filesystem;

class Target
{
    /** @var IGit */
    protected $gitRepo;

    /** @var Filesystem */
    protected $filesystem;

    /** @var string[] */
    protected $gitTags;

    public function __construct(IGit $gitRepo, Filesystem $filesystem)
    {
        $this->gitRepo = $gitRepo;
        $this->filesystem = $filesystem;
    }

    protected function getGitTags(): array
    {
        if ($this->gitTags === null) {
            $this->gitRepo->fetch('origin');
            $this->gitTags = (array) $this->gitRepo->getTags();
        }

        return $this->gitTags;
    }

    protected function hasGitTag(string $tag): bool
    {
        return in_array($tag, $this->getGitTags(), true);
    }

    public function add(PackageInterface $package): void
    {
        $version = $package->getPrettyVersion();
        if ($this->hasGitTag($version)) {
            return;
        }

        $this->gitRepo->execute(['checkout', '--orphan', $version]);

        $temporaryDirectoryPath = $this->gitRepo->getRepositoryPath();
        $composerJsonContent = json_encode(
            $package,
            JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
        );
        $this->filesystem->dumpFile(
            "${temporaryDirectoryPath}/composer.json",
            $composerJsonContent
        );

        $this->gitRepo->addFile('composer.json');
        $this->gitRepo->commit("Version bump ${version}");
        $this->gitRepo->createTag($version, [
            '--annotate',
            '--message' => "Version bump ${version}",
        ]);
        $this->gitRepo->push('origin', ["refs/heads/${version}", '--follow-tags']);

        $this->gitTags[] = $version;
    }
}
