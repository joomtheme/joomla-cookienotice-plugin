<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookienotice
 *
 * @copyright   (C) 2026 Joomtheme
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$escape             = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$titleId             = 'jt-cookie-title';
$messageId           = 'jt-cookie-message';
$preferencesTitleId = 'jt-cookie-preferences-title';
$policyTarget        = $displayData['policyExternal'] ? ' target="_blank" rel="noopener noreferrer"' : '';
?>
<div class="jt-cookie-root" data-jt-cookie-consent-root>
  <section class="jt-cookie-notice <?php echo $escape($displayData['positionClass']); ?>" role="region" aria-labelledby="<?php echo $titleId; ?>" aria-describedby="<?php echo $messageId; ?>">
    <div class="jt-cookie-copy">
      <h2 id="<?php echo $titleId; ?>" class="jt-cookie-title"><?php echo $escape($displayData['title']); ?></h2>
      <p id="<?php echo $messageId; ?>" class="jt-cookie-message">
        <?php echo nl2br($escape($displayData['message']), false); ?>
        <?php if ($displayData['policyUrl'] !== '') : ?>
          <a class="jt-cookie-link" href="<?php echo $escape($displayData['policyUrl']); ?>"<?php echo $policyTarget; ?>><?php echo $escape($displayData['learnText']); ?></a>
        <?php endif; ?>
      </p>
    </div>
    <div class="jt-cookie-actions">
      <button type="button" class="jt-cookie-button" data-jt-cookie-action="reject"><?php echo $escape($displayData['rejectText']); ?></button>
      <button type="button" class="jt-cookie-button jt-cookie-button-options" data-jt-cookie-action="preferences"><?php echo $escape($displayData['manageText']); ?></button>
      <button type="button" class="jt-cookie-button" data-jt-cookie-action="accept"><?php echo $escape($displayData['acceptText']); ?></button>
    </div>
  </section>

  <button type="button" class="jt-cookie-launcher" data-jt-cookie-action="open" hidden><?php echo $escape($displayData['launcherText']); ?></button>

  <div class="jt-cookie-backdrop" data-jt-cookie-preferences hidden>
    <section class="jt-cookie-preferences" role="dialog" aria-modal="true" aria-labelledby="<?php echo $preferencesTitleId; ?>" tabindex="-1">
      <div class="jt-cookie-preferences-header">
        <div>
          <h2 id="<?php echo $preferencesTitleId; ?>" class="jt-cookie-preferences-title"><?php echo $escape($displayData['preferencesTitle']); ?></h2>
          <p class="jt-cookie-preferences-message"><?php echo $escape($displayData['preferencesMessage']); ?></p>
        </div>
        <button type="button" class="jt-cookie-close" data-jt-cookie-action="close" aria-label="<?php echo $escape($displayData['closeText']); ?>"></button>
      </div>

      <div class="jt-cookie-categories">
        <div class="jt-cookie-category">
          <div class="jt-cookie-category-copy">
            <h3><?php echo $escape($displayData['necessaryTitle']); ?></h3>
            <p><?php echo $escape($displayData['necessaryDescription']); ?></p>
          </div>
          <span class="jt-cookie-always-active"><?php echo $escape($displayData['alwaysActiveText']); ?></span>
        </div>

        <label class="jt-cookie-category">
          <span class="jt-cookie-category-copy">
            <strong><?php echo $escape($displayData['preferencesCategory']); ?></strong>
            <span><?php echo $escape($displayData['preferencesDesc']); ?></span>
          </span>
          <span class="jt-cookie-switch"><input type="checkbox" data-jt-cookie-category="preferences"><span aria-hidden="true"></span></span>
        </label>

        <label class="jt-cookie-category">
          <span class="jt-cookie-category-copy">
            <strong><?php echo $escape($displayData['analyticsTitle']); ?></strong>
            <span><?php echo $escape($displayData['analyticsDescription']); ?></span>
          </span>
          <span class="jt-cookie-switch"><input type="checkbox" data-jt-cookie-category="analytics"><span aria-hidden="true"></span></span>
        </label>

        <label class="jt-cookie-category">
          <span class="jt-cookie-category-copy">
            <strong><?php echo $escape($displayData['marketingTitle']); ?></strong>
            <span><?php echo $escape($displayData['marketingDescription']); ?></span>
          </span>
          <span class="jt-cookie-switch"><input type="checkbox" data-jt-cookie-category="marketing"><span aria-hidden="true"></span></span>
        </label>
      </div>

      <?php if ($displayData['policyUrl'] !== '') : ?>
        <p class="jt-cookie-policy-row"><a class="jt-cookie-link" href="<?php echo $escape($displayData['policyUrl']); ?>"<?php echo $policyTarget; ?>><?php echo $escape($displayData['learnText']); ?></a></p>
      <?php endif; ?>

      <div class="jt-cookie-preferences-actions">
        <button type="button" class="jt-cookie-button" data-jt-cookie-action="reject"><?php echo $escape($displayData['rejectText']); ?></button>
        <button type="button" class="jt-cookie-button jt-cookie-button-options" data-jt-cookie-action="save"><?php echo $escape($displayData['saveText']); ?></button>
        <button type="button" class="jt-cookie-button" data-jt-cookie-action="accept"><?php echo $escape($displayData['acceptText']); ?></button>
      </div>
    </section>
  </div>

  <script type="application/json" class="jt-cookie-config"><?php echo $displayData['configJson']; ?></script>
</div>
