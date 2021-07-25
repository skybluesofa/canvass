<?php
namespace SkyBlueSofa\Canvass\Support\Contract\Admin;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Support\Loader;
use SkyBlueSofa\Canvass\Support\Wordpress;
use SkyBlueSofa\Canvass\Support\PluginErrors;

/**
* Functionality that is used from within the dashboard
*/
class SettingsPage extends BaseObject
{
    /**
    * @var string
    */
    protected $pageTitle;
    /**
    * @var string
    */
    protected $menuTitle;
    /**
    * @var string
    */
    protected $capabilities = 'manage_options';
    /**
    * @var string
    */
    protected $pageSlug;
    /**
    * @var string
    */
    protected $actionLinkName = 'Settings';

    /**
    * @var array
    */
    protected $tabs = [
        'setup' => ['title'=>'Setup', 'active'=>true, 'url'=>'#'],
    ];

    /**
    * @var bool
    */
    protected $showReadmeTab = true;

    /**
    * Setup adminstrative functionality
    * @param Loader $loader
    * @return void
    */
    public function __construct()
    {
        parent::__construct();

        $this->setPageSlug();

        Wordpress::addAction('admin_init', [$this, 'save']);
        Wordpress::addFilter('plugin_action_links_' . Wordpress::pluginBasename($this->loader->pluginFile()), [$this, 'addActionLinksToPluginPage']);
        Wordpress::addAction('admin_menu', [$this, 'setupAdminMenus'], 11);
        Wordpress::addAction('admin_notices', function () {
            PluginErrors::showFor($this);
        });
    }

    public function providedPlugins_getTabData()
    {
        $this->updatePluginStatus();
        return [
            'hideSubmitButton' => true,
            'pluginSettings' => $this->settings,
        ];
    }

    protected function updatePluginStatus()
    {
        if (!isset($_GET['status']) || !in_array($_GET['status'], ['activate','deactivate'])) {
            return false;
        }

        if ($_GET['status']=='activate') {
            $installErrors = Wordpress::activatePlugin($_GET['plugin']);//, Wordpress::adminUrl('admin.php?page='.$this->pageSlug.'&status=activated'));
        } elseif ($_GET['status']=='deactivate') {
            $installErrors = Wordpress::deactivatePlugins($_GET['plugin']);//, Wordpress::adminUrl('admin.php?page='.$this->pageSlug.'&status=deactivated'));
        }
        print "<script>window.location.href='".Wordpress::adminUrl('admin.php?page='.$this->pageSlug)."#associatedPlugins';</script>";
        die();
    }

    /**
    * Add an 'action link' to the plugin's listing on the 'Plugins' page
    * @param array $links
    * @return array
    */
    public function addActionLinksToPluginPage($links)
    {
        return array_merge(
            array(
                '<a href="' . Wordpress::adminUrl('admin.php?page='.$this->pageSlug) . '">'.$this->actionLinkName.'</a>',
            ),
            $links
        );
    }

    /**
    * Adds submenu for this plugin to the dashboard under the 'DealerInspire' menu
    * @return void
    */
    public function setupAdminMenus()
    {
        Wordpress::addMenuPage(
            $this->pageTitle,
            $this->menuTitle,
            $this->capabilities,
            $this->pageSlug,
            [$this, 'settings']
        );
    }

    /**
    * Shows the form to edit settings for this page
    * @return void
    */
    public function settings()
    {
        if (!Wordpress::currentUserCan($this->capabilities)) {
            Wordpress::wpDie('You do not have sufficient permissions to access this page.');
        }

        $viewData = [
            'pageTitle' => $this->pageTitle,
            'tabs' => $this->getSettingsTabs(),
            'currentOptionSettings' => $this->settings->all(),
            'defaultOptionSettings' => $this->settings->defaults(),
            'postTo' => $this->getPostToUrl(),
        ] + $this->getTabData();

        $this->runTabSetup();

        $this->loader->css($this->pageSlug, 'admin/settings.min.css');
        $this->render($this->getViewName(), $viewData);
    }

