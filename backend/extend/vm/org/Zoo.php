<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/4/1 20:10
 * @description
 */

namespace vm\org;

class Zoo
{
    static private $instance;
    private $handler;

    protected $acl = array(
        array(
            'perms' => \Zookeeper::PERM_ALL,
            'scheme' => 'world',
            'id' => 'anyone'
        )
    );

    private function __construct(array $config)
    {
        $this->handler = new \Zookeeper($config['host'] . ':' . $config['port']);
    }

    public static function instance(array $config)
    {
        if (self::$instance == null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function set($path, $value, $version = -1, array &$stat = null)
    {
        return $this->handler->set($path, $value, $version, $stat);
    }

    public function get($path, $watcherCb = null, array &$stat = null, $maxSize = 0)
    {
        return $this->handler->get($path, $watcherCb, $stat, $maxSize);
    }

    public function setWatcher($watcherCb)
    {
        return $this->handler->setWatcher($watcherCb);
    }

    public function delete($path, $version = -1)
    {
        return $this->handler->delete($path, $version);
    }

    public function exists($path)
    {
        return $this->handler->exists($path);
    }

    public function create($path, $value, array $acls = null, $flags = null)
    {
        if(empty($acls)) {
            $acls = $this->acl;
        }
        if($this->exists($path)) {
            return true;
        }
        if(!$this->create(dirname($path), $value, $acls, $flags)) {
            return false;
        }
        return $this->handler->create($path, $value, $acls, $flags);
    }

    public function getChildren($path, $watcherCb = null)
    {
        return $this->handler->getChildren($path, $watcherCb);
    }
}