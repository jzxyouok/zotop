<?php
defined('ZOTOP') or die('No direct access allowed.');
/**
 * array数组操作类
 *
 * @copyright  (c)2009 zotop team
 * @package    zotop
 * @author     zotop team
 * @license    http://zotop.com/license.html
 */
class arr
{
    public static $tree = array();

    /**
     * 键名转路径
     *
     * data[name] => data.name
     * 
     * @param  string $path 键名路径
     * @param  string $delimiter 原始分隔符
     * @return string 格式化后的路径
     */
    public static function key_path($path, $delimiter=null)
    {
        $path = str_replace(array('[*]', '[', ']'), array('*', '.', ''), $path);

        if ( $delimiter )
        {
            $path = str_replace($delimiter, '.', $path);
        }

        return $path;
    }


    /**
     * 分解数组为键名和键值数组
     *
     * @param  array  $array
     * @return array
     */
    public static function divide($array)
    {
        return array(array_keys($array), array_values($array));
    }

    /**
     * 用第二个数组中的数据覆盖第一个数组中同键名的数据
     * 与merge的区别是第一个数组中并不存在的键不会被添加
     * <code>
     *     // 例如：
     *     $a1 = array('name' => 'john', 'mood' => 'happy', 'food' => 'bacon');
     *     $a2 = array('name' => 'jack', 'food' => 'tacos', 'drink' => 'beer');
     *
     *     // 操作：
     *     $array = arr::overwrite($a1, $a2);
     *
     *     // 结果：
     *     $array = array('name' => 'jack', 'mood' => 'happy', 'food' => 'tacos')
     *
     *  </code>
     *
     * @param   array   $array1 数组
     * @param   array   $array2 数组
     * @return  array
     */
    public static function overwrite($array1, $array2)
    {
        foreach (array_intersect_key($array2, $array1) as $key => $value)
        {
            $array1[$key] = $value;
        }

        if (func_num_args() > 2)
        {
            foreach (array_slice(func_get_args(), 2) as $array2)
            {
                foreach (array_intersect_key($array2, $array1) as $key => $value)
                {
                    $array1[$key] = $value;
                }
            }
        }

        return $array1;
    }       

