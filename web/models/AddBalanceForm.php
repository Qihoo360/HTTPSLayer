<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/10/30
 * Time: 18:23
 */

namespace app\models;


/**
 * Class AddBalanceForm
 * @package app\models
 */
class AddBalanceForm extends Project
{

    private $_project_balances = [];
    const pb_prefix = "project_balances_";

    /**
     * AddBalanceForm constructor.
     */
    public function __construct()
    {
        $idcs = \Constant::getIdcs();
        foreach ($idcs as $idc) {
            $this->_project_balances[self::pb_prefix . $idc] = [];
        }
        parent::__construct();
    }


    public function rules()
    {
        $idcs = \Constant::getIdcs();
        $idc_in_safe_rules = [];
        foreach ($idcs as $idc) {
            $idc_in_safe_rules[] = self::pb_prefix . $idc;
        }
        if (!empty($idc_in_safe_rules)) {
            $idc_in_safe_rules_statement = [
                [
                    $idc_in_safe_rules,
                    'safe'
                ],
            ];
        } else {
            $idc_in_safe_rules_statement = [];
        }

        return parent::rules() + $idc_in_safe_rules_statement;
    }

    public function attributeLabels()
    {
        $qfe_idc = \Constant::getQfeIdc();
        $idc_labels = [];
        foreach ($qfe_idc as $_idc => $_name) {
            $idc_labels[self::pb_prefix . $_idc] = "接入层集群" . $_name;
        }
        return parent::attributeLabels() + $idc_labels;
    }


    public function save($runValidation = true, $attributeNames = null)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        Context::$global_message = "";
        try {
            $idcs = \Constant::getIdcs();
            foreach ($idcs as $_qfe_idc) {
                if (!empty($this->{self::pb_prefix . $_qfe_idc})) {
                    $existed_balances = [];
                    if (!empty($this->balances)) {
                        foreach ($this->balances as $_balance) {
                            if ($_balance->qfe_idc != $_qfe_idc) { // 只处理同一个机房的记录
                                continue;
                            }
                            /**
                             * @var $_balance Balance
                             */
                            $existed_balances[$_balance->vip . "~" . $_balance->location] = $_balance;
                        }
                    }
                    $total_weight = 0;
                    foreach ($this->{self::pb_prefix . $_qfe_idc} as $project_balance) {
                        $total_weight += intval($project_balance['weight']);
                    }

                    if ($total_weight != 100) {
                        Context::$global_message = "{$_qfe_idc}下所有vip的权重之和应该是100,请修正数据";
                        $transaction->rollBack();
                        return false;
                    }


                    foreach ($this->{self::pb_prefix . $_qfe_idc} as $project_balance) {
                        $location = \Utils::xssStrip($project_balance['location']);
                        $vip = \Utils::xssStrip($project_balance['vip']);
                        $weight = intval($project_balance['weight']);
                        if (isset($existed_balances[$vip . "~" . $location])) { // 库里已有的, 并且在本次更新列表的 更新权重
                            $balance = $existed_balances[$vip . "~" . $location];
                            unset($existed_balances[$vip . "~" . $location]); // 从库里有的中,去除在本次更新列表里的, 剩下的需要被删除。
                            if ($balance->weight != $weight) {
                                $balance->weight = $weight;
                                $balance->save();
                            } else {
                                continue;
                            }
                        } else { // 库里没有的, 但是在本次更新列表里的, 添加。
                            $balance = new Balance();
                            $balance->location = $location;
                            $balance->project_id = $this->id;
                            $balance->vip = $vip;
                            $balance->weight = $weight;
                            $balance->qfe_idc = $_qfe_idc;
                            $balance->save(false);
                        }
                    }

                    foreach ($existed_balances as $_balance) { // 将库里存在的,不在本次更新列表里的 删除。
                        $_balance->delete();
                    }
                } else { // 本次更新没有此机房的负载均衡
                    if (!empty($this->balances)) {
                        foreach ($this->balances as $_balance) {
                            if ($_balance->qfe_idc == $_qfe_idc) { // 只处理同一个机房的记录
                                $_balance->delete();
                            }
                        }
                    }
                }
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            Context::$global_message = "数据库出错,本次操作回滚中";
            $transaction->rollBack();
            return false;
        }

    }

    public function __get($name)
    {
        $idcs = \Constant::getIdcs();
        $idc = self::pb2Idc($name);
        if (in_array($idc, $idcs)) {
            return isset($this->_project_balances[$name]) ? $this->_project_balances[$name] : [];
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        $idcs = \Constant::getIdcs();
        $idc = self::pb2Idc($name);
        if (in_array($idc, $idcs)) {
            $this->_project_balances[$name] = $value;
            return;
        }
        parent::__set($name, $value);
    }

    public function __isset($name)
    {
        $idcs = \Constant::getIdcs();
        $idc = self::pb2Idc($name);
        if (in_array($idc, $idcs)) {
            return true;
        }
        return parent::__isset($name);
    }

    public function __unset($name)
    {
        $idcs = \Constant::getIdcs();
        $idc = self::pb2Idc($name);
        if (in_array($idc, $idcs)) {
            $this->_project_balances[$name] = [];
            return;
        }
        parent::__unset($name);
    }

    public static function pb2Idc($name)
    {
        return str_replace(self::pb_prefix, "", $name);
    }
}