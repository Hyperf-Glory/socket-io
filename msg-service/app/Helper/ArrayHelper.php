<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * Class ArrayHelper
 * @package App\Helper
 */
class ArrayHelper
{
    /**
     * 对多维数组进行排序
     *
     * @param array  $arr     多维数组
     * @param string $sortKey 排序键值
     * @param int    $sort    排序类型:SORT_DESC/SORT_ASC
     *
     * @return array
     */
    public static function multiArraySort(array $arr, string $sortKey, int $sort = SORT_DESC): array
    {
        $keyArr = [];
        foreach ($arr as $subArr) {
            if (!is_array($subArr) || !isset($subArr[$sortKey])) {
                return [];
            }
            array_push($keyArr, $subArr[$sortKey]);
        }
        array_multisort($keyArr, $sort, $arr);
        return $arr;
    }

    /**
     * 多维数组去重
     *
     * @param array $arr
     * @param bool  $keepKey 是否保留键值
     *
     * @return array
     */
    public static function multiArrayUnique(array $arr, bool $keepKey = false): array
    {
        $hasArr = $res = [];
        foreach ($arr as $k => $v) {
            $hash = md5(serialize($v));
            if (!in_array($hash, $hasArr)) {
                array_push($hasArr, $hash);
                if ($keepKey) {
                    $res[$k] = $v;
                } else {
                    $res[] = $v;
                }
            }
        }
        unset($hasArr);
        return $res;
    }

    /**
     * 取多维数组的最底层值
     *
     * @param array $arr
     * @param array $vals 结果
     *
     * @return array
     */
    public static function multiArrayValues(array $arr, &$vals = []): array
    {
        foreach ($arr as $v) {
            if (is_array($v)) {
                self::multiArrayValues($v, $vals);
            } else {
                array_push($vals, $v);
            }
        }
        return $vals;
    }

    /**
     * 对数组元素递归求值
     *
     * @param array    $arr
     * @param callable $fn 回调函数
     *
     * @return array
     */
    public static function mapRecursive(array $arr, callable $fn): array
    {
        $res = [];
        foreach ($arr as $k => $v) {
            $res[$k] = is_array($v) ? (self::mapRecursive($v, $fn)) : call_user_func($fn, $v);
        }
        return $res;
    }

    /**
     * 对象转数组
     *
     * @param mixed $val
     *
     * @return array
     */
    public static function object2Array($val): array
    {
        $arr = is_object($val) ? get_object_vars($val) : $val;
        if (is_array($arr)) {
            foreach ($arr as $k => $item) {
                if (is_array($item) && !empty($item)) {
                    $arr[$k] = array_map(__METHOD__, $item);
                }
            }
        } else {
            $arr = (array)$arr;
        }
        return $arr;
    }

    /**
     * 数组转对象
     *
     * @param array $arr
     *
     * @return object
     */
    public static function array2Object(array $arr): object
    {
        foreach ($arr as $k => $item) {
            if (is_array($item)) {
                $arr[$k] = empty($item) ? new \stdClass() : call_user_func(__METHOD__, $item);
            }
        }
        return (object)$arr;
    }

    /**
     * 从数组中剪切元素,将改变原数组,并返回剪切的元素数组.
     *
     * @param array $arr     原数组
     * @param mixed ...$keys 要剪切的元素键,一个或多个
     *
     * @return array
     */
    public static function cutItems(array &$arr, ...$keys): array
    {
        $res = [];
        foreach ($keys as $key) {
            if (isset($arr[$key])) {
                array_push($res, $arr[$key]);
                unset($arr[$key]);
            } else {
                array_push($res, null);
            }
        }
        return $res;
    }

    /**
     * 数组元素组合(按元素值组合)
     *
     * @param array  $arr       数组
     * @param int    $len       组合长度(从数组中取几个元素来组合)
     * @param string $separator 分隔符
     *
     * @return array
     */
    private static function _combinationValue(array $arr, int $len, string $separator = ''): array
    {
        $res = [];
        if ($len <= 1) {
            return $arr;
        } elseif ($len >= count($arr)) {
            array_push($res, implode($separator, $arr));
            return $res;
        }
        $firstItem = array_shift($arr);
        $newArr    = array_values($arr);
        $list1     = self::_combinationValue($newArr, $len - 1, $separator);
        foreach ($list1 as $item) {
            $str = strval($firstItem) . $separator . strval($item);
            array_push($res, $str);
        }
        $list2 = self::_combinationValue($newArr, $len, $separator);
        foreach ($list2 as $item) {
            array_push($res, strval($item));
        }
        return $res;
    }

    /**
     * 数组元素组合(按元素值和位置组合)
     *
     * @param array  $arr 数组
     * @param string $separator
     *
     * @return array
     */
    private static function _combinationPosition(array $arr, string $separator = ''): array
    {
        $len = count($arr);
        $res = self::combinationAll($arr, $separator);
        if ($len >= 2) {
            foreach ($arr as $k => $item) {
                $newArr = $arr;
                self::cutItems($newArr, $k);
                $newRes = self::_combinationPosition($newArr, $separator);
                if (!empty($newRes)) {
                    $res = array_merge($res, $newRes);
                }
            }
        }
        return $res;
    }

