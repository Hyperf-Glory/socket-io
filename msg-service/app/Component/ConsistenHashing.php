<?php
declare(strict_types = 1);

namespace App\Component;

class ConsistenHashing
{
    protected $nodes = [];    //真实节点
    protected $position = [];  //虚拟节点
    protected $mul = 64;  // 每个节点对应64个虚拟节点

    /**
     * 把字符串转为32位符号整数
     *
     * @param string $str
     *
     * @return string
     */
    public function hash(string $str)
    {
        return sprintf('%u', crc32($str));
    }

    /**
     * 核心功能
     *
     * @param string $key
     *
     * @return mixed
     */
    public function lookup(string $key)
    {
        $point = $this->hash($key);

        //先取圆环上最小的一个节点,当成结果
        $node = current($this->position);

        // 循环获取相近的节点
        foreach ($this->position as $key => $val) {
            if ($point <= $key) {
                $node = $val;
                break;
            }
        }

        reset($this->position);    //把数组的内部指针指向第一个元素，便于下次查询从头查找

        return $node;
    }

    /**
     * 添加节点
     *
     * @param string $node
     */
    public function addNode(string $node)
    {
        if (isset($this->nodes[$node])) {
            return;
        }

        // 添加节点和虚拟节点
        for ($i = 0; $i < $this->mul; $i++) {
            $pos                  = $this->hash($node . '-' . $i);
            $this->position[$pos] = $node;
            $this->nodes[$node][] = $pos;
        }

        // 重新排序
        $this->sortPos();
    }

    /**
     * 删除节点
     *
     * @param $node
     */
    public function delNode($node)
    {
        if (!isset($this->nodes[$node])) {
            return;
        }

        // 循环删除虚拟节点
        foreach ($this->nodes[$node] as $val) {
            unset($this->position[$val]);
        }

        // 删除节点
        unset($this->nodes[$node]);
    }

    /**
     * 排序
     */
    public function sortPos()
    {
        ksort($this->position, SORT_REGULAR);
    }
}


