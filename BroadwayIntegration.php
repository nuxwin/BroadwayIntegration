<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

/**
 * @noinspection PhpUnhandledExceptionInspection PhpDocMissingThrowsInspection
 */

use Psr\Container\ContainerInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

/**
 * Class iMSCP_Plugin_BroadWayIntegration
 */
class iMSCP_Plugin_BroadWayIntegration extends iMSCP_Plugin_Action
{
    const ON_CONTAINER_SETUP_EVENT = 'onContainerSetupEvent';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @inheritDoc
     */
    public function init()
    {
        require __DIR__ . '/vendor/autoload.php';

        l10n_addTranslations(__DIR__ . '/l10n', 'Array', $this->getName());
    }

    /**
     * @inheritDoc
     */
    public function register(iMSCP_Events_Manager_Interface $events)
    {
        $events->registerListener(
            iMSCP_Events::onBeforeInstallPlugin,
            function (iMSCP_Events_Event $event) {
                $this->checkForPluginRequirements($event);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function install(iMSCP_Plugin_Manager $pm)
    {
        try {
            $this->migrateDb('up');
        } catch (Throwable $e) {
            throw new iMSCP_Plugin_Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function uninstall(iMSCP_Plugin_Manager $pm)
    {
        try {
            $this->clearTranslations();
            $this->migrateDb('down');
        } catch (Throwable $e) {
            throw new iMSCP_Plugin_Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function update(iMSCP_Plugin_Manager $pm, $fromVersion, $toVersion)
    {
        try {
            $this->clearTranslations();
            $this->migrateDb('up');
        } catch (Throwable $e) {
            throw new iMSCP_Plugin_Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getContainer(): ContainerInterface
    {
        if (NULL === $this->container) {
            $config = $this->getConfig();
            $this->container = new ServiceManager($config['container']);
            $this->container->setService('config', $config);

            $this->getPluginManager()->getEventManager()->dispatch(
                self::ON_CONTAINER_SETUP_EVENT,
                ['container' => $this->container]
            );
        }

        return $this->container;
    }

    /**
     * Check for plugin requirements
     *
     * @param iMSCP_Events_Event $event
     * @return void
     */
    private function checkForPluginRequirements(iMSCP_Events_Event $event)
    {
        if ($event->getParam('pluginName') != $this->getName()) {
            // We're not the target of the event; return early
            return;
        }

        $config = iMSCP_Registry::get('config');
        $version = $config['Version'];
        $build = $config['Build'];

        if (!preg_match('/^\d+\.\d+\.\d+-\d{10}$/', "$version-$build")
            || version_compare("$version-$build", '1.5.3-2018120800', '<')
        ) {
            set_page_message(
                'The BroadwayIntegration plugin requires i-MSCP version ≥ 1.5.3 (build 20181200800)',
                'error'
            );
            $event->stopPropagation();
            return;
        }

        if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
            return;
        }

        set_page_message(
            'The BroadwayIntegration plugin requires PHP ≥ 7.1', 'error'
        );
        $event->stopPropagation();
    }

    /**
     * Clear translations if any.
     *
     * @return void
     */
    private function clearTranslations()
    {
        /** @var Zend_Translate $translator */
        $translator = iMSCP_Registry::get('translator');
        if ($translator->hasCache()) {
            $translator->clearCache($this->getName());
        }
    }
}
