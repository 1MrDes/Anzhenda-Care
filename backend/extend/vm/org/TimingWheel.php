<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/5/27 11:03
 * @description
 */

namespace vm\org;


use think\Log;

class TimingWheel
{
    private $wheel = [];

    const LENGTH = 3600;

    public function __construct()
    {
        $this->wheel = array_fill(0, self::LENGTH, []);
    }

    /**
     * 找出当前需要执行的任务，并将其从时间轮片中删除
     * @return array
     */
    public function popSlots()
    {
        $curIndex = $this->getCurIndex();
        $list = $this->wheel[$curIndex];
        $slots = [];
        if (!empty($list)) {
            foreach ($list as $key => $item) {
                if ($item['round'] == 0) {
                    $slots[] = $item['data'];
                    unset($this->wheel[$curIndex][$key]);
                } else {
                    $this->wheel[$curIndex][$key]['round'] -= 1;
                }
            }
        }
        return $slots;
    }

    /**
     * @param $interavl
     * @param $data
     * 新增一个数据到时间仑片中
     */
    public function add($interavl, $data)
    {
        $curIndex = $this->getCurIndex();
        $totalIndex = $curIndex + $interavl;
        $round = intval($interavl / self::LENGTH);
        $index = $totalIndex % self::LENGTH;
        if ($interavl % self::LENGTH == 0) {
            $round -= 1;
        }
        $slot = [
            'round' => $round,
            'data' => $data
        ];
        Log::info("slot:" . print_r($slot, true));
        $this->wheel[$index][] = $slot;
    }

    /**
     * @return float|int
     * 获取当前时间轮片已走到哪个节点
     */
    public function getCurIndex()
    {
        $nowTime = time();
        $minute = date('i', $nowTime);
        $second = date('s', $nowTime);
        return (int)$minute * 60 + (int)$second;
    }

    /**
     * 重置
     */
    public function clear()
    {
        $this->wheel = array_fill(0, self::LENGTH, []);
    }

    /**
     * 调试用
     */
    public function desc()
    {
        $list = [];
        foreach ($this->wheel as $key => $slots) {
            if (!empty($slots)) {
                $list[$key] = $slots;
            }
        }
        Log::info(print_r($list, true));
    }

}