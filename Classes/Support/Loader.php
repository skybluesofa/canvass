<?php

namespace SkyBlueSofa\Canvass\Support;

use SkyBlueSofa\Canvass\Support\Wordpress;
use \Parsedown;

class Loader
{
    protected $pluginPath;
    protected $pluginURL;
    protected $pluginFile;
    protected $pluginVersion;
    protected static $instance;

    private function __construct($pluginFilename)
    {
        $this->pluginFile = $pluginFilename;
        $this->pluginPath = Wordpress::pluginDirPath($pluginFilename);
        $this->pluginURL = Wordpress::pluginsURL('/', $pluginFilename);
    }

    public static function instantiate($pluginFilename)
    {
        self::$instance = new Loader($pluginFilename);
    }

    public static function instance()
    {
        if (!self::$instance) {
            Wordpress::logError('Loader has not been instansiated', 'SBSCanvass');
        }
        return self::$instance;
    }

    public function pluginPath()
    {
        return $this->pluginPath;
    }

    public function pluginURL()
    {
        return $this->pluginURL;
    }

    public function pluginFile()
    {
        return $this->pluginFile;
    }

    public function pluginVersion()
    {
        if (is_null($this->pluginVersion)) {
            $this->pluginVersion = Wordpress::pluginData($this->pluginFile())['Version'];
        }
        return $this->pluginVersion;
    }

    public function loadClass($class)
    {
        $path = $this->pluginPath.'Classes/'.$class.'.php';

        require_once $path;
    }

    public function config($file)
    {
        return require $this->pluginPath.'Config/'.$file.'.php';
    }

    public function view($file, $variables = [], $variablesAsTokens = false)
    {
        if ($variablesAsTokens) {
            $tokenWrappers = $variablesAsTokens===true ? null : $this->getTokenWrappers($variablesAsTokens);
            return $this->tokenView($file, $variables, $tokenWrappers);
        }
        return $this->phpView($file, $variables);
    }

    private function phpView($file, $variables)
    {
        $file = (strpos($file, '.')===false) ? $file.'.php' : $file;
        ob_start();
        extract($variables);
        $viewData = $variables;

        require $this->pluginPath.'Views/'.$file;
        return ob_get_clean();
    }

    private function getTokenWrappers($tokenWrappers)
    {
        if (!is_array($tokenWrappers)) {
            return [$tokenWrappers, $tokenWrappers];
        }
        if (count($tokenWrappers)==0) {
            return null;
        }
        if (count($tokenWrappers)==1) {
            return [$tokenWrappers[0], $tokenWrappers[0]];
        }
        return [$tokenWrappers[0], $tokenWrappers[1]];
    }

    private function tokenView($file, $variables, $tokenWrappers = ['%%','%%'])
    {
        $tokens = [];
        foreach ($variables as $key => $value) {
            $tokenKey = $tokenWrappers[0].$key.$tokenWrappers[1];
            $tokens[$tokenKey] = (string) $value;
        }

        return str_replace(
            array_keys($tokens),
            array_values($tokens),
            $this->phpView($file, [])
        );
    }

    public function render($view, $data = [], $variablesAsTokens = false)
    {
        print $this->view($view, $data, $variablesAsTokens);
    }

    public function readme()
    {
        if (file_exists($this->pluginPath.'README.md')) {
            if (class_exists(Parsedown::class)) {
                ob_start();
                require $this->pluginPath.'README.md';
                $readme = ob_get_clean();

                $Parsedown = new \Parsedown();
                return $Parsedown->text($readme);
            }
        }
        return false;
    }

    public function diFunctions()
    {
        require_once(ABSPATH . '../wp-content/plugins/dealerinspire/classes/DIFunctions.php');
    }

    public function js($name, $path, $dependencies = array())
    {
        Wordpress::enqueueScript($name, $this->pluginURL.'assets/js/'.$path, $dependencies, null, true);
    }

    public function css($name, $path)
    {
        Wordpress::enqueueStyle($name, $this->pluginURL.'assets/css/'.$path);
    }

    public function javascriptUnitTests()
    {
        require_once(dirname(__FILE__).'/../../Tests/QUnit/JavascriptUnitTesting.php');
    }
}
