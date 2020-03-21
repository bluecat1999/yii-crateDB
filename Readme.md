## Yii crate(elastic)db Connection class

#####描述:
提供crateDB的一个Connection类

creteDB,elastic 的SQL版本，参见https://crate.io/．

crateDB PDO 来自https://github.com/crate/crate-pdo, downgrade it for adapting to  php 5.5．

#####配置：
１ common/config/bootstrap.php 或者　common/config/bootstrap-local.php　中添加:

        require_once (dirname(__DIR__) . '/cratedb/yii/autoload.php');
   or
   composer require crateDB
        
 2 common/config/main-local.php 增加crateDB如:
        
        ...
        'cratedb' => [
                    'class' => 'Crate\yii\CrateConnection',
                    'dsn' => 'crate:localhost:4201/b2b',
                    'charset' => 'utf8',
                ],
        ...
#####示例：

        ...

        $sql = "SELECT sum(goods_amount) as goods_amounts ,date_format('%Y%m%d',max(a.created)) as last_created,b.sales_staff_id  FROM bd_order a inner
         join md_member b on a.member_id=b.member_id group by b.sales_staff_id order by goods_amounts desc LIMIT 1000";
        $result = \Yii::$app->get('cratedb')->query($sql);
        foreach($result as $v){
            ....
        }
        ....
 
####历史:
１.2019-06-04 query完成，原来查询(支持sql99语法的)sql语句基本无需改变；

２.2019-06-23 mysqlToEs 数据迁移,同步完成,目前迁移同步的有bd_order,md_member两个表,兼容SQL 99

语法,支持以下数据类型:

        ============= ===========
           DB Type 对应 PHP Type
        ============= ===========
        `boolean`__   `boolean`__
        `byte`__
        `short`__     `integer`__
        `integer`__   `integer`__
        `long`__      `integer`__
        `float`__     `float`__
        `double`__    `float`__
        `string`__    `string`__
        `ip`__        `string`__
        `timestamp`__ `integer`__
        `geo_point`__ `array`__
        `geo_shape`__ `object`__
        `object`__    `object`__
        `array`__     `array`__
        ============= ===========
  
  故原mysql中的date,time,datetime,timestamp迁移后转为timestamp类型,timestamp类型字段输出时可使用date_format函数,100%兼容mysql的
  date_format函数
３.不支持事务及批量增删改操作

  