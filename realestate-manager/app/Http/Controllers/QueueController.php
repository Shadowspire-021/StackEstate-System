<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * Show failed jobs list
     */
    public function failedJobs()
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(20);

        return view('queue.failed-jobs', compact('failedJobs'));
    }

    /**
     * Retry a failed job
     */
    public function retryJob($jobId)
    {
        $job = DB::table('failed_jobs')->where('id', $jobId)->first();

        if (!$job) {
            return back()->withErrors(['error' => 'Job not found.']);
        }

        // Re-queue the job
        $payload = json_decode($job->payload, true);
        if (isset($payload['data']['command'])) {
            // Unserialize and re-dispatch
            $command = unserialize($payload['data']['command']);
            dispatch($command);

            // Remove from failed_jobs
            DB::table('failed_jobs')->where('id', $jobId)->delete();

            return back()->with('success', 'Job re-queued successfully.');
        }

        return back()->withErrors(['error' => 'Cannot re-queue: invalid payload.']);
    }

    /**
     * Delete a failed job
     */
    public function deleteJob($jobId)
    {
        DB::table('failed_jobs')->where('id', $jobId)->delete();

        return back()->with('success', 'Failed job deleted.');
    }
}
