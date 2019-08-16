<?php

use yii\db\Migration;

/**
 * Class m190722_103812_create_frequency_version
 */
class m190722_103812_create_frequency_version extends Migration
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
//        echo "m190722_103812_create_frequency_version cannot be reverted.\n";
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
        $this->createTable('frequency_version', [
            'id' => $this->primaryKey()->unsigned(),
            "project_id" => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            "data" => $this->text(),
            "version" => $this->string(32)->notNull()->defaultValue(""),
            "online_date" => $this->dateTime()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
            "update_date" => $this->dateTime()->append("ON UPDATE CURRENT_TIMESTAMP"),
            "online_user" => $this->string(100)->notNull()->defaultValue(""),
            "update_user" => $this->string(100)->notNull()->defaultValue(''),
            "status" => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
            "project_label" => $this->string(100)->notNull()->defaultValue(''),
        ], $tableOptions);
    }

    public function down()
    {
        echo "m190722_103812_create_frequency_version cannot be reverted.\n";

        return false;
    }
}
