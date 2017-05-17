<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */

namespace BinarySpanner\QuickForms\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Filesystem\Filesystem;
use BinarySpanner\QuickForms\DependencyInjection\PathBuilder;

/**
 * Class PathBuilderTest.old
 * @package BinarySpanner\QuickForms\Tests
 * @coversDefaultClass BinarySpanner\QuickForms\DependencyInjection\PathBuilder
 */
class PathBuilderTest extends TestCase
{
    /** @var  array  Root path components. */
    private $rootPaths;

    /** @var  array  Directory path components. */
    private $directories;

    /** @var  array  Filename path components. */
    private $fileNames;

    /** @var  array  Collated path components. */
    private $pathComponents;

    /** @var  array  Resolved paths to directories. */
    private $directoryPaths;

    /** @var  array  Resolved paths to files. */
    private $filePaths;

    /** @var  ObjectProphecy  Prophecy mock object. */
    private $filesystemMock;

    /** @var  Argument  A prophecy Argument instance. */
    private $prophecyArgument;

    /** @var  Argument  Callback argument to use with Prophecy. */
    private $prophecyCallback;

    public function setUp()
    {
        $this->rootPaths = ['/var/www/app', '/var/www/src', '/var/www'];
        $this->directories = ['Resources', 'AppBundle', 'Resources/forms'];
        $this->fileNames = ['file1.yml', 'file2.yml', 'file3.yml'];
        $this->pathComponents = [
            'rootPaths' => $this->rootPaths,
            'directoryPaths' => $this->directories,
            'fileNames' => $this->fileNames
        ];
        $this->directoryPaths = $this->createDirectoryPaths();
        $this->filePaths = $this->createFilePaths();
        $this->filesystemMock = $this->prophesize(Filesystem::class);
        $this->prophecyArgument = new Argument();
        $this->prophecyCallback = $this->prophecyArgument->that(
            [$this, 'ensureArgumentPassedToStubbedMethodIsFromDirectoriesArgument']
        );


        parent::setUp();
    }

