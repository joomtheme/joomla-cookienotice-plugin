<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookienotice
 *
 * @copyright   (C) 2026 Joomtheme
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace My\Plugin\System\CookieNotice\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

class CookieNotice extends CMSPlugin
{
    /**
     * Load assets only when needed.
     */
    public function onBeforeCompileHead(): void
    {
        $app = Factory::getApplication();

        // Only frontend
        if (!$app->isClient('site')) {
            return;
        }

        $cookieName = (string) $this->params->get('cookie_name', 'cn_accepted');

        // If cookie exists, do nothing
        if ($app->getInput()->cookie->getString($cookieName, '') !== '') {
            return;
        }

        $doc = $app->getDocument();
        $wa  = $doc->getWebAssetManager();

        // Register + load plugin assets (Bootstrap-friendly, with fallback CSS)
        $wa->registerStyle(
            'plg.system.cookienotice',
            'media/plg_system_cookienotice/css/cookienotice.css',
            [],
            ['version' => 'auto']
        );
        $wa->useStyle('plg.system.cookienotice');

        $wa->registerScript(
            'plg.system.cookienotice',
            'media/plg_system_cookienotice/js/cookienotice.js',
            [],
            ['defer' => true, 'version' => 'auto']
        );
        $wa->useScript('plg.system.cookienotice');

        // Optional custom CSS from plugin params
        $customCss = (string) $this->params->get('custom_css', '');
        if (trim($customCss) !== '') {
            $wa->addInlineStyle($customCss);
        }
    }

    /**
     * Inject the banner HTML at the end of the body.
     */
    public function onAfterRender(): void
    {
        $app = Factory::getApplication();

        // Only frontend
        if (!$app->isClient('site')) {
            return;
        }

        // Params
        $message    = (string) $this->params->get('message', 'We use cookies to improve your experience.');
        $policyUrl  = (string) $this->params->get('policy_url', '/privacy-policy');
        $learnText  = (string) $this->params->get('learn_text', 'Learn more');
        $acceptText = (string) $this->params->get('accept_text', 'Accept');
        $cookieName = (string) $this->params->get('cookie_name', 'cn_accepted');
        $days       = max(1, (int) $this->params->get('days', 180));
        $delay      = max(0, (int) $this->params->get('show_delay', 0));
        $position   = (string) $this->params->get('position', 'br');

        // Title with fallback
        $title = trim((string) $this->params->get('title', ''));
        if ($title === '' || $title === 'PLG_SYSTEM_COOKIENOTICE_DEFAULT_TITLE') {
            $langTag = Factory::getApplication()->getLanguage()->getTag();
            if (strpos($langTag, 'tr') === 0) {
                $title = 'Çerez alır mıydınız? 🍪';
            } else {
                $title = 'Would you like cookies? 🍪';
            }
        }


        // If cookie exists, do nothing
        if ($app->getInput()->cookie->getString($cookieName, '') !== '') {
            return;
        }

        $maxAge = $days * 86400;

        // Position classes (Bootstrap utilities)
        $posClass = 'position-fixed ';
        switch ($position) {
            case 'bl':
                $posClass .= 'bottom-0 start-0 m-3';
                break;
            case 'tr':
                $posClass .= 'top-0 end-0 m-3';
                break;
            case 'tl':
                $posClass .= 'top-0 start-0 m-3';
                break;
            case 'bc':
                $posClass .= 'bottom-0 start-50 translate-middle-x mb-3 jt-pos-bc';
                break;
            case 'br':
            default:
                $posClass .= 'bottom-0 end-0 m-3';
                break;
        }

        // Optional policy link
        $policyHtml = '';
        if (trim($policyUrl) !== '') {
            $label = trim($learnText) !== '' ? $learnText : 'Learn more';
            $policyHtml = ' <a class="small" href="' . htmlspecialchars($policyUrl, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener">'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                . '</a>';
        }

        // Bootstrap 5 banner (JS will add jt-no-bs fallback styling if Bootstrap not present)
        $html = '
<div class="jt-cookie-notice alert alert-dark shadow rounded-4 p-3 fade d-none ' . $posClass . '" role="dialog" aria-live="polite"
  data-cookie-name="' . htmlspecialchars($cookieName, ENT_QUOTES, 'UTF-8') . '"
  data-max-age="' . (int) $maxAge . '"
  data-delay="' . (int) $delay . '">

  <div class="d-flex gap-3 align-items-start">
    <div class="jt-cookie-text flex-grow-1">
      <div class="jt-cookie-title fw-bold mb-1">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>
      <div class="jt-cookie-message">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . $policyHtml . '</div>
    </div>
    <button type="button" class="btn-close jt-cookie-close" aria-label="Close"></button>
  </div>

  <div class="jt-cookie-actions mt-3 d-flex justify-content-end gap-2">
    <button type="button" class="btn btn-primary btn-sm jt-cookie-accept">' . htmlspecialchars($acceptText, ENT_QUOTES, 'UTF-8') . '</button>
  </div>
</div>
';

        // Inject before </body> (case-insensitive), fallback append
        $body = $app->getBody();
        if (stripos($body, '</body>') !== false) {
            $body = preg_replace('~</body>~i', $html . '</body>', $body, 1);
        } else {
            $body .= $html;
        }

        $app->setBody($body);
    }
}
