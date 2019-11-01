<?php

namespace app\biz\scheduler\service;

interface SchedulerService
{
    const JOB_MEMORY_LIMIT = 209715200; //200MB

    /**
     * 注册Job
     *
     * @param [type] $job
     * @return void
     * @description 
     * @author
     */
    public function register(array $job);

    /**
     * 执行定时任务
     *
     * @return void
     * @description 
     * @author
     */
    public function execute();

    /**
     * 启用任务
     *
     * @param [type] $jobId
     * @return void
     * @description 
     * @author
     */
    public function enabledJob($jobId);

    /**
     * 禁止任务
     *
     * @param [type] $jobId
     * @return void
     * @description 
     * @author
     */
    public function disabledJob($jobId);

    public function findExecutingJobFiredByJobId($jobId);

    public function countJobs($condition);

    public function searchJobs($condition, $orderBy, $start, $limit);

    /**
     * 查询任务执行日志
     *
     * @param [type] $condition
     * @param [type] $orderBy
     * @param [type] $start
     * @param [type] $limit
     * @return void
     */
    public function searchJobLogs($condition, $orderBy, $start, $limit);

    public function countJobLogs($condition);

    public function searchJobFires($condition, $orderBy, $start, $limit);

    public function countJobFires($condition);

    /**
     * 删除定时任务
     *
     * @param [type] $id
     * @return void
     * @description 
     * @author
     */
    public function deleteJob($id);

    /**
     * 创建任务进程
     *
     * @param [type] $process
     * @return void
     * @description 
     * @author
     */
    public function createJobProcess($process);

    /**
     * 更新任务进程
     *
     * @param [type] $id
     * @param [type] $process
     * @return void
     * @description 
     * @author
     */
    public function updateJobProcess($id, $process);

    public function deleteUnacquiredJobFired($keepDays);

    public function markTimeoutJobs();
}
