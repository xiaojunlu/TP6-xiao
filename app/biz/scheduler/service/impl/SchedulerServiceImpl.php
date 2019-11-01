<?php

namespace app\biz\scheduler\service\impl;

use app\biz\BaseService;
use Cron\CronExpression;
use app\common\TimeMachine;
use app\common\ArrayToolkit;
use app\biz\scheduler\service\JobPool;
use app\biz\scheduler\service\SchedulerService;
use app\biz\common\exception\ServiceException;
use app\common\exception\InvalidArgumentException;

class SchedulerServiceImpl extends BaseService implements SchedulerService
{
    const EXECUTING = 'executing';

    public function register(array $job)
    {
        if (empty($job['expression'])) {
            throw new InvalidArgumentException('expression is empty.');
        }

        if (empty($job['name'])) {
            throw new InvalidArgumentException('name is empty.');
        }

        if (empty($job['class'])) {
            throw new InvalidArgumentException('class is empty.');
        }

        if (is_integer($job['expression'])) {
            $job['next_fire_time'] = $job['expression'] - $job['expression'] % 60;
            unset($job['expression']);
        } else {
            if (!CronExpression::isValidExpression($job['expression'])) {
                throw new InvalidArgumentException('expression is invalid.');
            }

            $job['next_fire_time'] = $this->getNextFireTime($job['expression']);
        }

        $default = array(
            'misfire_threshold' => 300,
            'misfire_policy' => 'missed',
            'priority' => 200,
            'source' => 'MAIN',
        );

        $job = array_merge($default, $job);

        $job = $this->getJobDao()->create($job);

        $this->dispatchEvent('scheduler.job.created', $job);

        $jobFired['job_detail'] = $job;

        $this->createJobLog($jobFired, 'created');

        return $job;
    }

    public function countJobs($condition)
    {
        return $this->getJobDao()->count($condition);
    }

    public function searchJobs($condition, $orderBy, $start, $limit)
    {
        return $this->getJobDao()->search($condition, $orderBy, $start, $limit);
    }

    public function searchJobLogs($condition, $orderBy, $start, $limit)
    {
        return $this->getJobLogDao()->search($condition, $orderBy, $start, $limit);
    }

    public function countJobLogs($condition)
    {
        return $this->getJobLogDao()->count($condition);
    }

    public function searchJobFires($condition, $orderBy, $start, $limit)
    {
        return $this->getJobFiredDao()->search($condition, $orderBy, $start, $limit);
    }

    public function countJobFires($condition)
    {
        return $this->getJobFiredDao()->count($condition);
    }

    public function execute()
    {
        $initProcess = $this->createJobProcess(array(
            'pid' => getmypid(), //获取 PHP 进程的 ID
        ));

        $process['start_time'] = $this->getMillisecond();
        $this->updateWaitingJobsToAcquired();
        do {
            $result = $this->runAcquiredJobs($initProcess);
            $peakMemory = !function_exists('memory_get_peak_usage') ? 0 : memory_get_peak_usage();
            $currentTime = $this->getMillisecond();
            $processUsedTime = (int) (($currentTime - $process['start_time']) / 1000);
        } while ($result && $peakMemory < SchedulerService::JOB_MEMORY_LIMIT && $processUsedTime < $this->getMaxProcessExecTime());
        $process['end_time'] = $this->getMillisecond();
        $process['cost_time'] = $process['end_time'] - $process['start_time'];
        $process['peak_memory'] = $peakMemory;

        $this->updateJobProcess($initProcess['id'], $process);
    }

    public function enabledJob($jobId)
    {
        $job = $this->getJobDao()->update($jobId, array('enabled' => 1));
        $this->createJobEnabledLog($job, 'enabled');

        return $job;
    }

    public function disabledJob($jobId)
    {
        $job = $this->getJobDao()->update($jobId, array('enabled' => 0));
        $this->createJobEnabledLog($job, 'disabled');

        return $job;
    }

    protected function createJobEnabledLog($job, $enableStatus)
    {
        $log = ArrayToolkit::parts($job, array(
            'name',
            'source',
            'class',
            'args',
            'priority',
        ));

        $log['status'] = $enableStatus;
        $log['job_id'] = $job['id'];
        $log['hostname'] = gethostname();

        $this->getJobLogDao()->create($log);
    }

