<?php

namespace App\Observers;

use App\Models\WorkQueue;

class WorkQueueObserver
{
    /**
     * Handle the WorkQueue "updated" event.
     */
    public function updated(WorkQueue $workQueue): void
    {
        // Check if status was changed to 'delivered'
        if ($workQueue->wasChanged('status') && $workQueue->status === WorkQueue::STATUS_DELIVERED) {
            // Auto-set delivered_at timestamp if not already set
            if (!$workQueue->delivered_at) {
                $workQueue->delivered_at = now();
                $workQueue->saveQuietly(); // Save without triggering another observer call
            }
        }
        
        // If status changed away from 'delivered', clear the delivered_at timestamp
        if ($workQueue->wasChanged('status') && $workQueue->status !== WorkQueue::STATUS_DELIVERED) {
            if ($workQueue->delivered_at) {
                $workQueue->delivered_at = null;
                $workQueue->saveQuietly();
            }
        }
    }

    /**
     * Handle the WorkQueue "saving" event.
     */
    public function saving(WorkQueue $workQueue): void
    {
        // If status is being set to 'delivered' and delivered_at is not set, set it
        if ($workQueue->status === WorkQueue::STATUS_DELIVERED && !$workQueue->delivered_at) {
            $workQueue->delivered_at = now();
        }
        
        // If status is not 'delivered' and delivered_at is set, clear it
        if ($workQueue->status !== WorkQueue::STATUS_DELIVERED && $workQueue->delivered_at) {
            $workQueue->delivered_at = null;
        }
    }
}
