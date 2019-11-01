<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "frequency".
 *
 * @property integer $id
 * @property integer $project_id
 * @property string $path
 * @property string $description
 * @property integer $according
 * @property string $method
 * @property string $cookie_name
 * @property string $time_window
 * @property string $referer
 * @property string $arguments
 * @property string $white_ip
 * @property string $black_ip
 * @property integer $handle_way
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 * @property int $create_user
 * @property int $update_user
 * @property int $update_operation
 *
 * @property array $time_window_arr
 */
class Frequency extends \yii\db\ActiveRecord
{
    //限频依据 according  1 ip + cookie 2 只是ip  3 只是cookie
    const AC_IP_COOKIE = 0;
    const AC_IP = 1;
    const AC_COOKIE = 2;

    public static $accordingToArray = [
        self::AC_IP_COOKIE => 'IP+Cookie',
        self::AC_IP => '仅IP',
//        self::AC_COOKIE => '仅Cookie',
    ];

    // 所有状态 status  0 删除  1 未开启 2 开启
    const STATUS_DELETED = 0;
    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 2;

    public static $statusArray = [
        self::STATUS_DELETED => '已删除',
        self::STATUS_OPEN => '已启用',
        self::STATUS_CLOSE => '未启用',
    ];

    // 所有处理方式 handleWay  1 只记日志 2 验证码  3 业务处理
    const HD_LOG_ONLY = 1;
    const HD_VERIFY_CODE = 2;
    const HD_HEADER_MARK = 3;

    public static $handleWayArray = [
        self::HD_LOG_ONLY => '只记日志',
        self::HD_VERIFY_CODE => '验证码',
        self::HD_HEADER_MARK => '业务处理',
    ];

    public static $handleWayArrayEn = [
        self::HD_LOG_ONLY => 'recordLog',
        self::HD_VERIFY_CODE => 'verificationCode',
        self::HD_HEADER_MARK => 'buildProjectTag',
    ];

    // 更新操作
    const OP_CREATE = 1;
    const OP_UPDATE = 2;
    const OP_DELETE = 3;
    const OP_ONLINE = 4;
    const OP_OFFLINE = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'frequency';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'according', 'handle_way', 'status', 'create_user', 'update_user', 'update_operation'], 'integer'],
            [['time_window', 'path', 'description', 'method'], 'required'],
            [['description', 'time_window', 'referer', 'arguments', 'white_ip', 'black_ip'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['path', 'method', 'cookie_name', 'description'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => '项目id',
            'path' => '路径',
            'description' => '路径描述',
            'according' => '限频依据',
            'method' => '允许的Method',
            'cookie_name' => 'Cookie名称',
            'time_window' => '时间窗口',
            'referer' => '允许来源',
            'arguments' => '必含参数',
            'white_ip' => 'IP白名单',
            'black_ip' => 'IP黑名单',
            'handle_way' => '处理方式',
            'status' => '状态',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'create_user' => '创建用户',
            'update_user' => '更新用户',
            'update_operation' => '更新操作',
        ];
    }


    /**
     * 格式化Frequency的字段
     * @param Frequency $model
     * @return object Frequency
     */
    public static function formatData($model)
    {
        $model->method = implode(';', $model->method);
        $time_window = (array)$model->time_window;
        foreach($time_window as $t) {
            if (!preg_match('/^[0-9]+$/', $t['interval']) || !preg_match('/^[0-9]+$/', $t['count'])) {
                $model->addError('time_window', '请输入正确的格式, 只允许数字');
            }
        }

        $model->time_window = json_encode($time_window);
        return $model;
    }


    /**
     * 发布项目的所有path
     * @param $project_id
     * @return bool
     */
    public function releaseByProjectId($project_id)
    {
        $project_id = intval($project_id);
        $project = Project::findOne($project_id);
        if (empty($project)) {
            return false;
        }

        $openStatus = self::STATUS_OPEN;
        $pathList = self::find()->where([
            "project_id" => $project_id,
            "status" => $openStatus]
        )->asArray()->all();

        //path
        $path = [];
        foreach ($pathList as $key => $row) {
            // 格式化时间窗口
            $tm_arr = json_decode($row['time_window'], true);
            $time_arr = ['s' => 1, 'm' => 60, 'h' => 3600, 'd' => 86400];
            $limit = [];
            foreach ($tm_arr as $k1 => $l1) {
                $interval = isset($time_arr[$l1['unit']]) ? $time_arr[$l1['unit']] * intval($l1['interval']) : null;
                if (empty($interval)) {
                    continue;
                }
                $limit[] = [
                    'seconds' => $interval,
                    'count' => intval($l1['count']),
                ];
            }

            // 处理方式
            $handling = self::$handleWayArrayEn[$row['handle_way']];

            // 依据
            $id = [
                'ip' => true,
                'cookie' => $row['cookie_name'],
            ];

            // method
            $method = empty(trim($row['method'])) ? [] : explode(';', $row['method']);

            // referer
            $referer = empty(trim($row['referer'])) ? [] : explode("\r\n", $row['referer']);

            // 允许的get 参数
            $get_params = empty(trim($row['arguments'])) ? [] : explode("\r\n", $row['arguments']);

            // ip_white_list
            $ip_white_list = empty(trim($row['white_ip'])) ? [] : explode("\r\n", $row['white_ip']);

            // ip black list
            $ip_black_list = empty(trim($row['black_ip'])) ? [] : explode("\r\n", $row['black_ip']);

            // 生成path数组
            $path[] = [
                'path' => $row['path'],
                'method' => array_combine($method, $method),
                'id' => $id,
                'limit' => $limit,
                'referer' => array_combine($referer, $referer),
                'get' => $get_params,
                'ip_white_list' => array_combine($ip_white_list, $ip_white_list),
                'ip_black_list' => array_combine($ip_black_list, $ip_black_list),
                'handling' => $handling,
            ];
        }

        // project_info
        $project_label = $project->label;
        $project_host = ProjHost::find()->where(["project_id" => $project_id])->select('name')->asArray()->all();
        foreach ($project_host as $h) {
            $db = RedisQfe::DB_FREQUENCYCONFIG_FILE;
            $key = ProjectForm::QFE_CAPTCHE_PREFIX. $h['name'];
            $value = RedisQfe::getInstance()->get($db, $key);
            $hosts[$h['name']] = empty($value) ? [] : json_decode($value, true);
        }

        // 格式化结构
        $data = [
            'project_id' => $project_id,
            'project_label' => $project_label,
            'domain' => $hosts,
            'path' => $path,
        ];

        $model = new FrequencyVersion();
        $version = $project_id . microtime(true);
        $model->project_id = $project_id;
        $model->project_label = $project_label;
        $model->data = json_encode($data);
        $model->version = $version;
        $user_name = Context::getInstance()->bizUser()->name;
        $user_email = Context::getInstance()->bizUser()->email;
        $model->online_user = "{$user_name}({$user_email})";

        // 上线
        $online_result = $model->onlineNewVersion();

        return $online_result;
    }

}
