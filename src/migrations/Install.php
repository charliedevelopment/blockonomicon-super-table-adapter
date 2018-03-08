<?php
/**
 * Blockonomicon Super Table Adapter plugin for Craft 3.0
 * @copyright Copyright Charlie Development
 */

namespace charliedev\blockonomicon\migrations;

use charliedev\blockonomicon\Blockonomicon;

use Craft;
use craft\db\Migration;
use craft\helpers\FileHelper;

/**
 * Install migration.
 */
class Install extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		// Prevent installation if Blockonomicon is not installed as well.
		return Craft::$app->getPlugin('blockonomicon') != null;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
	}
}
