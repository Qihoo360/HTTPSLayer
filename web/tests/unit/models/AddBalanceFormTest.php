<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 2019/5/15
 * Time: 10:49 AM
 */

namespace tests\models;


use app\models\AddBalanceForm;
use Codeception\Test\Unit;

class AddBalanceFormTest extends Unit
{
    /**
     * @var $model AddBalanceForm
     */
    private $model;

    public $tester;

    public function testSave() {
        $this->model = new AddBalanceForm();
        $this->model->name = "test";
        $this->model->project_balances_CORP = [

        ];


        codecept_debug($this->model);
    }

}