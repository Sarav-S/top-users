<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddPostToCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $posts = cache()->get('top_users_data_redis', []);
            array_push($posts, [
                'user_id' => $this->post['user_id'],
                'post_id' => $this->post['id'],
                'title' => $this->post['title'],
                'username' => $this->post['username'],
                'expiration' => Carbon::parse($this->post['created_at'])->addDays(7)->endOfDay()->timestamp,
            ]);
            cache()->put('top_users_data_redis', $posts);
        } catch (\Exception $e) {
            logger($e);
        }
    }
}
