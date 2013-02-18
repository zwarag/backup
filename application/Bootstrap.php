<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initBaseUrl() {

        $baseUrl = substr($this->array2key(explode('public/', $_SERVER['REDIRECT_URL']), 0), 0, -1);
        $this->bootstrap('frontcontroller');
        $front = $this->getResource('frontcontroller');
        $front->setBaseUrl($baseUrl);
        Zend_Registry::set('baseUrl', $baseUrl);
    }

    protected function _initMenu() {
        $Items = array(
            'Stream' => '/'
        );

        #$Menu = new ZFC_Menu();
        #$Menu->add_menu($Items);
    }

    protected function _initConfig() {
        $config = $this->get_config();
        Zend_Registry::set('config', $config);
    }

    public function get_config() {
        return new Zend_Config_Ini("../application/configs/application.ini");
    }

    protected function array2key(array $array, $key) {
        if (isset($array[$key]))
            return $array[$key];
        else
            return false;
    }

    protected function _initZFDebug() {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('ZFDebug');

        $options = array(
            'plugins' => array('Variables',
                'File' => array('base_path' => '/path/to/project'),
                'Memory',
                'Time',
                'Registry',
                'Exception')
        );

        # Instantiate the database adapter and setup the plugin.
        # Alternatively just add the plugin like above and rely on the autodiscovery feature.
        if ($this->hasPluginResource('db')) {
            $this->bootstrap('db');
            $db = $this->getPluginResource('db')->getDbAdapter();
            $options['plugins']['Database']['adapter'] = $db;
        }

        # Setup the cache plugin
        if ($this->hasPluginResource('cache')) {
            $this->bootstrap('cache');
            $cache = $this - getPluginResource('cache')->getDbAdapter();
            $options['plugins']['Cache']['backend'] = $cache->getBackend();
        }

        $debug = new ZFDebug_Controller_Plugin_Debug($options);

        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        $frontController->registerPlugin($debug);
    }

}

