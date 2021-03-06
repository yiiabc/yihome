<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;
use app\services\UrlService;
use yii\web\AssetBundle;
/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
   /* public $css = [
        'bootstrap/css/bootstrap.min.css',
    ];
    public $js = [
        'bootstrap/js/bootstrap.min.js',
        'jquery/jquery-3.2.1.min.js',
    ];*/

    public function registerAssetFiles( $view )
    {
        $release = '20171213';
        $this->css = [
            UrlService::buildUrl( "bootstrap/css/bootstrap.min.css",[ 'v' => $release ]),
            UrlService::buildUrl( "./css/app.css")
        ];
        $this->js = [
            UrlService::buildUrl( "bootstrap/js/bootstrap.min.js" ),
            UrlService::buildUrl( "jquery/jquery-3.2.1.min.js" ),
        ];
        parent::registerAssetFiles( $view );
    }

}
