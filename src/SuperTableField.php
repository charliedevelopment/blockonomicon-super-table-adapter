<?php
/**
 * Blockonomicon plugin for Craft 3.0
 * @copyright Copyright Charlie Development
 */

namespace charliedev\blockonomicon\adapters;

use charliedev\blockonomicon\Blockonomicon;
use charliedev\blockonomicon\events\RenderImportControlsEvent;
use charliedev\blockonomicon\events\SaveFieldEvent;
use charliedev\blockonomicon\events\LoadFieldEvent;

use Craft;

use yii\base\Event;

/**
 * Blockonomicon adapter for Super Table fields.
 * Exports data about inner fields of a Super Table field, and will provide existing field IDs
 * to imported data, in an attempt to save data when re-importing over existing data.
 */
class SuperTableField
{
	/**
	 * Binds to necessary event handlers.
	 */
	public static function setup()
	{
		// On export, gather inner field data and attach to the event.
		Event::on(
			Blockonomicon::class,
			Blockonomicon::EVENT_SAVE_FIELD,
			function (SaveFieldEvent $event) {

				// Ignore any fields that are not Super Table fields.
				if (get_class($event->field) != \verbb\supertable\fields\SuperTableField::class) {
					return;
				}

				$event->settings['typesettings']['fields'] = [];
			}
		);

		// On import, check existing field information and reuse their IDs.
		Event::on(
			Blockonomicon::class,
			Blockonomicon::EVENT_LOAD_FIELD,
			function (LoadFieldEvent $event) {
				
				// Ignore any fields that are not Super Table fields.
				if ($event->settings['type'] != \verbb\supertable\fields\SuperTableField::class) {
					return;
				}

				if ($event->field == null) {
					return;
				}

				
			}
		);
	}
}
