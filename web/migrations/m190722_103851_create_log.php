<?php

use yii\db\Migration;

/**
 * Class m190722_103851_create_log
 */
class m190722_103851_create_log extends Migration
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
//        echo "m190722_103851_create_log cannot be reverted.\n";
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
        $this->createTable('log', [
            'id' => $this->bigPrimaryKey(20)->unsigned(),
            'level' => $this->integer(11)->notNull()->defaultValue(0),
            'category' => $this->string(255)->notNull()->defaultValue(''),
            'log_time' => $this->double(),
            'prefix' => $this->string(255)->defaultValue(''),
            'message' => $this->text(),


        ], $tableOptions);

        $this->createIndex(
            'idx_log_level',
            'log',
            'level'
        );
        $this->createIndex(
            'idx_log_category',
            'log',
            'category'
        );
    }

    public function down()
    {
        echo "m190722_103851_create_log cannot be reverted.\n";

        return false;
    }
}