    /**
     * Test that ::buildPaths returns the expected value when all arguments are provided.
     *
     * @covers ::<public>
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildPathsReturnsExpectedValueWhenAllArgumentsAreProvided()
    {
        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->filesystemMock->isAbsolutePath($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(false);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $paths = $pathBuilder->buildPaths($this->rootPaths, $this->directories, $this->fileNames);

        $failMessage = '::buildPaths does not return the expected value when all arguments are provided.';
        $this->assertSame($this->filePaths, $paths, $failMessage);
    }

    /**
     * Test that ::buildPaths returns the expected value when no arguments are provided.
     *
     * @covers ::<public>
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildPathsReturnsExpectedValueWhenNoArgumentsAreProvided()
    {
        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->filesystemMock->isAbsolutePath($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(false);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal(), $this->pathComponents);
        $paths = $pathBuilder->buildPaths();

        $failMessage = '::buildPaths does not return the expected value when no arguments are provided.';
        $this->assertSame($this->filePaths, $paths, $failMessage);
    }

    /**
     * Test that ::buildPaths only returns directory paths when no fileNames value is set.
     *
     * @covers ::__construct
     * @covers ::buildPaths
     * @covers ::buildDirectoryPaths
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildPathsOnlyReturnsDirectoryPathsWhenNoFileNamesValueIsSet()
    {
        $this->pathComponents['fileNames'] = null;

        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->filesystemMock->isAbsolutePath($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(false);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal(), $this->pathComponents);
        $paths = $pathBuilder->buildPaths();

        $failMessage = '::buildPaths does not only return directory paths when no filenames are set.';
        $this->assertSame($this->directoryPaths, $paths, $failMessage);
    }

    /**
     * Test that ::buildDirectoryPaths returns the expected value.
     *
     * @covers ::__construct
     * @covers ::buildDirectoryPaths
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildDirectoryPathsReturnsExpectedValue()
    {
        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->filesystemMock->isAbsolutePath($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(false);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $paths = $pathBuilder->buildDirectoryPaths($this->rootPaths, $this->directories);

        $failMessage = '::buildDirectoryPaths does not return the expected value.';
        $this->assertSame($this->directoryPaths, $paths, $failMessage);
    }

    /**
     * Ensure that ::buildDirectoryPaths does not output any duplicate paths.
     *
     * @covers ::__construct
     * @covers ::buildDirectoryPaths
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildDirectoryPathsDoesNotOutputDuplicatePaths()
    {
        $this->rootPaths[] = '/var/www/app';
        $this->directories[] = 'Resources';

        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->filesystemMock->isAbsolutePath($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(false);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $paths = $pathBuilder->buildDirectoryPaths($this->rootPaths, $this->directories);

        $failMessage = '::buildDirectoryPaths outputs duplicate paths.';
        $this->assertSame($this->directoryPaths, $paths, $failMessage);
    }

    /**
     * Test that ::buildFilePaths returns the expected value.
     *
     * @covers ::__construct
     * @covers ::buildFilePaths
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildFilePathsReturnsExpectedValue()
    {
        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(true);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $paths = $pathBuilder->buildFilePaths($this->directoryPaths, $this->fileNames);

        $failMessage = '::buildFilePaths does not return the expected value.';
        $this->assertSame($this->filePaths, $paths, $failMessage);
    }

    /**
     * Ensure that ::buildFilePaths does not output any duplicate paths.
     *
     * @covers ::__construct
     * @covers ::buildFilePaths
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildFilePathsDoesNotOutputDuplicatePaths()
    {
        $this->fileNames[] = 'file1.yml';

        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(true);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $paths = $pathBuilder->buildFilePaths($this->directoryPaths, $this->fileNames);

        $failMessage = '::buildFilePaths outputs duplicate paths.';
        $this->assertSame($this->filePaths, $paths, $failMessage);
    }

    /**
     * Ensure that ::buildDirectoryPaths handles absolute directory paths correctly.
     *
     * @covers ::__construct
     * @covers ::buildDirectoryPaths
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testAbsoluteDirectoryPathsAreProcessedCorrectly()
    {
        foreach ($this->directories as $key => $directory) {
            $this->directories[$key] = '/' . $directory;
        }

        $this->filesystemMock->exists($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->filesystemMock->isAbsolutePath($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(true);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $paths = $pathBuilder->buildDirectoryPaths($this->rootPaths, $this->directories);

        $failMessage = '::buildDirectoryPaths does not process absolute paths correctly.';
        $this->assertSame($this->directories, $paths, $failMessage);
    }

    /**
     * Ensure that excess slashes are removed from all paths and filenames.
     *
     * @covers ::__construct
     * @covers ::trimExcessSlashes
     * @covers ::buildDirectoryPaths
     * @covers ::buildFilePaths
     * @covers ::addValidPath
     */
    public function testExcessSlashesAreTrimmedFromAllPathsAndFileNames()
    {
        $this->rootPaths = ['///var/www///'];
        $this->directories = ['///Resources/forms///'];
        $this->fileNames = ['///validForm.yml////'];
        $expected = ['/var/www/Resources/forms/validForm.yml'];

        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->filesystemMock->isAbsolutePath($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(false);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $directoryPath = $pathBuilder->buildDirectoryPaths($this->rootPaths, $this->directories);
        $filePath = $pathBuilder->buildFilePaths($directoryPath, $this->fileNames);

        $failMessage = 'Excess slashes are not properly removed from paths and filenames.';
        $this->assertSame($expected, $filePath, $failMessage);
    }

    /**
     * Ensure ::buildPaths throws an exception if no rootPaths argument is available.
     *
     * @covers ::__construct
     * @covers ::buildPaths
     */
    public function testBuildPathsThrowsExceptionIfNoRootPathsArgumentIsAvailable()
    {
        $this->pathComponents = [
            'directoryPaths' => $this->directories,
            'fileNames' => $this->fileNames
        ];

        $exceptionMessage = 'No root paths have been set.';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal(), $this->pathComponents);
        $pathBuilder->buildPaths();
    }

