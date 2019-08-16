<?php

use yii\db\Migration;

/**
 * Class m190722_103908_create_project
 */
class m190722_103908_create_project extends Migration
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
//        echo "m190722_103908_create_project cannot be reverted.\n";
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
        $this->createTable('project', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(128)->notNull()->defaultValue(""),
            'user_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            'contact_email' => $this->string(64)->notNull()->defaultValue(''),
            'create_time' => $this->dateTime()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
            'update_time' => $this->dateTime()->append('ON UPDATE CURRENT_TIMESTAMP'),
            'label' => $this->string(64)->notNull()->defaultValue('')->unique(),
            'auto_balance_on' => $this->tinyInteger(5)->unsigned()->notNull()->defaultValue(0),
            'rate5in2' => $this->integer(11)->notNull()->defaultValue(0)
        ], $tableOptions);

    }

    public function down()
    {
        echo "m190722_103908_create_project cannot be reverted.\n";

        return false;
    }
}
