<?php

namespace Cherry\Jobs\Helpers;

use Exception;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\PendingDispatch;

Trait JobFailedRetry
{
    /**
     * 重试次数 默认首次
     * @author xueyu
     * @var int
     */
    protected $cherry_retry_times = 1;

    /**
     * 要重试的任务
     * @author xueyu
     * @var clone self
     */
    protected $cherry_retry_job;

    /**
     * 如果handle中会改变类的属性要先保存实例
     * @author xueyu
     */
    protected function setRetryJob()
    {
        $this->cherry_retry_job = clone $this;
    }

    /**
     * 队列失败处理
     * @author xueyu
     * @param Exception $exception
     */
    public function failed(Exception $exception)
    {
        $job = $this->cherry_retry_job ?? clone $this;
        $job->cherry_retry_times++;

        switch ($this->cherry_retry_times)
        {
            case 1:
                (new PendingDispatch($job))->delay(Carbon::now()->addSecond(5));
                break;

            case 2:
                (new PendingDispatch($job))->delay(Carbon::now()->addSecond(30));
                break;

            case 3:
                (new PendingDispatch($job))->delay(Carbon::now()->addMinutes(10));
                break;

            default:
                event(new JobFailedEvent($exception));
        }

    }

}
