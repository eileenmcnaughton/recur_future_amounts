<?php

require_once 'recur_future_amounts.civix.php';
// phpcs:disable
use Civi\Api4\ContributionRecur;
use Civi\Core\Event\PostEvent;
use CRM_RecurFutureAmounts_ExtensionUtil as E;
// phpcs:enable


/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function recur_future_amounts_civicrm_config(&$config): void {
  _recur_future_amounts_civix_civicrm_config($config);
  Civi::dispatcher()->addListener('hook_civicrm_post', 'recur_future_amounts_hook_civicrm_post');
}

/**
 * Event fired after modifying a contribution.
 *
 * @param \Civi\Core\Event\PostEvent $event
 *
 * @throws \CRM_Core_Exception
 */
function recur_future_amounts_hook_civicrm_post(PostEvent $event): void {
  if ($event->action === 'edit' && $event->object === 'Contribution') {
    $contribution = $event->object;
    // This is exactly the same as the core hook but core only handles
    // when is_template === '0' and this only handles when it === '1'
    if ($contribution->is_template === '1' || empty($contribution->contribution_recur_id)) {
      return;
    }

    if ($contribution->total_amount === NULL || $contribution->currency === NULL || $contribution->is_template === NULL) {
      // The contribution has not been fully loaded, so fetch a full copy now.
      $contribution->find(TRUE);
    }
    if (!$contribution->is_template) {
      return;
    }

    $contributionRecur = ContributionRecur::get(FALSE)
      ->addWhere('id', '=', $contribution->contribution_recur_id)
      ->execute()
      ->first();

    if ($contribution->currency !== $contributionRecur['currency'] || !CRM_Utils_Money::equals($contributionRecur['amount'], $contribution->total_amount, $contribution->currency)) {
      ContributionRecur::update(FALSE)
        ->addValue('amount', $contribution->total_amount)
        ->addValue('currency', $contribution->currency)
        ->addValue('modified_date', 'now')
        ->addWhere('id', '=', $contributionRecur['id'])
        ->execute();
    }
  }

}
