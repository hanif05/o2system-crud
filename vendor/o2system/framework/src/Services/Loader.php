<?php
/**
 * This file is part of the O2System Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */

// ------------------------------------------------------------------------

namespace O2System\Framework\Services;

// ------------------------------------------------------------------------

use O2System\Framework\Containers\Modules;
use O2System\Psr\Loader\AutoloadInterface;

/**
 * O2System Loader
 *
 * Class and files loader based on PSR-4 Autoloader
 *
 * @see     http://www.php-fig.org/psr/psr-4/
 *
 * @package O2System\Kernel
 */
class Loader implements AutoloadInterface
{
    /**
     * Loader::$publicDirs
     *
     * Loader Public Directories.
     *
     * @var array
     */
    protected $publicDirs = [
        'default' => PATH_PUBLIC,
    ];

    /**
     * Loader::$resourcesDirs
     *
     * Loader Resources Directories.
     *
     * @var array
     */
    protected $resourcesDirs = [
        'default' => PATH_RESOURCES,
    ];

    /**
     * Loader::$namespaceDirs
     *
     * Loader Namespaces Directories.
     *
     * @var array
     */
    protected $namespaceDirs = [];

    /**
     * Loader::$namespaceDirsMap
     *
     * Loader Namespaces Directories Maps.
     *
     * @var array
     */
    protected $namespaceDirsMap = [];

    /**
     * Loader::$loadedHelpers
     *
     * Loader Loaded Helpers Registry.
     *
     * @var array
     */
    protected $loadedHelpers = [];

    // ------------------------------------------------------------------------