    /**
     * Ensure ::buildPaths throws an exception if no directoryPaths  argument is available.
     *
     * @covers ::__construct
     * @covers ::buildPaths
     */
    public function testBuildPathsThrowsExceptionIfNoDirectoryPathsArgumentIsAvailable()
    {
        $this->pathComponents = [
            'rootPaths' => $this->rootPaths,
            'fileNames' => $this->fileNames
        ];

        $exceptionMessage = 'No directories have been set.';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal(), $this->pathComponents);
        $pathBuilder->buildPaths();
    }

    /**
     * Ensure ::buildDirectoryPaths throws an exception if a root directory is not found.
     *
     * @covers ::__construct
     * @covers ::buildDirectoryPaths
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildDirectoryPathsThrowsExceptionIfRootDirectoryNotFound()
    {
        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(false);

        $this->filesystemMock->isAbsolutePath($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(false);

        $exceptionMessage = sprintf('The root directory "%s" does not exist.', $this->rootPaths[0]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $pathBuilder->buildDirectoryPaths($this->rootPaths, $this->directories);
    }

    /**
     * Ensure ::buildDirectoryPaths throws an exception if a directory is not found.
     *
     * @covers ::__construct
     * @covers ::buildDirectoryPaths
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildDirectoryPathsThrowsExceptionIfDirectoryNotFound()
    {
        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->filesystemMock->isAbsolutePath($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->filesystemMock->exists($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(false);

        $exceptionMessage = sprintf(
            'The directory "%s" does not exist in any of the root directories.', $this->directories[0]
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $pathBuilder->buildDirectoryPaths($this->rootPaths, $this->directories);
    }

    /**
     * Ensure ::buildDirectoryPaths throws an exception if no directory is found at an absolute path.
     *
     * @covers ::__construct
     * @covers ::buildDirectoryPaths
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildDirectoryPathsThrowsExceptionIfAbsolutePathNotFound()
    {
        $this->filesystemMock->isAbsolutePath($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->filesystemMock->exists($this->prophecyCallback)
            ->shouldBeCalled()
            ->willReturn(false);

        $exceptionMessage = sprintf('The directory "%s" does not exist.', $this->directories[0]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $pathBuilder->buildDirectoryPaths($this->rootPaths, $this->directories);
    }

    /**
     * Ensure ::buildFilePaths throws an exception if a file is not found.
     *
     * @covers ::__construct
     * @covers ::buildFilePaths
     * @covers ::addValidPath
     * @covers ::trimExcessSlashes
     */
    public function testBuildFilePathsThrowsExceptionIfFileNotFound()
    {
        $this->filesystemMock->exists($this->prophecyArgument->containingString('/var/www'))
            ->shouldBeCalled()
            ->willReturn(false);

        $exceptionMessage = "No file named \"%s\" found in the following directories:\n";
        foreach ($this->directoryPaths as $directoryPath) {
            $exceptionMessage .= "\n" . '"' . $directoryPath . '"';
        }
        $exceptionMessage = sprintf($exceptionMessage, $this->fileNames[0]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $pathBuilder = new PathBuilder($this->filesystemMock->reveal());
        $pathBuilder->buildFilePaths($this->directoryPaths, $this->fileNames);
    }

    /**
     * Callback method to use with Prophecy to check that the arguments passed to stubbed methods are
     * from the directories argument.
     *
     * @param   string  $arg    The argument passed to the stubbed method.
     * @return  bool    Returns true if argument is in the directories argument.
     */
    public function ensureArgumentPassedToStubbedMethodIsFromDirectoriesArgument(string $arg)
    {
        foreach ($this->directories as $dirPath) {
            if (strpos($arg, $dirPath) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create an array of directory paths to test against.
     * @return  array   An array of directory paths.
     */
    private function createDirectoryPaths()
    {
        $paths = [];

        foreach ($this->directories as $directory) {
            foreach ($this->rootPaths as $rootPath) {
                $paths[] = $rootPath . DIRECTORY_SEPARATOR . $directory;
            }
        }

        return $paths;
    }

    /**
     * Create an array of file paths to test against.
     * @return  array   An array of directory paths.
     */
    private function createFilePaths()
    {
        $paths = [];

        foreach ($this->fileNames as $fileName) {
            foreach ($this->directoryPaths as $directoryPath) {
                $paths[] = $directoryPath . DIRECTORY_SEPARATOR . $fileName;
            }
        }

        return $paths;
    }
}