    /**
     * 执行获取的任务
     *
     * @param [type] $initProcess 创建的进程信息
     * @return void
     * @description 
     * @author
     */
    protected function runAcquiredJobs($initProcess)
    {
        $result = '';
        $jobFired = $this->triggerJob();
        if (empty($jobFired)) {
            return false;
        }
        $process['process_id'] = $initProcess['id'];
        $process['pid'] = $initProcess['pid'];
        $process['start_time'] = $this->getMillisecond();
        $jobInstance = $this->createJobInstance($jobFired);
        try {
            $result = $this->getJobPool()->execute($jobInstance);
        } catch (\Exception $e) {
            $this->createErrorLog($jobFired, $e->getMessage(), $e->getTraceAsString());
        }
        $process['end_time'] = $this->getMillisecond();
        $process['cost_time'] = $process['end_time'] - $process['start_time'];
        $process['peak_memory'] = !function_exists('memory_get_peak_usage') ? 0 : memory_get_peak_usage(); //获取内存使用情况

        if (empty($result)) {
            $result = 'failure';
        }
        $this->jobExecuted($jobFired, $result, $process);

        return true;
    }

    public function deleteJob($id)
    {
        $job = $this->getJobDao()->get($id);

        $this->getJobDao()->delete($id);

        $this->createJobLog(array('job_detail' => $job), 'delete');
    }

    public function createJobProcess($process)
    {
        return $this->getJobProcessDao()->create($process);
    }

    protected function createJobLog($jobFired, $status)
    {
        $job = $jobFired['job_detail'];
        $log = ArrayToolkit::parts($job, array(
            'name',
            'source',
            'class',
            'args',
            'priority',
            'status',
            'message',
            'trace',
            'process_id',
            'pid',
        ));

        if (!empty($jobFired['id'])) {
            $log['job_fired_id'] = $jobFired['id'];
        }
        $log['status'] = $status;
        $log['job_id'] = $job['id'];
        $log['hostname'] = gethostname();

        $this->getJobLogDao()->create($log);
    }

    public function updateJobProcess($id, $process)
    {
        return $this->getJobProcessDao()->update($id, $process);
    }

    public function deleteUnacquiredJobFired($keepDays)
    {
        $startTime = strtotime("-{$keepDays} day", TimeMachine::time());

        return $this->getJobFiredDao()->deleteUnacquiredBeforeCreatedTime($startTime);
    }

    public function markTimeoutJobs()
    {
        $runtimeout = $this->getTimeout();
        $jobFireds = $this->getJobFiredDao()->search(array(
            'status' => 'executing',
            'fired_time_LT' => time() - $runtimeout,
        ), array(), 0, 100);

        foreach ($jobFireds as $jobFired) {
            if ('Scheduler_MarkExecutingTimeoutJob' != $jobFired['job_detail']['name']) {
                $this->markTimeout($jobFired);
            }
        }
    }

