<?php

declare(strict_types=1);

namespace App\Helper;

class NumberHelper
{
    /**
     * 格式化文件比特大小
     *
     * @param int    $size      文件大小(比特)
     * @param int    $dec       小数位
     * @param string $delimiter 数字和单位间的分隔符
     *
     * @return string
     */
    public static function formatBytes(int $size, int $dec = 2, string $delimiter = ''): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $size >= 1024 && $i < 5; $i++) {
            $size /= 1024;
        }

        return round($size, $dec) . $delimiter . ($units[$i] ?? Consts::UNKNOWN);
    }

    /**
     * 值是否在某范围内
     *
     * @param int|float $val 值
     * @param int|float $min 小值
     * @param int|float $max 大值
     *
     * @return bool
     */
    public static function inRange($val, $min, $max): bool
    {
        $val = floatval($val);
        $min = floatval($min);
        $max = floatval($max);
        return $val >= $min && $val <= $max;
    }

    /**
     * 对数列求和,忽略非数值.
     *
     * @param mixed ...$vals
     *
     * @return float
     */
    public static function sum(...$vals): float
    {
        $res = 0;
        foreach ($vals as $val) {
            if (is_numeric($val)) {
                $res += floatval($val);
            }
        }

        return $res;
    }

    /**
     * 对数列求平均值,忽略非数值.
     *
     * @param mixed ...$vals
     *
     * @return float
     */
    public static function average(...$vals): float
    {
        $res   = 0;
        $count = 0;
        $total = 0;
        foreach ($vals as $val) {
            if (is_numeric($val)) {
                $total += floatval($val);
                $count++;
            }
        }

        if ($count > 0) {
            $res = $total / $count;
        }

        return $res;
    }

    /**
     * 获取地理距离/米.
     * 参数分别为两点的经度和纬度.lat:-90~90,lng:-180~180.
     *
     * @param float $lng1 起点经度
     * @param float $lat1 起点纬度
     * @param float $lng2 终点经度
     * @param float $lat2 终点纬度
     *
     * @return float
     */
    public static function geoDistance(float $lng1 = 0, float $lat1 = 0, float $lng2 = 0, float $lat2 = 0): float
    {
        $earthRadius = 6371000.0;
        $lat1        = ($lat1 * pi()) / 180;
        $lng1        = ($lng1 * pi()) / 180;
        $lat2        = ($lat2 * pi()) / 180;
        $lng2        = ($lng2 * pi()) / 180;

        $calcLongitude = $lng2 - $lng1;
        $calcLatitude  = $lat2 - $lat1;
        $stepOne       = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo       = 2 * asin(min(1, sqrt($stepOne)));
        return $earthRadius * $stepTwo;
    }

    /**
     * 数值格式化
     *
     * @param float|int $number       要格式化的数字
     * @param int       $decimals     小数位数
     * @param string    $decPoint     小数点
     * @param string    $thousandssep 千分位符号
     *
     * @return string
     */
    public static function numberFormat($number, int $decimals = 2, string $decPoint = '.', string $thousandssep = ''): string
    {
        return number_format($number, $decimals, $decPoint, $thousandssep);
    }
}
