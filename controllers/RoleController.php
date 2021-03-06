<?php
/**
 * Created by PhpStorm.
 * User: afellow
 * Date: 2017/12/21
 * Time: 10:38
 */

namespace app\controllers;


use app\controllers\common\BaseController;
use app\models\Role;
use app\services\UrlService;
use app\models\Access;
use app\models\RoleAccess;

class RoleController extends BaseController
{
    //角色列表页面
    public function actionIndex()
    {
        $list = Role::find()->orderBy(['id'=> SORT_DESC])->all();
        return $this->render('index',['list'=>$list]);
    }

    /**
     * 添加角色页面
     * get  展示页面
     * post 处理添加动作
     * @return string
     */
    public function actionSet()
    {
        $dateNow =  date('Y-m-d H:i:s');
        if(\Yii::$app->request->isGet)
        {
            $id = $this->get('id',0);
            $info = [];
            if ( $id )
            {
               $info =  Role::find()->where( [ 'id'=>$id ] )->one();
            }
            return $this->render('set',[ 'info'=>$info ]);
        }
        $name = $this->post("name",'');
        $id   = $this->post('id',0);
        if ( !$name )
        {
            return $this->renderJSON([],'请输入角色名称~~',-1);
        }
        //查询角色是否存在角色相等的记录
        $role_info = Role::find()->where( [ 'name'=>$name ] )->andWhere( [ '!=','id',$id ] )->one();
        if( $role_info )
        {
            return $this->renderJSON([],'该角色名称已存在，请输入其他的角色名称~~',-1);
        }
        $info = Role::find()->where(['id'=>$id])->one();
        if ( $info )
        {//编辑
            $role_mdoel = $info;
        }else{//添加
            $role_mdoel  = new Role();
            $role_mdoel->created_time = $dateNow;
        }
        $role_mdoel->name = $name;
        $role_mdoel->updated_time = $dateNow;
        $role_mdoel->save(0);
        return $this->renderJSON([],'操作成功~~',200);
    }

    public function actionAccess()
    {
        //http get 请求 展示页面
        if( \Yii::$app->request->isGet ){
            $id = $this->get("id",0);
            $reback_url =  UrlService::buildUrl("/role/index");
            if( !$id ){
                return $this->redirect( $reback_url );
            }
            $info = Role::find()->where([ 'id' => $id ])->one();
            if( !$info ){
                return $this->redirect( $reback_url );
            }

            //取出所有的权限
            $access_list = Access::find()->where([ 'status' => 1 ])->orderBy( [ 'id' => SORT_DESC ])->all();

            //取出所有已分配的权限
            $role_access_list = RoleAccess::find()->where([ 'role_id' => $id ])->asArray()->all();
            $access_ids = [];
            foreach ($role_access_list as $val)
            {
                $access_ids[] = $val['access_id'];
            }
            return $this->render("access",[
                "info" => $info,
                'access_list' => $access_list,
                "access_ids" => $access_ids
            ]);
        }
        //实现保存选中权限的逻辑
        $id = $this->post("id",0);
        $access_ids = $this->post("access_ids",[]);

        if( !$id ){
            return $this->renderJSON([],"您指定的角色不存在",-1);
        }

        $info = Role::find()->where([ 'id' => $id ])->one();
        if( !$info ){
            return $this->renderJSON([],"您指定的角色不存在",-1);
        }

        //取出所有已分配给指定角色的权限
        $role_access_list = RoleAccess::find()->where([ 'role_id' => $id ])->asArray()->all();
        $assign_access_ids = [];
        foreach ($role_access_list as $val)
        {
            $assign_access_ids[] = $val['access_id'];
        }
        //$assign_access_ids = array_column( $role_access_list,'access_id' );
        /**
         * 找出删除的权限
         * 假如已有的权限集合是A，界面传递过得权限集合是B
         * 权限集合A当中的某个权限不在权限集合B当中，就应该删除
         * 使用 array_diff() 计算补集
         */
        $delete_access_ids = array_diff( $assign_access_ids,$access_ids );
        if( $delete_access_ids ){
            RoleAccess::deleteAll([ 'role_id' => $id,'access_id' => $delete_access_ids ]);
        }

        /**
         * 找出添加的权限
         * 假如已有的权限集合是A，界面传递过得权限集合是B
         * 权限集合B当中的某个权限不在权限集合A当中，就应该添加
         * 使用 array_diff() 计算补集
         */
        $new_access_ids = array_diff( $access_ids,$assign_access_ids );
        if( $new_access_ids ){
            foreach( $new_access_ids as $_access_id  ){
                $tmp_model_role_access = new RoleAccess();
                $tmp_model_role_access->role_id = $id;
                $tmp_model_role_access->access_id = $_access_id;
                $tmp_model_role_access->created_time = date("Y-m-d H:i:s");
                $tmp_model_role_access->save( 0 );
            }
        }
        return $this->renderJSON([],"操作成功~~",200 );
        
    }

}