<?php
/**
 * Blockonomicon Super Table Adapter plugin for Craft 3.0
 * @copyright Copyright Charlie Development
 */

namespace charliedev\blockonomicon\adapters\supertable\migrations;

use Craft;
use craft\db\Migration;

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
