<?php if (!defined('ABSPATH')) exit; ?>
</div>
<section class="consent-banner" data-ff-consent-banner role="region" aria-label="Privacy preferences" hidden>
  <div class="consent-copy">
    <strong>Your privacy choices</strong>
    <p>WP Statistics records visitor statistics only if you allow it. You can change your choice at any time.</p>
  </div>
  <div class="consent-actions">
    <button type="button" class="consent-button consent-accept" data-ff-consent="accept">Accept all</button>
    <button type="button" class="consent-button" data-ff-consent="reject">Reject optional</button>
    <button type="button" class="consent-button consent-minimal" data-ff-consent="necessary">Accept necessary only</button>
  </div>
</section>
<button class="consent-settings" type="button" data-ff-consent-open hidden>Privacy settings</button>
<?php wp_footer(); ?>
</body>
</html>
