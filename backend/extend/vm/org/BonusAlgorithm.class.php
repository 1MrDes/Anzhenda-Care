<?php

namespace vm\org;

/**
 * 抢红包分配算法
 * @copyright        2013 — 2015
 * @author                c.k xiao <jihaoju@qq.com>
 * @date                2015-5-27
 * @desc            该算法来自于网络，计算出来的红包金额呈正态分布
 */
class BonusAlgorithm
{

    /**
     * 生产min和max之间的随机数，但是概率不是平均的，从min到max方向概率逐渐加大。
     * 先平方，然后产生一个平方值范围内的随机数，再开方，这样就产生了一种“膨胀”再“收缩”的效果。
     *
     * @param min
     * @param max
     * @return
     */
    public static function xRandom($min, $max)
    {
        return sqrt(self::nextLong(self::sqr($max - $min)));
    }

    /**
     *
     * @param total
     *            红包总额
     * @param count
     *            红包个数
     * @param max
     *            每个小红包的最大额
     * @param min
     *            每个小红包的最小额
     * @return 存放生成的每个小红包的值的数组
     */
    public static function generate($total, $count, $max, $min)
    {
        $bonus_total = $total;

        $result = array();

        $average = $total / $count;

        $a = $average - $min;
        $b = $max - $min;

        //
        //这样的随机数的概率实际改变了，产生大数的可能性要比产生小数的概率要小。
        //这样就实现了大部分红包的值在平均数附近。大红包和小红包比较少。
        $range1 = self::sqr($average - $min);
        $range2 = self::sqr($max - $average);

        for ($i = 0; $i < $count; $i++) {
            //因为小红包的数量通常是要比大红包的数量要多的，因为这里的概率要调换过来。
            //当随机数>平均值，则产生小红包
            //当随机数<平均值，则产生大红包
            if (rnd_number($min, $max) > $average) {
                // 在平均线上减钱
                $temp = round(($min + sqrt(self::nextLong($range1))), 2);
                //$temp = $min + self::xRandom($min, $average);
                $result[$i] = $temp;
                $total -= $temp;
            } else {
                // 在平均线上加钱
                $temp = round(($max - sqrt(self::nextLong($range2))), 2);
                //$temp = $max - self::xRandom($average, $max);
                $result[$i] = $temp;
                $total -= $temp;
            }
        }
        // 如果还有余钱，则尝试加到小红包里，如果加不进去，则尝试下一个。
        while ($total > 0) {
            for ($i = 0; $i < $count; $i++) {
                if ($total > 0 && $result[$i] < $max) {
                    $result[$i]++;
                    $total--;
                }
            }
        }
        // 如果钱是负数了，还得从已生成的小红包中抽取回来
        while ($total < 0) {
            for ($i = 0; $i < $count; $i++) {
                if ($total < 0 && $result[$i] > $min) {
                    //$result[$i]--;
                    //$total++;
                    /** -1的话会照成红包金额是负数或者0 所以改成减min红包最小数  by zhoumianze@qq.com 2015-10-09  */
                    $result[$i] = $result[$i] - $min;
                    $total = $total + $min;
                }
            }
        }
        if (array_sum($result) < $bonus_total) {
            $result[count($result) - 1] += $bonus_total - array_sum($result);
        }
        return $result;
    }

    public static function sqr($n)
    {
        // 查表快，还是直接算快？
        return $n * $n;
    }

    public static function nextLong($n)
    {
        return rnd_number(0, $n - 1);
    }

    /**
     * 简易红包算法
     * @param float $total 红包总额
     * @param int $num 红包个数
     * @param float $min 每个小红包的最小额
     */
    public static function simple($total, $num, $min)
    {
        $result = array();
        for ($i = 1; $i < $num; $i++) {
            $safe_total = ($total - ($num - $i) * $min) / ($num - $i); // 随机安全上限
            $money = mt_rand($min * 100, $safe_total * 100) / 100;
            $total = $total - $money;
            $result[] = $money;
        }
        $result[] = $total;
        return $result;
    }

    /**
     * 生产min和max之间的随机数，但是概率不是平均的，从min到max方向概率逐渐加大。
     * 先平方，然后产生一个平方值范围内的随机数，再开方，这样就产生了一种“膨胀”再“收缩”的效果。
     */
    public static function xRandom_new($bonus_min, $bonus_max)
    {
        $sqr = intval(self::sqr($bonus_max - $bonus_min));
        $rand_num = rand(0, ($sqr - 1));
        return intval(sqrt($rand_num));
    }


    /**
     *
     * @param float $bonus_total 红包总额
     * @param int $bonus_count 红包个数
     * @param float $bonus_max 每个小红包的最大额
     * @param float $bonus_min 每个小红包的最小额
     * @return array  $return  存放生成的每个小红包的值的一维数组
     */
    public static function generate_bonus($bonus_total, $bonus_count, $bonus_max, $bonus_min)
    {
        //总价，最大值，最小值  都乘以100,
        $bonus_total = intval($bonus_total * 100);
        $bonus_max = intval($bonus_max * 100);
        $bonus_min = intval($bonus_min * 100);

        $result = array();
        $return = array();
        $average = $bonus_total / $bonus_count;

        $a = $average - $bonus_min;
        $b = $bonus_max - $bonus_min;

        //
        //这样的随机数的概率实际改变了，产生大数的可能性要比产生小数的概率要小。
        //这样就实现了大部分红包的值在平均数附近。大红包和小红包比较少。
        $range1 = self::sqr($average - $bonus_min);
        $range2 = self::sqr($bonus_max - $average);

        for ($i = 0; $i < $bonus_count; $i++) {
            //因为小红包的数量通常是要比大红包的数量要多的，因为这里的概率要调换过来。
            //当随机数>平均值，则产生小红包
            //当随机数<平均值，则产生大红包
            if (rand($bonus_min, $bonus_max) > $average) {
                // 在平均线上减钱
                $temp = $bonus_min + self::xRandom_new($bonus_min, $average);
                $result[$i] = $temp;
                $bonus_total -= $temp;
            } else {
                // 在平均线上加钱
                $temp = $bonus_max - self::xRandom_new($average, $bonus_max);
                $result[$i] = $temp;
                $bonus_total -= $temp;
            }
        }
        // 如果还有余钱，则尝试加到小红包里，如果加不进去，则尝试下一个。
        while ($bonus_total > 0) {
            for ($i = 0; $i < $bonus_count; $i++) {
                if ($bonus_total > 0 && $result[$i] < $bonus_max) {
                    $result[$i]++;
                    $bonus_total--;
                }
            }
        }
        // 如果钱是负数了，还得从已生成的小红包中抽取回来
        while ($bonus_total < 0) {
            for ($i = 0; $i < $bonus_count; $i++) {
                if ($bonus_total < 0 && $result[$i] > $bonus_min) {
                    $result[$i]--;
                    $bonus_total++;
                }
            }
        }
        //返回值全部除以100
        foreach ($result as $v) {
            $v = $v / 100;
            $return[] = $v;
        }
        return $return;
    }

}