    /**
     * 数组全排列,f(n)=n!.
     *
     * @param array  $arr       要排列组合的数组
     * @param string $separator 分隔符
     *
     * @return array
     */
    public static function combinationAll(array $arr, string $separator = '')
    {
        $len = count($arr);
        if ($len == 0) {
            return [];
        } elseif ($len == 1) {
            return $arr;
        }
        //保证初始数组是有序的
        sort($arr);
        $last = $len - 1; //尾部元素下标
        $x    = $last;
        $res  = [];
        array_push($res, implode($separator, $arr)); //第一种组合
        while (true) {
            $y = $x--; //相邻的两个元素
            if ($arr[$x] < $arr[$y]) { //如果前一个元素的值小于后一个元素的值
                $z = $last;
                while ($arr[$x] > $arr[$z]) { //从尾部开始，找到第一个大于 $x 元素的值
                    $z--;
                }
                /* 交换 $x 和 $z 元素的值 */
                [$arr[$x], $arr[$z]] = [$arr[$z], $arr[$x]];
                /* 将 $y 之后的元素全部逆向排列 */
                for ($i = $last; $i > $y; $i--, $y++) {
                    [$arr[$i], $arr[$y]] = [$arr[$y], $arr[$i]];
                }
                array_push($res, implode($separator, $arr));
                $x = $last;
            }
            if ($x == 0) { //全部组合完毕
                break;
            }
        }
        return $res;
    }

    /**
     * 以字符串形式,排列组合数组的元素,全部可能的组合.
     *
     * @param array  $arr       要排列组合的数组
     * @param string $separator 分隔符
     * @param bool   $unique    组合中的元素是否唯一.设为true时,只考虑元素值而忽略元素位置,则[a,b]与[b,a]是相同的组合;设为false时,同时考虑元素值和元素位置,则[a,b]与[b,a]是不同的组合.
     *
     * @return array
     */
    public static function combinationFull(array $arr, string $separator = '', bool $unique = true): array
    {
        $res = [];
        $len = count($arr);
        if ($unique) {
            for ($i = 1; $i <= $len; $i++) {
                $news = self::_combinationValue($arr, $i, $separator);
                if (!empty($news)) {
                    $res = array_merge($res, $news);
                }
            }
        } else {
            $news = self::_combinationPosition($arr, $separator);
            if (!empty($news)) {
                $res = array_merge($res, $news);
            }
            $res = array_unique($res);
            sort($res);
        }
        return $res;
    }

    /**
     * 从数组中搜索对应元素(单个).若匹配,返回该元素;否则返回false.
     *
     * @param array $arr        要搜索的数据数组
     * @param array $conditions 条件数组
     * @param bool  $delSource  若匹配,是否删除原数组的该元素
     *
     * @return bool|mixed
     */
    public static function searchItem(array &$arr, array $conditions, bool $delSource = false)
    {
        if (empty($arr) || empty($conditions)) {
            return false;
        }
        $condLen = count($conditions);
        foreach ($arr as $i => $item) {
            $chk = 0;
            foreach ($conditions as $k => $v) {
                if (is_bool($v) && $v) {
                    $chk++;
                } elseif (isset($item[$k]) && $item[$k] == $v) {
                    $chk++;
                }
            }
            //条件完全匹配
            if ($chk == $condLen) {
                if ($delSource) {
                    unset($arr[$i]);
                }
                return $item;
            }
        }
        return false;
    }

    /**
     * 从数组中搜索对应元素(多个).若匹配,返回新数组,包含一个以上元素;否则返回空数组.
     *
     * @param array $arr        要搜索的数据数组
     * @param array $conditions 条件数组
     * @param bool  $delSource  若匹配,是否删除原数组的该元素
     *
     * @return array
     */
    public static function searchMutil(array &$arr, array $conditions, bool $delSource = false): array
    {
        $res = [];
        if (empty($arr) || empty($conditions)) {
            return $res;
        }
        $condLen = count($conditions);
        foreach ($arr as $i => $item) {
            $chk = 0;
            foreach ($conditions as $k => $v) {
                if (is_bool($v) && $v) {
                    $chk++;
                } elseif (isset($item[$k]) && $item[$k] == $v) {
                    $chk++;
                }
            }
            //条件完全匹配
            if ($chk == $condLen) {
                if ($delSource) {
                    unset($arr[$i]);
                }
                array_push($res, $item);
            }
        }
        return $res;
    }