    /*
     This gets additional settings for the current tab by combining the key from
     the $tabs array with the string '_getTabData'. Then if that function exists,
     it will be run, expecting that an array of data will be returned.
     */
    protected function getTabData()
    {
        $tabDataMethodName = $this->getActiveTab().'_getTabData';
        if (method_exists($this, $tabDataMethodName)) {
            return call_user_func([$this, $tabDataMethodName]);
        } else {
            return [];
        }
    }

    /*
     This runs additional code for the current tab by combining the key from
     the $tabs array with the string '_tabSetup'. Then if that function exists,
     it will be run. This is useful for enqueueing scripts or css.
     */
    protected function runTabSetup()
    {
        $tabDataMethodName = $this->getActiveTab().'_tabSetup';
        if (method_exists($this, $tabDataMethodName)) {
            return call_user_func([$this, $tabDataMethodName]);
        }
    }

    /*
     * Returns the content of the currently active tab; or null
     */
    protected function getActiveTab()
    {
        $activeTab = null;
        foreach ($this->getSettingsTabs() as $key => $tab) {
            if ($tab['active']) {
                $activeTab = $key;
            }
        }
        return $activeTab;
    }

    /**
    * Save the values POSTed from Settings page
    * @return void
    */
    public function save()
    {
        $this->settings->savePostSettings();
    }

    /**
    * Takes the $tabs array and adds additional information, such as:
    *   * A Readme tab, if the README.md file exists
    *   * Marking the current tab element as 'active', based on the 'tab'
    *     variable in the querystring
    * @return array
    */
    private function getSettingsTabs()
    {
        $tabs = $this->tabs;
        $defaultActiveTab = array_reduce(array_keys($this->tabs), function ($carry, $key) use ($tabs) {
            return ($tabs[$key]['active']) ? $key : $carry;
        });
        if ($this->showReadmeTab && !isset($tabs['readme'])) {
            $tabs = $this->addReadmeTab($tabs);
        }
        $activeTabSet = false;
        foreach ($tabs as $key => $info) {
            $tabs[$key]['url'] = Wordpress::addQueryArg('tab', $key);
            if (isset($_GET['tab']) && $_GET['tab']==$key) {
                $activeTabSet = true;
                $tabs[$key]['active'] = true;
                if ($key!=$defaultActiveTab) {
                    $tabs[$defaultActiveTab]['active'] = false;
                }
            }
        }
        if (!$activeTabSet) {
            foreach ($tabs as $key => $info) {
                $tabs[$key]['active'] = true;
                break;
            }
        }
        return $tabs;
    }
    /**
    * Takes the existing $tabs array and adds a 'Readme' tab if the README.md
    * file is available in the plugin.
    * @return array
    */
    private function addReadmeTab($tabs)
    {
        if ($readmeContent = $this->loader->readme()) {
            $tabs['readme'] = [
                'title' => 'Documentation',
                'active' => false,
                'url' => '#',
                'content' => $readmeContent,
            ];
        }
        return $tabs;
    }

    private function getPostToUrl()
    {
        $postToUrl = 'admin.php?page='.$this->pageSlug;

        $defaultActiveTab = null;
        foreach ($this->tabs as $key => $tab) {
            if ($tab['active']) {
                $defaultActiveTab = $key;
            }
        }

        $currentActiveTab = null;
        foreach ($this->getSettingsTabs() as $key => $tab) {
            if ($tab['active']) {
                $currentActiveTab = $key;
            }
        }
        if ($currentActiveTab!=$defaultActiveTab) {
            $postToUrl .= '&tab='.$currentActiveTab;
        }

        return Wordpress::adminUrl($postToUrl);
    }

    private function setPageSlug()
    {
        if (!$this->pageSlug) {
            $this->pageSlug = $this->slugify($this->menuTitle);
        }
    }

    public function getPageSlug()
    {
        return $this->pageSlug;
    }

    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    private function getViewName()
    {
        return 'Admin/Pages/Dashboard/' . $this->getClassShortname();
    }

    private function getSubviewName($partial)
    {
        return 'Admin/Partials/Dashboard/' . $this->getClassShortname() . '/' . str_replace(' ', '', ucwords($partial));
    }

    private function getClassShortname()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
