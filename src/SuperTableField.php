<?php
/**
 * Blockonomicon Super Table Adapter plugin for Craft 3.0
 * @copyright Copyright Charlie Development
 */

namespace charliedev\blockonomicon\adapters\supertable;

use charliedev\blockonomicon\Blockonomicon;
use charliedev\blockonomicon\events\RenderImportControlsEvent;
use charliedev\blockonomicon\events\SaveFieldEvent;
use charliedev\blockonomicon\events\LoadFieldEvent;

use Craft;
use craft\base\Plugin;

use yii\base\Event;

/**
 * Blockonomicon adapter for Super Table fields.
 * Exports data about inner fields of a Super Table field, and will provide existing field IDs
 * to imported data, in an attempt to save data when re-importing over existing data.
 */
class SuperTableField extends Plugin
{
	/**
	 * @inheritdoc
	 * @see craft\base\Plugin
	 */
	public function init()
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

				foreach ($event->field->getBlockTypeFields() as $field) {
					$fielddata = Blockonomicon::getInstance()->blocks->getFieldData($field);
					$event->settings['typesettings']['fields'][] = $fielddata;
				}
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

				// Find/generate block ID.
				$blockid = 'new';
				if ($event->field) {
					$blockid = $event->field->getBlockTypes()[0]->id; // Super tables have only one block type.
				}

				// Create a list of existing fields, keyed by handle, if possible.
				$currentfields = [];
				if ($event->field) {
					$currentfields = array_reduce($event->field->getBlockTypeFields(), function ($value, $item) {
						$value[$item->handle] = $item;
						return $value;
					}, []);
				}

				// Match block fields to settings fields, reusing ids when possible.
				$fields = [];
				foreach ($event->settings['typesettings']['fields'] as $field) {
					$currentfield = $currentfields[$field['handle']] ?? null;

					// Send off event to update inner field settings.
					$secondaryevent = new LoadFieldEvent();
					$secondaryevent->field = $currentfield;
					$secondaryevent->importoptions = $event->importoptions[$field['handle']] ?? null; // Get available import options for the subfield, if possible.
					$secondaryevent->settings = $field;
					Blockonomicon::getInstance()->trigger(Blockonomicon::EVENT_LOAD_FIELD, $secondaryevent);
					if ($currentfield) {
						$fields[$currentfield->id] = $secondaryevent->settings;
					} else {
						$fields['new' . (count($fields) + 1)] = $secondaryevent->settings;
					}
				}

				// Add inner fields to the settings being used.
				unset($event->settings['typesettings']['fields']);
				$event->settings['typesettings']['blocktypes'] = [];
				$event->settings['typesettings']['blocktypes'][$blockid] = [];
				$event->settings['typesettings']['blocktypes'][$blockid]['fields'] = $fields;
			}
		);

		// Generate controls to set data stripped on block export.
		Event::on(
			Blockonomicon::class,
			Blockonomicon::EVENT_RENDER_IMPORT_CONTROLS,
			function (RenderImportControlsEvent $event) {

				// Ignore any fields that are not Assets fields.
				if ($event->settings['type'] != \verbb\supertable\fields\SuperTableField::class) {
					return;
				}

				// Gather possible controls for each inner field.
				$blockcontrols = [];
				foreach ($event->settings['typesettings']['fields'] as $field) {
					$secondaryevent = new RenderImportControlsEvent();
					$secondaryevent->blockHandle = $event->blockHandle; // Keep the base imported block handle.
					$secondaryevent->handle = $event->handle . '[' . $field['handle'] . ']'; // Nest the imported field handle within the base handle.
					$secondaryevent->cachedoptions = $event->cachedoptions[$field['handle']] ?? null; // Get cached options for the subfield, if possible.
					$secondaryevent->settings = $field;
					Blockonomicon::getInstance()->trigger(Blockonomicon::EVENT_RENDER_IMPORT_CONTROLS, $secondaryevent);
					if (!empty($secondaryevent->controls)) {
						$blockcontrols[] = [
							'table' => $event->settings['name'],
							'handle' => $field['handle'],
							'name' => $field['name'],
							'control' => $secondaryevent->controls,
						];
					}
				}

				// No controls, don't add them to the event.
				if (count($blockcontrols) == 0) {
					return;
				}

				$event->controls = Craft::$app->getView()->renderTemplate('blockonomicon-super-table-adapter/Adapter.html', [
					'safeHandle' => $event->blockHandle . '_' . implode('_', preg_split('/[\[\]]+/', $event->handle, -1, PREG_SPLIT_NO_EMPTY)),
					'fieldHandle' => $event->handle,
					'settings' => $event->settings,
					'cachedOptions' => $event->cachedoptions,
					'blockControls' => $blockcontrols,
				]);
			}
		);

		parent::init();
	}
}
