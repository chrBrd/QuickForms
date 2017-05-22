<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */
namespace BinarySpanner\QuickForms\DependencyInjection;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Builds paths from arrays of path components.
 * @package BinarySpanner\QuickForms\PathFinder
 */
class PathBuilder
{
    /** @var  Filesystem  An instance of Symfony's Filesystem component.  */
    protected $filesystem;

    /** @var  array  The components to be used to build the paths.  */
    protected $pathComponents;

    /**
     * PathBuilder constructor.
     * @param Filesystem $filesystem        An instance of Symfony's Filesystem component.
     * @param array      $pathComponents    The components to be used to build the paths.
     */
    public function __construct(Filesystem $filesystem, array $pathComponents = null)
    {
        $this->filesystem = $filesystem;
        $this->pathComponents = $pathComponents;
    }

    /**
     * Build an array of resolved paths to files and directories.
     * @param   array   $rootPaths          The paths of the root directories to build from.
     * @param   array   $directoryPaths     The paths of directories to search for in the root directories.
     * @param   array   $fileNames          File names to search for in the resolved directories.
     * @return  array   An array of resolved paths to files or directories.
     */
    public function buildPaths(array $rootPaths = null, array $directoryPaths = null, array $fileNames = null) : array
    {
        // Check there are both rootPaths and directoryPaths to build with.
        if (!$rootPaths) {
            if (!isset($this->pathComponents['rootPaths']) && empty($this->pathComponents['rootPaths'])) {
                throw new \InvalidArgumentException('No root paths have been set.');
            }

            $rootPaths = $this->pathComponents['rootPaths'];
        }

        if (!$directoryPaths) {
            if (!isset($this->pathComponents['directoryPaths']) && empty($this->pathComponents['directoryPaths'])) {
                throw new \InvalidArgumentException('No directories have been set.');
            }

            $directoryPaths = $this->pathComponents['directoryPaths'];
        }

        // TODO File names aren't really optional as a default is set. Possibly better to accept a wildcard?
        // File names are optional.
        if (!$fileNames) {
            $fileNames = $this->pathComponents['fileNames'];
        }

        // Build resolved directory paths.
        $paths = $this->buildDirectoryPaths($rootPaths, $directoryPaths);

        // Resolve paths to files if file names have been specified.
        if ($fileNames) {
            $paths = $this->buildFilePaths($paths, $fileNames);
        }

        return $paths;
    }

    /**
     * Build an array of resolved paths to directories.
     * @param   array   $rootPaths          The paths of the root directories to build from.
     * @param   array   $directoryPaths     The paths of directories to search for in the root directories.
     * @return  array   An array of resolved paths to directories.
     */
    public function buildDirectoryPaths(array $rootPaths = null, array $directoryPaths = null) : array
    {
        $paths = [];

        foreach ($directoryPaths as $directoryPath) {
            switch ($this->filesystem->isAbsolutePath($directoryPath)) {
                case true:
                    $fullPath = DIRECTORY_SEPARATOR . $this->trimExcessSlashes($directoryPath);
                    $paths = $this->addValidPath($fullPath, $paths);
                    if (empty($paths)) {
                        $message = sprintf('The directory "%s" does not exist.', $directoryPath);
                        throw new \InvalidArgumentException($message);
                    }
                    break;
                default:
                    foreach ($rootPaths as $rootPath) {
                        // Prepare and validate the root path.
                        $rootPath = DIRECTORY_SEPARATOR . $this->trimExcessSlashes($rootPath);
                        if (!$this->filesystem->exists($rootPath)) {
                            $message = sprintf('The root directory "%s" does not exist.', $rootPath);
                            throw new \InvalidArgumentException($message);
                        }

                        $fullPath = $rootPath . DIRECTORY_SEPARATOR . $this->trimExcessSlashes($directoryPath);
                        $paths = $this->addValidPath($fullPath, $paths);
                    }
                    break;
            }

            if (empty($paths)) {
                $message = sprintf(
                    'The directory "%s" does not exist in any of the root directories.',
                    $directoryPath
                );
                throw new \InvalidArgumentException($message);
            }
        }

        return $paths;
    }

    /**
     * Build an array of resolved paths to files.
     * @param   array   $directoryPaths     The paths of directories to search for in the root directories.
     * @param   array   $fileNames          File names to search for in the resolved directories.
     * @return  array   An array of resolved paths to files.
     */
    public function buildFilePaths(array $directoryPaths, array $fileNames = null) : array
    {
        $paths = [];

        foreach ($fileNames as $fileName) {
            foreach ($directoryPaths as $directoryPath) {
                $fileName = $this->trimExcessSlashes($fileName);
                $fullPath = $directoryPath . DIRECTORY_SEPARATOR . $fileName;

                $paths = $this->addValidPath($fullPath, $paths);
            }

            if (empty($paths)) {
                $msg = "No file named \"%s\" found in the following directories:\n";
                foreach ($directoryPaths as $dir) {
                    $msg .= "\n" . '"' . $dir . '"';
                }
                throw new \InvalidArgumentException(sprintf($msg, $fileName));
            }
        }

        return $paths;
    }

    /**
     * Check a path exists and add it to the paths array if it does.
     * @param   string  $path       The path string to search for.
     * @param   array   $paths      Array containing previously validated paths.
     * @return  array   An array containing valid paths.
     */
    protected function addValidPath($path, $paths)
    {

        if (!$this->filesystem->exists($path)) {
            return [];
        }

        if (!in_array($path, $paths)) {
            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * Trim excess slashes from path strings.
     * @param   string  $path       The path string to trim.
     * @return  string  The trimmed paths string.
     */
    protected function trimExcessSlashes(string $path)
    {
        $path = ltrim($path, '/');
        $path = rtrim($path, '/');
        return $path;
    }
}
