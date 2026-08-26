<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookienotice
 *
 * @copyright   (C) 2026 Joomtheme
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomtheme\Plugin\System\CookieNotice\Extension\CookieNotice;

return new class () implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            static function (Container $container): PluginInterface {
                $config  = (array) PluginHelper::getPlugin('system', 'cookienotice');
                $subject = $container->get(DispatcherInterface::class);
                $plugin  = new CookieNotice($subject, $config);

                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
