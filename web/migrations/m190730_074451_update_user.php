<?php

use yii\db\Migration;

/**
 * Class m190730_074451_update_user
 */
class m190730_074451_update_user extends Migration
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
//        echo "m190730_074451_update_user cannot be reverted.\n";
//
//        return false;
//    }

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        try {
            $this->update('user', [
                'password' => Yii::$app->getSecurity()->generatePasswordHash("admin")
            ], [
                'name' => 'admin'
            ]);
        } catch (\yii\base\Exception $e) {
        }
    }

    public function down()
    {
        echo "m190730_074451_update_user cannot be reverted.\n";

        return false;
    }
}