    /**
     * 多维数组检查键名是否存在，运行使用点符号分割
     *
     * @param  array   $array
     * @param  string  $key
     * @return bool
     */
    public static function has($array, $key)
    {
        if ( empty($array) || is_null($key) )
        {
            return false;
        }

        if ( array_key_exists($key, $array) )
        {
            return true;
        }

        foreach ( explode('.', $key) as $segment )
        {
            if ( !is_array($array) || !array_key_exists($segment, $array) ) 
            {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }

    /**
     *  从数组或者多维数组中按照键名路径返回值
     *
     *     // 获取 $array['foo']['bar']
     *     $value = arr::get($array, 'foo.bar');
     *
     *     // 使用 * 可以进行搜索，并返回一个数组
     *     // 获取 $array['foo'][*]['bar']
     *
     *     $colors = arr::get($array, 'themes.*.name');
     *
     * @param   array   $array      原数组
     * @param   mixed   $path       键名路径(可使用点符号分割和*号搜索)
     * @param   mixed   $default    默认值
     * @return  mixed
     */
    public static function get($array, $path, $default = NULL)
    {
        // 如果路径为数组，则为获取多个值
        if ( is_array($path) )
        {
            $result  = array();

            // 如果path为关联数组，则分割数组，键名为路径数组，键值为赋值数组
            if ( self::is_assoc($path) )
            {
                $default = array_merge($path, (array)$default);
                $path    = array_keys($path);
            }

            foreach ($path as $p)
            {
                $result[$p] = self::get($array, $p, $default[$p]);
            }

            return $result;
        }

        // 如果不是数组，直接返回默认值
        if ( !is_array($array))
        {
            return $default;
        }

        // 如果已经存在，直接返回
        if ( array_key_exists($path, $array) )
        {
            return $array[$path];
        }

        // 定义分隔符
        $delimiter = '.';

        // 处理不规范的路径
        $path = ltrim($path, "{$delimiter} ");
        $path = rtrim($path, "{$delimiter} *");
        $path = self::key_path($path);

        // 路径转数组
        $keys = explode($delimiter, $path);

        do
        {
            $key = array_shift($keys);
            $key = ctype_digit($key) ? intval($key) : $key;

            if ( isset($array[$key]) )
            {
                if ($keys)
                {
                    if ( is_array($array[$key]) )
                    {
                        $array = $array[$key];
                    }
                    else
                    {
                        break;
                    }
                }
                else
                {
                    return $array[$key];
                }
            }
            elseif ($key === '*')
            {
                $values = array();

                foreach ($array as $arr)
                {
                    if ( $value = self::get($arr, implode($delimiter, $keys)) )
                    {
                        $values[] = $value;
                    }
                }

                if ($values)
                {
                    return $values;
                }
                else
                {
                    break;
                }
            }
            else
            {
                break;
            }
        }
        while ($keys);

        // 未找到直接返回默认值
        return $default;
    }    

    /**
    * 通过键名路径给数组赋值，当值为NULL时，删除对应的键名，键名路径暂时不支持*匹配
    *
    * @param array   $array  数组
    * @param string  $path   键名路径
    * @param mixed   $value  值
    * @return array
    */   
    public static function set(&$array, $path, $value = NULL)
    {
        // 如果是数组，则为多个赋值
        if ( is_array($path) )
        {
            // 如果path为关联数组，则分割数组，键名为路径数组，键值为赋值数组
            if ( self::is_assoc($path) )
            {
                $value = array_merge($path, (array)$value);
                $path  = array_keys($path);           
            }

            foreach ($path as $p)
            {
                self::set($array, $p, $value[$p]);
            }

            return $array;
        }

        // 定义分隔符
        $delimiter = '.';

        $path = self::key_path($path);

        // 键名路径转化为数组
        $keys = explode($delimiter, $path);

        while (count($keys) > 1)
        {
            $key = array_shift($keys);
            $key = ctype_digit($key) ? intval($key) : $key;

            if (!isset($array[$key]))
            {
                $array[$key] = array();
            }

            $array = &$array[$key];
        }

        // 当值为NULL的时候，删除值
        if ( $value === NULL )
        {
            unset($array[array_shift($keys)]);
        }
        else
        {
            $array[array_shift($keys)] = $value;
        }        

        return $array;
    }

    /**
     * 通过键名路径删除数组中的键
     * 
     * @param  array &$array    数组
     * @param  mixed $path      键名路径，为数组的时候删除多个键值
     * @return array
     */
    public static function forget(&$array, $path)
    {
        return self::set($array, $path, null);
    }

    /**
     * 从数组或者多维数组中按照键名路径返回值，并删除原有的值
     * 如果弹出多个键，则返回一个由弹出键组成的数组, 否则返回该项的值
     *
     * <code>
     *  // 数组
     *  $array = array('value'=>'1','description'=>'2','label'=>'3');
     *
     *  // 操作
     *  $a = arr::pull($array,'label','默认值');
     *  $b = arr::pull($array,'text','默认值');
     *  $c = arr::pull($array,array('label','description'));
     *  $d = arr::pull($array,array('text','description'),array('text'=>'默认值'));
     *  
     *  $a = 3
     *  $b = '默认值'
     *  $c = array('label'=>null,'description'=>'2')
     *  $d = array('text'=>'默认值','description'=>null)
     *  $array = array('value'=>'1');
     *
     * </code>
     *
     * @param   array   $array      原数组
     * @param   mixed   $path       键名路径(可使用点符号分割)
     * @param   mixed   $default    默认值
     *
     * @return $mix 被弹出的数据, 如果弹出多个，则返回数组，否则返回该键的值
     */      
    public static function pull(&$array, $path, $default = NULL)
    {
        $value = self::get($array, $path, $default);

        self::forget($array, $path);

        return $value;
    }

    /**
     * 测试数组是否为关联数组
     *
     *     // Returns TRUE
     *     arr::is_assoc(array('username' => 'john.doe'));
     *
     *     // Returns FALSE
     *     Arr::is_assoc('foo', 'bar');
     *
     * @param   array   $array  array to check
     * @return  boolean
     */
    public static function is_assoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }    

    /**
     * 合并多维数组
     *
     *     $john = array('name' => 'john', 'children' => array('fred', 'paul', 'sally', 'jane'));
     *     $mary = array('name' => 'mary', 'children' => array('jane'));
     *
     *     // 合并数组
     *     $john = arr::merge($john, $mary);
     *
     *     // 结果
     *     array('name' => 'mary', 'children' => array('fred', 'paul', 'sally', 'jane'))
     *
     * @param   array  initial array
     * @param   array  array to merge
     * @param   array  ...
     * @return  array
     */
    public static function merge(array $a1, array $a2)
    {
        $result = array();

        for ($i = 0, $total = func_num_args(); $i < $total; $i++)
        {
            $arr = func_get_arg($i);

            $assoc = arr::is_assoc($arr);

            foreach ($arr as $key => $val)
            {
                if (isset($result[$key]))
                {
                    if (is_array($val) and is_array($result[$key]))
                    {
                        if (arr::is_assoc($val))
                        {
                            $result[$key] = arr::merge($result[$key], $val);
                        }
                        else
                        {
                            $diff = array_diff($val, $result[$key]);

                            $result[$key] = array_merge($result[$key], $diff);
                        }
                    }
                    else
                    {
                        if ($assoc)
                        {
                            $result[$key] = $val;
                        }
                        elseif (!in_array($val, $result, true))
                        {
                            $result[] = $val;
                        }
                    }
                }
                else
                {
                    $result[$key] = $val;
                }
            }
        }

        return $result;
    }

    /**
     * 从数组中删除键，并返回删除该键后的数组 废弃
     *
     * <code>
     *  $array = array('value'=>'1','description'=>'2','label'=>'3');
     *  $a = arr::remove($array,'label');
     *  $b = arr::remove($array,array('label','description'));
     *  $c = arr::remove($array,'label','description');
     *  
     *  $a = 3
     *  $b = $c = array('label'=>'3','description'=>'2')
     *
     *</code>
     *
     * @param array $array 目标数组
     * @param string $key 待删除的键名
     * ……
     * @return $mix	被弹出 的数据
     */
    public static function remove(&$array, $key)
    {
        $array = (array)$array;

        if (!is_array($key))
        {
            $key = array_slice(func_get_args(), 1);
        }

        foreach ($key as $k)
        {
            unset($array[$k]);
        }

        return $array;
    }

    /*
    * 根据键值多维排序
    */
   
    /**
     * 多维数组排序
     * 
     * @param  array &$array [description]
     * @param  string $key    [description]
     * @param  static $sort   升序[SORT_ASC]或者降序[SORT_DESC]
     * @return array
     */
    public static function multisort(&$array, $key, $sort = SORT_ASC)
    {
        if (!is_array($array)) return false;

        foreach ($array as $row)
        {
            if (is_array($row))
            {
                $keys[] = $row[$key];
            }
            else
            {
                return false;
            }
        }

        array_multisort($keys, $sort, $array);

        return $array;
    }

    /**
     * trim 数组
     *
     * @param array $array 目标数组
     * @return array
     */
    public static function trim($input)
    {
        if (!is_array($input))
        {
            return trim($input);
        }
        return array_map(array('arr', 'trim'), $input);
    }

    /**
     * 从数组中删除空白的元素（包括只有空白字符的元素）
     *
     * 用法：
     * @code php
     * $arr = array('', 'test', '   ');
     * arr::clear($arr);
     *
     * dump($arr);
     *   // 输出结果中将只有 'test'
     * @endcode
     *
     * @param array $arr 要处理的数组
     * @param boolean $trim 是否对数组元素调用 trim 函数
     */
    public static function clear(&$arr, $trim = true)
    {
        foreach ($arr as $key => $value)
        {
            if (is_array($value))
            {
                arr::clear($arr[$key]);
            }
            else
            {
                $value = trim($value);

                if ($value == '')
                {
                    unset($arr[$key]);
                }
                elseif ($trim)
                {
                    $arr[$key] = $value;
                }
            }
        }
        return $arr;
    }

    /**
     * 从一个二维数组中返回指定键的所有值
     *
     * 用法：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $values = arr::column($rows, 'value');
     *
     * dump($values);
     *   // 输出结果为
     *   // array(
     *   //   '1-1',
     *   //   '2-1',
     *   // )
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $key 要查询的键
     *
     * @return array 包含指定键所有值的数组
     */
    public static function column($arr, $key)
    {
        //PHP 5 >= 5.5.0 支持 array_column 参见：http://php.net/manual/zh/function.array-column.php
        if ( function_exists("array_column") )
        {
            return array_column($arr, $key);
        }

        $ret = array();

        foreach ($arr as $row)
        {
            if ( isset($row[$key]) )
            {
                $ret[] = $row[$key];
            }
        }
        
        return $ret;
    }

    /**
     * 将一个二维数组转换为 HashMap，并返回结果
     *
     * 用法1：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $hashmap = arr::hashmap($rows, 'id', 'value');
     *
     * dump($hashmap);
     *   // 输出结果为
     *   // array(
     *   //   1 => '1-1',
     *   //   2 => '2-1',
     *   // )
     * @endcode
     *
     * 如果省略 $value_field 参数，则转换结果每一项为包含该项所有数据的数组。
     *
     * 用法2：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $hashmap = arr::hashmap($rows, 'id');
     *
     * debug::dump($hashmap);
     *   // 输出结果为
     *   // array(
     *   //   1 => array('id' => 1, 'value' => '1-1'),
     *   //   2 => array('id' => 2, 'value' => '2-1'),
     *   // )
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $key_field 按照什么键的值进行转换
     * @param string $value_field 对应的键值
     *
     * @return array 转换后的 HashMap 样式数组
     */
    public static function hashmap(array $arr, $key_field, $value_field = null)
    {
        $ret = array();

        if ($value_field)
        {
            foreach ($arr as $row)
            {
                $ret[$row[$key_field]] = $row[$value_field];
            }
        }
        else
        {
            foreach ($arr as $row)
            {
                $ret[$row[$key_field]] = $row;
            }
        }

        return $ret;
    }

    /**
     * 将一个二维数组按照指定字段的值分组
     *
     * 用法：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1', 'parent' => 1),
     *     array('id' => 2, 'value' => '2-1', 'parent' => 1),
     *     array('id' => 3, 'value' => '3-1', 'parent' => 1),
     *     array('id' => 4, 'value' => '4-1', 'parent' => 2),
     *     array('id' => 5, 'value' => '5-1', 'parent' => 2),
     *     array('id' => 6, 'value' => '6-1', 'parent' => 3),
     * );
     * $values = arr::group($rows, 'parent');
     *
     * dd($values);
     *   // 按照 parent 分组的输出结果为
     *   // array(
     *   //   1 => array(
     *   //        array('id' => 1, 'value' => '1-1', 'parent' => 1),
     *   //        array('id' => 2, 'value' => '2-1', 'parent' => 1),
     *   //        array('id' => 3, 'value' => '3-1', 'parent' => 1),
     *   //   ),
     *   //   2 => array(
     *   //        array('id' => 4, 'value' => '4-1', 'parent' => 2),
     *   //        array('id' => 5, 'value' => '5-1', 'parent' => 2),
     *   //   ),
     *   //   3 => array(
     *   //        array('id' => 6, 'value' => '6-1', 'parent' => 3),
     *   //   ),
     *   // )
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $key 作为分组依据的键名
     *
     * @return array 分组后的结果
     */
    public static function group($arr, $key)
    {
        $ret = array();
        foreach ($arr as $row)
        {
            $key = $row[$key];
            $ret[$key][] = $row;
        }
        return $ret;
    }

    /**
     * 将多维数组转化为扁平数组
     *
     *     $array = array('set' => array('one' => 'something'), 'two' => 'other');
     *
     *     // Flatten the array
     *     $array = arr::flatten($array);
     *
     *     // The array will now be
     *     array('one' => 'something', 'two' => 'other');
     *
     *
     * @param   array   $array  待扁平的数组
     * @return  array
     */
    public static function flatten($array)
    {
        $is_assoc = arr::is_assoc($array);

        $flat = array();

        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $flat = array_merge($flat, arr::flatten($value));
            }
            else
            {
                if ($is_assoc)
                {
                    $flat[$key] = $value;
                }
                else
                {
                    $flat[] = $value;
                }
            }
        }
        return $flat;
    }    

    /**
     * 将无限分类数组转化为树
     *
     * 用法：
     * @code php
     * $data = array(
     *     array('id' => 1, 'parentid' => 0, 'name' => '1-0'),
     *     array('id' => 5, 'parentid' => 0, 'name' => '5-0'),
     *     array('id' => 2, 'parentid' => 1, 'name' => '2-1'),
     *     array('id' => 3, 'parentid' => 1, 'name' => '2-1'),
     *     array('id' => 4, 'parentid' => 3, 'name' => '4-3'),	 
     *     array('id' => 6, 'parentid' => 5, 'name' => '6-5'),
     * );
     *
     * $values = arr::tree($data, 0);
     *
     * @endcode
     *
     * @param array $data   //无限分类数组(传入前需要对无限分类数组的 parentid 进行 ASC 排序，数据库读取数据排序或者使用arr::multisort($data,'parentid'))
     * @param int $rootid	//初始的父编号，一般为0             
     * @param int $id		//编号键名
     * @param int $parentid //父编号键名
     * @param int $level    //分类级别
     * @return array $tree 
     */
    public static function tree(&$data, $rootid = 0, $id = "id", $parentid = "parentid", $level = 0)
    {
        foreach ($data as $k => $v)
        {
            if ($v[$parentid] == $rootid)
            {
                $v['_level'] = $level;
                self::$tree[] = $v;
                unset($data[$k]);
                self::tree($data, $v[$id], $id, $parentid, $level + 1);
            }
        }

        return self::$tree;
    }

    /**
     * 返回经过压缩的数组结构信息，默认为直接返回，类似于var_export
     * 
     * @param  mixed $var  
     * @return sting 数组结构信息
     */
    public function export($var, $level=0)
    {    
        if ( is_array($var) )
        {
            $implode = array();

            foreach ($var as $key => $value)
            { 
                if ( is_array($value) )
                {
                    $newline = "\n";
                    $indent1 = str_repeat('     ', $level+1);
                    $indent2 = str_repeat('     ', $level);
                } 

                if ( is_int($key) )
                {
                    $implode[] = $indent1.arr::export($value, $level+1);
                }
                else
                {
                    $implode[] = $indent1.var_export($key, true).'=>'.arr::export($value, $level+1);                    
                }
            }
            
            $code = 'array('.$newline.implode(','.$newline, $implode).$newline.$indent2.')';           

            return $code;
        }

        return var_export($var,true);
    }      

    /**
     * 在数组的开头插入数组
     *
     *     arr::unshift($array, 'key', 'value');
     *
     * @param   array   array to modify
     * @param   string  array key name
     * @param   mixed   array value
     * @return  array
     */
    public static function unshift(array & $array, $key, $val = '')
    {

        // 插入的数组
        $insert = is_array($key) ? $key : array($key => $val);

        $array = array_reverse($array, true);
        $array = array_merge($array, $insert);
        $array = array_reverse($array, true);

        return $array;
    }

    /**
     * 在数组组的特定位置插入数组
     *
     * @param array $array 原始数组
     * @param int $i 插入位置
     * @param string||array $key 插入的数组键名 或者数组
     * @param mixed  $val 插入的数组值
     * @return array 插入后的数组
     */
    public static function insert(array & $array, $i, $key, $val = '')
    {
        // 如果数组已经存在，则删除原数组中的数据
        if (in_array($key, $array, true))
        {
            unset($array[$key]);
        }

        // 插入的数组
        $insert = is_array($key) ? $key : array($key => $val);

        // 将数组插入到新的位置
        $array = array_merge(array_slice($array, 0, $i), $insert, array_slice($array, $i));

        return $array;
    }

    /**
     * 将数组插入到特定的键前面,如果比对的键名不存在，则直接插入到末尾
     *
     * @param array $array 原始数组
     * @param string $existing 比对键名
     * @param string $key 插入的数组键名
     * @param mixed  $val 插入的数组值
     * @return array 插入后的数组
     */
    public static function before(array & $array, $existing, $key, $val = '')
    {
        if (false === $i = array_search($existing, array_keys($array)))
        {
            $array[$key] = $val;
            return $array;
        }

        return self::insert($array, $i, $key, $val);
    }

    /**
     * 将数组插入到特定的键后面,如果比对的键名不存在，则直接插入到末尾
     *
     * @param array $array 原始数组
     * @param string $existing 比对键名
     * @param string $key 插入的数组键名
     * @param mixed  $val 插入的数组值
     * @return array 插入后的数组
     */
    public static function after(array & $array, $existing, $key, $val = '')
    {
        if (false === $i = array_search($existing, array_keys($array)))
        {
            $array[$key] = $val;
            return $array;
        }

        return self::insert($array, $i + 1, $key, $val);
    }


}
?>