    /**
     * Loader::__construct
     */
    public function __construct()
    {
        $this->register();

        // Add Kernel Namespace
        $this->addNamespace('O2System\Kernel', PATH_KERNEL);

        if (class_exists('O2System', false)) {
            $this->addNamespace('O2System\Framework', PATH_FRAMEWORK);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::register
     *
     * Register loader with SPL autoloader stack.
     *
     * @return void
     */
    public function register()
    {
        // Prepend the PSR4 autoloader for maximum performance.
        spl_autoload_register([&$this, 'loadClass'], true, true);

        // Append the custom modular PSR4 autoloader.
        spl_autoload_register([&$this, 'loadModuleClass'], true, false);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::addNamespace
     *
     * Adds a base directory for a namespace prefix.
     *
     * @param string $namespace     The namespace prefix.
     * @param string $baseDirectory A base directory for class files in the
     *                              namespace.
     * @param bool   $prepend       If true, prepend the base directory to the stack
     *                              instead of appending it; this causes it to be searched first rather
     *                              than last.
     *
     * @return void
     */
    public function addNamespace($namespace, $baseDirectory, $prepend = false)
    {
        // normalize namespace prefix
        $namespace = trim($namespace, '\\') . '\\';

        if (empty($namespace) OR $namespace === '\\') {
            return;
        }

        // normalize the base directory with a trailing separator
        $baseDirectory = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $baseDirectory);
        $baseDirectory = rtrim($baseDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (is_dir($baseDirectory)) {
            // initialize the namespace prefix array
            if (isset($this->namespaceDirs[ $namespace ]) === false) {
                $this->namespaceDirs[ $namespace ] = [];
            }

            // retain the base directory for the namespace prefix
            if ( ! in_array($baseDirectory, $this->namespaceDirs[ $namespace ])) {
                if ($prepend) {
                    array_unshift($this->namespaceDirs[ $namespace ], $baseDirectory);
                } else {
                    array_push($this->namespaceDirs[ $namespace ], $baseDirectory);
                }
            }

            $this->namespaceDirsMap[ $baseDirectory ] = $namespace;

            // Register Namespace Language
            language()->addFilePath($baseDirectory);

            // Register Namespace Output FilePath
            output()->addFilePath($baseDirectory);

            // Register Namespace Views FilePath
            if (services()->has('view')) {
                view()->addFilePath($baseDirectory);
            }

            // Autoload Composer
            if (is_file($baseDirectory . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
                require($baseDirectory . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::addPublicDir
     *
     * Adds a public directory for assets.
     *
     * @param string $publicDir
     * @param null $offset
     */
    public function addPublicDir($publicDir, $offset = null)
    {
        // normalize the public directory with a trailing separator
        $publicDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $publicDir);
        $publicDir = PATH_PUBLIC . str_replace(PATH_PUBLIC, '', $publicDir);
        $publicDir = rtrim($publicDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (is_dir($publicDir) and ! in_array($publicDir, $this->publicDirs)) {
            if (isset($offset)) {
                $this->publicDirs[ $offset ] = $publicDir;
            } else {
                $this->publicDirs[ rtrim(str_replace(PATH_PUBLIC, '', $publicDir), '/') ] = $publicDir;
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::addResourceDir
     *
     * Adds a resource directory for assets.
     *
     * @param $resourcesDir
     * @param null $offset
     */
    public function addResourceDir($resourcesDir, $offset = null)
    {
        // normalize the public directory with a trailing separator
        $resourcesDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $resourcesDir);
        $resourcesDir = PATH_RESOURCES . str_replace(PATH_RESOURCES, '', $resourcesDir);
        $resourcesDir = rtrim($resourcesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (is_dir($resourcesDir) and ! in_array($resourcesDir, $this->resourcesDirs)) {
            if (isset($offset)) {
                $this->resourcesDirs[ $offset ] = $resourcesDir;
            } else {
                $this->resourcesDirs[ rtrim(str_replace(PATH_RESOURCES, '', $resourcesDir), '/') ] = $resourcesDir;
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::getPublicDirs
     *
     * Gets all public directories
     *
     * @param bool $reverse
     *
     * @return array
     */
    public function getPublicDirs($reverse = false)
    {
        return $reverse === true ? array_reverse($this->publicDirs) : $this->publicDirs;
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::getResourcesDirs
     *
     * Gets all resources directories
     *
     * @param bool $reverse
     *
     * @return array
     */
    public function getResourcesDirs($reverse = false)
    {
        return $reverse === true ? array_reverse($this->resourcesDirs) : $this->resourcesDirs;
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::getDirNamespace
     *
     * Get Namespace
     *
     * Get PSR4 Directory base on directory path
     *
     * @param string $dir
     *
     * @return string|bool
     */
    public function getDirNamespace($dir)
    {
        $dir = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $dir);

        $dir = realpath($dir);
        $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (array_key_exists($dir, $this->namespaceDirsMap)) {
            return $this->namespaceDirsMap[ $dir ];
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::getClassNamespaceDirs
     *
     * Get Namespace Class Directory
     *
     * @param string $className
     *
     * @return string|null
     */
    public function getClassNamespaceDirs($className)
    {
        $className = ltrim($className, '\\');
        $namespace = null;

        if ($lastNsPos = strripos($className, '\\')) {
            return $this->getNamespaceDirs(substr($className, 0, $lastNsPos));
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::getNamespaceDirs
     *
     * Get Namespace Directory
     *
     * @param string $namespace
     *
     * @return string
     */
    public function getNamespaceDirs($namespace)
    {
        $namespace = trim($namespace, '\\') . '\\';

        if (array_key_exists($namespace, $this->namespaceDirs)) {
            return $this->namespaceDirs[ $namespace ];
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::loadModuleClass
     *
     * @param string $class
     *
     * @return mixed
     */
    public function loadModuleClass($class)
    {
        if (modules() instanceof Modules) {
            if (false !== ($modules = modules()->getRegistry())) {
                foreach ($modules as $module) {
                    if ($module instanceof Module) {
                        if (empty($this->namespaceDirs[ $module->getNamespace() ])) {
                            $this->addNamespace($module->getNamespace(), $module->getRealPath());
                        }
                    }
                }
            }
        }

        return $this->loadClass($class);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::loadClass
     *
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     *
     * @return mixed The mapped file name on success, or boolean false on
     * failure.
     */
    public function loadClass($class)
    {
        // the current namespace prefix
        $namespace = $class;

        // work backwards through the namespace names of the fully-qualified
        // class name to find a mapped file name
        while (false !== $pos = strrpos($namespace, '\\')) {
            // retain the trailing namespace separator in the prefix
            $namespace = substr($class, 0, $pos + 1);

            // the rest is the relative class name
            $relativeClass = substr($class, $pos + 1);

            // try to load a mapped file for the prefix and relative class
            $mappedFile = $this->loadMappedFile($namespace, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }

            // remove the trailing namespace separator for the next iteration
            // of strrpos()
            $namespace = rtrim($namespace, '\\');
        }

        // never found a mapped file
        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::loadMappedFile
     *
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $namespace     The namespace prefix.
     * @param string $relativeClass The relative class name.
     *
     * @return mixed Boolean false if no mapped file can be loaded, or the
     * name of the mapped file that was loaded.
     */
    public function loadMappedFile($namespace, $relativeClass)
    {
        // are there any base directories for this namespace prefix?
        if (isset($this->namespaceDirs[ $namespace ]) === false) {
            return false;
        }

        // look through base directories for this namespace prefix
        foreach ($this->namespaceDirs[ $namespace ] as $namespaceDirectory) {

            // replace the namespace prefix with the base directory,
            // replace namespace separators with directory separators
            // in the relative class name, append with .php
            $file = $namespaceDirectory
                . str_replace('\\', '/', $relativeClass)
                . '.php';

            // if the mapped file exists, require it
            if ($this->requireFile($file)) {
                // yes, we're done
                return $file;
            }
        }

        // never found it
        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::requireFile
     *
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     *
     * @return bool True if the file exists, false if not.
     */
    public function requireFile($file)
    {
        if (is_file($file)) {
            require_once $file;

            return true;
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::config
     *
     * @param string $config
     *
     * @return mixed
     */
    public function config($config)
    {
        return config()->loadFile($config, true);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::helper
     *
     * @param string $helper
     */
    public function helper($helper)
    {
        $this->loadHelper($helper);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::loadHelper
     *
     * @param string $helper
     */
    public function loadHelper($helper)
    {
        if (array_key_exists($helper, $this->loadedHelpers)) {

            return;
        }

        if ($this->requireFile($helper)) {
            $this->loadedHelpers[ pathinfo($helper, PATHINFO_FILENAME) ][] = $helper;

            return;
        }

        $helperDirectories = [
            PATH_KERNEL . 'Helpers' . DIRECTORY_SEPARATOR,
            PATH_FRAMEWORK . 'Helpers' . DIRECTORY_SEPARATOR,
            PATH_APP . 'Helpers' . DIRECTORY_SEPARATOR,
        ];

        if (method_exists(modules(), 'current')) {
            array_push($helperDirectories, modules()->top()->getPath() . 'Helpers' . DIRECTORY_SEPARATOR);
        }

        if ( ! array_key_exists($helper, $this->loadedHelpers)) {
            $this->loadedHelpers[ $helper ] = [];
        }

        foreach ($helperDirectories as $helperDirectory) {

            $helperFilePath = $helperDirectory . studlycase($helper) . '.php';

            if (in_array($helperFilePath, $this->loadedHelpers[ $helper ])) {
                continue;
            } elseif ($this->requireFile($helperFilePath)) {
                $this->loadedHelpers[ $helper ][] = $helperFilePath;
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::helpers
     *
     * @param array $helpers
     */
    public function helpers(array $helpers)
    {
        $this->loadHelpers($helpers);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::loadHelpers
     *
     * @param array $helpers
     */
    public function loadHelpers(array $helpers)
    {
        foreach ($helpers as $helper) {
            $this->loadHelper($helper);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::library
     *
     * @param string|Object $class
     * @param string|null   $name
     */
    public function library($class, $name = null)
    {
        $this->service($class, $name);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::service
     *
     * @param string|Object $class
     * @param string|null   $name
     */
    public function service($class, $name = null)
    {
        services()->load($class, $name);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::libraries
     *
     * @param array $classes
     */
    public function libraries(array $classes)
    {
        $this->services($classes);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::services
     *
     * @param array $classes
     */
    public function services(array $classes)
    {
        foreach ($classes as $name => $class) {
            if (is_numeric($name)) {
                services()->load($class);
            } elseif (is_string($name)) {
                services()->load($class, $name);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::model
     *
     * @param string $model
     */
    public function model($model)
    {
        models()->load($model);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::models
     *
     * @param array $models
     */
    public function models(array $models)
    {
        foreach ($models as $model) {
            models()->load($model);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::view
     *
     * @param string  $file
     * @param array   $vars
     * @param bool    $return
     *
     * @return \O2System\Framework\Http\View|string
     */
    public function view($file, array $vars = [], $return = false)
    {
        return view($file, $vars, $return);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::page
     *
     * @param string $file
     * @param array  $vars
     * @param bool   $return
     *
     * @return bool|string
     */
    public function page($file, array $vars = [], $return = false)
    {
        return view()->page($file, $vars, $return);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::model
     *
     * @param string $file
     * @param array  $vars
     */
    public function modal($file, array $vars = [])
    {
        return view()->modal($file, $vars);
    }

    // ------------------------------------------------------------------------

    /**
     * Loader::language
     *
     * @param string $file
     *
     * @return \O2System\Framework\Services\Language|\O2System\Kernel\Services\Language
     */
    public function language($file)
    {
        return language()->loadFile($file);
    }
}