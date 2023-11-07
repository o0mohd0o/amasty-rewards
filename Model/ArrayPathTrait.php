<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

trait ArrayPathTrait
{
    private $replace = ['.' => 'children'];

    private $delimiter = '/';

    /**
     * @param array $array
     * @param string $path
     * @param mixed $value
     * @param bool $merge
     */
    public function setToArrayByPath(&$array, $path, $value, $merge = true)
    {
        if (!$merge) {
            $this->unsetArrayValueByPath($array, $path);
        }

        $path = $this->preparePath($path);
        $path = array_reverse($path);
        $result[array_shift($path)] = $value;

        foreach ($path as $key) {
            $result = [$key => $result];
        }
        $array = array_merge_recursive($array, $result);
    }

    /**
     * @param array $array
     * @param string $path
     */
    public function unsetArrayValueByPath(&$array, $path)
    {
        $path = $this->preparePath($path);
        $lastKey = array_pop($path);
        $result = &$array;

        foreach ($path as $key) {
            $result = &$result[$key];
        }

        if (isset($result[$lastKey])) {
            unset($result[$lastKey]);
        }
    }

    /**
     * @param array $array
     * @param string $path
     *
     * @return mixed|null
     */
    public function getArrayValueByPath($array, $path)
    {
        $path = $this->preparePath($path);
        $lastKey = array_pop($path);
        $result = &$array;

        foreach ($path as $key) {
            $result = &$result[$key];
        }

        if (isset($result[$lastKey])) {
            return $result[$lastKey];
        }

        return null;
    }

    private function preparePath($path)
    {
        $path = strtr($path, $this->replace);
        $path = explode($this->delimiter, $path);

        return $path;
    }
}
