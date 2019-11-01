<?php

namespace app\biz\scheduler\service;

use app\biz\scheduler\Job;
use app\common\ArrayToolkit;
use app\biz\common\ServiceKernel;

class JobPool
{
    private $options = array();

    const SUCCESS = 'success';
    const POOL_FULL = 'pool_full';

    public function __construct()
    {
        $this->options = app('scheduler.options');
    }

    public function execute(Job $job)
    {
        if ($this->isFull($job)) {
            return static::POOL_FULL;
        }

        $result = '';
        try {
            $result = $job->execute();
        } catch (\Exception $e) {
            $this->release($job);
            throw $e;
        }

        $this->release($job);

        if (empty($result)) {
            return static::SUCCESS;
        }

        return $result;
    }

    public function getJobPool($name = 'default')
    {
        return $this->getJobPoolDao()->getByName($name);
    }

    public function release($job)
    {
        $jobPool = $this->getJobPool($job['pool']);

        $lockName = "job_pool.{$jobPool['name']}";

        app('lock')->get($lockName, 10);

        $this->wavePoolNum($jobPool['id'], -1);

        app('lock')->release($lockName);
    }

    protected function isFull($job)
    {
        $options = array_merge($this->options, array('name' => $job['pool']));

        $lockName = "job_pool.{$options['name']}";
        app('lock')->get($lockName, 10);

        $jobPool = $this->getJobPool($options['name']);
        if (empty($jobPool)) {
            $jobPool = ArrayToolkit::parts($options, array('max_num', 'num', 'name', 'timeout'));
            $jobPool = $this->getJobPoolDao()->create($jobPool);
        }

        if ($jobPool['num'] == $jobPool['max_num']) {
            app('lock')->release($lockName);

            return true;
        }

        $this->wavePoolNum($jobPool['id'], 1);

        app('lock')->release($lockName);

        return false;
    }

    protected function wavePoolNum($id, $diff)
    {
        $ids = array($id);
        $diff = array('num' => $diff);
        $jobPool = $this->getJobPoolDao()->get($id);
        if (!(0 == $jobPool['num'] && $diff['num'] < 0)) {
            $this->getJobPoolDao()->wave($ids, $diff);
        }
    }

    /**
     * @return getJobPoolDao
     */
    protected function getJobPoolDao()
    {
        return ServiceKernel::instance()->createDao('scheduler.JobPoolDao');
    }
}
