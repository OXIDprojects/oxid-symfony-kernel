<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidCommunity\SymfonyKernel\Composer\Utilities\CopyFileManager;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\CopyGlobFilteredFileManager as OxidCopyGlobFilteredFileManager;
use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\GlobMatcher;
use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Iteration\BlacklistFilterIterator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CopyGlobFilteredFileManager.
 *
 * Copies files/directories from source to destination which matches the criteria described in a glob filter.
 */
class CopyGlobFilteredFileManager extends OxidCopyGlobFilteredFileManager
{
    /**
     * Symlink files/directories from source to destination.
     *
     * @param string $sourcePath         Absolute path to file or directory.
     * @param string $destinationPath    Absolute path to file or directory.
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     *
     * @throws \InvalidArgumentException If given $sourcePath is not a string.
     * @throws \InvalidArgumentException If given $destinationPath is not a string.
     *
     * @return null
     */
    public static function symlink($sourcePath, $destinationPath, $globExpressionList = [])
    {
        if (!is_string($sourcePath)) {
            $message = "Given value \"$sourcePath\" is not a valid source path entry. ".
                "Valid entry must be an absolute path to an existing file or directory.";

            throw new \InvalidArgumentException($message);
        }

        if (!is_string($destinationPath)) {
            $message = "Given value \"$destinationPath\" is not a valid destination path entry. ".
                "Valid entry must be an absolute path to an existing directory.";

            throw new \InvalidArgumentException($message);
        }

        if (!file_exists($sourcePath)) {
            return;
        }

        if (is_dir($sourcePath)) {
            self::symlinkDirectory($sourcePath, $destinationPath, $globExpressionList);
        } else {
            self::symlinkFile($sourcePath, $destinationPath, $globExpressionList);
        }
    }

    /**
     * Returns relative path from an absolute path to a file.
     *
     * @param string $sourcePath Absolute path to a file.
     *
     * @return string
     */
    private static function getRelativePathForSingleFile($sourcePath)
    {
        return Path::makeRelative($sourcePath, Path::getDirectory($sourcePath));
    }

    /**
     * Return an iterator which iterates through a given directory tree in a one-dimensional fashion.
     *
     * Consider the following file/directory structure as an example:
     *
     *   * directory_a
     *     * file_a_a
     *   * directory_b
     *     * file_b_a
     *     * file_b_b
     *   * file_c
     *
     * RecursiveDirectoryIterator would iterate through:
     *   * directory_a [iterator]
     *   * directory_b [iterator]
     *   * file_c [SplFileInfo]
     *
     * In contrast current method would iterate through:
     *   * directory_a [SplFileInfo]
     *   * directory_a/file_a_a [SplFileInfo]
     *   * directory_b [SplFileInfo]
     *   * directory_b/file_b_a [SplFileInfo]
     *   * directory_b/file_b_b [SplFileInfo]
     *   * file_c [SplFileInfo]
     *
     * @param string $sourcePath Absolute path to directory.
     *
     * @return \Iterator
     */
    private static function getFlatFileListIterator($sourcePath)
    {
        $recursiveFileIterator = new \RecursiveDirectoryIterator($sourcePath, \FilesystemIterator::SKIP_DOTS);
        $flatFileListIterator = new \RecursiveIteratorIterator($recursiveFileIterator);

        return $flatFileListIterator;
    }

    /**
     * Symlink whole directory using given glob filters.
     *
     * @param string $sourcePath         Absolute path to directory.
     * @param string $destinationPath    Absolute path to directory.
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     */
    private static function symlinkDirectory($sourcePath, $destinationPath, $globExpressionList)
    {
        $filesystem = new Filesystem();

        $flatFileListIterator = self::getFlatFileListIterator($sourcePath);
        $filteredFileListIterator = new BlacklistFilterIterator(
            $flatFileListIterator,
            $sourcePath,
            $globExpressionList
        );

        $filesystem->symlink($filesystem->makePathRelative($sourcePath, dirname($destinationPath)), $destinationPath, $filteredFileListIterator, ["override" => true]);
    }

    /**
     * Symlink file using given glob filters.
     *
     * @param string $sourcePathOfFile   Absolute path to file.
     * @param string $destinationPath    Absolute path to directory.
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     */
    private static function symlinkFile($sourcePathOfFile, $destinationPath, $globExpressionList)
    {
        $filesystem = new Filesystem();

        $relativeSourcePath = self::getRelativePathForSingleFile($sourcePathOfFile);

        if (!GlobMatcher::matchAny($relativeSourcePath, $globExpressionList)) {
            $filesystem->symlink($sourcePathOfFile, $destinationPath, ["override" => true]);
        }
    }
}