    protected function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime()); //返回当前 Unix 时间戳的微秒数：

        return (float) sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }

    protected function markTimeout($jobFired)
    {
        $jobFired = $this->getJobFiredDao()->update($jobFired['id'], array('status' => 'timeout'));

        $this->getJobPool()->release($jobFired['job_detail']);

        $this->createJobLog($jobFired, 'timeout');
    }

    protected function updateWaitingJobsToAcquired()
    {
        $lock = app('lock');
        $lockName = 'scheduler.job.acquire_jobs';

        try {
            $lock->get($lockName, 20);
            $this->beginTransaction();

            $jobs = $this->getJobDao()->findWaitingJobsByLessThanFireTime(time());

            foreach ($jobs as $job) {
                $this->updateJobToAcquired($job);
            }

            $this->commit();
            $lock->release($lockName);
        } catch (\Exception $e) {
            $this->rollback();
            $lock->release($lockName);
            throw new ServiceException($e);
        }
    }

    protected function updateJobToAcquired($job)
    {
        $jobFired = array(
            'job_id' => $job['id'],
            'fired_time' => $job['next_fire_time'],
            'status' => 'acquired',
            'job_detail' => $job,
            'job_name' => $job['name'],
        );
        if (empty($job['expression'])) {
            $jobFired['priority'] = 200;
        }

        $jobFired = $this->getJobFiredDao()->create($jobFired);
        $jobFired['job_detail'] = $this->updateNextFireTime($job);

        $this->dispatchEvent('scheduler.job.acquired', $job);

        $this->createJobLog($jobFired, 'acquired');
    }

    protected function updateNextFireTime($job)
    {
        if ($job['next_fire_time'] > time()) {
            return $job;
        }

        if (empty($job['expression'])) {
            $this->deleteJob($job['id']);

            return $job;
        }

        $nextFireTime = $this->getNextFireTime($job['expression']);

        $fields = array(
            'pre_fire_time' => $job['next_fire_time'],
            'next_fire_time' => $nextFireTime,
        );

        return $this->getJobDao()->update($job['id'], $fields);
    }

    protected function getNextFireTime($expression)
    {
        $cron = CronExpression::factory($expression);

        return strtotime($cron->getNextRunDate()->format('Y-m-d H:i:s'));
    }

    protected function triggerJob()
    {
        $lock = app('lock');
        $lockName = 'scheduler.job.trigger';
        try {
            $result = $lock->get($lockName, 20);
            if (!$result) {
                return;
            }
            $this->beginTransaction();

            $jobFired = $this->getAcquiredJob();

            $this->commit();
            $lock->release($lockName);

            return $jobFired;
        } catch (\Exception $e) {
            $this->rollback();
            $lock->release($lockName);
            throw new ServiceException($e);
        }
    }

    protected function getAcquiredJob()
    {
        $createdJobFired = $this->getJobFiredDao()->getByStatus('acquired');
        if (empty($createdJobFired)) {
            return;
        }

        $result = $this->check($createdJobFired);
        $jobFired = $this->getJobFiredDao()->update($createdJobFired['id'], array('status' => $result));

        $this->createJobLog($jobFired, $result);

        if (self::EXECUTING == $result) {
            //执行任务触发
            $this->dispatchEvent('scheduler.job.executing', $jobFired);

            return $jobFired;
        }

        return $this->getAcquiredJob();
    }

    public function createErrorLog($jobFired, $message, $trace)
    {
        $jobFired['job_detail']['message'] = $message;
        $jobFired['job_detail']['trace'] = $trace;
        $this->createJobLog($jobFired, 'error');
    }

    protected function check($jobFired)
    {
        $result = $this->checkExecuting($jobFired);
        if (static::EXECUTING != $result) {
            return $result;
        }

        return $this->checkMisfire($jobFired);
    }

    protected function checkMisfire($jobFired)
    {
        $now = time();
        $job = $jobFired['job_detail'];
        $fireTime = $job['next_fire_time'];

        if (!empty($job['misfire_threshold']) && ($now - $fireTime) > $job['misfire_threshold']) {
            return $job['misfire_policy'];
        }

        return static::EXECUTING;
    }

    protected function jobExecuted($jobFired, $result, $process)
    {
        $process = ArrayToolkit::parts($process, array('process_id', 'peak_memory', 'start_time', 'end_time', 'cost_time'));
        if ('success' == $result) {
            $this->getJobFiredDao()->update($jobFired['id'], array_merge(array(
                'status' => 'success',
            ), $process));
            $this->createJobLog($jobFired, 'success');
        } elseif ('retry' == $result) {
            if ($jobFired['retry_num'] < $this->getMaxRetryNum()) {
                $this->getJobFiredDao()->update($jobFired['id'], array_merge(array(
                    'retry_num' => $jobFired['retry_num'] + 1,
                    'fired_time' => time(),
                    'status' => 'acquired',
                ), $process));
                $this->createJobLog($jobFired, 'acquired');
            } else {
                $result = 'failure';
                $this->getJobFiredDao()->update($jobFired['id'], array_merge(array(
                    'fired_time' => time(),
                    'status' => $result,
                ), $process));
                $this->createJobLog($jobFired, $result);
            }
        } else {
            $this->getJobFiredDao()->update($jobFired['id'], array_merge(array(
                'fired_time' => time(),
                'status' => $result,
            ), $process));
            $this->createJobLog($jobFired, $result);
        }

        $this->dispatchEvent('scheduler.job.executed', array('result' => $result));
    }

    protected function checkExecuting($jobFired)
    {
        $firedJobs = $this->findExecutingJobFiredByJobId($jobFired['job_id']);
        foreach ($firedJobs as $item) {
            if ($item['id'] == $jobFired['id']) {
                continue;
            }

            if (static::EXECUTING == $item['status']) {
                return 'ignore';
            }
        }

        return static::EXECUTING;
    }

    public function findExecutingJobFiredByJobId($jobId)
    {
        return $this->getJobFiredDao()->findByJobIdAndStatus($jobId, static::EXECUTING);
    }

    /**
     * 实例化定时任务类
     *
     * @param [type] $jobFired
     * @return void
     * @description 
     * @author
     */
    protected function createJobInstance($jobFired)
    {
        $job = $jobFired['job_detail'];
        $class = $jobFired['job_detail']['class'];

        return new $class($job);
    }

    protected function getJobLogDao()
    {
        return $this->createDao('scheduler.JobLogDao');
    }

    protected function getJobDao()
    {
        return $this->createDao('scheduler.JobDao');
    }

    /**
     * @return JobProcessDao
     */
    protected function getJobProcessDao()
    {
        return $this->createDao('scheduler.JobProcessDao');
    }

    protected function getJobFiredDao()
    {
        return $this->createDao('scheduler.JobFiredDao');
    }

    protected function getJobPool()
    {
        return new JobPool();
    }

    protected function getTimeout()
    {
        $schedulerOptions = app('scheduler.options');
        return $schedulerOptions['timeout'];
    }

    protected function getMaxRetryNum()
    {
        $schedulerOptions = app('scheduler.options');
        return $schedulerOptions['max_retry_num'];
    }

    protected function getMaxProcessExecTime()
    {
        $schedulerOptions = app('scheduler.options');
        return $schedulerOptions['max_process_exec_time'];
    }
}
