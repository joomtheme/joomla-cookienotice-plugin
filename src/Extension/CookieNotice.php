<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookienotice
 *
 * @copyright   (C) 2026 Joomtheme
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomtheme\Plugin\System\CookieNotice\Extension;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Event\Application\AfterRenderEvent;
use Joomla\CMS\Event\Application\BeforeCompileHeadEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

defined('_JEXEC') or die;

final class CookieNotice extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead' => 'registerAssets',
            'onAfterRender'       => 'injectBanner',
        ];
    }

    public function registerAssets(BeforeCompileHeadEvent $event): void
    {
        $app      = $event->getApplication();
        $document = $event->getDocument();

        if (!$app->isClient('site') || !$this->isHtmlRequest($document)) {
            return;
        }

        $wa = $document->getWebAssetManager();

        $wa->getRegistry()->addExtensionRegistryFile('plg_system_cookienotice');
        $wa->useStyle('plg_system_cookienotice.banner.styles');
        $wa->useScript('plg_system_cookienotice.banner.script');

        $customCss = trim((string) $this->params->get('custom_css', ''));

        if ($customCss !== '') {
            $wa->addInlineStyle($customCss);
        }
    }

    public function injectBanner(AfterRenderEvent $event): void
    {
        $app = $event->getApplication();

        if (!$app->isClient('site') || !$this->isHtmlRequest($app->getDocument())) {
            return;
        }

        $body = $app->getBody();

        if (stripos($body, 'data-jt-cookie-consent-root') !== false) {
            return;
        }

        $this->loadLanguage();

        $days       = min(3650, max(1, (int) $this->params->get('days', 180)));
        $delay      = min(60000, max(0, (int) $this->params->get('show_delay', 0)));
        $position   = (string) $this->params->get('position', 'bc');
        $policyUrl  = $this->getSafePolicyUrl((string) $this->params->get('policy_url', '/privacy-policy'));
        $cookieName = $this->getCookieName((string) $this->params->get('cookie_name', 'jt_cookie_consent'));
        $revision   = $this->getConsentRevision((string) $this->params->get('consent_revision', '1'));

        $config = [
            'cookieName'     => $cookieName,
            'maxAge'         => $days * 86400,
            'revision'       => $revision,
            'delay'          => $delay,
            'showLauncher'   => (bool) $this->params->get('show_reopen', 1),
            'snippets'       => [
                'preferences' => trim((string) $this->params->get('preferences_snippets', '')),
                'analytics'   => trim((string) $this->params->get('analytics_snippets', '')),
                'marketing'   => trim((string) $this->params->get('marketing_snippets', '')),
            ],
            'cleanupCookies' => [
                'preferences' => $this->getListParam('preferences_cookie_names'),
                'analytics'   => $this->getListParam('analytics_cookie_names', "_ga\n_ga_*\n_gid\n_gat"),
                'marketing'   => $this->getListParam('marketing_cookie_names', "_gcl_au\n_fbp\n_fbc"),
            ],
        ];

        $configJson = json_encode(
            $config,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) ?: '{}';

        $displayData = [
            'positionClass'        => $this->getPositionClass($position),
            'title'                => $this->getTextParam('title', 'PLG_SYSTEM_COOKIENOTICE_DEFAULT_TITLE'),
            'message'              => $this->getTextParam('message', 'PLG_SYSTEM_COOKIENOTICE_DEFAULT_MESSAGE'),
            'acceptText'           => $this->getTextParam('accept_text', 'PLG_SYSTEM_COOKIENOTICE_ACCEPT_ALL'),
            'rejectText'           => $this->getTextParam('reject_text', 'PLG_SYSTEM_COOKIENOTICE_REJECT_ALL'),
            'manageText'           => $this->getTextParam('manage_text', 'PLG_SYSTEM_COOKIENOTICE_MANAGE_PREFERENCES'),
            'saveText'             => $this->getTextParam('save_text', 'PLG_SYSTEM_COOKIENOTICE_SAVE_PREFERENCES'),
            'launcherText'         => $this->getTextParam('launcher_text', 'PLG_SYSTEM_COOKIENOTICE_COOKIE_SETTINGS'),
            'learnText'            => $this->getTextParam('learn_text', 'PLG_SYSTEM_COOKIENOTICE_LEARN_MORE'),
            'preferencesTitle'     => Text::_('PLG_SYSTEM_COOKIENOTICE_PREFERENCES_TITLE'),
            'preferencesMessage'   => Text::_('PLG_SYSTEM_COOKIENOTICE_PREFERENCES_MESSAGE'),
            'necessaryTitle'       => Text::_('PLG_SYSTEM_COOKIENOTICE_NECESSARY_TITLE'),
            'necessaryDescription' => Text::_('PLG_SYSTEM_COOKIENOTICE_NECESSARY_DESC'),
            'preferencesCategory'  => Text::_('PLG_SYSTEM_COOKIENOTICE_PREFERENCES_CATEGORY'),
            'preferencesDesc'      => Text::_('PLG_SYSTEM_COOKIENOTICE_PREFERENCES_DESC'),
            'analyticsTitle'       => Text::_('PLG_SYSTEM_COOKIENOTICE_ANALYTICS_TITLE'),
            'analyticsDescription' => Text::_('PLG_SYSTEM_COOKIENOTICE_ANALYTICS_DESC'),
            'marketingTitle'       => Text::_('PLG_SYSTEM_COOKIENOTICE_MARKETING_TITLE'),
            'marketingDescription' => Text::_('PLG_SYSTEM_COOKIENOTICE_MARKETING_DESC'),
            'alwaysActiveText'     => Text::_('PLG_SYSTEM_COOKIENOTICE_ALWAYS_ACTIVE'),
            'closeText'            => Text::_('PLG_SYSTEM_COOKIENOTICE_CLOSE_PREFERENCES'),
            'policyUrl'            => $policyUrl,
            'policyExternal'       => (bool) preg_match('#^(https?:)?//#i', $policyUrl),
            'configJson'           => $configJson,
        ];

        $layout = new FileLayout('banner', dirname(__DIR__, 2) . '/layouts');
        $html   = $layout->render($displayData);

        if (stripos($body, '</body>') !== false) {
            $body = preg_replace('~</body>~i', $html . '</body>', $body, 1) ?? ($body . $html);
        } else {
            $body .= $html;
        }

        $app->setBody($body);
    }

    private function isHtmlRequest(object $document): bool
    {
        return $document instanceof HtmlDocument;
    }

    private function getTextParam(string $name, string $languageKey): string
    {
        $value = trim((string) $this->params->get($name, ''));

        return $value !== '' ? $value : Text::_($languageKey);
    }

    private function getCookieName(string $cookieName): string
    {
        $cookieName = trim($cookieName);

        return preg_match('/^[A-Za-z0-9._-]{1,80}$/', $cookieName) ? $cookieName : 'jt_cookie_consent';
    }

    private function getConsentRevision(string $revision): string
    {
        $revision = trim($revision);

        return preg_match('/^[A-Za-z0-9._-]{1,40}$/', $revision) ? $revision : '1';
    }

    private function getPositionClass(string $position): string
    {
        $allowed = ['br', 'bl', 'tr', 'tl', 'bc'];

        if (!in_array($position, $allowed, true)) {
            $position = 'bc';
        }

        return 'jt-pos-' . $position;
    }

    private function getListParam(string $name, string $default = ''): array
    {
        $value = trim((string) $this->params->get($name, $default));

        if ($value === '') {
            return [];
        }

        $items = preg_split('/[\r\n,]+/', $value) ?: [];
        $items = array_map('trim', $items);

        return array_values(array_unique(array_filter($items, static fn (string $item): bool => $item !== '')));
    }

    private function getSafePolicyUrl(string $policyUrl): string
    {
        $policyUrl = trim($policyUrl);

        if ($policyUrl === '' || preg_match('/[[:cntrl:]]/', $policyUrl)) {
            return '';
        }

        if (parse_url($policyUrl) === false) {
            return '';
        }

        $scheme = parse_url($policyUrl, PHP_URL_SCHEME);

        if ($scheme !== null && !in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
            return '';
        }

        return $policyUrl;
    }
}
