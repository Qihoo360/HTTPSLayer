<?php

namespace tests\models;

use app\models\BizUser;
use Constant;

class BizUserTest extends \Codeception\Test\Unit
{
    private $model;
    /**
     * @var \UnitTester
     */
    public $tester;

    public function testSimpleRandom() {
        expect("abc")->equals("abc");
    }

    public function testFindIdentity() {
        expect_that($biz_user = BizUser::findIdentity(1));
        expect($biz_user->name)->equals("abc");

    }

    public function testValidAsDict() {
        expect_that($dict_1 = BizUser::validAsDict());
        expect_that($dict_2 = BizUser::validAsDict(Constant::VALID));
        expect($dict_1[1])->equals("abc");
        expect($dict_1[2])->equals("abc2");

        expect($dict_2[1])->equals("abc");
        expect_not(isset($dict_2[2]));

    }
}
