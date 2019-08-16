<?php

use yii\db\Migration;

/**
 * Class m190722_103844_create_global_config
 */
class m190722_103844_create_global_config extends Migration
{
//    /**
//     * {@inheritdoc}
//     */
//    public function safeUp()
//    {
//
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function safeDown()
//    {
//        echo "m190722_103844_create_global_config cannot be reverted.\n";
//
//        return false;
//    }

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // Create user table
        $this->createTable('global_config', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            'content' => $this->text(),
            'status' => $this->tinyInteger(6)->unsigned()->notNull()->defaultValue(0),
            'create_time' => $this->dateTime()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
            'update_time' => $this->dateTime()->append("ON UPDATE CURRENT_TIMESTAMP"),
            'user_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->createIndex(
            'idx_pid',
            'global_config',
            'project_id'
        );
    }

    public function down()
    {
        echo "m190722_103844_create_global_config cannot be reverted.\n";

        return false;
    }
}
