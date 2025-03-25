<?php

require_once 'habitat.civix.php';
use CRM_Habitat_ExtensionUtil as E;

function habitat_modifyChecks($event) {
  $env = Civi::settings()->get('environment');
  $messages = $event->messages;
  /* Don't do the following checks:
  Cron
  Stripe Webhooks
  Debug Mode (on Dev only)
  Check Environment (reduce to severity of "Info")
  Check Xdebug (on Dev only)
   */
  foreach ($messages as $key => $message) {
    $name = $message->getName();
    if (in_array($name, ['checkLastCron', 'sparkpost_sendingdomains'])) {
      unset($event->messages[$key]);
    }
    if (strpos($name, 'stripe_webhook')) {
      unset($event->messages[$key]);
    }
    if ($name === 'checkEnvironment') {
      $event->messages[$key]->setLevel(\Psr\Log\LogLevel::INFO);
    }
    if ($env === 'Development' && $name === 'checkDebug') {
      unset($event->messages[$key]);
    }
    if ($env === 'Development' && $name === 'checkXdebug') {
      unset($event->messages[$key]);
    }
    // Reduce 'Extensions check disabled' notice to info
    if ($name === 'checkExtensions' && $message->getTitle() === E::ts('Extensions check disabled')) {
      $event->messages[$key]->setLevel(\Psr\Log\LogLevel::INFO);
    }
  }
}

function habitat_noSparkpost($event) {
  if (is_a($event->mailer, 'Mail_Sparkpost')) {
    // This is all ganked from CRM_Utils_Mail::_createMailer, we want to skip calling the hook.
    $driver = $event->driver;
    $params = $event->params;

    if ($driver == 'CRM_Mailing_BAO_Spool') {
      $mailer = new CRM_Mailing_BAO_Spool($params);
    }
    else {
      $mailer = Mail::factory($driver, $params);
    }

    // Previously, CiviCRM bundled patches to change the behavior of 3 specific drivers. Use wrapper/filters to avoid patching.
    $mailer = new CRM_Utils_Mail_FilteredPearMailer($driver, $params, $mailer);
    if (in_array($driver, ['smtp', 'mail', 'sendmail'])) {
      $mailer->addFilter('2000_log', ['CRM_Utils_Mail_Logger', 'filter']);
      $mailer->addFilter('2100_validate', function ($mailer, &$recipients, &$headers, &$body) {
        if (!is_array($headers)) {
          return PEAR::raiseError('$headers must be an array');
        }
      });
    }
    $event->mailer = $mailer;
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function habitat_civicrm_config(&$config) {
  _habitat_civix_civicrm_config($config);
  $env = Civi::settings()->get('environment');
  if ($env !== 'Production') {
    Civi::dispatcher()->addListener('hook_civicrm_check', "habitat_modifyChecks", -150);
    Civi::dispatcher()->addListener('hook_civicrm_alterMailer', "habitat_noSparkpost", -150);
  }
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function habitat_civicrm_install() {
  _habitat_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function habitat_civicrm_enable() {
  _habitat_civix_civicrm_enable();
}
