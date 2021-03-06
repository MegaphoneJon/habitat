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
   */
  foreach ($messages as $key => $message) {
    $name = $message->getName();
    if (in_array($name, ['checkLastCron', 'stripe_webhook'])) {
      unset($event->messages[$key]);
    }
    if ($name === 'checkEnvironment') {
      $event->messages[$key]->setLevel(\Psr\Log\LogLevel::INFO);
    }
    if ($env === 'Development' && $name === 'checkDebug') {
      unset($event->messages[$key]);
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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function habitat_civicrm_xmlMenu(&$files) {
  _habitat_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function habitat_civicrm_postInstall() {
  _habitat_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function habitat_civicrm_uninstall() {
  _habitat_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function habitat_civicrm_enable() {
  _habitat_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function habitat_civicrm_disable() {
  _habitat_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function habitat_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _habitat_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function habitat_civicrm_managed(&$entities) {
  _habitat_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function habitat_civicrm_caseTypes(&$caseTypes) {
  _habitat_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function habitat_civicrm_angularModules(&$angularModules) {
  _habitat_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function habitat_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _habitat_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function habitat_civicrm_entityTypes(&$entityTypes) {
  _habitat_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function habitat_civicrm_themes(&$themes) {
  _habitat_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
function habitat_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
function habitat_civicrm_navigationMenu(&$menu) {
  _habitat_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _habitat_civix_navigationMenu($menu);
} // */
