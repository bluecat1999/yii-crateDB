#####<?php
//============= ===========
//CrateDB Type  PHP Type
//============= ===========
//`boolean`__   `boolean`__
//`byte`__
//`short`__     `integer`__
//`integer`__   `integer`__
//`long`__      `integer`__
//`float`__     `float`__
//`double`__    `float`__
//`string`__    `string`__
//`ip`__        `string`__
//`timestamp`__ `integer`__
//`geo_point`__ `array`__
//`geo_shape`__ `object`__
//`object`__    `object`__
//`array`__     `array`__
//============= ===========
//require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ .'/yiicmd');

require_once (__DIR__ . '/Crate/autoload.php');
use Crate\PDO\PDO as PDO;
// use common\lang\Consts;
use Crate\yii\CrateConnection;

//   $dsn = 'crate:localhost:4201/b2b';
//   $user = 'crate';
//   $password = null;
//   $options = [PDO::CRATE_ATTR_DEFAULT_SCHEMA=>'b2b',PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
//   $options = [PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
//   $connection = new PDO($dsn,$user,$password,$options);
//   $statement = $connection->prepare("SELECT sum(goods_amount),b.member_name  FROM bd_order a inner join md_member b on a.member_id=b.member_id group by b.member_id,b.member_name LIMIT 100");
////   var_dump($statement);
//   $statement->execute();
//   var_dump($statement->fetchAll());
//    $result = $connection->query("SELECT * FROM bd_order LIMIT 100");
 //   var_dump($result->fetchAll());
# new EsConnection
//    $connection = new EsConnection(['dsn'=>$dsn]);
//    $result = $connection->createCommand("SELECT * from bd_order limit 100")->queryAll();
//    var_dump($result);
#
//    var_dump(Yii::$app->elastic);
//var_dump(Yii::$app->get('elastic'));

//    try{
# global
//     $reader =  Yii::$app->elastic;
//     var_dump($reader->createCommand("SELECT sum(goods_amount),b.member_name  FROM bd_order a inner join md_member b on a.member_id=b.member_id group by b.member_id,b.member_name LIMIT 100")->queryAll());
//     $reader = Consts::esreader();
//    } catch(\Exception $e){
//        var_dump($e->getMessage());
//    }
//     $result = Consts::esreader()->query("SELECT * from bd_order limit 1000");
/*
echo ' start:' . memory_get_usage().PHP_EOL;
$result = Consts::esreader()->query("SELECT sum(goods_amount) as goods_amounts ,b.sales_staff_id  FROM bd_order a inner join md_member b on a.member_id=b.member_id group by b.sales_staff_id order by goods_amounts desc LIMIT 10000");
echo '1 search:' . memory_get_usage().PHP_EOL;
//var_dump($result);
//echo memSize($result).PHP_EOL;
unset($result);
echo '1.2 free result:'. memory_get_usage().PHP_EOL;

echo '2.0 start:'.memory_get_usage().PHP_EOL;
$result = Consts::esreader()->query("SELECT sum(goods_amount) as goods_amounts ,b.sales_staff_id  FROM bd_order a inner join md_member b on a.member_id=b.member_id group by b.sales_staff_id order by goods_amounts desc LIMIT 10");
echo '2.1 search:' .memory_get_usage().PHP_EOL;
//echo memSize($result).PHP_EOL;
//var_dump($result);
unset($result);
echo '2.2 finish:'.memory_get_usage().PHP_EOL;
*/
#schema and columnschema
#$db = Consts::esreader()->db;
#
$db = \Yii::$app->get('CrateConnection');
$schema = $db->getSchema();
var_dump($db->createcommand('SELECT * FROM doc.my_table1')-queryAll());
//$tablesName =($schema->getTableNames('b2b'));
//$schemas = ($schema->getSchemaNames());
//$tableSchema = $schema->getTableSchema('doc.my_table1');
//var_dump($tableSchema);
//$sql = "insert into doc.my_table1 (f1,f2,f3,f4) values(true,'192.168.0.201',{\"age\"='20190201',\"name\"='shaha'},['first','second'])";
//$db->createCommand($sql)->execute();
//var_dump(Consts::esreader()->query("SELECT * FROM doc.my_table1"));
//var_dump(Consts::esreader()->getListMapKey("SELECT * FROM bd_order limit 10","order_id"));
//var_dump(\Yii::$app->db);
$writer = $db;
$f =  new stdClass();
$f->age='20100201';
$f->name ='shasha';
$arr = [ '12222','22222'];
$writer->createcommand()->insert("doc.my_table1",['f1'=>false,'f2'=>'192.172.0.200','f4'=>$arr],false)->execute();
function memSize($a){
    $serializedFoo = serialize($a);
    if (function_exists('mb_strlen')) {
        $size = mb_strlen($serializedFoo, '8bit');
    } else {
        $size = strlen($serializedFoo);
    }
//    unset($serializedFoo);
    return $size;
}
