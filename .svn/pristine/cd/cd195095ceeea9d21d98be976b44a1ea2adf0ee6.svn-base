<?php
/**
 * @Author: anchen
 * @Date:   2018-11-01 15:23:37
 * @Last Modified by:   anchen
 * @Last Modified time: 2018-11-01 17:06:42
 */
namespace app\models\base;

use yii\db\ActiveRecord;
use yii\db\QueryInterface;
use yii\base\ModelEvent;
use app\models\TablesChangeLog;

class BaseModel extends ActiveRecord
{
    /**
     * @desc 系统用户常量
     * @var unknown
     */
    const SYSTEM_USER = 'system';
    const EVENT_DELETE_BEFORE = 'deletebefore';
    const EVENT_DELETE_AFTER = 'deleteafter';
    /**
     * @desc 观察者列表
     * @var Array
     */
    protected $observers = [];

    public static $tableChangeLogEnabled = true;          //是否记录数据表修改日志

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'beforeInserEvent']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'beforeUpdateEvent']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'afterInsertEvent']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, 'afterUpdateEvent']);
        $this->on(self::EVENT_DELETE_BEFORE, [$this, 'beforeDeleteEvent']);
        $this->on(self::EVENT_DELETE_AFTER, [$this, 'afterDeleteEvent']);
    }
    /**
     * @desc 自动填充创建人，创建时间
     * @param unknown $event
     */
    public function beforeInserEvent($event)
    {
        if (isset(\Yii::$app->user)) {
            $user = \Yii::$app->user->getIdentity();
            $createBy = '';
            if ($user != null) {
                $createBy = $user->username;
            }
        } else {
            $createBy = self::SYSTEM_USER;
        }
        $modifyBy = $createBy;
        $time = date('Y-m-d H:i:s');
        if ($this->hasAttribute('create_by') && $this->create_by === null) {
            $this->create_by = $createBy;
        }
        if ($this->hasAttribute('create_time') && $this->create_time === null) {
            $this->create_time = $time;
        }
        if ($this->hasAttribute('modify_by') && $this->modify_by === null) {
            $this->modify_by = $modifyBy;
        }
        if ($this->hasAttribute('modify_time') && $this->modify_time === null) {
            $this->modify_time = $time;
        }
    }

    /**
     * @desc 自动填充修改人，修改时间
     * @param unknown $event
     */
    public function beforeUpdateEvent($event)
    {
        $createBy = self::SYSTEM_USER;
        $modifyBy = self::SYSTEM_USER;
        if (isset(\Yii::$app->user)) {
            $user = \Yii::$app->user->getIdentity();
            if ($user != null) {
                $createBy = $user->username;
                $modifyBy = $user->username;
            }
        }
        if ($this->hasAttribute('modify_by') &&
            $this->oldAttributes['modify_by'] == $this->attributes['modify_by']) {
            $this->modify_by = $modifyBy;
        }
        if ($this->hasAttribute('modify_time') &&
            $this->oldAttributes['modify_time'] == $this->attributes['modify_time']) {
            $this->modify_time = date('Y-m-d H:i:s');
        }
        if (static::$tableChangeLogEnabled) {
            $this->save_tables_change_log(TablesChangeLog::CHANGE_TYPE_UPDATE);
        }
    }

    public function beforeDeleteEvent($event)
    {
        if (isset(\Yii::$app->user)) {
            $user = \Yii::$app->user->getIdentity();
            $createBy = '';
            if ($user != null) {
                $createBy = $user->username;
            }
        } else {
            $createBy = self::SYSTEM_USER;
        }
        $modifyBy = $createBy;
        $time = date('Y-m-d H:i:s');
        if ($this->hasAttribute('create_by') && $this->create_by === null) {
            $this->create_by = $createBy;
        }
        if ($this->hasAttribute('create_time') && $this->create_time === null) {
            $this->create_time = $time;
        }
        if ($this->hasAttribute('modify_by') && $this->modify_by === null) {
            $this->modify_by = $modifyBy;
        }
        if ($this->hasAttribute('modify_time') && $this->modify_time === null) {
            $this->modify_time = $time;
        }
        
         if (static::$tableChangeLogEnabled)
            $this->save_tables_change_log(TablesChangeLog::CHANGE_TYPE_DELETE);
        //throw new \Exception("Value must be 1 or below"); 
        //var_dump($this->attributes);die();
        //var_dump(self::getTableSchema());die();
    }

    public function afterDeleteEvent($event)
    {
        if (static::$tableChangeLogEnabled)
            $this->save_tables_change_log(TablesChangeLog::CHANGE_TYPE_DELETE);
    }

    public function afterInsertEvent($event)
    {
        if (static::$tableChangeLogEnabled)
            $this->save_tables_change_log(TablesChangeLog::CHANGE_TYPE_INSERT);
    }

    public function afterUpdateEvent($event)
    {
        //$this->save_tables_change_log(TablesChangeLog::CHANGE_TYPE_UPDATE);
    }

    /**
     * 存取系统数据表变动日志的公共方法
     * @param int $change_type 变动类型(1insert，2update，3delete)
     */
    public function save_tables_change_log($change_type, $content = '')
    {
        $table_name = $this->getTableSchema()->name;
        $change_content = $content;
        if (empty($content)) {
            $change_content = $this->get_content_by_change_type($change_type);
        }
        TablesChangeLog::save_tables_change_log_data($table_name, $change_type, $change_content);
    }
    /**
     * @desc 根据变动类型组装变动的内容描述
     * @param int $change_type 变动类型(1insert,2update,3delete)
     */
    public function get_content_by_change_type($change_type)
    {
        $content_prefix = TablesChangeLog::get_change_type_data($change_type) . ":";

        if ($change_type == TablesChangeLog::CHANGE_TYPE_INSERT) {
            $id = isset($this->id) ? $this->id : null;
            $content = isset($id) ? '新增id值为' . $id . '的记录' : '新增记录请看结合表名和日志新增时间';
            return $content_prefix . $content;
        }

        if ($change_type == TablesChangeLog::CHANGE_TYPE_DELETE) {
            $id = isset($this->id) ? $this->id : null;
            $content = isset($id) ? '删除id值为' . $id . '的记录' : '删除记录请看结合表名和日志新增时间';
            return $content_prefix . $content;
        }

        if ($change_type == TablesChangeLog::CHANGE_TYPE_UPDATE) {
            list($update_after_data, $update_before_data, $content) = [$this->attributes, $this->oldattributes, $content_prefix];
            foreach ($update_before_data as $key => $value) {
                if ($value != $update_after_data[$key]) {
                    $content = $content . $key . ":" . $value . "=>" . $update_after_data[$key] . ",";
                }
            }
            return $content;
        }

        return null;
    }
    /**
     * 覆写yii2里面的beforeDelete绑定自定义的事件名称
     */
    public function beforeDelete()
    {
        $event = new ModelEvent;
        $this->trigger(self::EVENT_DELETE_BEFORE, $event);

        return $event->isValid;
    }
}