    /**
     * 二维数组按指定的键值排序.若元素的键值不存在,则返回空数组.
     *
     * @param array  $arr
     * @param string $key     排序的键
     * @param string $sort    排序方式:desc/asc
     * @param bool   $keepKey 是否保留外层键值
     *
     * @return array
     */
    public static function sortByField(array $arr, string $key, string $sort = 'desc', bool $keepKey = false): array
    {
        $res    = [];
        $values = [];
        $sort   = strtolower(trim($sort));
        foreach ($arr as $k => $v) {
            if (!isset($v[$key])) {
                return [];
            }
            $values[$k] = $v[$key];
        }
        if ($sort === 'asc') {
            asort($values);
        } else {
            arsort($values);
        }
        reset($values);
        foreach ($values as $k => $v) {
            if ($keepKey) {
                $res[$k] = $arr[$k];
            } else {
                $res[] = $arr[$k];
            }
        }
        return $res;
    }

    /**
     * 数组按照多字段排序
     *
     * @param array $arr      多维数组
     * @param array ...$sorts 多个排序信息.其中的元素必须是数组,形如['field', SORT_ASC],或者['field'];若没有排序类型,则默认 SORT_DESC .
     *
     * @return array
     */
    public static function sortByMultiFields(array $arr, array ...$sorts): array
    {
        if (empty($arr)) {
            return [];
        }
        if (!empty($sorts)) {
            $sortConditions = [];
            foreach ($sorts as $sortInfo) {
                //$sortInfo必须形如['field', SORT_ASC],或者['field']
                $file   = strval(current($sortInfo));
                $sort   = intval($sortInfo[1] ?? SORT_DESC);
                $tmpArr = [];
                foreach ($arr as $k => $item) {
                    //排序字段不存在
                    if (empty($file) || !isset($item[$file])) {
                        return [];
                    }
                    $tmpArr[$k] = $item[$file];
                }
                array_push($sortConditions, $tmpArr, $sort);
            }
            array_push($sortConditions, $arr);
            array_multisort(...$sortConditions);
            return end($sortConditions);
        }
        return $arr;
    }

    /**
     * 交换2个元素的值
     *
     * @param array      $arr
     * @param int|string $keya 键a
     * @param int|string $keyb 键b
     *
     * @return bool
     */
    public static function swapItem(array &$arr, $keya, $keyb): bool
    {
        $keya = strval($keya);
        $keyb = strval($keyb);
        if (isset($arr[$keya]) && isset($arr[$keyb])) {
            [$arr[$keya], $arr[$keyb]] = [$arr[$keyb], $arr[$keya]];
            return true;
        }
        return false;
    }

    /**
     * 设置数组带点的键值.
     * 若键为空,则会替换原数组为[$value].
     *
     * @param array $arr   原数组
     * @param mixed $key   键,可带点的多级,如row.usr.name
     * @param mixed $value 值
     */
    public static function setDotKey(array &$arr, $key, $value): void
    {
        if (is_null($key) || $key == '') {
            $arr = is_array($value) ? $value : (array)$value;
            return;
        }
        $keyStr = strval($key);
        if (ValidateHelper::isInteger($keyStr) || strpos($keyStr, '.') === false) {
            $arr[$keyStr] = $value;
            return;
        }
        $keys = explode('.', $keyStr);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!array_key_exists($key, $arr)) {
                $arr[$key] = [];
            } elseif (!is_array($arr[$key])) {
                $arr[$key] = (array)$arr[$key];
            }
            $arr = &$arr[$key];
        }
        $arr[array_shift($keys)] = $value;
    }

    /**
     * 获取数组带点的键值.
     *
     * @param array $arr     数组
     * @param mixed $key     键,可带点的多级,如row.usr.name
     * @param mixed $default 默认值
     *
     * @return mixed|null
     */
    public static function getDotKey(array $arr, $key = null, $default = null)
    {
        if (is_null($key) || $key == '') {
            return $arr;
        }
        $keyStr = strval($key);
        if (ValidateHelper::isInteger($keyStr) || strpos($keyStr, '.') === false) {
            return $arr[$keyStr] ?? $default;
        }
        $keys = explode('.', $keyStr);
        foreach ($keys as $key) {
            if (is_array($arr) && array_key_exists($key, $arr)) {
                $arr = $arr[$key];
            } else {
                return $default;
            }
        }
        return $arr;
    }

    /**
     * 数组是否存在带点的键
     *
     * @param array $arr
     * @param mixed $key 键,可带点的多级,如row.usr.name
     *
     * @return bool
     */
    public static function hasDotKey(array $arr, $key = null): bool
    {
        if (is_null($key) || $key == '') {
            return false;
        }
        $keyStr = strval($key);
        if (ValidateHelper::isInteger($keyStr) || strpos($keyStr, '.') === false) {
            return array_key_exists($keyStr, $arr);
        }
        $keys = explode('.', $keyStr);
        foreach ($keys as $key) {
            if (is_null($key) || $key == '') {
                return false;
            } elseif (!is_array($arr) || !array_key_exists($key, $arr)) {
                return false;
            }
            $arr = $arr[$key];
        }
        return true;
    }
}
