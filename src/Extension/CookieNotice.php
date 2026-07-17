<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookienotice
 *
 * @copyright   (C) 2026 Joomtheme
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace My\Plugin\System\CookieNotice\Extension;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Event\Application\AfterRenderEvent;
use Joomla\CMS\Event\Application\BeforeCompileHeadEvent;
use Joomla\CMS\Language\Text;
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
        $app = $event->getApplication();

        if (!$app->isClient('site') || !$this->isHtmlRequest($event->getDocument())) {
            return;
        }

        $cookieName = (string) $this->params->get('cookie_name', 'cn_accepted');

        if ($this->hasConsentCookie($cookieName)) {
            return;
        }

        $document = $event->getDocument();
        $wa       = $document->getWebAssetManager();

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

        $cookieName = (string) $this->params->get('cookie_name', 'cn_accepted');

        if ($this->hasConsentCookie($cookieName)) {
            return;
        }

        $this->loadLanguage();

        $title = trim((string) $this->params->get('title', ''));

        if ($title === '') {
            $title = Text::_('PLG_SYSTEM_COOKIENOTICE_DEFAULT_TITLE');
        }

        $message    = (string) $this->params->get('message', 'We use cookies to improve your experience.');
        $policyUrl  = trim((string) $this->params->get('policy_url', '/privacy-policy'));
        $learnText  = trim((string) $this->params->get('learn_text', 'Learn more'));
        $acceptText = trim((string) $this->params->get('accept_text', 'Accept'));
        $days       = max(1, (int) $this->params->get('days', 180));
        $delay      = max(0, (int) $this->params->get('show_delay', 0));
        $position   = (string) $this->params->get('position', 'br');
        $maxAge     = $days * 86400;

        $positionClass  = $this->getPositionClass($position);
        $policyHtml     = $this->buildPolicyHtml($policyUrl, $learnText);
        $titleId        = 'jt-cookie-title';
        $messageId      = 'jt-cookie-message';
        $closeLabel     = Text::_('PLG_SYSTEM_COOKIENOTICE_CLOSE');
        $escapedTitle   = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escapedMessage = nl2br(htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false);
        $escapedCookie  = htmlspecialchars($cookieName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $buttonText     = htmlspecialchars($acceptText !== '' ? $acceptText : 'Accept', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escapedClose   = htmlspecialchars($closeLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = <<<HTML
<div class="jt-cookie-notice {$positionClass}" role="dialog" aria-live="polite" aria-labelledby="{$titleId}" aria-describedby="{$messageId}" data-cookie-name="{$escapedCookie}" data-max-age="{$maxAge}" data-delay="{$delay}">
  <div class="jt-cookie-layout">
    <div class="jt-cookie-text">
      <div id="{$titleId}" class="jt-cookie-title">{$escapedTitle}</div>
      <div id="{$messageId}" class="jt-cookie-message">{$escapedMessage}{$policyHtml}</div>
    </div>
    <button type="button" class="jt-cookie-close" aria-label="{$escapedClose}"></button>
  </div>
  <div class="jt-cookie-actions">
    <button type="button" class="jt-cookie-button jt-cookie-accept">{$buttonText}</button>
  </div>
</div>
HTML;

        $body = $app->getBody();

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

    private function hasConsentCookie(string $cookieName): bool
    {
        return $this->getApplication()->getInput()->cookie->getString($cookieName, '') !== '';
    }

    private function getPositionClass(string $position): string
    {
        $allowed = ['br', 'bl', 'tr', 'tl', 'bc'];

        if (!in_array($position, $allowed, true)) {
            $position = 'br';
        }

        return 'jt-pos-' . $position;
    }

    private function buildPolicyHtml(string $policyUrl, string $learnText): string
    {
        $policyUrl = $this->getSafePolicyUrl($policyUrl);

        if ($policyUrl === '') {
            return '';
        }

        $label      = $learnText !== '' ? $learnText : 'Learn more';
        $isExternal = (bool) preg_match('#^(https?:)?//#i', $policyUrl);
        $target     = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';

        return ' <a class="jt-cookie-link small" href="' . htmlspecialchars($policyUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' . $target . '>'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '</a>';
    }

    private function getSafePolicyUrl(string $policyUrl): string
    {
        $policyUrl = trim($policyUrl);

        if ($policyUrl === '') {
            return '';
        }

        $scheme = parse_url($policyUrl, PHP_URL_SCHEME);

        if ($scheme !== null && !in_array(strtolower($scheme), ['http', 'https'], true)) {
            return '';
        }

        return $policyUrl;
    }
}
