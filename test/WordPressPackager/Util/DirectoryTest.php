<?php
declare(strict_types=1);

namespace Roots\WordPressPackager\Util;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class DirectoryTest extends TestCase
{
    public function testMktemp()
    {
        $expectedTemporaryDirectoryPathPrefix = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'wordpress-packager';

        $filesystem = $this->getMockBuilder(Filesystem::class)
                           ->setMethods(['mkdir'])
                           ->getMock();

        $filesystem->expects($this->once())
                   ->method('mkdir')
                   ->with(
                       $this->stringStartsWith($expectedTemporaryDirectoryPathPrefix)
                   );

        Directory::mktemp($filesystem);
    }
}
