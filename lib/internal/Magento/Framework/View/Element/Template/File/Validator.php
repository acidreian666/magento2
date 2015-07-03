<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Template\File;

use \Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Validator
 * @package Magento\Framework\View\Element\Template\File
 */
class Validator
{
    /**
     * Config path to 'Allow Symlinks' template settings
     */
    const XML_PATH_TEMPLATE_ALLOW_SYMLINK = 'dev/template/allow_symlink';

    /**
     * Template files map
     *
     * @var []
     */
    protected $_templatesValidationResults = [];

    /**
     * View filesystem
     *
     * @var \Magento\Framework\FileSystem
     */
    protected $_filesystem;

    /**
     * Allow symlinks flag
     *
     * @var bool
     */
    protected $_isAllowSymlinks = false;

    /**
     * Root directory
     *
     * @var bool
     */
    protected $directory = null;

    /**
     * Themes directory
     *
     * @var string
     */
    protected $_themesDir;

    /**
     * Application directory
     *
     * @var string
     */
    protected $_appDir;

    /**
     * Compiled templates directory
     *
     * @var string
     */
    protected $_compiledDir;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param string|null $scope
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        $scope = null
    ) {
        $this->_filesystem = $filesystem;
        $this->_isAllowSymlinks = $scopeConfigInterface->getValue(self::XML_PATH_TEMPLATE_ALLOW_SYMLINK, $scope);
        $this->_themesDir = $this->_filesystem->getDirectoryRead(DirectoryList::THEMES)->getAbsolutePath();
        $this->_appDir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath();
        $this->_compiledDir = $this->_filesystem->getDirectoryRead(DirectoryList::TEMPLATE_MINIFICATION_DIR)
            ->getAbsolutePath();
    }

    /**
     * Checks whether the provided file can be rendered.
     *
     * Available directories which are allowed to be rendered
     * (the template file should be located under these directories):
     *  - app
     *  - design
     *
     * @param string $filename
     * @return bool
     */
    public function isValid($filename)
    {
        $filename = str_replace('\\', '/', $filename);
        if (!isset($this->_templatesValidationResults[$filename])) {
            $this->_templatesValidationResults[$filename] =
                ($this->isPathInDirectory($filename, $this->_compiledDir)
                || $this->isPathInDirectory($filename, $this->_appDir)
                || $this->isPathInDirectory($filename, $this->_themesDir)
                || $this->_isAllowSymlinks)
                && $this->getRootDirectory()->isFile($this->getRootDirectory()->getRelativePath($filename));
        }
        return $this->_templatesValidationResults[$filename];
    }

    /**
     * Checks whether path related to the directory
     *
     * @param string $path
     * @param string $directory
     * @return bool
     */
    protected function isPathInDirectory($path, $directory)
    {
        return 0 === strpos($path, $directory);
    }

    /**
     * Instantiates filesystem directory
     *
     * @return \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected function getRootDirectory()
    {
        if (null === $this->directory) {
            $this->directory = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT);
        }
        return $this->directory;
    }
}
