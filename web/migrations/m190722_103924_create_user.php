<?php

use yii\db\Migration;

/**
 * Class m190722_103924_create_user
 */
class m190722_103924_create_user extends Migration
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
//        echo "m190722_103924_create_user cannot be reverted.\n";
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
        $this->createTable('user', [
            'id' => $this->primaryKey()->unsigned(),
            'email' => $this->string(64)->notNull()->defaultValue('')->unique(),
            'name' => $this->string(40)->notNull()->defaultValue('')->unique(),
            'phone' => $this->string(20)->notNull()->defaultValue(''),
            'status' => $this->tinyInteger(4)->unsigned()->notNull()->defaultValue(0),
            'is_admin' => $this->tinyInteger(4)->unsigned()->notNull()->defaultValue(0),
            'create_time' => $this->dateTime()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
            'update_time' => $this->dateTime()->append('ON UPDATE CURRENT_TIMESTAMP'),
            'role_id' => $this->tinyInteger(6)->unsigned()->notNull()->defaultValue(0),
            'password' => $this->string(100)->notNull()->defaultValue(''),
            'auth_key' => $this->string(40)->notNull()->defaultValue(''),
            'access_token' => $this->string(40)->notNull()->defaultValue(''),
        ], $tableOptions);

        $this->insert('user', [
            'email' => 'admin@example.com',
            'name' => 'admin',
            'status' => 1,
            'is_admin' => 1,
            'password' => 'admin',
            'auth_key' => 'admin-auth-key',
            'access_token' => 'admin-access-token',
        ]);
    }

    public function down()
    {
        echo "m190722_103924_create_user cannot be reverted.\n";

        return false;
    }
}
