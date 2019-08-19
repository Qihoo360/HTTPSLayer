<?php


class ModelsProjectFormTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    private $_prj;

    protected $tester;

    protected function _before()
    {
        $this->_prj = new \app\models\Project();

    }

    protected function _after()
    {
    }

    public function testValidation()
    {
        $this->specify('project validation fail', function() {

            $this->_prj->name = 'prj1234';
            $this->_prj->user_id = 1;
            $this->_prj->label = "prj_new";
            $this->_prj->contact_email = "prj1_tes";

            $this->assertFalse($this->_prj->validate());
            $this->assertFalse($this->_prj->validate());
        });
        $this->specify('project validation pass', function() {
            $this->_prj->name = 'prj122';
            $this->_prj->user_id = 1;
            $this->_prj->label = "prj_newwqe1";
            $this->_prj->contact_email = "prj1_tes@360.cn";

            $this->assertTrue($this->_prj->validate());
        });

    }

    public function testSavePrj(){
        $this->_prj = new \app\models\ProjectForm();

        $this->_prj->name = 'prj1234';
        $this->_prj->user_id = 1;
        $this->_prj->label = "prj1_labe234l";
        $this->_prj->contact_email = "prj1_test@360.cn";
        $this->_prj->host_names =["www.163.com"];
        $this->_prj->save();
        $this->tester->seeRecord('app\models\ProjectForm', [
            'name' => $this->_prj->name,
            'user_id' => $this->_prj->user_id,
            'label' => $this->_prj->label,
            'contact_email' => $this->_prj->contact_email,
        ]);
    }

    public function testModifyPrj(){

        $this->_prj = \app\models\ProjectForm::find()
            ->where(['name' => 'prj1234'])
            ->limit(1)
            ->one();
        $originalName = $this->_prj->name;
        $originaluserId = $this->_prj->user_id;
        $originalLabel = $this->_prj->label;
        $originalContactEmail = $this->_prj->contact_email;


        $this->_prj->name = "prj_newwqe1";
        $this->_prj->user_id = 1;
        $this->_prj->label = "label_324nesdfdswsdf";
        $this->_prj->contact_email = "prj_new@360.cn";
        $this->_prj->host_names =["www.163344asdsa.com"];

        $this->_prj->save();

        $this->tester->seeRecord('app\models\ProjectForm', [
            'name' => $this->_prj->name,
            'user_id' => $this->_prj->user_id,
            'label' => $this->_prj->label,
            'contact_email' => $this->_prj->contact_email,

        ]);
        $this->tester->dontSeeRecord('app\models\ProjectForm', [
            'name' => $originalName,
            'user_id' => $originaluserId,
            'label' =>$originalLabel,
            'contact_email' => $originalContactEmail,
        ]);
    }
}