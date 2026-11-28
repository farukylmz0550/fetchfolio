<?php if (!defined('ABSPATH')) exit; ?>
</div>
<section class="consent-banner" data-ff-consent-banner role="region" aria-label="<?php esc_attr_e('Privacy preferences', 'fetchfolio'); ?>" hidden>
  <div class="consent-copy">
    <strong><?php esc_html_e('Your privacy choices', 'fetchfolio'); ?></strong>
    <p><?php esc_html_e('Choose your privacy preference. This notice will not appear again after you make a choice.', 'fetchfolio'); ?></p>
  </div>
  <div class="consent-actions">
    <button type="button" class="consent-button consent-accept" data-ff-consent="accept"><?php esc_html_e('Accept all', 'fetchfolio'); ?></button>
    <button type="button" class="consent-button" data-ff-consent="reject"><?php esc_html_e('Reject optional', 'fetchfolio'); ?></button>
    <button type="button" class="consent-button consent-minimal" data-ff-consent="necessary"><?php esc_html_e('Accept necessary only', 'fetchfolio'); ?></button>
  </div>
</section>
<?php wp_footer(); ?>
</body>
</html>
