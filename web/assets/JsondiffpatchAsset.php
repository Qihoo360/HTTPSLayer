<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/11/1
 * Time: 11:43
 */

namespace app\assets;


use yii\web\AssetBundle;
use yii\web\View;

class JsondiffpatchAsset extends AssetBundle
{
    public $sourcePath = '@bower/jsondiffpatch/public';

    public $css = [
        "formatters-styles/html.css",
        "formatters-styles/annotated.css",
    ];
    public $js = [
        "build/jsondiffpatch.min.js",
        "build/jsondiffpatch-formatters.min.js",
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD
    ];

}