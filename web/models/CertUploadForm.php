<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/10/27
 * Time: 16:58
 */

namespace app\models;


use Codeception\Module\Redis;
use PHPUnit\Framework\Exception;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * 用于证书上传
 * Class CertUploadForm
 * @package app\models
 * @property $pub_file
 * @property $priv_file
 */
class CertUploadForm extends Model
{
    public $pub_file;

    public $priv_file;

    private $upload_dir = "";

    public function rules()
    {
        return [
            [['pub_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'crt', 'checkExtensionByMimeType' => false],
            [['priv_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'key', 'checkExtensionByMimeType' => false],
        ];
    }

    /**
     * @param $model Certificate
     * @return bool
     */
    public function upload(&$model)
    {
        $dir = \Utils::getUploadedDir();
        $disk_name = "tmp-" . time() . "-" . mt_rand(1000,9999);
        $pub_content = $this->saveUploadedFile($this->pub_file, $disk_name . ".crt");
        if ($pub_content) { // 本地文件存储成功,用户上传了新的文件
            $model->pub_content = $pub_content;
            $model->initParsePubContent(); // 上传新的公钥需要重做解析并赋值
            if (!empty($model->serial_no)) {
                $key_pairs = $model->fileCacheKey();
                $model->pub_key = $key_pairs['pub'];
                $model->priv_key = $key_pairs['priv'];
            }
            if ($model->pub_key) {
                RedisQfe::getInstance()->set(RedisQfe::DB_CERTIFICATE_FILE, $model->pub_key, $model->pub_content);
            }
            unlink($dir . "/" . $disk_name . ".crt");
        }

        $priv_content = $this->saveUploadedFile($this->priv_file, $disk_name . ".key");
        if (!empty($priv_content)) { // 用户上传了新的文件
            $model->priv_content = $priv_content;
            if ($model->priv_key) {
                RedisQfe::getInstance()->set(RedisQfe::DB_CERTIFICATE_FILE, $model->priv_key, $model->priv_content);
            }
            unlink($dir . "/" . $disk_name . ".key");
        }

        return !empty($model->pub_content) && !empty($model->priv_content);
    }

    /**
     * 上传的文件存储到本地
     * @param \yii\web\UploadedFile $uploaded_file
     * @param string $disk_file_name 存储的文件名
     * @return bool|string
     */
    public function saveUploadedFile($uploaded_file, $disk_file_name)
    {
        $dir = \Utils::getUploadedDir();
        try {
            if ($uploaded_file && $this->validate()) {
                if (!file_exists($dir)) {
                    \Utils::mkdirs($dir);
                }
                $disk_success = $uploaded_file->saveAs($dir . "/" . $disk_file_name);
                if ($disk_success) {
                    $content = file_get_contents($dir . "/" . $disk_file_name);
                    if (!empty($content)) {
                        return $content;
                    }
                }
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * 获取配置的文件上传目录
     * @return string
     */
    public function getUploadDir()
    {
        return $this->upload_dir;
    }
}