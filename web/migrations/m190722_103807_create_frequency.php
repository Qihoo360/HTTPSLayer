<?php

use yii\db\Migration;

/**
 * Class m190722_103807_create_frequency
 */
class m190722_103807_create_frequency extends Migration
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
//        echo "m190722_103807_create_frequency cannot be reverted.\n";
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
        $this->createTable('frequency', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            'description' => $this->string(255)->notNull()->defaultValue(""),
            'path' => $this->string(255)->notNull()->defaultValue(""),
            'according' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
            'method' => $this->string(256)->notNull()->defaultValue(""),
            'cookie_name' => $this->string(256)->notNull()->defaultValue(""),
            'time_window' => $this->string(2048)->notNull()->defaultValue(""),
            'referer' => $this->string(2048)->notNull()->defaultValue(""),
            'arguments' => $this->string(2048)->notNull()->defaultValue(""),
            'white_ip' => $this->text(),
            'black_ip' => $this->text(),
            'handle_way' => $this->integer(11)->unsigned()->notNull()->defaultValue(1)->comment("1-log;2-captcha;3-user handle"),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(2),
            'create_time' => $this->dateTime()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
            'update_time' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'create_user' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            'update_user' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            'update_operation' => $this->integer(11)->notNull()->defaultValue(0),
        ], $tableOptions);
    }

    public function down()
    {
        echo "m190722_103807_create_frequency cannot be reverted.\n";

        return false;
    }
}
